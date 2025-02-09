<?php
// Start the session
session_start();

// Check if the user is logged in and is a host
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'host') {
    header("Location: login.php");
    exit();
}

// Check if the booking_id is set
if (isset($_GET['booking_id'])) {
    // Get booking ID
    $booking_id = (int)$_GET['booking_id'];

    // Database connection
    $db = new mysqli('localhost', 'root', 'mysql', 'stayeasy_db');
    if ($db->connect_error) {
        die("Connection failed: " . $db->connect_error);
    }

    // Delete the booking from the database (delete from bookings table)
    $query = "DELETE FROM bookings WHERE booking_id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $stmt->close();

    // Close database connection
    $db->close();

    // Redirect to the dashboard with a success message
    header("Location: host_dashboard.php?status=reject_success");
    exit();
}
?>
