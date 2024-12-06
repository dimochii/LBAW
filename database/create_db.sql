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
    password VARCHAR(255) NOT NULL,
    reputation INT DEFAULT 0,
    is_suspended BOOLEAN DEFAULT FALSE,
    creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP CHECK (creation_date <= CURRENT_TIMESTAMP),
    birth_date TIMESTAMP NOT NULL CHECK (birth_date < CURRENT_TIMESTAMP),
    description TEXT,
    is_admin BOOLEAN DEFAULT FALSE,
    image_id INT,
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
    authenticated_user_id INT,
    community_id INT,
    PRIMARY KEY (authenticated_user_id, community_id),
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
    vote_id INT,
    comment_id INT,
    PRIMARY KEY (vote_id, comment_id),
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
('images/user50.jpg');


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
('Tech community', 'A place for tech enthusiasts to share knowledge.', FALSE, 1),
('Book Lovers', 'A community for book lovers.', TRUE, 2),
('Anime Fans', 'Discuss your favorite anime and manga.', FALSE, 3),
('Travel Enthusiasts', 'Share your travel stories and tips.', TRUE, 4),
('Fantasy Writers', 'A place for aspiring fantasy authors.', FALSE, 5),
('Culinary Artists', 'Share recipes and cooking tips.', TRUE, 6),
('Movie Buffs', 'Discuss films and series.', FALSE, 7),
('Fitness Fanatics', 'Share fitness tips and motivation.', TRUE, 8),
('Game Developers', 'A community for game creation discussions.', FALSE, 9),
('Nature Lovers', 'Share your nature photography and stories.', TRUE, 10);

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
('Wildlife Photography Tips', 'Capture nature beautifully.', 10);

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
(2, CURRENT_TIMESTAMP, 'accepted'),
(3, CURRENT_TIMESTAMP, 'rejected'),
(4, CURRENT_TIMESTAMP, 'pending'),
(5, CURRENT_TIMESTAMP, 'accepted'),
(6, CURRENT_TIMESTAMP, 'pending'),
(7, CURRENT_TIMESTAMP, 'accepted'),
(8, CURRENT_TIMESTAMP, 'pending'),
(9, CURRENT_TIMESTAMP, 'rejected'),
(10, CURRENT_TIMESTAMP, 'accepted');

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

INSERT INTO reports (reason, report_date, is_open, report_type, authenticated_user_id) VALUES
-- User Reports
('User is spamming the community with irrelevant content', '2024-01-15 10:30:00', true, 'user_report', 1),
('Harassment and inappropriate messages', '2024-01-16 14:20:00', false, 'user_report', 2),
('Multiple accounts used for vote manipulation', '2024-01-17 09:15:00', true, 'user_report', 3),
('Impersonating another user', '2024-01-18 16:45:00', true, 'user_report', 4),
('Spreading misinformation', '2024-01-19 11:25:00', false, 'user_report', 5),

-- Comment Reports
('Hate speech in comment', '2024-01-20 13:10:00', true, 'comment_report', 6),
('Spam comment with malicious links', '2024-01-21 15:30:00', false, 'comment_report', 7),
('Harassment in comment section', '2024-01-22 17:45:00', true, 'comment_report', 8),
('Off-topic and inflammatory comments', '2024-01-23 09:20:00', false, 'comment_report', 9),
('Personal information shared in comment', '2024-01-24 14:15:00', true, 'comment_report', 10),

-- Item (Post) Reports
('Copyright violation in post', '2024-01-25 10:45:00', true, 'item_report', 11),
('Misleading news article', '2024-01-26 16:30:00', false, 'item_report', 12),
('Inappropriate content in post', '2024-01-27 11:20:00', true, 'item_report', 13),
('Duplicate post spam', '2024-01-28 13:40:00', false, 'item_report', 14),
('False information in news post', '2024-01-29 15:55:00', true, 'item_report', 15),

-- Topic Reports
('Topic violates community guidelines', '2024-01-30 12:25:00', true, 'topic_report', 16),
('Inappropriate topic title', '2024-02-01 14:35:00', false, 'topic_report', 17),
('Topic contains sensitive material', '2024-02-02 16:50:00', true, 'topic_report', 18),
('Topic promotes harmful behavior', '2024-02-03 09:30:00', false, 'topic_report', 19),
('Topic contains personal attacks', '2024-02-04 11:45:00', true, 'topic_report', 20);

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
(3, 1), (4, 1), (5, 1), (31, 1), (2,1),
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