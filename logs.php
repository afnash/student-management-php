<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit();
}

$log_file = __DIR__ . "/activity.log";
$log_content = "Log file not found or empty.";

// Error and Exception Handling for file operations [cite: 11]
try {
    if (file_exists($log_file)) {
        $log_content = file_get_contents($log_file);
        if ($log_content === false) {
            throw new Exception("Could not read log file.");
        }
        if (empty($log_content)) {
            $log_content = "Log file is empty.";
        }
    } else {
        // Create the log file if it doesn't exist
        file_put_contents($log_file, "Log file created on " . date("Y-m-d H:i:s") . PHP_EOL);
        $log_content = "Log file was just created and is now empty.";
    }
} catch (Exception $e) {
    $log_content = "Error accessing log file: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Logs - Student Management System</title>
    <style>
        body { font-family: sans-serif; }
        .navbar { background-color: #333; overflow: hidden; }
        .navbar a { float: left; display: block; color: white; text-align: center; padding: 14px 20px; text-decoration: none; }
        .navbar a:hover { background-color: #ddd; color: black; }
        .container { padding: 20px; }
        pre { background-color: #f8f8f8; border: 1px solid #ddd; padding: 10px; white-space: pre-wrap; word-wrap: break-word; }
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
        <h2>System Activity Logs</h2>
        <pre><?php echo htmlspecialchars($log_content); ?></pre>
    </div>
</body>
</html>
