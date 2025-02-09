<?php
// Include the database connection file
include('database/connection.php');

// Start the session to get the current user's ID
session_start();

// Check if property_id is set in the POST request
if (isset($_POST['property_id'])) {
    // Sanitize the property_id to prevent SQL injection
    $property_id = $conn->real_escape_string($_POST['property_id']);
    
    // Get the current user's ID from the session
    $user_id = $_SESSION['user_id'];
    
    // Create a SQL query to delete the record from the favorites table
    $query = "DELETE FROM favorites WHERE user_id = '$user_id' AND property_id = '$property_id'";
    
    // Execute the query
    if ($conn->query($query) === TRUE) {
        // Redirect to favorites.php after deletion
        header("Location: favorites.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    echo "Invalid request.";
}
?>
