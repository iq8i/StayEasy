<?php
session_start();

// Database connection
$db = new mysqli('localhost', 'root', 'mysql', 'stayeasy_db');
if ($db->connect_error) {
    die(json_encode(['error' => 'Database connection failed']));
}

$property_id = $_GET['property_id'];

if (isset($_GET['property_id'], $_GET['checkin_date'], $_GET['checkout_date'])) {
    $property_id = $_GET['property_id'];
    $checkin_date = $_GET['checkin_date'];
    $checkout_date = $_GET['checkout_date'];

    // Check availability for the selected dates
    $query = "SELECT start_date, end_date, status FROM availability WHERE property_id = ? AND status = 'unavailable'";
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $property_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $is_available = true; // Assume available unless proven otherwise

    while ($row = $result->fetch_assoc()) {
        $start_date = $row['start_date'];
        $end_date = $row['end_date'];
        $status = $row['status'];
        // Check if the requested dates overlap with unavailable dates
        if (
            ($checkin_date >= $start_date && $checkin_date < $end_date) ||
            ($checkout_date > $start_date && $checkout_date <= $end_date) ||
            ($checkin_date < $start_date && $checkout_date > $end_date)
        ) {
            $is_available = false;
            break;
        }
    }

    // Return the JSON response for availability
    echo json_encode(['is_available' => $is_available]);

} else {
    // Use case 2: Managing availability (fetch all availability for the calendar)
    $query = "SELECT * FROM availability WHERE property_id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $property_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = [
            'title' => $row['status'],  // Optional, for debugging
            'start' => $row['start_date'],
            'end' => $row['end_date'],
            'status' => $row['status']  // This will be used in eventDidMount for color coding
        ];
    }

    // Return the events as JSON for the calendar
    echo json_encode($events);
}

$stmt->close();
$db->close();
?>
