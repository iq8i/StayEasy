<?php
session_start();

// Ensure the user is logged in as a host
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'host') {
    header("Location: login.php");
    exit();
}

// Connect to the database
$db = new mysqli('localhost', 'root', 'mysql', 'stayeasy_db');
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Fetch the properties managed by the host
$host_id = $_SESSION['user_id'];
$query = "SELECT * FROM properties WHERE host_id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $host_id);
$stmt->execute();
$properties = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Management - STAYEASY</title>
    <link rel="stylesheet" href="assets/css/style.css"> <!-- Link to your existing CSS -->
    <!-- FullCalendar CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.2/main.min.css">
    <style>
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        .property-list {
            margin-bottom: 20px;
        }
        .calendar-container {
            margin-top: 20px;
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

<!-- Navigation bar -->
<header>
<nav class="navbar navbar-expand-lg navbar-dark">
        <a class="navbar-brand" href="index.php">
            <img src="assets/images/1.png" alt="STAYEASY Logo">
            STAYEASY
        </a>
        <div class="ml-auto profile-dropdown">
            <img src="<?php echo $user_profile_pic; ?>" alt="Profile Photo">
            <div class="profile-dropdown-content">
                <a href="guest_dashboard.php">Dashboard</a>
                <a href="profile.php">Manage Profile</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>
</header>

<div class="container">
    <h1>Manage Property Availability</h1>

    <!-- Property Selection Dropdown -->
    <form id="property-select-form">
        <label for="property">Select Property:</label>
        <select id="property" name="property_id">
            <option value="">Select a property...</option>
            <?php while ($property = $properties->fetch_assoc()): ?>
                <option value="<?php echo $property['property_id']; ?>"><?php echo $property['location']; ?></option>
            <?php endwhile; ?>
        </select>
    </form>

    <!-- Calendar to manage availability -->
    <div id="calendar" class="calendar-container"></div>
</div>

<!-- FullCalendar and jQuery Scripts -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.2/main.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    let calendarEl = document.getElementById('calendar');
    let calendar;

    // Handle property selection change
    $('#property').change(function() {
        const propertyId = $(this).val();
        if (propertyId) {
            loadCalendar(propertyId);
        }
    });

    // Load the calendar for the selected property
    function loadCalendar(propertyId) {
        if (calendar) {
            calendar.destroy();
        }

        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            selectable: true,
            events: `get_availability.php?property_id=${propertyId}`, // Fetch availability events
            select: function(info) {
                const start = info.startStr;
                const end = info.endStr;

                // Send the date and property ID via AJAX to toggle the status
                $.ajax({
                    url: 'update_availability.php',
                    type: 'POST',
                    data: {
                        property_id: propertyId,
                        start_date: start,
                        end_date: end
                    },
                    success: function(response) {
                        if (response === 'available') {
                            alert('Marked as available!');
                        } else if (response === 'unavailable') {
                            alert('Marked as unavailable!');
                        } else if (response === 'error') {
                            alert('Error updating availability.');
                        }

                        // Refetch events to update the calendar display
                        calendar.refetchEvents();
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX error: ", error); // Log any AJAX errors
                        alert('Error updating availability.');
                    }
                });
            },
            eventDidMount: function(info) {
                // Customize color based on event's status (unavailable or available)
                if (info.event.extendedProps.status === 'unavailable') {
                    info.el.style.backgroundColor = 'red';  // Set color to red for unavailable
                    info.el.style.borderColor = 'red';      // Set border color
                    info.el.style.color = 'white';          // Set text color for readability
                } else if (info.event.extendedProps.status === 'available') {
                    info.el.style.backgroundColor = 'green'; // Set color to green for available
                    info.el.style.borderColor = 'green';     // Set border color
                    info.el.style.color = 'white';           // Set text color for readability
                }
            },
            eventSourceSuccess: function(content, xhr) {
                console.log("Events refetched successfully.");
            }
        });

        calendar.render();
    }
});

</script>

</body>
</html>

<?php
$stmt->close();
$db->close();
?>
