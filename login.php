<?php
// Include the database connection file
include('database/connection.php');

// Start the session
session_start();

// Initialize variables
$email = $password = "";
$errors = [];


// Example PHP logic to check and change the value of $username
if (isset($username) && $username === 'root') {
    $username = ''; // or you can set it to any default value like $username = 'Guest';
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    // Validate email and password
    if (empty($email)) {
        $errors[] = "Email is required";
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    }

    // If no errors, proceed with login
    if (empty($errors)) {
        // Prepare SQL query to fetch user by email
        $stmt = $conn->prepare("SELECT id, username, email, password, user_type FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        // Check if user exists
        if ($stmt->num_rows == 1) {
            // Bind the result
            $stmt->bind_result($id, $username, $email, $hashed_password, $user_type);
            $stmt->fetch();

            // Verify the password
            if (password_verify($password, $hashed_password)) {
                // Store user information in session
                $_SESSION['user_id'] = $id;
                $_SESSION['username'] = $username;
                $_SESSION['user_type'] = $user_type;

                // Redirect user to different pages based on their role
                if ($user_type === 'guest') {
                    header("Location: guest_dashboard.php");
                } else if ($user_type === 'host') {
                    header("Location: host_dashboard.php");
                }
                exit();
            } else {
                $errors[] = "Incorrect password";
            }
        } else {
            $errors[] = "No account found with that email";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <meta name="description" content="Login - Register Template">
    <meta name="author" content="Lorenzo Angelino aka MrLolok">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
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
        top: 170px;
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
        width: 350px;
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
            <i class="material-icons lock">lock</i> Login
        </div>

        <form action="login.php" method="POST">
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

           

            <div class="clearfix"></div>

            <div class="input">
                <div class="input-addon">
                    <i class="material-icons">face</i>
                </div>
                <input id="username" name="username" placeholder="Username" type="text" required class="validate" autocomplete="off" value="<?php echo htmlspecialchars($username); ?>">
            </div>

            <div class="input">
                <div class="input-addon">
                    <i class="material-icons">email</i>
                </div>
                <input id="email" name="email" placeholder="Email" type="email" required class="validate" autocomplete="off" value="<?php echo htmlspecialchars($email); ?>">
            </div>

            <div class="clearfix"></div>

            <div class="input">
                <div class="input-addon">
                    <i class="material-icons">vpn_key</i>
                </div>
                <input id="password" name="password" placeholder="Password" type="password" required class="validate" autocomplete="off">
            </div>

            <div class="clearfix"></div>

            <input type="submit" value="Log in" />
        </form>

        <div class="register">
            Don't have an account?
            <a href="signup.php"><button id="register-link">Sign Up here</button></a>
        </div>
    </div>
</body>

</html>
