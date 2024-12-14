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
('images/user200.jpg');

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

('Book Lovers', 'A community for book lovers.', TRUE, 2),
('Travel Enthusiasts', 'Share your travel stories and tips.', TRUE, 4),
('Fantasy Writers', 'A place for aspiring fantasy authors.', FALSE, 5),
('Culinary Artists', 'Share recipes and cooking tips.', TRUE, 6),
('Movie Buffs', 'Discuss films and series.', FALSE, 7),
('Fitness Fanatics', 'Share fitness tips and motivation.', TRUE, 8),
('Game Developers', 'A community for game creation discussions.', FALSE, 9),
('Nature Lovers', 'Share your nature photography and stories.', TRUE, 10),
('Music Producers', 'A community for aspiring and professional music producers.', FALSE, 11),
('History Buffs', 'Explore and discuss historical events and figures.', TRUE, 12),
('Sports Fans', 'A place to talk about your favorite sports and teams.', FALSE, 13),
('Pet Lovers', 'Share tips and stories about pets.', TRUE, 14),
('Science Geeks', 'Discuss recent scientific discoveries and theories.', FALSE, 15),
('Art Enthusiasts', 'Share and critique artwork.', TRUE, 16),
('DIY Creators', 'Tips and tricks for do-it-yourself projects.', FALSE, 17),
('Environment Advocates', 'Discussions on environmental conservation and activism.', TRUE, 18),
('Coding Wizards', 'A space for developers to share coding tips.', FALSE, 19),
('Health Gurus', 'Tips for leading a healthy lifestyle.', TRUE, 20),
('Car Enthusiasts', 'Share knowledge and stories about cars.', FALSE, 21),
('Photography Experts', 'A community to discuss photography tips and gear.', TRUE, 22),
('Board Game Lovers', 'Discuss strategies and reviews of board games.', FALSE, 23),
('Startup Founders', 'A place for entrepreneurs to share insights.', TRUE, 24),
('Comedy Enthusiasts', 'Share jokes and discuss stand-up acts.', FALSE, 25),
('Space Enthusiasts', 'Discuss astronomy and space exploration.', TRUE, 26),
('Language Learners Hub', 'Share tips and resources for learning new languages.', FALSE, 27),
('Mental Health Community', 'Support and resources for mental health awareness.', TRUE, 28),
('Fashion Innovators', 'A community to discuss fashion trends and designs.', FALSE, 29),
('Tech Reviewers', 'Discuss the latest gadgets and technologies.', TRUE, 30),
('Urban Gardeners Club', 'Tips for gardening in urban spaces.', FALSE, 31),
('Philosophy Minds', 'Discuss philosophical ideas and theories.', TRUE, 32),
('Volunteer Network', 'A place for volunteers to connect and share experiences.', FALSE, 33),
('Hiking Enthusiasts', 'Share hiking trails and experiences.', TRUE, 34),
('E-Sports Fans', 'Discuss e-sports teams and tournaments.', FALSE, 35),
('Creative Writers Network', 'Share and critique creative writing pieces.', TRUE, 36),
('Meditation Circle', 'Discuss meditation techniques and benefits.', FALSE, 37),
('Classic Car Enthusiasts', 'Share and discuss classic car collections.', TRUE, 38),
('Cryptocurrency Experts', 'Discuss trends and tips in cryptocurrency.', FALSE, 39),
('Parenting Community', 'Share advice and stories about parenting.', TRUE, 40),
('Martial Arts Practitioners', 'Discuss techniques and training.', FALSE, 41),
('Remote Work Hub', 'Tips and support for working remotely.', TRUE, 42),
('Guitar Players', 'A community for guitar players of all levels.', FALSE, 43),
('Chess Enthusiasts', 'Discuss strategies and famous matches.', TRUE, 44),
('Wildlife Advocates', 'Discuss efforts to protect wildlife.', FALSE, 45),
('Cycling Fans', 'Share cycling routes and tips.', TRUE, 46),
('Interior Designers', 'Discuss home decoration and design ideas.', FALSE, 47),
('Economics Enthusiasts', 'Share resources and discuss economic theories.', TRUE, 48),
('Film Makers Guild', 'A space for filmmakers to share tips and ideas.', FALSE, 49),
('Astronomy Lovers', 'Discuss celestial events and phenomena.', TRUE, 50),
('Yoga Community', 'Share yoga techniques and benefits.', FALSE, 51),
('Comic Book Enthusiasts', 'Discuss your favorite comics and graphic novels.', TRUE, 52),
('Social Justice Warriors', 'Discuss social issues and activism.', FALSE, 53),
('Gardening Beginners', 'Tips and support for starting a garden.', TRUE, 54),
('Piano Enthusiasts', 'A community for pianists to share tips.', FALSE, 55),
('Vintage Collectors', 'Share your vintage collections and stories.', TRUE, 56),
('Outdoor Adventurers', 'Discuss camping and outdoor activities.', FALSE, 57),
('Political Enthusiasts', 'Discuss current political events.', TRUE, 58),
('Baking Enthusiasts', 'Share baking tips and recipes.', FALSE, 59),
('Homebrewing Experts', 'Tips and tricks for brewing your own drinks.', TRUE, 60),
('AI Innovators', 'Discuss advancements in artificial intelligence.', FALSE, 61),
('Surfing Enthusiasts', 'Share surfing tips and experiences.', TRUE, 62),
('Classic Movie Fans', 'Discuss your favorite classic films.', FALSE, 63),
('Cosplay Artists', 'Share tips and showcase your cosplay.', TRUE, 64),
('Adventure Travelers', 'Discuss extreme travel experiences.', TRUE, 66),
('Science Fiction Enthusiasts', 'Discuss sci-fi books and movies.', FALSE, 67),
('Pet Trainers Group', 'Share pet training tips and techniques.', TRUE, 68),
('Mountain Climbers', 'Share experiences and tips for climbing.', FALSE, 69),
('Beach Enthusiasts', 'Discuss your favorite beach destinations.', TRUE, 70),
('K-Pop Community', 'Discuss your favorite K-Pop groups and music.', FALSE, 71),
('Digital Art Hub', 'Share digital art and techniques.', TRUE, 72),
('Entrepreneurs Network', 'Discuss ideas and challenges for startups.', FALSE, 73),
('Board Gamers Circle', 'Discuss your favorite board games and strategies.', TRUE, 74),
('Cat Lovers Club', 'Share stories and tips about cats.', FALSE, 75),
('Movie Directors', 'Discuss filmmaking and production tips.', TRUE, 76),
('Skiing Enthusiasts', 'Share skiing tips and destinations.', FALSE, 77),
('Drone Hobbyists', 'Discuss drone flying tips and experiences.', TRUE, 78),
('Vegan Community', 'Share vegan recipes and tips.', FALSE, 79),
('Graphic Designers', 'Discuss design tools and techniques.', TRUE, 80),
('Bird Watching Enthusiasts', 'Share birdwatching tips and sightings.', FALSE, 81),
('Sculptors Network', 'Discuss sculpting techniques and share work.', TRUE, 82),
('Fiction Writers Hub', 'Share your fictional works and critique.', FALSE, 83),
('Sneaker Collectors Group', 'Discuss your sneaker collections.', TRUE, 84),
('Robotics Enthusiasts', 'Discuss and share robotics projects.', FALSE, 91),
('Fishing Fans', 'Tips and stories about fishing experiences.', TRUE, 92),
('Drone Experts', 'A community for drone hobbyists.', FALSE, 93),
('Aquarium Enthusiasts', 'Share tips and setups for aquariums.', TRUE, 94),
('Horror Movie Fans', 'Discuss your favorite horror films.', FALSE, 95),
('Landscape Photographers', 'Share and discuss landscape photography.', TRUE, 96),
('Tattoo Artists', 'Discuss techniques and showcase tattoos.', FALSE, 97),
('Mobile Gamers Hub', 'Discuss mobile games and tips.', TRUE, 98),
('Home Improvement Gurus', 'Tips for improving your home.', FALSE, 99),
('Virtual Reality Fans', 'Discuss VR games and technology.', TRUE, 100),
('Ask Me Anything', 'A space for unique Q&A sessions.', TRUE, 101),
('Life Pro Tips', 'Tips for improving everyday life.', FALSE, 102),
('Explain Like I’m Five', 'Complex ideas explained simply.', TRUE, 103),
('Today I Learned', 'Share interesting facts you learned.', FALSE, 104),
('Shower Thoughts', 'Unique and quirky thoughts.', TRUE, 105),
('Wholesome Memes', 'Feel-good memes for everyone.', FALSE, 106),
('DIY Projects', 'Creative do-it-yourself ideas.', TRUE, 107),
('Productivity Hacks', 'Tips for getting things done efficiently.', FALSE, 108),
('Space Exploration', 'Discussions about the universe.', TRUE, 109),
('World News', 'Stay updated on global news.', FALSE, 110),
('Food Porn', 'Delicious food photography.', TRUE, 111),
('Art Critique', 'Get constructive feedback on your art.', FALSE, 112),
('Ask a Historian', 'Historical questions answered by experts.', TRUE, 113),
('Casual Conversations', 'Relaxed discussions on any topic.', FALSE, 114),
('Gaming Memes', 'Funny content for gamers.', TRUE, 115),
('Minimalism', 'Discuss simple and clutter-free living.', FALSE, 116),
('Sustainable Living', 'Tips for eco-friendly living.', TRUE, 117),
('Data Is Beautiful', 'Visualizations of interesting data.', FALSE, 118),
('Personal Finance', 'Advice for managing your money.', TRUE, 119),
('Learn Programming', 'Resources and tips for coders.', FALSE, 120),
('Astronomy', 'Explore the stars and galaxies.', TRUE, 121),
('Casual Photography', 'Share and discuss everyday photos.', FALSE, 122),
('Parenting Tips', 'Support and advice for parents.', TRUE, 123),
('Fitness Progress', 'Share fitness achievements.', FALSE, 124),
('Political Humor', 'Jokes and memes about politics.', TRUE, 125),
('Startup Ideas', 'Discuss innovative business concepts.', FALSE, 126),
('Language Exchange', 'Practice new languages with others.', TRUE, 127),
('Investing 101', 'Advice for new investors.', FALSE, 128),
('Cryptocurrency News', 'Stay updated on crypto trends.', TRUE, 129),
('Creative Writing Prompts', 'Ideas to inspire your writing.', FALSE, 130),
('Meme Economy', 'Buy, sell, and trade memes.', TRUE, 131),
('Crafting Enthusiasts', 'Discuss crafts and DIY projects.', FALSE, 132),
('Futurology', 'Discuss the future of humanity.', TRUE, 133),
('Hairstyling Tips', 'Advice and tutorials for great hair.', FALSE, 134),
('Urban Exploration', 'Explore abandoned or hidden places.', TRUE, 135),
('Pet Care', 'Tips for taking care of your pets.', FALSE, 136),
('Wild Camping', 'Share camping experiences and tips.', TRUE, 137),
('Guitar Tutorials', 'Lessons for guitar players.', FALSE, 138),
('World Building', 'Create and discuss fictional worlds.', TRUE, 139),
('Rare Puppers', 'Share adorable dog photos.', FALSE, 140),
('Tech Support', 'Help for solving tech problems.', TRUE, 141),
('Bad Jokes', 'So bad they’re funny.', FALSE, 142),
('Hiking Trails', 'Discover and share hiking locations.', TRUE, 143),
('Urban Legends', 'Discuss myths and legends.', FALSE, 144),
('Photography Challenges', 'Participate in themed photo contests.', TRUE, 145),
('Tech Gadgets', 'Reviews and discussions on gadgets.', FALSE, 146),
('Movie Reviews', 'Share your thoughts on films.', TRUE, 147),
('Classic Literature', 'Discuss timeless books.', FALSE, 148),
('Geography Nerds', 'Share and discuss maps.', TRUE, 149),
('Skincare Tips', 'Discuss routines and products.', FALSE, 150),
('Board Game Designers', 'Create and test board games.', TRUE, 151),
('Astronomy Events', 'Updates on celestial happenings.', FALSE, 152),
('Mental Health Tips', 'Share resources and advice.', TRUE, 153),
('Seasonal Recipes', 'Cook seasonal and festive dishes.', FALSE, 154),
('Extreme Weather Fans', 'Share storm and weather stories.', TRUE, 155),
('Bird Photography', 'Share stunning bird photos.', FALSE, 156),
('Handwriting Help', 'Improve your handwriting skills.', TRUE, 157),
('Riddle Lovers', 'Solve and share riddles.', FALSE, 158),
('Plant Identification', 'Help identify mysterious plants.', TRUE, 159),
('Home Automation', 'Discuss smart home tech.', FALSE, 160),
('Alien Theories', 'Speculate about extraterrestrial life.', TRUE, 161),
('Philosophy 101', 'Discuss beginner philosophical ideas.', FALSE, 162),
('Wilderness Survival', 'Share survival tips and gear.', TRUE, 163),
('Car Maintenance Tips', 'DIY tips for car upkeep.', FALSE, 164),
('Knitting & Crochet', 'Share patterns and projects.', TRUE, 165),
('Vintage Electronics', 'Discuss retro gadgets.', FALSE, 166),
('Minimalist Design', 'Tips for simple and effective design.', TRUE, 167),
('Home Renovations', 'Tips for improving your house.', FALSE, 168),
('Stand-up Comedy', 'Share jokes and funny clips.', TRUE, 169),
('Trail Runners', 'Discuss trail running gear and tips.', FALSE, 170),
('Martial Arts Theory', 'Debate techniques and philosophy.', TRUE, 171),
('Cooking Experiments', 'Share your culinary trials.', FALSE, 172),
('K-Pop Dance Covers', 'Discuss and share dance covers.', TRUE, 173),
('Sustainable Fashion', 'Talk about eco-friendly clothing.', FALSE, 174),
('Classic Music Enthusiasts', 'Discuss classical music works.', TRUE, 175),
('Origami Artists', 'Share folds and designs.', FALSE, 176),
('Sketch Artists', 'Discuss tools and techniques.', TRUE, 177),
('Comedy Sketches', 'Share and create humorous sketches.', FALSE, 178),
('AI Generated Art', 'Discuss and share AI art.', TRUE, 179),
('Book Club Picks', 'Vote on and discuss books.', FALSE, 180),
('Coding Challenges', 'Sharpen your programming skills.', TRUE, 181),
('Archaeology Fans', 'Discuss ancient discoveries.', FALSE, 182),
('Chess Tournaments', 'Share games and results.', TRUE, 183),
('Landscape Painting', 'Showcase and discuss techniques.', FALSE, 184),
('Fantasy Maps', 'Create and critique fictional maps.', TRUE, 185),
('Roller Coaster Fans', 'Discuss and share thrill rides.', FALSE, 186),
('Beach Photography', 'Share stunning beach shots.', TRUE, 187),
('Meditation Techniques', 'Explore and share practices.', FALSE, 188),
('Quilting Enthusiasts', 'Discuss patterns and fabrics.', TRUE, 189),
('Stock Market Insights', 'Analyze and discuss stock trends.', FALSE, 190),
('Magic Tricks', 'Learn and share magic tips.', TRUE, 191),
('Food Science', 'Explore the science of cooking.', FALSE, 192),
('Space Missions', 'Discuss recent space exploration.', TRUE, 193),
('Jazz Music Fans', 'Share your favorite jazz pieces.', FALSE, 194),
('Aquascaping', 'Create stunning underwater worlds.', TRUE, 195),
('Minimal Wardrobe', 'Tips for simplifying clothing.', FALSE, 196),
('Candle Making', 'Discuss techniques and designs.', TRUE, 197),
('Speedrunning', 'Discuss and share gaming records.', FALSE, 198),
('3D Printing', 'Share tips and designs.', TRUE, 199),
('Graphic Novel Fans', 'Discuss and share favorite comics.', FALSE, 200);


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
100, FALSE, '1985-05-15', 'Site Administrator', TRUE, 1),

('Vasco Costa', 'vasco_admin', 'vasco@admin.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfI', 
100, FALSE, '1990-03-20', 'Senior Site Administrator', TRUE, 1),

('Teresa Mascarenhas', 'teresa_admin', 'teresa@admin.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfI', 
100, FALSE, '1988-11-10', 'Community Management Admin', TRUE, 1),

('Diana Nunes', 'diana_admin', 'diana@admin.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfI', 
100, FALSE, '1992-07-25', 'Content Moderation Admin', TRUE, 1),

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
('Bondrewd', 'bondrewd', 'bondrewd@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 10000, FALSE, '2017-07-7', 'A ruthless and manipulative White Whistle Delver with a twisted obsession with the Abyss, elevators and fur.', FALSE, 56),
('Nanachi', 'nanachi', 'nanachi@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 0, FALSE, '2017-07-7', 'A compassionate and fluffy elevator hater.', FALSE, 57),
('Prushka', 'prushka', 'prushka@example.com', '$2y$10$BPqmTy3x20LFhZOytOMToecjvIAOMnyJ4LmwY4ZQrKyLb3GWKIfIy', 0, FALSE, '2017-07-7', 'A kind-hearted and courageous girl who was adopted by Bondrewd.', FALSE, 58);





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
('Photographing Wildlife in Challenging Conditions', 'How to capture stunning shots in tough environments.', 10),

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
('New season confirmed', 'Next season coming up!', 11);

INSERT INTO news (post_id, news_url)
VALUES 
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
(46, 'https://thedirect.com/article/made-in-abyss-season-3-confirmed-details');

-- Insert into Topics table (topics awaiting review)
INSERT INTO topics (post_id, status, review_date)
VALUES 
(11, 'accepted', CURRENT_TIMESTAMP),
(12, 'rejected', CURRENT_TIMESTAMP),
(13, 'pending', CURRENT_TIMESTAMP),
(14, 'accepted', CURRENT_TIMESTAMP),
(15, 'pending', CURRENT_TIMESTAMP),
(16, 'accepted', CURRENT_TIMESTAMP),
(17, 'pending', CURRENT_TIMESTAMP),
(18, 'rejected', CURRENT_TIMESTAMP),
(19, 'accepted', CURRENT_TIMESTAMP),

(21, 'pending', CURRENT_TIMESTAMP),
(22, 'pending', CURRENT_TIMESTAMP),
(23, 'accepted', CURRENT_TIMESTAMP),
(25, 'pending', CURRENT_TIMESTAMP),
(28, 'accepted', CURRENT_TIMESTAMP),
(29, 'pending', CURRENT_TIMESTAMP),
(31, 'accepted', CURRENT_TIMESTAMP),
(33, 'pending', CURRENT_TIMESTAMP),
(34, 'accepted', CURRENT_TIMESTAMP),
(37, 'pending', CURRENT_TIMESTAMP),
(40, 'accepted', CURRENT_TIMESTAMP),
(42, 'pending', CURRENT_TIMESTAMP),
(43, 'accepted', CURRENT_TIMESTAMP),
(44, 'pending', CURRENT_TIMESTAMP),
(45, 'accepted', CURRENT_TIMESTAMP);

-- Link authors to posts
INSERT INTO authors (authenticated_user_id, post_id, pinned) VALUES
(6, 21, FALSE),
(7, 22, TRUE),
(8, 23, FALSE),
(9, 24, FALSE),
(6, 25, TRUE),
(10, 26, FALSE),
(9, 27, TRUE),
(6, 28, FALSE),
(8, 29, TRUE),
(9, 30, FALSE),
(16, 31, FALSE),
(17, 32, TRUE),
(18, 33, FALSE),
(5, 34, TRUE),
(6, 35, FALSE),
(24, 36, FALSE),  -- Liam Neeson
(25, 37, TRUE),   -- Monica Geller
(26, 38, FALSE), -- Nina Williams
(27, 39, TRUE),  -- Oscar Wilde
(28, 40, FALSE), -- Penny Lane
(29, 41, TRUE),  -- Quentin Tarantino
(30, 42, FALSE), -- Rachel Green
(31, 43, TRUE),  -- Steve Rogers
(32, 44, FALSE), -- Tony Stark
(33, 45, TRUE),  -- Ursula K. Le Guin
(62, 46, FALSE),
(61, 46, FALSE),
(60, 46, FALSE),
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
