DROP SCHEMA IF EXISTS lbaw2454 CASCADE;
CREATE SCHEMA lbaw2454;

SET search_path TO lbaw2454;
CREATE TYPE topic_status AS ENUM ('pending', 'accepted', 'rejected');
CREATE TYPE report_type AS ENUM ('user_report', 'comment_report', 'item_report', 'topic_report');

CREATE TABLE images (
    id SERIAL PRIMARY KEY,
    path VARCHAR(512) NOT NULL
);

CREATE TABLE communities (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP CHECK (creation_date <= CURRENT_TIMESTAMP),
    privacy BOOLEAN DEFAULT FALSE,
    image_id INT,
    FOREIGN KEY (image_id) REFERENCES images(id)
);

CREATE TABLE posts (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP CHECK (creation_date <= CURRENT_TIMESTAMP),
    content TEXT NOT NULL,
    community_id INT,
    FOREIGN KEY (community_id) REFERENCES communities(id)
);

CREATE TABLE authenticated_users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    username VARCHAR(255) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE CHECK (email LIKE '%_@__%.__%'),
    password VARCHAR(255),  
    reputation INT DEFAULT 0,
    is_suspended BOOLEAN DEFAULT FALSE,
    creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP CHECK (creation_date <= CURRENT_TIMESTAMP),
    birth_date TIMESTAMP NOT NULL CHECK (birth_date < CURRENT_TIMESTAMP),
    description TEXT,
    is_admin BOOLEAN DEFAULT FALSE,
    image_id INT,
    google_id VARCHAR,
    FOREIGN KEY (image_id) REFERENCES images(id)
);


CREATE TABLE user_followers (
    id SERIAL PRIMARY KEY,
    follower_id INT,
    followed_id INT,
    FOREIGN KEY (follower_id) REFERENCES authenticated_users(id),
    FOREIGN KEY (followed_id) REFERENCES authenticated_users(id)
);

CREATE TABLE community_moderators (
    id SERIAL PRIMARY KEY,
    authenticated_user_id INT,
    community_id INT,
    FOREIGN KEY (authenticated_user_id) REFERENCES authenticated_users(id),
    FOREIGN KEY (community_id) REFERENCES communities(id)
);

CREATE TABLE votes (
    id SERIAL PRIMARY KEY,
    upvote BOOLEAN NOT NULL,
    authenticated_user_id INT,
    FOREIGN KEY (authenticated_user_id) REFERENCES authenticated_users(id)
);

CREATE TABLE post_votes (
    id SERIAL PRIMARY KEY,
    vote_id INT,
    post_id INT,

    FOREIGN KEY (vote_id) REFERENCES votes(id),
    FOREIGN KEY (post_id) REFERENCES posts(id)
);

CREATE TABLE comments (
    id SERIAL PRIMARY KEY,
    content TEXT NOT NULL,
    creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP CHECK (creation_date <= CURRENT_TIMESTAMP),
    updated BOOLEAN DEFAULT FALSE,
    authenticated_user_id INT,
    post_id INT,
    parent_comment_id INT,
    FOREIGN KEY (authenticated_user_id) REFERENCES authenticated_users(id),
    FOREIGN KEY (post_id) REFERENCES posts(id),
    FOREIGN KEY (parent_comment_id) REFERENCES comments(id)
);

CREATE TABLE comment_votes (
    id SERIAL PRIMARY KEY,
    vote_id INT,
    comment_id INT,
    FOREIGN KEY (vote_id) REFERENCES votes(id),
    FOREIGN KEY (comment_id) REFERENCES comments(id)
);

CREATE TABLE news (
    post_id INT PRIMARY KEY,
    news_url VARCHAR(255) NOT NULL,
    image_url VARCHAR(500),
    FOREIGN KEY (post_id) REFERENCES posts(id)
);

CREATE TABLE topics (
    post_id INT PRIMARY KEY,
    review_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP CHECK (review_date <= CURRENT_TIMESTAMP),
    status topic_status NOT NULL DEFAULT 'pending',
    FOREIGN KEY (post_id) REFERENCES posts(id)
);

CREATE TABLE notifications (
    id SERIAL PRIMARY KEY,
    is_read BOOLEAN DEFAULT FALSE,
    notification_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP CHECK (notification_date <= CURRENT_TIMESTAMP),
    authenticated_user_id INT,
    FOREIGN KEY (authenticated_user_id) REFERENCES authenticated_users(id)
);

CREATE TABLE follow_notifications (
    id SERIAL PRIMARY KEY,
    follower_id INT,
    notification_id INT,
    FOREIGN KEY (notification_id) REFERENCES notifications(id),
    FOREIGN KEY (follower_id) REFERENCES authenticated_users(id)
);

CREATE TABLE upvote_notifications (
    id SERIAL PRIMARY KEY,
    vote_id INT,
    notification_id INT,
    FOREIGN KEY (notification_id) REFERENCES notifications(id),
    FOREIGN KEY (vote_id) REFERENCES votes(id)
);

CREATE TABLE comment_notifications (
    id SERIAL PRIMARY KEY,
    comment_id INT,
    notification_id INT,
    FOREIGN KEY (notification_id) REFERENCES notifications(id),
    FOREIGN KEY (comment_id) REFERENCES comments(id)
);

CREATE TABLE post_notifications (
    id SERIAL PRIMARY KEY,
    post_id INT,
    notification_id INT,
    FOREIGN KEY (notification_id) REFERENCES notifications(id),
    FOREIGN KEY (post_id) REFERENCES posts(id)
);

CREATE TABLE suspensions (
    id SERIAL PRIMARY KEY,
    reason TEXT NOT NULL,
    start TIMESTAMP NOT NULL,
    duration TIMESTAMP,
    authenticated_user_id INT,
    FOREIGN KEY (authenticated_user_id) REFERENCES authenticated_users(id)
);

CREATE TABLE reports (
    id SERIAL PRIMARY KEY,
    reported_id INT NOT NULL,
    reason TEXT NOT NULL,
    report_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP CHECK (report_date <= CURRENT_TIMESTAMP),
    is_open BOOLEAN DEFAULT TRUE,
    report_type report_type NOT NULL,
    authenticated_user_id INT,
    FOREIGN KEY (authenticated_user_id) REFERENCES authenticated_users(id)
);

CREATE TABLE community_followers (
    authenticated_user_id INT,
    community_id INT,
    PRIMARY KEY (authenticated_user_id, community_id),
    FOREIGN KEY (authenticated_user_id) REFERENCES authenticated_users(id),
    FOREIGN KEY (community_id) REFERENCES communities(id)
);

CREATE TABLE authors (
    authenticated_user_id INT,
    post_id INT,
    pinned BOOLEAN DEFAULT FALSE,
    PRIMARY KEY (authenticated_user_id, post_id),
    FOREIGN KEY (authenticated_user_id) REFERENCES authenticated_users(id),
    FOREIGN KEY (post_id) REFERENCES posts(id)
);

CREATE TABLE favorite_posts (
    authenticated_user_id INT,
    post_id INT,
    PRIMARY KEY (authenticated_user_id, post_id),
    FOREIGN KEY (authenticated_user_id) REFERENCES authenticated_users(id),
    FOREIGN KEY (post_id) REFERENCES posts(id)
);

ALTER TABLE authenticated_users ADD COLUMN tsvector_name TSVECTOR;

CREATE FUNCTION user_name_update() RETURNS TRIGGER AS $$
BEGIN
    IF TG_OP = 'INSERT' OR (TG_OP = 'UPDATE' AND NEW.name IS DISTINCT FROM OLD.name) THEN
        NEW.tsvector_name = setweight(to_tsvector('english', NEW.name), 'C');
    END IF;
    RETURN NEW;
END $$ LANGUAGE plpgsql;

CREATE TRIGGER user_name_update
BEFORE INSERT OR UPDATE ON authenticated_users
FOR EACH ROW
EXECUTE FUNCTION user_name_update();

CREATE INDEX idx_user_name_search ON authenticated_users USING GIN (tsvector_name);

ALTER TABLE posts ADD COLUMN tsvector_title TSVECTOR;

CREATE FUNCTION post_title_update() RETURNS TRIGGER AS $$
BEGIN
    IF TG_OP = 'INSERT' OR (TG_OP = 'UPDATE' AND NEW.title IS DISTINCT FROM OLD.title) THEN
        NEW.tsvector_title = setweight(to_tsvector('english', NEW.title), 'A');
    END IF;
    RETURN NEW;
END $$ LANGUAGE plpgsql;

CREATE TRIGGER post_title_update
BEFORE INSERT OR UPDATE ON posts
FOR EACH ROW
EXECUTE FUNCTION post_title_update();

CREATE INDEX idx_post_title_search ON posts USING GIN (tsvector_title);

ALTER TABLE posts ADD COLUMN tsvector_content TSVECTOR;

CREATE FUNCTION post_content_update() RETURNS TRIGGER AS $$
BEGIN
    IF TG_OP = 'INSERT' OR (TG_OP = 'UPDATE' AND NEW.content IS DISTINCT FROM OLD.content) THEN
        NEW.tsvector_content = setweight(to_tsvector('english', NEW.content), 'B');
    END IF;
    RETURN NEW;
END $$ LANGUAGE plpgsql;

CREATE TRIGGER post_content_update
BEFORE INSERT OR UPDATE ON posts
FOR EACH ROW
EXECUTE FUNCTION post_content_update();

CREATE INDEX idx_post_content_search ON posts USING GIN (tsvector_content);

CREATE INDEX idx_post_creation_date_btree ON posts USING btree (creation_date);
CLUSTER posts USING idx_post_creation_date_btree;

CREATE INDEX idx_topic_status_hash ON topics USING hash(status);
CREATE INDEX idx_topic_review_date_btree ON topics USING btree (review_date);

-- Triggers for Notifications and Reputation Updates
CREATE FUNCTION comment_update_trigger() RETURNS TRIGGER AS $$
BEGIN
    NEW.updated := TRUE;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER set_comment_updated
AFTER UPDATE OF content ON comments
FOR EACH ROW
WHEN (OLD.content IS DISTINCT FROM NEW.content)
EXECUTE FUNCTION comment_update_trigger();

CREATE FUNCTION follow_create_notification_trigger()
RETURNS TRIGGER AS $$
DECLARE
    new_notification_id INT;
BEGIN
    INSERT INTO notifications (is_read, authenticated_user_id)
    VALUES (FALSE, NEW.followed_id)
    RETURNING id INTO new_notification_id;

    INSERT INTO follow_notifications (notification_id, follower_id)
    VALUES (new_notification_id, NEW.follower_id);

    RETURN NULL;
END;
$$ LANGUAGE plpgsql;


CREATE TRIGGER follow_notification_trigger
AFTER INSERT ON user_followers
FOR EACH ROW
EXECUTE FUNCTION follow_create_notification_trigger();




-- Step 1: Insert images
INSERT INTO images (path) VALUES
('images/user1.jpg'),
('images/user2.jpg'),
('images/user3.jpg'),
('images/user4.jpg'),
('images/user5.jpg'),
('images/user6.jpg'),
('images/user7.jpg'),
('images/user8.jpg'),
('images/user9.jpg'),
('images/user10.jpg'),
('images/user11.jpg'),
('images/user12.jpg'),
('images/user13.jpg'),
('images/user14.jpg'),
('images/user15.jpg'),
('images/user16.jpg'),
('images/user17.jpg'),
('images/user18.jpg'),
('images/user19.jpg'),
('images/user20.jpg'),
('images/user21.jpg'),
('images/user22.jpg'),
('images/user23.jpg'),
('images/user24.jpg'),
('images/user25.jpg'),
('images/user26.jpg'),
('images/user27.jpg'),
('images/user28.jpg'),
('images/user29.jpg'),
('images/user30.jpg'),
('images/user31.jpg'),
('images/user32.jpg'),
('images/user33.jpg'),
('images/user34.jpg'),
('images/user35.jpg'),
('images/user36.jpg'),
('images/user37.jpg'),
('images/user38.jpg'),
('images/user39.jpg'),
('images/user40.jpg'),
('images/user41.jpg'),
('images/user42.jpg'),
('images/user43.jpg'),
('images/user44.jpg'),
('images/user45.jpg'),
('images/user46.jpg'),
('images/user47.jpg'),
('images/user48.jpg'),
('images/user49.jpg'),
('images/user50.jpg'),
('images/user51.jpg'),
('images/user52.jpg'),
('images/user53.jpg'),
('images/user54.jpg'),
('images/user55.jpg'),
('images/user56.jpg'),
('images/user57.jpg'),
('images/user58.jpg'),
('images/user59.jpg'),
('images/user60.jpg'),
('images/user61.jpg'),
('images/user62.jpg'),
('images/user63.jpg'),
('images/user64.jpg'),
('images/user65.jpg'),
('images/user66.jpg'),
('images/user67.jpg'),
('images/user68.jpg'),
('images/user69.jpg'),
('images/user70.jpg'),
('images/user71.jpg'),
('images/user72.jpg'),
('images/user73.jpg'),
('images/user74.jpg'),
('images/user75.jpg'),
('images/user76.jpg'),
('images/user77.jpg'),
('images/user78.jpg'),
('images/user79.jpg'),
('images/user80.jpg'),
('images/user81.jpg'),
('images/user82.jpg'),
('images/user83.jpg'),
('images/user84.jpg'),
('images/user85.jpg'),
('images/user86.jpg'),
('images/user87.jpg'),
('images/user88.jpg'),
('images/user89.jpg'),
('images/user90.jpg'),
('images/user91.jpg'),
('images/user92.jpg'),
('images/user93.jpg'),
('images/user94.jpg'),
('images/user95.jpg'),
('images/user96.jpg'),
('images/user97.jpg'),
('images/user98.jpg'),
('images/user99.jpg'),
('images/user100.jpg'),
('images/user101.jpg'),
('images/user102.jpg'),
('images/user103.jpg'),
('images/user104.jpg'),
('images/user105.jpg'),
('images/user106.jpg'),
('images/user107.jpg'),
('images/user108.jpg'),
('images/user109.jpg'),
('images/user110.jpg'),
('images/user111.jpg'),
('images/user112.jpg'),
('images/user113.jpg'),
('images/user114.jpg'),
('images/user115.jpg'),
('images/user116.jpg'),
('images/user117.jpg'),
('images/user118.jpg'),
('images/user119.jpg'),
('images/user120.jpg'),
('images/user121.jpg'),
('images/user122.jpg'),
('images/user123.jpg'),
('images/user124.jpg'),
('images/user125.jpg'),
('images/user126.jpg'),
('images/user127.jpg'),
('images/user128.jpg'),
('images/user129.jpg'),
('images/user130.jpg'),
('images/user131.jpg'),
('images/user132.jpg'),
('images/user133.jpg'),
('images/user134.jpg'),
('images/user135.jpg'),
('images/user136.jpg'),
('images/user137.jpg'),
('images/user138.jpg'),
('images/user139.jpg'),
('images/user140.jpg'),
('images/user141.jpg'),
('images/user142.jpg'),
('images/user143.jpg'),
('images/user144.jpg'),
('images/user145.jpg'),
('images/user146.jpg'),
('images/user147.jpg'),
('images/user148.jpg'),
('images/user149.jpg'),
('images/user150.jpg'),
('images/user151.jpg'),
('images/user152.jpg'),
('images/user153.jpg'),
('images/user154.jpg'),
('images/user155.jpg'),
('images/user156.jpg'),
('images/user157.jpg'),
('images/user158.jpg'),
('images/user159.jpg'),
('images/user160.jpg'),
('images/user161.jpg'),
('images/user162.jpg'),
('images/user163.jpg'),
('images/user164.jpg'),
('images/user165.jpg'),
('images/user166.jpg'),
('images/user167.jpg'),
('images/user168.jpg'),
('images/user169.jpg'),
('images/user170.jpg'),
('images/user171.jpg'),
('images/user172.jpg'),
('images/user173.jpg'),
('images/user174.jpg'),
('images/user175.jpg'),
('images/user176.jpg'),
('images/user177.jpg'),
('images/user178.jpg'),
('images/user179.jpg'),
('images/user180.jpg'),
('images/user181.jpg'),
('images/user182.jpg'),
('images/user183.jpg'),
('images/user184.jpg'),
('images/user185.jpg'),
('images/user186.jpg'),
('images/user187.jpg'),
('images/user188.jpg'),
('images/user189.jpg'),
('images/user190.jpg'),
('images/user191.jpg'),
('images/user192.jpg'),
('images/user193.jpg'),
('images/user194.jpg'),
('images/user195.jpg'),
('images/user196.jpg'),
('images/user197.jpg'),
('images/user198.jpg'),
('images/user199.jpg'),
('images/user200.jpg'),
('images/hub1.jpg'),
('images/hub2.jpg'),
('images/hub3.jpg'),
('images/hub4.jpg'),
('images/hub5.jpg'),
('images/hub6.jpg'),
('images/hub7.jpg'),
('images/hub8.jpg'),
('images/hub9.jpg'),
('images/hub10.jpg'),
('images/hub11.jpg'),
('images/hub12.jpg'),
('images/hub13.jpg'),
('images/hub14.jpg'),
('images/hub15.jpg'),
('images/hub16.jpg'),
('images/hub17.jpg'),
('images/hub18.jpg'),
('images/hub19.jpg'),
('images/hub20.jpg'),
('images/hub21.jpg'),
('images/hub22.jpg'),
('images/hub23.jpg'),
('images/hub24.jpg'),
('images/hub25.jpg'),
('images/hub26.jpg'),
('images/hub27.jpg'),
('images/hub28.jpg'),
('images/hub29.jpg'),
('images/hub30.jpg'),
('images/hub31.jpg'),
('images/hub32.jpg'),
('images/hub33.jpg'),
('images/hub34.jpg'),
('images/hub35.jpg'),
('images/hub36.jpg'),
('images/hub37.jpg'),
('images/hub38.jpg'),
('images/hub39.jpg'),
('images/hub40.jpg'),
('images/hub41.jpg'),
('images/hub42.jpg'),
('images/hub43.jpg'),
('images/hub44.jpg'),
('images/hub45.jpg'),
('images/hub46.jpg'),
('images/hub47.jpg'),
('images/hub48.jpg'),
('images/hub49.jpg'),
('images/hub50.jpg'),
('images/hub51.jpg'),
('images/hub52.jpg'),
('images/hub53.jpg'),
('images/hub54.jpg'),
('images/hub55.jpg'),
('images/hub56.jpg'),
('images/hub57.jpg'),
('images/hub58.jpg'),
('images/hub59.jpg'),
('images/hub60.jpg'),
('images/hub61.jpg'),
('images/hub62.jpg'),
('images/hub63.jpg'),
('images/hub64.jpg'),
('images/hub65.jpg'),
('images/hub66.jpg'),
('images/hub67.jpg'),
('images/hub68.jpg'),
('images/hub69.jpg'),
('images/hub70.jpg'),
('images/hub71.jpg'),
('images/hub72.jpg'),
('images/hub73.jpg'),
('images/hub74.jpg'),
('images/hub75.jpg'),
('images/hub76.jpg'),
('images/hub77.jpg'),
('images/hub78.jpg'),
('images/hub79.jpg'),
('images/hub80.jpg'),
('images/hub81.jpg'),
('images/hub82.jpg'),
('images/hub83.jpg'),
('images/hub84.jpg'),
('images/hub85.jpg'),
('images/hub86.jpg'),
('images/hub87.jpg'),
('images/hub88.jpg'),
('images/hub89.jpg'),
('images/hub90.jpg'),
('images/hub91.jpg'),
('images/hub92.jpg'),
('images/hub93.jpg'),
('images/hub94.jpg'),
('images/hub95.jpg'),
('images/hub96.jpg'),
('images/hub97.jpg'),
('images/hub98.jpg'),
('images/hub99.jpg'),
('images/hub100.jpg'),
('images/hub101.jpg'),
('images/hub102.jpg'),
('images/hub103.jpg'),
('images/hub104.jpg'),
('images/hub105.jpg'),
('images/hub106.jpg'),
('images/hub107.jpg'),
('images/hub108.jpg'),
('images/hub109.jpg'),
('images/hub110.jpg'),
('images/hub111.jpg'),
('images/hub112.jpg'),
('images/hub113.jpg'),
('images/hub114.jpg'),
('images/hub115.jpg'),
('images/hub116.jpg'),
('images/hub117.jpg'),
('images/hub118.jpg'),
('images/hub119.jpg'),
('images/hub120.jpg'),
('images/hub121.jpg'),
('images/hub122.jpg'),
('images/hub123.jpg'),
('images/hub124.jpg'),
('images/hub125.jpg'),
('images/hub126.jpg'),
('images/hub127.jpg'),
('images/hub128.jpg'),
('images/hub129.jpg'),
('images/hub130.jpg'),
('images/hub131.jpg'),
('images/hub132.jpg'),
('images/hub133.jpg'),
('images/hub134.jpg'),
('images/hub135.jpg'),
('images/hub136.jpg'),
('images/hub137.jpg'),
('images/hub138.jpg'),
('images/hub139.jpg'),
('images/hub140.jpg'),
('images/hub141.jpg'),
('images/hub142.jpg'),
('images/hub143.jpg'),
('images/hub144.jpg'),
('images/hub145.jpg'),
('images/hub146.jpg'),
('images/hub147.jpg'),
('images/hub148.jpg'),
('images/hub149.jpg'),
('images/hub150.jpg'),
('images/hub151.jpg'),
('images/hub152.jpg'),
('images/hub153.jpg'),
('images/hub154.jpg'),
('images/hub155.jpg'),
('images/hub156.jpg'),
('images/hub157.jpg'),
('images/hub158.jpg'),
('images/hub159.jpg'),
('images/hub160.jpg'),
('images/hub161.jpg'),
('images/hub162.jpg'),
('images/hub163.jpg'),
('images/hub164.jpg'),
('images/hub165.jpg'),
('images/hub166.jpg'),
('images/hub167.jpg'),
('images/hub168.jpg'),
('images/hub169.jpg'),
('images/hub170.jpg'),
('images/hub171.jpg'),
('images/hub172.jpg'),
('images/hub173.jpg'),
('images/hub174.jpg'),
('images/hub175.jpg'),
('images/hub176.jpg'),
('images/hub177.jpg'),
('images/hub178.jpg'),
('images/hub179.jpg'),
('images/hub180.jpg'),
('images/hub181.jpg'),
('images/hub182.jpg'),
('images/hub183.jpg'),
('images/hub184.jpg'),
('images/hub185.jpg'),
('images/hub186.jpg'),
('images/hub187.jpg'),
('images/hub188.jpg'),
('images/hub189.jpg'),
('images/hub190.jpg'),
('images/hub191.jpg'),
('images/hub192.jpg'),
('images/hub193.jpg'),
('images/hub194.jpg'),
('images/hub195.jpg'),
('images/hub196.jpg'),
('images/hub197.jpg'),
('images/hub198.jpg'),
('images/hub199.jpg'),
('images/hub200.jpg');


-- Step 2: Insert Users into authenticateduser
INSERT INTO authenticated_users (name, username, email, password, birth_date, description, is_admin, image_id)
VALUES
('Anonymous', 'Anonymous', 'anonymous@example.com', '$2y$10$FLtQvBMa8TZpNeHMG1EnTu8QbbEZe8e2GJbzqfSdnQyht4ozH1zRa', '1000-01-01', 'Anonymous.', FALSE, 1),
('Bob Johnson', 'bob', 'bob@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '1988-02-02', 'Loves to share news.', FALSE, 2),
('Charlie Brown', 'charlie', 'charlie@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '1992-03-03', 'Tech enthusiast.', FALSE, 3),
('Diana Prince', 'diana', 'diana@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '1985-04-04', 'Avid reader and commenter.',TRUE, 4),
('Edward Elric', 'edward', 'edward@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '1994-05-05', 'Anime and manga lover.', FALSE, 5),
('Fiona Gallagher', 'fiona', 'fiona@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '1991-06-06', 'Loves traveling and photography.', FALSE, 6),
('George Martin', 'george', 'george@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '1975-07-07', 'Fantasy writer and fan.', FALSE, 7),
('Hannah Montana', 'hannah', 'hannah@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '1998-08-08', 'Pop culture enthusiast.', FALSE, 8),
('Ian Malcolm', 'ian', 'ian@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '1980-09-09', 'Dinosaur expert and scientist.', FALSE, 9),
('Jack Sparrow', 'jack', 'jack@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '1980-10-10', 'Pirate captain and adventurer.', FALSE, 10),
('Katherine Pierce', 'katherine', 'katherine@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '1993-11-11', 'Mystery novel lover.', FALSE, 11),
('Liam Neeson', 'liam', 'liam@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '1982-12-12', 'Film and theater enthusiast.', FALSE, 12),
('Monica Geller', 'monica', 'monica@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '1980-01-13', 'Chef and cleanliness freak.', FALSE, 13),
('Nina Williams', 'nina', 'nina@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '1979-02-14', 'Martial artist and game developer.', FALSE, 14),
('Oscar Wilde', 'oscar', 'oscar@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '1854-03-15', 'Famous playwright and poet.', FALSE, 15),
('Penny Lane', 'penny', 'penny@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '1992-04-16', 'Music lover and singer.', FALSE, 16),
('Quentin Tarantino', 'quentin', 'quentin@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '1963-05-17', 'Director and screenwriter.', FALSE, 17),
('Rachel Green', 'rachel', 'rachel@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '1988-06-18', 'Fashion enthusiast.', FALSE, 18),
('Steve Rogers', 'steve', 'steve@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '1922-07-19', 'Super soldier and leader.', FALSE, 19),
('Tony Stark', 'tony', 'tony@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '1970-08-20', 'Inventor and philanthropist.', FALSE, 20),
('Ursula K. Le Guin', 'ursula', 'ursula@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '1929-09-21', 'Renowned fantasy author.', FALSE, 21),
('Victor Frankenstein', 'victor', 'victor@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '1794-10-22', 'Scientist and creator.', FALSE, 22),
('Will Turner', 'will', 'will@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '1985-11-23', 'Blacksmith and pirate.', FALSE, 23),
('Xena Warrior', 'xena', 'xena@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '1985-12-24', 'Warrior princess and leader.', FALSE, 24),
('Yoda', 'yoda', 'yoda@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '896-01-25', 'Jedi Master and wise mentor.', FALSE, 25),
('Zorro', 'zorro', 'zorro@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '1840-02-26', 'Masked hero and protector.', FALSE, 26),
('Albus Dumbledore', 'albus', 'albus@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '1881-03-27', 'Headmaster of Hogwarts.', FALSE, 27),
('Bella Swan', 'bella', 'bella@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '1987-04-28', 'Vampire and werewolf enthusiast.', FALSE, 28),
('Clark Kent', 'clark', 'clark@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '1985-05-29', 'Journalist and superhero.', FALSE, 29),
('Darth Vader', 'darth', 'darth@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '1941-06-30', 'Sith Lord and father figure.', FALSE, 30),
('Elliot Alderson', 'elliot', 'elliot@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '1992-07-31', 'Cybersecurity engineer.', FALSE, 31),
('Frodo Baggins', 'frodo', 'frodo@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '1968-08-01', 'Ring bearer and adventurer.', FALSE, 32),
('Gandalf the Grey', 'gandalf', 'gandalf@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '1000-09-02', 'Wielder of magic and wisdom.', FALSE, 33),
('Homer Simpson', 'homer', 'homer@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '1956-10-03', 'Loves donuts and family.', FALSE, 34),
('Icarus', 'icarus', 'icarus@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '2000-11-04', 'Aspiring inventor and dreamer.', FALSE, 35),
('Jules Winnfield', 'jules', 'jules@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '1971-12-05', 'Professional hitman with a passion.', FALSE, 36),
('Katniss Everdeen', 'katniss', 'katniss@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '1990-01-06', 'Revolutionary and survivor.', FALSE, 37),
('Lara Croft', 'lara', 'lara@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '1975-02-07', 'Adventurer and archaeologist.', FALSE, 38),
('Marty McFly', 'marty', 'marty@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '1968-03-08', 'Time traveler and teenager.', FALSE, 39),
('Nancy Drew', 'nancy', 'nancy@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '1980-04-09', 'Famous detective and sleuth.', FALSE, 40),
('Oliver Twist', 'oliver', 'oliver@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '1837-05-10', 'Orphan and survivor.', FALSE, 41),
('Pikachu', 'pikachu', 'pikachu@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '1996-06-11', 'Electric mouse and companion.', TRUE, 42),
('Quasimodo', 'quasimodo', 'quasimodo@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '1460-07-12', 'Bell-ringer and misunderstood.', FALSE, 43),
('R2-D2', 'r2d2', 'r2d2@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '1932-08-13', 'Astromech droid and hero.', FALSE, 44),
('SpongeBob SquarePants', 'spongebob', 'spongebob@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '1986-09-14', 'Underwater fry cook and optimist.', FALSE, 45),
('Thor Odinson', 'thor', 'thor@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '1965-10-15', 'God of thunder and hero.', FALSE, 46),
('Ultron', 'ultron', 'ultron@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '2015-11-16', 'A.I. villain with a complex.', FALSE, 47),
('Violet Parr', 'violet', 'violet@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '2000-12-17', 'Superhero with force fields.', FALSE, 48),
('Wolverine', 'wolverine', 'wolverine@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '1882-01-18', 'Mutant and fighter.', FALSE, 49),
('X-Men', 'xmen', 'xmen@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', '1963-02-19', 'Superhero team with various powers.', FALSE, 50);

-- Step 3: Insert Communities
INSERT INTO communities (name, description, privacy, image_id) VALUES
('Tech community', 'A place for tech enthusiasts to share knowledge.', FALSE, 201),
('Book Lovers', 'A community for book lovers.', TRUE, 202),
('Anime Fans', 'Discuss your favorite anime and manga.', FALSE, 203),
('Travel Enthusiasts', 'Share your travel stories and tips.', TRUE, 204),
('Fantasy Writers', 'A place for aspiring fantasy authors.', FALSE, 205),
('Culinary Artists', 'Share recipes and cooking tips.', TRUE, 206),
('Movie Buffs', 'Discuss films and series.', FALSE, 207),
('Fitness Fanatics', 'Share fitness tips and motivation.', TRUE, 208),
('Game Developers', 'A community for game creation discussions.', FALSE, 209),
('Nature Lovers', 'Share your nature photography and stories.', TRUE, 210),
('Music Producers', 'A community for aspiring and professional music producers.', FALSE, 211),
('History Buffs', 'Explore and discuss historical events and figures.', TRUE, 212),
('Sports Fans', 'A place to talk about your favorite sports and teams.', FALSE, 213),
('Pet Lovers', 'Share tips and stories about pets.', TRUE, 214),
('Science Geeks', 'Discuss recent scientific discoveries and theories.', FALSE, 215),
('Art Enthusiasts', 'Share and critique artwork.', TRUE, 216),
('DIY Creators', 'Tips and tricks for do-it-yourself projects.', FALSE, 217),
('Environment Advocates', 'Discussions on environmental conservation and activism.', TRUE, 218),
('Coding Wizards', 'A space for developers to share coding tips.', FALSE, 219),
('Health Gurus', 'Tips for leading a healthy lifestyle.', TRUE, 220),
('Car Enthusiasts', 'Share knowledge and stories about cars.', FALSE, 221),
('Photography Experts', 'A community to discuss photography tips and gear.', TRUE, 222),
('Board Game Lovers', 'Discuss strategies and reviews of board games.', FALSE, 223),
('Startup Founders', 'A place for entrepreneurs to share insights.', TRUE, 224),
('Comedy Enthusiasts', 'Share jokes and discuss stand-up acts.', FALSE, 225),
('Space Enthusiasts', 'Discuss astronomy and space exploration.', TRUE, 226),
('Language Learners Hub', 'Share tips and resources for learning new languages.', FALSE, 227),
('Mental Health Community', 'Support and resources for mental health awareness.', TRUE, 228),
('Fashion Innovators', 'A community to discuss fashion trends and designs.', FALSE, 229),
('Tech Reviewers', 'Discuss the latest gadgets and technologies.', TRUE, 230),
('Urban Gardeners Club', 'Tips for gardening in urban spaces.', FALSE, 231),
('Philosophy Minds', 'Discuss philosophical ideas and theories.', TRUE, 232),
('Volunteer Network', 'A place for volunteers to connect and share experiences.', FALSE, 233),
('Hiking Enthusiasts', 'Share hiking trails and experiences.', TRUE, 234),
('E-Sports Fans', 'Discuss e-sports teams and tournaments.', FALSE, 235),
('Creative Writers Network', 'Share and critique creative writing pieces.', TRUE, 236),
('Meditation Circle', 'Discuss meditation techniques and benefits.', FALSE, 237),
('Classic Car Enthusiasts', 'Share and discuss classic car collections.', TRUE, 238),
('Cryptocurrency Experts', 'Discuss trends and tips in cryptocurrency.', FALSE, 239),
('Parenting Community', 'Share advice and stories about parenting.', TRUE, 240),
('Martial Arts Practitioners', 'Discuss techniques and training.', FALSE, 241),
('Remote Work Hub', 'Tips and support for working remotely.', TRUE, 242),
('Guitar Players', 'A community for guitar players of all levels.', FALSE, 243),
('Chess Enthusiasts', 'Discuss strategies and famous matches.', TRUE, 244),
('Wildlife Advocates', 'Discuss efforts to protect wildlife.', FALSE, 245),
('Cycling Fans', 'Share cycling routes and tips.', TRUE, 246),
('Interior Designers', 'Discuss home decoration and design ideas.', FALSE, 247),
('Economics Enthusiasts', 'Share resources and discuss economic theories.', TRUE, 248),
('Film Makers Guild', 'A space for filmmakers to share tips and ideas.', FALSE, 249),
('Astronomy Lovers', 'Discuss celestial events and phenomena.', TRUE, 250),
('Yoga Community', 'Share yoga techniques and benefits.', FALSE, 251),
('Comic Book Enthusiasts', 'Discuss your favorite comics and graphic novels.', TRUE, 252),
('Social Justice Warriors', 'Discuss social issues and activism.', FALSE, 253),
('Gardening Beginners', 'Tips and support for starting a garden.', TRUE, 254),
('Piano Enthusiasts', 'A community for pianists to share tips.', FALSE, 255),
('Vintage Collectors', 'Share your vintage collections and stories.', TRUE, 256),
('Outdoor Adventurers', 'Discuss camping and outdoor activities.', FALSE, 257),
('Political Enthusiasts', 'Discuss current political events.', TRUE, 258),
('Baking Enthusiasts', 'Share baking tips and recipes.', FALSE, 259),
('Homebrewing Experts', 'Tips and tricks for brewing your own drinks.', TRUE, 260),
('AI Innovators', 'Discuss advancements in artificial intelligence.', FALSE, 261),
('Surfing Enthusiasts', 'Share surfing tips and experiences.', TRUE, 262),
('Classic Movie Fans', 'Discuss your favorite classic films.', FALSE, 263),
('Cosplay Artists', 'Share tips and showcase your cosplay.', TRUE, 264),
('Adventure Travelers', 'Discuss extreme travel experiences.', TRUE, 266),
('Science Fiction Enthusiasts', 'Discuss sci-fi books and movies.', FALSE, 267),
('Pet Trainers Group', 'Share pet training tips and techniques.', TRUE, 268),
('Mountain Climbers', 'Share experiences and tips for climbing.', FALSE, 269),
('Beach Enthusiasts', 'Discuss your favorite beach destinations.', TRUE, 270),
('K-Pop Community', 'Discuss your favorite K-Pop groups and music.', FALSE, 271),
('Digital Art Hub', 'Share digital art and techniques.', TRUE, 272),
('Entrepreneurs Network', 'Discuss ideas and challenges for startups.', FALSE, 273),
('Board Gamers Circle', 'Discuss your favorite board games and strategies.', TRUE, 274),
('Cat Lovers Club', 'Share stories and tips about cats.', FALSE, 275),
('Movie Directors', 'Discuss filmmaking and production tips.', TRUE, 276),
('Skiing Enthusiasts', 'Share skiing tips and destinations.', FALSE, 277),
('Drone Hobbyists', 'Discuss drone flying tips and experiences.', TRUE, 278),
('Vegan Community', 'Share vegan recipes and tips.', FALSE, 279),
('Graphic Designers', 'Discuss design tools and techniques.', TRUE, 280),
('Bird Watching Enthusiasts', 'Share birdwatching tips and sightings.', FALSE, 281),
('Sculptors Network', 'Discuss sculpting techniques and share work.', TRUE, 282),
('Fiction Writers Hub', 'Share your fictional works and critique.', FALSE, 283),
('Sneaker Collectors Group', 'Discuss your sneaker collections.', TRUE, 284),
('Robotics Enthusiasts', 'Discuss and share robotics projects.', FALSE, 291),
('Fishing Fans', 'Tips and stories about fishing experiences.', TRUE, 292),
('Drone Experts', 'A community for drone hobbyists.', FALSE, 293),
('Aquarium Enthusiasts', 'Share tips and setups for aquariums.', TRUE, 294),
('Horror Movie Fans', 'Discuss your favorite horror films.', FALSE, 295),
('Landscape Photographers', 'Share and discuss landscape photography.', TRUE, 296),
('Tattoo Artists', 'Discuss techniques and showcase tattoos.', FALSE, 297),
('Mobile Gamers Hub', 'Discuss mobile games and tips.', TRUE, 298),
('Home Improvement Gurus', 'Tips for improving your home.', FALSE, 299),
('Virtual Reality Fans', 'Discuss VR games and technology.', TRUE, 300),
('Ask Me Anything', 'A space for unique Q&A sessions.', TRUE, 301),
('Life Pro Tips', 'Tips for improving everyday life.', FALSE, 302),
('Explain Like I’m Five', 'Complex ideas explained simply.', TRUE, 303),
('Today I Learned', 'Share interesting facts you learned.', FALSE, 304),
('Shower Thoughts', 'Unique and quirky thoughts.', TRUE, 305),
('Wholesome Memes', 'Feel-good memes for everyone.', FALSE, 306),
('DIY Projects', 'Creative do-it-yourself ideas.', TRUE, 307),
('Productivity Hacks', 'Tips for getting things done efficiently.', FALSE, 308),
('Space Exploration', 'Discussions about the universe.', TRUE, 309),
('World News', 'Stay updated on global news.', FALSE, 310),
('Food Porn', 'Delicious food photography.', TRUE, 311),
('Art Critique', 'Get constructive feedback on your art.', FALSE, 312),
('Ask a Historian', 'Historical questions answered by experts.', TRUE, 313),
('Casual Conversations', 'Relaxed discussions on any topic.', FALSE, 314),
('Gaming Memes', 'Funny content for gamers.', TRUE, 315),
('Minimalism', 'Discuss simple and clutter-free living.', FALSE, 316),
('Sustainable Living', 'Tips for eco-friendly living.', TRUE, 317),
('Data Is Beautiful', 'Visualizations of interesting data.', FALSE, 318),
('Personal Finance', 'Advice for managing your money.', TRUE, 319),
('Learn Programming', 'Resources and tips for coders.', FALSE, 320),
('Astronomy', 'Explore the stars and galaxies.', TRUE, 321),
('Casual Photography', 'Share and discuss everyday photos.', FALSE, 322),
('Parenting Tips', 'Support and advice for parents.', TRUE, 323),
('Fitness Progress', 'Share fitness achievements.', FALSE, 324),
('Political Humor', 'Jokes and memes about politics.', TRUE, 325),
('Startup Ideas', 'Discuss innovative business concepts.', FALSE, 326),
('Language Exchange', 'Practice new languages with others.', TRUE, 327),
('Investing 101', 'Advice for new investors.', FALSE, 328),
('Cryptocurrency News', 'Stay updated on crypto trends.', TRUE, 329),
('Creative Writing Prompts', 'Ideas to inspire your writing.', FALSE, 330),
('Meme Economy', 'Buy, sell, and trade memes.', TRUE, 331),
('Crafting Enthusiasts', 'Discuss crafts and DIY projects.', FALSE, 332),
('Futurology', 'Discuss the future of humanity.', TRUE, 333),
('Hairstyling Tips', 'Advice and tutorials for great hair.', FALSE, 334),
('Urban Exploration', 'Explore abandoned or hidden places.', TRUE, 335),
('Pet Care', 'Tips for taking care of your pets.', FALSE, 336),
('Wild Camping', 'Share camping experiences and tips.', TRUE, 337),
('Guitar Tutorials', 'Lessons for guitar players.', FALSE, 338),
('World Building', 'Create and discuss fictional worlds.', TRUE, 339),
('Rare Puppers', 'Share adorable dog photos.', FALSE, 340),
('Tech Support', 'Help for solving tech problems.', TRUE, 341),
('Bad Jokes', 'So bad they’re funny.', FALSE, 342),
('Hiking Trails', 'Discover and share hiking locations.', TRUE, 343),
('Urban Legends', 'Discuss myths and legends.', FALSE, 344),
('Photography Challenges', 'Participate in themed photo contests.', TRUE, 345),
('Tech Gadgets', 'Reviews and discussions on gadgets.', FALSE, 346),
('Movie Reviews', 'Share your thoughts on films.', TRUE, 347),
('Classic Literature', 'Discuss timeless books.', FALSE, 348),
('Geography Nerds', 'Share and discuss maps.', TRUE, 349),
('Skincare Tips', 'Discuss routines and products.', FALSE, 350),
('Board Game Designers', 'Create and test board games.', TRUE, 351),
('Astronomy Events', 'Updates on celestial happenings.', FALSE, 352),
('Mental Health Tips', 'Share resources and advice.', TRUE, 353),
('Seasonal Recipes', 'Cook seasonal and festive dishes.', FALSE, 354),
('Extreme Weather Fans', 'Share storm and weather stories.', TRUE, 355),
('Bird Photography', 'Share stunning bird photos.', FALSE, 356),
('Handwriting Help', 'Improve your handwriting skills.', TRUE, 357),
('Riddle Lovers', 'Solve and share riddles.', FALSE, 358),
('Plant Identification', 'Help identify mysterious plants.', TRUE, 359),
('Home Automation', 'Discuss smart home tech.', FALSE, 360),
('Alien Theories', 'Speculate about extraterrestrial life.', TRUE, 361),
('Philosophy 101', 'Discuss beginner philosophical ideas.', FALSE, 362),
('Wilderness Survival', 'Share survival tips and gear.', TRUE, 363),
('Car Maintenance Tips', 'DIY tips for car upkeep.', FALSE, 364),
('Knitting & Crochet', 'Share patterns and projects.', TRUE, 365),
('Vintage Electronics', 'Discuss retro gadgets.', FALSE, 366),
('Minimalist Design', 'Tips for simple and effective design.', TRUE, 367),
('Home Renovations', 'Tips for improving your house.', FALSE, 368),
('Stand-up Comedy', 'Share jokes and funny clips.', TRUE, 369),
('Trail Runners', 'Discuss trail running gear and tips.', FALSE, 370),
('Martial Arts Theory', 'Debate techniques and philosophy.', TRUE, 371),
('Cooking Experiments', 'Share your culinary trials.', FALSE, 372),
('K-Pop Dance Covers', 'Discuss and share dance covers.', TRUE, 373),
('Sustainable Fashion', 'Talk about eco-friendly clothing.', FALSE, 374),
('Classic Music Enthusiasts', 'Discuss classical music works.', TRUE, 375),
('Origami Artists', 'Share folds and designs.', FALSE, 376),
('Sketch Artists', 'Discuss tools and techniques.', TRUE, 377),
('Comedy Sketches', 'Share and create humorous sketches.', FALSE, 378),
('AI Generated Art', 'Discuss and share AI art.', TRUE, 379),
('Book Club Picks', 'Vote on and discuss books.', FALSE, 380),
('Coding Challenges', 'Sharpen your programming skills.', TRUE, 381),
('Archaeology Fans', 'Discuss ancient discoveries.', FALSE, 382),
('Chess Tournaments', 'Share games and results.', TRUE, 383),
('Landscape Painting', 'Showcase and discuss techniques.', FALSE, 384),
('Fantasy Maps', 'Create and critique fictional maps.', TRUE, 385),
('Roller Coaster Fans', 'Discuss and share thrill rides.', FALSE, 386),
('Beach Photography', 'Share stunning beach shots.', TRUE, 387),
('Meditation Techniques', 'Explore and share practices.', FALSE, 388),
('Quilting Enthusiasts', 'Discuss patterns and fabrics.', TRUE, 389),
('Stock Market Insights', 'Analyze and discuss stock trends.', FALSE, 390),
('Magic Tricks', 'Learn and share magic tips.', TRUE, 391),
('Food Science', 'Explore the science of cooking.', FALSE, 392),
('Space Missions', 'Discuss recent space exploration.', TRUE, 393),
('Jazz Music Fans', 'Share your favorite jazz pieces.', FALSE, 394),
('Aquascaping', 'Create stunning underwater worlds.', TRUE, 395),
('Minimal Wardrobe', 'Tips for simplifying clothing.', FALSE, 396),
('Candle Making', 'Discuss techniques and designs.', TRUE, 397),
('Speedrunning', 'Discuss and share gaming records.', FALSE, 398),
('3D Printing', 'Share tips and designs.', TRUE, 399),
('Graphic Novel Fans', 'Discuss and share favorite comics.', FALSE, 400);

-- Step 4: Insert posts
INSERT INTO posts (title, content, community_id) VALUES
('The Rise of AI', 'Artificial Intelligence is revolutionizing the world.', 1),
('Must-Read Books of 2024', 'Here are some books you shouldn’t miss this year.', 2),
('Top Anime of the Season', 'Let’s discuss the best anime airing this season.', 3),
('Travel Tips for 2024', 'Best destinations to visit next year.', 4),
('Building a Fantasy World', 'Tips for world-building in fantasy fiction.', 5),
('Cooking Healthy Meals', 'Share your best healthy recipes.', 6),
('Upcoming Movies in 2024', 'What films are you excited about?', 7),
('Staying Fit During Winter', 'Winter workouts to keep you healthy.', 8),
('Game Development Basics', 'How to get started with game development.', 9),
('Wildlife Photography Tips', 'Capture nature beautifully.', 10),
('AI in Everyday Life', 'How AI is simplifying our daily routines.', 1),
('Underrated Books to Check Out', 'Hidden literary gems you might have missed.', 2),
('Classic Anime That Still Shine', 'Timeless anime worth revisiting.', 3),
('Budget-Friendly Travel Hacks', 'Explore the world without breaking the bank.', 4),
('Creating Believable Characters', 'Bring your fantasy worlds to life with relatable characters.', 5),
('Quick and Healthy Breakfast Ideas', 'Start your day with these simple, nutritious recipes.', 6),
('Anticipated Sequels of 2024', 'Movie sequels everyone is looking forward to.', 7),
('Staying Motivated to Exercise in the Cold', 'Tips to beat winter laziness and stay active.', 8),
('The Best Tools for Aspiring Game Developers', 'Essential software and resources for beginners.', 9),
('Photographing Wildlife in Challenging Conditions', 'How to capture stunning shots in tough environments.', 10);


-- Step 5: Insert comments
INSERT INTO comments (content, creation_date, authenticated_user_id, post_id) VALUES
('Great insights! I totally agree with you.', CURRENT_TIMESTAMP, 1, 1),
('I can’t wait to read these books!', CURRENT_TIMESTAMP, 2, 2),
('Anime has really evolved over the years.', CURRENT_TIMESTAMP, 3, 3),
('Looking forward to discussing more on this!', CURRENT_TIMESTAMP, 1, 3),
('Awesome travel tips!', CURRENT_TIMESTAMP, 4, 4),
('Delicious! I want to try this recipe.', CURRENT_TIMESTAMP, 5, 5),
('So many good movies coming up!', CURRENT_TIMESTAMP, 2, 6),
('Let’s keep each other motivated!', CURRENT_TIMESTAMP, 3, 7),
('Game dev is so much fun!', CURRENT_TIMESTAMP, 4, 8),
('Nature is so beautiful!', CURRENT_TIMESTAMP, 5, 9);

-- Child Comment 1
INSERT INTO comments (content, creation_date, authenticated_user_id, post_id, parent_comment_id)
VALUES ('I agree with your point about sustainable practices!', CURRENT_TIMESTAMP, 2, 1, 1);

-- Child Comment 2
INSERT INTO comments (content, creation_date, authenticated_user_id, post_id, parent_comment_id)
VALUES ('This is a great discussion, I have some thoughts too.', CURRENT_TIMESTAMP, 3, 1, 1);

-- Child Comment 3 (nested reply to Child Comment 1)
INSERT INTO comments (content, creation_date, authenticated_user_id, post_id, parent_comment_id)
VALUES ('I think we need more focus on local community efforts.', CURRENT_TIMESTAMP, 4, 1, 11);

INSERT INTO authors (authenticated_user_id, post_id, pinned) VALUES
(1, 1, FALSE),  -- Anonymous - The Rise of AI
(2, 2, FALSE),  -- Bob Johnson - Must-Read Books of 2024
(3, 3, FALSE),  -- Charlie Brown - Top Anime of the Season
(4, 4, FALSE),  -- Diana Prince - Travel Tips for 2024
(5, 5, FALSE),  -- Edward Elric - Building a Fantasy World
(6, 6, FALSE),  -- Fiona Gallagher - Cooking Healthy Meals
(7, 7, FALSE),  -- George Martin - Upcoming Movies in 2024
(8, 8, FALSE),  -- Hannah Montana - Staying Fit During Winter
(9, 9, FALSE),  -- Ian Malcolm - Game Development Basics
(10, 10, FALSE), -- Jack Sparrow - Wildlife Photography Tips
(11, 1, FALSE),  -- Katherine Pierce - The Rise of AI
(12, 2, FALSE),  -- Liam Neeson - Must-Read Books of 2024
(13, 3, FALSE),  -- Monica Geller - Top Anime of the Season
(14, 4, FALSE),  -- Nina Williams - Travel Tips for 2024
(15, 5, FALSE),  -- Oscar Wilde - Building a Fantasy World
(16, 6, FALSE),  -- Penny Lane - Cooking Healthy Meals
(17, 7, FALSE),  -- Quentin Tarantino - Upcoming Movies in 2024
(18, 8, FALSE),  -- Rachel Green - Staying Fit During Winter
(19, 9, FALSE),  -- Steve Rogers - Game Development Basics
(20, 10, FALSE),  -- Tony Stark - Wildlife Photography Tips
(21, 1, FALSE),  -- Ursula K. Le Guin - The Rise of AI
(22, 2, FALSE),  -- Victor Frankenstein - Must-Read Books of 2024
(23, 3, FALSE),  -- Will Turner - Top Anime of the Season
(24, 4, FALSE),  -- Xena Warrior - Travel Tips for 2024
(25, 5, FALSE),  -- Yoda - Building a Fantasy World
(26, 6, FALSE),  -- Zorro - Cooking Healthy Meals
(27, 7, FALSE),  -- Albus Dumbledore - Upcoming Movies in 2024
(28, 8, FALSE),  -- Bella Swan - Staying Fit During Winter
(29, 9, FALSE),  -- Clark Kent - Game Development Basics
(30, 10, FALSE),  -- Darth Vader - Wildlife Photography Tips
(31, 1, FALSE),  -- Elliot Alderson - The Rise of AI
(32, 2, FALSE),  -- Frodo Baggins - Must-Read Books of 2024
(33, 3, FALSE),  -- Gandalf the Grey - Top Anime of the Season
(34, 4, FALSE),  -- Homer Simpson - Travel Tips for 2024
(35, 5, FALSE),  -- Icarus - Building a Fantasy World
(36, 6, FALSE),  -- Jules Winnfield - Cooking Healthy Meals
(37, 7, FALSE),  -- Katniss Everdeen - Upcoming Movies in 2024
(38, 8, FALSE),  -- Lara Croft - Staying Fit During Winter
(39, 9, FALSE),  -- Marty McFly - Game Development Basics
(40, 10, FALSE),  -- Nancy Drew - Wildlife Photography Tips
(41, 1, FALSE),  -- Oliver Twist - The Rise of AI
(42, 2, TRUE),   -- Pikachu - Must-Read Books of 2024 (Pinned)
(43, 3, FALSE),  -- Quasimodo - Top Anime of the Season
(44, 4, FALSE),  -- R2-D2 - Travel Tips for 2024
(45, 5, FALSE),  -- SpongeBob SquarePants - Building a Fantasy World
(46, 6, FALSE),  -- Thor Odinson - Cooking Healthy Meals
(47, 7, FALSE),  -- Ultron - Upcoming Movies in 2024
(48, 8, FALSE),  -- Violet Parr - Staying Fit During Winter
(49, 9, FALSE),  -- Wolverine - Game Development Basics
(50, 10, FALSE); -- X-Men - Wildlife Photography Tips

-- Step 6: Insert votes
-- Each user votes for some posts and comments
INSERT INTO votes (upvote, authenticated_user_id) VALUES(TRUE, 1), 
(FALSE, 2), 
(TRUE, 3),
(TRUE, 4),
(FALSE, 5),
(TRUE, 6),
(FALSE, 7),
(TRUE, 8),
(FALSE, 9),
(TRUE, 10),
(TRUE, 11),
(FALSE, 12),
(TRUE, 13),
(FALSE, 14),
(TRUE, 15),
(FALSE, 16),
(TRUE, 17),
(FALSE, 18),
(TRUE, 19),
(FALSE, 20),
(TRUE, 21),
(FALSE, 22),
(TRUE, 23),
(FALSE, 24),
(TRUE, 25),
(FALSE, 26),
(TRUE, 27),
(FALSE, 28),
(TRUE, 29),
(FALSE, 30),
(TRUE, 31),
(FALSE, 32),
(TRUE, 33),
(FALSE, 34),
(TRUE, 35),
(FALSE, 36),
(TRUE, 37),
(FALSE, 38),
(TRUE, 39),
(FALSE, 40),
(TRUE, 41),
(FALSE, 42),
(TRUE, 43),
(FALSE, 44),
(TRUE, 45),
(FALSE, 46),
(TRUE, 47),
(FALSE, 48),
(TRUE, 49),
(FALSE, 50);

-- Step 7: Link votes to posts
INSERT INTO post_votes (vote_id, post_id) VALUES(1, 1),
(2, 2),
(3, 3),
(4, 4),
(5, 5),
(6, 1),
(7, 2),
(8, 3),
(9, 4),
(10, 5),
(11, 1),
(12, 2),
(13, 3),
(14, 4),
(15, 5),
(16, 1),
(17, 2),
(18, 3),
(19, 4),
(20, 5),
(21, 1),
(22, 2),
(23, 3),
(24, 4),
(25, 5),
(26, 1),
(27, 2),
(28, 3),
(29, 4),
(30, 5),
(31, 1),
(32, 2),
(33, 3),
(34, 4),
(35, 5),
(36, 1),
(37, 2),
(38, 3),
(39, 4),
(40, 5),
(41, 1),
(42, 2),
(43, 3),
(44, 4),
(45, 5),
(46, 1),
(47, 2),
(48, 3),
(49, 4),
(50, 5);

-- Step 8: Insert comments votes
INSERT INTO comment_votes (vote_id, comment_id) VALUES(1, 1),
(2, 2),
(3, 3),
(4, 4),
(5, 5);

-- Step 9: Insert topics
INSERT INTO topics (post_id, review_date, status) VALUES(1, CURRENT_TIMESTAMP, 'pending'),
(11, CURRENT_TIMESTAMP, 'accepted'),
(12, CURRENT_TIMESTAMP, 'rejected'),
(13, CURRENT_TIMESTAMP, 'pending'),
(14, CURRENT_TIMESTAMP, 'accepted'),
(15, CURRENT_TIMESTAMP, 'pending'),
(16, CURRENT_TIMESTAMP, 'accepted'),
(17, CURRENT_TIMESTAMP, 'pending'),
(18, CURRENT_TIMESTAMP, 'rejected'),
(19, CURRENT_TIMESTAMP, 'accepted');

-- Step 10: Insert news
INSERT INTO news (post_id, news_url) VALUES(1, 'http://example.com/news1'),
(2, 'http://example.com/news2'),
(3, 'http://example.com/news3'),
(4, 'http://example.com/news4'),
(5, 'http://example.com/news5'),
(6, 'http://example.com/news6'),
(7, 'http://example.com/news7'),
(8, 'http://example.com/news8'),
(9, 'http://example.com/news9'),
(10, 'http://example.com/news10');

-- Step 11: Insert notifications
INSERT INTO notifications (is_read, authenticated_user_id) VALUES(FALSE, 1),
(FALSE, 2),
(TRUE, 3),
(FALSE, 4),
(TRUE, 5),
(FALSE, 6),
(TRUE, 7),
(FALSE, 8),
(TRUE, 9),
(FALSE, 10),
(FALSE, 11),
(TRUE, 12),
(FALSE, 13),
(TRUE, 14),
(FALSE, 15),
(TRUE, 16),
(FALSE, 17),
(TRUE, 18),
(FALSE, 19),
(TRUE, 20),
(FALSE, 21),
(TRUE, 22),
(FALSE, 23),
(TRUE, 24),
(FALSE, 25),
(TRUE, 26),
(FALSE, 27),
(TRUE, 28),
(FALSE, 29),
(TRUE, 30),
(FALSE, 31),
(TRUE, 32),
(FALSE, 33),
(TRUE, 34),
(FALSE, 35),
(TRUE, 36),
(FALSE, 37),
(TRUE, 38),
(FALSE, 39),
(TRUE, 40),
(FALSE, 41),
(TRUE, 42),
(FALSE, 43),
(TRUE, 44),
(FALSE, 45),
(TRUE, 46),
(FALSE, 47),
(TRUE, 48),
(FALSE, 49),
(TRUE, 50);

INSERT INTO reports (reported_id, reason, report_date, is_open, report_type, authenticated_user_id) VALUES
-- User Reports
(2,'User is spamming the community with irrelevant content', '2024-01-15 10:30:00', true, 'user_report', 1),
(3,'Harassment and inappropriate messages', '2024-01-16 14:20:00', false, 'user_report', 2),
(4,'Multiple accounts used for vote manipulation', '2024-01-17 09:15:00', true, 'user_report', 3),
(5,'Impersonating another user', '2024-01-18 16:45:00', true, 'user_report', 4),
(6,'Spreading misinformation', '2024-01-19 11:25:00', false, 'user_report', 5),

-- Comment Reports
(2,'Hate speech in comment', '2024-01-20 13:10:00', true, 'comment_report', 6),
(3,'Spam comment with malicious links', '2024-01-21 15:30:00', false, 'comment_report', 7),
(4,'Harassment in comment section', '2024-01-22 17:45:00', true, 'comment_report', 8),
(5,'Off-topic and inflammatory comments', '2024-01-23 09:20:00', false, 'comment_report', 9),
(6,'Personal information shared in comment', '2024-01-24 14:15:00', true, 'comment_report', 10),

-- Item (Post) Reports
(2,'Copyright violation in post', '2024-01-25 10:45:00', true, 'item_report', 11),
(3,'Misleading news article', '2024-01-26 16:30:00', false, 'item_report', 12),
(4,'Inappropriate content in post', '2024-01-27 11:20:00', true, 'item_report', 13),
(5,'Duplicate post spam', '2024-01-28 13:40:00', false, 'item_report', 14),
(6,'False information in news post', '2024-01-29 15:55:00', true, 'item_report', 15),

-- Topic Reports
(2,'Topic violates community guidelines', '2024-01-30 12:25:00', true, 'topic_report', 16),
(3,'Inappropriate topic title', '2024-02-01 14:35:00', false, 'topic_report', 17),
(4,'Topic contains sensitive material', '2024-02-02 16:50:00', true, 'topic_report', 18),
(5,'Topic promotes harmful behavior', '2024-02-03 09:30:00', false, 'topic_report', 19),
(6,'Topic contains personal attacks', '2024-02-04 11:45:00', true, 'topic_report', 20);

INSERT INTO user_followers (follower_id, followed_id) VALUES 
(2, 3), (3, 4), (5, 6), (6, 7), (7, 8), (8, 9), (9, 10), (10, 11), (12, 13), (14, 15), (16, 17), (18, 19), (19, 20), (21, 22), (23, 24), (25, 26), (27, 28), (29, 30), (31, 32), (33, 34), (35, 36), (37, 38), (39, 40), (41, 42), (43, 44), (45, 46), (47, 48), (49, 50);



INSERT INTO follow_notifications (notification_id, follower_id) VALUES(1, 2),
(2, 3),
(3, 4),
(4, 5),
(5, 6),
(6, 7),
(7, 8),
(8, 9),
(9, 10),
(10, 1),
(11, 1),
(12, 2),
(13, 3),
(14, 4),
(15, 5),
(16, 6),
(17, 7),
(18, 8),
(19, 9),
(20, 10);

INSERT INTO community_followers (authenticated_user_id, community_id) VALUES
-- Tech community
(3, 1), (4, 1), (5, 1), (31, 1), 
-- Book Lovers
(4, 2), (18, 2), (29, 2), (40, 2),
-- Anime Fans
(5, 3), (42, 3), (44, 3), (36, 3),
-- Travel Enthusiasts
(6, 4), (10, 4), (43, 4), (33, 4),
-- Fantasy Writers
(7, 5), (21, 5), (38, 5), (15, 5),
-- Culinary Artists
(13, 6), (9, 6), (26, 6), (45, 6),
-- Movie Buffs
(17, 7), (1, 7), (27, 7), (19, 7),
-- Fitness Fanatics
(8, 8), (34, 8), (30, 8), (48, 8),
-- Game Developers
(14, 9), (25, 9), (3, 9), (49, 9),
-- Nature Lovers
(6, 10), (20, 10), (33, 10), (44, 10);


INSERT INTO community_moderators (authenticated_user_id, community_id) 
VALUES 
(1, 1),  -- User 1 is a moderator of community 1
(2, 1);  -- User 2 is also a moderator of community 1

INSERT INTO favorite_posts(authenticated_user_id, post_id) VALUES (1,5);