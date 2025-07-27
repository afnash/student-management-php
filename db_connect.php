<?php
$servername = "localhost";
$username = "afnash";
$password = "123";
$dbname = "student_management";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
        die("Connection failed" . $conn->connect_error);
}
?>
