<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to leave a review.");
}

// Database connection
$db = new mysqli('localhost', 'root', 'mysql', 'stayeasy_db');
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Check if property_id is provided via POST or GET
$property_id = null;
if (isset($_POST['property_id'])) {
    $property_id = (int)$_POST['property_id'];
} elseif (isset($_GET['property_id'])) {
    $property_id = (int)$_GET['property_id'];
} else {
    die("Property ID is not specified.");
}

// Ensure property_id exists in the properties table
$query = $db->prepare("SELECT p.property_id, p.location FROM properties as p WHERE property_id = ?");
$query->bind_param("i", $property_id);
$query->execute();
$result = $query->get_result();
$property = $result->fetch_assoc();
if ($result->num_rows === 0) {
    die("Invalid Property ID. Please check the property.");
}
$query->close();

// Handle review form submission
$successMessage = "";
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submit_review'])) {
    $user_id = $_SESSION['user_id']; // Use session-based user ID
    $rating = (int)$_POST['rating'];
    $comment = !empty($_POST['comment']) ? trim($_POST['comment']) : null;

    // Validate input
    if ($rating < 1 || $rating > 5) {
        $successMessage = "Invalid rating. Please select a value between 1 and 5.";
    } else {
        // Insert the review into the database
        $stmt = $db->prepare("INSERT INTO reviews (property_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $property_id, $user_id, $rating, $comment);
        if ($stmt->execute()) {
            $successMessage = "Review submitted successfully!";
        } else {
            $successMessage = "Failed to submit review. Please try again.";
        }
        $stmt->close();
    }
}

// Fetch all reviews for the property
$query = $db->prepare("SELECT r.rating, r.comment, u.username 
                       FROM reviews r
                       JOIN users u ON r.user_id = u.id
                       WHERE r.property_id = ?");
$query->bind_param("i", $property_id);
$query->execute();
$result = $query->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Reviews</title>
    <style>
        /* CSS for styling */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }

        .container {
            width: 90%;
            max-width: 800px;
            margin: 30px auto;
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
            color: #555;
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
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .form-group button:hover {
            background-color: #0056b3;
        }

        .success-message {
            color: green;
            margin-top: 10px;
        }

        .reviews-container {
            margin-top: 30px;
        }

        .review-card {
            background-color: #fff;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .review-card strong {
            color: #333;
        }

        .review-card p {
            margin: 10px 0 0;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="container">
    <div class="form-group">
    <button><a href="view_properties.php" style="text-decoration: none; color:white;" class="btn">back</a></button>
       </div>
        <h1> Reviews for Property : <?php echo htmlspecialchars($property['location']); ?></h1>

        <!-- Display Success Message -->
        <?php if (!empty($successMessage)): ?>
            <p class="success-message"><?php echo htmlspecialchars($successMessage); ?></p>
        <?php endif; ?>

        <!-- Leave a Review Form -->
        <h2>Leave a Review</h2>
        <form action="reviews.php" method="POST">
            <input type="hidden" name="property_id" value="<?php echo htmlspecialchars($property_id); ?>">
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
                <label for="comment">Comment (optional):</label>
                <textarea name="comment" id="comment" rows="4" placeholder="Write your review..."></textarea>
            </div>

            <div class="form-group">
                <button type="submit" name="submit_review"><a href="guest_dashboard.php" style="text-decoration: none; color:white;">Submit Review</a></button>
            </div>
        </form>

        <!-- Display All Reviews -->
        <h2>All Reviews</h2>
        <div class="reviews-container">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="review-card">
                        <strong><?php echo htmlspecialchars($row['username']); ?></strong> rated it 
                        <strong><?php echo htmlspecialchars($row['rating']); ?>/5</strong>
                        <p><?php echo htmlspecialchars($row['comment'] ?: 'No comment provided.'); ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align: center; color: #555;">No reviews yet for this property.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php
// Close the query and database connection
$query->close();
$db->close();
?>
