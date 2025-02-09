<?php
// Start the session
session_start();

// Destroy the session, which logs the user out
session_unset(); // Unset all session variables
session_destroy(); // Destroy the session

// Redirect the user to the login page or homepage
header("Location: index.php");
exit();
?>
