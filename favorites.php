<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'guest') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Database connection
$db = new mysqli('localhost', 'root', 'mysql', 'stayeasy_db');

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Fetch user's favorite properties
$query = "SELECT p.*, f.added_at, 
                 (SELECT photo_path 
                  FROM property_photos 
                  WHERE property_id = p.property_id 
                  LIMIT 1) AS photo_path
          FROM properties p
          JOIN favorites f ON p.property_id = f.property_id
          WHERE f.user_id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$favorites = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$db->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Favorite Properties - STAYEASY</title>
    <style>
        /* Navbar styling */
        .navbar {
            background-color: #f1f1f1;
            color: #333;
            padding: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .navbar .navbar-brand {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .navbar .navbar-brand img {
            height: 40px;
            margin-right: 10px;
        }

        .navbar .menu a {
            color: #333;
            margin-left: 20px;
            text-decoration: none;
            font-weight: bold;
        }

        .navbar .menu a:hover {
            color: #555;
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
            background-color: #ddd;
        }

        .profile-dropdown:hover .profile-dropdown-content {
            display: block;
        }

        /* Content styling */
        .container {
            width: 80%;
            margin: auto;
            margin-top: 20px;
            padding: 20px;
            background: #fff;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .property-list {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
        }

        .property-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 15px;
            margin: 15px;
            width: 300px;
            background-color: #fff;
            text-align: center;
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

        .property-card .btn-book, .property-card .btn-remove {
            display: block;
            padding: 10px;
            width: 100%;
            border-radius: 5px;
            text-align: center;
            margin-top: 10px;
            color: #fff;
            border: none;
            cursor: pointer;
        }

        .property-card .btn-book {
            background-color: #4CAF50;
        }

        .property-card .btn-book:hover {
            background-color: #45a049;
        }

        .property-card .btn-remove {
            background-color: #d9534f;
        }

        .property-card .btn-remove:hover {
            background-color: #c9302c;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <a class="navbar-brand" href="index.php">
            <img src="assets/images/1.png" alt="STAYEASY Logo">
            STAYEASY
        </a>
        
        <div class="profile-dropdown">
            <img src="assets/images/profile_picture.jpg" alt="Profile Photo">
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

    <!-- Favorites Section -->
    <div class="container">
        <h1 class="text-center">My Favorite Properties</h1>

        <?php if (!empty($favorites)): ?>
            <div class="property-list">
                <?php foreach ($favorites as $property): ?>
                    <div class="property-card">
                        <h3><?php echo htmlspecialchars($property['location']); ?></h3>
                        
                        <?php if (!empty($property['photo_path'])): ?>
                            <img src="<?php echo htmlspecialchars($property['photo_path']); ?>" alt="Property Image">
                        <?php else: ?>
                            <img src="assets/images/default_property.jpg" alt="No Image Available">
                        <?php endif; ?>

                        <p>Price: $<?php echo htmlspecialchars($property['price']); ?> per night</p>
                        <p>Description: <?php echo htmlspecialchars($property['description']); ?></p>

                        <!-- Book Now Button -->
                        <a href="booking_form.php?property_id=<?php echo $property['property_id']; ?>" class="btn-book" style="width:280px">Book Now</a>

                        <!-- Remove from Favorites Button -->
                        <form action="remove_from_favorites.php" method="POST">
                            <input type="hidden" name="property_id" value="<?php echo $property['property_id']; ?>">
                            <button type="submit" class="btn-remove">Remove from Favorites</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-center">You have no favorite properties.</p>
        <?php endif; ?>
    </div>
</body>
</html>
