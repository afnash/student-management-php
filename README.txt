Student Information Management System

This project is a web-based PHP application for managing student details, including adding, viewing, searching, and updating student records. It also features file operations, session/cookie management, and error handling.

Setup Instructions for Ubuntu :

1.  Install LAMP Stack:
    sudo apt update
    sudo apt install apache2 mysql-server php libapache2-mod-php php-mysql php-cli php-json php-gd

2.  Secure MySQL Installation:
    sudo mysql_secure_installation
    (Set root password and follow prompts)

3.  Create Database and User:
    Log in to MySQL as root:
    sudo mysql -u root -p
    (Enter root password)

    CREATE DATABASE student_management_system;
    CREATE USER 'your_username'@'localhost' IDENTIFIED BY 'your_password';
    GRANT ALL PRIVILEGES ON student_management_system.* TO 'your_username'@'localhost';
    FLUSH PRIVILEGES;
    EXIT;

    (Replace 'your_username' and 'your_password' with your desired credentials)

4.  Import Database Schema:
    You can import the `student_management_system.sql` file using:
    mysql -u your_username -p student_management_system < student_management_system.sql
    (Enter your_password)

5.  Copy Project Files:
    Copy all .php files, the 'uploads' directory (once created), and the SQL file into your Apache web root.
    Recommended directory: /var/www/html/student_app/
    sudo mkdir /var/www/html/student_app
    sudo chown -R www-data:www-data /var/www/html/student_app
    sudo chmod -R 755 /var/www/html/student_app

    Place all project files (index.php, add_student.php, view_students.php, etc.) into /var/www/html/student_app/

6.  Configure Database Connection:
    Open db_connect.php and update the $username and $password variables with the database user and password you created in step 3.

7.  Restart Apache:
    sudo systemctl restart apache2

8.  Access the Application:
    Open your web browser and go to:
    http://localhost/student_app/

Default Login (for demonstration):
Username: admin
Password: password123
(It's highly recommended to implement a proper user management system with hashed passwords for production.)

Features:
- Add, View, Search, Update Student Records
- File Uploads (Profile Pictures)
- Session Management for Login
- Cookie Management for User Preferences (Theme)
- Basic Error and Exception Handling
- Activity Logging
