<?php
session_start();

// Redirect to login page if not logged in
if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit;
}


$uid = $_SESSION['uid'];

// Database connection
$host = '127.0.0.1'; // Replace with your host
$db = 'pms';         // Replace with your database name
$user = 'root';       // Replace with your database username
$pass = '';           // Replace with your database password
$conn = new mysqli($host, $user, $pass, $db);

// Fetch user hash key from the database
function getUserHashKey($uid, $pdo) {
    $stmt = $pdo->prepare("SELECT hash_key FROM users WHERE uid = :uid");
    $stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchColumn();
}

// File encryption function
function encryptFile($filePath, $outputPath, $key) {
    $iv = openssl_random_pseudo_bytes(16);  // Generate a 16-byte initialization vector
    $data = file_get_contents($filePath);

    if ($data === false) {
        return false;
    }

    // Encrypt using AES-256-CBC
    $encryptedData = openssl_encrypt($data, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
    if ($encryptedData === false) {
        return false;
    }

    // Save IV + encrypted data to the output file
    $outputData = $iv . $encryptedData;
    return file_put_contents($outputPath, $outputData) !== false;
}

// Database connection using PDO
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=pms', 'root', '');  // Replace with your credentials
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$uid = $_SESSION['uid'];  // Get user ID from session
$hashKey = getUserHashKey($uid, $pdo);

if (!$hashKey) {
    die("No hash key found for the user. Please register your face first.");
}

// Handle file upload and encryption
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fileToEncrypt'])) {
    $file = $_FILES['fileToEncrypt'];

    // Check for file upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        die("File upload failed. Error code: " . $file['error']);
    }

    // Sanitize file name and set paths
    $filePath = $file['tmp_name'];  // Temporary file path
    $fileName = basename($file['name']);  // Get sanitized file name
    $outputPath = 'encrypted_files/' . $fileName;

    // Ensure the 'encrypted_files' directory exists
    if (!is_dir('encrypted_files')) {
        if (!mkdir('encrypted_files', 0777, true) && !is_dir('encrypted_files')) {
            die("Failed to create directory for encrypted files.");
        }
    }

    // Derive a 256-bit key from the hash key using SHA-256
    $key = substr(hash('sha256', $hashKey, true), 0, 32);

    // Encrypt the file
    if (encryptFile($filePath, $outputPath, $key)) {
        echo "File successfully encrypted. Download it <a href='$outputPath' target='_blank'>here</a>.";
    } else {
        echo "File encryption failed. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encrypt File</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        label {
            font-size: 1.2em;
            margin-bottom: 10px;
        }
        input[type="file"] {
            margin-bottom: 20px;
        }
        button {
            padding: 10px 20px;
            font-size: 1em;
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
        button:hover {
            background-color: #218838;
        }
        a {
            display: block;
            text-align: center;
            margin-top: 20px;
            text-decoration: none;
            color: #007bff;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<h1>Encrypt a File</h1>
<form action="encrypt.php" method="post" enctype="multipart/form-data">
    <label for="fileToEncrypt">Choose a file to encrypt:</label>
    <input type="file" name="fileToEncrypt" id="fileToEncrypt" required>
    <button type="submit">Encrypt</button>
</form>

<a href="user_dashboard.php">Back to Dashboard</a>

</body>
</html>
