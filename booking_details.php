<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'guest') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['booking_id'])) {
    echo "Booking ID not provided.";
    exit();
}

$booking_id = $_GET['booking_id'];
$db = new mysqli('localhost', 'root', 'mysql', 'stayeasy_db');
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Query to retrieve booking details along with host name
$query = "SELECT b.booking_id, b.property_id, p.location, p.price, b.start_date, b.end_date, u.username AS host_name
          FROM bookings b
          JOIN properties p ON b.property_id = p.property_id
          JOIN users u ON b.host_id = u.id
          WHERE b.booking_id = ? AND b.guest_id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param('ii', $booking_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();

if (!$booking) {
    echo "No booking details found for booking ID: " . htmlspecialchars($booking_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details - STAYEASY</title>
    <link rel="stylesheet" href="assets/css/style.css">
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

        /* Profile Dropdown */
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

        /* Content Styling */
        .container { 
            width: 80%; 
            margin: auto; 
            padding: 20px; 
            background: #fff; 
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); 
            border-radius: 8px; 
            margin-top: 20px;
        }
        
        h2.text-center { 
            text-align: center; 
            margin-bottom: 20px; 
        }

        .table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px; 
        }
        
        .table-bordered { 
            border: 1px solid #dee2e6; 
        }
        
        .table th, .table td { 
            padding: 12px; 
            border: 1px solid #dee2e6; 
            text-align: left; 
        }

        .btn-primary, .btn-cancel { 
            background-color: #4CAF50; /* Green color */
            color: white; 
            padding: 8px 12px; 
            text-decoration: none; 
            border-radius: 4px; 
        }

        .btn-primary:hover, .btn-cancel:hover { 
            background-color: #45a049; 
        }

        .btn-cancel {
            background-color: #d9534f; /* Red color */
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <a class="navbar-brand" href="index.php">
            <img src="assets/images/1.png" alt="STAYEASY Logo">
            STAYEASY
        </a>
        <div class="ml-auto profile-dropdown">
            <img src="assets\images\profile_picture.jpg" alt="Profile Photo">
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

    <!-- Booking Details Section -->
    <div class="container">
        <h2 class="text-center">Booking Details</h2>
        <table class="table table-bordered">
            <tr>
                <th>Location</th>
                <td><?php echo htmlspecialchars($booking['location']); ?></td>
            </tr>
            <tr>
                <th>Price per Night</th>
                <td>$<?php echo htmlspecialchars($booking['price']); ?></td>
            </tr>
            <tr>
                <th>Start Date</th>
                <td><?php echo htmlspecialchars($booking['start_date']); ?></td>
            </tr>
            <tr>
                <th>End Date</th>
                <td><?php echo htmlspecialchars($booking['end_date']); ?></td>
            </tr>
            <tr>
                <th>Host</th>
                <td><?php echo htmlspecialchars($booking['host_name']); ?></td>
            </tr>
        </table>
        <div class="text-center">
            <a href="my_bookings.php" class="btn btn-primary">Back to Bookings</a>
            <form action="cancel_booking.php" method="POST" style="display:inline;">
                <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
                <button type="submit" class="btn btn-cancel">Cancel Booking</button>
            </form>
            <!-- Go to Reviews Button -->
<?php if (isset($booking['property_id'])): ?>
    <a href="reviews.php?property_id=<?php echo htmlspecialchars($booking['property_id']); ?>" 
        style="position: relative;left: 300px; color:#333; ">Leave a Review</a>
<?php else: ?>
    <p style="color: red;">Property ID is not available for this booking.</p>
<?php endif; ?>

        </div>
    </div>
</body>
</html>

<?php
$stmt->close();
$db->close();
?>
