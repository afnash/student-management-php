<?php

function validateStudentData($name, $email, $dob, $course, $grade) {
	$errors = [];
	if(empty($name)) {
		$errors[] = "name is required.";
	}
	if(empty($email)) {
                $errors[] = "email is required.";
        }
	if(empty($dob)) {
                $errors[] = "dob is required.";
        }
	
	return $errors;
}

function logAction($message) {
	$log_file = __DIR__ ."/activity.log";
	$timestamp = date("Y-m-d H:i:s");
	$log_entry = "[" .$timestamp . "]" . $message . PHP_EQL;
	file_put_contents($log_file,$log_entry, FILE_APPEND);
}
?>

