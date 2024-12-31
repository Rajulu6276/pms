<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Page</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: #f4f6f8;
            text-align: center;
        }

        h1 {
            margin-top: 60px;
            color: #2c3e50;
            font-size: 2.5rem;
        }

        p {
            font-size: 1.1rem;
            color: #7f8c8d;
        }

        .options {
            margin-top: 50px;
        }

        button {
            display: inline-block;
            margin: 20px;
            padding: 15px 30px;
            font-size: 18px;
            color: white;
            background-color: #3498db;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: transform 0.2s ease-in-out, background-color 0.3s ease;
        }

        button:hover {
            background-color: #2980b9;
            transform: scale(1.05);
        }

        button:focus {
            outline: none;
        }

        .redirect-link {
            text-decoration: none;
            color: white;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            flex-direction: column;
        }

        .options button {
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }

        .options button:active {
            transform: scale(0.98);
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Biometric Based Encryption and Decryption</h1>
        <p>Please choose an option below:</p>

        <div class="options">
            <!-- Button to Register Face -->
            <button onclick="location.href='register_face.php'">
                Register Face
            </button>

            <!-- Button to Encrypt File -->
            <button onclick="location.href='encrypt.php'">
                Encrypt File
            </button>

            <!-- Button to Decrypt File -->
            <button onclick="location.href='decrypt.php'">
                Decrypt File
            </button>
        </div>
    </div>

</body>
</html>
    