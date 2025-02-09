<?php
// Start the session
session_start();
include('database/connection.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $email = $profile_pic = "";

// Fetch user info
$stmt = $conn->prepare("SELECT username, email, profile_pic FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $email, $profile_pic);
$stmt->fetch();
$stmt->close();

// Handle profile picture update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Check if a profile picture is uploaded
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['tmp_name'] != "") {
        $target_dir = "uploads/";

        // Check if the uploads directory exists, if not, create it
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);  // Create the directory with proper permissions
        }

        $target_file = $target_dir . basename($_FILES['profile_pic']['name']);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is a real image
        $check = getimagesize($_FILES['profile_pic']['tmp_name']);
        if ($check !== false) {
            // Attempt to move the uploaded file
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_file)) {
                // Update the database with the new profile picture
                $stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
                $stmt->bind_param("si", $target_file, $user_id);
                $stmt->execute();
                $stmt->close();

                echo "Profile picture updated successfully!";
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        } else {
            echo "File is not an image.";
        }
    }

    // Check if the password update is requested (only if both current_password and new_password fields are provided)
    if (!empty($_POST['current_password']) && !empty($_POST['new_password'])) {
        // Fetch the current password from the database
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($stored_password);
        $stmt->fetch();
        $stmt->close();

        // Verify the current password
        if (password_verify($_POST['current_password'], $stored_password)) {
            // Update the password
            $new_password_hash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $new_password_hash, $user_id);
            $stmt->execute();
            $stmt->close();

            echo "Password updated successfully!";
        } else {
            echo "Current password is incorrect.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
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
            <img src="<?php echo $profile_pic ?: 'assets/images/default_profile.png'; ?>" alt="Profile Photo">
            <div class="profile-dropdown-content">
            <a href="host_dashboard.php">Dashboard</a>
                <a href="profileHost.php">Manage Profile</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>
    <div class="container">
        <h2>Profile</h2>
        <form action="profile.php" method="POST" enctype="multipart/form-data">
            <!-- Profile Picture -->
            <div class="form-group">
                <label for="profile_pic">Profile Picture</label>
                <input type="file" class="form-control-file" name="profile_pic" id="profile_pic">
            </div>

            <!-- Username (disabled) -->
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" name="username" id="username" value="<?php echo htmlspecialchars($username); ?>" disabled>
            </div>

            <!-- Email (disabled) -->
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>" disabled>
            </div>

            <!-- Password Update -->
            <h4>Change Password</h4>
            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" class="form-control" name="current_password" id="current_password" >
            </div>
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" class="form-control" name="new_password" id="new_password" >
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" class="form-control" name="confirm_password" id="confirm_password" >
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>

        <!-- Error or success message -->
        <?php if (isset($error)) echo "<p class='text-danger'>$error</p>"; ?>
        <?php if (isset($success)) echo "<p class='text-success'>$success</p>"; ?>
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
