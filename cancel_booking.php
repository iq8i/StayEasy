<?php
// Start the session
session_start();

// Check if the user is logged in and is a host
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'guest') {
    header("Location: login.php"e j);
    exit();
}

// Display feedback messages if 'status' is set in the URL
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'cancellation_success') {
        echo '<div id="feedback-message" class="alert alert-success">Booking canceled successfully. You will receive your refund within 5 business days.</div>';
    } elseif ($_GET['status'] == 'error') {
        echo '<div id="feedback-message" class="alert alert-danger">An error occurred. Please try again.</div>';
    }
}

// Database connection
$db = new mysqli('localhost', 'root', 'mysql', 'stayeasy_db');
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Check if a booking ID is provided
if (isset($_GET['booking_id'])) {
    $booking_id = $_GET['booking_id'];

    // Fetch the booking details to calculate the refund
    $query = "SELECT price, start_date, end_date FROM bookings WHERE booking_id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($price, $start_date, $end_date);
    
    if ($stmt->num_rows > 0) {
        $stmt->fetch();
        
        // Calculate the refund amount (deduct 10% from the price)
        $refund_amount = $price * 0.90;
    } else {
        $_SESSION['error'] = 'Booking not found.';
        header("Location: my_bookings.php");
        exit();
    }

    $stmt->close();

    // Handle form submission for bank details and refund process
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get the bank details from the form
        $iban = $_POST['iban'];
        $bank_name = $_POST['bank_name'];
        $account_holder = $_POST['account_holder'];

        // Delete the booking from the database
        $delete_query = "DELETE FROM bookings WHERE booking_id = ?";
        $delete_stmt = $db->prepare($delete_query);
        $delete_stmt->bind_param('i', $booking_id);
        $delete_stmt->execute();

        if ($delete_stmt->affected_rows > 0) {
            // Success: Booking canceled, inform the user
            $_SESSION['message'] = "Booking canceled successfully. You will receive the refund of $" . number_format($refund_amount, 2) . " within 5 business days.";
            header("Location: my_bookings.php?status=cancellation_success");
            exit();
        } else {
            $_SESSION['error'] = "Failed to cancel booking. Please try again.";
        }

        $delete_stmt->close();
    }
} else {
    header("Location: my_bookings.php");
    exit();
}

$db->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Cancellation - STAYEASY</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .form-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
        .form-container h3 {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-container .alert {
            text-align: center;
            margin-bottom: 20px;
        }
        label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        input[type="text"],
        button {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            background-color: #007bff;
            color: white;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <!-- Feedback Message -->
        <?php if (isset($_SESSION['message'])): ?>
            <div id="feedback-message" class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div id="feedback-message" class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <!-- Cancellation Form -->
        <div class="form-container">
            <h3>Booking Cancellation</h3>
            <p>You are about to cancel the booking. A 10% cancellation fee will be deducted, and you will receive a refund within 5 business days.</p>
            <p>Refund Amount: $<?php echo number_format($refund_amount, 2); ?></p>

            <form method="post" action="">
                <label for="iban">IBAN:</label>
                <input type="text" id="iban" name="iban" required>

                <label for="bank_name">Bank Name:</label>
                <input type="text" id="bank_name" name="bank_name" required>

                <label for="account_holder">Account Holder's Name:</label>
                <input type="text" id="account_holder" name="account_holder" required>

                <button type="submit">Confirm Cancellation</button>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
