<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['property_id']) && !empty($_POST['property_id'])) {
    $property_id = intval($_POST['property_id']);

    // Debugging: Log variables
    error_log("Debug: User ID = $user_id, Property ID = $property_id");

    $db = new mysqli('localhost', 'root', 'mysql', 'stayeasy_db');
    if ($db->connect_error) {
        die("Connection failed: " . $db->connect_error);
    }

    // Check if the property exists in the database
    $property_check_query = "SELECT * FROM properties WHERE property_id = ?";
    $property_check_stmt = $db->prepare($property_check_query);
    $property_check_stmt->bind_param("i", $property_id);
    $property_check_stmt->execute();
    $property_check_result = $property_check_stmt->get_result();

    if ($property_check_result->num_rows === 0) {
        $_SESSION['error'] = "The selected property does not exist.";
        header("Location: view_properties.php");
        exit();
    }

    

    // Check if the property is already in the user's favorites
    $check_query = "SELECT * FROM favorites WHERE user_id = ? AND property_id = ?";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bind_param("ii", $user_id, $property_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows === 0) {
        // If not already favorited, add it to the favorites table
        $insert_query = "INSERT INTO favorites (user_id, property_id) VALUES (?, ?)";
        $insert_stmt = $db->prepare($insert_query);
        $insert_stmt->bind_param("ii", $user_id, $property_id);

        if ($insert_stmt->execute()) {
            $_SESSION['message'] = "Property added to favorites!";
        } else {
            $_SESSION['error'] = "Failed to add to favorites: " . $insert_stmt->error;
        }

        $insert_stmt->close();
    } else {
        $_SESSION['message'] = "Property is already in your favorites.";
    }

    $check_stmt->close();
    $db->close();

    header("Location: view_properties.php");
    exit();
} else {
    $_SESSION['error'] = "Invalid property ID.";
    header("Location: view_properties.php");
    exit();
}
