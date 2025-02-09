<?php
session_start();

// Check if the user is logged in and is a host
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'host') {
    header("Location: login.php");
    exit();
}

// Database connection
$db = new mysqli('localhost', 'root', 'mysql', 'stayeasy_db');

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $host_id = $_SESSION['user_id'];
    $location = $_POST['location'];
    $price = $_POST['price'];
    $description = $_POST['description'];

    // Prepare SQL statement
    $stmt = $db->prepare("INSERT INTO properties (host_id, location, price, description) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isds", $host_id, $location, $price, $description);

    if ($stmt->execute()) {
        $property_id = $db->insert_id;

        // Handle file uploads
        if (isset($_FILES['photos'])) {
            $upload_dir = "uploads/property_photos/";
            foreach ($_FILES['photos']['tmp_name'] as $key => $tmp_name) {
                $file_name = $_FILES['photos']['name'][$key];
                $file_path = $upload_dir . $property_id . "_" . $file_name;
                
                if (move_uploaded_file($tmp_name, $file_path)) {
                    // Insert photo path into database
                    $photo_stmt = $db->prepare("INSERT INTO property_photos (property_id, photo_path) VALUES (?, ?)");
                    $photo_stmt->bind_param("is", $property_id, $file_path);
                    $photo_stmt->execute();
                    $photo_stmt->close();
                }
            }
        }

        header("Location: host_dashboard.php?success=1");
    } else {
        header("Location: add_property.php?error=1");
    }

    $stmt->close();
} else {
    header("Location: add_property.php");
}

$db->close();
?>
