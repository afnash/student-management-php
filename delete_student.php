<?php
session_start();
include 'db_connect.php';
include 'functions.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    $student_id = (int)$_GET['id'];

    if ($student_id > 0) {
        // First, get the profile picture path to delete the file 
        $stmt_select = $conn->prepare("SELECT profile_picture FROM students WHERE id = ?");
        $stmt_select->bind_param("i", $student_id);
        $stmt_select->execute();
        $result_select = $stmt_select->get_result();
        $row = $result_select->fetch_assoc();
        $profile_picture_to_delete = $row['profile_picture'] ?? '';
        $stmt_select->close();

        // Delete from database 
        $stmt_delete = $conn->prepare("DELETE FROM students WHERE id = ?");
        if ($stmt_delete === false) {
            $_SESSION['delete_status'] = ['success' => false, 'message' => "Database error: " . $conn->error];
            logAction("Error preparing delete statement: " . $conn->error);
            header("Location: view_students.php");
            exit();
        }
        $stmt_delete->bind_param("i", $student_id);

        try { // Error and Exception Handling [cite: 11]
            if ($stmt_delete->execute()) {
                // Delete the profile picture file from the server 
                if (!empty($profile_picture_to_delete) && file_exists($profile_picture_to_delete)) {
                    unlink($profile_picture_to_delete);
                }
                $_SESSION['delete_status'] = ['success' => true, 'message' => "Student deleted successfully!"];
                logAction("Student deleted: ID " . $student_id); // Log student deletion 
            } else {
                $_SESSION['delete_status'] = ['success' => false, 'message' => "Error deleting student: " . $stmt_delete->error];
                logAction("Error deleting student: " . $stmt_delete->error);
            }
        } catch (mysqli_sql_exception $e) {
            $_SESSION['delete_status'] = ['success' => false, 'message' => "Database exception: " . $e->getMessage()];
            logAction("Database exception during student deletion: " . $e->getMessage());
        }

        $stmt_delete->close();
    } else {
        $_SESSION['delete_status'] = ['success' => false, 'message' => "Invalid student ID for deletion."];
    }
} else {
    $_SESSION['delete_status'] = ['success' => false, 'message' => "No student ID provided for deletion."];
}

$conn->close();
header("Location: view_students.php"); // Redirect back to view_students
exit();
?>
