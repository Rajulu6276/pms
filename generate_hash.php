<?php
if (isset($_POST['generate_hash'])) {
    $face_data_path = 'uploads/face_data.txt';
    $face_data = file_get_contents($face_data_path);

    $command = escapeshellcmd("python3 python/biometric_tool.py generate_hash $face_data");
    $output = shell_exec($command);

    if ($output) {
        echo "Generated Hash: $output";
    } else {
        echo "Failed to generate hash.";
    }
}
?>
