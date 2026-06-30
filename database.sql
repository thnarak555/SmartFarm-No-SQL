CREATE DATABASE IF NOT EXISTS smartfarm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smartfarm;

-- 1. devices table
CREATE TABLE IF NOT EXISTS devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    device_name VARCHAR(100) NOT NULL,
    device_type ENUM('LED', 'SENSOR') NOT NULL,
    pin INT NOT NULL,
    status VARCHAR(50) DEFAULT 'OFF',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample data
INSERT INTO devices (device_name, device_type, pin, status) VALUES
('LED1', 'LED', 13, 'OFF'),
('LED2', 'LED', 12, 'OFF'),
('Temperature', 'SENSOR', 34, 'ACTIVE'),
('Humidity', 'SENSOR', 35, 'ACTIVE'),
('Soil Moisture', 'SENSOR', 36, 'ACTIVE');

-- 2. sensor_data table
CREATE TABLE IF NOT EXISTS sensor_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sensor_name VARCHAR(100) NOT NULL,
    sensor_value FLOAT NOT NULL,
    unit VARCHAR(20) NOT NULL,
    datetime TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. led_status table
CREATE TABLE IF NOT EXISTS led_status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    led_name VARCHAR(100) NOT NULL,
    status VARCHAR(50) NOT NULL,
    datetime TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
