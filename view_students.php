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

$students = [];
$sql = "SELECT id, name, email, dob, course, grade, profile_pic FROM students";
$result = $conn->query($sql);

if ($result) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
    }
} else {
    echo "Error: " . $conn->error;
    logAction("Error fetching students: " . $conn->error);
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Students - Student Management System</title>
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
        
        .stats-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }
        
        .stat-item {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .stat-item:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-medium);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: rgba(248, 250, 252, 0.8);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .table-container {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow-medium);
            margin: 2rem 0;
            animation: slideIn 0.8s ease;
        }
        
        .table-header {
            background: rgba(15, 23, 42, 0.4);
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--glass-border);
        }
        
        .table-header h3 {
            color: var(--text-primary);
            font-size: 1.3rem;
            font-weight: 600;
            margin: 0;
        }
        
        .student-table {
            width: 100%;
            border-collapse: collapse;
            background: transparent;
        }
        
        .student-table th {
            background: rgba(15, 23, 42, 0.4);
            color: var(--text-primary);
            padding: 1.5rem 1rem;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.9rem;
            border-bottom: 1px solid var(--glass-border);
        }
        
        .student-table td {
            padding: 1.5rem 1rem;
            color: var(--text-primary);
            border-bottom: 1px solid rgba(148, 163, 184, 0.1);
            transition: all 0.3s ease;
        }
        
        .student-table tr:hover td {
            background: rgba(99, 102, 241, 0.1);
            transform: scale(1.01);
        }
        
        .profile-pic {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--glass-border);
        }
        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-edit {
            background: var(--gradient-primary);
            color: var(--text-primary);
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-delete {
            background: var(--gradient-secondary);
            color: var(--text-primary);
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-edit:hover,
        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }
        
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: rgba(248, 250, 252, 0.8);
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: var(--text-primary);
        }
        
        .empty-state p {
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }
        
        .btn-add-student {
            background: var(--gradient-primary);
            color: var(--text-primary);
            padding: 1rem 2rem;
            border: none;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .btn-add-student:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-medium);
        }
        
        @media (max-width: 768px) {
            .student-table {
                font-size: 0.9rem;
            }
            
            .student-table th,
            .student-table td {
                padding: 1rem 0.5rem;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 0.25rem;
            }
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
            <h1><i class="fas fa-users"></i> Student Records</h1>
            <p>View and manage all student information</p>
        </div>
        
        <div class="stats-summary">
            <div class="stat-item">
                <div class="stat-number"><?php echo count($students); ?></div>
                <div class="stat-label">Total Students</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo count(array_filter($students, function($s) { return !empty($s['course']); })); ?></div>
                <div class="stat-label">Enrolled</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo count(array_filter($students, function($s) { return !empty($s['profile_pic']); })); ?></div>
                <div class="stat-label">With Photos</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo count(array_unique(array_column($students, 'course'))); ?></div>
                <div class="stat-label">Active Courses</div>
            </div>
        </div>
        
        <?php if (empty($students)): ?>
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <h3>No Students Found</h3>
                <p>There are currently no student records in the system.</p>
                <a href="add_student.php" class="btn-add-student">
                    <i class="fas fa-user-plus"></i> Add First Student
                </a>
            </div>
        <?php else: ?>
            <div class="table-container">
                <div class="table-header">
                    <h3><i class="fas fa-list"></i> All Student Records</h3>
                </div>
                <table class="student-table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-id-card"></i> ID</th>
                            <th><i class="fas fa-user"></i> Name</th>
                            <th><i class="fas fa-envelope"></i> Email</th>
                            <th><i class="fas fa-calendar"></i> Date of Birth</th>
                            <th><i class="fas fa-graduation-cap"></i> Course</th>
                            <th><i class="fas fa-star"></i> Grade</th>
                            <th><i class="fas fa-camera"></i> Photo</th>
                            <th><i class="fas fa-cogs"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td>#<?php echo htmlspecialchars($student['id']); ?></td>
                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td><?php echo htmlspecialchars($student['dob'] ? date('M d, Y', strtotime($student['dob'])) : 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($student['course'] ?: 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($student['grade'] ?: 'N/A'); ?></td>
                                <td>
                                    <?php if ($student['profile_pic']): ?>
                                        <img src="<?php echo htmlspecialchars($student['profile_pic']); ?>" alt="Profile Picture" class="profile-pic">
                                    <?php else: ?>
                                        <div class="glass-icon" style="width: 40px; height: 40px; font-size: 1rem;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="update_student.php?id=<?php echo $student['id']; ?>" class="btn-edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="delete_student.php?id=<?php echo $student['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this student?');">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
