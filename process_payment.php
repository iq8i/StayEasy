<?php
session_start();

// Check if payment details and session booking data exist
if (!isset($_SESSION['booking'], $_POST['card_number'], $_POST['expiry_date'], $_POST['cvv'])) {
    header("Location: view_properties.php");
    exit();
}

// Retrieve session data and sanitize input
$guest_id = $_SESSION['user_id'];
$property_id = $_SESSION['booking']['property_id'];
$checkin_date = $_SESSION['booking']['checkin_date'];
$checkout_date = $_SESSION['booking']['checkout_date'];
$guests = $_SESSION['booking']['guests'];

// Payment details
$card_number = str_replace(' ', '', $_POST['card_number']);
$expiry_date = $_POST['expiry_date'];
$cvv = $_POST['cvv'];

// Simulate payment validation
if (strlen($card_number) !== 16 || strlen($cvv) !== 3) {
    header("Location: payment.php?error=InvalidPayment");
    exit();
}

// Database connection
$db = new mysqli('localhost', 'root', 'mysql', 'stayeasy_db');
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

try {
    // Start transaction
    $db->begin_transaction();

    // Prevent double booking by checking if the property is already booked for the selected dates
    $check_booking = $db->prepare("
        SELECT * FROM bookings 
        WHERE property_id = ? 
          AND ((start_date <= ? AND end_date >= ?) 
          OR (start_date <= ? AND end_date >= ?))
    ");
    $check_booking->bind_param("issss", $property_id, $checkout_date, $checkin_date, $checkin_date, $checkout_date);
    $check_booking->execute();
    $booking_result = $check_booking->get_result();

    if ($booking_result->num_rows > 0) {
        throw new Exception("Property is already booked for the selected dates.");
    }

    // Insert booking into the bookings table
    $stmt = $db->prepare("
        INSERT INTO bookings (guest_id, property_id, start_date, end_date, guests) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iissi", $guest_id, $property_id, $checkin_date, $checkout_date, $guests);

    if (!$stmt->execute()) {
        throw new Exception("Failed to insert into bookings: " . $stmt->error);
    }

    // Insert into booking_history table
    $history_stmt = $db->prepare("
        INSERT INTO booking_history (guest_id, property_id, start_date, end_date, guests) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $history_stmt->bind_param("iissi", $guest_id, $property_id, $checkin_date, $checkout_date, $guests);

    if (!$history_stmt->execute()) {
        throw new Exception("Failed to insert into booking_history: " . $history_stmt->error);
    }

    // Commit transaction if both insertions succeed
    $db->commit();

    // Clear session data and set success message
    unset($_SESSION['booking']);
    $_SESSION['success_message'] = "Your booking has been confirmed and recorded in your history!";
    header("Location: success.php");

} catch (Exception $e) {
    // Roll back transaction on failure
    $db->rollback();

    // Log the error and set error message in session
    error_log("Transaction failed: " . $e->getMessage());
    $_SESSION['error_message'] = "Booking could not be completed. Please try again.";
    header("Location: success.php");
} finally {
    // Close statements and connection
    $stmt->close();
    $history_stmt->close();
    $db->close();
}
?>
