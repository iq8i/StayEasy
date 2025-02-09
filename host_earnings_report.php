<?php
// Start the session
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

// Get the logged-in user's information
$host_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$user_profile_pic = "assets/images/profile_picture.jpg"; // Replace with the dynamic path to the user's profile picture

// Fetch the host's properties along with total earnings from bookings
$query = "
    SELECT p.property_id, p.location, p.price, 
           IFNULL(SUM(DATEDIFF(b.end_date, b.start_date)), 0) AS days_booked
    FROM properties p
    LEFT JOIN bookings b ON p.property_id = b.property_id
    WHERE p.host_id = ?
    GROUP BY p.property_id;
";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $host_id);
$stmt->execute();
$result = $stmt->get_result();
$host_properties = $result->fetch_all(MYSQLI_ASSOC);

// Calculate total earnings for the host
$total_earnings = 0;
foreach ($host_properties as $property) {
    $days_booked = $property['days_booked'] ?? 0;
    $price_per_night = $property['price'];
    $total_earnings += $days_booked * $price_per_night;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Host Earnings Report - STAYEASY</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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
        .booking-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
        h1 {
            font-size: 1.5em;
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        .property-info {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .property-info h2 {
            font-size: 1.4em;
            margin-bottom: 10px;
        }
        .property-info p {
            margin: 5px 0;
            color: #666;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
            color: #555;
        }
        input[type="date"],
        input[type="number"],
        button {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
        }
        input[type="date"],
        input[type="number"] {
            background-color: #f9f9f9;
        }
        button {
            background-color: #007bff;
            color: white;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark">
    <a class="navbar-brand" href="index.php">
        <img src="assets/images/1.png" alt="STAYEASY Logo" height="30">
        STAYEASY
    </a>
    <div class="ml-auto profile-dropdown">
        <img src="<?php echo $user_profile_pic; ?>" alt="Profile Photo">
        <div class="profile-dropdown-content">
            <a href="host_dashboard.php">Dashboard</a>
            <a href="profileHost.php">Manage Profile</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>
</nav>

<!-- Host Earnings Report Content -->
<div class="container mt-5">
    <h2 class="text-center">Earnings Report for <?php echo htmlspecialchars($username); ?></h2>
    <p class="text-center">View your total earnings from all properties listed.</p>

    <!-- Total Earnings -->
    <div class="text-center mb-4">
        <h3>Total Earnings: $<?php echo number_format($total_earnings, 2); ?></h3>
    </div>

    <!-- Property Listings and Earnings -->
    <h3>Your Properties and Earnings</h3>
    <div class="row mt-4">
        <?php foreach ($host_properties as $property): ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="assets/images/property.jpg" class="card-img-top" alt="<?php echo htmlspecialchars($property['location']); ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($property['location']); ?></h5>
                        <p class="card-text">Price: $<?php echo htmlspecialchars($property['price']); ?> per night</p>

                        <!-- Calculate Total Earnings from this property -->
                        <?php
                        $days_booked = $property['days_booked'] ?? 0;
                        $price_per_night = $property['price'];
                        $property_earnings = $days_booked * $price_per_night;
                        ?>

                        <!-- Display Property Earnings -->
                        <p class="card-text" style="color: green;">Total Earnings: $<?php echo number_format($property_earnings, 2); ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Footer -->
<footer class="bg-dark text-white text-center py-3 mt-5">
    <p>&copy; 2024 STAYEASY. All rights reserved.</p>
</footer>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>

<?php
// Close the database connection
$db->close();
?>
