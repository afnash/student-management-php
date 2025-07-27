<?php
session_start();
include 'db_connect.php';
include 'functions.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit();
}

$student = null;
$student_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($student_id > 0) {
    // Fetch student data for pre-filling the form
    $stmt = $conn->prepare("SELECT id, name, email, date_of_birth, course, grade, profile_picture FROM students WHERE id = ?");
    if ($stmt === false) {
        // Error handling
        echo "Error preparing statement: " . $conn->error;
        exit();
    }
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
    } else {
        echo "Student not found.";
        exit();
    }
    $stmt->close();
} else {
    echo "Invalid student ID.";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $date_of_birth = $_POST['date_of_birth'];
    $course = $_POST['course'];
    $grade = $_POST['grade'];
    $current_profile_picture = $_POST['current_profile_picture'] ?? ''; // Hidden field for current pic

    $errors = validateStudentData($name, $email, $date_of_birth, $course, $grade);

    $profile_picture_path = $current_profile_picture; // Assume current picture unless new one uploaded

    // Handle new profile picture upload 
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_name = uniqid() . '_' . basename($_FILES["profile_picture"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
        if ($check !== false) {
            if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
                $errors[] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed for profile pictures.";
            }
            if ($_FILES["profile_picture"]["size"] > 5000000) {
                $errors[] = "Sorry, your profile picture is too large (max 5MB).";
            }
            if (empty($errors)) {
                // Delete old picture if new one is uploaded and old one exists
                if (!empty($current_profile_picture) && file_exists($current_profile_picture)) {
                    unlink($current_profile_picture);
                }
                if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                    $profile_picture_path = $target_file;
                } else {
                    $errors[] = "Sorry, there was an error uploading your new profile picture.";
                }
            }
        } else {
            $errors[] = "Uploaded file is not a valid image.";
        }
    } elseif (isset($_POST['remove_picture']) && $_POST['remove_picture'] == 'yes') {
        // Option to remove existing picture
        if (!empty($current_profile_picture) && file_exists($current_profile_picture)) {
            unlink($current_profile_picture);
        }
        $profile_picture_path = ''; // Set to empty if removed
    }


    if (empty($errors)) {
        // Update database 
        $stmt = $conn->prepare("UPDATE students SET name = ?, email = ?, date_of_birth = ?, course = ?, grade = ?, profile_picture = ? WHERE id = ?");
        if ($stmt === false) {
            $_SESSION['update_status'] = ['success' => false, 'message' => "Database error: " . $conn->error];
            logAction("Error preparing update statement: " . $conn->error);
            header("Location: view_students.php");
            exit();
        }
        $stmt->bind_param("ssssssi", $name, $email, $date_of_birth, $course, $grade, $profile_picture_path, $student_id);

        try { // Error and Exception Handling [cite: 11]
            if ($stmt->execute()) {
                $_SESSION['update_status'] = ['success' => true, 'message' => "Student updated successfully!"];
                logAction("Student updated: ID " . $student_id . " (Name: " . $name . ")");
            } else {
                $_SESSION['update_status'] = ['success' => false, 'message' => "Error updating student: " . $stmt->error];
                logAction("Error updating student: " . $stmt->error);
            }
        } catch (mysqli_sql_exception $e) {
            $_SESSION['update_status'] = ['success' => false, 'message' => "Database exception: " . $e->getMessage()];
            logAction("Database exception during student update: " . $e->getMessage());
        }

        $stmt->close();
        $conn->close();
        header("Location: view_students.php");
        exit();
    } else {
        // If there are errors, reload the form with existing data and errors
        $_SESSION['update_status'] = ['success' => false, 'message' => implode("<br>", $errors)];
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Student - Student Management System</title>
    <style>
        body { font-family: sans-serif; }
        .navbar { background-color: #333; overflow: hidden; }
        .navbar a { float: left; display: block; color: white; text-align: center; padding: 14px 20px; text-decoration: none; }
        .navbar a:hover { background-color: #ddd; color: black; }
        .container { padding: 20px; }
        form { margin-top: 20px; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], input[type="email"], input[type="date"], input[type="file"] { width: 100%; padding: 8px; margin-bottom: 10px; box-sizing: border-box; }
        input[type="submit"] { background-color: #4CAF50; color: white; padding: 10px 15px; border: none; cursor: pointer; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="index.php">Home</a>
        <a href="add_student.php">Add Student</a>
        <a href="view_students.php">View Students</a>
        <a href="search_student.php">Search Student</a>
        <a href="logs.php">View Logs</a>
        <a href="index.php?logout=true" style="float: right;">Logout</a>
    </div>

    <div class="container">
        <h2>Update Student Record</h2>
        <?php
        if (isset($_SESSION['update_status'])) {
            echo '<p class="' . ($_SESSION['update_status']['success'] ? 'success' : 'error') . '">' . $_SESSION['update_status']['message'] . '</p>';
            unset($_SESSION['update_status']);
        }
        if ($student):
        ?>
        <form action="update_student.php?id=<?php echo $student['id']; ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="current_profile_picture" value="<?php echo htmlspecialchars($student['profile_picture']); ?>">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($student['name']); ?>" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required>

            <label for="date_of_birth">Date of Birth:</label>
            <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($student['date_of_birth']); ?>">

            <label for="course">Course:</label>
            <input type="text" id="course" name="course" value="<?php echo htmlspecialchars($student['course']); ?>">

            <label for="grade">Grade:</label>
            <input type="text" id="grade" name="grade" value="<?php echo htmlspecialchars($student['grade']); ?>">

            <label for="profile_picture">Profile Picture:</label>
            <?php if ($student['profile_picture']): ?>
                <p>Current Picture: <img src="<?php echo htmlspecialchars($student['profile_picture']); ?>" alt="Profile Picture" width="100"></p>
                <input type="checkbox" name="remove_picture" id="remove_picture" value="yes"> <label for="remove_picture">Remove current picture</label><br><br>
            <?php endif; ?>
            <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
            <p><small>Upload a new picture to replace the current one, or check "Remove current picture" to clear it.</small></p>

            <input type="submit" value="Update Student">
        </form>
        <?php else: ?>
            <p>Error: Student data could not be loaded for update.</p>
        <?php endif; ?>
    </div>
</body>
</html>
