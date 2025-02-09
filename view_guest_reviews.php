<?php
// Start the session and check if the user is logged in as a host
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'host') {
    header("Location: login.php");
    exit();
}

// Database connection
$db = new mysqli('localhost', 'root', 'mysql', 'stayeasy_db');
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Get guest_id from URL (the ID of the guest whose reviews are being viewed)
if (isset($_GET['guest_id'])) {
    $guest_id = (int)$_GET['guest_id'];

    // Fetch reviews left by the guest
    $query = "
        SELECT r.*, p.location 
        FROM reviews r 
        JOIN properties p ON r.property_id = p.property_id 
        WHERE r.user_id = ?;
    ";
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $guest_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $reviews = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    // If guest_id is not provided, redirect to host dashboard
    header("Location: host_dashboard.php");
    exit();
}

// Get guest name from users table
$query = "SELECT username FROM users WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $guest_id);
$stmt->execute();
$stmt->bind_result($guest_name);
$stmt->fetch();
$stmt->close();

// Close DB connection
$db->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Reviews - STAYEASY</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .reviews-container {
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
        h2 {
            font-size: 1.8em;
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
        }
        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background-color: #f1f1f1;
            font-weight: bold;
        }
        table td {
            font-size: 1.1em;
        }
        .rating {
            color: #ffcc00;
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
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="index.php">
        <img src="assets/images/1.png" alt="STAYEASY Logo" height="30">
        STAYEASY
    </a>
    <div class="ml-auto profile-dropdown">
        <img src="assets/images/profile_picture.jpg" alt="Profile Photo" width="50" height="50">
        <div class="profile-dropdown-content">
            <a href="host_dashboard.php">Dashboard</a>
            <a href="profileHost.php">Manage Profile</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>
</nav>

<!-- Guest Reviews Section -->
<div class="container mt-5">
    <h2 class="text-center">Reviews for <?php echo htmlspecialchars($guest_name); ?></h2>

    <?php if (!empty($reviews)): ?>
        <div class="card">
            <div class="card-body">
                <table>
                    <thead>
                        <tr>
                            <th>Property</th>
                            <th>Rating</th>
                            <th>Comment</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reviews as $review): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($review['location']); ?></td>
                                <td>
                                    <span class="rating">
                                        <?php
                                        $rating = $review['rating'];
                                        for ($i = 0; $i < 5; $i++) {
                                            if ($i < $rating) {
                                                echo "&#9733;"; // Full star
                                            } else {
                                                echo "&#9734;"; // Empty star
                                            }
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($review['comment']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <p class="text-center">No reviews found for this guest.</p>
    <?php endif; ?>

    <div class="text-center mt-4">
        <a href="host_dashboard.php" class="btn btn-primary">Back to Dashboard</a>
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
