<?php
include 'database.php';

session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: admin_login");
    exit();
}

$user_role = $_SESSION['role'];
$user_id = $_SESSION['id'];

if ($user_role === 'Admin' || $user_role === 'Guard' || $user_role === 'Teacher') {
    $stmt = $pdo->query("SELECT * FROM users");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt2 = $pdo->query("SELECT * FROM staff");
    $staffs = $stmt2->fetchAll(PDO::FETCH_ASSOC);
} else {
    header("Location: student_dashboard");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Student List</title>
    <link rel="shortcut icon" href="https://i.ibb.co/SB5ZvFh/images.jpg" type="image/jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.css" />
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>

    <style>
        body {
            background-color: #FDEDEE;
            font-family: Arial, sans-serif;
        }
        .sidebar {
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
            padding: 48px 10px 0 15px;
            background-color:rgb(248, 221, 222);
            transition: left 0.3s ease;
        }
        .content {
            margin-left: 250px;
            transition: margin-left 0.3s ease;
        }
        .toggle-btn {
            position: fixed; 
            top: 20px; 
            right: 15px; 
            cursor: pointer;
            z-index: 200;
            display: none; 
        }
        .img-responsive {
            width: 100px; 
            height: auto; 
        }

        .img-thumbnail {
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 5px;
        width: 1in;   /* Set width to 2 inches */
        height: 1in;  /* Set height to 2 inches */
        object-fit: cover;  /* Ensures the image covers the area, keeping its aspect ratio */
    }


        @media (max-width: 768px) {
            .sidebar {
                position: absolute;
                left: -250px;
            }
            .sidebar.active {
                left: 0; 
            }
            .content {
                margin-left: 0; 
            }
            .navbar {
                display: flex;
                justify-content: flex-start; 
                align-items: center;
            }

            .welcome {
                margin-right: auto; 
                margin-left: 5%;
            }

            .toggle-btn {
                display: block; 
            }
            .img-thumbnail {
                border: 1px solid #ddd;
                border-radius: 4px;
                padding: 5px;
                width: 1in;   /* Set width to 2 inches */
                height: 1in;  /* Set height to 2 inches */
                object-fit: cover;  /* Ensures the image covers the area, keeping its aspect ratio */
            }
        }

        .table thead th {
            color: black;
            
        }
        .modal-header {
            background-color: #007bff;
            color: white;
        }
        .modal-footer {
            border-top: none; 
        }
        .table-responsive {
            max-height: 500px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    
    <div class="sidebar">
        <h4>Dashboard</h4>
        <ul class="nav flex-column">
            <?php if ($user_role === 'Teacher'): ?>
                <li class="nav-item">
                    <a class="nav-link active" style="color: #A82D2D;" href="admin">Student List</a>
                </li>
                <hr>
            <?php endif; ?>
            <?php if ($user_role === 'Admin'): ?>
                <li class="nav-item">
                    <a class="nav-link active" style="color: #A82D2D;" href="admin">Student List</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" style="color: #A82D2D;" href="staff">Staff List</a>
                </li>
                <hr>
            <?php endif; ?>
            <?php if ($user_role === 'Guard'): ?>
                <li class="nav-item">
                    <a class="nav-link active" style="color: #A82D2D;" href="attendance">Attendance In</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" style="color: #A82D2D;" href="attendance_out">Attendance Out</a>
                </li>
                <hr>
            <?php endif; ?>
            <li class="nav-item">
                <a class="nav-link" style="color: #A82D2D;" href="logout">Logout</a>
            </li>
        </ul>
    </div>

    <div class="content">
        <nav class="navbar navbar-expand-lg navbar-light">
            <hr>
            <?php
                foreach ($staffs as $staff) {
                    if ($user_id == $staff['id']) {
                        echo '<h2 class="navbar-brand welcome" >Hi, <b>' . htmlspecialchars($staff['name']) . '</b></h2>';
                    }
                }
            ?>
            <button class="btn btn-outline-danger toggle-btn" id="toggleSidebar" aria-label="Toggle Sidebar">
                <i class="fas fa-bars"></i> 
            </button>
        </nav>
                
        <hr>
        <div class="container mt-1">
            <h2>Student List</h2>
            <div class="table-responsive">
                <table id="myTable" class="display">
                    <thead>
                        <tr>  
                            <th>Profile</th>  
                            <th>ID #</th>
                            <th>Name</th>
                            <th>Grade</th>
                            <th>Teacher</th>
                            <th>Action</th>
                            <?php if ($user_role === 'Admin'): ?>
                                <th>Delete</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody id="studentTable">
                        <?php
                        foreach ($staffs as $staff) {
                            if ($user_id == $staff['id']) {
                                foreach ($students as $student) {
                                    if ($student['teacher'] == $staff['name']) {
                                        echo "<tr>";
                                        echo "<td><a href='http://localhost/mcsatt/student_info?idnumber=" . htmlspecialchars($student['id_number']) . "'><img src='" . (!empty($student['image_path']) ? htmlspecialchars($student['image_path']) : 'https://i.ibb.co/s68CT2w/Nice-Png-watsapp-icon-png-9332131.png') . "' alt='Profile Image' class='img-thumbnail img-responsive'></a></td>";
                                        echo "<td>" . htmlspecialchars($student['id_number']) . "</td>";
                                        echo "<td>" . htmlspecialchars($student['name']) . "</td>";
                                        echo "<td>" . htmlspecialchars($student['grade']) . "</td>";
                                        echo "<td>" . htmlspecialchars($student['teacher']) . "</td>";
                                        echo "<td><button style='background-color: #FFD6D6; color: #A82D2D;' class='btn badge mb-3 view-attendance' data-id='" . htmlspecialchars($student['id_number']) . "'>View Record</button></td>";
    
                                        if ($staff['role'] === "Admin" ||  $staff['role'] === "Guard") {
                                            if ($user_role === 'Admin') {
                                                echo "<td><button style='background-color: #A82D2D;' class='btn badge mb-3 delete-student' data-id='" . htmlspecialchars($student['id_number']) . "'>Delete</button></td>";
                                            }
                                        }
    
                                        echo "</tr>";
                                    } elseif ($user_role === 'Admin' ||  $user_role === 'Guard') {

                                        echo "<tr>";
                                        echo "<td><a href='http://localhost/mcsatt/student_info?idnumber=" . htmlspecialchars($student['id_number']) . "'><img src='" . (!empty($student['image_path']) ? htmlspecialchars($student['image_path']) : 'https://i.ibb.co/s68CT2w/Nice-Png-watsapp-icon-png-9332131.png') . "' alt='Profile Image' class='img-thumbnail img-responsive'></a></td>";
                                        echo "<td>" . htmlspecialchars($student['id_number']) . "</td>";
                                        echo "<td>" . htmlspecialchars($student['name']) . "</td>";
                                        echo "<td>" . htmlspecialchars($student['grade']) . "</td>";
                                        echo "<td>" . htmlspecialchars($student['teacher']) . "</td>";
                                        echo "<td><button style='background-color: #FFD6D6; color: #A82D2D;' class='btn badge mb-3 view-attendance' data-id='" . htmlspecialchars($student['id_number']) . "'>View Record</button></td>";
    
                                        if ($staff['role'] === "Admin" ||  $staff['role'] === "Guard") {
                                            if ($user_role === 'Admin') {
                                                echo "<td><button style='background-color: #A82D2D;' class='btn badge mb-3 delete-student' data-id='" . htmlspecialchars($student['id_number']) . "'>Delete</button></td>";
                                            }
                                        }
    
                                        echo "</tr>";
                                    }
                                }
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="modal fade" id="attendanceModal" tabindex="-1" role="dialog" aria-labelledby="attendanceModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header" style="background-color: #FFD6D6;">
                            <h5 class="modal-title text-dark" id="attendanceModalLabel">Attendance</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" >
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
                                        
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                            
                            <?php if ($user_role === 'Teacher'): ?>
                                <button type="button" class="btn btn-success" id="exportToExcel">Export to Excel</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
    const deleteButtons = document.querySelectorAll('.delete-student');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const studentId = this.getAttribute('data-id');
            const studentRow = this.closest('tr'); // Locate the table row for the student

            if (confirm('Are you sure you want to delete this student?')) {
                fetch('delete_student.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: studentId }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        studentRow.remove(); // Remove the row from the table
                    } else {
                        alert('Failed to delete student: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the student.');
                });
            }
        });
    });
});




    $(document).ready(function() {
        let studentName = '';

        $('.view-attendance').click(function() {
            var studentId = $(this).data('id');
            $('#attendanceTable tbody').empty(); 
            $('#attendanceModal').modal('show');

            $.ajax({
                url: 'get_attendance.php',
                type: 'POST',
                data: { id_number: studentId },
                dataType: 'json',
                success: function(data) {
                    if (data.length > 0) {
                        studentName = data[0].name;

                        $.each(data, function(index, record) {
                            let type_text = record.status == 1
                                ? "<span class='badge bg-success'>In</span>"
                                : "<span class='badge bg-danger'>Out</span>"; 
                            
                            // Initialize status_text based on the record status
                            let status_text = '';
                            if (record.status !== 0) { // Check if status is not 0
                                status_text = record.late == 1
                                    ? "<span class='badge bg-danger'>Late</span>"
                                    : "<span class='badge bg-success'>On Time</span>"; 
                            }
                            
                            $('#attendanceTable tbody').append(
                                '<tr><td>' + type_text + '</td>' +
                                '<td>' + (status_text ? status_text : '') + '</td>' + // Only include status_text if it's not empty
                                '<td>' + record.day + '</td>' +
                                '<td>' + record.date + '</td>' +
                                '<td>' + record.time + '</td></tr>'
                            );
                        });
                    } else {
                        $('#attendanceTable tbody').append('<tr><td colspan="4">No attendance records found.</td></tr>');
                    }
                },
                error: function() {
                    alert('Error fetching attendance data.');
                }
            });

            $('#exportToExcel').on('click', function() {
                let formattedStudentName = studentName.trim().replace(/\s+/g, '_');

                let attendanceData = [
                    ["Type","Status", "Day", "Date", "Time"]
                ];
                
                $('#attendanceTable tbody tr').each(function() {
                    let rowData = [];
                    $(this).find('td').each(function() {
                        rowData.push($(this).text());
                    });
                    attendanceData.push(rowData);
                });

                let worksheet = XLSX.utils.aoa_to_sheet(attendanceData);
                let workbook = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(workbook, worksheet, 'Attendance');
                
                let filename = `${formattedStudentName}_Attendance_Record.xlsx`;
                XLSX.writeFile(workbook, filename);
            });
        });

        $('#searchInput').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $("#studentTable tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });

        $('#toggleSidebar').on('click', function() {
            $('.sidebar').toggleClass('active');
            $('.content').toggleClass('active');
        });
    });

    $(document).ready( function () {
        $('#myTable').DataTable();
    } );
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

