DROP SCHEMA IF EXISTS lbaw2454 CASCADE;
CREATE SCHEMA lbaw2454;

SET search_path TO lbaw2454;
CREATE TYPE topic_status AS ENUM ('pending', 'accepted', 'rejected');
CREATE TYPE request_status AS ENUM ('pending', 'accepted', 'rejected');
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
    duration INT,
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

CREATE TABLE community_follow_requests (
    id SERIAL PRIMARY KEY,
    authenticated_user_id INT,
    community_id INT,
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    request_status request_status DEFAULT 'pending',
    FOREIGN KEY (authenticated_user_id) REFERENCES authenticated_users(id),
    FOREIGN KEY (community_id) REFERENCES communities(id)
);

CREATE TABLE request_notifications (
    id SERIAL PRIMARY KEY,
    request_id INT,
    notification_id INT,
    FOREIGN KEY (notification_id) REFERENCES notifications(id),
    FOREIGN KEY (request_id) REFERENCES community_follow_requests(id)
);


CREATE TABLE authors (
    authenticated_user_id INT,
    post_id INT,
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
SET search_path TO lbaw2454;


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
('images/hub200.jpg'),
('images/default.jpg');

-- Insert communities
INSERT INTO communities (name, description, privacy, image_id) VALUES
('AnimeFans', 'A community for anime enthusiasts', FALSE, 201),
('Superheroes', 'All about comic book and movie superheroes', FALSE,202),
('Mythology', 'Exploring myths and legends from around the world', FALSE, 203),
('TechTalk', 'Discussions about technology and innovations', FALSE, 204),
('AnimeTheories', 'Deep dive into anime plot theories and discussions', FALSE, 205),
('SciFiEnthusiasts', 'A community for science fiction lovers', FALSE, 206),
('MysteryLovers', 'Discussing detective novels and crime stories', FALSE, 207),
('FilmBuffs', 'All about cinema and movie discussions', FALSE, 208),
('GamingWorld', 'Video game discussions and reviews', FALSE, 209),
('LiteratureClub', 'Book discussions and literary analysis', FALSE, 210),
('AbyssLovers', 'Community for abyss and elevator enthusiasts', TRUE, 211),
('BookLovers', 'A community for book lovers.', TRUE, 212),
('TravelEnthusiasts', 'Share your travel stories and tips.', TRUE, 213),
('FantasyWriters', 'A place for aspiring fantasy authors.', FALSE, 214),
('CulinaryArtists', 'Share recipes and cooking tips.', TRUE, 215),
('MovieBuffs', 'Discuss films and series.', FALSE, 216),
('FitnessFanatics', 'Share fitness tips and motivation.', TRUE, 217),
('GameDevelopers', 'A community for game creation discussions.', FALSE, 218),
('NatureLovers', 'Share your nature photography and stories.', TRUE, 219),
('MusicProducers', 'A community for aspiring and professional music producers.', FALSE, 220),
('HistoryBuffs', 'Explore and discuss historical events and figures.', TRUE, 221),
('SportsFans', 'A place to talk about your favorite sports and teams.', FALSE, 222),
('PetLovers', 'Share tips and stories about pets.', TRUE, 223),
('ScienceGeeks', 'Discuss recent scientific discoveries and theories.', FALSE, 224),
('ArtEnthusiasts', 'Share and critique artwork.', TRUE, 225),
('DIYCreators', 'Tips and tricks for do-it-yourself projects.', FALSE, 226),
('EnvironmentAdvocates', 'Discussions on environmental conservation and activism.', TRUE, 227),
('CodingWizards', 'A space for developers to share coding tips.', FALSE, 228),
('HealthGurus', 'Tips for leading a healthy lifestyle.', TRUE, 229),
('CarEnthusiasts', 'Share knowledge and stories about cars.', FALSE, 230),
('PhotographyExperts', 'A community to discuss photography tips and gear.', TRUE, 231),
('BoardGameLovers', 'Discuss strategies and reviews of board games.', FALSE, 232),
('StartupFounders', 'A place for entrepreneurs to share insights.', TRUE, 233),
('ComedyEnthusiasts', 'Share jokes and discuss stand-up acts.', FALSE, 234),
('SpaceEnthusiasts', 'Discuss astronomy and space exploration.', TRUE, 235),
('LanguageLearners Hub', 'Share tips and resources for learning new languages.', FALSE, 236),
('MentalHealthCommunity', 'Support and resources for mental health awareness.', TRUE, 237),
('FashionInnovators', 'A community to discuss fashion trends and designs.', FALSE, 238),
('TechReviewers', 'Discuss the latest gadgets and technologies.', TRUE, 239),
('UrbanGardenersClub', 'Tips for gardening in urban spaces.', FALSE, 240),
('PhilosophyMinds', 'Discuss philosophical ideas and theories.', TRUE, 241),
('VolunteerNetwork', 'A place for volunteers to connect and share experiences.', FALSE, 242),
('HikingEnthusiasts', 'Share hiking trails and experiences.', TRUE, 243),
('E-SportsFans', 'Discuss e-sports teams and tournaments.', FALSE, 244),
('CreativeWritersNetwork', 'Share and critique creative writing pieces.', TRUE, 245),
('MeditationCircle', 'Discuss meditation techniques and benefits.', FALSE, 246),
('ClassicCarEnthusiasts', 'Share and discuss classic car collections.', TRUE, 247),
('CryptocurrencyExperts', 'Discuss trends and tips in cryptocurrency.', FALSE, 248),
('ParentingCommunity', 'Share advice and stories about parenting.', TRUE, 249),
('MartialArtsPractitioners', 'Discuss techniques and training.', FALSE, 250),
('RemoteWorkHub', 'Tips and support for working remotely.', TRUE, 251),
('GuitarPlayers', 'A community for guitar players of all levels.', FALSE, 252),
('ChessEnthusiasts', 'Discuss strategies and famous matches.', TRUE, 253),
('WildlifeAdvocates', 'Discuss efforts to protect wildlife.', FALSE, 254),
('CyclingFans', 'Share cycling routes and tips.', TRUE, 255),
('InteriorDesigners', 'Discuss home decoration and design ideas.', FALSE, 256),
('EconomicsEnthusiasts', 'Share resources and discuss economic theories.', TRUE, 257),
('FilmMakersGuild', 'A space for filmmakers to share tips and ideas.', FALSE, 258),
('AstronomyLovers', 'Discuss celestial events and phenomena.', TRUE, 259),
('YogaCommunity', 'Share yoga techniques and benefits.', FALSE, 260),
('ComicBookEnthusiasts', 'Discuss your favorite comics and graphic novels.', TRUE, 261),
('SocialJusticeWarriors', 'Discuss social issues and activism.', FALSE, 262),
('GardeningBeginners', 'Tips and support for starting a garden.', TRUE, 263),
('PianoEnthusiasts', 'A community for pianists to share tips.', FALSE, 264),
('VintageCollectors', 'Share your vintage collections and stories.', TRUE, 265),
('OutdoorAdventurers', 'Discuss camping and outdoor activities.', FALSE, 266),
('PoliticalEnthusiasts', 'Discuss current political events.', TRUE, 267),
('BakingEnthusiasts', 'Share baking tips and recipes.', FALSE, 268),
('HomebrewingExperts', 'Tips and tricks for brewing your own drinks.', TRUE, 269),
('AIInnovators', 'Discuss advancements in artificial intelligence.', FALSE, 270),
('SurfingEnthusiasts', 'Share surfing tips and experiences.', TRUE, 271),
('ClassicMovieFans', 'Discuss your favorite classic films.', FALSE, 272),
('CosplayArtists', 'Share tips and showcase your cosplay.', TRUE, 273),
('AdventureTravelers', 'Discuss extreme travel experiences.', TRUE, 274),
('ScienceFictionEnthusiasts', 'Discuss sci-fi books and movies.', FALSE, 275),
('PetTrainersGroup', 'Share pet training tips and techniques.', TRUE, 276),
('MountainClimbers', 'Share experiences and tips for climbing.', FALSE, 277),
('BeachEnthusiasts', 'Discuss your favorite beach destinations.', TRUE, 278),
('K-PopCommunity', 'Discuss your favorite K-Pop groups and music.', FALSE, 279),
('DigitalArtHub', 'Share digital art and techniques.', TRUE, 280),
('EntrepreneursNetwork', 'Discuss ideas and challenges for startups.', FALSE, 281),
('BoardGamersCircle', 'Discuss your favorite board games and strategies.', TRUE, 282),
('CatLoversClub', 'Share stories and tips about cats.', FALSE, 283),
('MovieDirectors', 'Discuss filmmaking and production tips.', TRUE, 284),
('SkiingEnthusiasts', 'Share skiing tips and destinations.', FALSE, 285),
('DroneHobbyists', 'Discuss drone flying tips and experiences.', TRUE, 286),
('VeganCommunity', 'Share vegan recipes and tips.', FALSE, 287),
('GraphicDesigners', 'Discuss design tools and techniques.', TRUE, 288),
('BirdWatchingEnthusiasts', 'Share birdwatching tips and sightings.', FALSE, 289),
('SculptorsNetwork', 'Discuss sculpting techniques and share work.', TRUE, 290),
('FictionWritersHub', 'Share your fictional works and critique.', FALSE, 291),
('SneakerCollectorsGroup', 'Discuss your sneaker collections.', TRUE, 292),
('RoboticsEnthusiasts', 'Discuss and share robotics projects.', FALSE, 293),
('FishingFans', 'Tips and stories about fishing experiences.', TRUE, 294),
('DroneExperts', 'A community for drone hobbyists.', FALSE, 295),
('AquariumEnthusiasts', 'Share tips and setups for aquariums.', TRUE, 296),
('HorrorMovieFans', 'Discuss your favorite horror films.', FALSE, 297),
('LandscapePhotographers', 'Share and discuss landscape photography.', TRUE, 298),
('TattooArtists', 'Discuss techniques and showcase tattoos.', FALSE, 299),
('MobileGamersHub', 'Discuss mobile games and tips.', TRUE, 300),
('HomeImprovementGurus', 'Tips for improving your home.', FALSE, 301),
('VirtualRealityFans', 'Discuss VR games and technology.', TRUE, 302);


-- Insert authenticated users
INSERT INTO authenticated_users (
    name, username, email, password, reputation, 
    is_suspended, birth_date, description, is_admin, image_id
) VALUES
-- Anonymous user (special case)
('Anonymous', 'anonymous', 'anonymous@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 
0, FALSE, '1990-01-01', 'Deleted User', FALSE, 1),

-- Admins
('Tiago Monteiro', 'tiago_admin', 'tiago@admin.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 
100, FALSE, '1985-05-15', 'Site Administrator', TRUE, 59),

('Vasco Costa', 'vasco_admin', 'vasco@admin.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 
100, FALSE, '1990-03-20', 'Senior Site Administrator', TRUE, 60),

('Teresa Mascarenhas', 'teresa_admin', 'teresa@admin.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 
100, FALSE, '1988-11-10', 'Community Management Admin', TRUE, 61),

('Diana Nunes', 'diana_admin', 'diana@admin.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 
100, FALSE, '1992-07-25', 'Content Moderation Admin', TRUE, 62),

-- Regular users
('Naruto Uzumaki', 'dattebayo', 'naruto@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 50, FALSE, '1997-10-10', 'Future Hokage', FALSE, 51),
('Goku Son', 'saiyan_warrior', 'goku@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 75, TRUE, '1984-04-05', 'Protector of Earth', FALSE, 52),
('Sherlock Holmes', 'consulting_detective', 'sherlock@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 90, FALSE, '1854-01-06', 'The world only consulting detective', FALSE, 53),
('Elsa Arendelle', 'ice_queen', 'elsa@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 60, FALSE, '1991-12-21', 'Queen of Arendelle', FALSE, 54),
('Peter Parker', 'spidey', 'peter@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 80, FALSE, '1995-08-15', 'Friendly neighborhood Spider-Man', FALSE, 55),
('Bob Johnson', 'bob', 'bob@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 40, FALSE, '1988-02-02', 'Loves to share news.', FALSE, 2),
('Charlie Brown', 'charlie', 'charlie@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 45, FALSE, '1992-03-03', 'Tech enthusiast.', FALSE, 3),
('Diana Prince', 'diana', 'diana@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 85, FALSE, '1985-04-04', 'Avid reader and commenter.', FALSE, 4),
('Edward Elric', 'edward', 'edward@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 70, FALSE, '1994-05-05', 'Anime and manga lover.', FALSE, 5),
('Fiona Gallagher', 'fiona', 'fiona@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 55, FALSE, '1991-06-06', 'Loves traveling and photography.', FALSE, 6),
('George Martin', 'george', 'george@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 95, FALSE, '1975-07-07', 'Fantasy writer and fan.', FALSE, 7),
('Hannah Montana', 'hannah', 'hannah@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 60, FALSE, '1998-08-08', 'Pop culture enthusiast.', FALSE, 8),
('Ian Malcolm', 'ian', 'ian@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 75, FALSE, '1980-09-09', 'Dinosaur expert and scientist.', FALSE, 9),
('Jack Sparrow', 'jack', 'jack@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 65, FALSE, '1980-10-10', 'Pirate captain and adventurer.', FALSE, 10),
('Katherine Pierce', 'katherine', 'katherine@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 50, FALSE, '1993-11-11', 'Mystery novel lover.', FALSE, 11),
('Liam Neeson', 'liam', 'liam@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy',0, FALSE, '1982-12-12', 'Film and theater enthusiast.', FALSE, 12),
('Monica Geller', 'monica', 'monica@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy',0, FALSE, '1980-01-13', 'Chef and cleanliness freak.', FALSE, 13),
('Nina Williams', 'nina', 'nina@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy',0, FALSE, '1979-02-14', 'Martial artist and game developer.', FALSE, 14),
('Oscar Wilde', 'oscar', 'oscar@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy',0, FALSE, '1854-03-15', 'Famous playwright and poet.', FALSE, 15),
('Penny Lane', 'penny', 'penny@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy',0, FALSE, '1992-04-16', 'Music lover and singer.', FALSE, 16),
('Quentin Tarantino', 'quentin', 'quentin@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy',0, FALSE, '1963-05-17', 'Director and screenwriter.', FALSE, 17),
('Rachel Green', 'rachel', 'rachel@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 0, FALSE,'1988-06-18', 'Fashion enthusiast.', FALSE, 18),
('Steve Rogers', 'steve', 'steve@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy',0, FALSE, '1922-07-19', 'Super soldier and leader.', FALSE, 19),
('Tony Stark', 'tony', 'tony@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy',0, FALSE, '1970-08-20', 'Inventor and philanthropist.', FALSE, 20),
('Ursula K. Le Guin', 'ursula', 'ursula@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy',0, FALSE, '1929-09-21', 'Renowned fantasy author.', FALSE, 21),
('Victor Frankenstein', 'victor', 'victor@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy',0, FALSE, '1794-10-22', 'Scientist and creator.', FALSE, 22),
('Will Turner', 'will', 'will@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy',0, FALSE, '1985-11-23', 'Blacksmith and pirate.', FALSE, 23),
('Xena Warrior', 'xena', 'xena@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy',0, FALSE, '1985-12-24', 'Warrior princess and leader.', FALSE, 24),
('Yoda', 'yoda', 'yoda@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 0, FALSE,'896-01-25', 'Jedi Master and wise mentor.', FALSE, 25),
('Zorro', 'zorro', 'zorro@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy',0, FALSE, '1840-02-26', 'Masked hero and protector.', FALSE, 26),
('Albus Dumbledore', 'albus', 'albus@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 0, FALSE,'1881-03-27', 'Headmaster of Hogwarts.', FALSE, 27),
('Bella Swan', 'bella', 'bella@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy',0, FALSE, '1987-04-28', 'Vampire and werewolf enthusiast.', FALSE, 28),
('Clark Kent', 'clark', 'clark@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy',0, FALSE, '1985-05-29', 'Journalist and superhero.', FALSE, 29),
('Darth Vader', 'darth', 'darth@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy',0, FALSE, '1941-06-30', 'Sith Lord and father figure.', FALSE, 30),
('Elliot Alderson', 'elliot', 'elliot@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 0, FALSE,'1992-07-31', 'Cybersecurity engineer.', FALSE, 31),
('Frodo Baggins', 'frodo', 'frodo@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy',0, FALSE, '1968-08-01', 'Ring bearer and adventurer.', FALSE, 32),
('Gandalf the Grey', 'gandalf', 'gandalf@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 0, FALSE,'1000-09-02', 'Wielder of magic and wisdom.', FALSE, 33),
('Homer Simpson', 'homer', 'homer@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy',0, FALSE, '1956-10-03', 'Loves donuts and family.', FALSE, 34),
('Icarus', 'icarus', 'icarus@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 0, FALSE,'2000-11-04', 'Aspiring inventor and dreamer.', FALSE, 35),
('Jules Winnfield', 'jules', 'jules@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy',0, FALSE, '1971-12-05', 'Professional hitman with a passion.', FALSE, 36),
('Katniss Everdeen', 'katniss', 'katniss@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy',0, FALSE, '1990-01-06', 'Revolutionary and survivor.', FALSE, 37),
('Lara Croft', 'lara', 'lara@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy',0, FALSE, '1975-02-07', 'Adventurer and archaeologist.', FALSE, 38),
('Marty McFly', 'marty', 'marty@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy',0, FALSE, '1968-03-08', 'Time traveler and teenager.', FALSE, 39),
('Nancy Drew', 'nancy', 'nancy@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy',0, FALSE, '1980-04-09', 'Famous detective and sleuth.', FALSE, 40),
('Oliver Twist', 'oliver', 'oliver@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 0, FALSE,'1837-05-10', 'Orphan and survivor.', FALSE, 41),
('Pikachu', 'pikachu', 'pikachu@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy',0, FALSE, '1996-06-11', 'Electric mouse and companion.', TRUE, 42),
('Quasimodo', 'quasimodo', 'quasimodo@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy',0, FALSE, '1460-07-12', 'Bell-ringer and misunderstood.', FALSE, 43),
('R2-D2', 'r2d2', 'r2d2@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy',0, FALSE, '1932-08-13', 'Astromech droid and hero.', FALSE, 44),
('SpongeBob SquarePants', 'spongebob', 'spongebob@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy',0, FALSE, '1986-09-14', 'Underwater fry cook and optimist.', FALSE, 45),
('Thor Odinson', 'thor', 'thor@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 0, FALSE,'1965-10-15', 'God of thunder and hero.', FALSE, 46),
('Ultron', 'ultron', 'ultron@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 0, FALSE,'2015-11-16', 'A.I. villain with a complex.', FALSE, 47),
('Violet Parr', 'violet', 'violet@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy',0, FALSE, '2000-12-17', 'Superhero with force fields.', FALSE, 48),
('Wolverine', 'wolverine', 'wolverine@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy',0, FALSE, '1882-01-18', 'Mutant and fighter.', FALSE, 49),
('X-Men', 'xmen', 'xmen@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy',0, FALSE, '1963-02-19', 'Superhero team with various powers.', FALSE, 50),
('Bondrewd', 'bondrewd', 'bondrewd@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 10000, FALSE, '2017-07-7', 'A ruthless and manipulative White Whistle Delver with a twisted obsession with the Abyss, elevators and fur.', FALSE, 56), --user 60
('Nanachi', 'nanachi', 'nanachi@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 0, FALSE, '2017-07-7', 'A compassionate and fluffy elevator hater.', FALSE, 57),
('Prushka', 'prushka', 'prushka@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 0, FALSE, '2017-07-7', 'A kind-hearted and courageous girl who was adopted by Bondrewd.', FALSE, 58),

('Geralt of Rivia', 'geralt', 'geralt@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 0, FALSE, '1163-09-01', 'A mutated monster hunter known as The Witcher.', FALSE, 63),
('Yennefer of Vengerberg', 'yennefer', 'yennefer@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 0, FALSE, '1173-05-03', 'A powerful sorceress seeking redemption.', FALSE, 64),
('Wednesday Addams', 'wednesday', 'wednesday@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 0, FALSE, '2007-10-13', 'A brilliant and darkly humorous student of Nevermore Academy.', FALSE, 65),
('Joe Goldberg', 'joe', 'joe@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 0, FALSE, '1987-06-18', 'A charismatic yet dangerously obsessive bookseller.', FALSE, 66),
('Eleven', 'eleven', 'eleven@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 0, FALSE, '1971-03-24', 'A telekinetic girl with a mysterious past.', FALSE, 67),
('Jim Hopper', 'hopper', 'hopper@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 0, FALSE, '1965-04-21', 'A protective sheriff in Hawkins.', FALSE, 68),
('BoJack Horseman', 'bojack', 'bojack@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 0, FALSE, '1964-01-02', 'A washed-up sitcom star struggling with identity.', FALSE, 69),
('Nairobi', 'nairobi', 'nairobi@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 0, FALSE, '1982-12-12', 'A bold and resourceful member of the heist team.', FALSE, 70),
('Michael Wheeler', 'mike', 'mike@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 0, FALSE, '1971-04-07', 'A loyal friend and leader of a group of kids in Hawkins.', FALSE, 71),
('Ruth Langmore', 'ruth', 'ruth@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 0, FALSE, '1995-03-26', 'A tough and determined member of a criminal enterprise.', FALSE, 72),
('Otis Milburn', 'otis', 'otis@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 0, FALSE, '2004-07-01', 'A socially awkward teen turned unofficial therapist.', FALSE, 73),
('Maeve Wiley', 'maeve', 'maeve@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 0, FALSE, '2004-09-12', 'A brilliant and fiercely independent student.', FALSE, 74),
('Marienne Bellamy', 'marienne', 'marienne@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 0, FALSE, '1985-02-20', 'A librarian with a deep connection to literature.', FALSE, 75),
('Simon Basset', 'simon', 'simon@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 0, FALSE, '1783-08-05', 'The dashing Duke of Hastings.', FALSE, 76),
('Luther Hargreeves', 'luther', 'luther@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 0, FALSE, '1989-11-05', 'An enhanced leader of the Umbrella Academy.', FALSE, 77),
('Klaus Hargreeves', 'klaus', 'klaus@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 0, FALSE, '1989-12-13', 'A troubled medium who can speak to the dead.', FALSE, 78),
('Beth Harmon', 'beth', 'beth@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 0, FALSE, '1948-11-09', 'A chess prodigy navigating her genius and addiction.', FALSE, 79),
('Tokyo', 'tokyo', 'tokyo@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 0, FALSE, '1989-07-07', 'A passionate and unpredictable heist team member.', FALSE, 80),
('Dustin Henderson', 'dustin', 'dustin@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 0, FALSE, '1971-05-29', 'A quirky and clever member of the Hawkins kids.', FALSE, 81),
('The Professor', 'professor', 'professor@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 0, FALSE, '1976-02-27', 'The mastermind behind the heist in Money Heist.', FALSE, 82),

('Mickey Mouse', 'mickey', 'mickey@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 100, FALSE, '1928-11-18', 'The cheerful and adventurous mouse who started it all.', FALSE, 83),
('Minnie Mouse', 'minnie', 'minnie@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 95, FALSE, '1928-11-18', 'Mickey’s sweetheart with a charming and fashionable personality.', FALSE, 84),
('Donald Duck', 'donald', 'donald@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 80, FALSE, '1934-06-09', 'The quick-tempered but lovable duck with a heart of gold.', FALSE, 85),
('Goofy Goof', 'goofy', 'goofy@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 70, FALSE, '1932-05-25', 'A clumsy but well-meaning friend to Mickey and the gang.', FALSE, 86),
('Daisy Duck', 'daisy', 'daisy@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 85, FALSE, '1940-01-09', 'Donald’s sophisticated and sassy girlfriend.', FALSE, 87),
('Pluto', 'pluto', 'pluto@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 60, FALSE, '1930-09-05', 'Mickey’s loyal and playful pet dog.', FALSE, 88),
('Scrooge McDuck', 'scrooge', 'scrooge@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 150, FALSE, '1947-12-19', 'A wealthy and adventurous Scottish duck with a penchant for treasure.', FALSE, 89),
('Huey Duck', 'huey', 'huey@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 50, FALSE, '1947-12-19', 'The smart and resourceful eldest triplet.', FALSE, 90),
('Dewey Duck', 'dewey', 'dewey@example.com', '$2y$10$BPqmTy3x20LFhZOytOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 50, FALSE, '1947-12-19', 'The bold and curious middle triplet.', FALSE, 91),
('Louie Duck', 'louie', 'louie@example.com', '$2y$10$BPqmTy3x20LFhZOytOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 50, FALSE, '1947-12-19', 'The laid-back and clever youngest triplet.', FALSE, 92),
('Belle', 'belle', 'belle@example.com', '$2y$10$BPqmTy3x20LFhZOytOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 120, FALSE, '1991-11-22', 'A kind and intelligent young woman with a love for books.', FALSE, 93),
('Beast', 'beast', 'beast@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 90, FALSE, '1991-11-22', 'A cursed prince with a gruff exterior but a kind heart.', FALSE, 94),
('Aladdin', 'aladdin', 'aladdin@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 95, FALSE, '1992-11-25', 'A street-smart dreamer with a heart of gold.', FALSE, 95),
('Jasmine', 'jasmine', 'jasmine@example.com', '$2y$10$BPqmTy3x20LFhZOytOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 100, FALSE, '1992-11-25', 'A spirited princess who seeks freedom and true love.', FALSE, 96),
('Genie', 'genie', 'genie@example.com', '$2y$10$BPqmTy3x20LFhZOytOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 200, FALSE, '1992-11-25', 'A hilarious and magical wish-granting entity.', FALSE, 97),
('Ariel', 'ariel', 'ariel@example.com', '$2y$10$BPqmTy3x20LFhZOytOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 110, FALSE, '1989-11-17', 'A curious mermaid who dreams of life on land.', FALSE, 98),
('Sebastian', 'sebastian', 'sebastian@example.com', '$2y$10$BPqmTy3x20LFhZOytOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 80, FALSE, '1989-11-17', 'A loyal crab with a knack for music and keeping Ariel safe.', FALSE, 99),
('Violet Evergarden', 'viorettu', 'violetevergarden@example.com', '$2y$10$BPqmTy3x20LFhZOytOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 60, FALSE, '1989-11-17', 'A strong soldier now looking to fill letters with feelings.', FALSE, 100);







-- Insert posts
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
('Photographing Wildlife in Challenging Conditions', 'How to capture stunning shots in tough environments.', 10),--20

('Top 10 Anime Fights of All Time', 'A comprehensive list of the most epic battles in anime history...', 1),
('The Science Behind Dragon Ball Power Levels', 'An in-depth analysis of power scaling in Dragon Ball universe...', 1),
('Mythology and Modern Storytelling', 'How ancient myths continue to inspire contemporary narratives...', 3),
('Emerging Tech Trends in 2024', 'A look at the most promising technological innovations...', 4),
('Naruto: Chakra System Explained', 'A detailed breakdown of how chakra works in the Naruto universe...', 1),
('Superhero Origin Stories Comparison', 'Comparing the backstories of different iconic superheroes...', 2),
('Quantum Computing Breakthroughs', 'Recent advancements in quantum computing technology...', 4),
('Anime Character Psychological Profiles', 'Deep psychological analysis of complex anime characters...', 5),
('The Evolution of Detective Fiction', 'Tracing the development of detective stories through history...', 3),
('Web Development Frameworks Comparison', 'An objective look at modern web development technologies...', 4),
('The Art of Storytelling in Fantasy Novels', 'An exploration of narrative techniques in modern fantasy writing...', 3),
('Dinosaur Research: Past and Future', 'A comprehensive look at paleontological discoveries and future prospects...', 4),
('Pirate Legends and Historical Accuracy', 'Separating myth from reality in pirate narratives...', 3),
('Pop Culture Trends of the Decade', 'Analyzing the most significant pop culture moments...', 5),
('Technology and Scientific Innovation', 'How modern science is changing our understanding of the world...', 4),
('Star Wars vs Star Trek: An Epic Comparison', 'A deep dive into two of the most iconic sci-fi franchises...', 6),
('Best Detective Novels of the 21st Century', 'Exploring groundbreaking mystery literature...', 7),
('Indie Music Festival Highlights', 'Showcasing hidden gems from recent independent songs...', 8),
('Next-Gen Gaming Technologies', 'Exploring the future of video game development...', 9),
('Classic Literature Reimagined', 'How modern authors reinterpret classic works...', 10),
('Space Exploration Documentary Review', 'An in-depth look at recent space documentaries...', 6),
('The Psychology of Detective Characters', 'Analyzing complex protagonists in mystery fiction...', 7),
('Cinematography Techniques in Modern Cinema', 'Breaking down innovative filming methods...', 8),
('eSports: The Future of Competitive Gaming', 'Exploring the rise of professional gaming...', 9),
('Magical Realism in Contemporary Literature', 'Examining the genre''s evolution and impact...', 10),
('New season confirmed', 'Next season coming up!', 11), --46

('Understanding Blockchain', 'A beginner-friendly introduction to blockchain technology.', 39),
('Top 10 Anime Villains', 'Discussing the most iconic antagonists in anime history.', 1),
('Hidden Beaches You Need to Visit', 'Explore secret beach destinations for your next vacation.', 70),
('How to Start a Home Garden', 'Easy steps for beginners to start gardening at home.', 54),
('K-Pop Trends of 2024', 'What’s new in the K-Pop world this year?', 71),
('Must-Have Gadgets for 2024', 'Top tech gadgets that are making waves.', 30),
('Classic Detective Stories Worth Reading', 'Dive into the best classic mystery novels.', 7),
('The Future of VR Gaming', 'How virtual reality is transforming gaming.', 98),
('Tips for Writing Sci-Fi Novels', 'Advice for creating compelling science fiction stories.', 67),
('Best Cars for Off-Road Adventures', 'Explore the top off-road vehicles of the year.', 21),
('Tips for DIY Home Renovations', 'Simple DIY projects to transform your space.', 99),
('Exploring Mythical Creatures', 'How mythical creatures vary across cultures.', 3),
('Tips for Capturing Stunning Portraits', 'Portrait photography essentials.', 22),
('Eco-Friendly Travel Tips', 'How to reduce your carbon footprint while traveling.', 4),
('How to Get Started with Coding', 'Beginner’s guide to programming.', 19),
('The History of Jazz Music', 'Exploring the roots and evolution of jazz.', 11),
('Hiking Trails for Beginners', 'Top beginner-friendly hiking spots.', 34),
('Space Exploration Milestones', 'A timeline of humanity’s journey into space.', 26),
('Yoga Poses for Stress Relief', 'Best yoga techniques to reduce stress.', 51),
('How to Train Your Pet', 'Effective training tips for dogs and cats.', 68),
('Exploring Surrealism in Art', 'A deep dive into surrealist techniques and history.', 80),
('Best Cosplays of the Year', 'Showcasing outstanding cosplay creations.', 64),
('Top Board Games for Strategy Lovers', 'A guide to the best strategic board games.', 74),
('Guitar Techniques for Beginners', 'Essential skills for new guitar players.', 43),
('Mental Health and Social Media', 'The impact of social media on mental well-being.', 28),
('Anticipated Sci-Fi Movies of 2024', 'What to expect in the world of science fiction cinema.', 67),
('Drone Photography Tips', 'How to take breathtaking shots with drones.', 78),
('Healthy Eating on a Budget', 'Tips for eating well without overspending.', 20),
('Advancements in Robotics', 'Recent breakthroughs in robotics technology.', 91),
('Beach Safety Tips for Families', 'How to stay safe while enjoying the beach.', 70),
('Traveling as a Digital Nomad', 'Tips for working remotely while exploring the world.', 42),
('Best Books for Fantasy Lovers', 'Top picks for fans of the fantasy genre.', 5),
('The Rise of Indie Video Games', 'Exploring the indie gaming scene.', 9),
('How to Plan a Startup', 'Step-by-step guide for aspiring entrepreneurs.', 73),
('Environmental Conservation Success Stories', 'Celebrating achievements in conservation efforts.', 18),
('Essential Tools for Digital Artists', 'Top tools and software for digital art creation.', 72),
('Meditation for Beginners', 'Tips to start your meditation journey.', 37),
('The Science Behind Horror Movies', 'Why we love to be scared.', 95),
('Best Practices for Urban Gardening', 'Grow plants effectively in small spaces.', 31),
('How AI is Reshaping the Job Market', 'The impact of artificial intelligence on careers.', 61),
('The Role of Philosophy in Modern Society', 'Why philosophical discussions matter today.', 32),
('Best Cameras for Wildlife Photography', 'Top gear recommendations for nature photography.', 22),
('Understanding Cryptocurrencies', 'An overview of popular cryptocurrencies in 2024.', 39),
('The Benefits of Volunteer Work', 'How volunteering can transform communities.', 33),
('How to Master Chess Openings', 'Strategies to dominate your next chess match.', 44),
('Tips for Interior Lighting Design', 'How to use lighting to enhance your home.', 47),
('The History of Martial Arts', 'Tracing the roots of popular martial arts styles.', 41),
('Best Sci-Fi Books of All Time', 'Top science fiction novels everyone should read.', 67),
('The Future of Renewable Energy', 'Innovative solutions in green energy.', 18),
('Parenting Tips for the Digital Age', 'Raising kids in a technology-driven world.', 40),
('Top Vegan Recipes for Beginners', 'Easy and delicious plant-based meals.', 79),
('How to Get Into Film Directing', 'Steps to kickstart your filmmaking journey.', 76),
('Upcoming Astronomy Events in 2024', 'Don’t miss these celestial phenomena.', 50),
('Famous Classical Sculptures', 'Exploring iconic sculptures from history.', 82),
('How to Save Money While Traveling', 'Budgeting tips for globetrotters.', 4),
('The Psychology of Superheroes', 'Why we love superhero stories.', 2),
('Fitness Challenges to Try in 2024', 'Fun ways to stay active and motivated.', 8),
('Tips for Brewing Craft Beer at Home', 'A beginner’s guide to homebrewing.', 60),
('How to Protect Wildlife Habitats', 'Simple steps to support conservation.', 45),
('The Art of Baking Bread', 'Techniques for perfect homemade bread.', 59),
('Creative Writing Prompts', 'Ideas to spark your next story.', 36),
('Why Science Fiction Inspires Innovation', 'The connection between sci-fi and real-world technology.', 6),
('Exploring the World of Miniature Gaming', 'A guide to tabletop miniatures.', 23),
('The Best Hiking Gear for 2024', 'Must-have equipment for your next adventure.', 34), --110
('How to Spot Rare Birds', 'Tips for beginner birdwatchers.', 81),
('Exploring the Deep Ocean', 'What lies beneath the surface?', 11),
('Essential Skills for Aspiring Entrepreneurs', 'What you need to succeed as a startup founder.', 24),
('Top Beaches for Surfing', 'The best spots to catch waves this year.', 62),
('Writing Historical Fiction', 'Tips for weaving history into your stories.', 48),
('The Art of Stand-Up Comedy', 'What makes a great comedian?', 25),
('How to Start a Fitness Journey', 'Advice for beginners in fitness.', 20),
('Exploring Classic Films of the 20th Century', 'Must-watch movies from the golden era of cinema.', 63),
('The Impact of AI on Creative Arts', 'How artificial intelligence is influencing art.', 72),
('Best Biking Trails Around the World', 'Top cycling routes for adventure seekers.', 46),
('The Evolution of Horror Films', 'Tracing the history of the horror genre.', 95),
('How to Build a Successful Online Business', 'Tips for digital entrepreneurship.', 42),
('Sustainable Fashion Trends', 'How to make eco-friendly choices in fashion.', 29),
('Tips for Writing Detective Novels', 'Crafting compelling mysteries.', 7),
('The Future of Space Exploration', 'Upcoming missions and innovations.', 26); --125

INSERT INTO news (post_id, news_url)
VALUES 
(1, 'https://www.emergingtechbrew.com/stories/2024/12/13/small-business-ai-amex-census'),
(2, 'https://www.nytimes.com/2024/12/03/books/best-books-2024.html'),
(3, 'https://animecorner.me/summer-2024-anime-of-the-season-rankings/'),
(4, 'https://www.kplctv.com/video/2024/12/14/watching-your-wallet-winter-travel-tips/'),
(5, 'https://culturefly.co.uk/dee-benson-on-creating-a-magical-fantasy-world/'),
(6, 'https://www.womenshealthmag.com/food/a63013707/high-protein-meal-prep-manual-review/'),
(7, 'https://www.forbes.com/sites/travisbean/2024/12/14/the-top-10-roundup-netflixs-most-popular-movies-in-2024/'),
(8, 'https://www.theguardian.com/lifeandstyle/2024/nov/10/in-for-the-chill-five-useful-tips-to-help-you-stay-fit-in-the-winter'),
(9, 'https://www.designrush.com/agency/blockchain-development-companies/trends/blockchain-game-development'),
(10, 'https://www.timeout.pt/lisboa/pt/noticias/em-imagens-os-vencedores-dos-comedy-wildlife-photography-awards-2024-121424'),

(24, 'https://www2.deloitte.com/us/en/insights/focus/tech-trends.html'),
(26, 'https://gamerant.com/hollywood-done-superhero-origin-stories/'),
(27, 'https://www.forbes.com/sites/ansellindner/2024/12/12/googles-quantum-computing-leap-what-it-means-for-bitcoins-security/'),
(30, 'https://www.statista.com/statistics/1124699/worldwide-developer-survey-most-used-frameworks-web/'),
(32, 'https://www.korea.net/NewsFocus/Society/view?articleId=262982'),
(35, 'https://sdg.iisd.org/events/8th-g-stic-conference/'),
(36, 'https://observador.pt/especiais/star-trek-vs-star-wars-esta-e-a-verdadeira-guerra-das-estrelas/'),
(38, 'https://expresso.pt/blitz/2024-08-08-mais-um-agosto-mais-um-vodafone-paredes-de-coura-o-espirito-e-a-musica-de-um-festival-indie-a9cbdf2b'),
(39, 'https://www.ign.com/videos/the-most-next-gen-games-of-2024-next-gen-console-watch'),
(46, 'https://thedirect.com/article/made-in-abyss-season-3-confirmed-details'),


(23, 'https://www.timesnownews.com/lifestyle/books/features/how-mythology-and-folklore-shape-modern-storytelling-article-110905765'), -- The Rise of Indie Video Games
(41, 'https://www.nasa.gov/news-release/nasas-planetary-protection-review-addresses-changing-reality-of-space-exploration/'), -- The History of Martial Arts
(47, 'https://radiocastelobranco.sapo.pt/docente-do-ipcb-participa-em-conferencia-sobre-tecnologia-blockchain-e-chatgpt/'), -- Tips for Interior Lighting Design
(51, 'https://www.sportskeeda.com/us/k-pop/what-fantastic-year-k-pop-fans-celebrate-dazed-reveals-top-50-k-pop-songs-2024-featuring-stray-kids-bts-rm-jin'), -- Yoga Poses for Stress Relief
(52, 'https://www.zdnet.com/home-and-office/the-7-tech-gadgets-i-couldnt-live-without-in-2024/'),
(53, 'https://www.nytimes.com/2024/11/11/books/review/vintage-crime-reissued-novels-fiction.html'),
(55, 'https://reactormag.com/some-of-reactors-best-articles-about-fiction-reading-and-writing-in-2024/'),
(57, 'https://realestate.usnews.com/real-estate/articles/how-to-prepare-for-a-home-renovation'),
(65, 'https://www.terra.com.br/vida-e-estilo/saude/especialista-recomenda-yoga-no-combate-ao-stress,5c9403ab333beeb976460907c426181avbabg7e5.html'),
(66, 'https://www.kinship.com/pet-behavior'), --30
(68, 'https://variety.com/gallery/best-cosplay-of-comic-con/'),
(69, 'https://www.theguardian.com/thefilter/2024/dec/11/best-family-board-games'),
(71, 'https://www.gearnews.com/essential-guitar-techniques-mastering-what-you-need-to-know/'),
(75, 'https://govciomedia.com/ai-unmanned-systems-are-transforming-power-navy-secretary-says/'),
(77, 'https://www.aa.com.tr/en/life/digital-nomadism-redefines-work-and-travel-across-the-globe/3400753'),
(83, 'https://news.hamlethub.com/redding-connecticut/meditation-for-beginners-1732565170444'),
(60, 'https://esgnews.com/top-7-sustainable-travel-tips-for-winter-holiday/'), -- Tips for Brewing Craft Beer at Home
(61, 'https://www.simplilearn.com/tutorials/programming-tutorial/coding-for-beginners'), -- How AI is Reshaping the Job Market
(78, 'https://www.nytimes.com/2024/12/06/books/review/best-science-fiction-fantasy-books-2024.html'), -- Drone Photography Tips
(91, 'https://www.chess.com/article/view/book-review-mastering-the-chess-openings'), -- Advancements in Robotics
(95, 'https://www.weforum.org/impact/clean-energy-in-emerging-markets/'), 
(96, 'https://www.livemint.com/mint-lounge/ideas/how-to-raise-children-in-the-digital-age-111667911752992.html'),
(97, 'https://www.realsimple.com/food-recipes/recipe-collections-favorites/healthy-meals/easy-vegan-recipes'),
(98, 'https://www.backstage.com/magazine/article/become-film-director-3080/'),
(100, 'https://mymodernmet.com/famous-sculptures-art-history/'), --45
(101, 'https://www.nerdwallet.com/article/travel/saving-money-on-travel-tricks'),
(102, 'https://www.smithsonianmag.com/arts-culture/the-psychology-behind-superhero-origin-stories-4015776/'),
(103, 'https://gulfbusiness.com/fun-things-to-do-at-dubai-fitness-challenge-2024/'),
(104, 'https://chatelaine.com/food/drinks/homebrew-beer-tips/'),
(105, 'https://defenders.org/blog/2024/12/how-best-protect-wildlife-of-pacific-northwest-forest-0'),
(106, 'https://arstechnica.com/culture/2024/11/flour-water-salt-github-the-bread-code-is-a-sourdough-baking-framework/'),
(107, 'https://clairehennessy.substack.com/p/advent-scribbles-daily-december-writing?utm_campaign=post&utm_medium=web'),
(108, 'https://timesofmalta.com/article/science-fiction-catalyst-tomorrow-technology.1091552'),
(109, 'https://www.mk.co.kr/en/culture/11123415'),
(110, 'https://www.backpacker.com/gear/gear-backpackers-editors-loved-in-2024/'); --55

-- Insert into Topics table (topics awaiting review)
INSERT INTO topics (post_id, status, review_date)
VALUES 
(11, 'accepted', CURRENT_TIMESTAMP),
(12, 'accepted', CURRENT_TIMESTAMP),
(13, 'pending', CURRENT_TIMESTAMP),
(14, 'accepted', CURRENT_TIMESTAMP),
(15, 'pending', CURRENT_TIMESTAMP),
(16, 'accepted', CURRENT_TIMESTAMP),
(17, 'pending', CURRENT_TIMESTAMP),
(18, 'accepted', CURRENT_TIMESTAMP),
(19, 'accepted', CURRENT_TIMESTAMP), --9

(25, 'accepted', CURRENT_TIMESTAMP),
(28, 'accepted', CURRENT_TIMESTAMP),
(29, 'pending', CURRENT_TIMESTAMP),
(31, 'accepted', CURRENT_TIMESTAMP),
(33, 'pending', CURRENT_TIMESTAMP),
(34, 'accepted', CURRENT_TIMESTAMP),
(37, 'pending', CURRENT_TIMESTAMP),
(40, 'accepted', CURRENT_TIMESTAMP),
(42, 'pending', CURRENT_TIMESTAMP),
(43, 'accepted', CURRENT_TIMESTAMP),
(44, 'pending', CURRENT_TIMESTAMP), --20
(45, 'accepted', CURRENT_TIMESTAMP), 
(49, 'accepted', CURRENT_TIMESTAMP),
(56, 'accepted', CURRENT_TIMESTAMP),
(58, 'accepted', CURRENT_TIMESTAMP),
(84, 'accepted', CURRENT_TIMESTAMP),
(85, 'accepted', CURRENT_TIMESTAMP),
(86, 'accepted', CURRENT_TIMESTAMP),
(87, 'accepted', CURRENT_TIMESTAMP),
(88, 'accepted', CURRENT_TIMESTAMP),
(89, 'rejected', CURRENT_TIMESTAMP),
(90, 'rejected', CURRENT_TIMESTAMP),
(92, 'pending', CURRENT_TIMESTAMP),
(93, 'pending', CURRENT_TIMESTAMP),
(94, 'pending', CURRENT_TIMESTAMP), --34

(20, 'accepted', CURRENT_TIMESTAMP),          -- Healthy Eating on a Budget
(21, 'accepted', CURRENT_TIMESTAMP),                  -- Best Cars for Off-Road Adventures
(22, 'accepted', CURRENT_TIMESTAMP),                 -- Tips for Capturing Stunning Portraits
(48, 'accepted', CURRENT_TIMESTAMP),                     -- Writing Historical Fiction
(50, 'accepted', CURRENT_TIMESTAMP),                   -- Upcoming Astronomy Events in 2024
(54, 'accepted', CURRENT_TIMESTAMP),                   -- How to Start a Home Garden
(59, 'accepted', CURRENT_TIMESTAMP),          -- The Art of Baking Bread
(62, 'accepted', CURRENT_TIMESTAMP),       -- Top Beaches for Surfing
(63, 'accepted', CURRENT_TIMESTAMP),               -- Exploring Classic Films of the 20th Century
(64, 'accepted', CURRENT_TIMESTAMP),                     -- Best Cosplays of the Year
(67, 'accepted', CURRENT_TIMESTAMP),             -- Tips for Writing Sci-Fi Novels
(70, 'accepted', CURRENT_TIMESTAMP),                      -- Beach Safety Tips for Families
(72, 'accepted', CURRENT_TIMESTAMP),                 -- Essential Tools for Digital Artists
(73, 'accepted', CURRENT_TIMESTAMP),                    -- How to Plan a Startup
(74, 'accepted', CURRENT_TIMESTAMP),                 -- Top Board Games for Strategy Lovers
(76, 'accepted', CURRENT_TIMESTAMP), --50          -- How to Get Into Film Directing
(79, 'accepted', CURRENT_TIMESTAMP),             -- Top Vegan Recipes for Beginners
(80, 'accepted', CURRENT_TIMESTAMP),             -- Exploring Surrealism in Art
(81, 'accepted', CURRENT_TIMESTAMP),                -- How to Spot Rare Birds
(82, 'accepted', CURRENT_TIMESTAMP),        -- Famous Classical Sculptures
(99, 'accepted', CURRENT_TIMESTAMP),  --55          -- Tips for DIY Home Renovations
(111, 'accepted', CURRENT_TIMESTAMP), 
(112, 'accepted', CURRENT_TIMESTAMP), 
(113, 'accepted', CURRENT_TIMESTAMP), 
(114, 'accepted', CURRENT_TIMESTAMP), 
(115, 'accepted', CURRENT_TIMESTAMP), 
(116, 'pending', CURRENT_TIMESTAMP),
(117, 'pending', CURRENT_TIMESTAMP),
(118, 'pending', CURRENT_TIMESTAMP),
(119, 'pending', CURRENT_TIMESTAMP),
(120, 'pending', CURRENT_TIMESTAMP),
(121, 'rejected', CURRENT_TIMESTAMP),
(122, 'rejected', CURRENT_TIMESTAMP),
(123, 'rejected', CURRENT_TIMESTAMP),
(124, 'rejected', CURRENT_TIMESTAMP),
(125, 'rejected', CURRENT_TIMESTAMP); --70

-- Link authors to posts
INSERT INTO authors (authenticated_user_id, post_id) VALUES

  -- Naruto Uzumaki (dattebayo)
  (6, 3),  -- Top Anime of the Season (fan)
  (6, 27), -- Naruto: Chakra System Explained (expert)
  (6, 45),  -- Top 10 Anime Fights of All Time (expert)
  (6, 49),  -- The Science Behind Dragon Ball Power Levels (related)
  (6, 53), -- Exploring the World of Miniature Gaming (related)
  (6, 93), -- Best Anime of All Time (fan)

  -- Goku Son (saiyan_warrior)
  (7, 14),  -- Budget Friendly Travel Hacks (curious)
  (7, 68),  -- Anticipated Sci-Fi Movies of 2024 (fan)
  (7, 95), -- The Science Behind Horror Movies (curious)
  (7, 49),  -- The Science Behind Dragon Ball Power Levels (expert)
  (7, 93), -- Best Anime of All Time (fan)

  -- Sherlock Holmes (consulting_detective)
  (8, 7),   -- Tips for Writing Detective Novels (expert)
  (8, 37), -- Meditation for Beginners (curious)
  (8, 63), -- Exploring Classic Films of the 20th Century (curious)
  (8, 81), -- How to Spot Rare Birds (curious)
  (8, 95), -- The Evolution of Horror Films (curious)

  -- Elsa Arendelle (ice_queen)
  (9, 40), -- Environmental Conservation Success Stories (supporter)
  (9, 70), -- Hidden Beaches You Need to Visit (interested)
  (9, 80), -- Top Vegan Recipes for Beginners (interested)
  (9, 99), -- Tips for DIY Home Renovations (interested)

  -- Peter Parker (spidey)
  (10, 120),   -- The Psychology of Superheroes (fan)
  (10, 40), -- Environmental Conservation Success Stories (supporter)
  (10, 63), -- Exploring Classic Films of the 20th Century (curious)
  (10, 76), -- How to Get Into Film Directing (interested)
  (10, 95), -- The Evolution of Horror Films (curious)

  -- Bob Johnson (bob)
  (11, 23),  -- The Rise of AI (interested)
  (11, 4),  -- Eco-Friendly Travel Tips (interested)
  (11, 40), -- Environmental Conservation Success Stories (supporter)
  (11, 20), -- Healthy Eating on a Budget (interested)
  (11, 61), -- How AI is Reshaping the Job Market (interested)

  -- Charlie Brown (charlie)
  (12, 9),   -- The Rise of Indie Video Games (expert)
  (12, 19), -- How to Get Started with Coding (interested)
  (12, 30), -- Must-Have Gadgets for 2024 (interested)
  (12, 72),  -- Essential Tools for Digital Artists (expert)
  (12, 91), -- Advancements in Robotics (interested)

  -- Diana Prince (diana)
  (13, 121),  -- The Psychology of Superheroes (interested)
  (13, 18), -- Environmental Conservation Success Stories (supporter)
  (13, 32), -- The Role of Philosophy in Modern Society (interested)
  (13, 40), -- Parenting Tips for the Digital Age (interested)
  (13, 95), -- The Evolution of Horror Films (curious)

  -- Edward Elric (edward) (continued)
  (14, 10),  -- Wildlife Photography Tips (interested)
  (14, 13),  -- Classic Anime That Still Shine (fan)
  (14, 28), -- Mental Health and Social Media (interested)
  (14, 31), -- Best Practices for Urban Gardening (interested)
  (14, 53), -- Exploring the World of Miniature Gaming (interested)
  (14, 69), -- Anticipated Sci-Fi Movies of 2024 (fan)

  -- Fiona Gallagher (fiona)
  (15, 4),   -- Budget-Friendly Travel Hacks (expert)
  (15, 20), -- Healthy Eating on a Budget (interested)
  (15, 34), -- Hiking Trails for Beginners (interested)
  (15, 42),  -- Traveling as a Digital Nomad (expert)
  (15, 56), -- Understanding Blockchain (interested)
  (15, 70),  -- Hidden Beaches You Need to Visit (expert)

  -- George Martin (george)
  (16, 5),   -- Building a Fantasy World (expert)
  (16, 15),  -- Creating Believable Characters (expert)
  (16, 21), -- Pirate Legends and Historical Accuracy (interested)
  (16, 36),  -- Creative Writing Prompts (expert)
  (16, 48),  -- Writing Historical Fiction (expert)
  (16, 67), -- Anticipated Sci-Fi Movies of 2024 (interested)

  -- Hannah Montana (hannah)
  (17, 17),  -- Anticipated Sequels of 2024 (interested)
  (17, 8),  -- Staying Motivated to Exercise in the Cold (interested)
  (17, 16), -- Anticipated Sequels of 2024 (interested)
  (17, 25),  -- Pop Culture Trends of the Decade (expert)
  (17, 68), -- The Science Behind Horror Movies (interested)
  (17, 101), -- Superhero Origin Stories Comparison (interested)

  -- Ian Malcolm (ian)
  (18, 23),  -- The Rise of AI (interested)
  (18, 4),  -- Eco-Friendly Travel Tips (interested)
  (18, 9),   -- Game Development Basics (expert)
  (18, 40), -- Environmental Conservation Success Stories (supporter)
  (18, 22), -- Dinosaur Research: Past and Future (expert)
  (18, 45),  -- How to Protect Wildlife Habitats (expert)

  -- Jack Sparrow (jack)
  (19, 4),   -- Budget-Friendly Travel Hacks (expert)
  (19, 21),  -- Pirate Legends and Historical Accuracy (expert)
  (19, 34), -- Hiking Trails for Beginners (interested)
  (19, 42),  -- Traveling as a Digital Nomad (expert)
  (19, 33), -- Pirate Legends and Historical Accuracy (interested)
  (19, 70),  -- Hidden Beaches You Need to Visit (expert)

  -- Katherine Pierce (katherine)
  (20, 7),   -- Tips for Writing Detective Novels (expert)
  (20, 11),  -- Underrated Books to Check Out (expert)
  (20, 20), -- Healthy Eating on a Budget (interested)
  (20, 37), -- Meditation for Beginners (curious)
  (20, 51), -- Yoga Poses for Stress Relief (interested)
  (20, 63), -- Exploring Classic Films of the 20th Century (curious)

  -- Liam Neeson (liam)
  (21, 7),  -- Tips for Writing Detective Novels (interested)
  (21, 12),  -- Anticipated Sequels of 2024 (expert)
  (21, 25), -- Pop Culture Trends of the Decade (interested)
  (21, 68), -- The Science Behind Horror Movies (curious)
  (21, 102), -- Superhero Origin Stories Comparison (interested)
  (21, 63),  -- Exploring Classic Films of the 20th Century (expert)

  -- Monica Geller (monica)
  (22, 6),   -- Cooking Healthy Meals (expert)
  (22, 16), -- Anticipated Sequels of 2024 (interested)
  (22, 20),  -- Healthy Eating on a Budget (expert)
  (22, 34), -- Hiking Trails for Beginners (interested)
  (22, 51), -- Yoga Poses for Stress Relief (interested)
  (22, 79),  -- Top Vegan Recipes for Beginners (expert)

  -- Nina Williams (nina)
  (23, 9),   -- Game Development Basics (expert)
  (23, 19), -- How to Get Started with Coding (interested)
  (23, 30), -- Must-Have Gadgets for 2024 (interested)
  (23, 72),  -- Essential Tools for Digital Artists (expert)
  (23, 91), -- Advancements in Robotics (interested)

  -- Oscar Wilde (oscar)
  (24, 23),  -- Mythology and Modern Storytelling (interested)
  (24, 11),  -- Underrated Books to Check Out (expert)
  (24, 25), -- Pop Culture Trends of the Decade (interested)
  (24, 36),  -- Creative Writing Prompts (expert)
  (24, 48),  -- Writing Historical Fiction (expert)
  (24, 63),  -- Exploring Classic Films of the 20th Century (expert)

  -- Penny Lane (penny)
  (25, 6),  -- Cooking Healthy Meals (interested)
  (25, 16), -- Anticipated Sequels of 2024 (interested)
  (25, 20), -- Healthy Eating on a Budget (interested)
  (25, 34), -- Hiking Trails for Beginners (interested)
  (25, 51), -- Yoga Poses for Stress Relief (interested)
  (25, 79),  -- Top Vegan Recipes for Beginners (expert)

  -- Quentin Tarantino (quentin)
  (26, 7),   -- Tips for Writing Detective Novels (expert)
  (26, 12),  -- Anticipated Sequels of 2024 (expert)
  (26, 25),  -- Pop Culture Trends of the Decade (expert)
  (26, 68),  -- The Science Behind Horror Movies (expert)
  (26, 103),  -- Superhero Origin Stories Comparison (expert)
  (26, 63),  -- Exploring Classic Films of the 20th Century (expert)

  -- Rachel Green (rachel)
  (27, 122),  -- The Psychology of Superheroes (interested)
  (27, 8),  -- Staying Motivated to Exercise in the Cold (interested)
  (27, 16), -- Anticipated Sequels of 2024 (interested)
  (27, 50), -- How to Start a Home Garden (interested)
  (27, 68), -- The Science Behind Horror Movies (curious)
  (27, 104), -- Superhero Origin Stories Comparison (interested)
  -- Steve Rogers (steve)
(28, 123),   -- The Psychology of Superheroes (expert)
(28, 18), -- Environmental Conservation Success Stories (supporter)
(28, 50), -- How to Start a Home Garden (interested)
(28, 68), -- The Science Behind Horror Movies (curious)
(28, 105),  -- Superhero Origin Stories Comparison (expert)
(28, 63), -- Exploring Classic Films of the 20th Century (interested)

-- Tony Stark (tony)
(29, 1),   -- The Rise of AI (expert)
(29, 9),   -- Game Development Basics (expert)
(29, 40), -- Environmental Conservation Success Stories (supporter)
(29, 26), -- Space Exploration Milestones (interested)
(29, 30),  -- Must-Have Gadgets for 2024 (expert)
(29, 61),  -- How AI is Reshaping the Job Market (expert)

-- Ursula K. Le Guin (ursula)
(30, 5),   -- Building a Fantasy World (expert)
(30, 15),  -- Creating Believable Characters (expert)
(30, 21), -- Pirate Legends and Historical Accuracy (interested)
(30, 36),  -- Creative Writing Prompts (expert)
(30, 48),  -- Writing Historical Fiction (expert)
(30, 67), -- Anticipated Sci-Fi Movies of 2024 (interested)

-- Victor Frankenstein (victor)
(31, 23),  -- The Rise of AI (curious)
(31, 9),   -- Game Development Basics (expert)
(31, 40), -- Environmental Conservation Success Stories (supporter)
(31, 22),  -- Dinosaur Research: Past and Future (expert)
(31, 31), -- Best Practices for Urban Gardening (interested)
(31, 61),  -- How AI is Reshaping the Job Market (expert)

-- Will Turner (will)
(32, 4),   -- Budget-Friendly Travel Hacks (expert)
(32, 39),  -- Next-Gen Gaming Technologies (expert)
(32, 34), -- Hiking Trails for Beginners (interested)
(32, 42),  -- Traveling as a Digital Nomad (expert)
(32, 56), -- Understanding Blockchain (interested)
(32, 70),  -- Hidden Beaches You Need to Visit (expert)

-- Xena Warrior (xena)
(33, 124),  -- The Psychology of Superheroes (interested)
(33, 57), -- Environmental Conservation Success Stories (supporter)
(33, 25), -- Pop Culture Trends of the Decade (interested)
(33, 89), -- The Science Behind Horror Movies (curious)
(33, 106),  -- Superhero Origin Stories Comparison (expert)
(33, 63), -- Exploring Classic Films of the 20th Century (interested)

-- Yoda (yoda)
(34, 23),  -- The Rise of AI (curious)
(34, 5),   -- Building a Fantasy World (expert)
(34, 15),  -- Creating Believable Characters (expert)
(34, 21), -- Pirate Legends and Historical Accuracy (interested)
(34, 36),  -- Creative Writing Prompts (expert)
(34, 48),  -- Writing Historical Fiction (expert)

-- Zorro (zorro)
(35, 125),  -- The Psychology of Superheroes (interested)
(35, 57), -- Environmental Conservation Success Stories (supporter)
(35, 25), -- Pop Culture Trends of the Decade (interested)
(35, 89), -- The Science Behind Horror Movies (curious)
(35, 107),  -- Superhero Origin Stories Comparison (expert)
(35, 63), -- Exploring Classic Films of the 20th Century (interested)

-- Albus Dumbledore (albus)
(36, 23),  -- The Rise of AI (curious)
(36, 5),   -- Building a Fantasy World (expert)
(36, 15),  -- Creating Believable Characters (expert)
(36, 21), -- Pirate Legends and Historical Accuracy (interested)
(36, 36),  -- Creative Writing Prompts (expert)
(36, 48),  -- Writing Historical Fiction (expert)

-- Bella Swan (bella)
(37, 5),  -- Building a Fantasy World (interested)
(37, 15), -- Creating Believable Characters (interested)
(37, 21), -- Pirate Legends and Historical Accuracy (interested)
(37, 36), -- Creative Writing Prompts (interested)
(37, 48), -- Writing Historical Fiction (interested)
(37, 67),  -- Anticipated Sci-Fi Movies of 2024 (fan)

-- Clark Kent (clark)
(38, 125),   -- The Psychology of Superheroes (expert)
(38, 18), -- Environmental Conservation Success Stories (supporter)
(38, 38), -- Indie Music Festival Highlights (interested)
(38, 89), -- The Science Behind Horror Movies (curious)
(38, 108),  -- Superhero Origin Stories Comparison (expert)
(38, 63), -- Exploring Classic Films of the 20th Century (interested)

-- Darth Vader (darth)
(39, 23),  -- The Rise of AI (curious)
(39, 5),  -- Building a Fantasy World (interested)
(39, 15), -- Creating Believable Characters (interested)
(39, 21), -- Pirate Legends and Historical Accuracy (interested)
(39, 36), -- Creative Writing Prompts (interested)
(39, 48), -- Writing Historical Fiction (interested)

-- Elliot Alderson (elliot)
(40, 24),   -- The Rise of AI (expert)
(40, 9),   -- Game Development Basics (expert)
(40, 18), -- Environmental Conservation Success Stories (supporter)
(40, 26), -- Space Exploration Milestones (interested)
(40, 30),  -- Must-Have Gadgets for 2024 (expert)
(40, 61),  -- How AI is Reshaping the Job Market (expert)

-- Frodo Baggins (frodo)
(41, 5),   -- Building a Fantasy World (expert)
(41, 15),  -- Creating Believable Characters (expert)
(41, 41), -- Space Exploration Documentary Review(interested)
(41, 36),  -- Creative Writing Prompts (expert)
(41, 48),  -- Writing Historical Fiction (expert)
(41, 67), -- Anticipated Sci-Fi Movies of 2024 (interested)

-- Gandalf the Grey (gandalf)
(42, 24),  -- The Rise of AI (curious)
(42, 5),   -- Building a Fantasy World (expert)
(42, 15),  -- Creating Believable Characters (expert)
(42, 21), -- Pirate Legends and Historical Accuracy (interested)
(42, 36),  -- Creative Writing Prompts (expert)
(42, 48),  -- Writing Historical Fiction (expert)

-- Homer Simpson (homer)
(43, 8),   -- Staying Motivated to Exercise in the Cold (interested)
(43, 16), -- Anticipated Sequels of 2024 (interested)
(43, 25), -- Pop Culture Trends of the Decade (interested)
(43, 89), -- The Science Behind Horror Movies (curious)
(43, 43), -- Cinematography Techniques in Modern Cinema (interested)
(43, 63), -- Exploring Classic Films of the 20th Century (interested)

-- Icarus (icarus)
(44, 1),   -- The Rise of AI (expert)
(44, 9),   -- Game Development Basics (expert)
(44, 18), -- Environmental Conservation Success Stories (supporter)
(44, 26), -- Space Exploration Milestones (interested)
(44, 30),  -- Must-Have Gadgets for 2024 (expert)
(44, 61),  -- How AI is Reshaping the Job Market (expert)

-- Jules Winnfield (jules)
(45, 2),  -- The Psychology of Superheroes (interested)
(45, 18), -- Environmental Conservation Success Stories (supporter)
(45, 25), -- Pop Culture Trends of the Decade (interested)
(45, 89),  -- The Science Behind Horror Movies (expert)
(45, 109),  -- Superhero Origin Stories Comparison (expert)
(45, 63), -- Exploring Classic Films of the 20th Century (interested)

-- Katniss Everdeen (katniss)
(46, 2),  -- The Psychology of Superheroes (interested)
(46, 18), -- Environmental Conservation Success Stories (supporter)
(46, 50), -- How to Start a Home Garden (interested)
(46, 89), -- The Science Behind Horror Movies (curious)
(46, 110), -- Superhero Origin Stories Comparison (interested)
(46, 63), -- Exploring Classic Films of the 20th Century (interested)

-- Lara Croft (lara)
(47, 4),   -- Budget-Friendly Travel Hacks (expert)
(47, 10),  -- Wildlife Photography Tips (expert)
(47, 22),  -- Dinosaur Research: Past and Future (expert)
(47, 34),  -- Hiking Trails for Beginners (expert)
(47, 42),  -- Traveling as a Digital Nomad (expert)
(47, 70),  -- Hidden Beaches You Need to Visit (expert)

-- Marty McFly (marty)
(48, 1),  -- The Rise of AI (curious)
(48, 9),   -- Game Development Basics (expert)
(48, 18), -- Environmental Conservation Success Stories (supporter)
(48, 26), -- Space Exploration Milestones (interested)
(48, 30),  -- Must-Have Gadgets for 2024 (expert)
(48, 61),  -- How AI is Reshaping the Job Market (expert)

-- Nancy Drew (nancy)
(49, 7),   -- Tips for Writing Detective Novels (expert)
(49, 11),  -- Underrated Books to Check Out (expert)
(49, 20), -- Healthy Eating on a Budget (interested)
(49, 37), -- Meditation for Beginners (curious)
(49, 51), -- Yoga Poses for Stress Relief (interested)
(49, 63), -- Exploring Classic Films of the 20th Century (curious)

-- Oliver Twist (oliver)
(50, 2),  -- The Psychology of Superheroes (interested)
(50, 18), -- Environmental Conservation Success Stories (supporter)
(50, 25), -- Pop Culture Trends of the Decade (interested)
(50, 90), -- The Science Behind Horror Movies (curious)
(50, 111), -- Superhero Origin Stories Comparison (interested)
(50, 63), -- Exploring Classic Films of the 20th Century (interested)

-- Pikachu (pikachu)
(51, 3),   -- Top Anime of the Season (expert)
(51, 13),  -- Classic Anime That Still Shine (expert)
(51, 28), -- Mental Health and Social Media (interested)
(51, 31), -- Best Practices for Urban Gardening (interested)
(51, 53), -- Exploring the World of Miniature Gaming (interested)
(51, 69), -- Anticipated Sci-Fi Movies of 2024 (fan)

-- Quasimodo (quasimodo)
(52, 2),  -- The Psychology of Superheroes (interested)
(52, 18), -- Environmental Conservation Success Stories (supporter)
(52, 25), -- Pop Culture Trends of the Decade (interested)
(52, 90), -- The Science Behind Horror Movies (curious)
(52, 112), -- Superhero Origin Stories Comparison (interested)
(52, 63), -- Exploring Classic Films of the 20th Century (interested)

-- R2-D2 (r2d2)
(53, 1),  -- The Rise of AI (curious)
(53, 9),   -- Game Development Basics (expert)
(53, 18), -- Environmental Conservation Success Stories (supporter)
(53, 26),  -- Space Exploration Milestones (expert)
(53, 30),  -- Must-Have Gadgets for 2024 (expert)
(53, 61),  -- How AI is Reshaping the Job Market (expert)

-- SpongeBob SquarePants (spongebob)
(54, 2),  -- The Psychology of Superheroes (interested)
(54, 8),   -- Staying Motivated to Exercise in the Cold (interested)
(54, 16), -- Anticipated Sequels of 2024 (interested)
(54, 54), -- Pop Culture Trends of the Decade (interested)
(54, 90), -- The Science Behind Horror Movies (curious)
(54, 113), -- Superhero Origin Stories Comparison (interested)

-- Thor Odinson (thor)
(55, 2),   -- The Psychology of Superheroes (expert)
(55, 18), -- Environmental Conservation Success Stories (supporter)
(55, 55), -- Pop Culture Trends of the Decade (interested)
(55, 90), -- The Science Behind Horror Movies (curious)
(55, 114),  -- Superhero Origin Stories Comparison (expert)
(55, 63), -- Exploring Classic Films of the 20th Century (interested)

-- Ultron (ultron)
(56, 1),   -- The Rise of AI (expert)
(56, 9),   -- Game Development Basics (expert)
(56, 18), -- Environmental Conservation Success Stories (supporter)
(56, 26), -- Space Exploration Milestones (interested)
(56, 30),  -- Must-Have Gadgets for 2024 (expert)
(56, 61),  -- How AI is Reshaping the Job Market (expert)

-- Violet Parr (violet)
(57, 2),  -- The Psychology of Superheroes (interested)
(57, 18), -- Environmental Conservation Success Stories (supporter)
(57, 57), -- Pop Culture Trends of the Decade (interested)
(57, 90), -- The Science Behind Horror Movies (curious)
(57, 58), -- Superhero Origin Stories Comparison (interested)
(57, 63), -- Exploring Classic Films of the 20th Century (interested)

-- Wolverine (wolverine)
(58, 2),   -- The Psychology of Superheroes (expert)
(58, 18), -- Environmental Conservation Success Stories (supporter)
(58, 56), -- Pop Culture Trends of the Decade (interested)
(58, 90), -- The Science Behind Horror Movies (curious)
(58, 115),  -- Superhero Origin Stories Comparison (expert)
(58, 63), -- Exploring Classic Films of the 20th Century (interested)

-- X-Men (xmen)
(59, 2),   -- The Psychology of Superheroes (expert)
(59, 18), -- Environmental Conservation Success Stories (supporter)
(59, 47), -- Understanding Blockchain (interested)
(59, 90), -- The Science Behind Horror Movies (curious)
(59, 59),  -- Superhero Origin Stories Comparison (expert)
(59, 63), -- Exploring Classic Films of the 20th Century (interested)

-- Bondrewd (bondrewd)
(60, 3),  -- Top Anime of the Season (interested)
(60, 13), -- Classic Anime That Still Shine (interested)
(60, 28), -- Mental Health and Social Media (interested)
(60, 31), -- Best Practices for Urban Gardening (interested)
(60, 46), -- New season confirmed (interested)
(60, 69), -- Anticipated Sci-Fi Movies of 2024 (fan)

-- Nanachi (nanachi)
(61, 3),  -- Top Anime of the Season (interested)
(61, 13), -- Classic Anime That Still Shine (interested)
(61, 28), -- Mental Health and Social Media (interested)
(61, 31), -- Best Practices for Urban Gardening (interested)
(61, 46), -- New season confirmed(interested)
(61, 67), -- Anticipated Sci-Fi Movies of 2024 (fan)

-- Prushka (prushka)
(62, 3),  -- Top Anime of the Season (interested)
(62, 13), -- Classic Anime That Still Shine (interested)
(62, 28), -- Mental Health and Social Media (interested)
(62, 31), -- Best Practices for Urban Gardening (interested)
(62, 46), -- New season confirmed (interested)
(62, 67), -- Anticipated Sci-Fi Movies of 2024 (fan)

-- Geralt of Rivia (geralt)
(63, 5),   -- Building a Fantasy World (expert)
(63, 15),  -- Creating Believable Characters (expert)
(63, 21), -- Pirate Legends and Historical Accuracy (interested)
(63, 36),  -- Creative Writing Prompts (expert)
(63, 48),  -- Writing Historical Fiction (expert)
(63, 67), -- Anticipated Sci-Fi Movies of 2024 (interested)

-- Yennefer of Vengerberg (yennefer)
(64, 5),   -- Building a Fantasy World (expert)
(64, 15),  -- Creating Believable Characters (expert)
(64, 21), -- Pirate Legends and Historical Accuracy (interested)
(64, 36),  -- Creative Writing Prompts (expert)
(64, 48),  -- Writing Historical Fiction (expert)
(64, 67), -- Anticipated Sci-Fi Movies of 2024 (interested)

-- Wednesday Addams (wednesday)
(65, 2),  -- The Psychology of Superheroes (interested)
(65, 18), -- Environmental Conservation Success Stories (supporter)
(65, 47), -- Understanding Blockchain (interested)
(65, 74),  -- The Science Behind Horror Movies (expert)
(65, 60), -- Superhero Origin Stories Comparison (interested)
(65, 63), -- Exploring Classic Films of the 20th Century (interested)

-- Joe Goldberg (joe)
(66, 7),   -- Tips for Writing Detective Novels (expert)
(66, 11),  -- Underrated Books to Check Out (expert)
(66, 20), -- Healthy Eating on a Budget (interested)
(66, 37), -- Meditation for Beginners (curious)
(66, 51), -- Yoga Poses for Stress Relief (interested)
(66, 63), -- Exploring Classic Films of the 20th Century (curious)

-- Eleven (eleven)
(67, 2),  -- The Psychology of Superheroes (interested)
(67, 18), -- Environmental Conservation Success Stories (supporter)
(67, 25), -- Pop Culture Trends of the Decade (interested)
(67, 74), -- The Science Behind Horror Movies (curious)
(67, 60), -- Superhero Origin Stories Comparison (interested)
(67, 63), -- Exploring Classic Films of the 20th Century (interested)

-- Jim Hopper (hopper)
(68, 2),  -- The Psychology of Superheroes (interested)
(68, 18), -- Environmental Conservation Success Stories (supporter)
(68, 25), -- Pop Culture Trends of the Decade (interested)
(68, 74), -- The Science Behind Horror Movies (curious)
(68, 62), -- Superhero Origin Stories Comparison (interested)
(68, 63), -- Exploring Classic Films of the 20th Century (interested)

-- BoJack Horseman (bojack)
(69, 2),  -- The Psychology of Superheroes (interested)
(69, 8),   -- Staying Motivated to Exercise in the Cold (interested)
(69, 16), -- Anticipated Sequels of 2024 (interested)
(69, 25), -- Pop Culture Trends of the Decade (interested)
(69, 74), -- The Science Behind Horror Movies (curious)
(69, 62), -- Superhero Origin Stories Comparison (interested)

-- Nairobi (nairobi)
(70, 4),   -- Budget-Friendly Travel Hacks (expert)
(70, 20), -- Healthy Eating on a Budget (interested)
(70, 34), -- Hiking Trails for Beginners (interested)
(70, 42),  -- Traveling as a Digital Nomad (expert)
(70, 56), -- Understanding Blockchain (interested)
(70, 71),  -- Hidden Beaches You Need to Visit (expert)

-- Michael Wheeler (mike)
(71, 2),  -- The Psychology of Superheroes (interested)
(71, 18), -- Environmental Conservation Success Stories (supporter)
(71, 44), -- eSports: The Future of Competitive Gaming (interested)
(71, 19), -- The Science Behind Horror Movies (curious)
(71, 73), -- Superhero Origin Stories Comparison (interested)
(71, 64), -- Exploring Classic Films of the 20th Century (interested)

-- Ruth Langmore (ruth)
(72, 4),   -- Budget-Friendly Travel Hacks (expert)
(72, 20), -- Healthy Eating on a Budget (interested)
(72, 34), -- Hiking Trails for Beginners (interested)
(72, 42),  -- Traveling as a Digital Nomad (expert)
(72, 56), -- Understanding Blockchain (interested)
(72, 70),  -- Hidden Beaches You Need to Visit (expert)
-- Otis Milburn (otis) (continued)
(73, 2),  -- The Psychology of Superheroes (interested)
(73, 8),   -- Staying Motivated to Exercise in the Cold (interested)
(73, 16), -- Anticipated Sequels of 2024 (interested)
(73, 25), -- Pop Culture Trends of the Decade (interested)
(73, 19), -- The Science Behind Horror Movies (curious)
(73, 73), -- Superhero Origin Stories Comparison (interested)

-- Maeve Wiley (maeve)
(74, 2),  -- The Psychology of Superheroes (interested)
(74, 8),   -- Staying Motivated to Exercise in the Cold (interested)
(74, 16), -- Anticipated Sequels of 2024 (interested)
(74, 25), -- Pop Culture Trends of the Decade (interested)
(74, 19), -- The Science Behind Horror Movies (curious)
(74, 74), -- Superhero Origin Stories Comparison (interested)

-- Marienne Bellamy (marienne)
(75, 75),  -- The Psychology of Superheroes (interested)
(75, 11),  -- Underrated Books to Check Out (expert)
(75, 20), -- Healthy Eating on a Budget (interested)
(75, 37), -- Meditation for Beginners (curious)
(75, 51), -- Yoga Poses for Stress Relief (interested)
(75, 64), -- Exploring Classic Films of the 20th Century (curious)

-- Simon Basset (simon)
(76, 77),  -- The Psychology of Superheroes (interested)
(76, 8),  -- Staying Motivated to Exercise in the Cold (interested)
(76, 16), -- Anticipated Sequels of 2024 (interested)
(76, 25), -- Pop Culture Trends of the Decade (interested)
(76, 35), -- The Science Behind Horror Movies (curious)
(76, 116), -- Superhero Origin Stories Comparison (interested)

-- Luther Hargreeves (luther)
(77, 2),   -- The Psychology of Superheroes (expert)
(77, 43), -- Environmental Conservation Success Stories (supporter)
(77, 25), -- Pop Culture Trends of the Decade (interested)
(77, 35), -- The Science Behind Horror Movies (curious)
(77, 117),  -- Superhero Origin Stories Comparison (expert)
(77, 65), -- Exploring Classic Films of the 20th Century (interested)

-- Klaus Hargreeves (klaus)
(78, 2),  -- The Psychology of Superheroes (interested)
(78, 43), -- Environmental Conservation Success Stories (supporter)
(78, 25), -- Pop Culture Trends of the Decade (interested)
(78, 35), -- The Science Behind Horror Movies (curious)
(78, 118), -- Superhero Origin Stories Comparison (interested)
(78, 65), -- Exploring Classic Films of the 20th Century (interested)

-- Beth Harmon (beth)
(79, 77),  -- The Psychology of Superheroes (interested)
(79, 8),   -- Staying Motivated to Exercise in the Cold (interested)
(79, 16), -- Anticipated Sequels of 2024 (interested)
(79, 25), -- Pop Culture Trends of the Decade (interested)
(79, 35), -- The Science Behind Horror Movies (curious)
(79, 119), -- Superhero Origin Stories Comparison (interested)

-- Tokyo (tokyo)
(80, 4),   -- Budget-Friendly Travel Hacks (expert)
(80, 20), -- Healthy Eating on a Budget (interested)
(80, 34), -- Hiking Trails for Beginners (interested)
(80, 42),  -- Traveling as a Digital Nomad (expert)
(80, 56), -- Understanding Blockchain (interested)
(80, 70),  -- Hidden Beaches You Need to Visit (expert)

-- Dustin Henderson (dustin)
(81, 24),  -- Emerging Tech Trends in 2024 (interested)
(81, 43), -- Environmental Conservation Success Stories (supporter)
(81, 29), -- The Evolution of Detective Fiction (interested)
(81, 35), -- The Science Behind Horror Movies (curious)
(81, 52), -- Superhero Origin Stories Comparison (interested)
(81, 63), -- Exploring Classic Films of the 20th Century (interested)

-- The Professor (professor)
(82, 1),   -- The Rise of AI (expert)
(82, 9),   -- Game Development Basics (expert)
(82, 43), -- Environmental Conservation Success Stories (supporter)
(82, 26), -- Space Exploration Milestones (interested)
(82, 30),  -- Must-Have Gadgets for 2024 (expert)
(82, 61),  -- How AI is Reshaping the Job Market (expert)

-- Mickey Mouse (mickey)
(83, 78),  -- The Psychology of Superheroes (interested)
(83, 8),   -- Staying Motivated to Exercise in the Cold (interested)
(83, 16), -- Anticipated Sequels of 2024 (interested)
(83, 25), -- Pop Culture Trends of the Decade (interested)
(83, 35), -- The Science Behind Horror Movies (curious)
(83, 52), -- Superhero Origin Stories Comparison (interested)

-- Minnie Mouse (minnie)
(84, 78),  -- The Psychology of Superheroes (interested)
(84, 8),   -- Staying Motivated to Exercise in the Cold (interested)
(84, 16), -- Anticipated Sequels of 2024 (interested)
(84, 25), -- Pop Culture Trends of the Decade (interested)
(84, 35), -- The Science Behind Horror Movies (curious)
(84, 52), -- Superhero Origin Stories Comparison (interested)

-- Donald Duck (donald)
(85, 85),  -- The Psychology of Superheroes (interested)
(85, 8),   -- Staying Motivated to Exercise in the Cold (interested)
(85, 16), -- Anticipated Sequels of 2024 (interested)
(85, 25), -- Pop Culture Trends of the Decade (interested)
(85, 35), -- The Science Behind Horror Movies (curious)
(85, 52), -- Superhero Origin Stories Comparison (interested)

-- Goofy Goof (goofy)
(86, 83),  -- The Psychology of Superheroes (interested)
(86, 8),   -- Staying Motivated to Exercise in the Cold (interested)
(86, 16), -- Anticipated Sequels of 2024 (interested)
(86, 25), -- Pop Culture Trends of the Decade (interested)
(86, 35), -- The Science Behind Horror Movies (curious)
(86, 86), -- Superhero Origin Stories Comparison (interested)

-- Daisy Duck (daisy)
(87, 83),  -- The Psychology of Superheroes (interested)
(87, 8),   -- Staying Motivated to Exercise in the Cold (interested)
(87, 16), -- Anticipated Sequels of 2024 (interested)
(87, 25), -- Pop Culture Trends of the Decade (interested)
(87, 35), -- The Science Behind Horror Movies (curious)
(87, 87), -- Superhero Origin Stories Comparison (interested)

-- Pluto (pluto) (continued)
(88, 83),  -- The Psychology of Superheroes (interested)
(88, 8),   -- Staying Motivated to Exercise in the Cold (interested)
(88, 16), -- Anticipated Sequels of 2024 (interested)
(88, 25), -- Pop Culture Trends of the Decade (interested)
(88, 35), -- The Science Behind Horror Movies (curious)
(88, 88), -- Superhero Origin Stories Comparison (interested)

-- Scrooge McDuck (scrooge)
(89, 4),   -- Budget-Friendly Travel Hacks (expert)
(89, 20), -- Healthy Eating on a Budget (interested)
(89, 34), -- Hiking Trails for Beginners (interested)
(89, 42),  -- Traveling as a Digital Nomad (expert)
(89, 89), -- Understanding Blockchain (interested)
(89, 70),  -- Hidden Beaches You Need to Visit (expert)

-- Huey Duck (huey)
(90, 1),  -- The Rise of AI (curious)
(90, 9),   -- Game Development Basics (expert)
(90, 43), -- Environmental Conservation Success Stories (supporter)
(90, 26), -- Space Exploration Milestones (interested)
(90, 90),  -- Must-Have Gadgets for 2024 (expert)
(90, 61),  -- How AI is Reshaping the Job Market (expert)

-- Dewey Duck (dewey)
(91, 1),  -- The Rise of AI (curious)
(91, 9),   -- Game Development Basics (expert)
(91, 43), -- Environmental Conservation Success Stories (supporter)
(91, 26), -- Space Exploration Milestones (interested)
(91, 30),  -- Must-Have Gadgets for 2024 (expert)
(91, 61),  -- How AI is Reshaping the Job Market (expert)

-- Louie Duck (louie)
(92, 1),  -- The Rise of AI (curious)
(92, 9),   -- Game Development Basics (expert)
(92, 40), -- Environmental Conservation Success Stories (supporter)
(92, 26), -- Space Exploration Milestones (interested)
(92, 92),  -- Must-Have Gadgets for 2024 (expert)
(92, 61),  -- How AI is Reshaping the Job Market (expert)

-- Belle (belle)
(93, 82),  -- The Psychology of Superheroes (interested)
(93, 11),  -- Underrated Books to Check Out (expert)
(93, 93), -- Healthy Eating on a Budget (interested)
(93, 37), -- Meditation for Beginners (curious)
(93, 51), -- Yoga Poses for Stress Relief (interested)
(93, 66), -- Exploring Classic Films of the 20th Century (curious)

-- Beast (beast)
(94, 82),  -- The Psychology of Superheroes (interested)
(94, 40), -- Environmental Conservation Success Stories (supporter)
(94, 25), -- Pop Culture Trends of the Decade (interested)
(94, 35), -- The Science Behind Horror Movies (curious)
(94, 94), -- Superhero Origin Stories Comparison (interested)
(94, 66), -- Exploring Classic Films of the 20th Century (interested)

-- Aladdin (aladdin)
(95, 4),   -- Budget-Friendly Travel Hacks (expert)
(95, 20), -- Healthy Eating on a Budget (interested)
(95, 34), -- Hiking Trails for Beginners (interested)
(95, 42),  -- Traveling as a Digital Nomad (expert)
(95, 56), -- Understanding Blockchain (interested)
(95, 70),  -- Hidden Beaches You Need to Visit (expert)

-- Jasmine (jasmine)
(96, 82),  -- The Psychology of Superheroes (interested)
(96, 40), -- Environmental Conservation Success Stories (supporter)
(96, 25), -- Pop Culture Trends of the Decade (interested)
(96, 35), -- The Science Behind Horror Movies (curious)
(96, 96), -- Superhero Origin Stories Comparison (interested)
(96, 63), -- Exploring Classic Films of the 20th Century (interested)

-- Genie (genie)
(97, 14),  -- Budget Friendly Travel Hacks (curious)
(97, 9),   -- Game Development Basics (expert)
(97, 40), -- Environmental Conservation Success Stories (supporter)
(97, 26), -- Space Exploration Milestones (interested)
(97, 97),  -- Must-Have Gadgets for 2024 (expert)
(97, 61),  -- How AI is Reshaping the Job Market (expert)

-- Ariel (ariel)
(98, 4),   -- Budget-Friendly Travel Hacks (expert)
(98, 98), -- Healthy Eating on a Budget (interested)
(98, 34), -- Hiking Trails for Beginners (interested)
(98, 42),  -- Traveling as a Digital Nomad (expert)
(98, 56), -- Understanding Blockchain (interested)
(98, 70),  -- Hidden Beaches You Need to Visit (expert)

-- Sebastian (sebastian)
(99, 84),  -- The Psychology of Superheroes (interested)
(99, 8),   -- Staying Motivated to Exercise in the Cold (interested)
(99, 16), -- Anticipated Sequels of 2024 (interested)
(99, 25), -- Pop Culture Trends of the Decade (interested)
(99, 35), -- The Science Behind Horror Movies (curious)
(99, 99), -- Superhero Origin Stories Comparison (interested)

-- Violet Evergarden (viorettu)
(100, 82),  -- The Psychology of Superheroes (interested)
(100, 11),  -- Underrated Books to Check Out (expert)
(100, 100), -- Healthy Eating on a Budget (interested)
(100, 37), -- Meditation for Beginners (curious)
(100, 51), -- Yoga Poses for Stress Relief (interested)
(100, 63); -- Exploring Classic Films of the 20th Century (curious)





-- Insert comments
-- Comments for the post "The Rise of AI" (post_id 1)
INSERT INTO comments (content, authenticated_user_id, post_id, parent_comment_id) VALUES
('I think AI is definitely the future! But it does raise a lot of ethical questions.', 3, 1, NULL),
('Agreed, especially when it comes to job displacement. It’s a big concern.', 2, 1, 1),
('AI is already revolutionizing industries, but it’s not without its problems.', 3, 1, NULL);

-- Comments for the post "Must-Read Books of 2024" (post_id 2)
INSERT INTO comments (content, authenticated_user_id, post_id, parent_comment_id) VALUES
('I’m really looking forward to the new book by Haruki Murakami!', 4, 2, NULL),
('Which one are you talking about? I didn’t know he had a new release!', 5, 2, 4),
('I think "Harry Potter and the Philosophers Stone" should be on this list, it’s amazing!', 6, 2, NULL);

-- Comments for the post "Top Anime of the Season" (post_id 3)
INSERT INTO comments (content, authenticated_user_id, post_id, parent_comment_id) VALUES
('I’m loving the new season of Re:Zero!', 7, 3, NULL),
('I haven’t started watching that one yet. Is it worth it?', 8, 3, 7),
('You should give it a try! The animation and story are top-notch this season.', 9, 3, 8);

-- Comments for the post "Travel Tips for 2024" (post_id 4)
INSERT INTO comments (content, authenticated_user_id, post_id, parent_comment_id) VALUES
('I’ve been thinking about visiting Japan next year. Any tips?', 10, 4, NULL),
('Japan is a fantastic destination! Try visiting Kyoto for a mix of culture and nature.', 11, 4, 10),
('If you go, make sure to visit the temples! They are breathtaking.', 12, 4, 11);

-- Comments for the post "Building a Fantasy World" (post_id 5)
INSERT INTO comments (content, authenticated_user_id, post_id, parent_comment_id) VALUES
('Great tips! One thing I focus on is creating a rich history for my world.', 13, 5, NULL),
('History is key! It helps create a sense of depth and realism in the world.', 14, 5, 13),
('Absolutely, without a believable history, the world feels flat.', 15, 5, 14);

-- Comments for the post "Cooking Healthy Meals" (post_id 6)
INSERT INTO comments (content, authenticated_user_id, post_id, parent_comment_id) VALUES
('Does anyone have a good recipe for a healthy breakfast smoothie?', 16, 6, NULL),
('I have a great one! Mix spinach, banana, almond milk, and some chia seeds!', 17, 6, 16),
('That sounds delicious! I’m going to try it tomorrow morning.', 18, 6, 17);

-- Comments for the post "Upcoming Movies in 2024" (post_id 7)
INSERT INTO comments (content, authenticated_user_id, post_id, parent_comment_id) VALUES
('I can’t wait for the next superhero movie! It looks epic.', 19, 7, NULL),
('The superhero genre is definitely dominating the market right now.', 20, 7, 19),
('I’m more excited for the horror flicks coming out this year, though.', 21, 7, NULL);

-- Comments for the post "Staying Fit During Winter" (post_id 8)
INSERT INTO comments (content, authenticated_user_id, post_id, parent_comment_id) VALUES
('I find it really hard to stay motivated when it’s so cold outside.', 22, 8, NULL),
('Try indoor workouts like yoga or Pilates! They keep me active through the winter.', 23, 8, 22),
('Great idea! I’ll start with yoga and see how it goes.', 24, 8, 23);

-- Comments for the post "Game Development Basics" (post_id 9)
INSERT INTO comments (content, authenticated_user_id, post_id, parent_comment_id) VALUES
('I’ve been wanting to start game development for a while now. Any beginner resources?', 25, 9, NULL),
('You should definitely check out Unity! It’s beginner-friendly and has tons of tutorials.', 26, 9, 25),
('I second that! Unity’s community is really supportive, too.', 27, 9, 26);

-- Comments for the post "Wildlife Photography Tips" (post_id 10)
INSERT INTO comments (content, authenticated_user_id, post_id, parent_comment_id) VALUES
('What’s the best camera for wildlife photography? I’m looking to upgrade.', 28, 10, NULL),
('I recommend the Canon EOS R5. It’s excellent for wildlife with fast autofocus.', 29, 10, 28),
('The R5 is a beast! I’ve used it for bird photography and it’s amazing.', 30, 10, 29);

-- Comments for the post "AI in Everyday Life" (post_id 1)
INSERT INTO comments (content, authenticated_user_id, post_id, parent_comment_id) VALUES
('I use AI for everyday tasks like shopping recommendations and scheduling.', 31, 1, NULL),
('Same here! It’s convenient, but I do worry about privacy sometimes.', 32, 1, 31),
('That’s a valid concern. AI is great, but the data collection can be a bit unsettling.', 33, 1, 32);

-- Comments for the post "Underrated Books to Check Out" (post_id 2)
INSERT INTO comments (content, authenticated_user_id, post_id, parent_comment_id) VALUES
('I’ve heard a lot about "Title of Book" lately. It’s supposed to be really good.', 34, 2, NULL),
('It really is! It’s one of those hidden gems that didn’t get the attention it deserved.', 35, 2, 34),
('I’ll check it out. Thanks for the recommendation!', 36, 2, 35);

-- Comments for the post "Classic Anime That Still Shine" (post_id 3)
INSERT INTO comments (content, authenticated_user_id, post_id, parent_comment_id) VALUES
('You can’t go wrong with animes like "Attack on Titan" and "Fullmetal Alchemist".', 14, 3, NULL),
('"Made in Abyss" is one of my all-time favorites! The soundtrack alone is iconic.', 60, 3, 37),
('I agree! The music really enhances the experience of the show.', 5, 3, 38);

-- Comments for the post "Budget-Friendly Travel Hacks" (post_id 4)
INSERT INTO comments (content, authenticated_user_id, post_id, parent_comment_id) VALUES
('Anyone got tips for saving money while traveling to Europe?', 40, 4, NULL),
('Try staying in hostels or using Airbnb to cut down on accommodation costs!', 41, 4, 40),
('Also, consider cooking your own meals instead of eating out all the time.', 42, 4, 41);

-- Comments for the post "Creating Believable Characters" (post_id 5)
INSERT INTO comments (content, authenticated_user_id, post_id, parent_comment_id) VALUES
('It’s all about giving your characters flaws. Perfect characters are often boring.', 43, 5, NULL),
('I completely agree. Flaws make characters feel real and relatable.', 44, 5, 43),
('And don’t forget about backstory! It adds depth and motivation to their actions.', 45, 5, 44);

-- Comments for the post "Quick and Healthy Breakfast Ideas" (post_id 6)
INSERT INTO comments (content, authenticated_user_id, post_id, parent_comment_id) VALUES
('I love oatmeal with fruit! It’s filling and nutritious.', 46, 6, NULL),
('Oatmeal is great! You can also add a bit of honey for sweetness.', 47, 6, 46),
('I like to throw some nuts in mine for extra protein.', 48, 6, 47);

-- Comments for the post "Anticipated Sequels of 2024" (post_id 7)
INSERT INTO comments (content, authenticated_user_id, post_id, parent_comment_id) VALUES
('I can’t wait for "Title of Sequel". The first movie was incredible.', 49, 7, NULL),
('Same! The sequel has so much potential. I hope they don’t ruin it.', 50, 7, 49),
('Let’s hope they live up to expectations. The trailer was promising!', 51, 7, 50); --51

-- Comments for posts 51 to 60
INSERT INTO comments (content, authenticated_user_id, post_id, parent_comment_id) VALUES
('K-Pop has definitely evolved this year!', 5, 51, NULL),
('I’m loving the new trends. The fashion aspect is so cool!', 6, 51, 5),
('These gadgets are a game-changer, can’t wait to try some of them!', 10, 52, NULL),
('The VR tech is truly next level, can’t wait to experience it in games.', 9, 52, NULL),
('I still haven’t read enough detective stories from this list! Adding them to my list.', 3, 53, NULL),
('Some of the older ones are classics, but I’m really excited about the new mysteries.', 7, 53, 3),
('The future of VR gaming looks so exciting, especially with the advancements in graphics!', 2, 54, NULL),
('I can’t wait for the new VR titles coming out. It’s amazing how immersive the experience is getting.', 8, 54, 2),
('Thanks for the tips! I’ve been wanting to try writing sci-fi for a while now.', 15, 55, NULL),
('World-building in sci-fi is so intricate. How do you balance science with storytelling?', 11, 55, 15),
('I love how off-road vehicles are getting more powerful each year!', 23, 56, NULL),
('Great list! Looking forward to seeing these vehicles in action.', 40, 56, 23),
('These DIY home renovations are a lifesaver. I’m starting a new project this weekend!', 28, 57, NULL),
('I tried one of the projects last week—totally transformed my living room!', 34, 57, 28),
('Mythical creatures are fascinating, especially how different cultures interpret them.', 22, 58, NULL),
('Do you think any real-world creatures have inspired these myths?', 33, 58, 22),
('Portrait photography is an art! I love experimenting with lighting to get that perfect shot.', 48, 59, NULL),
('Portraits with natural light have always been my favorite. Any tips for shooting outdoors?', 36, 59, 48),
('I’m planning a trip to a secret beach this year! Hope to find a place as peaceful as the one in your post.', 41, 60, NULL),
('Beaches like these make for the best vacations. Hopefully, the crowds stay away!', 7, 60, 41);
--20
-- Comments for posts 61 to 70
INSERT INTO comments (content, authenticated_user_id, post_id, parent_comment_id) VALUES
('I never realized how impactful social media could be on mental health until recently.', 29, 61, NULL),
('It’s tough to disconnect sometimes, especially when everyone is online all the time.', 42, 61, 29),
('Brewing craft beer at home is so rewarding, I love experimenting with new flavors!', 3, 62, NULL),
('I’ve been trying a lot of new recipes. It’s fun to see what works and what doesn’t!', 18, 62, 3),
('AI and automation are definitely changing the job market. Exciting but a little scary!', 11, 63, NULL),
('The balance between human workers and AI will be interesting to watch unfold.', 21, 63, 11),
('Meditation has helped me a lot with stress relief. Starting with small sessions made a huge difference.', 25, 64, NULL),
('I agree! Even five minutes a day can make a difference for mental clarity.', 38, 64, 25),
('The history of jazz is so rich. I’m definitely going to dive into more classic records!', 9, 65, NULL),
('Jazz is timeless! Some of those old-school records still sound amazing today.', 4, 65, 9),
('Starting a fitness journey has been one of the best decisions I’ve made this year.', 8, 66, NULL),
('The hardest part is just getting started, but once you do, it feels great.', 16, 66, 8),
('These classic films really shaped modern cinema. Some of the best movies ever made are from this era.', 17, 67, NULL),
('Such an interesting comparison! I’m a Star Wars fan, but I can appreciate Star Trek’s unique style.', 3, 67, 17),
('How AI is reshaping the creative arts is mind-blowing. It’ll be interesting to see where it goes.', 10, 68, NULL),
('As a photographer, I’m already experimenting with AI-assisted editing. It’s a new world!', 41, 68, 10),
('This article about biking trails has me ready to plan my next adventure!', 24, 69, NULL),
('These trails look incredible! I can’t wait to hit the road this summer.', 39, 69, 24),
('Sustainable fashion is the way forward. Glad to see more eco-friendly options available.', 2, 70, NULL),
('I’ve started switching to sustainable brands. It’s a small step, but it makes a difference!', 5, 70, 2);

-- Comments for posts 71 to 80
INSERT INTO comments (content, authenticated_user_id, post_id, parent_comment_id) VALUES
('This post about detective novels has me so excited to read more! I need new material.', 30, 71, NULL),
('Mystery novels are my favorite! Have you read anything by Agatha Christie?', 26, 71, 30),
('Space exploration is so exciting! I love hearing about all the new advancements.', 18, 72, NULL),
('I can’t wait to see where space technology takes us in the next decade.', 34, 72, 18),
('Building a successful online business requires patience, but it’s worth the effort!', 5, 73, NULL),
('It’s amazing how much you can accomplish with the right tools and mindset.', 22, 73, 5),
('I love that this is all about digital entrepreneurship. It’s a new era of business!', 6, 73, 22),
('The psychology of superheroes is so fascinating! I wonder what draws us to these characters.', 50, 74, NULL),
('I think it’s their human qualities that make them relatable, even though they have superpowers.', 45, 74, 50),
('These hiking gear recommendations are fantastic. I definitely need to invest in better boots!', 12, 75, NULL),
('The right gear makes all the difference when hiking. I can’t wait for my next trail adventure!', 20, 75, 12),
('I’d love to hear more about how technology and scientific innovation are changing everything.', 31, 76, NULL),
('Science is evolving so quickly, it’s hard to keep up sometimes, but it’s exciting!', 19, 76, 31),
('How to train your pet has been so helpful! My dog is finally learning some tricks.', 8, 77, NULL),
('Training my cat, on the other hand, is a challenge, but I’m working on it!', 21, 77, 8),
('These insights into photography are so useful. I’m going to try some of these tips for my next shoot.', 27, 78, NULL),
('Drone photography is so cool. I’d love to get my hands on one of these gadgets!', 15, 78, 27),
('This article on space exploration milestones is so inspiring. Space is the final frontier!', 14, 79, NULL),
('The timeline of space exploration is so fascinating. We’ve come so far in just a few decades.', 13, 79, 14),
('I’m all in for eco-friendly travel. I love that this article focuses on reducing our carbon footprint!', 36, 80, NULL);

DO $$
DECLARE
    user_id INT;
    vote_count INT;
    is_upvote BOOLEAN;
BEGIN
    -- Generate 10,000 random votes for 100 users
    FOR vote_count IN 1..10000 LOOP
        -- Assign a random user ID between 1 and 100
        user_id := (SELECT FLOOR(RANDOM() * 99 + 2)::INT);

        -- Randomize vote type (80% upvote, 20% downvote)
        is_upvote := (RANDOM() < 0.8);

        -- Insert vote
        INSERT INTO votes (upvote, authenticated_user_id)
        VALUES (is_upvote, user_id);
    END LOOP;
END $$;

-- Insert random post_votes, ensuring each post gets between 5 and 50 votes
DO $$
DECLARE
    post_id INT;
    vote_id INT;
    num_votes INT;
    vote_offset INT := 1; -- Keeps track of the vote_id offset for linking
BEGIN
    -- For each post (1 to 125), assign random votes
    FOR post_id IN 1..125 LOOP
        -- Generate a random number of votes for the post (between 5 and 50)
        num_votes := (SELECT FLOOR(RANDOM() * 46 + 5)::INT);

        -- Insert post_votes for this post
        FOR vote_id IN vote_offset..(vote_offset + num_votes - 1) LOOP
            INSERT INTO post_votes (vote_id, post_id)
            VALUES (vote_id, post_id);
        END LOOP;

        -- Update the vote offset to skip used votes
        vote_offset := vote_offset + num_votes;
    END LOOP;
END $$;

-- Insert random comment_votes, ensuring each comment gets between 1 and 30 votes
DO $$
DECLARE
    comment_id INT;
    vote_id INT;
    num_votes INT;
    vote_offset INT := (SELECT MAX(id) FROM post_votes) + 1; -- Start after the last post_vote ID
BEGIN
    -- For each comment (1 to 111), assign random votes
    FOR comment_id IN 1..111 LOOP
        -- Generate a random number of votes for the comment (between 1 and 30)
        num_votes := (SELECT FLOOR(RANDOM() * 30 + 1)::INT);

        -- Insert comment_votes for this comment
        FOR vote_id IN vote_offset..(vote_offset + num_votes - 1) LOOP
            INSERT INTO comment_votes (vote_id, comment_id)
            VALUES (vote_id, comment_id);
        END LOOP;

        -- Update the vote offset to skip used votes
        vote_offset := vote_offset + num_votes;
    END LOOP;
END $$;

-- Insert community followers
INSERT INTO community_followers (authenticated_user_id, community_id) VALUES
(6, 1), -- User 6 is moderator for 'AnimeFans'
(5, 1), -- User 6 is moderator for 'AnimeFans'
(14, 1), -- User 6 is moderator for 'AnimeFans'
(7, 2), -- User 7 is moderator for 'Superheroes'
(2, 2), -- User 7 is moderator for 'Superheroes'
(3, 2), -- User 7 is moderator for 'Superheroes'
(47, 3), -- User 47 is moderator for 'Mythology'
(48, 4), -- User 48 is moderator for 'TechTalk'
(49, 5), -- User 49 is moderator for 'AnimeTheories'
(60, 5), -- User 49 is moderator for 'AnimeTheories'
(14, 5), -- User 49 is moderator for 'AnimeTheories'
(50, 6), -- User 50 is moderator for 'SciFiEnthusiasts'
(51, 7), -- User 51 is moderator for 'MysteryLovers'
(52, 8), -- User 52 is moderator for 'FilmBuffs'
(53, 9), -- User 53 is moderator for 'GamingWorld'
(5, 9), -- User 53 is moderator for 'GamingWorld'
(54, 10), -- User 54 is moderator for 'LiteratureClub'
(60, 11), -- User 55 is moderator for 'AbyssLovers'
(5, 11), -- User 55 is moderator for 'AbyssLovers'
(56, 12), -- User 56 is moderator for 'BookLovers'
(4, 12), -- User 56 is moderator for 'BookLovers'
(3, 12), -- User 56 is moderator for 'BookLovers'
(57, 13), -- User 57 is moderator for 'TravelEnthusiasts'
(58, 14), -- User 58 is moderator for 'FantasyWriters'
(59, 15), -- User 59 is moderator for 'CulinaryArtists'
(55, 16), -- User 60 is moderator for 'MovieBuffs'
(4, 16), -- User 60 is moderator for 'MovieBuffs'
(3, 16), -- User 60 is moderator for 'MovieBuffs'
(2, 16), -- User 60 is moderator for 'MovieBuffs'
(61, 17), -- User 61 is moderator for 'FitnessFanatics'
(4, 17), -- User 61 is moderator for 'FitnessFanatics'
(62, 18), -- User 62 is moderator for 'GameDevelopers'
(63, 19), -- User 63 is moderator for 'NatureLovers'
(64, 20), -- User 64 is moderator for 'MusicProducers'
(65, 21), -- User 65 is moderator for 'HistoryBuffs'
(66, 22), -- User 66 is moderator for 'SportsFans'
(67, 23), -- User 67 is moderator for 'PetLovers'
(5, 23), -- User 67 is moderator for 'PetLovers'
(2, 23), -- User 67 is moderator for 'PetLovers'
(3, 23), -- User 67 is moderator for 'PetLovers'
(4, 23), -- User 67 is moderator for 'PetLovers'
(68, 24), -- User 68 is moderator for 'ScienceGeeks'
(69, 25), -- User 69 is moderator for 'ArtEnthusiasts'
(5, 25), -- User 69 is moderator for 'ArtEnthusiasts'
(4, 25), -- User 69 is moderator for 'ArtEnthusiasts'
(70, 26), -- User 70 is moderator for 'DIYCreators'
(71, 27), -- User 71 is moderator for 'EnvironmentAdvocates'
(72, 28), -- User 72 is moderator for 'CodingWizards'
(2, 28), -- User 72 is moderator for 'CodingWizards'
(3, 28), -- User 72 is moderator for 'CodingWizards'
(73, 29), -- User 73 is moderator for 'HealthGurus'
(74, 30), -- User 74 is moderator for 'CarEnthusiasts'
(75, 31), -- User 75 is moderator for 'PhotographyExperts'
(76, 32), -- User 76 is moderator for 'BoardGameLovers'
(77, 33), -- User 77 is moderator for 'StartupFounders'
(78, 34), -- User 78 is moderator for 'ComedyEnthusiasts'
(2, 34), -- User 78 is moderator for 'ComedyEnthusiasts'
(3, 34), -- User 78 is moderator for 'ComedyEnthusiasts'
(4, 34), -- User 78 is moderator for 'ComedyEnthusiasts'
(5, 34), -- User 78 is moderator for 'ComedyEnthusiasts'
(79, 35), -- User 79 is moderator for 'SpaceEnthusiasts'
(80, 36), -- User 80 is moderator for 'LanguageLearners Hub'
(81, 37), -- User 81 is moderator for 'MentalHealthCommunity'
(82, 38), -- User 82 is moderator for 'MartialArtsPractitioners'
(83, 39), -- User 83 is moderator for 'RemoteWorkHub'
(84, 40), -- User 84 is moderator for 'GuitarPlayers'
(85, 41), -- User 85 is moderator for 'ChessEnthusiasts'
(86, 42), -- User 86 is moderator for 'WildlifeAdvocates'
(87, 43), -- User 87 is moderator for 'CyclingFans'
(88, 44), -- User 88 is moderator for 'InteriorDesigners'
(89, 45), -- User 89 is moderator for 'EconomicsEnthusiasts'
(90, 46), -- User 90 is moderator for 'FilmMakersGuild'
(91, 47), -- User 91 is moderator for 'AstronomyLovers'
(92, 48), -- User 92 is moderator for 'YogaCommunity'
(93, 49), -- User 93 is moderator for 'ComicBookEnthusiasts'
(94, 50), -- User 94 is moderator for 'SocialJusticeWarriors'
(95, 51), -- User 95 is moderator for 'GardeningBeginners'
(96, 52), -- User 96 is moderator for 'PianoEnthusiasts'
(97, 53), -- User 97 is moderator for 'VintageCollectors'
(98, 54), -- User 98 is moderator for 'OutdoorAdventurers'
(99, 55), -- User 99 is moderator for 'PoliticalEnthusiasts'
(100, 56), -- User 100 is moderator for 'BakingEnthusiasts'
(53, 57), -- User 101 is moderator for 'HomebrewingExperts'
(91, 58), -- User 102 is moderator for 'AIInnovators'
(24, 59), -- User 103 is moderator for 'SurfingEnthusiasts'
(46, 60), -- User 104 is moderator for 'ClassicMovieFans'
(7, 61), -- User 105 is moderator for 'CosplayArtists'
(19, 62), -- User 106 is moderator for 'AdventureTravelers'
(78, 63), -- User 107 is moderator for 'ScienceFictionEnthusiasts'
(18, 64), -- User 108 is moderator for 'PetTrainersGroup'
(92, 65), -- User 109 is moderator for 'MountainClimbers'
(15, 66), -- User 110 is moderator for 'BeachEnthusiasts'
(11, 67), -- User 111 is moderator for 'K-PopCommunity'
(14, 67), -- User 111 is moderator for 'K-PopCommunity'
(62, 68), -- User 112 is moderator for 'DigitalArtHub'
(4, 68), -- User 112 is moderator for 'DigitalArtHub'
(5, 68), -- User 112 is moderator for 'DigitalArtHub'
(13, 69), -- User 113 is moderator for 'EntrepreneursNetwork'
(11, 70), -- User 114 is moderator for 'BoardGamersCircle'
(35, 71), -- User 115 is moderator for 'CatLoversClub'
(2, 71), -- User 115 is moderator for 'CatLoversClub'
(36, 72), -- User 116 is moderator for 'MovieDirectors'
(47, 73), -- User 117 is moderator for 'SkiingEnthusiasts'
(58, 74), -- User 118 is moderator for 'DroneHobbyists'
(69, 75), -- User 119 is moderator for 'VeganCommunity'
(70, 76), -- User 120 is moderator for 'GraphicDesigners'
(72, 77), -- User 121 is moderator for 'BirdWatchingEnthusiasts'
(82, 78), -- User 122 is moderator for 'SculptorsNetwork'
(23, 79), -- User 123 is moderator for 'FictionWritersHub'
(24, 80), -- User 124 is moderator for 'SneakerCollectorsGroup'
(25, 81), -- User 125 is moderator for 'RoboticsEnthusiasts'
(26, 82), -- User 126 is moderator for 'FishingFans'
(27, 83), -- User 127 is moderator for 'DroneExperts'
(28, 84), -- User 128 is moderator for 'AquariumEnthusiasts'
(29, 85), -- User 129 is moderator for 'HorrorMovieFans'
(30, 86), -- User 130 is moderator for 'LandscapePhotographers'
(31, 87), -- User 131 is moderator for 'TattooArtists'
(32, 88), -- User 132 is moderator for 'MobileGamersHub'
(33, 89), -- User 133 is moderator for 'HomeImprovementGurus'
(34, 90), -- User 134 is moderator for 'VirtualRealityFans'
(35, 91), -- User 135 is moderator for 'ClassicCarEnthusiasts'
(36, 92), -- User 136 is moderator for 'SculptingArtists'
(37, 93), -- User 137 is moderator for 'TechnologyTrends'
(38, 94), -- User 138 is moderator for 'PhotographyMasters'
(39, 95), -- User 139 is moderator for 'SpaceExplorers'
(40, 96), -- User 140 is moderator for 'WildlifePhotographers'
(41, 97), -- User 141 is moderator for 'SustainabilityEnthusiasts'
(42, 98), -- User 142 is moderator for 'CreativeDesigners'
(43, 99), -- User 143 is moderator for 'AICommunity'
(44, 100), -- User 144 is moderator for 'TechPioneers'

(2, 1), -- User 2 follows 'AnimeFans'
(3, 1), -- User 3 follows 'AnimeFans'
(4, 1), -- User 4 follows 'AnimeFans'
(5, 2), -- User 5 follows 'Superheroes'
(6, 2), -- User 6 follows 'Superheroes'
(7, 3), -- User 7 follows 'Mythology'
(8, 3), -- User 8 follows 'Mythology'
(9, 3), -- User 9 follows 'Mythology'
(10, 3), -- User 10 follows 'Mythology'
(11, 4), -- User 11 follows 'TechTalk'
(12, 4), -- User 12 follows 'TechTalk'
(13, 4), -- User 13 follows 'TechTalk'
(14, 4), -- User 14 follows 'TechTalk'
(15, 5), -- User 15 follows 'AnimeTheories'
(16, 5), -- User 16 follows 'AnimeTheories'
(17, 5), -- User 17 follows 'AnimeTheories'
(18, 5), -- User 18 follows 'AnimeTheories'
(19, 6), -- User 19 follows 'SciFiEnthusiasts'
(20, 6), -- User 20 follows 'SciFiEnthusiasts'
(21, 6), -- User 21 follows 'SciFiEnthusiasts'
(22, 6), -- User 22 follows 'SciFiEnthusiasts'
(23, 7), -- User 23 follows 'MysteryLovers'
(24, 7), -- User 24 follows 'MysteryLovers'
(25, 7), -- User 25 follows 'MysteryLovers'
(26, 7), -- User 26 follows 'MysteryLovers'
(27, 8), -- User 27 follows 'FilmBuffs'
(28, 8), -- User 28 follows 'FilmBuffs'
(29, 8), -- User 29 follows 'FilmBuffs'
(30, 8), -- User 30 follows 'FilmBuffs'
(31, 9), -- User 31 follows 'GamingWorld'
(32, 9), -- User 32 follows 'GamingWorld'
(33, 9), -- User 33 follows 'GamingWorld'
(34, 9), -- User 34 follows 'GamingWorld'
(35, 10), -- User 35 follows 'LiteratureClub'
(36, 10), -- User 36 follows 'LiteratureClub'
(37, 10), -- User 37 follows 'LiteratureClub'
(38, 10), -- User 38 follows 'LiteratureClub'
(39, 11), -- User 39 follows 'AbyssLovers'
(40, 11), -- User 40 follows 'AbyssLovers'
(41, 11), -- User 41 follows 'AbyssLovers'
(42, 11), -- User 42 follows 'AbyssLovers'
(43, 12), -- User 43 follows 'BookLovers'
(44, 12), -- User 44 follows 'BookLovers'
(45, 12), -- User 45 follows 'BookLovers'
(46, 12), -- User 46 follows 'BookLovers'
(47, 13), -- User 47 follows 'TravelEnthusiasts'
(48, 13), -- User 48 follows 'TravelEnthusiasts'
(49, 13), -- User 49 follows 'TravelEnthusiasts'
(50, 13), -- User 50 follows 'TravelEnthusiasts'
(51, 14), -- User 51 follows 'FantasyWriters'
(52, 14), -- User 52 follows 'FantasyWriters'
(53, 14), -- User 53 follows 'FantasyWriters'
(54, 14), -- User 54 follows 'FantasyWriters'
(55, 15), -- User 55 follows 'CulinaryArtists'
(56, 15), -- User 56 follows 'CulinaryArtists'
(57, 15), -- User 57 follows 'CulinaryArtists'
(58, 15), -- User 58 follows 'CulinaryArtists'
(59, 16), -- User 59 follows 'MovieBuffs'
(60, 16), -- User 60 follows 'MovieBuffs'
(61, 16), -- User 61 follows 'MovieBuffs'
(62, 16), -- User 62 follows 'MovieBuffs'
(63, 17), -- User 63 follows 'FitnessFanatics'
(64, 17), -- User 64 follows 'FitnessFanatics'
(65, 17), -- User 65 follows 'FitnessFanatics'
(66, 17), -- User 66 follows 'FitnessFanatics'
(67, 18), -- User 67 follows 'GameDevelopers'
(68, 18), -- User 68 follows 'GameDevelopers'
(69, 18), -- User 69 follows 'GameDevelopers'
(70, 18), -- User 70 follows 'GameDevelopers'
(71, 19), -- User 71 follows 'NatureLovers'
(72, 19), -- User 72 follows 'NatureLovers'
(73, 19), -- User 73 follows 'NatureLovers'
(74, 19), -- User 74 follows 'NatureLovers'
(75, 20), -- User 75 follows 'MusicProducers'
(76, 20), -- User 76 follows 'MusicProducers'
(77, 20), -- User 77 follows 'MusicProducers'
(78, 20), -- User 78 follows 'MusicProducers'
(79, 21), -- User 79 follows 'HistoryBuffs'
(80, 21), -- User 80 follows 'HistoryBuffs'
(81, 21), -- User 81 follows 'HistoryBuffs'
(82, 21), -- User 82 follows 'HistoryBuffs'
(83, 22), -- User 83 follows 'SportsFans'
(84, 22), -- User 84 follows 'SportsFans'
(85, 22), -- User 85 follows 'SportsFans'
(86, 22), -- User 86 follows 'SportsFans'
(2, 3), -- User 2 follows 'Mythology'
(3, 3), -- User 3 follows 'Mythology'
(4, 3), -- User 4 follows 'Mythology'
(5, 3), -- User 5 follows 'Mythology'
(6, 3), -- User 6 follows 'Mythology'
(7, 4), -- User 7 follows 'TechTalk'
(8, 4), -- User 8 follows 'TechTalk'
(9, 4), -- User 9 follows 'TechTalk'
(10, 4), -- User 10 follows 'TechTalk'
(11, 5), -- User 11 follows 'AnimeTheories'
(12, 5), -- User 12 follows 'AnimeTheories'
(13, 5), -- User 13 follows 'AnimeTheories'
(15, 6), -- User 15 follows 'SciFiEnthusiasts'
(16, 6), -- User 16 follows 'SciFiEnthusiasts'
(17, 6), -- User 17 follows 'SciFiEnthusiasts'
(18, 6), -- User 18 follows 'SciFiEnthusiasts'
(19, 7), -- User 19 follows 'MysteryLovers'
(20, 7), -- User 20 follows 'MysteryLovers'
(21, 7), -- User 21 follows 'MysteryLovers'
(22, 7), -- User 22 follows 'MysteryLovers'
(23, 8), -- User 23 follows 'FilmBuffs'
(24, 8), -- User 24 follows 'FilmBuffs'
(25, 8), -- User 25 follows 'FilmBuffs'
(26, 8), -- User 26 follows 'FilmBuffs'
(27, 9), -- User 27 follows 'GamingWorld'
(28, 9), -- User 28 follows 'GamingWorld'
(29, 9), -- User 29 follows 'GamingWorld'
(30, 9), -- User 30 follows 'GamingWorld'
(31, 10), -- User 31 follows 'LiteratureClub'
(32, 10), -- User 32 follows 'LiteratureClub'
(33, 10), -- User 33 follows 'LiteratureClub'
(34, 10), -- User 34 follows 'LiteratureClub'
(35, 11), -- User 35 follows 'AbyssLovers'
(36, 11), -- User 36 follows 'AbyssLovers'
(37, 11), -- User 37 follows 'AbyssLovers'
(38, 11), -- User 38 follows 'AbyssLovers'
(39, 12), -- User 39 follows 'BookLovers'
(40, 12), -- User 40 follows 'BookLovers'
(41, 12), -- User 41 follows 'BookLovers'
(42, 12), -- User 42 follows 'BookLovers'
(43, 13), -- User 43 follows 'TravelEnthusiasts'
(44, 13), -- User 44 follows 'TravelEnthusiasts'
(45, 13), -- User 45 follows 'TravelEnthusiasts'
(46, 13), -- User 46 follows 'TravelEnthusiasts'
(47, 14), -- User 47 follows 'FantasyWriters'
(48, 14), -- User 48 follows 'FantasyWriters'
(49, 14), -- User 49 follows 'FantasyWriters'
(50, 14), -- User 50 follows 'FantasyWriters'
(51, 15), -- User 51 follows 'CulinaryArtists'
(52, 15), -- User 52 follows 'CulinaryArtists'
(53, 15), -- User 53 follows 'CulinaryArtists'
(54, 15), -- User 54 follows 'CulinaryArtists'
(56, 16), -- User 56 follows 'MovieBuffs'
(57, 16), -- User 57 follows 'MovieBuffs'
(58, 16), -- User 58 follows 'MovieBuffs'
(59, 17), -- User 59 follows 'FitnessFanatics'
(60, 17), -- User 60 follows 'FitnessFanatics'
(62, 17), -- User 62 follows 'FitnessFanatics'
(63, 18), -- User 63 follows 'GameDevelopers'
(64, 18), -- User 64 follows 'GameDevelopers'
(65, 18), -- User 65 follows 'GameDevelopers'
(66, 18), -- User 66 follows 'GameDevelopers'
(67, 19), -- User 67 follows 'NatureLovers'
(68, 19), -- User 68 follows 'NatureLovers'
(69, 19), -- User 69 follows 'NatureLovers'
(70, 19), -- User 70 follows 'NatureLovers'
(71, 20), -- User 71 follows 'MusicProducers'
(72, 20), -- User 72 follows 'MusicProducers'
(73, 20), -- User 73 follows 'MusicProducers'
(74, 20), -- User 74 follows 'MusicProducers'
(75, 21), -- User 75 follows 'HistoryBuffs'
(76, 21), -- User 76 follows 'HistoryBuffs'
(77, 21), -- User 77 follows 'HistoryBuffs'
(78, 21), -- User 78 follows 'HistoryBuffs'
(79, 22), -- User 79 follows 'SportsFans'
(80, 22), -- User 80 follows 'SportsFans'
(81, 22), -- User 81 follows 'SportsFans'
(82, 22), -- User 82 follows 'SportsFans'
(83, 23), -- User 83 follows 'PetLovers'
(84, 23), -- User 84 follows 'PetLovers'
(85, 23), -- User 85 follows 'PetLovers'
(86, 23), -- User 86 follows 'PetLovers'
(87, 24), -- User 87 follows 'ScienceGeeks'
(88, 24), -- User 88 follows 'ScienceGeeks'
(89, 24), -- User 89 follows 'ScienceGeeks'
(90, 24), -- User 90 follows 'ScienceGeeks'
(91, 25), -- User 91 follows 'ArtEnthusiasts'
(92, 25), -- User 92 follows 'ArtEnthusiasts'
(93, 25), -- User 93 follows 'ArtEnthusiasts'
(94, 25), -- User 94 follows 'ArtEnthusiasts'
(95, 26), -- User 95 follows 'DIYCreators'
(96, 26), -- User 96 follows 'DIYCreators'
(97, 26), -- User 97 follows 'DIYCreators'
(98, 26), -- User 98 follows 'DIYCreators'
(99, 27), -- User 99 follows 'EnvironmentAdvocates'
(100, 27), -- User 100 follows 'EnvironmentAdvocates'
(5, 29), -- User 5 follows 'HealthGurus'
(6, 29), -- User 6 follows 'HealthGurus'
(7, 29), -- User 7 follows 'HealthGurus'
(8, 29), -- User 8 follows 'HealthGurus'
(9, 30), -- User 9 follows 'CarEnthusiasts'
(10, 30), -- User 10 follows 'CarEnthusiasts'
(11, 30), -- User 11 follows 'CarEnthusiasts'
(12, 30), -- User 12 follows 'CarEnthusiasts'
(13, 31), -- User 13 follows 'PhotographyExperts'
(14, 31), -- User 14 follows 'PhotographyExperts'
(15, 31), -- User 15 follows 'PhotographyExperts'
(16, 31), -- User 16 follows 'PhotographyExperts'
(17, 32), -- User 17 follows 'BoardGameLovers'
(18, 32), -- User 18 follows 'BoardGameLovers'
(19, 32), -- User 19 follows 'BoardGameLovers'
(20, 32), -- User 20 follows 'BoardGameLovers'
(21, 33), -- User 21 follows 'StartupFounders'
(22, 33), -- User 22 follows 'StartupFounders'
(23, 33), -- User 23 follows 'StartupFounders'
(24, 33), -- User 24 follows 'StartupFounders'
(25, 34), -- User 25 follows 'ComedyEnthusiasts'
(26, 34), -- User 26 follows 'ComedyEnthusiasts'
(27, 34), -- User 27 follows 'ComedyEnthusiasts'
(28, 34), -- User 28 follows 'ComedyEnthusiasts'
(29, 35), -- User 29 follows 'SpaceEnthusiasts'
(30, 35), -- User 30 follows 'SpaceEnthusiasts'
(31, 35), -- User 31 follows 'SpaceEnthusiasts'
(32, 35), -- User 32 follows 'SpaceEnthusiasts'
(33, 36), -- User 33 follows 'LanguageLearners Hub'
(34, 36), -- User 34 follows 'LanguageLearners Hub'
(35, 36), -- User 35 follows 'LanguageLearners Hub'
(36, 36), -- User 36 follows 'LanguageLearners Hub'
(37, 37), -- User 37 follows 'MentalHealthCommunity'
(38, 37), -- User 38 follows 'MentalHealthCommunity'
(39, 37), -- User 39 follows 'MentalHealthCommunity'
(40, 37), -- User 40 follows 'MentalHealthCommunity'
(41, 38), -- User 41 follows 'MartialArtsPractitioners'
(42, 38), -- User 42 follows 'MartialArtsPractitioners'
(43, 38), -- User 43 follows 'MartialArtsPractitioners'
(44, 38), -- User 44 follows 'MartialArtsPractitioners'
(45, 39), -- User 45 follows 'RemoteWorkHub'
(46, 39), -- User 46 follows 'RemoteWorkHub'
(47, 39), -- User 47 follows 'RemoteWorkHub'
(48, 39), -- User 48 follows 'RemoteWorkHub'
(49, 40), -- User 49 follows 'GuitarPlayers'
(50, 40), -- User 50 follows 'GuitarPlayers'
(51, 40), -- User 51 follows 'GuitarPlayers'
(52, 40), -- User 52 follows 'GuitarPlayers'
(53, 41), -- User 53 follows 'ChessEnthusiasts'
(54, 41), -- User 54 follows 'ChessEnthusiasts'
(55, 41), -- User 55 follows 'ChessEnthusiasts'
(56, 41), -- User 56 follows 'ChessEnthusiasts'
(57, 42), -- User 57 follows 'WildlifeAdvocates'
(58, 42), -- User 58 follows 'WildlifeAdvocates'
(59, 42), -- User 59 follows 'WildlifeAdvocates'
(60, 42), -- User 60 follows 'WildlifeAdvocates'
(61, 43), -- User 61 follows 'CyclingFans'
(62, 43), -- User 62 follows 'CyclingFans'
(63, 43), -- User 63 follows 'CyclingFans'
(64, 43), -- User 64 follows 'CyclingFans'
(65, 44), -- User 65 follows 'InteriorDesigners'
(66, 44), -- User 66 follows 'InteriorDesigners'
(67, 44), -- User 67 follows 'InteriorDesigners'
(68, 44), -- User 68 follows 'InteriorDesigners'
(69, 45), -- User 69 follows 'EconomicsEnthusiasts'
(70, 45), -- User 70 follows 'EconomicsEnthusiasts'
(71, 45), -- User 71 follows 'EconomicsEnthusiasts'
(72, 45), -- User 72 follows 'EconomicsEnthusiasts'
(73, 46), -- User 73 follows 'FilmMakersGuild'
(74, 46), -- User 74 follows 'FilmMakersGuild'
(75, 46), -- User 75 follows 'FilmMakersGuild'
(76, 46), -- User 76 follows 'FilmMakersGuild'
(77, 47), -- User 77 follows 'AstronomyLovers'
(78, 47), -- User 78 follows 'AstronomyLovers'
(79, 47), -- User 79 follows 'AstronomyLovers'
(80, 47), -- User 80 follows 'AstronomyLovers'
(81, 48), -- User 81 follows 'YogaCommunity'
(82, 48), -- User 82 follows 'YogaCommunity'
(83, 48), -- User 83 follows 'YogaCommunity'
(84, 48), -- User 84 follows 'YogaCommunity'
(85, 49), -- User 85 follows 'ComicBookEnthusiasts'
(86, 49), -- User 86 follows 'ComicBookEnthusiasts'
(87, 49), -- User 87 follows 'ComicBookEnthusiasts'
(88, 49), -- User 88 follows 'ComicBookEnthusiasts'
(89, 50), -- User 89 follows 'SocialJusticeWarriors'
(90, 50), -- User 90 follows 'SocialJusticeWarriors'
(91, 50), -- User 91 follows 'SocialJusticeWarriors'
(92, 50), -- User 92 follows 'SocialJusticeWarriors'
(93, 51), -- User 93 follows 'GardeningBeginners'
(94, 51), -- User 94 follows 'GardeningBeginners'
(96, 51), -- User 96 follows 'GardeningBeginners'
(97, 52), -- User 97 follows 'PianoEnthusiasts'
(98, 52), -- User 98 follows 'PianoEnthusiasts'
(99, 52), -- User 99 follows 'PianoEnthusiasts'
(100, 52), -- User 100 follows 'PianoEnthusiasts'
(2, 53), -- User 2 follows 'VintageCollectors'
(3, 53), -- User 3 follows 'VintageCollectors'
(4, 53), -- User 4 follows 'VintageCollectors'
(5, 54), -- User 5 follows 'OutdoorAdventurers'
(6, 54), -- User 6 follows 'OutdoorAdventurers'
(7, 54), -- User 7 follows 'OutdoorAdventurers'
(8, 54), -- User 8 follows 'OutdoorAdventurers'
(9, 55), -- User 9 follows 'PoliticalEnthusiasts'
(10, 55), -- User 10 follows 'PoliticalEnthusiasts'
(11, 55), -- User 11 follows 'PoliticalEnthusiasts'
(12, 55), -- User 12 follows 'PoliticalEnthusiasts'
(13, 56), -- User 13 follows 'BakingEnthusiasts'
(14, 56), -- User 14 follows 'BakingEnthusiasts'
(15, 56), -- User 15 follows 'BakingEnthusiasts'
(16, 56), -- User 16 follows 'BakingEnthusiasts'
(17, 57), -- User 17 follows 'HomebrewingExperts'
(18, 57), -- User 18 follows 'HomebrewingExperts'
(19, 57), -- User 19 follows 'HomebrewingExperts'
(20, 57), -- User 20 follows 'HomebrewingExperts'
(21, 58), -- User 21 follows 'AIInnovators'
(22, 58), -- User 22 follows 'AIInnovators'
(23, 58), -- User 23 follows 'AIInnovators'
(24, 58), -- User 24 follows 'AIInnovators'
(25, 59), -- User 25 follows 'SurfingEnthusiasts'
(26, 59), -- User 26 follows 'SurfingEnthusiasts'
(27, 59), -- User 27 follows 'SurfingEnthusiasts'
(28, 59), -- User 28 follows 'SurfingEnthusiasts'
(29, 60), -- User 29 follows 'ClassicMovieFans'
(30, 60), -- User 30 follows 'ClassicMovieFans'
(31, 60), -- User 31 follows 'ClassicMovieFans'
(32, 60), -- User 32 follows 'ClassicMovieFans'
(33, 61), -- User 33 follows 'CosplayArtists'
(34, 61), -- User 34 follows 'CosplayArtists'
(35, 61), -- User 35 follows 'CosplayArtists'
(36, 61), -- User 36 follows 'CosplayArtists'
(37, 62), -- User 37 follows 'AdventureTravelers'
(38, 62), -- User 38 follows 'AdventureTravelers'
(39, 62), -- User 39 follows 'AdventureTravelers'
(40, 62), -- User 40 follows 'AdventureTravelers'
(41, 63), -- User 41 follows 'ScienceFictionEnthusiasts'
(42, 63), -- User 42 follows 'ScienceFictionEnthusiasts'
(43, 63), -- User 43 follows 'ScienceFictionEnthusiasts'
(44, 63), -- User 44 follows 'ScienceFictionEnthusiasts'
(45, 64), -- User 45 follows 'PetTrainersGroup'
(46, 64), -- User 46 follows 'PetTrainersGroup'
(47, 64), -- User 47 follows 'PetTrainersGroup'
(48, 64), -- User 48 follows 'PetTrainersGroup'
(49, 65), -- User 49 follows 'MountainClimbers'
(50, 65), -- User 50 follows 'MountainClimbers'
(51, 65), -- User 51 follows 'MountainClimbers'
(52, 65), -- User 52 follows 'MountainClimbers'
(53, 66), -- User 53 follows 'BeachEnthusiasts'
(54, 66), -- User 54 follows 'BeachEnthusiasts'
(55, 66), -- User 55 follows 'BeachEnthusiasts'
(56, 66), -- User 56 follows 'BeachEnthusiasts'
(57, 67), -- User 57 follows 'K-PopCommunity'
(58, 67), -- User 58 follows 'K-PopCommunity'
(59, 67), -- User 59 follows 'K-PopCommunity'
(60, 67), -- User 60 follows 'K-PopCommunity'
(61, 68), -- User 61 follows 'DigitalArtHub'
(63, 68), -- User 63 follows 'DigitalArtHub'
(64, 68), -- User 64 follows 'DigitalArtHub'
(65, 69), -- User 65 follows 'EntrepreneursNetwork'
(66, 69), -- User 66 follows 'EntrepreneursNetwork'
(67, 69), -- User 67 follows 'EntrepreneursNetwork'
(68, 69), -- User 68 follows 'EntrepreneursNetwork'
(69, 70), -- User 69 follows 'BoardGamersCircle'
(70, 70), -- User 70 follows 'BoardGamersCircle'
(71, 70), -- User 71 follows 'BoardGamersCircle'
(72, 70), -- User 72 follows 'BoardGamersCircle'
(73, 71), -- User 73 follows 'CatLoversClub'
(74, 71), -- User 74 follows 'CatLoversClub'
(75, 71), -- User 75 follows 'CatLoversClub'
(76, 71), -- User 76 follows 'CatLoversClub'
(77, 72), -- User 77 follows 'MovieDirectors'
(78, 72), -- User 78 follows 'MovieDirectors'
(79, 72), -- User 79 follows 'MovieDirectors'
(80, 72), -- User 80 follows 'MovieDirectors'
(81, 73), -- User 81 follows 'SkiingEnthusiasts'
(82, 73), -- User 82 follows 'SkiingEnthusiasts'
(83, 73), -- User 83 follows 'SkiingEnthusiasts'
(84, 73), -- User 84 follows 'SkiingEnthusiasts'
(85, 74), -- User 85 follows 'DroneHobbyists'
(86, 74), -- User 86 follows 'DroneHobbyists'
(87, 74), -- User 87 follows 'DroneHobbyists'
(88, 74), -- User 88 follows 'DroneHobbyists'
(89, 75), -- User 89 follows 'VeganCommunity'
(90, 75), -- User 90 follows 'VeganCommunity'
(91, 75), -- User 91 follows 'VeganCommunity'
(92, 75), -- User 92 follows 'VeganCommunity'
(93, 76), -- User 93 follows 'GraphicDesigners'
(94, 76), -- User 94 follows 'GraphicDesigners'
(95, 76), -- User 95 follows 'GraphicDesigners'
(96, 76), -- User 96 follows 'GraphicDesigners'
(97, 77), -- User 97 follows 'BirdWatchingEnthusiasts'
(98, 77), -- User 98 follows 'BirdWatchingEnthusiasts'
(99, 77), -- User 99 follows 'BirdWatchingEnthusiasts'
(100, 77), -- User 100 follows 'BirdWatchingEnthusiasts'
(2, 78), -- User 2 follows 'SculptorsNetwork'
(3, 78), -- User 3 follows 'SculptorsNetwork'
(4, 78), -- User 4 follows 'SculptorsNetwork'
(5, 79), -- User 5 follows 'FictionWritersHub'
(6, 79), -- User 6 follows 'FictionWritersHub'
(7, 79), -- User 7 follows 'FictionWritersHub'
(8, 79), -- User 8 follows 'FictionWritersHub'
(9, 80), -- User 9 follows 'SneakerCollectorsGroup'
(10, 80), -- User 10 follows 'SneakerCollectorsGroup'
(11, 80), -- User 11 follows 'SneakerCollectorsGroup'
(12, 80), -- User 12 follows 'SneakerCollectorsGroup'
(13, 81), -- User 13 follows 'RoboticsEnthusiasts'
(14, 81), -- User 14 follows 'RoboticsEnthusiasts'
(15, 81), -- User 15 follows 'RoboticsEnthusiasts'
(16, 81), -- User 16 follows 'RoboticsEnthusiasts'
(17, 82), -- User 17 follows 'FishingFans'
(18, 82), -- User 18 follows 'FishingFans'
(19, 82), -- User 19 follows 'FishingFans'
(20, 82), -- User 20 follows 'FishingFans'
(21, 83), -- User 21 follows 'DroneExperts'
(22, 83), -- User 22 follows 'DroneExperts'
(23, 83), -- User 23 follows 'DroneExperts'
(24, 83), -- User 24 follows 'DroneExperts'
(25, 84), -- User 25 follows 'AquariumEnthusiasts'
(26, 84), -- User 26 follows 'AquariumEnthusiasts'
(27, 84), -- User 27 follows 'AquariumEnthusiasts'
(30, 85), -- User 30 follows 'HorrorMovieFans'
(31, 85), -- User 31 follows 'HorrorMovieFans'
(32, 85), -- User 32 follows 'HorrorMovieFans'
(33, 86), -- User 33 follows 'LandscapePhotographers'
(34, 86), -- User 34 follows 'LandscapePhotographers'
(35, 86), -- User 35 follows 'LandscapePhotographers'
(36, 86), -- User 36 follows 'LandscapePhotographers'
(37, 87), -- User 37 follows 'TattooArtists'
(38, 87), -- User 38 follows 'TattooArtists'
(39, 87), -- User 39 follows 'TattooArtists'
(40, 87), -- User 40 follows 'TattooArtists'
(41, 88), -- User 41 follows 'MobileGamersHub'
(42, 88), -- User 42 follows 'MobileGamersHub'
(43, 88), -- User 43 follows 'MobileGamersHub'
(44, 88), -- User 44 follows 'MobileGamersHub'
(45, 89), -- User 45 follows 'HomeImprovementGurus'
(46, 89), -- User 46 follows 'HomeImprovementGurus'
(47, 89), -- User 47 follows 'HomeImprovementGurus'
(48, 89), -- User 48 follows 'HomeImprovementGurus'
(49, 90), -- User 49 follows 'VirtualRealityFans'
(50, 90), -- User 50 follows 'VirtualRealityFans'
(51, 90), -- User 51 follows 'VirtualRealityFans'
(52, 90), -- User 52 follows 'VirtualRealityFans'
(53, 91), -- User 53 follows 'ClassicCarEnthusiasts'
(54, 91), -- User 54 follows 'ClassicCarEnthusiasts'
(55, 91), -- User 55 follows 'ClassicCarEnthusiasts'
(56, 91), -- User 56 follows 'ClassicCarEnthusiasts'
(57, 92), -- User 57 follows 'SculptingArtists'
(58, 92), -- User 58 follows 'SculptingArtists'
(59, 92), -- User 59 follows 'SculptingArtists'
(60, 92), -- User 60 follows 'SculptingArtists'
(61, 93), -- User 61 follows 'TechnologyTrends'
(62, 93), -- User 62 follows 'TechnologyTrends'
(63, 93), -- User 63 follows 'TechnologyTrends'
(64, 93), -- User 64 follows 'TechnologyTrends'
(65, 94), -- User 65 follows 'PhotographyMasters'
(66, 94), -- User 66 follows 'PhotographyMasters'
(67, 94), -- User 67 follows 'PhotographyMasters'
(68, 94), -- User 68 follows 'PhotographyMasters'
(69, 95), -- User 69 follows 'SpaceExplorers'
(70, 95), -- User 70 follows 'SpaceExplorers'
(71, 95), -- User 71 follows 'SpaceExplorers'
(72, 95), -- User 72 follows 'SpaceExplorers'
(73, 96), -- User 73 follows 'WildlifePhotographers'
(74, 96), -- User 74 follows 'WildlifePhotographers'
(75, 96), -- User 75 follows 'WildlifePhotographers'
(76, 96), -- User 76 follows 'WildlifePhotographers'
(77, 97), -- User 77 follows 'SustainabilityEnthusiasts'
(78, 97), -- User 78 follows 'SustainabilityEnthusiasts'
(79, 97), -- User 79 follows 'SustainabilityEnthusiasts'
(80, 97), -- User 80 follows 'SustainabilityEnthusiasts'
(81, 98), -- User 81 follows 'CreativeDesigners'
(82, 98), -- User 82 follows 'CreativeDesigners'
(83, 98), -- User 83 follows 'CreativeDesigners'
(84, 98), -- User 84 follows 'CreativeDesigners'
(85, 99), -- User 85 follows 'AICommunity'
(86, 99), -- User 86 follows 'AICommunity'
(87, 99), -- User 87 follows 'AICommunity'
(88, 99), -- User 88 follows 'AICommunity'
(89, 100), -- User 89 follows 'TechPioneers'
(90, 100), -- User 90 follows 'TechPioneers'
(91, 100), -- User 91 follows 'TechPioneers'
(92, 100), -- User 92 follows 'TechPioneers'
(93, 101), -- User 93 follows 'TechPioneers'
(94, 102); -- User 94 follows 'AICommunity'


-- Insert user followers
DO $$
DECLARE
    follower_id INT;
    followed_id INT;
    num_followers INT;
BEGIN
    -- For each user (1 to 100), assign random followers
    FOR follower_id IN 2..100 LOOP
        -- Determine how many users this user will follow (between 1 and 10)
        num_followers := (SELECT FLOOR(RANDOM() * 10 + 1)::INT);

        FOR i IN 2..num_followers LOOP
            -- Randomly select a user to follow, ensuring it is not the same as the follower
            LOOP
                followed_id := (SELECT FLOOR(RANDOM() * 99 + 2)::INT);
                EXIT WHEN followed_id <> follower_id;
            END LOOP;

            -- Insert the follower-followed pair into the table
            INSERT INTO user_followers (follower_id, followed_id)
            VALUES (follower_id, followed_id)
            ON CONFLICT DO NOTHING; -- Avoid duplicate entries
        END LOOP;
    END LOOP;
END $$;
-- Optional: Add some community moderators
INSERT INTO community_moderators (authenticated_user_id, community_id) VALUES
(6, 1), -- User 6 is moderator for 'AnimeFans'
(5, 1), -- User 6 is moderator for 'AnimeFans'
(14, 1), -- User 6 is moderator for 'AnimeFans'
(7, 2), -- User 7 is moderator for 'Superheroes'
(2, 2), -- User 7 is moderator for 'Superheroes'
(3, 2), -- User 7 is moderator for 'Superheroes'
(47, 3), -- User 47 is moderator for 'Mythology'
(48, 4), -- User 48 is moderator for 'TechTalk'
(49, 5), -- User 49 is moderator for 'AnimeTheories'
(60, 5), -- User 49 is moderator for 'AnimeTheories'
(14, 5), -- User 49 is moderator for 'AnimeTheories'
(50, 6), -- User 50 is moderator for 'SciFiEnthusiasts'
(51, 7), -- User 51 is moderator for 'MysteryLovers'
(52, 8), -- User 52 is moderator for 'FilmBuffs'
(53, 9), -- User 53 is moderator for 'GamingWorld'
(5, 9), -- User 53 is moderator for 'GamingWorld'
(54, 10), -- User 54 is moderator for 'LiteratureClub'
(60, 11), -- User 55 is moderator for 'AbyssLovers'
(5, 11), -- User 55 is moderator for 'AbyssLovers'
(56, 12), -- User 56 is moderator for 'BookLovers'
(4, 12), -- User 56 is moderator for 'BookLovers'
(3, 12), -- User 56 is moderator for 'BookLovers'
(57, 13), -- User 57 is moderator for 'TravelEnthusiasts'
(58, 14), -- User 58 is moderator for 'FantasyWriters'
(59, 15), -- User 59 is moderator for 'CulinaryArtists'
(55, 16), -- User 60 is moderator for 'MovieBuffs'
(4, 16), -- User 60 is moderator for 'MovieBuffs'
(3, 16), -- User 60 is moderator for 'MovieBuffs'
(2, 16), -- User 60 is moderator for 'MovieBuffs'
(61, 17), -- User 61 is moderator for 'FitnessFanatics'
(4, 17), -- User 61 is moderator for 'FitnessFanatics'
(62, 18), -- User 62 is moderator for 'GameDevelopers'
(63, 19), -- User 63 is moderator for 'NatureLovers'
(64, 20), -- User 64 is moderator for 'MusicProducers'
(65, 21), -- User 65 is moderator for 'HistoryBuffs'
(66, 22), -- User 66 is moderator for 'SportsFans'
(67, 23), -- User 67 is moderator for 'PetLovers'
(5, 23), -- User 67 is moderator for 'PetLovers'
(2, 23), -- User 67 is moderator for 'PetLovers'
(3, 23), -- User 67 is moderator for 'PetLovers'
(4, 23), -- User 67 is moderator for 'PetLovers'
(68, 24), -- User 68 is moderator for 'ScienceGeeks'
(69, 25), -- User 69 is moderator for 'ArtEnthusiasts'
(5, 25), -- User 69 is moderator for 'ArtEnthusiasts'
(4, 25), -- User 69 is moderator for 'ArtEnthusiasts'
(70, 26), -- User 70 is moderator for 'DIYCreators'
(71, 27), -- User 71 is moderator for 'EnvironmentAdvocates'
(72, 28), -- User 72 is moderator for 'CodingWizards'
(2, 28), -- User 72 is moderator for 'CodingWizards'
(3, 28), -- User 72 is moderator for 'CodingWizards'
(73, 29), -- User 73 is moderator for 'HealthGurus'
(74, 30), -- User 74 is moderator for 'CarEnthusiasts'
(75, 31), -- User 75 is moderator for 'PhotographyExperts'
(76, 32), -- User 76 is moderator for 'BoardGameLovers'
(77, 33), -- User 77 is moderator for 'StartupFounders'
(78, 34), -- User 78 is moderator for 'ComedyEnthusiasts'
(2, 34), -- User 78 is moderator for 'ComedyEnthusiasts'
(3, 34), -- User 78 is moderator for 'ComedyEnthusiasts'
(4, 34), -- User 78 is moderator for 'ComedyEnthusiasts'
(5, 34), -- User 78 is moderator for 'ComedyEnthusiasts'
(79, 35), -- User 79 is moderator for 'SpaceEnthusiasts'
(80, 36), -- User 80 is moderator for 'LanguageLearners Hub'
(81, 37), -- User 81 is moderator for 'MentalHealthCommunity'
(82, 38), -- User 82 is moderator for 'MartialArtsPractitioners'
(83, 39), -- User 83 is moderator for 'RemoteWorkHub'
(84, 40), -- User 84 is moderator for 'GuitarPlayers'
(85, 41), -- User 85 is moderator for 'ChessEnthusiasts'
(86, 42), -- User 86 is moderator for 'WildlifeAdvocates'
(87, 43), -- User 87 is moderator for 'CyclingFans'
(88, 44), -- User 88 is moderator for 'InteriorDesigners'
(89, 45), -- User 89 is moderator for 'EconomicsEnthusiasts'
(90, 46), -- User 90 is moderator for 'FilmMakersGuild'
(91, 47), -- User 91 is moderator for 'AstronomyLovers'
(92, 48), -- User 92 is moderator for 'YogaCommunity'
(93, 49), -- User 93 is moderator for 'ComicBookEnthusiasts'
(94, 50), -- User 94 is moderator for 'SocialJusticeWarriors'
(95, 51), -- User 95 is moderator for 'GardeningBeginners'
(96, 52), -- User 96 is moderator for 'PianoEnthusiasts'
(97, 53), -- User 97 is moderator for 'VintageCollectors'
(98, 54), -- User 98 is moderator for 'OutdoorAdventurers'
(99, 55), -- User 99 is moderator for 'PoliticalEnthusiasts'
(100, 56), -- User 100 is moderator for 'BakingEnthusiasts'
(53, 57), -- User 101 is moderator for 'HomebrewingExperts'
(91, 58), -- User 102 is moderator for 'AIInnovators'
(24, 59), -- User 103 is moderator for 'SurfingEnthusiasts'
(46, 60), -- User 104 is moderator for 'ClassicMovieFans'
(7, 61), -- User 105 is moderator for 'CosplayArtists'
(19, 62), -- User 106 is moderator for 'AdventureTravelers'
(78, 63), -- User 107 is moderator for 'ScienceFictionEnthusiasts'
(18, 64), -- User 108 is moderator for 'PetTrainersGroup'
(92, 65), -- User 109 is moderator for 'MountainClimbers'
(15, 66), -- User 110 is moderator for 'BeachEnthusiasts'
(11, 67), -- User 111 is moderator for 'K-PopCommunity'
(14, 67), -- User 111 is moderator for 'K-PopCommunity'
(62, 68), -- User 112 is moderator for 'DigitalArtHub'
(4, 68), -- User 112 is moderator for 'DigitalArtHub'
(5, 68), -- User 112 is moderator for 'DigitalArtHub'
(13, 69), -- User 113 is moderator for 'EntrepreneursNetwork'
(11, 70), -- User 114 is moderator for 'BoardGamersCircle'
(35, 71), -- User 115 is moderator for 'CatLoversClub'
(2, 71), -- User 115 is moderator for 'CatLoversClub'
(36, 72), -- User 116 is moderator for 'MovieDirectors'
(47, 73), -- User 117 is moderator for 'SkiingEnthusiasts'
(58, 74), -- User 118 is moderator for 'DroneHobbyists'
(69, 75), -- User 119 is moderator for 'VeganCommunity'
(70, 76), -- User 120 is moderator for 'GraphicDesigners'
(72, 77), -- User 121 is moderator for 'BirdWatchingEnthusiasts'
(82, 78), -- User 122 is moderator for 'SculptorsNetwork'
(23, 79), -- User 123 is moderator for 'FictionWritersHub'
(24, 80), -- User 124 is moderator for 'SneakerCollectorsGroup'
(25, 81), -- User 125 is moderator for 'RoboticsEnthusiasts'
(26, 82), -- User 126 is moderator for 'FishingFans'
(27, 83), -- User 127 is moderator for 'DroneExperts'
(28, 84), -- User 128 is moderator for 'AquariumEnthusiasts'
(29, 85), -- User 129 is moderator for 'HorrorMovieFans'
(30, 86), -- User 130 is moderator for 'LandscapePhotographers'
(31, 87), -- User 131 is moderator for 'TattooArtists'
(32, 88), -- User 132 is moderator for 'MobileGamersHub'
(33, 89), -- User 133 is moderator for 'HomeImprovementGurus'
(34, 90), -- User 134 is moderator for 'VirtualRealityFans'
(35, 91), -- User 135 is moderator for 'ClassicCarEnthusiasts'
(36, 92), -- User 136 is moderator for 'SculptingArtists'
(37, 93), -- User 137 is moderator for 'TechnologyTrends'
(38, 94), -- User 138 is moderator for 'PhotographyMasters'
(39, 95), -- User 139 is moderator for 'SpaceExplorers'
(40, 96), -- User 140 is moderator for 'WildlifePhotographers'
(41, 97), -- User 141 is moderator for 'SustainabilityEnthusiasts'
(42, 98), -- User 142 is moderator for 'CreativeDesigners'
(43, 99), -- User 143 is moderator for 'AICommunity'
(44, 100); -- User 144 is moderator for 'TechPioneers'

-- Optional: Add some favorite posts
INSERT INTO favorite_posts (authenticated_user_id, post_id) VALUES
(6, 2), (7, 5), (8, 3), (9, 7), (10, 6),(16, 12), (17, 15), (18, 11), (22, 13), (23, 14),
(24, 16), (25, 17), (26, 18), (27, 19), (28, 20),
(29, 21), (30, 22), (31, 23), (32, 24), (33, 25);


--news
INSERT INTO reports (reported_id, reason, is_open, report_type, authenticated_user_id) VALUES
    (1, 'This article contains outdated information.', TRUE, 'item_report', 12),
    (2, 'I find the content misleading.', FALSE, 'item_report', 7),
    (3, 'The source seems unreliable.', TRUE, 'item_report', 45),
    (4, 'The title is clickbait.', TRUE, 'item_report', 32),
    (5, 'This post promotes harmful ideas.', FALSE, 'item_report', 58),
    (6, 'The content violates community guidelines.', TRUE, 'item_report', 89),
    (7, 'I think this post is offensive.', FALSE, 'item_report', 21),
    (8, 'This post contains inappropriate language.', TRUE, 'item_report', 15),
    (9, 'This content spreads misinformation.', FALSE, 'item_report', 77),
    (10, 'I don’t agree with this post.', TRUE, 'item_report', 90),
    (24, 'This content should be fact-checked.', FALSE, 'item_report', 61),
    (26, 'This post is spam.', TRUE, 'item_report', 38),
    (27, 'The article lacks proper sources.', FALSE, 'item_report', 5),
    (30, 'I find this post offensive.', TRUE, 'item_report', 14),
    (32, 'This content is inappropriate.', FALSE, 'item_report', 47),
    (35, 'The post contains harmful information.', TRUE, 'item_report', 19),
    (36, 'This post promotes a biased perspective.', FALSE, 'item_report', 66),
    (38, 'The content is unsuitable for this platform.', TRUE, 'item_report', 9),
    (39, 'I think this post violates the rules.', FALSE, 'item_report', 72),
    (46, 'This post contains plagiarized content.', TRUE, 'item_report', 25);

--topics
INSERT INTO reports (reported_id, reason, is_open, report_type, authenticated_user_id) VALUES
    (11, 'This topic is offensive.', TRUE, 'topic_report', 18),
    (13, 'This content is spam.', FALSE, 'topic_report', 50),
    (15, 'I don’t agree with this topic.', TRUE, 'topic_report', 74),
    (17, 'The topic promotes harmful ideas.', FALSE, 'topic_report', 36),
    (19, 'This post contains incorrect data.', TRUE, 'topic_report', 85),
    (25, 'The topic is inappropriate.', FALSE, 'topic_report', 93),
    (28, 'This topic spreads misinformation.', TRUE, 'topic_report', 40),
    (29, 'I don’t agree with this topic.', FALSE, 'topic_report', 22),
    (31, 'This topic contains offensive material.', TRUE, 'topic_report', 11),
    (33, 'This content is spam.', FALSE, 'topic_report', 69),
    (37, 'This topic violates the community rules.', TRUE, 'topic_report', 56),
    (40, 'The topic should be fact-checked.', FALSE, 'topic_report', 81),
    (42, 'This post promotes a biased view.', TRUE, 'topic_report', 62),
    (44, 'This topic is poorly written.', FALSE, 'topic_report', 53),
    (45, 'I think this topic is misleading.', TRUE, 'topic_report', 7),
    (56, 'This topic lacks credibility.', FALSE, 'topic_report', 95),
    (58, 'I find this topic offensive.', TRUE, 'topic_report', 28),
    (84, 'This content spreads misinformation.', FALSE, 'topic_report', 33),
    (85, 'This topic contains plagiarized content.', TRUE, 'topic_report', 46),
    (86, 'This topic violates the rules.', FALSE, 'topic_report', 60);

--users
    INSERT INTO reports(reported_id, reason, is_open, report_type, authenticated_user_id) VALUES
    (2, 'User is posting offensive content', TRUE, 'user_report', 8),
    (2, 'User has been consistently disrespectful in comments', TRUE, 'user_report', 14),
    (2, 'User is spreading misinformation', TRUE, 'user_report', 15),
    (2, 'User has been involved in trolling and harassment', TRUE, 'user_report', 19),
    (14, 'User is using hate speech in discussions', TRUE, 'user_report', 23),
    (14, 'User has been violating community guidelines with inappropriate posts', TRUE, 'user_report', 30),
    (6, 'User is spamming the forum with irrelevant content', TRUE, 'user_report', 31),
    (6, 'User is bullying other members in private messages', TRUE, 'user_report', 33),
    (7, 'User has been sharing explicit content without consent', TRUE, 'user_report', 36),
    (8, 'User is repeatedly making offensive jokes', TRUE, 'user_report', 40),
    (9, 'User is causing disruption in group discussions', TRUE, 'user_report', 42),
    (10, 'User is impersonating others to cause harm', TRUE, 'user_report', 45),
    (11, 'User is leaking personal information without permission', TRUE, 'user_report', 47),
    (12, 'User has been using discriminatory language against a specific group', TRUE, 'user_report', 49),
    (13, 'User is violating privacy by sharing sensitive details of others', TRUE, 'user_report', 51),
    (14, 'User is making misleading and harmful statements in threads', TRUE, 'user_report', 55),
    (15, 'User has been involved in aggressive behavior towards new users', TRUE, 'user_report', 57),
    (16, 'User is posting defamatory content about other community members', TRUE, 'user_report', 60),
    (17, 'User has been posting repetitive and irrelevant content', TRUE, 'user_report', 62),
    (18, 'User is constantly engaging in online arguments', TRUE, 'user_report', 65),
    (19, 'User is using multiple accounts to manipulate discussions', TRUE, 'user_report', 68),
    (20, 'User is promoting harmful or unsafe practices', TRUE, 'user_report', 71);

--comments
INSERT INTO reports(reported_id, reason, is_open, report_type, authenticated_user_id) VALUES
    (1, 'User is promoting offensive views in comments', TRUE, 'comment_report', 8),
    (2, 'User is engaging in trolling behavior in response to comments', TRUE, 'comment_report', 14),
    (3, 'User is making irrelevant or spammy comments', TRUE, 'comment_report', 15),
    (4, 'User is causing unnecessary disruption in the discussion', TRUE, 'comment_report', 19),
    (5, 'User is using inappropriate language in comments', TRUE, 'comment_report', 23),
    (6, 'User is spamming comment sections with repeated statements', TRUE, 'comment_report', 30),
    (7, 'User is providing misleading or false information', TRUE, 'comment_report', 31),
    (8, 'User is using offensive humor in comments', TRUE, 'comment_report', 33),
    (9, 'User is mocking or ridiculing other users in replies', TRUE, 'comment_report', 36),
    (10, 'User is posting hateful or discriminatory remarks', TRUE, 'comment_report', 40),
    (11, 'User is engaging in hostile behavior towards other users', TRUE, 'comment_report', 42),
    (12, 'User is making baseless accusations in comments', TRUE, 'comment_report', 45),
    (13, 'User is making harmful or offensive jokes in comments', TRUE, 'comment_report', 47),
    (14, 'User is attempting to manipulate discussions with false claims', TRUE, 'comment_report', 49),
    (15, 'User is promoting unsafe or harmful ideas in the comment section', TRUE, 'comment_report', 51),
    (16, 'User is responding aggressively to simple questions', TRUE, 'comment_report', 55),
    (17, 'User is consistently spamming irrelevant comments', TRUE, 'comment_report', 57),
    (18, 'User is making inappropriate personal attacks against other users', TRUE, 'comment_report', 60),
    (19, 'User is trying to derail the conversation with unrelated comments', TRUE, 'comment_report', 62),
    (20, 'User is making statements that encourage harmful behavior', TRUE, 'comment_report', 65),
    (21, 'User is repeatedly posting inflammatory comments', TRUE, 'comment_report', 68),
    (22, 'User is flooding the comment section with negative feedback', TRUE, 'comment_report', 71),
    (23, 'User is engaging in aggressive debate tactics', TRUE, 'comment_report', 73),
    (24, 'User is sharing inappropriate or graphic content in the comments', TRUE, 'comment_report', 75),
    (25, 'User is not contributing constructively to the discussion', TRUE, 'comment_report', 78),
    (26, 'User is attempting to intimidate other users through comments', TRUE, 'comment_report', 80);

-- suspensions

INSERT INTO suspensions (reason, start, duration, authenticated_user_id)
VALUES ('Violation of forum rules', NOW(),7, 7);


