<?php
session_start();

// Check if there's a success or error message to show
if (!isset($_SESSION['success_message']) && !isset($_SESSION['error_message'])) {
    header("Location: guest_dashboard.php");
    exit();
}

// Get the messages and then unset them
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';

unset($_SESSION['success_message']);
unset($_SESSION['error_message']);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Status - STAYEASY</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            text-align: center;
        }
        h1 {
            font-size: 1.5em;
            color: #28a745;
            margin-bottom: 20px;
        }
        p {
            font-size: 1.2em;
            color: #333;
        }
        .error {
            color: #dc3545;
            font-size: 1.2em;
            margin-top: 20px;
        }
        .btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="container">
    <?php if ($success_message): ?>
        <h1>Booking Confirmed!</h1>
        <p><?php echo htmlspecialchars($success_message); ?></p>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="error">
            <h1>Booking Failed</h1>
            <p><?php echo htmlspecialchars($error_message); ?></p>
        </div>
    <?php endif; ?>

    <a href="guest_dashboard.php" class="btn">Go to Dashboard</a>
</div>

</body>
</html>
