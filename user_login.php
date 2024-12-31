<?php 
    session_start();
    include('includes/connection.php');
    if(isset($_POST['userLogin'])){
        $query = "SELECT email, password, name, uid FROM users WHERE email = '$_POST[email]' AND password = '$_POST[password]'";
        $query_run = mysqli_query($connection, $query);
        if(mysqli_num_rows($query_run)){
            while($row = mysqli_fetch_assoc($query_run)){
                $_SESSION['email'] = $row['email'];
                $_SESSION['name'] = $row['name'];
                $_SESSION['uid'] = $row['uid'];
            }
            echo "<script type='text/javascript'>
            window.location.href = 'user_dashboard.php';
            </script>";
        } else {
            echo "<script type='text/javascript'>
            alert('Incorrect email or password. Please try again.');
            window.location.href = 'user_login.php';
            </script>";
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .header {
            background: linear-gradient(90deg, #007bff, #0056b3);
            color: white;
            padding: 50px 20px;
            text-align: center;
            border-radius: 0 0 15px 15px;
        }
        .header h1 {
            font-size: 2.5rem;
            margin: 0;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            padding: 30px;
            background: white;
        }
        .btn-custom {
            padding: 15px;
            font-size: 1.1rem;
            font-weight: bold;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        .btn-custom:hover {
            background-color: #0056b3;
            color: white;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>User Login</h1>
    </div>
    <div class="container d-flex justify-content-center align-items-center" style="height: 80vh;">
        <div class="card w-100" style="max-width: 400px;">
            <h2 class="text-center text-primary mb-4">Login to Your Account</h2>
            <form action="" method="post">
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="Enter Email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Enter Password" required>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" name="userLogin" class="btn btn-primary btn-custom">Login</button>
                    <a href="index.php" class="btn btn-secondary btn-custom">Go to Home</a>
                </div>
            </form>
        </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>
