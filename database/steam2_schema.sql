-- S!team2 Project Database Schema

-- جدول کاربران (ادمین و پروموتر)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(32) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'promoter') NOT NULL,
    active TINYINT(1) DEFAULT 1,
    last_login DATETIME
);

-- جدول استان‌ها
CREATE TABLE provinces (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(64) NOT NULL
);

-- جدول شهرها
CREATE TABLE cities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    province_id INT NOT NULL,
    name VARCHAR(64) NOT NULL,
    FOREIGN KEY (province_id) REFERENCES provinces(id)
);

-- جدول پروموترها (اطلاعات تکمیلی)
CREATE TABLE promoters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    first_name VARCHAR(64) NOT NULL,
    last_name VARCHAR(64) NOT NULL,
    phone VARCHAR(20),
    province_id INT,
    city_id INT,
    birth_date DATE,
    join_date DATE,
    type ENUM('static', 'tour') DEFAULT 'static',
    profile_pic VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (province_id) REFERENCES provinces(id),
    FOREIGN KEY (city_id) REFERENCES cities(id)
);

-- جدول اسپات‌ها
CREATE TABLE spots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(64) NOT NULL,
    code VARCHAR(32) NOT NULL UNIQUE,
    province_id INT,
    city_id INT,
    lat DOUBLE NOT NULL,
    lng DOUBLE NOT NULL,
    FOREIGN KEY (province_id) REFERENCES provinces(id),
    FOREIGN KEY (city_id) REFERENCES cities(id)
);

-- جدول فعالیت‌ها
CREATE TABLE activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    promoter_id INT NOT NULL,
    spot_id INT,
    type ENUM('start', 'end', 'location_end', 'tour') NOT NULL,
    lat DOUBLE,
    lng DOUBLE,
    datetime DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (promoter_id) REFERENCES promoters(id),
    FOREIGN KEY (spot_id) REFERENCES spots(id)
);

-- جدول بررسی کیفیت
CREATE TABLE qc_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    promoter_id INT NOT NULL,
    datetime DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    answers JSON,
    selfie VARCHAR(255),
    video VARCHAR(255),
    lat DOUBLE,
    lng DOUBLE,
    FOREIGN KEY (promoter_id) REFERENCES promoters(id)
);

-- جدول گزارش حادثه
CREATE TABLE incidents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    promoter_id INT NOT NULL,
    datetime DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    organization VARCHAR(128),
    description TEXT,
    photo VARCHAR(255),
    video VARCHAR(255),
    audio VARCHAR(255),
    FOREIGN KEY (promoter_id) REFERENCES promoters(id)
);

-- جدول پشتیبانی فوری
CREATE TABLE support_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    promoter_id INT NOT NULL,
    spot_id INT,
    datetime DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (promoter_id) REFERENCES promoters(id),
    FOREIGN KEY (spot_id) REFERENCES spots(id)
);

-- پیش‌فرض برای provinces و cities (لیست کامل استان و شهر ایران را می‌توانید با فایل جداگانه import کنید)
INSERT INTO provinces (name) VALUES ('تهران'), ('اصفهان'), ('خراسان رضوی');
INSERT INTO cities (province_id, name) VALUES (1, 'تهران'), (2, 'اصفهان'), (3, 'مشهد');