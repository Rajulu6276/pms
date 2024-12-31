<?php
include('includes/connection.php');

if (isset($_POST['userRegistration'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $mobile = $_POST['mobile'];

    // Check if the user already exists
    $checkQuery = "SELECT * FROM users WHERE email = ? OR mobile = ?";
    $checkStmt = mysqli_prepare($connection, $checkQuery);
    mysqli_stmt_bind_param($checkStmt, "si", $email, $mobile);
    mysqli_stmt_execute($checkStmt);
    mysqli_stmt_store_result($checkStmt);

    if (mysqli_stmt_num_rows($checkStmt) > 0) {
        // Notify that the user already exists
        echo "<script type='text/javascript'>
        alert('User with this email or mobile number already exists!');
        window.location.href = 'register.php';
        </script>";
    } else {
        // Use prepared statements to insert a new user
        $query = "INSERT INTO users (name, email, password, mobile) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($connection, $query);
        mysqli_stmt_bind_param($stmt, "sssi", $name, $email, $password, $mobile);

        if (mysqli_stmt_execute($stmt)) {
            echo "<script type='text/javascript'>
            alert('User registered successfully.');
            window.location.href = 'index.php';
            </script>";
        } else {
            echo "<script type='text/javascript'>
            alert('Error...Please try again.');
            window.location.href = 'register.php';
            </script>";
        }

        mysqli_stmt_close($stmt);
    }

    mysqli_stmt_close($checkStmt);
    mysqli_close($connection);
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>User Registration</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  </head>
  <body class="bg-light">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-6 mt-5">
          <div class="card">
            <div class="card-header bg-primary text-white text-center">
              <h3>User Registration</h3>
            </div>
            <div class="card-body">
              <form action="" method="post">
                <div class="mb-3">
                  <label for="name" class="form-label">Name</label>
                  <input type="text" name="name" id="name" class="form-control" placeholder="Enter Name" required>
                </div>
                <div class="mb-3">
                  <label for="email" class="form-label">Email</label>
                  <input type="email" name="email" id="email" class="form-control" placeholder="Enter Email" required>
                </div>
                <div class="mb-3">
                  <label for="password" class="form-label">Password</label>
                  <input type="password" name="password" id="password" class="form-control" placeholder="Enter Password" required>
                </div>
                <div class="mb-3">
                  <label for="mobile" class="form-label">Mobile Number</label>
                  <input type="text" name="mobile" id="mobile" class="form-control" placeholder="Enter Mobile Number" required>
                </div>
                <button type="submit" name="userRegistration" class="btn btn-primary w-100">Register</button>
              </form>
            </div>
            <div class="card-footer text-center">
              <a href="index.php" class="btn btn-secondary">Go to Home</a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Bootstrap JS and Popper -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
  </body>
</html>
