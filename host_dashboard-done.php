<?php
// Start the session
session_start();

// Check if the user is logged in and is a host
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'host') {
    header("Location: login.php");
    exit();
}

// Get the logged-in user's information
$username = $_SESSION['username'];
$user_profile_pic = "assets/images/profile_picture.jpg"; // Replace with the dynamic path to the user's profile picture

// Database connection
$db = new mysqli('localhost', 'root', 'AA', 'stayeasy_db');

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Fetch the host's properties from the database
$host_id = $_SESSION['user_id'];
$query = "SELECT p.*, 
          (SELECT photo_path FROM property_photos WHERE property_id = p.property_id LIMIT 1) AS photo_path
          FROM properties p 
          WHERE p.host_id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $host_id);
$stmt->execute();
$result = $stmt->get_result();
$host_properties = $result->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Host Dashboard - STAYEASY</title>
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
            <img src="<?php echo $user_profile_pic; ?>" alt="Profile Photo">
            <div class="profile-dropdown-content">
                <a href="host_dashboard.php">Dashboard</a>
                <a href="profile.php">Manage Profile</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Host Dashboard Content -->
    <div class="container mt-5">
        <h2 class="text-center">Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
        <p class="text-center">Manage your properties and listings with STAYEASY.</p>

        <!-- Add New Property Button -->
        <div class="text-center mb-4">
            <a href="add_property.php" class="btn btn-primary">Add New Property</a>
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
                            <a href="edit_property.php?id=<?php echo $property['property_id']; ?>" class="btn btn-primary">Edit</a>
                            <a href="delete_property.php?id=<?php echo $property['property_id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this property?')">Delete</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Booking Requests -->
        <h3 class="mt-5">Booking Requests</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Property</th>
                    <th>Guest</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <!-- TODO: Fetch and display booking requests from the database -->
                <tr>
                    <td>Sample Property Location</td>
                    <td>John Doe</td>
                    <td>2024-05-01</td>
                    <td>2024-05-05</td>
                    <td>Pending</td>
                    <td>
                        <a href="#" class="btn btn-sm btn-success">Approve</a>
                        <a href="#" class="btn btn-sm btn-danger">Decline</a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <footer class="text-center mt-5">
        <p>&copy; 2024 STAYEASY. All rights reserved.</p>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>

<?php
$db->close();
?>
