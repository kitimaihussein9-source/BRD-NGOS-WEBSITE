-- BRD Research Consulting Centre MySQL schema for XAMPP.
-- db.php creates the database and imports legacy JSON data automatically.
CREATE DATABASE IF NOT EXISTS brd_ngos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE brd_ngos;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  username VARCHAR(100) NOT NULL UNIQUE,
  role ENUM('client', 'assistant') NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS content (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  type ENUM('announcement', 'course', 'consultancy') NOT NULL,
  title VARCHAR(255) NOT NULL,
  details TEXT NOT NULL,
  image_path VARCHAR(255) NULL,
  video_path VARCHAR(255) NULL,
  published_by VARCHAR(100) NOT NULL,
  created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS requests (
  id VARCHAR(80) PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  username VARCHAR(100) NOT NULL,
  type VARCHAR(30) NOT NULL,
  subject VARCHAR(255) NOT NULL,
  message TEXT NOT NULL,
  status VARCHAR(40) NOT NULL,
  payment_status VARCHAR(40) NOT NULL,
  created_at DATETIME NOT NULL,
  INDEX requests_username_idx (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

