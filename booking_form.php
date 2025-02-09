<?php
session_start();

// Check if the user is logged in and is a guest
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'guest') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['property_id'])) {
    header("Location: view_properties.php");
    exit();
}

$property_id = $_GET['property_id'];

// Database connection
$db = new mysqli('localhost', 'root', 'mysql', 'stayeasy_db');
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

$property_query = $db->prepare("SELECT * FROM properties WHERE property_id = ?");
$property_query->bind_param("i", $property_id);
$property_query->execute();
$property_result = $property_query->get_result();

if ($property_result->num_rows === 0) {
    header("Location: view_properties.php?error=PropertyNotFound");
    exit();
}

$property = $property_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Form</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <style>
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
<div class="booking-container">
    <h1>Book Your Stay</h1>

    <!-- Display Property Information -->
    <div class="property-info">
        
    <?php ?>

        <h2><?php echo htmlspecialchars($property['location']); ?></h2>
        <p><strong>Price:</strong> $<?php echo htmlspecialchars($property['price']); ?> per night</p>
        <p><strong>Description:</strong> <?php echo htmlspecialchars($property['description']); ?></p>
    </div>

    <div class="form-group">
        <label for="checkin_date">Check-in Date:</label>
        <input type="date" id="checkin_date" name="checkin_date" required>
    </div>

    <div class="form-group">
        <label for="checkout_date">Check-out Date:</label>
        <input type="date" id="checkout_date" name="checkout_date" required>
    </div>
    
    <div class="form-group">
    <label for="guests">Number of Guests:</label>
<input type="number" id="guests" name="guests" required>
<span style="display: none; color:red;" id="error">The apartment can't take more than 11 guests</span>

<script>
    let num = document.getElementById("guests");
    let error = document.getElementById("error");

    num.addEventListener("input", function() { 
        if (num.value <= 0 || num.value > 11) { 
            num.value = "";
            error.style.display = "block";  // Show error message
        } else {
            error.style.display = "none";   // Hide error message for valid input
        }
    });
</script>
    
    <button id="confirmBooking">Confirm Booking</button>

</div>

<script>
    $(document).ready(function() {
        $('#confirmBooking').click(function(event) {
            event.preventDefault();
            
            var propertyId = <?php echo $property_id; ?>;
            var checkinDate = $('#checkin_date').val();
            var checkoutDate = $('#checkout_date').val();
            var guests = $('#guests').val();

            if (!checkinDate || !checkoutDate || !guests) {
                alert("Please fill in all required fields.");
                return;
            }

            // AJAX request to check availability
            $.ajax({
                url: 'get_availability.php',
                type: 'GET',
                data: {
                    property_id: propertyId,
                    checkin_date: checkinDate,
                    checkout_date: checkoutDate
                },
                success: function(response) {
                    var availability = JSON.parse(response);
                    
                    if (!availability.is_available) {
                        alert('The apartment is not available for the selected date. Please choose a different date.');
                    } else {
                        alert('The apartment is available! Proceeding with booking...');
                        
                        // Redirect to payment page
                        var form = $('<form action="payment.php" method="POST">' +
                            '<input type="hidden" name="property_id" value="' + propertyId + '" />' +
                            '<input type="hidden" name="checkin_date" value="' + checkinDate + '" />' +
                            '<input type="hidden" name="checkout_date" value="' + checkoutDate + '" />' +
                            '<input type="hidden" name="guests" value="' + guests + '" />' +
                        '</form>');
                        $('body').append(form);
                        form.submit();
                    }
                },
                error: function() {
                    alert('Error checking availability. Please try again.');
                }
            });
        });
    });
</script>

</body>
</html>

<?php
$property_query->close();
$db->close();
?>
