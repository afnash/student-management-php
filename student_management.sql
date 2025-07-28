
CREATE DATABASE IF NOT EXISTS student_management;


USE student_management;


CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    date_of_birth DATE,
    course VARCHAR(255),
    grade VARCHAR(50),
    profile_picture VARCHAR(255)
);


