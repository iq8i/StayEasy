<?php
// Start the session
session_start();

// Check if the user is logged in and is a host
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'host') {
    header("Location: login.php");
    exit();
}

// Display feedback messages if 'status' is set in the URL
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'edit_success') {
        echo '<div id="feedback-message" class="alert alert-success">Property updated successfully!</div>';
    } elseif ($_GET['status'] == 'delete_success') {
        echo '<div id="feedback-message" class="alert alert-success">Property deleted successfully!</div>';
    } elseif ($_GET['status'] == 'error') {
        echo '<div id="feedback-message" class="alert alert-danger">An error occurred. Please try again.</div>';
    } elseif ($_GET['status'] == 'accept_success') {
        echo '<div id="feedback-message" class="alert alert-success">Booking request accepted successfully!</div>';
    } elseif ($_GET['status'] == 'reject_success') {
        echo '<div id="feedback-message" class="alert alert-success">Booking request rejected successfully!</div>';
    }
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

// Fetch the host's properties from the database with the first property image
$query = "
    SELECT p.*, 
           (SELECT pp.photo_path 
            FROM property_photos pp 
            WHERE pp.property_id = p.property_id 
            LIMIT 1) AS photo_path,
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

// Fetch pending booking requests
$query = "
    SELECT b.*, p.location, u.username AS guest_name
    FROM bookings b
    JOIN properties p ON b.property_id = p.property_id
    JOIN users u ON b.guest_id = u.id
    WHERE p.host_id = ?
    ORDER BY b.start_date;
";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $host_id);
$stmt->execute();
$result = $stmt->get_result();
$pending_bookings = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Host Dashboard - STAYEASY</title>
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
        #feedback-message {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            padding: 15px 20px;
            font-size: 16px;
            text-align: center;
            width: auto;
            opacity: 1;
            transition: opacity 2s ease;
        }
    </style>
</head>
<body>
<script>
// JavaScript to auto-hide the feedback message with a smooth fade-out after 5 seconds
setTimeout(function() {
    var feedback = document.getElementById('feedback-message');
    if (feedback) {
        feedback.style.opacity = '0'; // Trigger fade-out
        setTimeout(function() {
            feedback.style.display = 'none'; // Hide element after fade-out is complete
        }, 2000); // Wait for 2 seconds for the fade-out to complete
    }
}, 5000);
</script>

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

<!-- Host Dashboard Content -->
<div class="container mt-5">
    <h2 class="text-center">Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
    <p class="text-center">Manage your properties and listings with STAYEASY.</p>

    <!-- Dashboard Actions -->
    <div class="text-center mb-4">
        <a href="add_property.php" class="btn btn-primary">Add New Property</a>
        <a href="manage_availability.php" class="btn btn-secondary">Manage Availability</a>
    </div>

    <!-- Property Listings -->
    <h3>Your Properties</h3>
    <div class="row mt-4">
        <?php foreach ($host_properties as $property): ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="<?php echo htmlspecialchars($property['photo_path'] ?? 'assets/images/default_property.jpg'); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($property['location']); ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($property['location']); ?></h5>
                        <p class="card-text">Price: $<?php echo htmlspecialchars($property['price']); ?> per night</p>

                        <!-- Calculate Total Earnings based on bookings -->
                        <?php
                        $days_booked = $property['days_booked'] ?? 0; // Comes from SQL query
                        $price_per_night = $property['price'];

                        // Calculate total earnings
                        $total_earnings = $days_booked * $price_per_night;
                        ?>
                        <p>Total Earnings: $<?php echo number_format($total_earnings, 2); ?></p>

                        <a href="edit_property.php?property_id=<?php echo $property['property_id']; ?>" class="btn btn-warning">Edit</a>
                        <a href="delete_property.php?property_id=<?php echo $property['property_id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this property?')">Delete</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pending Booking Requests -->
    <h3 class="mt-5">Pending Booking Requests</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Property</th>
                <th>Guest</th>
                <th>Check-in</th>
                <th>Check-out</th>
                <th>Guests</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pending_bookings as $booking): ?>
                <tr>
                    <td><?php echo htmlspecialchars($booking['location']); ?></td>
                    <td><?php echo htmlspecialchars($booking['guest_name']); ?></td>
                    <td><?php echo htmlspecialchars($booking['start_date']); ?></td>
                    <td><?php echo htmlspecialchars($booking['end_date']); ?></td>
                    <td><?php echo htmlspecialchars($booking['guests']); ?></td>
                    <td>
                        <a href="accept_booking.php?booking_id=<?php echo $booking['booking_id']; ?>" class="btn btn-success" onclick="return confirm('Are you sure you want to accept this booking request?')">Accept</a>
                        <a href="reject_booking.php?booking_id=<?php echo $booking['booking_id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to reject this booking request?')">Reject</a>
                        <a href="view_guest_reviews.php?guest_id=<?php echo $booking['guest_id']; ?>" class="btn btn-info">View Reviews</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($pending_bookings)): ?>
                <tr>
                    <td colspan="6" class="text-center">No pending booking requests.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Footer -->
<footer class="footer mt-auto py-3">
    <div class="container text-center">
        <p class="text-muted">© 2024 STAYEASY. All rights reserved.</p>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
