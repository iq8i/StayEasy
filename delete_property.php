<?php
// Establish the database connection directly
$db = new mysqli('localhost', 'root', 'mysql', 'stayeasy_db');
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

if (isset($_GET['property_id'])) {
    $property_id = $_GET['property_id'];

    // Execute the query to delete the property
    $sql = "DELETE FROM properties WHERE property_id = '$property_id'";
    
    try {
        if ($db->query($sql) === TRUE) {
            // Redirect back to the host dashboard with a success message
            header("Location: host_dashboard.php?status=delete_success");
            exit;
        } else {
            throw new Exception("Foreign key constraint error");
        }
    } catch (Exception $e) {
        // Display an alert and redirect back to the host dashboard
        echo "<script>
                alert('This property cannot be deleted as it is associated with other guests.');
                window.location.href = 'host_dashboard.php';
              </script>";
        exit;
    }
} else {
    echo "No property ID provided!";
    exit;
}
?>