<?php
session_start();
$db = new mysqli('localhost', 'root', 'mysql', 'stayeasy_db');

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Decode JSON data sent from the AJAX request
$locations = json_decode($_POST['locations'], true);
$price_range = $_POST['price_range'];

// Build the query based on the filters
$query = "SELECT p.*, 
          (SELECT photo_path 
           FROM property_photos 
           WHERE property_id = p.property_id 
           LIMIT 1) AS photo_path 
          FROM properties p WHERE 1=1";

$params = [];
$types = "";

// Filter by location
if (!empty($locations)) {
    $placeholders = implode(',', array_fill(0, count($locations), '?'));
    $query .= " AND location IN ($placeholders)";
    $types .= str_repeat("s", count($locations));
    $params = array_merge($params, $locations);
}

// Filter by price range
if (!empty($price_range)) {
    list($min_price, $max_price) = explode('-', $price_range);
    $query .= " AND price BETWEEN ? AND ?";
    $types .= "ii";
    $params[] = $min_price;
    $params[] = $max_price;
}

// Prepare and execute the statement
$stmt = $db->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Display the filtered properties
while ($property = $result->fetch_assoc()) {
    echo '<div class="property-card">';
    echo '<h3>' . htmlspecialchars($property['location']) . '</h3>';
    
    // Display the property image or a default image if none is available
    if (!empty($property['photo_path'])) {
        echo '<img src="' . htmlspecialchars($property['photo_path']) . '" alt="Property Image">';
    } else {
        echo '<img src="assets/images/default_property.jpg" alt="No Image Available">';
    }
    
    echo '<p>Price: $' . htmlspecialchars($property['price']) . '</p>';
    echo '<p>Description: ' . htmlspecialchars($property['description']) . '</p>';
    echo '<a href="booking_form.php?property_id=' . $property['property_id'] . '" class="btn">Book Now</a>';
    echo '</div>';
}

$db->close();


?>
