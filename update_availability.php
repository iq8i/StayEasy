<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'host') {
    exit('Unauthorized');
}

// Database connection
$db = new mysqli('localhost', 'root', 'mysql', 'stayeasy_db');
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Check if all required POST data is present
if (isset($_POST['property_id'], $_POST['start_date'], $_POST['end_date'])) {
    $property_id = $_POST['property_id'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // Check if the date range is already marked
    $query = "SELECT status FROM availability WHERE property_id = ? AND start_date = ? AND end_date = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("iss", $property_id, $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Date is already marked; toggle the status
        $row = $result->fetch_assoc();
        $new_status = ($row['status'] === 'available') ? 'unavailable' : 'available';

        $update_query = "UPDATE availability SET status = ? WHERE property_id = ? AND start_date = ? AND end_date = ?";
        $update_stmt = $db->prepare($update_query);
        $update_stmt->bind_param("siss", $new_status, $property_id, $start_date, $end_date);
        if ($update_stmt->execute()) {
            echo $new_status;  // Return the new status
        } else {
            echo 'error';  // Failed to update
        }
    } else {
        // Date is not marked; add a new record as 'unavailable'
        $insert_query = "INSERT INTO availability (property_id, start_date, end_date, status) VALUES (?, ?, ?, 'unavailable')";
        $insert_stmt = $db->prepare($insert_query);
        $insert_stmt->bind_param("iss", $property_id, $start_date, $end_date);
        if ($insert_stmt->execute()) {
            echo 'unavailable';  // Return new status as 'unavailable'
        } else {
            echo 'error';  // Failed to insert
        }
    }
    $stmt->close();
} else {
    echo 'missing_parameters';  // Missing required parameters
}

$db->close();
?>
