USE college_accommodation_system;

CREATE TABLE users (
    id VARCHAR(50) PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    real_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    user_level ENUM('student', 'admin', 'accommodation_manager') NOT NULL
);

CREATE TABLE applications (
    id VARCHAR(20) PRIMARY KEY,
    student_id VARCHAR(50) NOT NULL,
    college_name VARCHAR(100) NOT NULL,
    room_type ENUM('single', 'double') NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    reason_for_rejection TEXT,
    FOREIGN KEY (student_id) REFERENCES users(id)
);

CREATE TABLE colleges (
    name VARCHAR(100) NOT NULL PRIMARY KEY,
    single_room_capacity INT NOT NULL,
    double_room_capacity INT NOT NULL,
    available_single_rooms INT NOT NULL,
    available_double_rooms INT NOT NULL
);

-- Drop the table if it already exists (optional, to start fresh)
DROP TABLE IF EXISTS accommodation_records;

-- Create the accommodation_records table with correct foreign key definitions
CREATE TABLE accommodation_records (
    record_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) NOT NULL,
    student_name VARCHAR(100) NOT NULL,
    room_id INT DEFAULT NULL,
    college_name VARCHAR(100) NOT NULL,
    room_type ENUM('single', 'double') NOT NULL,
    check_in_date DATE DEFAULT '2023-10-06',
    check_out_date DATE DEFAULT '2024-07-30',
    FOREIGN KEY (student_id) REFERENCES users(id),
    INDEX (college_name)
);

CREATE TABLE payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    application_id VARCHAR(20) NOT NULL,
    student_id VARCHAR(50) NOT NULL,
    student_name VARCHAR(100) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    status ENUM('paid', 'unpaid') NOT NULL DEFAULT 'unpaid',
    payment_date DATE DEFAULT CURRENT_DATE,
    FOREIGN KEY (application_id) REFERENCES applications(id),
    FOREIGN KEY (student_id) REFERENCES users(id)
);

INSERT INTO users VALUES 
('A22EC0047','denies', 'denies0516', 'Denies Wong Ke Ying', '01139762466', 'keying0516@gmail.com', 'student'),
('A22EC0071', 'xiaoxuan', 'xiaoxuan1105', 'Lim Xiao Xuan', '0183274549', 'xxuan1105@gmail.com', 'student'),
('A22EC0116','winki', 'winki1218', 'Winki Wong Jia Xuan', '0164885138', 'winki1218@gmail.com', 'student'),
('A22EC0078','yunyi', 'adminyy1110', 'Mok Yun Yi', '0183259081', 'yunnez1110@gmail.com', 'admin'),
('A22SC0466','zhixuan', 'managerzx1109', 'Pang Zhi Xuan', '01136005386', 'zxuan1109@gmail.com', 'accommodation_manager');

INSERT INTO colleges VALUES
('KTDI', 10, 20, 5, 10),
('KRP', 20, 30, 10, 15),
('KTC', 25, 35, 15, 20);


INSERT INTO applications VALUES
(UUID(), 'A22EC0071', 'KRP', 'single', 'approved', NULL);

INSERT INTO applications VALUES
(UUID(), 'A22EC0116', 'KTC', 'double', 'rejected', 'Outstanding balance');
