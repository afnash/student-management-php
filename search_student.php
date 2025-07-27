<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit();
}

$search_query = isset($_GET['query']) ? htmlspecialchars($_GET['query']) : '';
$students = [];

if (!empty($search_query)) {
    // Search by name or email
    $sql = "SELECT id, name, email, date_of_birth, course, grade, profile_picture FROM students WHERE name LIKE ? OR email LIKE ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        echo "Error preparing statement: " . $conn->error;
        exit();
    }
    $param = "%" . $search_query . "%";
    $stmt->bind_param("ss", $param, $param);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
    }
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Student - Student Management System</title>
    <style>
        body { font-family: sans-serif; }
        .navbar { background-color: #333; overflow: hidden; }
        .navbar a { float: left; display: block; color: white; text-align: center; padding: 14px 20px; text-decoration: none; }
        .navbar a:hover { background-color: #ddd; color: black; }
        .container { padding: 20px; }
        form { margin-top: 20px; }
        input[type="search"] { width: 70%; padding: 8px; margin-right: 10px; box-sizing: border-box; }
        input[type="submit"] { background-color: #007bff; color: white; padding: 10px 15px; border: none; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
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
        <h2>Search Student Records</h2>
        <form action="search_student.php" method="get">
            <label for="query">Search by Name or Email:</label>
            <input type="search" id="query" name="query" value="<?php echo $search_query; ?>" placeholder="Enter name or email">
            <input type="submit" value="Search">
        </form>

        <?php if (!empty($search_query)): ?>
            <h3>Search Results for "<?php echo $search_query; ?>"</h3>
            <?php if (empty($students)): ?>
                <p>No students found matching your search.</p>
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
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
