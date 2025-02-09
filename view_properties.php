<?php
session_start();

// Check if the user is logged in and is a guest
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'guest') {
    header("Location: login.php");
    exit();
}
$username = $_SESSION['username'];
$user_profile_pic = "assets/images/profile_picture.jpg"; // Replace with the dynamic path to the user's profile picture


// Database connection
$db = new mysqli('localhost', 'root', 'mysql', 'stayeasy_db');

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Fetch all properties from the database
$query = "SELECT p.*, 
                 (SELECT photo_path 
                  FROM property_photos 
                  WHERE property_id = p.property_id 
                  LIMIT 1) AS photo_path
          FROM properties p";
$result = $db->query($query);


$search_term = '';
$search_results = [];


if (isset($_POST['search'])) 
    $search_term = $_POST['search_term'] ?? '';  // Ensure search_term is set

    if (!empty($search_term)) {
    $query = "SELECT * FROM properties WHERE location LIKE ?";
    $stmt = $db->prepare($query);
    $like_search_term = '%' . $search_term . '%';
    $stmt->bind_param('s', $like_search_term);  // Only one string parameter
    $stmt->execute();
    $result = $stmt->get_result();
    $search_results = $result->fetch_all(MYSQLI_ASSOC);
}else {
    // If search term is empty, set an empty result
    $search_results = [];
}

?>
<?php if (isset($_SESSION['message']) || isset($_SESSION['error'])): ?>
    <div id="feedbackMessage" 
         class="alert <?php echo isset($_SESSION['message']) ? 'alert-success' : 'alert-danger'; ?>">
        <?php
        echo isset($_SESSION['message']) ? $_SESSION['message'] : $_SESSION['error'];
        unset($_SESSION['message'], $_SESSION['error']); // Clear messages after setting them
        ?>
    </div>
<?php endif; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Properties - STAYEASY</title>
    <link rel="stylesheet" href="assets/css/style.css"> <!-- Link to your existing CSS file -->
    <style>
        .property-list {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            margin-top: 20px;
        }
        .property-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 15px;
            margin: 15px;
            width: 300px;
            background-color: #fff;
        }
        .property-card img {
            width: 100%;
            height: auto;
            border-radius: 5px;
        }
        .property-card h3 {
            margin: 10px 0;
            font-size: 1.2em;
        }
        .property-card p {
            color: #666;
        }
        .property-card .btn {
            background-color: #6c757d;
            color: #fff;
            border: none;
            padding: 10px;
            width: 100%;
            border-radius: 5px;
            text-align: center;
        }
        .property-card .btn:hover {
            background-color: #5a6268;
        }
        .profile-dropdown {
            position: relative;
            display: inline-block;
        }

        .profile-dropdown img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            cursor: pointer;
        }

        .profile-dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: #f9f9f9;
            min-width: 160px;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.1);
            z-index: 1;
        }

        .profile-dropdown-content a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }

        .profile-dropdown-content a:hover {
            background-color: #f1f1f1;
        }

        .profile-dropdown:hover .profile-dropdown-content {
            display: block;
        }

        .search-bar {
            text-align: center;
            margin: 20px 0;
        }

        .search-bar input[type="text"] {
            padding: 10px;
            width: 60%;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .search-bar button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .search-results {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
        }

        .search-results .card {
            margin: 10px;
        }
        #filterButton {
        padding: 10px 20px;
        background-color: #007bff;
        color: white;
        border: none;
        cursor: pointer;
        font-size: 1em;
    }

    /* Filter panel styling */
    #filterPanel {
        position: absolute;
        top: 50px;
        left: 20px;
        padding: 20px;
        background-color: #f8f8f8;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        width: 200px;
    }

    /* Hide filter panel initially */
    #filterPanel h3 {
        margin-top: 0;
    }
    </style>
</head>
<body>

<!-- Complete navbar copied from guest dashboard -->
<nav class="navbar navbar-expand-lg navbar-dark">
        <a class="navbar-brand" href="index.php">
            <img src="assets/images/1.png" alt="STAYEASY Logo">
            STAYEASY
        </a>
         <!-- Search Bar -->
         <div class="search-bar">
            <form method="POST" action="">
                <input type="text" name="search_term" placeholder="Search by name or location"  style="width:500px" value="<?php echo htmlspecialchars($search_term); ?>">
                <button type="submit" name="search">Search</button>
                <button id="filterButton">Filter</button>

<!-- Hidden Filter Panel (Dropdown) -->
<div id="filterPanel" style="display: none;">
    <h3>Filter Properties</h3>

    

    <label for="price_range">Price Range:</label>
    <select id="price_range">
        <option value="">All Prices</option>
        <option value="0-100">0 - 100</option>
        <option value="100-500">100 - 500</option>
        <option value="500-1000">500 - 1000</option>
        <option value="1000-5000">1000 - 5000</option>
    </select>
     <button id="applyFilters">Apply Filters</button>
     
     <script>
        
        setTimeout(() => {
        const feedbackMessage = document.getElementById('feedbackMessage');
        if (feedbackMessage) {
            feedbackMessage.style.display = 'none';
        }
    }, 3000); // 3000ms = 3 seconds


document.getElementById("filterButton").onclick = function(event) {
    event.preventDefault();
    var panel = document.getElementById("filterPanel");
    panel.style.display = panel.style.display === "none" ? "block" : "none";
};

document.getElementById("applyFilters").onclick = function(event) {
    event.preventDefault();

    // Collect selected location checkboxes
    var selectedLocations = [];
    document.querySelectorAll('input[name="location"]:checked').forEach(function(checkbox) {
        selectedLocations.push(checkbox.value);
    });

    // Get selected price range
    var priceRange = document.getElementById("price_range").value;

    // Send AJAX request to fetch filtered properties
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "filter_properties.php", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            // Update the properties listing with filtered content
            document.querySelector(".search-results").innerHTML = xhr.responseText;
        }
    };

    xhr.send("locations=" + JSON.stringify(selectedLocations) + "&price_range=" + priceRange);

    // Hide the filter panel after applying filters
    document.getElementById("filterPanel").style.display = "none";
};

</script>

        </div>
    </div>
        <div class="ml-auto profile-dropdown">
            <img src="<?php echo $user_profile_pic; ?>" alt="Profile Photo">
            <div class="profile-dropdown-content">
               <a href="guest_dashboard.php">Dashboard</a>
                <a href="profile.php">Manage Profile</a>
                <a href="my_bookings.php">My Bookings</a>
                <a href="booking_history.php">My History</a>
                <a href="favorites.php">My Favorites</a> 
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>
        <!-- Search Results -->

        <div class="container">
         <h1 class="text-center">Available Properties</h1>
         <div class="search-results">
            <?php if ($search_results): ?>
                <?php foreach ($search_results as $property): ?>
                    <div class="card">
                        
                        <div class="card-body">
                            
                            <h5 class="card-title"><?php echo htmlspecialchars($property['location']); ?></h5>
                            <p class="card-text">description: <?php echo htmlspecialchars($property['description']); ?></p>
                            <p class="card-text">Price: $<?php echo htmlspecialchars($property['price']); ?> per night</p>
                            <a href="booking_form.php?property_id=<?php echo ($property['property_id']); ?>" class="btn btn-primary">Book Now</a>
                            <form action="add_to_favorites.php" method="post" style="display: inline;">
                            <input type="hidden" name="property_id" value="<?php echo htmlspecialchars($property['property_id']); ?>">
                            <button type="submit" class="btn btn-primary">Add to Favorites</button>
                            </form>
                            <a href="reviews.php?property_id=<?php echo ($property['property_id']); ?>" class="btn btn-primary">reviews</a>

                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: echo 'there is no properties based on your search criteria'?>
                <p class="text-center"></p>
            <?php endif; ?>
     </div>

     

     <div class="property-list">
     <?php
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

        // "Book Now" button
        echo '<a href="booking_form.php?property_id=' . $property['property_id'] . '" class="btn">Book Now</a>';

        // "Add to Favorites" button
        echo '<form action="add_to_favorites.php" method="POST" ">';
        echo '<input type="hidden" name="property_id" value="' . $property['property_id'] . '">';
        echo '<button type="submit" class="btn btn-favorite" style="position:relative;">Add to Favorites</button>';
        echo '</form>';
        echo   '<a href="reviews.php?property_id=' . $property['property_id'] . '" class="btn">Reviews</a>';
        echo '</div>';
    }
    ?>
    </div>
</div>

</div>

</body>
</html>

<?php
$db->close();
?>
