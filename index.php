<?php
// Include the database connection file
include('database/connection.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STAYEASY - Home</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Custom styles and animations for index page */
        .hero {
            background-image: url('assets/images/hero-background.jpg');
            background-size: cover;
            background-position: center;
            height: 100vh;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            animation: fadeIn 2s;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .hero h1 {
            font-size: 4rem;
            font-weight: bold;
        }

        .btn-primary {
            transition: transform 0.3s ease-in-out;
        }

        .btn-primary:hover {
            transform: scale(1.1);
        }

        .apartments .card {
            transition: transform 0.3s ease-in-out;
        }

        .apartments .card:hover {
            transform: translateY(-10px);
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light" style="background-color: #ffffff;">
        <a class="navbar-brand" href="#">
            <img src="assets/images/1.png" alt="STAYEASY Logo" width="80" height="80">
            <span>STAYEASY</span>
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="btn btn-light-black mx-2" href="login.php">Login</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero text-center">
        <div>
            <h1 class="display-4">Welcome to STAYEASY</h1>
            <p class="lead">Find your next stay or host a property with ease.</p>
            <a href="signup.php" class="btn btn-primary btn-lg">Join Us</a>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-us text-center py-5">
        <div class="container">
            <h2>About STAYEASY</h2>
            <p>STAYEASY connects hosts and guests, providing unique stays in some of the world's most beautiful destinations. Whether you're looking for a cozy apartment, a luxurious villa, or anything in between, we've got you covered. Join STAYEASY today and experience a hassle-free booking experience for your next stay!</p>
        </div>
    </section>

    <!-- Apartments Section -->
    <section class="apartments py-5">
        <div class="container">
            <h2 class="text-center mb-4">Explore Our Apartments</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="card mb-4">
                        <img src="assets/images/service_1.jpg" class="card-img-top" alt="Apartment 1">
                        <div class="card-body">
                            <h5 class="card-title">Luxurious Apartment with a View</h5>
                            <p class="card-text">Experience breathtaking views in this modern, luxurious apartment with top-notch amenities.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card mb-4">
                        <img src="assets/images/service_2.jpg" class="card-img-top" alt="Apartment 2">
                        <div class="card-body">
                            <h5 class="card-title">Cozy Apartment in the Heart of the City</h5>
                            <p class="card-text">Located in the heart of the city, this cozy apartment is perfect for those wanting easy access to urban life.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card mb-4">
                        <img src="assets/images/service_3.jpg" class="card-img-top" alt="Apartment 3">
                        <div class="card-body">
                            <h5 class="card-title">Modern Living with Great Amenities</h5>
                            <p class="card-text">Enjoy modern living in this stylish apartment equipped with all the amenities you need for a comfortable stay.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="text-center">
        <p>&copy; 2024 STAYEASY. All rights reserved.</p>
        <p><a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a></p>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
