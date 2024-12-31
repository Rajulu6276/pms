<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biometric-Based Encryption and Decryption</title>
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
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-10px);
        }
        .btn-custom {
            padding: 15px;
            font-size: 1.1rem;
            font-weight: bold;
            border-radius: 10px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .btn-custom:hover {
            color: white;
        }
        .container {
            max-width: 500px;
            margin-top: 80px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Biometric-Based Encryption and Decryption</h1>
    </div>
    <div class="container">
        <div class="card text-center p-4">
            <h2 class="mb-4 text-primary">Choose Your Role</h2>
            <div class="d-grid gap-3">
                <a href="user_login.php" class="btn btn-success btn-custom">User Login</a>
                <a href="admin/admin_login.php" class="btn btn-info btn-custom text-white">Admin Login</a>
                <a href="register.php" class="btn btn-warning btn-custom">User Registration</a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>
