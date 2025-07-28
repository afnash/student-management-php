<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// The rest of your PHP code follows
?>

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
    $stmt = $conn->prepare("SELECT id, name, email, dob, course, grade, profile_pic FROM students WHERE id = ?");
    if ($stmt === false) {
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
    $current_profile_picture = $_POST['current_profile_picture'] ?? '';

    $errors = validateStudentData($name, $email, $date_of_birth, $course, $grade);

    $profile_picture_path = $current_profile_picture;

    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_name = uniqid() . '_' . basename($_FILES["profile_pic"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $check = getimagesize($_FILES["profile_pic"]["tmp_name"]);
        if ($check !== false) {
            if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
                $errors[] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed for profile pictures.";
            }
            if ($_FILES["profile_pic"]["size"] > 5000000) {
                $errors[] = "Sorry, your profile picture is too large (max 5MB).";
            }
            if (empty($errors)) {
                if (!empty($current_profile_picture) && file_exists($current_profile_picture)) {
                    unlink($current_profile_picture);
                }
                if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
                    $profile_picture_path = $target_file;
                } else {
                    $errors[] = "Sorry, there was an error uploading your new profile picture.";
                }
            }
        } else {
            $errors[] = "Uploaded file is not a valid image.";
        }
    } elseif (isset($_POST['remove_picture']) && $_POST['remove_picture'] == 'yes') {
        if (!empty($current_profile_picture) && file_exists($current_profile_picture)) {
            unlink($current_profile_picture);
        }
        $profile_picture_path = '';
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE students SET name = ?, email = ?, dob = ?, course = ?, grade = ?, profile_pic = ? WHERE id = ?");
        if ($stmt === false) {
            $_SESSION['update_status'] = ['success' => false, 'message' => "Database error: " . $conn->error];
            logAction("Error preparing update statement: " . $conn->error);
            header("Location: view_students.php");
            exit();
        }
        $stmt->bind_param("ssssssi", $name, $email, $date_of_birth, $course, $grade, $profile_picture_path, $student_id);

        try {
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
    <link rel="stylesheet" href="modern-styles.css">
    <style>
        .page-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .page-header h1 {
            color: var(--text-primary);
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .page-header p {
            color: rgba(248, 250, 252, 0.8);
            font-size: 1.1rem;
        }
        
        .form-container {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 3rem;
            margin: 2rem auto;
            max-width: 700px;
            box-shadow: var(--shadow-medium);
            animation: slideIn 0.8s ease;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        .current-picture {
            background: rgba(15, 23, 42, 0.4);
            border: 1px solid var(--glass-border);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
        .current-picture img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--glass-border);
            margin-bottom: 1rem;
        }
        
        .current-picture h4 {
            color: var(--text-primary);
            margin-bottom: 1rem;
        }
        
        .remove-picture-option {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .remove-picture-option input[type="checkbox"] {
            width: auto;
            margin: 0;
        }
        
        .remove-picture-option label {
            color: rgba(248, 250, 252, 0.8);
            font-size: 0.9rem;
            margin: 0;
        }
        
        .file-upload-container {
            position: relative;
            border: 2px dashed var(--glass-border);
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            background: rgba(15, 23, 42, 0.3);
        }
        
        .file-upload-container:hover {
            border-color: var(--accent-color);
            background: rgba(15, 23, 42, 0.5);
        }
        
        .file-upload-container i {
            font-size: 3rem;
            color: rgba(203, 213, 225, 0.6);
            margin-bottom: 1rem;
        }
        
        .file-upload-container p {
            color: rgba(248, 250, 252, 0.8);
            margin-bottom: 1rem;
        }
        
        .file-upload-container input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }
        
        .btn-submit {
            width: 100%;
            padding: 1.5rem 2rem;
            background: var(--gradient-primary);
            color: var(--text-primary);
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            margin-top: 1rem;
        }
        
        .btn-submit::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: var(--gradient-secondary);
            transition: left 0.3s ease;
            z-index: -1;
        }
        
        .btn-submit:hover::before {
            left: 0;
        }
        
        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-heavy);
        }
        
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            font-weight: 500;
            animation: slideIn 0.5s ease;
        }
        
        .alert-success {
            background: rgba(74, 222, 128, 0.2);
            border: 1px solid rgba(74, 222, 128, 0.3);
            color: #22c55e;
        }
        
        .alert-error {
            background: rgba(248, 113, 113, 0.2);
            border: 1px solid rgba(248, 113, 113, 0.3);
            color: #ef4444;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="index.php"><i class="fas fa-home"></i> Home</a>
        <a href="add_student.php"><i class="fas fa-user-plus"></i> Add Student</a>
        <a href="view_students.php"><i class="fas fa-users"></i> View Students</a>
        <a href="search_student.php"><i class="fas fa-search"></i> Search Student</a>
        <a href="logs.php"><i class="fas fa-list"></i> View Logs</a>
        <a href="index.php?logout=true" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="glass-container">
        <div class="page-header">
            <h1><i class="fas fa-edit"></i> Update Student</h1>
            <p>Modify student information and details</p>
        </div>
        
        <?php
        if (isset($_SESSION['update_status'])) {
            $status_class = $_SESSION['update_status']['success'] ? 'alert-success' : 'alert-error';
            $icon = $_SESSION['update_status']['success'] ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle';
            echo '<div class="alert ' . $status_class . '">';
            echo '<i class="' . $icon . '"></i> ' . $_SESSION['update_status']['message'];
            echo '</div>';
            unset($_SESSION['update_status']);
        }
        ?>
        
        <?php if ($student): ?>
            <form action="update_student.php?id=<?php echo $student['id']; ?>" method="post" enctype="multipart/form-data" class="form-container">
                <input type="hidden" name="current_profile_picture" value="<?php echo htmlspecialchars($student['profile_pic']); ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">
                            <i class="fas fa-user"></i> Full Name
                        </label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($student['name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope"></i> Email Address
                        </label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="date_of_birth">
                            <i class="fas fa-calendar"></i> Date of Birth
                        </label>
                        <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($student['dob']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="course">
                            <i class="fas fa-graduation-cap"></i> Course
                        </label>
                        <input type="text" id="course" name="course" value="<?php echo htmlspecialchars($student['course']); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="grade">
                        <i class="fas fa-star"></i> Grade
                    </label>
                    <input type="text" id="grade" name="grade" value="<?php echo htmlspecialchars($student['grade']); ?>">
                </div>
                
                <?php if ($student['profile_pic']): ?>
                    <div class="current-picture">
                        <h4><i class="fas fa-camera"></i> Current Profile Picture</h4>
                        <img src="<?php echo htmlspecialchars($student['profile_pic']); ?>" alt="Current Profile Picture">
                        <div class="remove-picture-option">
                            <input type="checkbox" name="remove_picture" id="remove_picture" value="yes">
                            <label for="remove_picture">Remove current picture</label>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="form-group full-width">
                    <label>
                        <i class="fas fa-camera"></i> New Profile Picture
                    </label>
                    <div class="file-upload-container">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p>Click to upload or drag and drop</p>
                        <p style="font-size: 0.9rem; opacity: 0.7;">PNG, JPG, GIF up to 5MB</p>
                        <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
                    </div>
                </div>
                
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Update Student
                </button>
            </form>
        <?php else: ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i> Error: Student data could not be loaded for update.
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
