<?php
session_start();
include 'db_connect.php';
include 'functions.php'; // For logging if needed

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit();
}

$students = [];
$sql = "SELECT id, name, email, date_of_birth, course, grade, profile_picture FROM students";
$result = $conn->query($sql);

if ($result) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
    }
} else {
    // Error handling [cite: 11]
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
    <style>
        body { font-family: sans-serif; }
        .navbar { background-color: #333; overflow: hidden; }
        .navbar a { float: left; display: block; color: white; text-align: center; padding: 14px 20px; text-decoration: none; }
        .navbar a:hover { background-color: #ddd; color: black; }
        .container { padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .actions a { margin-right: 10px; text-decoration: none; color: blue; }
        .actions a.delete { color: red; }
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
        <h2>All Student Records</h2>
        <?php if (empty($students)): ?>
            <p>No student records found.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Date of Birth</th>
                        <th>Course</th>
                        <th>Grade</th>
                        <th>Picture</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['id']); ?></td>
                            <td><?php echo htmlspecialchars($student['name']); ?></td>
                            <td><?php echo htmlspecialchars($student['email']); ?></td>
                            <td><?php echo htmlspecialchars($student['date_of_birth']); ?></td>
                            <td><?php echo htmlspecialchars($student['course']); ?></td>
                            <td><?php echo htmlspecialchars($student['grade']); ?></td>
                            <td>
                                <?php if ($student['profile_picture']): ?>
                                    <img src="<?php echo htmlspecialchars($student['profile_picture']); ?>" alt="Profile Picture" width="50">
                                <?php else: ?>
                                    No Picture
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <a href="update_student.php?id=<?php echo $student['id']; ?>">Edit</a>
                                <a href="delete_student.php?id=<?php echo $student['id']; ?>" class="delete" onclick="return confirm('Are you sure you want to delete this student?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
