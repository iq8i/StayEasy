<?php
session_start();

// Check if the user is logged in and is a guest
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'guest') {
    header("Location: login.php");
    exit();
}

if (!isset($_POST['property_id'], $_POST['checkin_date'], $_POST['checkout_date'], $_POST['guests'])) {
    header("Location: view_properties.php");
    exit();
}

$property_id = $_POST['property_id'];
$checkin_date = $_POST['checkin_date'];
$checkout_date = $_POST['checkout_date'];
$guests = $_POST['guests'];

$cardNumberError = $expiryDateError = $cvvError = "";
$successMessage = "";

// Database connection
$db = new mysqli('localhost', 'root', 'mysql', 'stayeasy_db');
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Fetch the price per night from the PROPERTIES table
$price_query = $db->prepare("SELECT price FROM properties WHERE property_id = ?");
$price_query->bind_param("i", $property_id);
$price_query->execute();
$price_result = $price_query->get_result();

if ($price_result->num_rows > 0) {
    $property = $price_result->fetch_assoc();
    $price_per_night = $property['price']; // Fetch the price
} else {
    echo "<script>alert('Error: Property price not found.'); window.location.href = 'view_properties.php';</script>";
    exit();
}

$price_query->close();

// Calculate the total cost (price * number of days)
$checkin_date_obj = new DateTime($checkin_date);
$checkout_date_obj = new DateTime($checkout_date);
$interval = $checkin_date_obj->diff($checkout_date_obj);
$days = $interval->days;
$total_cost = $price_per_night * $days;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['card_number'], $_POST['expiry_date'], $_POST['cvv'])) {
    $cardNumber = str_replace(' ', '', $_POST['card_number']);
    $expiryDate = $_POST['expiry_date'];
    $cvv = $_POST['cvv'];
    $errors = false;

    // Validate card number
    if (!preg_match('/^\d{16}$/', $cardNumber)) {
        $cardNumberError = "Card number must be 16 digits.";
        $errors = true;
    }

    // Validate expiry date
    $currentDate = DateTime::createFromFormat('m/y', date('m/y'));
    $expiryDateObject = DateTime::createFromFormat('m/y', $expiryDate);
    if (!$expiryDateObject || $expiryDateObject < $currentDate) {
        $expiryDateError = "Expiry date must be in the future.";
        $errors = true;
    }

    // Validate CVV
    if (!preg_match('/^\d{3}$/', $cvv)) {
        $cvvError = "CVV must be 3 digits.";
        $errors = true;
    }

    if (!$errors) {
        // Fetch the host_id from the PROPERTIES table
        $host_query = $db->prepare("SELECT host_id FROM properties WHERE property_id = ?");
        $host_query->bind_param("i", $property_id);
        $host_query->execute();
        $host_result = $host_query->get_result();
    
        if ($host_result->num_rows > 0) {
            $host = $host_result->fetch_assoc();
            $host_id = $host['host_id']; // Fetch the host_id
        } else {
            echo "<script>alert('Error: Property host not found.'); window.location.href = 'view_properties.php';</script>";
            exit();
        }
    
        // Insert the booking data into the BOOKINGS table using start_date and end_date
        $insert_booking_query = $db->prepare("
            INSERT INTO bookings (guest_id, host_id, property_id, start_date, end_date, guests)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $insert_booking_query->bind_param(
            "iiissi", 
            $_SESSION['user_id'], // guest_id
            $host_id,             // host_id
            $property_id,         // property_id
            $checkin_date,        // start_date (check-in)
            $checkout_date,       // end_date (check-out)
            $guests               // number of guests
        );
    
        if ($insert_booking_query->execute()) {
            // Successful booking, redirect to guest dashboard with success message
            header("Location: guest_dashboard.php?status=success");
            exit();
        } else {
            echo "<script>alert('There was an error processing your booking. Please try again later.');</script>";
        }

        $insert_booking_query->close();

        $insert_booking = $db->prepare("INSERT INTO bookings (guest_id, host_id, property_id, start_date, end_date, guests) VALUES (?, ?, ?, ?, ?, ?)");
$insert_booking->bind_param("iiiisi", $guest_id, $host_id, $property_id, $start_date, $end_date, $guests);
$insert_booking->execute();

// Retrieve the last inserted `booking_id` from `bookings`
$booking_id = $db->insert_id;
$insert_booking->close();

// Check if `booking_id` was successfully retrieved

    // Step 2: Insert into `booking_history` with the `booking_id` reference
    $insert_history_query = $db->prepare("
    INSERT INTO booking_history (booking_id,guest_id, property_id, start_date, end_date, guests)
    VALUES (?, ?, ?, ?, ?, ?)
");
$insert_history_query->bind_param(
    "iiiisi", 
    $booking_id,          // booking_id
    $_SESSION['user_id'], // guest_id
    $property_id,         // property_id
    $checkin_date,        // start_date (check-in)
    $checkout_date,       // end_date (check-out)
    $guests               // number of guests
);
    $insert_history = $db->prepare("INSERT INTO booking_history (booking_id, guest_id, property_id, start_date, end_date, guests) VALUES (?, ?, ?, ?, ?, ?)");
    $insert_history->bind_param("iiissi", $booking_id, $guest_id, $property_id, $start_date, $end_date, $guests);
    $insert_history->execute();
    $insert_history->close();

    echo "Error: Unable to retrieve booking ID from bookings.";

    }
}

$db->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment</title>
    <style>
        .payment-container {
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
        input[type="text"],
        button {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
        }
        input[type="text"] {
            background-color: #f9f9f9;
        }
        button {
            background-color: #28a745;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        .error {
            color: red;
            font-size: 0.9em;
        }
        .bill {
            margin-top: 10px;
            font-size: 1.2em;
            font-weight: bold;
            text-align: center;
            color: #333;
        }
    </style>
</head>
<body>
<div class="payment-container">
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
        <input type="hidden" name="property_id" value="<?php echo htmlspecialchars($property_id); ?>">
        <input type="hidden" name="checkin_date" value="<?php echo htmlspecialchars($checkin_date); ?>">
        <input type="hidden" name="checkout_date" value="<?php echo htmlspecialchars($checkout_date); ?>">
        <input type="hidden" name="guests" value="<?php echo htmlspecialchars($guests); ?>">

        <div class="form-group">
            <label for="card_number">Card Number:</label>
            <input type="text" id="card_number" name="card_number" maxlength="19" value="<?php echo isset($_POST['card_number']) ? htmlspecialchars($_POST['card_number']) : ''; ?>" required>
            <?php if ($cardNumberError): ?>
                <span class="error"> <?php echo $cardNumberError; ?> </span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="expiry_date">Expiry Date (MM/YY):</label>
            <input type="text" id="expiry_date" name="expiry_date" maxlength="5" value="<?php echo isset($_POST['expiry_date']) ? htmlspecialchars($_POST['expiry_date']) : ''; ?>" required>
            <?php if ($expiryDateError): ?>
                <span class="error"> <?php echo $expiryDateError; ?> </span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="cvv">CVV:</label>
            <input type="text" id="cvv" name="cvv" maxlength="3" value="<?php echo isset($_POST['cvv']) ? htmlspecialchars($_POST['cvv']) : ''; ?>" required>
            <?php if ($cvvError): ?>
                <span class="error"> <?php echo $cvvError; ?> </span>
            <?php endif; ?>
        </div>

        <?php
// Initialize coupon variables
$couponMessage = "";
$discounted_total = $total_cost; // Default to the original total

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["coupon_code"])) {
    $coupon_code = trim($_POST["coupon_code"]);
    if ($coupon_code === "StayEasy") {
        $discounted_total = $total_cost * 0.90; // Apply a 10% discount
        $couponMessage = "Coupon Applied! 10% discount has been applied.";
    } else {
        $couponMessage = "Invalid Coupon Code.";
    }
}
?>

        <!-- Bill Section -->
        <div class="bill">
            Total Cost: $<?php echo number_format($discounted_total, 2); ?> for <?php echo $days; ?> days
        </div>
        <!-- Coupon Section -->
        <div class="form-group coupon-section">
        <label for="coupon_code">Apply Coupon Code:</label>
        <input type="text" id="coupon_code" name="coupon_code" maxlength="20" placeholder="Enter coupon code">
        <button>Apply Coupon</button>
        <?php if (!empty($couponMessage)): ?>
        <span class="coupon-message"> <?php echo htmlspecialchars($couponMessage); ?> </span>
        <?php endif; ?>
</div>
<script>
    document.getElementById('apply_coupon').addEventListener('click', function () {
        const couponCode = document.getElementById('coupon_code').value;
        const totalCostElement = document.getElementById('total_cost');
        const couponMessageElement = document.getElementById('coupon_message');

        fetch('apply_coupon.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ coupon_code: couponCode })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                totalCostElement.textContent = data.discounted_total.toFixed(2); // Update total cost
                couponMessageElement.textContent = data.message; // Show success message
                couponMessageElement.style.color = 'green';
            } else {
                couponMessageElement.textContent = data.message; // Show error message
                couponMessageElement.style.color = 'red';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            couponMessageElement.textContent = 'An error occurred. Please try again.';
            couponMessageElement.style.color = 'red';
        });
    });
</script>

        <button type="submit">Make Payment</button>
    </form>
</div>

<script>
    // Automatically format the card number as the user types (groups of 4 digits)
    const cardNumberInput = document.getElementById('card_number');
    cardNumberInput.addEventListener('input', function(e) {
        let cardNumber = e.target.value.replace(/\D/g, '');  // Remove non-digit characters
        if (cardNumber.length > 16) {
            cardNumber = cardNumber.slice(0, 16);  // Limit to 16 digits
        }
        // Format card number into groups of 4 separated by spaces
        e.target.value = cardNumber.replace(/(\d{4})(?=\d)/g, '$1 ');
    });

    // Automatically format the expiry date as MM/YY
    const expiryDateInput = document.getElementById('expiry_date');
    expiryDateInput.addEventListener('input', function(e) {
        let expiryDate = e.target.value.replace(/\D/g, '');  // Remove non-digit characters
        if (expiryDate.length > 4) {
            expiryDate = expiryDate.slice(0, 4);  // Limit to 4 digits (MMYY)
        }
        // Format expiry date into MM/YY
        if (expiryDate.length >= 2) {
            e.target.value = expiryDate.slice(0, 2) + '/' + expiryDate.slice(2);
        } else {
            e.target.value = expiryDate;
        }
    });
</script>

</body>
</html>
