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

// Fetch pending bookings
$query = "
    SELECT b.booking_id, p.location, u.username AS guest_name, b.start_date, b.end_date, b.status
    FROM bookings b
    JOIN properties p ON b.property_id = p.property_id
    JOIN users u ON b.guest_id = u.id
    WHERE p.host_id = ? AND b.status = 'pending'
    ORDER BY b.start_date;
";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $host_id);
$stmt->execute();
$result = $stmt->get_result();
$pending_bookings = $result->fetch_all(MYSQLI_ASSOC);

// Handle booking cancellation
if (isset($_GET['cancel_booking_id'])) {
    $cancel_booking_id = (int) $_GET['cancel_booking_id'];

    // Update the booking status to "canceled"
    $update_query = "UPDATE bookings SET status = 'canceled' WHERE booking_id = ?";
    $update_stmt = $db->prepare($update_query);
    $update_stmt->bind_param("i", $cancel_booking_id);
    $update_stmt->execute();

    // Redirect back to the page with a success message
    header("Location: host_cancellations.php?status=cancel_success");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Host Cancellation - STAYEASY</title>
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
        .form-group {
            margin-bottom: 20px;
        }
        label {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
            color: #555;
        }
        .btn-danger {
            background-color: #d9534f;
            border: none;
        }
        .btn-danger:hover {
            background-color: #c9302c;
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

<!-- Host Cancellation Content -->
<div class="container mt-5">
    <h2 class="text-center">Manage Your Bookings</h2>
    <p class="text-center">You can cancel pending bookings here.</p>

    <!-- Display success or error messages -->
    <?php if (isset($_GET['status']) && $_GET['status'] == 'cancel_success'): ?>
        <div class="alert alert-success text-center">
            Booking has been successfully canceled.
        </div>
    <?php endif; ?>

    <!-- Pending Bookings Table -->
    <h3>Pending Bookings</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Guest</th>
                <th>Property</th>
                <th>Check-in</th>
                <th>Check-out</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pending_bookings as $booking): ?>
                <tr>
                    <td><?php echo htmlspecialchars($booking['guest_name']); ?></td>
                    <td><?php echo htmlspecialchars($booking['location']); ?></td>
                    <td><?php echo htmlspecialchars($booking['start_date']); ?></td>
                    <td><?php echo htmlspecialchars($booking['end_date']); ?></td>
                    <td>
                        <a href="host_cancellations.php?cancel_booking_id=<?php echo $booking['booking_id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this booking?')">Cancel</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($pending_bookings)): ?>
                <tr>
                    <td colspan="5" class="text-center">No pending bookings to cancel.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
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
