<?php
include 'database.php';

session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: admin_login");
    exit();
}

$user_role = $_SESSION['role'];

if ($user_role == "Admin") {
    header("Location: admin");
    exit();
} elseif ($user_role == "student") {
    header("Location: student_dashboard");
    exit();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if (isset($_GET['id_number'])) {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $id_number = $_GET['id_number'];
    $response = ['found' => false, 'name' => '', 'email' => ''];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id_number = :id_number");
    $stmt->execute(['id_number' => $id_number]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $response['found'] = true;
        $response['name'] = $result['name'];
        $response['email'] = $result['email'];


        date_default_timezone_set('Asia/Manila');
        $dateTime = new DateTime();
        $formattedDateTime = $dateTime->format('Y-m-d H:i:s');
        $formattedDate = $dateTime->format('Y-m-d'); 
        $formattedTime = $dateTime->format('g:i:s A');
        $formattedDay = $dateTime->format('l');

        $checkStmt = $pdo->prepare("SELECT * FROM attendance WHERE id_number = :id_number AND date = :date");
        $checkStmt->execute(['id_number' => $id_number, 'date' => $formattedDate]);
        
        if ($checkStmt->rowCount() == 1) {
            $insertStmt = $pdo->prepare("INSERT INTO attendance (id_number, name, date, time, day, status) VALUES (:id_number, :name, :date, :time, :day, :status)");
            $insertStmt->execute([
                'id_number' => $id_number,
                'name' => $response['name'],
                'date' => $formattedDate,
                'time' => $formattedTime,
                'day' => $formattedDay,
                'status' => 0
            ]);

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'muntinlupacosmopolitans@gmail.com';
            $mail->Password   = 'djkf ppkg anck qntj'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('muntinlupacosmopolitans@gmail.com', 'Muntinlupa Cosmopolitan School');
            $mail->addAddress($result['pemail']);

            $mail->SMTPOptions = array(
                'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
                )
            );

            $mail->isHTML(true);
            $mail->Subject = 'Attendance Notification';
            $mail->Body = 'Dear Parent,<br><br>We would like to inform you that your child, <strong>' . $response['name'] . '</strong>, has left the school on <strong>' . $dateTime->format('l, F j, Y \a\t g:i A') . '</strong>.<br><br>Thank you!';

            $mail->send();
        } catch (Exception $e) {
            error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }
        } else {
            $response['scanned'] = true;
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MCS - Attendance Out</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #FDEDEE;
            font-family: Arial, sans-serif;
        }
        .container {
            text-align: center;
            margin-top: 100px;
        }
        #preview {
            position: relative;
            width: 100%;
            max-width: 550px;
            height: auto;
            border: 1px solid #A82D2D;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
            background: black;
            overflow: hidden;
        }
        #scanner-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 2;
        }
        .scan-line {
            position: absolute;
            top: 0;
            left: 50%;
            width: 45%;
            height: 2px;
            background: #A82D2D;
            animation: scan 2s linear infinite;
            transform: translateX(-50%);
        }

        @keyframes scan {
            from {
                top: 0;
            }
            to {
                top: 90%;
            }
        }

        #result {
        margin-top: 15px;
        min-height: 60px;
        opacity: 0;
        transition: opacity 1s ease-in-out;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12 position-relative">
                <video id="preview" class="img-responsive" autoplay playsinline muted></video>
                <div id="scanner-overlay">
                    <div class="scan-line"></div>
                </div>
            </div>
            <div class="d-flex justify-content-center col-md-12 mt-3">
                <input type="text" id="manualInput" placeholder="Enter ID Number" class="form-control w-50">
            </div>
            <p style="margin-top: 50px;">Back to <a href="admin" style="text-decoration: none; color: #A82D2D;">Dashboard</a></p>
            <div id="result" class="col-md-12 w-50"></div>
        </div>
    </div>

    <audio id="sound" src="assets/success.mp3"></audio>
    <audio id="error" src="assets/error.mp3"></audio>

    <script>
    const video = document.getElementById('preview');
    const resultDiv = document.getElementById('result');
    const sound = document.getElementById('sound');
    const qrCache = {}; 
    let isProcessing = false;

    navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
        .then((stream) => {
            video.srcObject = stream;
            video.play();
            requestAnimationFrame(scanQRCode); 
        })
        .catch((err) => {
            console.error("Camera access error: ", err);
            alert("Unable to access camera.");
        });

    function scanQRCode() {
        if (video.readyState === video.HAVE_ENOUGH_DATA) {
            const canvas = document.createElement("canvas");
            const context = canvas.getContext("2d");
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
            const code = jsQR(imageData.data, imageData.width, imageData.height);

            if (code && code.data.length === 8 && !isProcessing) {
                handleQRCode(code.data);
            }
        }
        requestAnimationFrame(scanQRCode); 
    }

    function handleQRCode(qrCode) {
    if (isProcessing) return;

    isProcessing = true;

    if (qrCache[qrCode]) {
        displayResult(qrCache[qrCode], true);
        fetch('?id_number=' + encodeURIComponent(qrCode))
            .then(response => response.json())
            .then(data => {
                qrCache[qrCode] = data;
            })
            .finally(() => {
                setTimeout(() => {
                    isProcessing = false;
                }, 1000);
            });
    } else {
        fetch('?id_number=' + encodeURIComponent(qrCode))
            .then(response => response.json())
            .then(data => {
                qrCache[qrCode] = data; 
                displayResult(data, false); 
            })
            .finally(() => {
                setTimeout(() => {
                    isProcessing = false; 
                }, 1000); 
            });
        }
    }

    function displayResult(data, fromCache) {
    resultDiv.innerHTML = '';  

    if (data.scanned) {
        resultDiv.innerHTML = `<div class="alert" style="position: fixed; bottom: 20px; right: 10px; z-index: 1050; background-color: #A82D2D; color: white;">Attendance already recorded for today.</div>`;
        error.play();  
    } else if (data.found) {
        resultDiv.innerHTML = `<div class="alert alert-success" style="position: fixed; bottom: 20px; right: 10px; z-index: 1050;">${data.name}</div>`;
        sound.play(); 
        data.scanned = true; 
    } else {
        resultDiv.innerHTML = `<div class="alert alert-danger">Not found</div>`;
        error.play(); 
    }

    resultDiv.style.opacity = 1;

    // Fade out the result after 2 seconds
    setTimeout(() => {
        resultDiv.style.opacity = 0;
    }, 2000);
}





    // Manual input handling with caching
    document.getElementById('manualInput').addEventListener('input', function() {
        const manualValue = this.value;
        if (manualValue.length === 8 && !isProcessing) {
            handleQRCode(manualValue);
        }
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>

</body>
</html>
