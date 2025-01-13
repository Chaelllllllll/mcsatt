<?php
require 'database.php'; // Make sure this file defines $host, $db, $user, $pass as strings

session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login");
    exit();
}

if (!isset($_SESSION['role'])) {
    header("Location: login");
    exit();
}

if ($_SESSION['role'] !== 'student') {
    header("Location: admin");
    exit();
}

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo "User ID is missing.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['student_image'])) {
    $file = $_FILES['student_image'];
    $upload_dir = 'uploads/';
    $file_path = $upload_dir . basename($file['name']);
    
    if ($file['error'] === UPLOAD_ERR_OK && in_array(mime_content_type($file['tmp_name']), ['image/jpeg', 'image/png'])) {
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            try {
                // Ensure that the database connection variables are strings
                $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $updateStmt = $pdo->prepare("UPDATE users SET image_path = :image_path WHERE id = :id");
                $updateStmt->execute([
                    ':image_path' => $file_path,
                    ':id' => $user_id
                ]);

                header("Location: student_dashboard");
                exit();
            } catch (PDOException $e) {
                echo "Database error: " . $e->getMessage();
                exit();
            }
        } else {
            echo "Failed to move uploaded file.";
        }
    } else {
        echo "File upload error or invalid file type.";
    }
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $attstmt = $pdo->prepare("SELECT * FROM attendance WHERE id_number = :id");
    $attstmt->execute([':id' => $user['id_number']]);

    $attrecords = $attstmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit();
}


try {
    $teacherStmt = $pdo->prepare("SELECT name, email FROM staff WHERE role = 'Teacher'");
    $teacherStmt->execute();
    $teachers = $teacherStmt->fetchAll(PDO::FETCH_ASSOC); 

    $teachersList = [];
    foreach ($teachers as $row) { 
        $teachersList[] = $row['name'];
    }

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit();
}
?>






<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MCS | Student Dashboard</title>
    <link rel="shortcut icon" href="https://i.ibb.co/SB5ZvFh/images.jpg" type="image/jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            background-color: #FDEDEE;
            font-family: Arial, sans-serif;
        }

        .card {
            background-color: #FFD6D6;
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .card-title {
            background-color: transparent;
            border-bottom: none;
            font-size: 24px;
            font-weight: bold;
            color: #A82D2D;
        }
        
        .profile-img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid #E89A9A;
        }

        .profile-img:hover {
            opacity: 0.8;
        }

        .upload-btn {
            display: none;
        }

        .qr-code {
            width: 150px;
            height: 150px;
            cursor: pointer;
            transition: opacity 0.3s;
        }

        .qr-code:hover {
            opacity: 0.8;
        }

        .info li{
            border-radius: 20px; 
            margin-top: 1%;
            color: #A82D2D;
        }
    </style>
</head>
<body>
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <h5 class="card-title mb-3">Welcome Cosmopolites</h5>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data" autocomplete="on">
                        <label for="upload-image">
                            <img 
                                src="<?php echo !empty($user['image_path']) ? htmlspecialchars($user['image_path']) : 'https://i.ibb.co/s68CT2w/Nice-Png-watsapp-icon-png-9332131.png'; ?>" 
                                alt="Profile Image" 
                                class="profile-img img-thumbnail mb-3">
                        </label>
                        <input type="file" name="student_image" id="upload-image" class="upload-btn" onchange="this.form.submit()">
                    </form>
                    <?php if ($user['is_verified'] == 1) {
                        echo '<span class="badge text-bg-success mb-3">Verified</span>';
                    } else {
                        echo '<span class="badge text-bg-danger mb-3">Not Verified</span>';
                    }
                    ?>
                    <ul class="list-group mb-4 info" >
                        <p class="mt-2 mb-2 text-start">Student Information</p>
                        <li class="list-group-item text-start">
                            <strong>ID Number:</strong> <?php echo htmlspecialchars($user['id_number']); ?>
                        </li>
                        <li class="list-group-item text-start">
                            <strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?>
                        </li>
                        <li class="list-group-item text-start">
                            <strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?>
                        </li>
                        <li class="list-group-item text-start">
                            <strong>Age:</strong> <?php echo htmlspecialchars($user['age']); ?>
                        </li>
                        <li class="list-group-item text-start">
                            <strong>Grade Level:</strong> <?php echo htmlspecialchars($user['grade']); ?>
                        </li>
                        <li class="list-group-item text-start">
                            <strong>Teacher:</strong> <?php echo htmlspecialchars($user['teacher']); ?>
                        </li>
                        <li class="list-group-item text-start">
                            <strong>Facebook Link:</strong> <a href="<?php echo htmlspecialchars($user['fblink']); ?>" style="color: #A82D2D;"><?php echo htmlspecialchars($user['fblink']); ?></a>
                        </li>
                        <p class="mt-2 mb-2 text-start">Parent/Guardian Information</p>
                        <li class="list-group-item text-start">
                            <strong>Name:</strong> <?php echo htmlspecialchars($user['pname']); ?>
                        </li>
                        <li class="list-group-item text-start">
                            <strong>Relationship:</strong> <?php echo htmlspecialchars($user['prelationship']); ?>
                        </li>
                        <li class="list-group-item text-start">
                            <strong>Mobile Number:</strong> <?php echo htmlspecialchars($user['pnumber']); ?>
                        </li>
                        <li class="list-group-item text-start">
                            <strong>Email:</strong> <?php echo htmlspecialchars($user['pemail']); ?>
                        </li>
                        <li class="list-group-item text-start">
                            <?php if (!empty($user['qr_code_path'])): ?>
                                <div class="d-flex align-items-center justify-content-center">
                                    <a href="<?php echo htmlspecialchars($user['qr_code_path']); ?>" download="<?php echo $user['email'];?> attendance qr.png">
                                        <img src="<?php echo htmlspecialchars($user['qr_code_path']); ?>" alt="QR Code" class="qr-code img-thumbnail">
                                        <br>
                                        <span class="badge text-bg-light">Attendance QR</span>
                                    </a>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">QR Code is not available.</p>
                            <?php endif; ?>
                        </li>
                        <li class="list-group-item text-start">
                            <?php if (!empty($user['qr_code_path_studentsinfo'])): ?>
                                <div class="d-flex align-items-center justify-content-center">
                                    <a href="<?php echo htmlspecialchars($user['qr_code_path_studentsinfo']); ?>" download="<?php echo $user['email'];?> information qr.png">
                                        <img src="<?php echo htmlspecialchars($user['qr_code_path_studentsinfo']); ?>" alt="QR Code" class="qr-code img-thumbnail">
                                        <br>
                                        <span class="badge text-bg-light">Information QR</span>
                                    </a>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">QR Code is not available.</p>
                            <?php endif; ?>
                        </li>
                    </ul>
                    <button type="button" class="btn view-attendance mt-3 w-100" style="background-color: #A82D2D; color: white;" data-bs-toggle="modal" data-bs-target="#recordModal" data-id="<?php  echo $user['id_number']; ?>">
                        View Record
                    </button>
                    <button type="button" class="btn mt-3 w-100" style="background-color: #A82D2D; color: white;" data-bs-toggle="modal" data-bs-target="#updateModal" data-id="<?php echo htmlspecialchars($user['id_number'])?>">
                        Update Information
                    </button>
                    <hr>
                    <a href="logout"><span class="badge text-bg-danger mb-3 mt-4">Logout</span></a>

                    

                    <div class="modal fade" id="recordModal" tabindex="-1" aria-labelledby="recordModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header" style="background-color: #FFD6D6;">
                                    <h5 class="modal-title" id="recordModalLabel">Attendance Record</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="attendanceTable">
                                            <thead>
                                                <tr>
                                                    <th>Type</th>
                                                    <th>Status</th>
                                                    <th>Day</th>
                                                    <th>Date</th>
                                                    <th>Time</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($attrecords as $attrecord): ?>
                                                    <tr>
                                                        <td>
                                                            <?php 
                                                                if ($attrecord['status'] == 1) {
                                                                    echo "<span class='badge bg-success'>In</span>";
                                                                } else {
                                                                    echo "<span class='badge bg-danger'>Out</span>";
                                                                }
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <?php 
                                                                if ($attrecord['status'] == 1) {
                                                                    if ($attrecord['late'] == 1) {
                                                                        echo "<span class='badge bg-danger'>Late</span>";
                                                                    } else {
                                                                        echo "<span class='badge bg-success'>On Time</span>";
                                                                    }
                                                                }
                                                            ?>
                                                        </td>
                                                        <td><?php echo $attrecord['day']; ?></td>
                                                        <td><?php echo $attrecord['date']; ?></td>
                                                        <td><?php echo $attrecord['time']; ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header" style="background-color: #FFD6D6;">
                                    <h5 class="modal-title" id="updateModalLabel">Update Your Information</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form action="update_information.php" method="post" autocomplete="on">
                                        <div class="mb-3">
                                            <p class="form-label text-start">Email</p>
                                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <p class="form-label text-start">Name</p>
                                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <p class="form-label text-start">Age</p>
                                            <input type="number" class="form-control" id="age" name="age" value="<?php echo htmlspecialchars($user['age']); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <p class="form-label text-start">Grade Level</p>
                                            <select class="form-select" id="grade" name="grade" required>
                                                <option value="Kindergarten" <?php echo $user['grade'] == '0' ? 'selected' : ''; ?>>Kindergarten</option>
                                                <option value="Grade 1" <?php echo $user['grade'] == '1' ? 'selected' : ''; ?>>Grade 1</option>
                                                <option value="Grade 2" <?php echo $user['grade'] == '2' ? 'selected' : ''; ?>>Grade 2</option>
                                                <option value="Grade 3" <?php echo $user['grade'] == '3' ? 'selected' : ''; ?>>Grade 3</option>
                                                <option value="Grade 4" <?php echo $user['grade'] == '4' ? 'selected' : ''; ?>>Grade 4</option>
                                                <option value="Grade 5" <?php echo $user['grade'] == '5' ? 'selected' : ''; ?>>Grade 5</option>
                                                <option value="Grade 6" <?php echo $user['grade'] == '6' ? 'selected' : ''; ?>>Grade 6</option>
                                                <option value="Grade 7" <?php echo $user['grade'] == '7' ? 'selected' : ''; ?>>Grade 7</option>
                                                <option value="Grade 8" <?php echo $user['grade'] == '8' ? 'selected' : ''; ?>>Grade 8</option>
                                                <option value="Grade 9" <?php echo $user['grade'] == '9' ? 'selected' : ''; ?>>Grade 9</option>
                                                <option value="Grade 10" <?php echo $user['grade'] == '10' ? 'selected' : ''; ?>>Grade 10</option>
                                                <option value="Grade 11 - STEM" <?php echo $user['grade'] == 'Grade 11 - STEM' ? 'selected' : ''; ?>>Grade 11 - STEM</option>
                                                <option value="Grade 11 - TVL ICT" <?php echo $user['grade'] == 'Grade 11 - TVL ICT' ? 'selected' : ''; ?>>Grade 11 - TVL ICT</option>
                                                <option value="Grade 11 - TVL HE" <?php echo $user['grade'] == 'Grade 11 - TVL HE' ? 'selected' : ''; ?>>Grade 11 - TVL HE</option>
                                                <option value="Grade 11 - ABM" <?php echo $user['grade'] == 'Grade 11 - ABM' ? 'selected' : ''; ?>>Grade 11 - ABM</option>
                                                <option value="Grade 11 - GAS" <?php echo $user['grade'] == 'Grade 11 - GAS' ? 'selected' : ''; ?>>Grade 11 - GAS</option>
                                                <option value="Grade 11 - HUMSS" <?php echo $user['grade'] == 'Grade 11 - HUMSS' ? 'selected' : ''; ?>>Grade 11 - HUMSS</option>
                                                <option value="Grade 12 - STEM" <?php echo $user['grade'] == 'Grade 12 - STEM' ? 'selected' : ''; ?>>Grade 12 - STEM</option>
                                                <option value="Grade 12 - TVL ICT" <?php echo $user['grade'] == 'Grade 12 - TVL ICT' ? 'selected' : ''; ?>>Grade 12 - TVL ICT</option>
                                                <option value="Grade 12 - TVL HE" <?php echo $user['grade'] == 'Grade 12 - TVL HE' ? 'selected' : ''; ?>>Grade 12 - TVL HE</option>
                                                <option value="Grade 12 - ABM" <?php echo $user['grade'] == 'Grade 12 - ABM' ? 'selected' : ''; ?>>Grade 12 - ABM</option>
                                                <option value="Grade 12 - GAS" <?php echo $user['grade'] == 'Grade 12 - GAS' ? 'selected' : ''; ?>>Grade 12 - GAS</option>
                                                <option value="Grade 12 - HUMSS" <?php echo $user['grade'] == 'Grade 12 - HUMSS' ? 'selected' : ''; ?>>Grade 12 - HUMSS</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <p class="form-label text-start">Teacher</p>
                                            <select class="form-select" id="teacher" name="teacher" required>
                                            <?php foreach ($teachersList as $teacher): ?>
                                                <option value="<?php echo htmlspecialchars($teacher); ?>" <?php echo $user['teacher'] == $teacher ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($teacher); ?>
                                                </option>
                                            <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <p class="form-label text-start">Facebook Account Link</p>
                                            <input type="text" class="form-control" id="fblink" name="fblink" value="<?php echo htmlspecialchars($user['fblink']); ?>" required>
                                        </div>
                                        <hr>
                                        <p class="mt-1 mb-1 text-start">Parent/Guardian Information</p>
                                        <div class="mb-3">
                                            <p class="form-label text-start">Name</p>
                                            <input type="text" class="form-control" id="pname" name="pname" value="<?php echo htmlspecialchars($user['pname']); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <p class="form-label text-start">Relationship</p>
                                            <select class="form-select" id="prelationship" name="prelationship" required>
                                                <option value="Father" <?php echo $user['prelationship'] == 'Father' ? 'selected' : ''; ?>>Father</option>
                                                <option value="Mother" <?php echo $user['prelationship'] == 'Mother' ? 'selected' : ''; ?>>Mother</option>
                                                <option value="Sister" <?php echo $user['prelationship'] == 'Sister' ? 'selected' : ''; ?>>Sister</option>
                                                <option value="Brother" <?php echo $user['prelationship'] == 'Brother' ? 'selected' : ''; ?>>Brother</option>
                                                <option value="Aunt" <?php echo $user['prelationship'] == 'Aunt' ? 'selected' : ''; ?>>Aunt</option>
                                                <option value="Uncle" <?php echo $user['prelationship'] == 'Uncle' ? 'selected' : ''; ?>>Uncle</option>
                                                <option value="Others" <?php echo $user['prelationship'] == 'Others' ? 'selected' : ''; ?>>Others</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <p class="form-label text-start">Mobile Number</p>
                                            <input type="text" class="form-control" id="pnumber" name="pnumber" value="<?php echo htmlspecialchars($user['pnumber']); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <p class="form-label text-start">Email</p>
                                            <input type="text" class="form-control" id="pemail" name="pemail" value="<?php echo htmlspecialchars($user['pemail']); ?>" required>
                                        </div>
                                        <hr>
                                        <button type="submit" class="btn w-100" style="background-color: #A82D2D; color: white;">Save Changes</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

