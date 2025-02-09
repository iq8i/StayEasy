<?php
session_start();

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

// Fetch user's booking history
$query = "SELECT bh.*, p.location, p.price, 
                 (SELECT photo_path 
                  FROM property_photos 
                  WHERE property_id = p.property_id 
                  LIMIT 1) AS photo_path
          FROM booking_history bh
          JOIN properties p ON bh.property_id = p.property_id
          WHERE bh.guest_id = ?";  // Updated to bh.guest_id
$stmt = $db->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$bookings = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$db->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Booking History - STAYEASY</title>
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

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table th, .table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .table th {
            background-color: #f2f2f2;
        }

        .btn-view {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
        }

        .btn-view:hover {
            background-color: #0056b3;
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

    <!-- Booking History Section -->
    <div class="container">
        <h1 class="text-center">My Booking History</h1>

        <?php if (!empty($bookings)): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Property</th>
                        <th>Location</th>
                        <th>Price</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Booking Date</th>
                    
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($booking['location']); ?></td>
                            <td><?php echo htmlspecialchars($booking['location']); ?></td>
                            <td>$<?php echo htmlspecialchars($booking['price']); ?></td>
                            <td><?php echo htmlspecialchars($booking['start_date']); ?></td>
                            <td><?php echo htmlspecialchars($booking['end_date']); ?></td>
                            <td><?php echo htmlspecialchars($booking['booking_date']); ?></td>
                            
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center">You have no booking history.</p>
        <?php endif; ?>
    </div>
</body>
</html> 
