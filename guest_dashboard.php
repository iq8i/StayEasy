<?php
// Start the session
session_start();

if (isset($_GET['status']) && $_GET['status'] == 'success') {
    echo '<div style="color: green; text-align: center; margin: 20px 0;">Payment succeeded and booking confirmed!</div>';
}

// Check if the user is logged in and is a guest
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'guest') {
    header("Location: login.php");
    exit();
}

// Get the logged-in user's information
$username = $_SESSION['username'];
$user_profile_pic = "assets/images/profile_picture.jpg"; // Replace with the dynamic path to the user's profile picture

// Database connection
$db = new mysqli('localhost', 'root', 'mysql', 'stayeasy_db');
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Initialize search term
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


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Dashboard - STAYEASY</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
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

    <label>Location:</label>
    <label><input type="checkbox" name="location" value="Riyadh"> Riyadh</label>
    <label><input type="checkbox" name="location" value="Jeddah"> Jeddah</label>
    <label><input type="checkbox" name="location" value="Dammam"> Dammam</label>

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
    // Toggle filter panel visibility
    document.getElementById("filterButton").onclick = function(event) {
        event.preventDefault(); // Prevents page refresh
        var panel = document.getElementById("filterPanel");
        panel.style.display = panel.style.display === "none" ? "block" : "none";
    };

    // Apply filters and update listings with filtered data
    document.getElementById("applyFilters").onclick = function(event) {
        event.preventDefault(); // Prevents page refresh

        // Collect selected location checkboxes
        var selectedLocations = [];
        document.querySelectorAll('input[name="location"]:checked').forEach(function(checkbox) {
            selectedLocations.push(checkbox.value);
        });

        // Get selected price range
        var priceRange = document.getElementById("price_range").value;

        // Make AJAX request to apply filters and update listings
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "view_properties.php", true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                // Update the properties listing with filtered content
                document.getElementById("propertiesListing").innerHTML = xhr.responseText;
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
    <!-- Guest Dashboard Content -->
    <div class="container mt-5">
        <h2 class="text-center">Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
        <p class="text-center">Explore the best properties around the world with STAYEASY.</p>

          <!-- Search Results -->
          <div class="search-results">
            <?php if ($search_results): ?>
                <?php foreach ($search_results as $property): ?>
                    <div class="card">
                        
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($property['location']); ?></h5>
                            <p class="card-text">Location: <?php echo htmlspecialchars($property['location']); ?></p>
                            <p class="card-text">Price: $<?php echo htmlspecialchars($property['price']); ?> per night</p>
                            <a href="booking_form.php?property_id=<?php echo $property['location']; ?>" class="btn btn-primary">Book Now</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center"></p>
            <?php endif; ?>
        </div>

       <!-- Property Listings -->
       <div class="row mt-4">
            <div class="col-md-4">
                <div class="card">
                    <img src="assets/images/property1.jpg" class="card-img-top" alt="Property 1">
                    <div class="card-body">
                        <h5 class="card-title">Cozy Apartment in Paris</h5>
                        <p class="card-text">Price: $100 per night</p>
                        <a href="booking_form.php?property_id=10" class="btn btn-primary">Book Now</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <img src="assets/images/property2.jpg" class="card-img-top" alt="Property 2">
                    <div class="card-body">
                        <h5 class="card-title">Modern Flat in New York</h5>
                        <p class="card-text">Price: $200 per night</p>
                        <a href="booking_form.php?property_id=11" class="btn btn-primary">Book Now</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <img src="assets/images/property3.jpg" class="card-img-top" alt="Property 3">
                    <div class="card-body">
                        <h5 class="card-title">Beach House in Malibu</h5>
                        <p class="card-text">Price: $300 per night</p>
                        <a href="booking_form.php?property_id=9" class="btn btn-primary">Book Now</a>
                    </div>
                </div>
            </div>
        </div>
<div class="text-center mt-4">
            <a href="view_properties.php" class="btn btn-outline-dark">View All Properties</a>
        </div>
    <!-- Footer -->
    <footer class="text-center mt-5">
        <p>&copy; 2024 STAYEASY. All rights reserved.</p>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        window.onload = function() {
            document.getElementById('search_term').value = '';  // Reset the search field
        };
    </script>
</body>
</html>
