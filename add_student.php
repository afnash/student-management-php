<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student - Student Management System</title>
    <link rel="stylesheet" href="modern-styles.css">
    <style>
        .page-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .page-header h1 {
            color: white;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .page-header p {
            color: rgba(255, 255, 255, 0.8);
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
        
        .file-upload-container {
            position: relative;
            border: 2px dashed var(--glass-border);
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.05);
        }
        
        .file-upload-container:hover {
            border-color: var(--accent-color);
            background: rgba(255, 255, 255, 0.1);
        }
        
        .file-upload-container i {
            font-size: 3rem;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 1rem;
        }
        
        .file-upload-container p {
            color: rgba(255, 255, 255, 0.8);
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
            color: white;
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
            <h1><i class="fas fa-user-plus"></i> Add New Student</h1>
            <p>Register a new student in the management system</p>
        </div>
        
        <?php
        if (isset($_SESSION['add_status'])) {
            $status_class = $_SESSION['add_status']['success'] ? 'alert-success' : 'alert-error';
            $icon = $_SESSION['add_status']['success'] ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle';
            echo '<div class="alert ' . $status_class . '">';
            echo '<i class="' . $icon . '"></i> ' . $_SESSION['add_status']['message'];
            echo '</div>';
            unset($_SESSION['add_status']);
        }
        ?>
        
        <form action="process_add_student.php" method="post" enctype="multipart/form-data" class="form-container">
            <div class="form-row">
                <div class="form-group">
                    <label for="name">
                        <i class="fas fa-user"></i> Full Name
                    </label>
                    <input type="text" id="name" name="name" placeholder="Enter student's full name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Email Address
                    </label>
                    <input type="email" id="email" name="email" placeholder="Enter student's email" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="date_of_birth">
                        <i class="fas fa-calendar"></i> Date of Birth
                    </label>
                    <input type="date" id="date_of_birth" name="date_of_birth">
                </div>
                
                <div class="form-group">
                    <label for="course">
                        <i class="fas fa-graduation-cap"></i> Course
                    </label>
                    <input type="text" id="course" name="course" placeholder="Enter course name">
                </div>
            </div>
            
            <div class="form-group">
                <label for="grade">
                    <i class="fas fa-star"></i> Grade
                </label>
                <input type="text" id="grade" name="grade" placeholder="Enter student's grade">
            </div>
            
            <div class="form-group full-width">
                <label>
                    <i class="fas fa-camera"></i> Profile Picture
                </label>
                <div class="file-upload-container">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Click to upload or drag and drop</p>
                    <p style="font-size: 0.9rem; opacity: 0.7;">PNG, JPG, GIF up to 10MB</p>
                    <input type="file" id="profile_pic" name="profile_pic" accept="image/*">
                </div>
            </div>
            
            <button type="submit" class="btn-submit">
                <i class="fas fa-save"></i> Add Student
            </button>
        </form>
    </div>
</body>
</html>
