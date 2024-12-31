<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit;
}

// Get user details from session
$uid = $_SESSION['uid'];

// Database connection
$host = '127.0.0.1'; // Replace with your host
$db = 'pms';         // Replace with your database name
$user = 'root';       // Replace with your database username
$pass = '';           // Replace with your database password

$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the hash key for the logged-in user
$stmt = $conn->prepare("SELECT hash_key FROM users WHERE uid = ?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$stmt->bind_result($hashKey);
$stmt->fetch();
$stmt->close();

if (!$hashKey) {
    die("No hash key found for the user. Please register your face first.");
}

// Close the database connection
$conn->close();

// File decryption function
function decryptFile($filePath, $outputPath, $key)
{
    // Read the encrypted file
    $data = file_get_contents($filePath);
    if ($data === false) {
        return false;
    }

    // Extract the IV from the encrypted data (first 16 bytes)
    $iv = substr($data, 0, 16);
    $encryptedData = substr($data, 16);

    // Decrypt the data using AES-256-CBC
    $decryptedData = openssl_decrypt($encryptedData, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
    if ($decryptedData === false) {
        return false;
    }

    // Save the decrypted data to the output file
    return file_put_contents($outputPath, $decryptedData) !== false;
}

// Initialize success status
$decryptionSuccess = false;
$outputPath = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fileToDecrypt'])) {
    $file = $_FILES['fileToDecrypt'];

    // Ensure the file was uploaded successfully
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(["message" => "File upload failed.", "status" => "error"]);
        exit;
    }

    $filePath = $file['tmp_name'];  // Temporary file path
    $fileName = $file['name'];      // Original file name
    $outputPath = 'decrypted_files/' . $fileName; // Destination path for decrypted file

    // Ensure the decrypted_files directory exists
    if (!is_dir('decrypted_files')) {
        mkdir('decrypted_files', 0777, true);
    }

    // Decrypt the file
    $key = substr(hash('sha256', $hashKey, true), 0, 32); // Derive a 256-bit key
    if (decryptFile($filePath, $outputPath, $key)) {
        $decryptionSuccess = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Decrypt File</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7fc;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: #fff;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
            text-align: center;
        }
        h1, h2 {
            color: #333;
            font-weight: 600;
        }
        label {
            font-size: 18px;
            color: #555;
            margin-bottom: 10px;
            display: block;
        }
        input[type="file"] {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #ccc;
            width: 100%;
            font-size: 16px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 18px;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #45a049;
        }
        button:focus {
            outline: none;
        }
        .download-button {
            display: none;
            margin-top: 20px;
            background-color: #007bff;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .download-button:hover {
            background-color: #0056b3;
        }
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }
            button {
                padding: 12px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Decrypt a File</h1>
        <form action="decrypt.php" method="post" enctype="multipart/form-data" id="decryptForm">
            <label for="fileToDecrypt">Choose a file to decrypt:</label>
            <input type="file" name="fileToDecrypt" id="fileToDecrypt" required>
            <button type="submit">Decrypt</button>
        </form>
        <br>
        <a href="user_dashboard.php">Back to Dashboard</a>

        <?php if ($decryptionSuccess): ?>
            <h3>Decryption Successful! ✔️</h3>
            <button class="download-button" id="downloadBtn">
                <a href="<?php echo $outputPath; ?>" download style="color: white; text-decoration: none;">Download Decrypted File</a>
            </button>
        <?php endif; ?>
    </div>

    <script>
        // Show download button if decryption was successful
        <?php if ($decryptionSuccess): ?>
            document.getElementById('downloadBtn').style.display = 'inline-block';
        <?php endif; ?>
    </script>
</body>
</html>
