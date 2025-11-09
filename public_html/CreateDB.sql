SET default_storage_engine=INNODB;

USE hiddenspots_dk_db;

-- ======================================
-- TABLES
-- ======================================

-- USERS
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    profile_photo VARCHAR(255) DEFAULT NULL, 
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    birthDate DATE,
    badges VARCHAR(255),
    blocked TINYINT(1) DEFAULT 0,
    role ENUM('user','admin') DEFAULT 'user'
);

-- HIDDEN SPOTS
CREATE TABLE hidden_spots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    city VARCHAR(100),
    profile_photo VARCHAR(255) DEFAULT NULL, 
    address VARCHAR(255),
    type VARCHAR(50),
    file_path VARCHAR(255),
    likes INT DEFAULT 0,
    latitude DECIMAL(10,8) DEFAULT NULL,
    longitude DECIMAL(11,8) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- COMMENTS
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    spot_id INT NOT NULL,
    text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (spot_id) REFERENCES hidden_spots(id) ON DELETE CASCADE
);

-- FAVORITES
CREATE TABLE favorites (
    user_id INT NOT NULL,
    spot_id INT NOT NULL,
    PRIMARY KEY (user_id, spot_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (spot_id) REFERENCES hidden_spots(id) ON DELETE CASCADE
);

-- LIKES
CREATE TABLE likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    spot_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (spot_id) REFERENCES hidden_spots(id) ON DELETE CASCADE
);

-- NOTIFICATIONS
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    spot_id INT,
    source_user_id INT,
    type VARCHAR(50),
    message TEXT,
    read_flag BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (spot_id) REFERENCES hidden_spots(id) ON DELETE SET NULL,
    FOREIGN KEY (source_user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- TAGS
CREATE TABLE tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL
);

-- SPOT_TAGS
CREATE TABLE spot_tags (
    spot_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (spot_id, tag_id),
    FOREIGN KEY (spot_id) REFERENCES hidden_spots(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
);

-- ======================================
-- VIEWS
-- ======================================

-- Hot pictures view
CREATE VIEW view_hot_pictures AS
SELECT hs.id, hs.name, hs.file_path, hs.city, hs.likes, hs.created_at, COUNT(c.id) AS comments_count
FROM hidden_spots hs
LEFT JOIN comments c ON hs.id = c.spot_id
GROUP BY hs.id
ORDER BY hs.likes DESC, comments_count DESC
LIMIT 10;

-- Latest comments view
CREATE VIEW view_latest_comments AS
SELECT c.id AS comment_id, c.text, c.created_at, u.name AS user_name, hs.name AS spot_name, hs.id AS spot_id
FROM comments c
JOIN users u ON c.user_id = u.id
JOIN hidden_spots hs ON c.spot_id = hs.id
ORDER BY c.created_at DESC
LIMIT 10;

-- ======================================
-- TRIGGERS
-- ======================================

-- After insert like -> increment likes
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

-- After delete like -> decrement likes
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

