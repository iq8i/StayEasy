<?php
// Include the database connection file
include('database/connection.php');

// Initialize variables to hold form data and errors
$username = $email = $password = $confirm_password = $user_type = "";
$errors = [];
$profile_pic = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';
    $user_type = isset($_POST['user_type']) ? $_POST['user_type'] : '';

    // Form validation
    if (empty($username)) {
        $errors[] = "Username is required";
    }

    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    }

    if ($password != $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    if (empty($user_type)) {
        $errors[] = "Please select whether you're signing up as a guest or a host";
    }

    // Handle profile picture upload
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES['profile_pic']['name']);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if the file is a valid image type
        $valid_extensions = ['jpg', 'jpeg', 'png'];
        if (!in_array($imageFileType, $valid_extensions)) {
            $errors[] = "Only JPG and PNG files are allowed for profile picture";
        } else {
            // Move the uploaded file to the uploads directory
            if (!move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_file)) {
                $errors[] = "Error uploading profile picture";
            } else {
                $profile_pic = $target_file;
            }
        }
    }

    // If there are no errors, insert the user into the database
    if (empty($errors)) {
        // Hash the password for security
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Prepare an SQL statement to insert the user data
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, user_type, profile_pic) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $username, $email, $hashed_password, $user_type, $profile_pic);

        if ($stmt->execute()) {
            // Redirect to a success page or login page
            header("Location: login.php");
            exit();
        } else {
            $errors[] = "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <meta name="description" content="Login - Register Template">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        /* Add any specific styling needed for the body */
        @import url('https://fonts.googleapis.com/css?family=Raleway');
    
        body {
            margin: 0;
            padding: 0;
            font-family: 'Raleway', sans-serif;
            color: #F2F2F2;
           
        }

        #container-register {
            background-color: #1D1F20;
            position: relative;
            top: 50px;
            margin: auto;
            width: 480px;
            height: auto;
            padding: 20px;
            border-radius: 0.35em;
            box-shadow: 0 3px 10px 0 rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        #title {
            position: relative;
            background-color: #1A1C1D;
            width: 100%;
            padding: 20px 0px;
            border-radius: 0.35em;
            font-size: 22px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .lock {
            position: relative;
            top: 2px;
        }

        .input {
            margin: auto;
            width: 400px;
            border-radius: 4px;
            background-color: #373b3d;
            padding: 8px 0px;
            margin-top: 15px;
            position: relative;
        }

        .input-addon {
            position: absolute;
            left: 10px;
            top: 8px;
            color: #949494;
        }

        .input input, .input select {
            width: 100%;
            padding: 8px 12px 8px 36px;
            background-color: #373b3d;
            border: 1px solid #373b3d;
            border-radius: 3px;
            color: #949494;
        }

        input[type=email]:focus, input[type=text]:focus, input[type=password]:focus, select:focus {
            border: 1px solid #5E6365;
            outline: none;
        }

        input[type=submit] {
            padding: 10px 25px;
            background: #373E4A;
            color: #C1C3C6;
            font-weight: bold;
            border: none;
            cursor: pointer;
            border-radius: 3px;
            width: 100%;
            margin-top: 15px;
        }

        .register {
            margin-top: 40px;
            border-top: 1px solid #C1C3C6;
            padding-top: 15px;
        }

        #register-link {
            background: none;
            border: none;
            color: #C1C3C6;
            cursor: pointer;
            text-decoration: underline;
            font-size: 14px;
        }

        #register-link:hover {
            color: #FFF;
        }

        .privacy {
            margin-top: 5px;
            position: relative;
            font-size: 12px;
            color: #949494;
        }

        .privacy a {
            color: #949494;
            text-decoration: none;
        }

        .privacy a:hover {
            color: #C1C3C6;
            transition: color 1s;
        }

        .clearfix {
            clear: both;
        }

        .form-control-file {
            margin-top: 15px;
            color: #949494;
        }

        .alert-danger {
            background-color: #f2dede;
            color: #a94442;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            text-align: left;
        }

        .alert-danger ul {
            padding-left: 20px;
        }

        .alert-danger li {
            list-style-type: disc;
        }
    </style>
</head>
<body>
    <div id="container-register">
        <div id="title">
            <i class="material-icons lock">lock</i> Register
        </div>

        <form action="signup.php" method="POST" enctype="multipart/form-data">
            <!-- Display Errors -->
            <?php if (!empty($errors)) : ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error) : ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="input">
                <div class="input-addon">
                    <i class="material-icons">email</i>
                </div>
                <input id="email" name="email" placeholder="Email" type="email" required class="validate" autocomplete="off" value="<?php echo htmlspecialchars($email); ?>">
            </div>

            <div class="clearfix"></div>

            <div class="input">
                <div class="input-addon">
                    <i class="material-icons">face</i>
                </div>
                <input id="username" name="username" placeholder="Username" type="text" required class="validate" autocomplete="off" value="<?php echo htmlspecialchars($username); ?>">
            </div>

            <div class="clearfix"></div>

            <div class="input">
                <div class="input-addon">
                    <i class="material-icons">vpn_key</i>
                </div>
                <input id="password" name="password" placeholder="Password" type="password" required class="validate" autocomplete="off">
            </div>

            <div class="clearfix"></div>

            <div class="input">
                <div class="input-addon">
                    <i class="material-icons">vpn_key</i>
                </div>
                <input id="confirm_password" name="confirm_password" placeholder="Confirm Password" type="password" required class="validate" autocomplete="off">
            </div>

            <div class="clearfix"></div>

            <div class="input">
                <div class="input-addon">
                    <i class="material-icons">person</i>
                </div>
                <select id="user_type" name="user_type" required class="validate">
                    <option value="" disabled selected>Select your role</option>
                    <option value="guest" <?php echo ($user_type === 'guest') ? 'selected' : ''; ?>>Guest</option>
                    <option value="host" <?php echo ($user_type === 'host') ? 'selected' : ''; ?>>Host</option>
                </select>
            </div>

            <div class="clearfix"></div>

            <div class="input">
                <label for="profile_pic">Profile Picture (JPG or PNG only)</label>
                <input id="profile_pic" name="profile_pic" type="file" class="form-control-file">
            </div>

            <div class="clearfix"></div>

            <input type="submit" value="Register" />
        </form>

        <div class="register">
            Do you already have an account?
            <a href="login.php"><button id="register-link">Log In here</button></a>
        </div>
    </div>
</body>
</html>
