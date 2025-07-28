<?php
session_start();

echo "<h1>Login Test</h1>";

// Test login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    if ($username === "admin" && $password === "password123") {
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        echo "<p style='color: green;'>Login successful! Redirecting...</p>";
        header("Location: test_login.php");
        exit();
    } else {
        echo "<p style='color: red;'>Login failed!</p>";
    }
}

// Test logout
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    echo "<p style='color: blue;'>Logged out! Redirecting...</p>";
    header("Location: test_login.php");
    exit();
}

echo "<h2>Session Info:</h2>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "Session Data: ";
print_r($_SESSION);
echo "</pre>";

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    echo "<h2 style='color: green;'>✅ Logged In!</h2>";
    echo "<p>Welcome, " . htmlspecialchars($_SESSION['username']) . "!</p>";
    echo "<a href='test_login.php?logout=true'>Logout</a>";
} else {
    echo "<h2 style='color: red;'>❌ Not Logged In</h2>";
    echo "<form method='post'>";
    echo "<input type='text' name='username' placeholder='Username' required><br>";
    echo "<input type='password' name='password' placeholder='Password' required><br>";
    echo "<button type='submit'>Login</button>";
    echo "</form>";
}

echo "<br><a href='index.php'>Go to Main App</a>";
?> 