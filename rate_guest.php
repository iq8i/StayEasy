<?php
session_start();

// Check if user is logged in and is a host
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'host') {
    die("You must be logged in as a host to rate guests.");
}

// Database connection
$db = new mysqli('localhost', 'root', 'mysql', 'stayeasy_db');
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Get the host's ID from session
$host_id = $_SESSION['user_id'];

// Fetch unrated completed bookings for the host
$query = $db->prepare("
    SELECT b.booking_id, u.username AS guest_name, u.id AS guest_id, p.location, b.start_date, b.end_date
    FROM bookings b
    JOIN users u ON b.guest_id = u.id
    JOIN properties p ON b.property_id = p.property_id
    WHERE p.host_id = ? AND b.status = 'completed' AND b.rated = 0
    ORDER BY b.end_date DESC;
");
$query->bind_param("i", $host_id);
$query->execute();
$result = $query->get_result();
$unrated_bookings = $result->fetch_all(MYSQLI_ASSOC);

// Handle rating form submission
$successMessage = "";
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit_rating'])) {
    $booking_id = $_POST['booking_id'];
    $guest_id = $_POST['guest_id']; // Ensure this is being set correctly from the form
    $rating = (int)$_POST['rating'];
    $review = !empty($_POST['review']) ? trim($_POST['review']) : null;

    // Debug: Check the value of guest_id
    if (empty($guest_id)) {
        die("Guest ID is missing.");
    }

    // Validate rating
    if ($rating < 1 || $rating > 5) {
        $successMessage = "Invalid rating. Please select a value between 1 and 5.";
    } else {
        // Update the booking as rated
        $update_query = $db->prepare("UPDATE bookings SET rated = 1 WHERE booking_id = ?");
        $update_query->bind_param("i", $booking_id);
        $update_query->execute();

        // Insert the rating and review into the guest_ratings table
        $insert_query = $db->prepare("INSERT INTO guest_ratings (booking_id, guest_id, rating, review) VALUES (?, ?, ?, ?)");
        $insert_query->bind_param("iiis", $booking_id, $guest_id, $rating, $review);
        if ($insert_query->execute()) {
            $successMessage = "Rating submitted successfully!";
        } else {
            $successMessage = "Failed to submit rating. Please try again.";
        }
    }
}

// Close the database connection
$db->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate Guest</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            max-width: 800px;
            margin: 50px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1, h2 {
            text-align: center;
            color: #333;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .form-group button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .form-group button:hover {
            background-color: #0056b3;
        }

        .success-message {
            color: green;
            text-align: center;
        }

        .booking-card {
            background-color: #fff;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .booking-card strong {
            color: #333;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Rate Your Guests</h1>

    <!-- Display Success Message -->
    <?php if (!empty($successMessage)): ?>
        <p class="success-message"><?php echo htmlspecialchars($successMessage); ?></p>
    <?php endif; ?>

    <!-- List of unrated bookings -->
    <?php if (empty($unrated_bookings)): ?>
        <p>No guests to rate at the moment.</p>
    <?php else: ?>
        <?php foreach ($unrated_bookings as $booking): ?>
            <div class="booking-card">
                <h3><?php echo htmlspecialchars($booking['guest_name']); ?></h3>
                <p><strong>Property:</strong> <?php echo htmlspecialchars($booking['location']); ?></p>
                <p><strong>Check-in:</strong> <?php echo htmlspecialchars($booking['start_date']); ?></p>
                <p><strong>Check-out:</strong> <?php echo htmlspecialchars($booking['end_date']); ?></p>

                <!-- Rating Form -->
                <form method="POST">
                    <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                    <input type="hidden" name="guest_id" value="<?php echo $booking['guest_id']; ?>"> <!-- Hidden field for guest_id -->

                    <div class="form-group">
                        <label for="rating">Rating (1-5):</label>
                        <select name="rating" id="rating" required>
                            <option value="">Select</option>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="review">Review (optional):</label>
                        <textarea name="review" id="review" rows="4" placeholder="Write your review..."></textarea>
                    </div>

                    <div class="form-group">
                        <button type="submit" name="submit_rating">Submit Rating</button>
                    </div>
                </form>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

</body>
</html>
