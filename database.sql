-- BRD Research Consulting Centre MySQL schema for XAMPP.
-- db.php creates the database and imports legacy JSON data automatically.
CREATE DATABASE IF NOT EXISTS brd_ngos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE brd_ngos;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  username VARCHAR(100) NOT NULL UNIQUE,
  role ENUM('client', 'assistant', 'admin') NOT NULL,
  email VARCHAR(180) NULL,
  phone VARCHAR(40) NULL,
  bio TEXT NULL,
  photo_path VARCHAR(255) NULL,
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

CREATE TABLE IF NOT EXISTS projects (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  location VARCHAR(150) NOT NULL,
  status VARCHAR(50) NOT NULL,
  details TEXT NOT NULL,
  created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS publications (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  type VARCHAR(80) NOT NULL,
  details TEXT NOT NULL,
  created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS events (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  event_date DATE NOT NULL,
  location VARCHAR(150) NOT NULL,
  details TEXT NOT NULL,
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
  email VARCHAR(180) NULL,
  phone VARCHAR(40) NULL,
  course_name VARCHAR(255) NULL,
  duration VARCHAR(80) NULL,
  created_at DATETIME NOT NULL,
  INDEX requests_username_idx (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

