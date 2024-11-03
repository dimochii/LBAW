DROP SCHEMA IF EXISTS lbaw2454 CASCADE;
CREATE SCHEMA lbaw2454;

SET search_path TO lbaw2454;

CREATE TYPE TopicStatus AS ENUM ('PENDING', 'ACCEPTED', 'REJECTED');
CREATE TYPE ReportType AS ENUM ('userReport', 'commentReport',
'itemReport', 'topicReport');


CREATE TABLE Image (
	imageID SERIAL PRIMARY KEY,
	path VARCHAR(512) NOT NULL
);

CREATE TABLE Community (
	communityID SERIAL PRIMARY KEY,
	name VARCHAR(255) NOT NULL UNIQUE,
	description TEXT,
	creationDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP CHECK (creationDate <= CURRENT_TIMESTAMP),
	privacy BOOLEAN DEFAULT FALSE,
	imageID INT,
	FOREIGN KEY (imageID) REFERENCES Image(imageID)
);

CREATE TABLE Post (
	postID SERIAL PRIMARY KEY,
	title VARCHAR(255) NOT NULL,
	creationDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP CHECK (creationDate <= CURRENT_TIMESTAMP),
	content TEXT NOT NULL,
	communityID INT,
	FOREIGN KEY (communityID) REFERENCES Community(communityID)
);

CREATE TABLE AuthenticatedUser (
	authenticatedUserID SERIAL PRIMARY KEY,
	name VARCHAR(255) NOT NULL,
	username VARCHAR(255) NOT NULL UNIQUE,
	email VARCHAR(255) NOT NULL UNIQUE CHECK (email LIKE '%_@__%.__%'),
	password VARCHAR(255) NOT NULL,
	reputation INT DEFAULT 0,
	isSuspended BOOLEAN DEFAULT FALSE,
	creationDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP CHECK (creationDate <= CURRENT_TIMESTAMP),
	birthDate TIMESTAMP NOT NULL CHECK (birthDate < CURRENT_TIMESTAMP),
	description TEXT,
	isAdmin BOOLEAN DEFAULT FALSE,
	imageID INT,
	FOREIGN KEY (imageID) REFERENCES Image(imageID)
);

CREATE TABLE UserFollower (
	followerID INT,
	followedID INT,
	PRIMARY KEY (followerID, followedID),
	FOREIGN KEY (followerID) REFERENCES AuthenticatedUser(authenticatedUserID),
	FOREIGN KEY (followedID) REFERENCES AuthenticatedUser(authenticatedUserID)
);

CREATE TABLE CommunityModerator (
	authenticatedUserID INT,
	communityID INT,
	PRIMARY KEY (authenticatedUserID, communityID),
	FOREIGN KEY (authenticatedUserID) REFERENCES AuthenticatedUser(authenticatedUserID),
	FOREIGN KEY (communityID) REFERENCES Community(communityID)
);

CREATE TABLE Vote (
	voteID SERIAL PRIMARY KEY,
	upvote BOOLEAN NOT NULL,
	authenticatedUserID INT,
	FOREIGN KEY (authenticatedUserID) REFERENCES AuthenticatedUser(authenticatedUserID)
);

CREATE TABLE PostVote (
	voteID INT,
	postID INT,
	PRIMARY KEY (voteID, postID),
	FOREIGN KEY (voteID) REFERENCES Vote(voteID),
	FOREIGN KEY (postID) REFERENCES Post(postID)
);

CREATE TABLE Comment (
	commentID SERIAL PRIMARY KEY,
	content TEXT NOT NULL,
	creationDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP CHECK (creationDate <= CURRENT_TIMESTAMP),
	updated BOOLEAN DEFAULT FALSE,
	authenticatedUserID INT,
	postID INT,
	parentCommentID INT,
	FOREIGN KEY (authenticatedUserID) REFERENCES AuthenticatedUser(authenticatedUserID),
	FOREIGN KEY (postID) REFERENCES Post(postID),
	FOREIGN KEY (parentCommentID) REFERENCES Comment(commentID)
);

CREATE TABLE CommentVote (
	voteID INT,
	commentID INT,
	PRIMARY KEY (voteID, commentID),
	FOREIGN KEY (voteID) REFERENCES Vote(voteID),
	FOREIGN KEY (commentID) REFERENCES Comment(commentID)
);

CREATE TABLE News (
	postID INT PRIMARY KEY,
	newsURL VARCHAR(255) NOT NULL,
	FOREIGN KEY (postID) REFERENCES Post(postID)
);

CREATE TABLE Topic (
	postID INT PRIMARY KEY,
	reviewDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP CHECK (reviewDate <= CURRENT_TIMESTAMP),
    status TopicStatus NOT NULL DEFAULT 'PENDING',
	FOREIGN KEY (postID) REFERENCES Post(postID)
);


CREATE TABLE Notification (
	notificationID SERIAL PRIMARY KEY,
	isRead BOOLEAN DEFAULT FALSE,
	notificationDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP CHECK (notificationDate <= CURRENT_TIMESTAMP),
	authenticatedUserID INT,
	FOREIGN KEY (authenticatedUserID) REFERENCES AuthenticatedUser(authenticatedUserID)
);

CREATE TABLE FollowNotification (
	notificationID INT PRIMARY KEY,
	followerID INT,
	FOREIGN KEY (notificationID) REFERENCES Notification(notificationID),
	FOREIGN KEY (followerID) REFERENCES AuthenticatedUser(authenticatedUserID)
);

CREATE TABLE UpvoteNotification (
	notificationID INT PRIMARY KEY,
	voteID INT,
	FOREIGN KEY (notificationID) REFERENCES Notification(notificationID),
	FOREIGN KEY (voteID) REFERENCES Vote(voteID)
);

CREATE TABLE CommentNotification (
	notificationID INT PRIMARY KEY,
	commentID INT,
	FOREIGN KEY (notificationID) REFERENCES Notification(notificationID),
	FOREIGN KEY (commentID) REFERENCES Comment(commentID)
);

CREATE TABLE PostNotification (
	notificationID INT PRIMARY KEY,
	postID INT,
	FOREIGN KEY (notificationID) REFERENCES Notification(notificationID),
	FOREIGN KEY (postID) REFERENCES Post(postID)
);

CREATE TABLE Suspension (
	suspensionID SERIAL PRIMARY KEY,
	reason TEXT NOT NULL,
	start TIMESTAMP NOT NULL,
	duration TIMESTAMP,
	authenticatedUserID INT,
	FOREIGN KEY (authenticatedUserID) REFERENCES AuthenticatedUser(authenticatedUserID)
);

CREATE TABLE Report (
	reportID SERIAL PRIMARY KEY,
	reason TEXT NOT NULL,
	reportDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP CHECK (reportDate <= CURRENT_TIMESTAMP),
	isOpen BOOLEAN DEFAULT TRUE,
	reportType TopicStatus NOT NULL,
	authenticatedUserID INT,
	FOREIGN KEY (authenticatedUserID) REFERENCES AuthenticatedUser(authenticatedUserID)
);


CREATE TABLE CommunityFollower (
	authenticatedUserID INT,
	communityID INT,
	PRIMARY KEY (authenticatedUserID, communityID),
	FOREIGN KEY (authenticatedUserID) REFERENCES AuthenticatedUser(authenticatedUserID),
	FOREIGN KEY (communityID) REFERENCES Community(communityID)
);

CREATE TABLE Author (
	authenticatedUserID INT,
	postID INT,
	pinned BOOLEAN DEFAULT FALSE,
	PRIMARY KEY (authenticatedUserID, postID),
	FOREIGN KEY (authenticatedUserID) REFERENCES AuthenticatedUser(authenticatedUserID),
	FOREIGN KEY (postID) REFERENCES Post(postID)
);

CREATE TABLE FavouritePost (
	authenticatedUserID INT,
	postID INT,
	PRIMARY KEY (authenticatedUserID, postID),
	FOREIGN KEY (authenticatedUserID) REFERENCES AuthenticatedUser(authenticatedUserID),
	FOREIGN KEY (postID) REFERENCES Post(postID)
);

---------------------------------------------------
ALTER TABLE AuthenticatedUser
ADD COLUMN tsvector_name TSVECTOR;

CREATE FUNCTION user_name_update() RETURNS TRIGGER AS $$
BEGIN
	IF TG_OP = 'INSERT' OR (TG_OP = 'UPDATE' AND NEW.name IS DISTINCT FROM OLD.name) THEN
    	NEW.tsvector_name = setweight(to_tsvector('english', NEW.name), 'C');
	END IF;
	RETURN NEW;
END $$ LANGUAGE plpgsql;

CREATE TRIGGER user_name_update
BEFORE INSERT OR UPDATE ON AuthenticatedUser
FOR EACH ROW
EXECUTE PROCEDURE user_name_update();

CREATE INDEX idx_user_name_search ON AuthenticatedUser USING GiST (tsvector_name);

------------------------------------------------------------------
ALTER TABLE Post ADD COLUMN tsvector_title TSVECTOR;
CREATE FUNCTION post_title_update() RETURNS TRIGGER AS $$
BEGIN
	IF TG_OP = 'INSERT' OR (TG_OP = 'UPDATE' AND NEW.title IS DISTINCT FROM OLD.title) THEN
    	NEW.tsvector_title = setweight(to_tsvector('english', NEW.title), 'A');
	END IF;
	RETURN NEW;
END $$ LANGUAGE plpgsql;
CREATE TRIGGER post_title_update
BEFORE INSERT OR UPDATE ON Post
FOR EACH ROW
EXECUTE PROCEDURE post_title_update();

CREATE INDEX idx_post_title_search ON Post USING GIN (tsvector_title);
----------------------------------------------------------------
ALTER TABLE Post
ADD COLUMN tsvector_content TSVECTOR;

CREATE FUNCTION post_content_update() RETURNS TRIGGER AS $$
BEGIN
	IF TG_OP = 'INSERT' OR (TG_OP = 'UPDATE' AND NEW.content IS DISTINCT FROM OLD.content) THEN
    	NEW.tsvector_content = setweight(to_tsvector('english', NEW.content), 'B');
	END IF;
	RETURN NEW;
END $$ LANGUAGE plpgsql;

CREATE TRIGGER post_content_update
BEFORE INSERT OR UPDATE ON Post
FOR EACH ROW
EXECUTE PROCEDURE post_content_update();

CREATE INDEX idx_post_content_search ON Post USING GIN (tsvector_content);
------------------------------------------------------
CREATE INDEX idx_post_creation_date_btree ON Post USING btree (creationDate);
CLUSTER Post USING idx_post_creation_date_btree;
------------------------------------------------------
CREATE INDEX idx_topic_status_hash ON Topic USING hash(status);
------------------------------------------------------
CREATE INDEX idx_topic_review_date_btree ON Topic USING btree (reviewDate);
------------------------------------------------------

CREATE FUNCTION comment_update_trigger() 
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated := TRUE;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER set_comment_updated
AFTER UPDATE OF content ON Comment
FOR EACH ROW
WHEN (OLD.content IS DISTINCT FROM NEW.content)
EXECUTE FUNCTION comment_update_trigger();


CREATE FUNCTION follow_create_notification_trigger()
RETURNS TRIGGER AS $$
DECLARE
    new_notification_id INT;
BEGIN

    INSERT INTO Notification (isRead, authenticatedUserID)
    VALUES (FALSE, NEW.followedID)
    RETURNING notificationID INTO new_notification_id;

    INSERT INTO FollowNotification (notificationID, followerID)
    VALUES (new_notification_id, NEW.followerID);

    RETURN NULL;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER create_follow_notification
AFTER INSERT ON UserFollower
FOR EACH ROW
EXECUTE FUNCTION follow_create_notification_trigger();


CREATE OR REPLACE FUNCTION vote_create_notification_trigger()
RETURNS TRIGGER AS $$
DECLARE
    post_owner_id INT;
BEGIN
    SELECT authenticatedUserID INTO post_owner_id
    FROM Author
    WHERE postID = NEW.postID;

    IF post_owner_id = NEW.voterID THEN
        RETURN NULL; 
    END IF;

    INSERT INTO Notification (isRead, link, authenticatedUserID)
    VALUES (FALSE, post_owner_id); 


    INSERT INTO UpvoteNotification (notificationID, voteID)
    VALUES (currval('Notification_notificationID_seq'), NEW.voteID);

    RETURN NULL;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER create_vote_notification
AFTER INSERT ON Vote
FOR EACH ROW
EXECUTE FUNCTION vote_create_notification_trigger();


CREATE FUNCTION comment_create_notification_trigger()
RETURNS TRIGGER AS $$
DECLARE
    post_owner_id INT;
BEGIN
    SELECT authenticatedUserID INTO post_owner_id 
    FROM Author 
    WHERE postID = NEW.postID;

    IF post_owner_id = NEW.authenticatedUserID THEN
        RETURN NULL;  
    END IF;

    INSERT INTO Notification (isRead, link, authenticatedUserID)
    VALUES (FALSE, post_owner_id); 

    INSERT INTO CommentNotification (notificationID, commentID)
    VALUES (currval('notification_id_seq'), NEW.commentID); 

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER create_comment_notification
AFTER INSERT ON Comment
FOR EACH ROW
EXECUTE FUNCTION comment_create_notification_trigger();



CREATE FUNCTION update_reputation_vote_trigger()
RETURNS TRIGGER AS $$
DECLARE
    post_owner_id INT;
BEGIN
    SELECT authenticatedUserID INTO post_owner_id  FROM Author WHERE postID = NEW.postID;

    IF post_owner_id = NEW.authenticatedUserID THEN
        RETURN NULL;  
    END IF;

    IF TG_OP = 'INSERT' THEN
        IF NEW.upvote THEN
            UPDATE AuthenticatedUser SET reputation = reputation + 1 WHERE authenticatedUserID = post_owner_id;
        ELSE
            UPDATE AuthenticatedUser SET reputation = reputation - 1 WHERE authenticatedUserID = post_owner_id;
        END IF;
    ELSEIF TG_OP = 'DELETE' THEN
        IF OLD.upvote THEN
            UPDATE AuthenticatedUser SET reputation = reputation - 1 WHERE authenticatedUserID = post_owner_id;
        ELSE
            UPDATE AuthenticatedUser SET reputation = reputation + 1 WHERE authenticatedUserID = post_owner_id;
        END IF;
    END IF;

    RETURN NULL;
END;
$$ LANGUAGE plpgsql;


CREATE TRIGGER update_reputation_vote
AFTER INSERT OR DELETE ON PostVote 
FOR EACH ROW
EXECUTE FUNCTION update_reputation_vote_trigger();


CREATE FUNCTION update_reputation_comment_trigger()
RETURNS TRIGGER AS $$
DECLARE
    post_owner_id INT;
BEGIN
    SELECT authenticatedUserID INTO post_owner_id
    FROM Author
    WHERE postID = NEW.postID;

    IF post_owner_id = NEW.authenticatedUserID THEN
        RETURN NULL;
    END IF;

    IF TG_OP = 'INSERT' THEN
        UPDATE AuthenticatedUser SET reputation = reputation + 1 WHERE authenticatedUserID = post_owner_id;
    ELSIF TG_OP = 'DELETE' THEN
        UPDATE AuthenticatedUser SET reputation = reputation - 1 WHERE authenticatedUserID = post_owner_id;
    END IF;

    RETURN NULL;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER update_reputation_comment
AFTER INSERT OR DELETE ON PostVote 
FOR EACH ROW
EXECUTE FUNCTION update_reputation_comment_trigger();




CREATE OR REPLACE FUNCTION check_before_posting()
RETURNS TRIGGER AS $$
DECLARE
    author_exists BOOLEAN; 
BEGIN
    SELECT EXISTS (
        SELECT 1 
        FROM CommunityFollower
        WHERE authenticatedUserID = NEW.authenticatedUserID 
          AND communityID = NEW.communityID
    ) INTO author_exists;

    IF NOT author_exists THEN
        RAISE EXCEPTION 'Author does not belong to the specified community. Cannot post.';
    END IF;

    RETURN NEW; 
END;
$$ LANGUAGE plpgsql;


CREATE TRIGGER check_before_posting
BEFORE INSERT ON Post
FOR EACH ROW
EXECUTE FUNCTION check_before_posting();