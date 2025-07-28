<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<?php
session_start();
include 'db_connect.php';
include 'functions.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $date_of_birth = $_POST['date_of_birth'];
    $course = $_POST['course'];
    $grade = $_POST['grade'];
    $profile_picture = ''; // Default empty

    // Input validation using functions 
    $errors = validateStudentData($name, $email, $date_of_birth, $course, $grade);

    if (empty($errors)) {
        // Handle profile picture upload 
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == UPLOAD_ERR_OK) {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $file_name = uniqid() . '_' . basename($_FILES["profile_pic"]["name"]);
            $target_file = $target_dir . $file_name;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Check if image file is a actual image or fake image
            $check = getimagesize($_FILES["profile_pic"]["tmp_name"]);
            if ($check !== false) {
                // Allow certain file formats
                if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
                    $errors[] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed for profile pictures.";
                }
                // Check file size (e.g., max 5MB)
                if ($_FILES["profile_pic"]["size"] > 5000000) {
                    $errors[] = "Sorry, your profile picture is too large (max 5MB).";
                }
                if (empty($errors)) {
                    if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
                        $profile_picture = $target_file;
                    } else {
                        $errors[] = "Sorry, there was an error uploading your profile picture. Please check file permissions.";
                    }
                }
            } else {
                $errors[] = "File is not an image.";
            }
        }

        if (empty($errors)) {
            // Insert into database using correct field names
            $stmt = $conn->prepare("INSERT INTO students (name, email, dob, course, grade, profile_pic) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt === false) {
                $_SESSION['add_status'] = ['success' => false, 'message' => "Database error: " . $conn->error];
                logAction("Error preparing statement for student addition: " . $conn->error);
                header("Location: add_student.php");
                exit();
            }
            $stmt->bind_param("ssssss", $name, $email, $date_of_birth, $course, $grade, $profile_picture);

            try {
                if ($stmt->execute()) {
                    $_SESSION['add_status'] = ['success' => true, 'message' => "Student added successfully!"];
                    logAction("Student added: " . $name . " (Email: " . $email . ")");
                } else {
                    $_SESSION['add_status'] = ['success' => false, 'message' => "Error adding student: " . $stmt->error];
                    logAction("Error adding student: " . $stmt->error);
                }
            } catch (mysqli_sql_exception $e) {
                $_SESSION['add_status'] = ['success' => false, 'message' => "Database exception: " . $e->getMessage()];
                logAction("Database exception during student addition: " . $e->getMessage());
            }

            $stmt->close();
        } else {
            $_SESSION['add_status'] = ['success' => false, 'message' => implode("<br>", $errors)];
        }
    } else {
        $_SESSION['add_status'] = ['success' => false, 'message' => implode("<br>", $errors)];
    }

    $conn->close();
    header("Location: add_student.php");
    exit();
}
?>
