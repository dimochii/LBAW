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
('images/hub200.jpg');

-- Insert communities
INSERT INTO communities (name, description, privacy, image_id) VALUES
('AnimeFans', 'A community for anime enthusiasts', FALSE, 3),
('Superheroes', 'All about comic book and movie superheroes', FALSE, 3),
('Mythology', 'Exploring myths and legends from around the world', FALSE, 4),
('TechTalk', 'Discussions about technology and innovations', FALSE, 1),
('AnimeTheories', 'Deep dive into anime plot theories and discussions', FALSE, 6),
('SciFiEnthusiasts', 'A community for science fiction lovers', FALSE, 7),
('MysteryLovers', 'Discussing detective novels and crime stories', FALSE, 8),
('FilmBuffs', 'All about cinema and movie discussions', FALSE, 9),
('GamingWorld', 'Video game discussions and reviews', FALSE, 10),
('LiteratureClub', 'Book discussions and literary analysis', FALSE, 11),
('AbyssLovers', 'Community for abyss and elevator enthusiasts', TRUE, 2),

('BookLovers', 'A community for book lovers.', TRUE, 2),
('TravelEnthusiasts', 'Share your travel stories and tips.', TRUE, 4),
('FantasyWriters', 'A place for aspiring fantasy authors.', FALSE, 5),
('CulinaryArtists', 'Share recipes and cooking tips.', TRUE, 6),
('MovieBuffs', 'Discuss films and series.', FALSE, 7),
('FitnessFanatics', 'Share fitness tips and motivation.', TRUE, 8),
('GameDevelopers', 'A community for game creation discussions.', FALSE, 9),
('NatureLovers', 'Share your nature photography and stories.', TRUE, 10),
('MusicProducers', 'A community for aspiring and professional music producers.', FALSE, 11),
('HistoryBuffs', 'Explore and discuss historical events and figures.', TRUE, 12),
('SportsFans', 'A place to talk about your favorite sports and teams.', FALSE, 13),
('PetLovers', 'Share tips and stories about pets.', TRUE, 14),
('ScienceGeeks', 'Discuss recent scientific discoveries and theories.', FALSE, 15),
('ArtEnthusiasts', 'Share and critique artwork.', TRUE, 16),
('DIYCreators', 'Tips and tricks for do-it-yourself projects.', FALSE, 17),
('EnvironmentAdvocates', 'Discussions on environmental conservation and activism.', TRUE, 18),
('CodingWizards', 'A space for developers to share coding tips.', FALSE, 19),
('HealthGurus', 'Tips for leading a healthy lifestyle.', TRUE, 20),
('CarEnthusiasts', 'Share knowledge and stories about cars.', FALSE, 21),
('PhotographyExperts', 'A community to discuss photography tips and gear.', TRUE, 22),
('BoardGameLovers', 'Discuss strategies and reviews of board games.', FALSE, 23),
('StartupFounders', 'A place for entrepreneurs to share insights.', TRUE, 24),
('ComedyEnthusiasts', 'Share jokes and discuss stand-up acts.', FALSE, 25),
('SpaceEnthusiasts', 'Discuss astronomy and space exploration.', TRUE, 26),
('LanguageLearners Hub', 'Share tips and resources for learning new languages.', FALSE, 27),
('MentalHealthCommunity', 'Support and resources for mental health awareness.', TRUE, 28),
('FashionInnovators', 'A community to discuss fashion trends and designs.', FALSE, 29),
('TechReviewers', 'Discuss the latest gadgets and technologies.', TRUE, 30),
('UrbanGardenersClub', 'Tips for gardening in urban spaces.', FALSE, 31),
('PhilosophyMinds', 'Discuss philosophical ideas and theories.', TRUE, 32),
('VolunteerNetwork', 'A place for volunteers to connect and share experiences.', FALSE, 33),
('HikingEnthusiasts', 'Share hiking trails and experiences.', TRUE, 34),
('E-SportsFans', 'Discuss e-sports teams and tournaments.', FALSE, 35),
('CreativeWritersNetwork', 'Share and critique creative writing pieces.', TRUE, 36),
('MeditationCircle', 'Discuss meditation techniques and benefits.', FALSE, 37),
('ClassicCarEnthusiasts', 'Share and discuss classic car collections.', TRUE, 38),
('CryptocurrencyExperts', 'Discuss trends and tips in cryptocurrency.', FALSE, 39),
('ParentingCommunity', 'Share advice and stories about parenting.', TRUE, 40),
('MartialArtsPractitioners', 'Discuss techniques and training.', FALSE, 41),
('RemoteWorkHub', 'Tips and support for working remotely.', TRUE, 42),
('GuitarPlayers', 'A community for guitar players of all levels.', FALSE, 43),
('ChessEnthusiasts', 'Discuss strategies and famous matches.', TRUE, 44),
('WildlifeAdvocates', 'Discuss efforts to protect wildlife.', FALSE, 45),
('CyclingFans', 'Share cycling routes and tips.', TRUE, 46),
('InteriorDesigners', 'Discuss home decoration and design ideas.', FALSE, 47),
('EconomicsEnthusiasts', 'Share resources and discuss economic theories.', TRUE, 48),
('FilmMakersGuild', 'A space for filmmakers to share tips and ideas.', FALSE, 49),
('AstronomyLovers', 'Discuss celestial events and phenomena.', TRUE, 50),
('YogaCommunity', 'Share yoga techniques and benefits.', FALSE, 51),
('ComicBookEnthusiasts', 'Discuss your favorite comics and graphic novels.', TRUE, 52),
('SocialJusticeWarriors', 'Discuss social issues and activism.', FALSE, 53),
('GardeningBeginners', 'Tips and support for starting a garden.', TRUE, 54),
('PianoEnthusiasts', 'A community for pianists to share tips.', FALSE, 55),
('VintageCollectors', 'Share your vintage collections and stories.', TRUE, 56),
('OutdoorAdventurers', 'Discuss camping and outdoor activities.', FALSE, 57),
('PoliticalEnthusiasts', 'Discuss current political events.', TRUE, 58),
('BakingEnthusiasts', 'Share baking tips and recipes.', FALSE, 59),
('HomebrewingExperts', 'Tips and tricks for brewing your own drinks.', TRUE, 60),
('AIInnovators', 'Discuss advancements in artificial intelligence.', FALSE, 61),
('SurfingEnthusiasts', 'Share surfing tips and experiences.', TRUE, 62),
('ClassicMovieFans', 'Discuss your favorite classic films.', FALSE, 63),
('CosplayArtists', 'Share tips and showcase your cosplay.', TRUE, 64),
('AdventureTravelers', 'Discuss extreme travel experiences.', TRUE, 66),
('ScienceFictionEnthusiasts', 'Discuss sci-fi books and movies.', FALSE, 67),
('PetTrainersGroup', 'Share pet training tips and techniques.', TRUE, 68),
('MountainClimbers', 'Share experiences and tips for climbing.', FALSE, 69),
('BeachEnthusiasts', 'Discuss your favorite beach destinations.', TRUE, 70),
('K-PopCommunity', 'Discuss your favorite K-Pop groups and music.', FALSE, 71),
('DigitalArtHub', 'Share digital art and techniques.', TRUE, 72),
('EntrepreneursNetwork', 'Discuss ideas and challenges for startups.', FALSE, 73),
('BoardGamersCircle', 'Discuss your favorite board games and strategies.', TRUE, 74),
('CatLoversClub', 'Share stories and tips about cats.', FALSE, 75),
('MovieDirectors', 'Discuss filmmaking and production tips.', TRUE, 76),
('SkiingEnthusiasts', 'Share skiing tips and destinations.', FALSE, 77),
('DroneHobbyists', 'Discuss drone flying tips and experiences.', TRUE, 78),
('VeganCommunity', 'Share vegan recipes and tips.', FALSE, 79),
('GraphicDesigners', 'Discuss design tools and techniques.', TRUE, 80),
('BirdWatchingEnthusiasts', 'Share birdwatching tips and sightings.', FALSE, 81),
('SculptorsNetwork', 'Discuss sculpting techniques and share work.', TRUE, 82),
('FictionWritersHub', 'Share your fictional works and critique.', FALSE, 83),
('SneakerCollectorsGroup', 'Discuss your sneaker collections.', TRUE, 84),
('RoboticsEnthusiasts', 'Discuss and share robotics projects.', FALSE, 91),
('FishingFans', 'Tips and stories about fishing experiences.', TRUE, 92),
('DroneExperts', 'A community for drone hobbyists.', FALSE, 93),
('AquariumEnthusiasts', 'Share tips and setups for aquariums.', TRUE, 94),
('HorrorMovieFans', 'Discuss your favorite horror films.', FALSE, 95),
('LandscapePhotographers', 'Share and discuss landscape photography.', TRUE, 96),
('TattooArtists', 'Discuss techniques and showcase tattoos.', FALSE, 97),
('MobileGamersHub', 'Discuss mobile games and tips.', TRUE, 98),
('HomeImprovementGurus', 'Tips for improving your home.', FALSE, 99),
('VirtualRealityFans', 'Discuss VR games and technology.', TRUE, 100),

('AskMeAnything', 'A space for unique Q&A sessions.', TRUE, 101),
('LifeProTips', 'Tips for improving everyday life.', FALSE, 102),
('ExplainLikeI’mFive', 'Complex ideas explained simply.', TRUE, 103),
('TodayILearned', 'Share interesting facts you learned.', FALSE, 104),
('ShowerThoughts', 'Unique and quirky thoughts.', TRUE, 105),
('WholesomeMemes', 'Feel-good memes for everyone.', FALSE, 106),
('DIYProjects', 'Creative do-it-yourself ideas.', TRUE, 107),
('ProductivityHacks', 'Tips for getting things done efficiently.', FALSE, 108),
('SpaceExploration', 'Discussions about the universe.', TRUE, 109),
('WorldNews', 'Stay updated on global news.', FALSE, 110),
('FoodNews', 'Delicious food news.', TRUE, 111),
('ArtCritique', 'Get constructive feedback on your art.', FALSE, 112),
('HistorianNews', 'Historical questions answered by experts.', TRUE, 113),
('CasualCorner', 'Relaxed discussions on any topic.', FALSE, 114),
('GamingMemesNews', 'Funny content for gamers.', TRUE, 115),
('Minimalism', 'Discuss simple and clutter-free living.', FALSE, 116),
('SustainableLiving', 'Tips for eco-friendly living.', TRUE, 117),
('DataIsBeautiful', 'Visualizations of interesting data.', FALSE, 118),
('PersonalFinance', 'Advice for managing your money.', TRUE, 119),
('LearnProgramming', 'Resources and tips for coders.', FALSE, 120),
('Astronomy', 'Explore the stars and galaxies.', TRUE, 121),
('CasualPhotography', 'Share and discuss everyday photos.', FALSE, 122),
('ParentingTips', 'Support and advice for parents.', TRUE, 123),
('FitnessProgress', 'Share fitness achievements.', FALSE, 124),
('PoliticalHumor', 'Jokes and memes about politics.', TRUE, 125),
('StartupIdeas', 'Discuss innovative business concepts.', FALSE, 126),
('LanguageExchange', 'Practice new languages with others.', TRUE, 127),
('Investing101', 'Advice for new investors.', FALSE, 128),
('CryptocurrencyNews', 'Stay updated on crypto trends.', TRUE, 129),
('CreativeWritingPrompts', 'Ideas to inspire your writing.', FALSE, 130),
('MemeEconomy', 'Buy, sell, and trade memes.', TRUE, 131),
('CraftingEnthusiasts', 'Discuss crafts and DIY projects.', FALSE, 132),
('Futurology', 'Discuss the future of humanity.', TRUE, 133),
('HairstylingTips', 'Advice and tutorials for great hair.', FALSE, 134),
('UrbanExploration', 'Explore abandoned or hidden places.', TRUE, 135),
('PetCare', 'Tips for taking care of your pets.', FALSE, 136),
('WildCamping', 'Share camping experiences and tips.', TRUE, 137),
('GuitarTutorials', 'Lessons for guitar players.', FALSE, 138),
('WorldBuilding', 'Create and discuss fictional worlds.', TRUE, 139),
('RarePuppers', 'Share adorable dog photos.', FALSE, 140),
('TechSupport', 'Help for solving tech problems.', TRUE, 141),
('BadJokes', 'So bad they’re funny.', FALSE, 142),
('HikingTrails', 'Discover and share hiking locations.', TRUE, 143),
('UrbanLegends', 'Discuss myths and legends.', FALSE, 144),
('PhotographyChallenges', 'Participate in themed photo contests.', TRUE, 145),
('TechGadgets', 'Reviews and discussions on gadgets.', FALSE, 146),
('MovieReviews', 'Share your thoughts on films.', TRUE, 147),
('ClassicLiterature', 'Discuss timeless books.', FALSE, 148),
('GeographyNerds', 'Share and discuss maps.', TRUE, 149),
('SkincareTips', 'Discuss routines and products.', FALSE, 150),
('BoardGameDesigners', 'Create and test board games.', TRUE, 151),
('AstronomyEvents', 'Updates on celestial happenings.', FALSE, 152),
('MentalHealthTips', 'Share resources and advice.', TRUE, 153),
('SeasonalRecipes', 'Cook seasonal and festive dishes.', FALSE, 154),
('ExtremeWeatherFans', 'Share storm and weather stories.', TRUE, 155),
('BirdPhotography', 'Share stunning bird photos.', FALSE, 156),
('HandwritingHelp', 'Improve your handwriting skills.', TRUE, 157),
('RiddleLovers', 'Solve and share riddles.', FALSE, 158),
('PlantIdentification', 'Help identify mysterious plants.', TRUE, 159),
('HomeAutomation', 'Discuss smart home tech.', FALSE, 160),
('AlienTheories', 'Speculate about extraterrestrial life.', TRUE, 161),
('Philosophy101', 'Discuss beginner philosophical ideas.', FALSE, 162),
('WildernessSurvival', 'Share survival tips and gear.', TRUE, 163),
('CarMaintenanceTips', 'DIY tips for car upkeep.', FALSE, 164),
('Knitting&Crochet', 'Share patterns and projects.', TRUE, 165),
('VintageElectronics', 'Discuss retro gadgets.', FALSE, 166),
('MinimalistDesign', 'Tips for simple and effective design.', TRUE, 167),
('HomeRenovations', 'Tips for improving your house.', FALSE, 168),
('Stand-upComedy', 'Share jokes and funny clips.', TRUE, 169),
('TrailRunners', 'Discuss trail running gear and tips.', FALSE, 170),
('MartialArtsTheory', 'Debate techniques and philosophy.', TRUE, 171),
('CookingExperiments', 'Share your culinary trials.', FALSE, 172),
('K-PopDanceCovers', 'Discuss and share dance covers.', TRUE, 173),
('SustainableFashion', 'Talk about eco-friendly clothing.', FALSE, 174),
('ClassicMusicEnthusiasts', 'Discuss classical music works.', TRUE, 175),
('OrigamiArtists', 'Share folds and designs.', FALSE, 176),
('SketchArtists', 'Discuss tools and techniques.', TRUE, 177),
('ComedySketches', 'Share and create humorous sketches.', FALSE, 178),
('AIGeneratedArt', 'Discuss and share AI art.', TRUE, 179),
('BookClubPicks', 'Vote on and discuss books.', FALSE, 180),
('CodingChallenges', 'Sharpen your programming skills.', TRUE, 181),
('ArchaeologyFans', 'Discuss ancient discoveries.', FALSE, 182),
('ChessTournaments', 'Share games and results.', TRUE, 183),
('LandscapePainting', 'Showcase and discuss techniques.', FALSE, 184),
('FantasyMaps', 'Create and critique fictional maps.', TRUE, 185),
('RollerCoasterFans', 'Discuss and share thrill rides.', FALSE, 186),
('BeachPhotography', 'Share stunning beach shots.', TRUE, 187),
('MeditationTechniques', 'Explore and share practices.', FALSE, 188),
('QuiltingEnthusiasts', 'Discuss patterns and fabrics.', TRUE, 189);


-- Insert authenticated users
INSERT INTO authenticated_users (
    name, username, email, password, reputation, 
    is_suspended, birth_date, description, is_admin, image_id
) VALUES
-- Anonymous user (special case)
('Anonymous', 'anonymous', 'anonymous@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfI', 
0, FALSE, '1990-01-01', 'System anonymous user', FALSE, 1),

-- Admins
('Tiago Monteiro', 'tiago_admin', 'tiago@admin.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfI', 
100, FALSE, '1985-05-15', 'Site Administrator', TRUE, 59),

('Vasco Costa', 'vasco_admin', 'vasco@admin.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfI', 
100, FALSE, '1990-03-20', 'Senior Site Administrator', TRUE, 60),

('Teresa Mascarenhas', 'teresa_admin', 'teresa@admin.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfI', 
100, FALSE, '1988-11-10', 'Community Management Admin', TRUE, 61),

('Diana Nunes', 'diana_admin', 'diana@admin.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfI', 
100, FALSE, '1992-07-25', 'Content Moderation Admin', TRUE, 62),

-- Regular users
('Naruto Uzumaki', 'dattebayo', 'naruto@konoha.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfI', 
50, FALSE, '1997-10-10', 'Future Hokage', FALSE, 51),

('Goku Son', 'saiyan_warrior', 'goku@dragonball.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfI', 
75, FALSE, '1984-04-05', 'Protector of Earth', FALSE, 52),

('Sherlock Holmes', 'consulting_detective', 'sherlock@221b.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfI', 
90, FALSE, '1854-01-06', 'The world only consulting detective', FALSE, 53),

('Elsa Arendelle', 'ice_queen', 'elsa@frozen.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfI', 
60, FALSE, '1991-12-21', 'Queen of Arendelle', FALSE, 54),

('Peter Parker', 'spidey', 'peter@dailybugle.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfI', 
80, FALSE, '1995-08-15', 'Friendly neighborhood Spider-Man', FALSE, 55),

('Bob Johnson', 'bob', 'bob@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfI', 
40, FALSE, '1988-02-02', 'Loves to share news.', FALSE, 2),

('Charlie Brown', 'charlie', 'charlie@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfI', 
45, FALSE, '1992-03-03', 'Tech enthusiast.', FALSE, 3),

('Diana Prince', 'diana', 'diana@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfI', 
85, FALSE, '1985-04-04', 'Avid reader and commenter.', FALSE, 4),

('Edward Elric', 'edward', 'edward@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfI', 
70, FALSE, '1994-05-05', 'Anime and manga lover.', FALSE, 5),

('Fiona Gallagher', 'fiona', 'fiona@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfI', 
55, FALSE, '1991-06-06', 'Loves traveling and photography.', FALSE, 6),

('George Martin', 'george', 'george@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfI', 
95, FALSE, '1975-07-07', 'Fantasy writer and fan.', FALSE, 7),

('Hannah Montana', 'hannah', 'hannah@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfI', 
60, FALSE, '1998-08-08', 'Pop culture enthusiast.', FALSE, 8),

('Ian Malcolm', 'ian', 'ian@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfI', 
75, FALSE, '1980-09-09', 'Dinosaur expert and scientist.', FALSE, 9),

('Jack Sparrow', 'jack', 'jack@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfI', 
65, FALSE, '1980-10-10', 'Pirate captain and adventurer.', FALSE, 10),

('Katherine Pierce', 'katherine', 'katherine@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfI', 
50, FALSE, '1993-11-11', 'Mystery novel lover.', FALSE, 11),
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
(1, 'http://example.com/news2'),
(2, 'http://example.com/news2'),
(3, 'http://example.com/news3'),
(4, 'http://example.com/news4'),
(5, 'http://example.com/news5'),
(6, 'http://example.com/news6'),
(7, 'http://example.com/news7'),
(8, 'http://example.com/news8'),
(9, 'http://example.com/news9'),
(10, 'http://example.com/news10'),

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


(23, 'www.example.com'), -- The Rise of Indie Video Games
(41, 'www.example.com'), -- The History of Martial Arts
(47, 'www.example.com'), -- Tips for Interior Lighting Design
(51, 'www.example.com'), -- Yoga Poses for Stress Relief
(52, 'www.example.com'),
(53, 'www.example.com'),
(55, 'www.example.com'),
(57, 'www.example.com'),
(65, 'www.example.com'),
(66, 'www.example.com'), --30
(68, 'www.example.com'),
(69, 'www.example.com'),
(71, 'www.example.com'),
(75, 'www.example.com'),
(77, 'www.example.com'),
(83, 'www.example.com'),
(60, 'www.example.com'), -- Tips for Brewing Craft Beer at Home
(61, 'www.example.com'), -- How AI is Reshaping the Job Market
(78, 'www.example.com'), -- Drone Photography Tips
(91, 'www.example.com'), -- Advancements in Robotics
(95, 'www.example.com'), 
(96, 'www.example.com'),
(97, 'www.example.com'),
(98, 'www.example.com'),
(100, 'www.example.com'), --45
(101, 'www.example.com'),
(102, 'www.example.com'),
(103, 'www.example.com'),
(104, 'www.example.com'),
(105, 'www.example.com'),
(106, 'www.example.com'),
(107, 'www.example.com'),
(108, 'www.example.com'),
(109, 'www.example.com'),
(110, 'www.example.com'); --55

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
INSERT INTO authors (authenticated_user_id, post_id, pinned) VALUES

  -- Naruto Uzumaki (dattebayo)
  (6, 3, FALSE),  -- Top Anime of the Season (fan)
  (6, 27, FALSE), -- Naruto: Chakra System Explained (expert)
  (6, 45, TRUE),  -- Top 10 Anime Fights of All Time (expert)
  (6, 49, TRUE),  -- The Science Behind Dragon Ball Power Levels (related)
  (6, 53, FALSE), -- Exploring the World of Miniature Gaming (related)
  (6, 93, FALSE), -- Best Anime of All Time (fan)

  -- Goku Son (saiyan_warrior)
  (7, 14, FALSE),  -- Budget Friendly Travel Hacks (curious)
  (7, 68, TRUE),  -- Anticipated Sci-Fi Movies of 2024 (fan)
  (7, 95, FALSE), -- The Science Behind Horror Movies (curious)
  (7, 49, TRUE),  -- The Science Behind Dragon Ball Power Levels (expert)
  (7, 93, FALSE), -- Best Anime of All Time (fan)

  -- Sherlock Holmes (consulting_detective)
  (8, 7, TRUE),   -- Tips for Writing Detective Novels (expert)
  (8, 37, FALSE), -- Meditation for Beginners (curious)
  (8, 63, FALSE), -- Exploring Classic Films of the 20th Century (curious)
  (8, 81, FALSE), -- How to Spot Rare Birds (curious)
  (8, 95, FALSE), -- The Evolution of Horror Films (curious)

  -- Elsa Arendelle (ice_queen)
  (9, 18, FALSE), -- Environmental Conservation Success Stories (supporter)
  (9, 70, FALSE), -- Hidden Beaches You Need to Visit (interested)
  (9, 80, FALSE), -- Top Vegan Recipes for Beginners (interested)
  (9, 99, FALSE), -- Tips for DIY Home Renovations (interested)

  -- Peter Parker (spidey)
  (10, 120, TRUE),   -- The Psychology of Superheroes (fan)
  (10, 18, FALSE), -- Environmental Conservation Success Stories (supporter)
  (10, 63, FALSE), -- Exploring Classic Films of the 20th Century (curious)
  (10, 76, FALSE), -- How to Get Into Film Directing (interested)
  (10, 95, FALSE), -- The Evolution of Horror Films (curious)

  -- Bob Johnson (bob)
  (11, 23, FALSE),  -- The Rise of AI (interested)
  (11, 4, FALSE),  -- Eco-Friendly Travel Tips (interested)
  (11, 18, FALSE), -- Environmental Conservation Success Stories (supporter)
  (11, 20, FALSE), -- Healthy Eating on a Budget (interested)
  (11, 61, FALSE), -- How AI is Reshaping the Job Market (interested)

  -- Charlie Brown (charlie)
  (12, 9, TRUE),   -- The Rise of Indie Video Games (expert)
  (12, 19, FALSE), -- How to Get Started with Coding (interested)
  (12, 30, FALSE), -- Must-Have Gadgets for 2024 (interested)
  (12, 72, TRUE),  -- Essential Tools for Digital Artists (expert)
  (12, 91, FALSE), -- Advancements in Robotics (interested)

  -- Diana Prince (diana)
  (13, 121, FALSE),  -- The Psychology of Superheroes (interested)
  (13, 18, FALSE), -- Environmental Conservation Success Stories (supporter)
  (13, 32, FALSE), -- The Role of Philosophy in Modern Society (interested)
  (13, 40, FALSE), -- Parenting Tips for the Digital Age (interested)
  (13, 95, FALSE), -- The Evolution of Horror Films (curious)

  -- Edward Elric (edward) (continued)
  (14, 10, TRUE),  -- Wildlife Photography Tips (interested)
  (14, 13, TRUE),  -- Classic Anime That Still Shine (fan)
  (14, 28, FALSE), -- Mental Health and Social Media (interested)
  (14, 31, FALSE), -- Best Practices for Urban Gardening (interested)
  (14, 53, FALSE), -- Exploring the World of Miniature Gaming (interested)
  (14, 69, FALSE), -- Anticipated Sci-Fi Movies of 2024 (fan)

  -- Fiona Gallagher (fiona)
  (15, 4, TRUE),   -- Budget-Friendly Travel Hacks (expert)
  (15, 20, FALSE), -- Healthy Eating on a Budget (interested)
  (15, 34, FALSE), -- Hiking Trails for Beginners (interested)
  (15, 42, TRUE),  -- Traveling as a Digital Nomad (expert)
  (15, 56, FALSE), -- Understanding Blockchain (interested)
  (15, 70, TRUE),  -- Hidden Beaches You Need to Visit (expert)

  -- George Martin (george)
  (16, 5, TRUE),   -- Building a Fantasy World (expert)
  (16, 15, TRUE),  -- Creating Believable Characters (expert)
  (16, 21, FALSE), -- Pirate Legends and Historical Accuracy (interested)
  (16, 36, TRUE),  -- Creative Writing Prompts (expert)
  (16, 48, TRUE),  -- Writing Historical Fiction (expert)
  (16, 67, FALSE), -- Anticipated Sci-Fi Movies of 2024 (interested)

  -- Hannah Montana (hannah)
  (17, 17, FALSE),  -- Anticipated Sequels of 2024 (interested)
  (17, 8, FALSE),  -- Staying Motivated to Exercise in the Cold (interested)
  (17, 16, FALSE), -- Anticipated Sequels of 2024 (interested)
  (17, 25, TRUE),  -- Pop Culture Trends of the Decade (expert)
  (17, 35, FALSE), -- The Science Behind Horror Movies (interested)
  (17, 101, FALSE), -- Superhero Origin Stories Comparison (interested)

  -- Ian Malcolm (ian)
  (18, 23, FALSE),  -- The Rise of AI (interested)
  (18, 4, FALSE),  -- Eco-Friendly Travel Tips (interested)
  (18, 9, TRUE),   -- Game Development Basics (expert)
  (18, 18, FALSE), -- Environmental Conservation Success Stories (supporter)
  (18, 22, FALSE), -- Dinosaur Research: Past and Future (expert)
  (18, 45, TRUE),  -- How to Protect Wildlife Habitats (expert)

  -- Jack Sparrow (jack)
  (19, 4, TRUE),   -- Budget-Friendly Travel Hacks (expert)
  (19, 21, TRUE),  -- Pirate Legends and Historical Accuracy (expert)
  (19, 34, FALSE), -- Hiking Trails for Beginners (interested)
  (19, 42, TRUE),  -- Traveling as a Digital Nomad (expert)
  (19, 33, FALSE), -- Pirate Legends and Historical Accuracy (interested)
  (19, 70, TRUE),  -- Hidden Beaches You Need to Visit (expert)

  -- Katherine Pierce (katherine)
  (20, 7, TRUE),   -- Tips for Writing Detective Novels (expert)
  (20, 11, TRUE),  -- Underrated Books to Check Out (expert)
  (20, 20, FALSE), -- Healthy Eating on a Budget (interested)
  (20, 37, FALSE), -- Meditation for Beginners (curious)
  (20, 51, FALSE), -- Yoga Poses for Stress Relief (interested)
  (20, 63, FALSE), -- Exploring Classic Films of the 20th Century (curious)

  -- Liam Neeson (liam)
  (21, 7, FALSE),  -- Tips for Writing Detective Novels (interested)
  (21, 12, TRUE),  -- Anticipated Sequels of 2024 (expert)
  (21, 25, FALSE), -- Pop Culture Trends of the Decade (interested)
  (21, 35, FALSE), -- The Science Behind Horror Movies (curious)
  (21, 102, FALSE), -- Superhero Origin Stories Comparison (interested)
  (21, 63, TRUE),  -- Exploring Classic Films of the 20th Century (expert)

  -- Monica Geller (monica)
  (22, 6, TRUE),   -- Cooking Healthy Meals (expert)
  (22, 16, FALSE), -- Anticipated Sequels of 2024 (interested)
  (22, 20, TRUE),  -- Healthy Eating on a Budget (expert)
  (22, 34, FALSE), -- Hiking Trails for Beginners (interested)
  (22, 51, FALSE), -- Yoga Poses for Stress Relief (interested)
  (22, 79, TRUE),  -- Top Vegan Recipes for Beginners (expert)

  -- Nina Williams (nina)
  (23, 9, TRUE),   -- Game Development Basics (expert)
  (23, 19, FALSE), -- How to Get Started with Coding (interested)
  (23, 30, FALSE), -- Must-Have Gadgets for 2024 (interested)
  (23, 72, TRUE),  -- Essential Tools for Digital Artists (expert)
  (23, 91, FALSE), -- Advancements in Robotics (interested)

  -- Oscar Wilde (oscar)
  (24, 23, FALSE),  -- Mythology and Modern Storytelling (interested)
  (24, 11, TRUE),  -- Underrated Books to Check Out (expert)
  (24, 25, FALSE), -- Pop Culture Trends of the Decade (interested)
  (24, 36, TRUE),  -- Creative Writing Prompts (expert)
  (24, 48, TRUE),  -- Writing Historical Fiction (expert)
  (24, 63, TRUE),  -- Exploring Classic Films of the 20th Century (expert)

  -- Penny Lane (penny)
  (25, 6, FALSE),  -- Cooking Healthy Meals (interested)
  (25, 16, FALSE), -- Anticipated Sequels of 2024 (interested)
  (25, 20, FALSE), -- Healthy Eating on a Budget (interested)
  (25, 34, FALSE), -- Hiking Trails for Beginners (interested)
  (25, 51, FALSE), -- Yoga Poses for Stress Relief (interested)
  (25, 79, TRUE),  -- Top Vegan Recipes for Beginners (expert)

  -- Quentin Tarantino (quentin)
  (26, 7, TRUE),   -- Tips for Writing Detective Novels (expert)
  (26, 12, TRUE),  -- Anticipated Sequels of 2024 (expert)
  (26, 25, TRUE),  -- Pop Culture Trends of the Decade (expert)
  (26, 35, TRUE),  -- The Science Behind Horror Movies (expert)
  (26, 103, TRUE),  -- Superhero Origin Stories Comparison (expert)
  (26, 63, TRUE),  -- Exploring Classic Films of the 20th Century (expert)

  -- Rachel Green (rachel)
  (27, 122, FALSE),  -- The Psychology of Superheroes (interested)
  (27, 8, FALSE),  -- Staying Motivated to Exercise in the Cold (interested)
  (27, 16, FALSE), -- Anticipated Sequels of 2024 (interested)
  (27, 50, FALSE), -- How to Start a Home Garden (interested)
  (27, 35, FALSE), -- The Science Behind Horror Movies (curious)
  (27, 104, FALSE), -- Superhero Origin Stories Comparison (interested)
  -- Steve Rogers (steve)
(28, 123, TRUE),   -- The Psychology of Superheroes (expert)
(28, 18, FALSE), -- Environmental Conservation Success Stories (supporter)
(28, 50, FALSE), -- How to Start a Home Garden (interested)
(28, 35, FALSE), -- The Science Behind Horror Movies (curious)
(28, 105, TRUE),  -- Superhero Origin Stories Comparison (expert)
(28, 63, FALSE), -- Exploring Classic Films of the 20th Century (interested)

-- Tony Stark (tony)
(29, 1, TRUE),   -- The Rise of AI (expert)
(29, 9, TRUE),   -- Game Development Basics (expert)
(29, 18, FALSE), -- Environmental Conservation Success Stories (supporter)
(29, 26, FALSE), -- Space Exploration Milestones (interested)
(29, 30, TRUE),  -- Must-Have Gadgets for 2024 (expert)
(29, 61, TRUE),  -- How AI is Reshaping the Job Market (expert)

-- Ursula K. Le Guin (ursula)
(30, 5, TRUE),   -- Building a Fantasy World (expert)
(30, 15, TRUE),  -- Creating Believable Characters (expert)
(30, 21, FALSE), -- Pirate Legends and Historical Accuracy (interested)
(30, 36, TRUE),  -- Creative Writing Prompts (expert)
(30, 48, TRUE),  -- Writing Historical Fiction (expert)
(30, 67, FALSE), -- Anticipated Sci-Fi Movies of 2024 (interested)

-- Victor Frankenstein (victor)
(31, 23, FALSE),  -- The Rise of AI (curious)
(31, 9, TRUE),   -- Game Development Basics (expert)
(31, 18, FALSE), -- Environmental Conservation Success Stories (supporter)
(31, 22, TRUE),  -- Dinosaur Research: Past and Future (expert)
(31, 31, FALSE), -- Best Practices for Urban Gardening (interested)
(31, 61, TRUE),  -- How AI is Reshaping the Job Market (expert)

-- Will Turner (will)
(32, 4, TRUE),   -- Budget-Friendly Travel Hacks (expert)
(32, 39, TRUE),  -- Next-Gen Gaming Technologies (expert)
(32, 34, FALSE), -- Hiking Trails for Beginners (interested)
(32, 42, TRUE),  -- Traveling as a Digital Nomad (expert)
(32, 56, FALSE), -- Understanding Blockchain (interested)
(32, 70, TRUE),  -- Hidden Beaches You Need to Visit (expert)

-- Xena Warrior (xena)
(33, 124, FALSE),  -- The Psychology of Superheroes (interested)
(33, 18, FALSE), -- Environmental Conservation Success Stories (supporter)
(33, 25, FALSE), -- Pop Culture Trends of the Decade (interested)
(33, 35, FALSE), -- The Science Behind Horror Movies (curious)
(33, 106, TRUE),  -- Superhero Origin Stories Comparison (expert)
(33, 63, FALSE), -- Exploring Classic Films of the 20th Century (interested)

-- Yoda (yoda)
(34, 23, FALSE),  -- The Rise of AI (curious)
(34, 5, TRUE),   -- Building a Fantasy World (expert)
(34, 15, TRUE),  -- Creating Believable Characters (expert)
(34, 21, FALSE), -- Pirate Legends and Historical Accuracy (interested)
(34, 36, TRUE),  -- Creative Writing Prompts (expert)
(34, 48, TRUE),  -- Writing Historical Fiction (expert)

-- Zorro (zorro)
(35, 125, FALSE),  -- The Psychology of Superheroes (interested)
(35, 18, FALSE), -- Environmental Conservation Success Stories (supporter)
(35, 25, FALSE), -- Pop Culture Trends of the Decade (interested)
(35, 35, FALSE), -- The Science Behind Horror Movies (curious)
(35, 107, TRUE),  -- Superhero Origin Stories Comparison (expert)
(35, 63, FALSE), -- Exploring Classic Films of the 20th Century (interested)

-- Albus Dumbledore (albus)
(36, 23, FALSE),  -- The Rise of AI (curious)
(36, 5, TRUE),   -- Building a Fantasy World (expert)
(36, 15, TRUE),  -- Creating Believable Characters (expert)
(36, 21, FALSE), -- Pirate Legends and Historical Accuracy (interested)
(36, 36, TRUE),  -- Creative Writing Prompts (expert)
(36, 48, TRUE),  -- Writing Historical Fiction (expert)

-- Bella Swan (bella)
(37, 5, FALSE),  -- Building a Fantasy World (interested)
(37, 15, FALSE), -- Creating Believable Characters (interested)
(37, 21, FALSE), -- Pirate Legends and Historical Accuracy (interested)
(37, 36, FALSE), -- Creative Writing Prompts (interested)
(37, 48, FALSE), -- Writing Historical Fiction (interested)
(37, 67, TRUE),  -- Anticipated Sci-Fi Movies of 2024 (fan)

-- Clark Kent (clark)
(38, 125, TRUE),   -- The Psychology of Superheroes (expert)
(38, 18, FALSE), -- Environmental Conservation Success Stories (supporter)
(38, 38, FALSE), -- Indie Music Festival Highlights (interested)
(38, 35, FALSE), -- The Science Behind Horror Movies (curious)
(38, 108, TRUE),  -- Superhero Origin Stories Comparison (expert)
(38, 63, FALSE), -- Exploring Classic Films of the 20th Century (interested)

-- Darth Vader (darth)
(39, 23, FALSE),  -- The Rise of AI (curious)
(39, 5, FALSE),  -- Building a Fantasy World (interested)
(39, 15, FALSE), -- Creating Believable Characters (interested)
(39, 21, FALSE), -- Pirate Legends and Historical Accuracy (interested)
(39, 36, FALSE), -- Creative Writing Prompts (interested)
(39, 48, FALSE), -- Writing Historical Fiction (interested)

-- Elliot Alderson (elliot)
(40, 24, TRUE),   -- The Rise of AI (expert)
(40, 9, TRUE),   -- Game Development Basics (expert)
(40, 18, FALSE), -- Environmental Conservation Success Stories (supporter)
(40, 26, FALSE), -- Space Exploration Milestones (interested)
(40, 30, TRUE),  -- Must-Have Gadgets for 2024 (expert)
(40, 61, TRUE),  -- How AI is Reshaping the Job Market (expert)

-- Frodo Baggins (frodo)
(41, 5, TRUE),   -- Building a Fantasy World (expert)
(41, 15, TRUE),  -- Creating Believable Characters (expert)
(41, 41, FALSE), -- Space Exploration Documentary Review(interested)
(41, 36, TRUE),  -- Creative Writing Prompts (expert)
(41, 48, TRUE),  -- Writing Historical Fiction (expert)
(41, 67, FALSE), -- Anticipated Sci-Fi Movies of 2024 (interested)

-- Gandalf the Grey (gandalf)
(42, 24, FALSE),  -- The Rise of AI (curious)
(42, 5, TRUE),   -- Building a Fantasy World (expert)
(42, 15, TRUE),  -- Creating Believable Characters (expert)
(42, 21, FALSE), -- Pirate Legends and Historical Accuracy (interested)
(42, 36, TRUE),  -- Creative Writing Prompts (expert)
(42, 48, TRUE),  -- Writing Historical Fiction (expert)

-- Homer Simpson (homer)
(43, 8, TRUE),   -- Staying Motivated to Exercise in the Cold (interested)
(43, 16, FALSE), -- Anticipated Sequels of 2024 (interested)
(43, 25, FALSE), -- Pop Culture Trends of the Decade (interested)
(43, 35, FALSE), -- The Science Behind Horror Movies (curious)
(43, 43, FALSE), -- Cinematography Techniques in Modern Cinema (interested)
(43, 63, FALSE), -- Exploring Classic Films of the 20th Century (interested)

-- Icarus (icarus)
(44, 1, TRUE),   -- The Rise of AI (expert)
(44, 9, TRUE),   -- Game Development Basics (expert)
(44, 18, FALSE), -- Environmental Conservation Success Stories (supporter)
(44, 26, FALSE), -- Space Exploration Milestones (interested)
(44, 30, TRUE),  -- Must-Have Gadgets for 2024 (expert)
(44, 61, TRUE),  -- How AI is Reshaping the Job Market (expert)

-- Jules Winnfield (jules)
(45, 2, FALSE),  -- The Psychology of Superheroes (interested)
(45, 18, FALSE), -- Environmental Conservation Success Stories (supporter)
(45, 25, FALSE), -- Pop Culture Trends of the Decade (interested)
(45, 35, TRUE),  -- The Science Behind Horror Movies (expert)
(45, 109, TRUE),  -- Superhero Origin Stories Comparison (expert)
(45, 63, FALSE), -- Exploring Classic Films of the 20th Century (interested)

-- Katniss Everdeen (katniss)
(46, 2, FALSE),  -- The Psychology of Superheroes (interested)
(46, 18, FALSE), -- Environmental Conservation Success Stories (supporter)
(46, 50, FALSE), -- How to Start a Home Garden (interested)
(46, 35, FALSE), -- The Science Behind Horror Movies (curious)
(46, 110, FALSE), -- Superhero Origin Stories Comparison (interested)
(46, 63, FALSE), -- Exploring Classic Films of the 20th Century (interested)

-- Lara Croft (lara)
(47, 4, TRUE),   -- Budget-Friendly Travel Hacks (expert)
(47, 10, TRUE),  -- Wildlife Photography Tips (expert)
(47, 22, TRUE),  -- Dinosaur Research: Past and Future (expert)
(47, 34, TRUE),  -- Hiking Trails for Beginners (expert)
(47, 42, TRUE),  -- Traveling as a Digital Nomad (expert)
(47, 70, TRUE),  -- Hidden Beaches You Need to Visit (expert)

-- Marty McFly (marty)
(48, 1, FALSE),  -- The Rise of AI (curious)
(48, 9, TRUE),   -- Game Development Basics (expert)
(48, 18, FALSE), -- Environmental Conservation Success Stories (supporter)
(48, 26, FALSE), -- Space Exploration Milestones (interested)
(48, 30, TRUE),  -- Must-Have Gadgets for 2024 (expert)
(48, 61, TRUE),  -- How AI is Reshaping the Job Market (expert)

-- Nancy Drew (nancy)
(49, 7, TRUE),   -- Tips for Writing Detective Novels (expert)
(49, 11, TRUE),  -- Underrated Books to Check Out (expert)
(49, 20, FALSE), -- Healthy Eating on a Budget (interested)
(49, 37, FALSE), -- Meditation for Beginners (curious)
(49, 51, FALSE), -- Yoga Poses for Stress Relief (interested)
(49, 63, FALSE), -- Exploring Classic Films of the 20th Century (curious)

-- Oliver Twist (oliver)
(50, 2, FALSE),  -- The Psychology of Superheroes (interested)
(50, 18, FALSE), -- Environmental Conservation Success Stories (supporter)
(50, 25, FALSE), -- Pop Culture Trends of the Decade (interested)
(50, 35, FALSE), -- The Science Behind Horror Movies (curious)
(50, 111, FALSE), -- Superhero Origin Stories Comparison (interested)
(50, 63, FALSE), -- Exploring Classic Films of the 20th Century (interested)

-- Pikachu (pikachu)
(51, 3, TRUE),   -- Top Anime of the Season (expert)
(51, 13, TRUE),  -- Classic Anime That Still Shine (expert)
(51, 28, FALSE), -- Mental Health and Social Media (interested)
(51, 31, FALSE), -- Best Practices for Urban Gardening (interested)
(51, 53, FALSE), -- Exploring the World of Miniature Gaming (interested)
(51, 69, FALSE), -- Anticipated Sci-Fi Movies of 2024 (fan)

-- Quasimodo (quasimodo)
(52, 2, FALSE),  -- The Psychology of Superheroes (interested)
(52, 18, FALSE), -- Environmental Conservation Success Stories (supporter)
(52, 25, FALSE), -- Pop Culture Trends of the Decade (interested)
(52, 35, FALSE), -- The Science Behind Horror Movies (curious)
(52, 112, FALSE), -- Superhero Origin Stories Comparison (interested)
(52, 63, FALSE), -- Exploring Classic Films of the 20th Century (interested)

-- R2-D2 (r2d2)
(53, 1, FALSE),  -- The Rise of AI (curious)
(53, 9, TRUE),   -- Game Development Basics (expert)
(53, 18, FALSE), -- Environmental Conservation Success Stories (supporter)
(53, 26, TRUE),  -- Space Exploration Milestones (expert)
(53, 30, TRUE),  -- Must-Have Gadgets for 2024 (expert)
(53, 61, TRUE),  -- How AI is Reshaping the Job Market (expert)

-- SpongeBob SquarePants (spongebob)
(54, 2, FALSE),  -- The Psychology of Superheroes (interested)
(54, 8, TRUE),   -- Staying Motivated to Exercise in the Cold (interested)
(54, 16, FALSE), -- Anticipated Sequels of 2024 (interested)
(54, 54, FALSE), -- Pop Culture Trends of the Decade (interested)
(54, 35, FALSE), -- The Science Behind Horror Movies (curious)
(54, 113, FALSE), -- Superhero Origin Stories Comparison (interested)

-- Thor Odinson (thor)
(55, 2, TRUE),   -- The Psychology of Superheroes (expert)
(55, 18, FALSE), -- Environmental Conservation Success Stories (supporter)
(55, 55, FALSE), -- Pop Culture Trends of the Decade (interested)
(55, 35, FALSE), -- The Science Behind Horror Movies (curious)
(55, 114, TRUE),  -- Superhero Origin Stories Comparison (expert)
(55, 63, FALSE), -- Exploring Classic Films of the 20th Century (interested)

-- Ultron (ultron)
(56, 1, TRUE),   -- The Rise of AI (expert)
(56, 9, TRUE),   -- Game Development Basics (expert)
(56, 18, FALSE), -- Environmental Conservation Success Stories (supporter)
(56, 26, FALSE), -- Space Exploration Milestones (interested)
(56, 30, TRUE),  -- Must-Have Gadgets for 2024 (expert)
(56, 61, TRUE),  -- How AI is Reshaping the Job Market (expert)

-- Violet Parr (violet)
(57, 2, FALSE),  -- The Psychology of Superheroes (interested)
(57, 18, FALSE), -- Environmental Conservation Success Stories (supporter)
(57, 57, FALSE), -- Pop Culture Trends of the Decade (interested)
(57, 35, FALSE), -- The Science Behind Horror Movies (curious)
(57, 58, FALSE), -- Superhero Origin Stories Comparison (interested)
(57, 63, FALSE), -- Exploring Classic Films of the 20th Century (interested)

-- Wolverine (wolverine)
(58, 2, TRUE),   -- The Psychology of Superheroes (expert)
(58, 18, FALSE), -- Environmental Conservation Success Stories (supporter)
(58, 56, FALSE), -- Pop Culture Trends of the Decade (interested)
(58, 35, FALSE), -- The Science Behind Horror Movies (curious)
(58, 115, TRUE),  -- Superhero Origin Stories Comparison (expert)
(58, 63, FALSE), -- Exploring Classic Films of the 20th Century (interested)

-- X-Men (xmen)
(59, 2, TRUE),   -- The Psychology of Superheroes (expert)
(59, 18, FALSE), -- Environmental Conservation Success Stories (supporter)
(59, 47, FALSE), -- Understanding Blockchain (interested)
(59, 35, FALSE), -- The Science Behind Horror Movies (curious)
(59, 59, TRUE),  -- Superhero Origin Stories Comparison (expert)
(59, 63, FALSE), -- Exploring Classic Films of the 20th Century (interested)

-- Bondrewd (bondrewd)
(60, 3, FALSE),  -- Top Anime of the Season (interested)
(60, 13, FALSE), -- Classic Anime That Still Shine (interested)
(60, 28, FALSE), -- Mental Health and Social Media (interested)
(60, 31, FALSE), -- Best Practices for Urban Gardening (interested)
(60, 46, FALSE), -- New season confirmed (interested)
(60, 69, FALSE), -- Anticipated Sci-Fi Movies of 2024 (fan)

-- Nanachi (nanachi)
(61, 3, FALSE),  -- Top Anime of the Season (interested)
(61, 13, FALSE), -- Classic Anime That Still Shine (interested)
(61, 28, FALSE), -- Mental Health and Social Media (interested)
(61, 31, FALSE), -- Best Practices for Urban Gardening (interested)
(61, 46, FALSE), -- New season confirmed(interested)
(61, 67, FALSE), -- Anticipated Sci-Fi Movies of 2024 (fan)

-- Prushka (prushka)
(62, 3, FALSE),  -- Top Anime of the Season (interested)
(62, 13, FALSE), -- Classic Anime That Still Shine (interested)
(62, 28, FALSE), -- Mental Health and Social Media (interested)
(62, 31, FALSE), -- Best Practices for Urban Gardening (interested)
(62, 46, FALSE), -- New season confirmed (interested)
(62, 67, FALSE), -- Anticipated Sci-Fi Movies of 2024 (fan)

-- Geralt of Rivia (geralt)
(63, 5, TRUE),   -- Building a Fantasy World (expert)
(63, 15, TRUE),  -- Creating Believable Characters (expert)
(63, 21, FALSE), -- Pirate Legends and Historical Accuracy (interested)
(63, 36, TRUE),  -- Creative Writing Prompts (expert)
(63, 48, TRUE),  -- Writing Historical Fiction (expert)
(63, 67, FALSE), -- Anticipated Sci-Fi Movies of 2024 (interested)

-- Yennefer of Vengerberg (yennefer)
(64, 5, TRUE),   -- Building a Fantasy World (expert)
(64, 15, TRUE),  -- Creating Believable Characters (expert)
(64, 21, FALSE), -- Pirate Legends and Historical Accuracy (interested)
(64, 36, TRUE),  -- Creative Writing Prompts (expert)
(64, 48, TRUE),  -- Writing Historical Fiction (expert)
(64, 67, FALSE), -- Anticipated Sci-Fi Movies of 2024 (interested)

-- Wednesday Addams (wednesday)
(65, 2, FALSE),  -- The Psychology of Superheroes (interested)
(65, 18, FALSE), -- Environmental Conservation Success Stories (supporter)
(65, 47, FALSE), -- Understanding Blockchain (interested)
(65, 35, TRUE),  -- The Science Behind Horror Movies (expert)
(65, 60, FALSE), -- Superhero Origin Stories Comparison (interested)
(65, 63, FALSE), -- Exploring Classic Films of the 20th Century (interested)

-- Joe Goldberg (joe)
(66, 7, TRUE),   -- Tips for Writing Detective Novels (expert)
(66, 11, TRUE),  -- Underrated Books to Check Out (expert)
(66, 20, FALSE), -- Healthy Eating on a Budget (interested)
(66, 37, FALSE), -- Meditation for Beginners (curious)
(66, 51, FALSE), -- Yoga Poses for Stress Relief (interested)
(66, 63, FALSE), -- Exploring Classic Films of the 20th Century (curious)

-- Eleven (eleven)
(67, 2, FALSE),  -- The Psychology of Superheroes (interested)
(67, 18, FALSE), -- Environmental Conservation Success Stories (supporter)
(67, 25, FALSE), -- Pop Culture Trends of the Decade (interested)
(67, 35, FALSE), -- The Science Behind Horror Movies (curious)
(67, 60, FALSE), -- Superhero Origin Stories Comparison (interested)
(67, 63, FALSE), -- Exploring Classic Films of the 20th Century (interested)

-- Jim Hopper (hopper)
(68, 2, FALSE),  -- The Psychology of Superheroes (interested)
(68, 18, FALSE), -- Environmental Conservation Success Stories (supporter)
(68, 25, FALSE), -- Pop Culture Trends of the Decade (interested)
(68, 35, FALSE), -- The Science Behind Horror Movies (curious)
(68, 62, FALSE), -- Superhero Origin Stories Comparison (interested)
(68, 63, FALSE), -- Exploring Classic Films of the 20th Century (interested)

-- BoJack Horseman (bojack)
(69, 2, FALSE),  -- The Psychology of Superheroes (interested)
(69, 8, TRUE),   -- Staying Motivated to Exercise in the Cold (interested)
(69, 16, FALSE), -- Anticipated Sequels of 2024 (interested)
(69, 25, FALSE), -- Pop Culture Trends of the Decade (interested)
(69, 35, FALSE), -- The Science Behind Horror Movies (curious)
(69, 62, FALSE), -- Superhero Origin Stories Comparison (interested)

-- Nairobi (nairobi)
(70, 4, TRUE),   -- Budget-Friendly Travel Hacks (expert)
(70, 20, FALSE), -- Healthy Eating on a Budget (interested)
(70, 34, FALSE), -- Hiking Trails for Beginners (interested)
(70, 42, TRUE),  -- Traveling as a Digital Nomad (expert)
(70, 56, FALSE), -- Understanding Blockchain (interested)
(70, 71, TRUE),  -- Hidden Beaches You Need to Visit (expert)

-- Michael Wheeler (mike)
(71, 2, FALSE),  -- The Psychology of Superheroes (interested)
(71, 18, FALSE), -- Environmental Conservation Success Stories (supporter)
(71, 44, FALSE), -- eSports: The Future of Competitive Gaming (interested)
(71, 35, FALSE), -- The Science Behind Horror Movies (curious)
(71, 73, FALSE), -- Superhero Origin Stories Comparison (interested)
(71, 64, FALSE), -- Exploring Classic Films of the 20th Century (interested)

-- Ruth Langmore (ruth)
(72, 4, TRUE),   -- Budget-Friendly Travel Hacks (expert)
(72, 20, FALSE), -- Healthy Eating on a Budget (interested)
(72, 34, FALSE), -- Hiking Trails for Beginners (interested)
(72, 42, TRUE),  -- Traveling as a Digital Nomad (expert)
(72, 56, FALSE), -- Understanding Blockchain (interested)
(72, 70, TRUE),  -- Hidden Beaches You Need to Visit (expert)
-- Otis Milburn (otis) (continued)
(73, 2, FALSE),  -- The Psychology of Superheroes (interested)
(73, 8, TRUE),   -- Staying Motivated to Exercise in the Cold (interested)
(73, 16, FALSE), -- Anticipated Sequels of 2024 (interested)
(73, 25, FALSE), -- Pop Culture Trends of the Decade (interested)
(73, 35, FALSE), -- The Science Behind Horror Movies (curious)
(73, 73, FALSE), -- Superhero Origin Stories Comparison (interested)

-- Maeve Wiley (maeve)
(74, 2, FALSE),  -- The Psychology of Superheroes (interested)
(74, 8, TRUE),   -- Staying Motivated to Exercise in the Cold (interested)
(74, 16, FALSE), -- Anticipated Sequels of 2024 (interested)
(74, 25, FALSE), -- Pop Culture Trends of the Decade (interested)
(74, 35, FALSE), -- The Science Behind Horror Movies (curious)
(74, 74, FALSE), -- Superhero Origin Stories Comparison (interested)

-- Marienne Bellamy (marienne)
(75, 75, FALSE),  -- The Psychology of Superheroes (interested)
(75, 11, TRUE),  -- Underrated Books to Check Out (expert)
(75, 20, FALSE), -- Healthy Eating on a Budget (interested)
(75, 37, FALSE), -- Meditation for Beginners (curious)
(75, 51, FALSE), -- Yoga Poses for Stress Relief (interested)
(75, 64, FALSE), -- Exploring Classic Films of the 20th Century (curious)

-- Simon Basset (simon)
(76, 77, FALSE),  -- The Psychology of Superheroes (interested)
(76, 8, FALSE),  -- Staying Motivated to Exercise in the Cold (interested)
(76, 16, FALSE), -- Anticipated Sequels of 2024 (interested)
(76, 25, FALSE), -- Pop Culture Trends of the Decade (interested)
(76, 35, FALSE), -- The Science Behind Horror Movies (curious)
(76, 116, FALSE), -- Superhero Origin Stories Comparison (interested)

-- Luther Hargreeves (luther)
(77, 2, TRUE),   -- The Psychology of Superheroes (expert)
(77, 18, FALSE), -- Environmental Conservation Success Stories (supporter)
(77, 25, FALSE), -- Pop Culture Trends of the Decade (interested)
(77, 35, FALSE), -- The Science Behind Horror Movies (curious)
(77, 117, TRUE),  -- Superhero Origin Stories Comparison (expert)
(77, 65, FALSE), -- Exploring Classic Films of the 20th Century (interested)

-- Klaus Hargreeves (klaus)
(78, 2, FALSE),  -- The Psychology of Superheroes (interested)
(78, 18, FALSE), -- Environmental Conservation Success Stories (supporter)
(78, 25, FALSE), -- Pop Culture Trends of the Decade (interested)
(78, 35, FALSE), -- The Science Behind Horror Movies (curious)
(78, 118, FALSE), -- Superhero Origin Stories Comparison (interested)
(78, 65, FALSE), -- Exploring Classic Films of the 20th Century (interested)

-- Beth Harmon (beth)
(79, 77, FALSE),  -- The Psychology of Superheroes (interested)
(79, 8, TRUE),   -- Staying Motivated to Exercise in the Cold (interested)
(79, 16, FALSE), -- Anticipated Sequels of 2024 (interested)
(79, 25, FALSE), -- Pop Culture Trends of the Decade (interested)
(79, 35, FALSE), -- The Science Behind Horror Movies (curious)
(79, 119, FALSE), -- Superhero Origin Stories Comparison (interested)

-- Tokyo (tokyo)
(80, 4, TRUE),   -- Budget-Friendly Travel Hacks (expert)
(80, 20, FALSE), -- Healthy Eating on a Budget (interested)
(80, 34, FALSE), -- Hiking Trails for Beginners (interested)
(80, 42, TRUE),  -- Traveling as a Digital Nomad (expert)
(80, 56, FALSE), -- Understanding Blockchain (interested)
(80, 70, TRUE),  -- Hidden Beaches You Need to Visit (expert)

-- Dustin Henderson (dustin)
(81, 24, FALSE),  -- Emerging Tech Trends in 2024 (interested)
(81, 18, FALSE), -- Environmental Conservation Success Stories (supporter)
(81, 29, FALSE), -- The Evolution of Detective Fiction (interested)
(81, 35, FALSE), -- The Science Behind Horror Movies (curious)
(81, 52, FALSE), -- Superhero Origin Stories Comparison (interested)
(81, 63, FALSE), -- Exploring Classic Films of the 20th Century (interested)

-- The Professor (professor)
(82, 1, TRUE),   -- The Rise of AI (expert)
(82, 9, TRUE),   -- Game Development Basics (expert)
(82, 18, FALSE), -- Environmental Conservation Success Stories (supporter)
(82, 26, FALSE), -- Space Exploration Milestones (interested)
(82, 30, TRUE),  -- Must-Have Gadgets for 2024 (expert)
(82, 61, TRUE),  -- How AI is Reshaping the Job Market (expert)

-- Mickey Mouse (mickey)
(83, 78, FALSE),  -- The Psychology of Superheroes (interested)
(83, 8, TRUE),   -- Staying Motivated to Exercise in the Cold (interested)
(83, 16, FALSE), -- Anticipated Sequels of 2024 (interested)
(83, 25, FALSE), -- Pop Culture Trends of the Decade (interested)
(83, 35, FALSE), -- The Science Behind Horror Movies (curious)
(83, 52, FALSE), -- Superhero Origin Stories Comparison (interested)

-- Minnie Mouse (minnie)
(84, 78, FALSE),  -- The Psychology of Superheroes (interested)
(84, 8, TRUE),   -- Staying Motivated to Exercise in the Cold (interested)
(84, 16, FALSE), -- Anticipated Sequels of 2024 (interested)
(84, 25, FALSE), -- Pop Culture Trends of the Decade (interested)
(84, 35, FALSE), -- The Science Behind Horror Movies (curious)
(84, 52, FALSE), -- Superhero Origin Stories Comparison (interested)

-- Donald Duck (donald)
(85, 85, FALSE),  -- The Psychology of Superheroes (interested)
(85, 8, TRUE),   -- Staying Motivated to Exercise in the Cold (interested)
(85, 16, FALSE), -- Anticipated Sequels of 2024 (interested)
(85, 25, FALSE), -- Pop Culture Trends of the Decade (interested)
(85, 35, FALSE), -- The Science Behind Horror Movies (curious)
(85, 52, FALSE), -- Superhero Origin Stories Comparison (interested)

-- Goofy Goof (goofy)
(86, 83, FALSE),  -- The Psychology of Superheroes (interested)
(86, 8, TRUE),   -- Staying Motivated to Exercise in the Cold (interested)
(86, 16, FALSE), -- Anticipated Sequels of 2024 (interested)
(86, 25, FALSE), -- Pop Culture Trends of the Decade (interested)
(86, 35, FALSE), -- The Science Behind Horror Movies (curious)
(86, 86, FALSE), -- Superhero Origin Stories Comparison (interested)

-- Daisy Duck (daisy)
(87, 83, FALSE),  -- The Psychology of Superheroes (interested)
(87, 8, TRUE),   -- Staying Motivated to Exercise in the Cold (interested)
(87, 16, FALSE), -- Anticipated Sequels of 2024 (interested)
(87, 25, FALSE), -- Pop Culture Trends of the Decade (interested)
(87, 35, FALSE), -- The Science Behind Horror Movies (curious)
(87, 87, FALSE), -- Superhero Origin Stories Comparison (interested)

-- Pluto (pluto) (continued)
(88, 83, FALSE),  -- The Psychology of Superheroes (interested)
(88, 8, TRUE),   -- Staying Motivated to Exercise in the Cold (interested)
(88, 16, FALSE), -- Anticipated Sequels of 2024 (interested)
(88, 25, FALSE), -- Pop Culture Trends of the Decade (interested)
(88, 35, FALSE), -- The Science Behind Horror Movies (curious)
(88, 88, FALSE), -- Superhero Origin Stories Comparison (interested)

-- Scrooge McDuck (scrooge)
(89, 4, TRUE),   -- Budget-Friendly Travel Hacks (expert)
(89, 20, FALSE), -- Healthy Eating on a Budget (interested)
(89, 34, FALSE), -- Hiking Trails for Beginners (interested)
(89, 42, TRUE),  -- Traveling as a Digital Nomad (expert)
(89, 89, FALSE), -- Understanding Blockchain (interested)
(89, 70, TRUE),  -- Hidden Beaches You Need to Visit (expert)

-- Huey Duck (huey)
(90, 1, FALSE),  -- The Rise of AI (curious)
(90, 9, TRUE),   -- Game Development Basics (expert)
(90, 18, FALSE), -- Environmental Conservation Success Stories (supporter)
(90, 26, FALSE), -- Space Exploration Milestones (interested)
(90, 90, TRUE),  -- Must-Have Gadgets for 2024 (expert)
(90, 61, TRUE),  -- How AI is Reshaping the Job Market (expert)

-- Dewey Duck (dewey)
(91, 1, FALSE),  -- The Rise of AI (curious)
(91, 9, TRUE),   -- Game Development Basics (expert)
(91, 18, FALSE), -- Environmental Conservation Success Stories (supporter)
(91, 26, FALSE), -- Space Exploration Milestones (interested)
(91, 30, TRUE),  -- Must-Have Gadgets for 2024 (expert)
(91, 61, TRUE),  -- How AI is Reshaping the Job Market (expert)

-- Louie Duck (louie)
(92, 1, FALSE),  -- The Rise of AI (curious)
(92, 9, TRUE),   -- Game Development Basics (expert)
(92, 18, FALSE), -- Environmental Conservation Success Stories (supporter)
(92, 26, FALSE), -- Space Exploration Milestones (interested)
(92, 92, TRUE),  -- Must-Have Gadgets for 2024 (expert)
(92, 61, TRUE),  -- How AI is Reshaping the Job Market (expert)

-- Belle (belle)
(93, 82, FALSE),  -- The Psychology of Superheroes (interested)
(93, 11, TRUE),  -- Underrated Books to Check Out (expert)
(93, 93, FALSE), -- Healthy Eating on a Budget (interested)
(93, 37, FALSE), -- Meditation for Beginners (curious)
(93, 51, FALSE), -- Yoga Poses for Stress Relief (interested)
(93, 66, FALSE), -- Exploring Classic Films of the 20th Century (curious)

-- Beast (beast)
(94, 82, FALSE),  -- The Psychology of Superheroes (interested)
(94, 18, FALSE), -- Environmental Conservation Success Stories (supporter)
(94, 25, FALSE), -- Pop Culture Trends of the Decade (interested)
(94, 35, FALSE), -- The Science Behind Horror Movies (curious)
(94, 94, FALSE), -- Superhero Origin Stories Comparison (interested)
(94, 66, FALSE), -- Exploring Classic Films of the 20th Century (interested)

-- Aladdin (aladdin)
(95, 4, TRUE),   -- Budget-Friendly Travel Hacks (expert)
(95, 20, FALSE), -- Healthy Eating on a Budget (interested)
(95, 34, FALSE), -- Hiking Trails for Beginners (interested)
(95, 42, TRUE),  -- Traveling as a Digital Nomad (expert)
(95, 56, FALSE), -- Understanding Blockchain (interested)
(95, 70, TRUE),  -- Hidden Beaches You Need to Visit (expert)

-- Jasmine (jasmine)
(96, 82, FALSE),  -- The Psychology of Superheroes (interested)
(96, 18, FALSE), -- Environmental Conservation Success Stories (supporter)
(96, 25, FALSE), -- Pop Culture Trends of the Decade (interested)
(96, 35, FALSE), -- The Science Behind Horror Movies (curious)
(96, 96, FALSE), -- Superhero Origin Stories Comparison (interested)
(96, 63, FALSE), -- Exploring Classic Films of the 20th Century (interested)

-- Genie (genie)
(97, 14, FALSE),  -- Budget Friendly Travel Hacks (curious)
(97, 9, TRUE),   -- Game Development Basics (expert)
(97, 18, FALSE), -- Environmental Conservation Success Stories (supporter)
(97, 26, FALSE), -- Space Exploration Milestones (interested)
(97, 97, TRUE),  -- Must-Have Gadgets for 2024 (expert)
(97, 61, TRUE),  -- How AI is Reshaping the Job Market (expert)

-- Ariel (ariel)
(98, 4, TRUE),   -- Budget-Friendly Travel Hacks (expert)
(98, 98, FALSE), -- Healthy Eating on a Budget (interested)
(98, 34, FALSE), -- Hiking Trails for Beginners (interested)
(98, 42, TRUE),  -- Traveling as a Digital Nomad (expert)
(98, 56, FALSE), -- Understanding Blockchain (interested)
(98, 70, TRUE),  -- Hidden Beaches You Need to Visit (expert)

-- Sebastian (sebastian)
(99, 84, FALSE),  -- The Psychology of Superheroes (interested)
(99, 8, TRUE),   -- Staying Motivated to Exercise in the Cold (interested)
(99, 16, FALSE), -- Anticipated Sequels of 2024 (interested)
(99, 25, FALSE), -- Pop Culture Trends of the Decade (interested)
(99, 35, FALSE), -- The Science Behind Horror Movies (curious)
(99, 99, FALSE), -- Superhero Origin Stories Comparison (interested)

-- Violet Evergarden (viorettu)
(100, 82, FALSE),  -- The Psychology of Superheroes (interested)
(100, 11, TRUE),  -- Underrated Books to Check Out (expert)
(100, 100, FALSE), -- Healthy Eating on a Budget (interested)
(100, 37, FALSE), -- Meditation for Beginners (curious)
(100, 51, FALSE), -- Yoga Poses for Stress Relief (interested)
(100, 63, FALSE); -- Exploring Classic Films of the 20th Century (curious)



-- Insert votes
INSERT INTO votes (upvote, authenticated_user_id) VALUES
(TRUE, 6), (TRUE, 7), (TRUE, 8), (TRUE, 9), (TRUE, 10),
(FALSE, 6), (FALSE, 7), (FALSE, 8), (FALSE, 9), (FALSE, 10),
(TRUE, 16), (TRUE, 17), (TRUE, 18), (TRUE, 5), (TRUE, 6),
(FALSE, 16), (FALSE, 17), (FALSE, 18), (FALSE, 5), (FALSE, 6),
(TRUE, 24), (TRUE, 25), (TRUE, 26), (TRUE, 27), (TRUE, 28),
(FALSE, 24), (FALSE, 25), (FALSE, 26), (FALSE, 27), (FALSE, 28);

-- Link votes to posts
INSERT INTO post_votes (vote_id, post_id) 
SELECT v.id, p.id 
FROM votes v, posts p 
WHERE p.id > 15;


-- Insert comments
INSERT INTO comments (content, authenticated_user_id, post_id, parent_comment_id) VALUES
('Great article about anime fights!', 7, 1, NULL),
('I disagree with some of these rankings', 8, 1, 1),
('Fascinating breakdown of chakra', 9, 5, NULL),
('Your analysis is spot on!', 10, 5, 3),
('Interesting tech trends', 6, 4, NULL),
('Fascinating insights into fantasy writing!', 5, 11, NULL),
('As a scientist, this dinosaur research is intriguing', 4, 12, NULL),
('Love the historical perspective on pirates', 3, 13, NULL),
('Great breakdown of pop culture trends', 2, 14, NULL),
('Thought-provoking take on technological innovation', 17, 15, NULL),
('Fascinating sci-fi comparison!', 35, 16, NULL),
('Some great points about detective novels', 36, 17, NULL),
('Loved the indie film insights', 37, 18, NULL),
('Gaming tech is evolving so fast', 38, 19, NULL),
('Brilliant take on modern literature', 39, 20, NULL),
('Detailed breakdown of space documentaries', 40, 21, NULL),
('Psychological analysis is spot on', 41, 22, NULL),
('Cinematography techniques are mind-blowing', 42, 23, NULL),
('eSports is definitely the future', 43, 24, NULL),
('Magical realism continues to amaze', 44, 25, NULL),
('Amazing, cant wait!', 57, 26, NULL),
('Same!!', 58, 26, 20);

-- Insert comment votes
INSERT INTO comment_votes (vote_id, comment_id)
SELECT v.id, c.id 
FROM votes v, comments c 
WHERE c.id > 10;

-- Insert community followers
INSERT INTO community_followers (authenticated_user_id, community_id) VALUES
(6, 1), (6, 2), 
(7, 1), (7, 4),
(8, 3), (8, 4),
(9, 3), (9, 5),
(10, 2), (10, 4),
(16, 4), (16, 5),
(17, 3), (17, 4),
(18, 3), (18, 5),
(22, 2), (22, 4),
(23, 3), (23, 5),
(34, 6), (35, 6),  -- SciFi Enthusiasts
(36, 7), (37, 7),  -- Mystery Lovers
(38, 8), (39, 8),  -- Film Buffs
(40, 9), (41, 9),  -- Gaming World
(42, 10), (43, 10),  -- Literature Club
(56, 11),(57, 11), (58, 11);

-- Insert user followers
INSERT INTO user_followers (follower_id, followed_id) VALUES
(6, 7), (7, 6), 
(8, 9), (9, 8),
(10, 6), (6, 10),
(16, 17), (17, 16),
(18, 19), (19, 18),
(22, 23), (23, 22),
(24, 25), (25, 24),
(26, 27), (27, 26),
(28, 29), (29, 28),
(30, 31), (31, 30),
(32, 33), (33, 32),
(58, 57), (58, 56),
(56, 57), (57, 56),
(2, 3), (3, 4), (5, 6), (7, 8),(9, 10), (10, 11), (12, 13), (14, 15), (19, 20), (21, 22), (23, 24), (25, 26), (27, 28), (29, 30), (31, 32), (33, 34), (35, 36), (37, 38), (39, 40), (41, 42), (43, 44), (45, 46), (47, 48), (49, 50);


-- Optional: Add some community moderators
INSERT INTO community_moderators (authenticated_user_id, community_id) VALUES
(6, 1),  -- Naruto moderates Anime Fans
(7, 4),  -- Goku moderates Tech Talk
(8, 3),  -- Sherlock moderates Mythology
(21, 3),  -- George Martin moderates Mythology
(23, 4),  -- Ian Malcolm moderates Tech Talk
(24, 6),   -- Liam Neeson moderates SciFi Enthusiasts
(25, 7),   -- Monica Geller moderates Mystery Lovers
(26, 8),   -- Nina Williams moderates Film Buffs
(27, 9),   -- Oscar Wilde moderates Gaming World
(28, 10),  -- Penny Lane moderates Literature Club
(60, 11);

-- Optional: Add some favorite posts
INSERT INTO favorite_posts (authenticated_user_id, post_id) VALUES
(6, 2), (7, 5), (8, 3), (9, 7), (10, 6),(16, 12), (17, 15), (18, 11), (22, 13), (23, 14),
(24, 16), (25, 17), (26, 18), (27, 19), (28, 20),
(29, 21), (30, 22), (31, 23), (32, 24), (33, 25);

