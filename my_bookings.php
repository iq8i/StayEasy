<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'guest') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$db = new mysqli('localhost', 'root', 'mysql', 'stayeasy_db');
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

$query = "SELECT b.booking_id, p.location, p.price, b.start_date, b.end_date 
          FROM bookings b 
          JOIN properties p ON b.property_id = p.property_id 
          WHERE b.guest_id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - STAYEASY</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Add styles for the message box */
        .message {
            background-color: #4CAF50; /* Green */
            color: white;
            padding: 10px;
            margin: 20px 0;
            text-align: center;
            border-radius: 5px;
        }

        .error {
            background-color: #d9534f; /* Red */
            color: white;
            padding: 10px;
            margin: 20px 0;
            text-align: center;
            border-radius: 5px;
        }

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

        .btn-primary { 
            background-color: #4CAF50; /* Green color */
            color: white; 
            padding: 8px 12px; 
            text-decoration: none; 
            border-radius: 4px; 
        }

        .btn-primary:hover { 
            background-color: #45a049; 
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

    <!-- Display Success or Error Message -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="message"><?php echo $_SESSION['message']; ?></div>
        <?php unset($_SESSION['message']); ?>
    <?php elseif (isset($_SESSION['error'])): ?>
        <div class="error"><?php echo $_SESSION['error']; ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- My Bookings Section -->
    <div class="container">
        <h2 class="text-center">My Bookings</h2>
        <?php if ($result->num_rows > 0): ?>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Location</th>
                        <th>Price per Night</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['location']); ?></td>
                            <td>$<?php echo htmlspecialchars($row['price']); ?></td>
                            <td><?php echo htmlspecialchars($row['start_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['end_date']); ?></td>
                            <td>
                                <a href="booking_details.php?booking_id=<?php echo $row['booking_id']; ?>" class="btn btn-primary">View Details</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center">You have no bookings.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
$stmt->close();
$db->close();
?>
