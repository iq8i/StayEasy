<?php
// Establish the database connection directly
$db = new mysqli('localhost', 'root', 'mysql', 'stayeasy_db');
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

if (isset($_GET['property_id'])) {
    $property_id = $_GET['property_id'];

    // Execute the query to fetch the property data
    $sql = "SELECT location, description, price FROM properties WHERE property_id = $property_id";
    $result = $db->query($sql);

    // Check if a property was found
    if ($result->num_rows > 0) {
        $property = $result->fetch_assoc();
        $location = $property['location'];
        $description = $property['description'];
        $price = $property['price'];
    } else {
        echo "No property found for this ID.";
        exit;
    }
} else {
    echo "No property ID provided!";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Property</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Edit Property</h2>
        <form action="process_property_edit.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="property_id" value="<?php echo $property_id; ?>">
            <div class="form-group">
                <label for="location">Location</label>
                <input type="text" class="form-control" id="location" name="location" value="<?php echo $location; ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3" required><?php echo $description; ?></textarea>
            </div>
            <div class="form-group">
                <label for="price">Price per Night ($)</label>
                <input type="number" class="form-control" id="price" name="price" min="0" step="0.01" value="<?php echo $price; ?>" required>
            </div>
            <div class="form-group">
                <label for="photos">Photos</label>
                <input type="file" class="form-control-file" id="photos" name="photos[]" multiple accept="image/*">
                <small>Leave empty if you don't want to update photos.</small>
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>
</body>
</html>
