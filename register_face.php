<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit;
}

// Get user details from session
$uid = $_SESSION['uid'];
$name = isset($_SESSION['name']) ? $_SESSION['name'] : 'Guest'; // Ensure $name is set

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ensure the registered_faces directory exists
    $targetDir = 'registered_faces';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    // Get the incoming POST data
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['image'])) {
        // Check if the user has already registered their face
        $stmt = $conn->prepare("SELECT image_path FROM users WHERE uid = ?");
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $stmt->bind_result($imagePath);
        $stmt->fetch();
        $stmt->close();

        if ($imagePath) {
            echo json_encode(["message" => "Face registration already completed.", "status" => "error"]);
            $conn->close();
            exit;
        }

        // Decode the base64 image
        $imageData = $data['image'];
        $imageData = str_replace('data:image/png;base64,', '', $imageData);
        $imageData = str_replace(' ', '+', $imageData);
        $decodedData = base64_decode($imageData);

        // Generate a unique filename
        $fileName = $targetDir . '/' . uniqid() . '.png';

        // Save the file
        if (file_put_contents($fileName, $decodedData)) {
            // Generate a unique hash key for the user
            $hashKey = hash('sha256', $uid . time() . rand());

            // Store image path and hash key in the database
            $stmt = $conn->prepare("UPDATE users SET image_path = ?, hash_key = ? WHERE uid = ?");
            $stmt->bind_param("ssi", $fileName, $hashKey, $uid);

            if ($stmt->execute()) {
                echo json_encode(["message" => "Face registration successful. Hash key generated and saved to database.", "status" => "success"]);
            } else {
                echo json_encode(["message" => "Image saved, but failed to update database.", "status" => "error"]);
            }

            $stmt->close();
        } else {
            echo json_encode(["message" => "Failed to save image.", "status" => "error"]);
        }
    } else {
        echo json_encode(["message" => "Invalid data received.", "status" => "error"]);
    }
    $conn->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Capture Face</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
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
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
            text-align: center;
        }
        h1, h2 {
            color: #333;
            font-weight: 600;
        }
        #video {
            border-radius: 10px;
            width: 100%;
            height: auto;
        }
        #canvas {
            display: none;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 15px 25px;
            margin: 20px 10px;
            font-size: 18px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #45a049;
        }
        button:focus {
            outline: none;
        }
        .loader {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Toast Notification */
        .toast {
            visibility: hidden;
            min-width: 250px;
            margin-left: -125px;
            background-color: #333;
            color: #fff;
            text-align: center;
            border-radius: 5px;
            padding: 16px;
            position: fixed;
            z-index: 1;
            left: 50%;
            bottom: 30px;
            font-size: 17px;
            transition: visibility 0s, opacity 0.5s ease-in-out;
        }

        .toast.show {
            visibility: visible;
            opacity: 1;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 30px;
            }
            button {
                padding: 12px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($name); ?></h1>
        <h2>Capture Your Face</h2>
        <video id="video" autoplay playsinline width="100%" height="auto"></video>
        <canvas id="canvas" width="640" height="480"></canvas>
        <button id="capture">Capture</button>
        <div id="loading" class="loader hidden"></div>
        <div id="toast" class="toast"></div>
    </div>

    <script>
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const captureButton = document.getElementById('capture');
        const loadingIndicator = document.getElementById('loading');
        const toast = document.getElementById('toast');
        const context = canvas.getContext('2d');

        let stream;

        // Access the webcam
        navigator.mediaDevices.getUserMedia({ video: true })
            .then((mediaStream) => {
                stream = mediaStream;
                video.srcObject = mediaStream;
            })
            .catch((err) => {
                console.error("Error accessing webcam: ", err);
            });

        // Capture the frame and upload automatically
        captureButton.addEventListener('click', () => {
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            canvas.style.display = 'block';
            video.style.display = 'none';
            captureButton.style.display = 'none';
            loadingIndicator.style.display = 'block'; // Show loading animation

            // Stop the webcam
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }

            // Convert the captured image to base64
            const dataUrl = canvas.toDataURL('image/png');

            // Automatically upload the image to the server
            fetch('', {
                method: 'POST',
                body: JSON.stringify({ image: dataUrl }),
                headers: { 'Content-Type': 'application/json' }
            })
            .then(response => response.json())
            .then(data => {
                console.log(data); // Handle success
                showToast(data.message, data.status); // Show success/failure message
                // Redirect to user dashboard immediately after uploading
                setTimeout(() => {
                    window.location.href = 'user_dashboard.php'; // Redirect to the dashboard page
                }, 2000); // Wait for 2 seconds to show the message
            })
            .catch(err => {
                console.error('Error uploading image:', err);
                showToast("An error occurred during the upload.", "error");
            });
        });

        // Show toast notification
        function showToast(message, status) {
            toast.textContent = message;
            toast.className = `toast show ${status}`;
            setTimeout(() => {
                toast.className = toast.className.replace("show", "");
            }, 3000); // Toast disappears after 3 seconds
        }
    </script>
</body>
</html>
