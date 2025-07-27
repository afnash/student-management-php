<?php
session_start();


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    
    if ($username === "admin" && $password === "password123") {
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        
        setcookie("theme", "dark", time() + (86400 * 2), "/"); 
        header("Location: index.php"); 
        exit();
    } else {
        $login_error = "Invalid username or password.";
    }
}


if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    setcookie("theme", "", time() - 3600, "/"); 
    header("Location: index.php");
    exit();
}


if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
   
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Student Management System</title>
    <style>
        body { font-family: sans-serif; }
        .container { width: 300px; margin: 100px auto; padding: 20px; border: 1px solid #ccc; border-radius: 5px; }
        input[type="text"], input[type="password"] { width: 100%; padding: 8px; margin-bottom: 10px; box-sizing: border-box; }
        input[type="submit"] { background-color: #4CAF50; color: white; padding: 10px 15px; border: none; cursor: pointer; }
        .error { color: red; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Student Management System Login</h2>
        <?php if (isset($login_error)) { echo '<p class="error">' . $login_error . '</p>'; } ?>
        <form action="index.php" method="post">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <input type="submit" value="Login">
        </form>
    </div>
</body>
</html>
<?php
} else {
    
    $theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Student Management System</title>
    <style>
        body { font-family: sans-serif; background-color: <?php echo ($theme == 'dark' ? '#333' : '#f4f4f4'); ?>; color: <?php echo ($theme == 'dark' ? '#fff' : '#333'); ?>; }
        .navbar { background-color: #333; overflow: hidden; }
        .navbar a { float: left; display: block; color: white; text-align: center; padding: 14px 20px; text-decoration: none; }
        .navbar a:hover { background-color: #ddd; color: black; }
        .container { padding: 20px; }
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
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
        <p>This is the home page of your Student Information Management System.</p>
        <p>Your current theme preference is: <?php echo htmlspecialchars($theme); ?></p>
        <p>You can manage student details using the navigation above.</p>
    </div>
</body>
</html>
<?php
}
?>
