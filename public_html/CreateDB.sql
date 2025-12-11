SET default_storage_engine=INNODB;
CREATE DATABASE IF NOT EXISTS hiddenspots_dk_db;
USE hiddenspots_dk_db;

-- ======================================
-- USERS
-- ======================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    profile_photo VARCHAR(255) DEFAULT NULL,
    email VARCHAR(150) UNIQUE,
    password VARCHAR(255) NOT NULL,
    birthDate DATE DEFAULT NULL,
    badges VARCHAR(255) DEFAULT NULL,
    blocked TINYINT(1) DEFAULT 0,
    role ENUM('user','admin') DEFAULT 'user'
);

-- Minimal test users
INSERT INTO users (name, email, password, role) VALUES
('Admin User', 'admin@example.com', 'testpass', 'admin'),
('Regular User', 'user@example.com', 'testpass', 'user');

-- ======================================
-- HIDDEN SPOTS
-- ======================================
CREATE TABLE hidden_spots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(150),
    description TEXT,
    city VARCHAR(100),
    profile_photo VARCHAR(255) DEFAULT NULL,
    address VARCHAR(255),
    type VARCHAR(50),
    file_path VARCHAR(255),
    likes INT DEFAULT 0,
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    featured TINYINT(1) DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);


INSERT INTO hidden_spots (user_id, name, description, city, type, file_path, latitude, longitude)
VALUES
(1, 'Quiet Café', 'A cozy café in the city center.', 'Esbjerg', 'Café & Restaurant', 'uploads/test1.jpg', 55.4666, 8.4500),
(2, 'Hidden Beach', 'Small quiet beach.', 'Aarhus', 'Nature', 'uploads/test2.jpg', 56.1500, 10.2100);

-- ======================================
-- COMMENTS
-- ======================================
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    spot_id INT NOT NULL,
    text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (spot_id) REFERENCES hidden_spots(id) ON DELETE CASCADE
);


INSERT INTO comments (user_id, spot_id, text)
VALUES (2, 1, 'Love this place!');

-- ======================================
-- FAVORITES
-- ======================================
CREATE TABLE favorites (
    user_id INT NOT NULL,
    spot_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, spot_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (spot_id) REFERENCES hidden_spots(id) ON DELETE CASCADE
);


INSERT INTO favorites (user_id, spot_id) VALUES (2, 1);

-- ======================================
-- LIKES
-- ======================================
CREATE TABLE likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    spot_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (spot_id) REFERENCES hidden_spots(id) ON DELETE CASCADE
);


INSERT INTO likes (user_id, spot_id) VALUES (2, 1);

-- ======================================
-- LIKE TRIGGERS
-- ======================================
DELIMITER $$
CREATE TRIGGER trg_update_likes_on_insert
AFTER INSERT ON likes
FOR EACH ROW
BEGIN
    UPDATE hidden_spots
    SET likes = likes + 1
    WHERE id = NEW.spot_id;
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_update_likes_on_delete
AFTER DELETE ON likes
FOR EACH ROW
BEGIN
    UPDATE hidden_spots
    SET likes = likes - 1
    WHERE id = OLD.spot_id;
END$$
DELIMITER ;

-- ======================================
-- NOTIFICATIONS
-- ======================================
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    spot_id INT DEFAULT NULL,
    source_user_id INT DEFAULT NULL,
    type VARCHAR(50),
    message TEXT,
    read_flag TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (spot_id) REFERENCES hidden_spots(id) ON DELETE SET NULL,
    FOREIGN KEY (source_user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ======================================
-- SITE SETTINGS
-- ======================================
CREATE TABLE site_settings (
    id INT NOT NULL DEFAULT 1 PRIMARY KEY,
    site_description TEXT,
    rules TEXT,
    contact_info TEXT,
    primary_color VARCHAR(20),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    about_title1 VARCHAR(255),
    about_subtitle1 VARCHAR(255),
    about_text1 TEXT,
    about_title2 VARCHAR(255),
    about_subtitle2 VARCHAR(255),
    about_text2 TEXT,
    how_title VARCHAR(255),
    how_subtitle VARCHAR(255),
    card1_title VARCHAR(255),
    card1_text VARCHAR(255),
    card2_title VARCHAR(255),
    card2_text VARCHAR(255),
    card3_title VARCHAR(255),
    card3_text VARCHAR(255),
    font_family VARCHAR(100) DEFAULT 'Arial'
);


INSERT INTO site_settings (site_description, rules, contact_info, primary_color)
VALUES
('Discover hidden places.', 'Follow the community rules.', 'Contact: hello@example.com', '#80b3e0');

-- ======================================
-- TAGS + SPOT_TAGS
-- ======================================
CREATE TABLE tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50)
);

INSERT INTO tags (name) VALUES ('Nature'), ('Urban'), ('Cafés');

CREATE TABLE spot_tags (
    spot_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (spot_id, tag_id),
    FOREIGN KEY (spot_id) REFERENCES hidden_spots(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
);


INSERT INTO spot_tags (spot_id, tag_id) VALUES (1, 3);

-- ======================================
-- VIEWS
-- ======================================
CREATE VIEW view_hot_pictures AS
SELECT hs.id, hs.name, hs.description, hs.file_path, hs.city, hs.likes, hs.created_at,
       u.name AS user_name, COUNT(c.id) AS comments_count
FROM hidden_spots hs
LEFT JOIN comments c ON hs.id = c.spot_id
JOIN users u ON hs.user_id = u.id
GROUP BY hs.id
ORDER BY hs.likes DESC, comments_count DESC
LIMIT 3;

CREATE VIEW view_latest_comments AS
SELECT c.id AS comment_id, c.text, c.created_at,
       u.name AS user_name, hs.name AS spot_name, hs.id AS spot_id
FROM comments c
JOIN users u ON c.user_id = u.id
JOIN hidden_spots hs ON c.spot_id = hs.id
ORDER BY c.created_at DESC
LIMIT 10;

-- ======================================
-- INDEXES
-- ======================================
CREATE INDEX idx_users_name ON users(name);

CREATE INDEX idx_spots_name ON hidden_spots(name);
CREATE INDEX idx_spots_city ON hidden_spots(city);
CREATE INDEX idx_spots_user ON hidden_spots(user_id);

CREATE INDEX idx_comments_user ON comments(user_id);
CREATE INDEX idx_comments_spot ON comments(spot_id);

CREATE INDEX idx_favorites_user ON favorites(user_id);
CREATE INDEX idx_favorites_spot ON favorites(spot_id);

CREATE INDEX idx_likes_user ON likes(user_id);
CREATE INDEX idx_likes_spot ON likes(spot_id);

CREATE INDEX idx_notifications_user ON notifications(user_id);
CREATE INDEX idx_notifications_spot ON notifications(spot_id);
CREATE INDEX idx_notifications_source ON notifications(source_user_id);
