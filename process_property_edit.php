<?php

// Establish the database connection directly
$db = new mysqli('localhost', 'root', 'AA', 'stayeasy_db');
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $property_id = $_POST['property_id'];
    $location = $_POST['location'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    
    // Check if photos were uploaded (optional handling for file uploads)
    if (!empty($_FILES['photos']['name'][0])) {
        // Handle file uploads here (code not included)
    }

    // Use a prepared statement to securely update the property details
    $stmt = $db->prepare("UPDATE properties SET location = ?, description = ?, price = ? WHERE property_id = ?");
    $stmt->bind_param("ssdi", $location, $description, $price, $property_id);  // 's' for strings, 'd' for double (price)

    // Execute the statement
    if ($stmt->execute()) {
        // Redirect back to the host dashboard with a success message
        header("Location: host_dashboard.php?status=edit_success");
        exit;
    } else {
        echo "Error updating property: " . $stmt->error;
    }

    // Close the prepared statement
    $stmt->close();
}
?>
