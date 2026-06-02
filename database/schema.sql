CREATE TABLE IF NOT EXISTS roles (
  role_id INT PRIMARY KEY,
  name VARCHAR(50) UNIQUE NOT NULL
);

INSERT IGNORE INTO roles (role_id, name) VALUES
(1, 'user'),
(2, 'admin');

CREATE TABLE IF NOT EXISTS users (
  id_user INT PRIMARY KEY AUTO_INCREMENT,
  username VARCHAR(100) UNIQUE NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  avatar VARCHAR(255),
  bio VARCHAR(255),
  role_id INT NOT NULL DEFAULT 1,
  is_verified BOOLEAN DEFAULT FALSE,
  newsletter_enabled BOOLEAN DEFAULT TRUE,
  verification_token VARCHAR(255) UNIQUE,
  verification_expires_at DATETIME,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (role_id) REFERENCES roles(role_id)
);

INSERT INTO users (username, email, password, role_id, is_verified, newsletter_enabled)
VALUES ('admin', 'admin@example.local', '$2y$10$wrDTDRbZDRjPA0fiyjVPGuYvo/jRzhOPUURWyi8.05ebvB2OqOVKy', 2, 1, 1)
ON DUPLICATE KEY UPDATE password = VALUES(password), role_id = 2, is_verified = 1, newsletter_enabled = 1;

CREATE TABLE IF NOT EXISTS logs (
  id_log INT PRIMARY KEY AUTO_INCREMENT,
  id_user INT NULL,
  action VARCHAR(100) NOT NULL,
  page VARCHAR(255) NOT NULL,
  ip_address VARCHAR(45),
  user_agent VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS captcha_images (
  id INT PRIMARY KEY AUTO_INCREMENT,
  filename VARCHAR(255) NOT NULL,
  mime_type VARCHAR(50),
  active BOOLEAN DEFAULT TRUE,
  completed INT DEFAULT 0,
  reseted INT DEFAULT 0,
  failed INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO captcha_images (filename, mime_type, active)
SELECT 'captcha_seed.svg', 'image/svg+xml', TRUE
WHERE NOT EXISTS (SELECT 1 FROM captcha_images WHERE filename = 'captcha_seed.svg');

CREATE TABLE IF NOT EXISTS newsletters (
  id_newsletter INT PRIMARY KEY AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  subject VARCHAR(255) NOT NULL,
  content TEXT NOT NULL,
  status VARCHAR(50) NOT NULL DEFAULT 'draft',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS newsletter_history (
  id_history INT PRIMARY KEY AUTO_INCREMENT,
  id_newsletter INT NULL,
  title VARCHAR(255) NOT NULL,
  subject VARCHAR(255) NOT NULL,
  content TEXT NOT NULL,
  recipients_count INT NOT NULL DEFAULT 0,
  sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_newsletter) REFERENCES newsletters(id_newsletter) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS categories (
  id_category INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(150) UNIQUE NOT NULL,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS articles (
  id_article INT PRIMARY KEY AUTO_INCREMENT,
  id_category INT NULL,
  id_user INT NULL,
  title VARCHAR(255) NOT NULL,
  content TEXT NOT NULL,
  status VARCHAR(50) NOT NULL DEFAULT 'draft',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (id_category) REFERENCES categories(id_category) ON DELETE SET NULL,
  FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE SET NULL
);
