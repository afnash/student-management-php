<?php
session_start();

echo "<h2>Session Debug Information</h2>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "Session Data: ";
print_r($_SESSION);
echo "\n";
echo "POST Data: ";
print_r($_POST);
echo "\n";
echo "GET Data: ";
print_r($_GET);
echo "\n";
echo "Logged in status: " . (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true ? 'YES' : 'NO') . "\n";
echo "</pre>";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username']) && isset($_POST['password'])) {
    echo "<h3>Login Attempt</h3>";
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    echo "Username: " . $username . "\n";
    echo "Password: " . $password . "\n";
    
    if ($username === "admin" && $password === "password123") {
        echo "Login successful!\n";
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        echo "Session updated. Redirecting...\n";
        header("Location: debug_session.php");
        exit();
    } else {
        echo "Login failed!\n";
    }
}

if (isset($_GET['logout'])) {
    echo "<h3>Logout</h3>";
    session_unset();
    session_destroy();
    echo "Session destroyed. Redirecting...\n";
    header("Location: debug_session.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Session Debug</title>
</head>
<body>
    <h1>Session Debug Page</h1>
    
    <?php if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true): ?>
        <h2>Login Form</h2>
        <form method="post">
            <input type="text" name="username" placeholder="Username" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <button type="submit">Login</button>
        </form>
    <?php else: ?>
        <h2>Dashboard (Logged In)</h2>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
        <a href="debug_session.php?logout=true">Logout</a>
    <?php endif; ?>
    
    <p><a href="index.php">Go to Main Index</a></p>
</body>
</html> 