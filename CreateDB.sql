DROP DATABASE IF EXISTS hiddenspots;
CREATE DATABASE hiddenspots;
USE hiddenspots;
SET default_storage_engine=INNODB;

-- USERS
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    birthDate DATE,
    `rank` VARCHAR(50),
    badges VARCHAR(255)
);

-- HIDDEN SPOTS
CREATE TABLE hidden_spots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    city VARCHAR(100),
    address VARCHAR(255),
    type VARCHAR(50),
    file_path VARCHAR(255),
    likes INT DEFAULT 0,
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

-- FAVORITES (M:N medzi users a hidden_spots)
CREATE TABLE favorites (
    user_id INT NOT NULL,
    spot_id INT NOT NULL,
    PRIMARY KEY (user_id, spot_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (spot_id) REFERENCES hidden_spots(id) ON DELETE CASCADE
);

-- LIKES (M:N medzi users a hidden_spots)
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

-- SPOT_TAGS (M:N medzi hidden_spots a tags)
CREATE TABLE spot_tags (
    spot_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (spot_id, tag_id),
    FOREIGN KEY (spot_id) REFERENCES hidden_spots(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
);

