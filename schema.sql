CREATE DATABASE IF NOT EXISTS bravestation CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bravestation;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('user','admin') NOT NULL DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE trainers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  specialty VARCHAR(190) NOT NULL,
  experience VARCHAR(50) NOT NULL,
  rating DECIMAL(3,1) NOT NULL,
  image_url VARCHAR(500) NOT NULL,
  bio VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  trainer_id INT NOT NULL,
  class_type VARCHAR(120) NOT NULL,
  date DATE NOT NULL,
  time_slot VARCHAR(20) NOT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'Confirmed',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (trainer_id) REFERENCES trainers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE cart_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  item_key VARCHAR(50) NOT NULL,
  item_name VARCHAR(50) NOT NULL,
  price INT NOT NULL,
  billing_cycle ENUM('monthly','yearly') NOT NULL DEFAULT 'monthly',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_user_item (user_id, item_key),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  order_code VARCHAR(80) NOT NULL,
  membership_name VARCHAR(50) NOT NULL,
  price INT NOT NULL,
  billing_cycle ENUM('monthly','yearly') NOT NULL DEFAULT 'monthly',
  status ENUM('active','completed') NOT NULL DEFAULT 'active',
  purchased_at DATETIME NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX (user_id),
  INDEX (purchased_at)
) ENGINE=InnoDB;



INSERT INTO users (full_name, email, password_hash, role)
VALUES ('Admin', 'admin@bravestation.com', '$2y$10$JCo4ELVQcJvpYgJuGiiBTOsyOm1lDA/4nHhWpiClV6plsg7WEdC5K', 'admin');

INSERT INTO trainers (name, specialty, experience, rating, image_url, bio) VALUES
('Sarah Johnson','Pilates Instructor','8 years',4.9,'https://media.istockphoto.com/id/1466988074/photo/a-fit-muscular-female-personal-trainer-is-holding-tablet-in-her-hands-and-smiling-at-the.jpg?s=612x612&w=0&k=20&c=x_pFg8qsm57IuyVjiVxsMOMRWrlWKiiLb6mohqWx3DI=','Sarah Johnson is a certified pilates instructor specializing in core strength, posture correction, and flexibility. She holds internationally recognized Mat and Reformer Pilates certifications. Her training programs focus on controlled movement, breathing.'),
('Marcus Chen','Bodybuilding & Nutrition','10 years',4.8,'https://img.freepik.com/premium-photo/muscular-fitness-man-with-perfect-body_966909-312.jpg?w=360','Alex Carter is a dedicated bodybuilding coach specializing in muscle hypertrophy, strength development, and physique aesthetics. He holds professional bodybuilding and advanced resistance training certifications. Alex designs structured, results-driven programs tailored to each client’s body type, experience level, and competition or lifestyle goals. He places strong emphasis on perfect lifting technique, mind–muscle connection, and recovery optimization. His coaching approach promotes long-term muscle growth, symmetry, and injury-free progress through disciplined, high-intensity training sessions.'),
('Emily Rodriguez','Yoga & Flexibility','6 years',5.0,'https://nomorecopyright.com/_next/image?url=https%3A%2F%2Fres.cloudinary.com%2Fddtk9h9bc%2Fimage%2Fupload%2Fv1747424542%2Fykosbqw77drvqviix7l5.webp&w=1200&q=75','Emily Rodriguez is a certified yoga instructor specializing in mobility, flexibility, and mindfulness-based practices. She holds Yoga Alliance RYT-500 certification and advanced breathwork training. Her classes combine fluid yoga flows with posture alignm.'),
('Daniel Brooks','Personal Trainer','3 years',4.9,'https://img.freepik.com/premium-photo/handsome-muscular-man-does-sports-gym-with-weights-his-hands_248459-16470.jpg','Daniel Brooks is an experienced personal trainer focused on strength training, fat loss, and functional performance. He holds NASM Certified Personal Trainer and Functional Training certifications.'),
('Sophia Bennett','Pilates Instructor','5 years',4.7,'https://imgcdn.stablediffusionweb.com/2024/10/12/ec6abeb2-704b-47a7-b9b5-3764e1eba7fe.jpg','Sophia Bennett specializes in reformer pilates and functional movement training. She is certified in Reformer Pilates and Functional Anatomy. Her sessions emphasize joint mobility, muscle balance, and injury prevention. Sophia designs structured and safe. ');

