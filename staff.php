<?php
include 'database.php';

session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: admin_login");
    exit();
}


$user_role = $_SESSION['role'];
$user_id = $_SESSION['id'];

if ($user_role === 'Admin' || $user_role === 'Guard') {
    $stmt2 = $pdo->query("SELECT * FROM staff");
    $students = $stmt2->fetchAll(PDO::FETCH_ASSOC);
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
    <title>Admin - Staff List</title>
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
        }
    </style>
</head>
<body>

        <div class="alert" id="responseMessage" style="position: fixed; bottom: 20px; right: 20px; z-index: 1050; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);">
        </div>
    <div class="sidebar" id="sidebar">
        <h4>Dashboard</h4>
        <ul class="nav flex-column">
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
                    <a class="nav-link active text-info" href="attendance">Attendance In</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-info" href="attendance_out">Attendance Out</a>
                </li>
                <hr>
            <?php endif; ?>
            <li class="nav-item">
                <a class="nav-link" style="color: #A82D2D;" href="logout">Logout</a>
            </li>
        </ul>
    </div>

    <div class="content" id="content">
        <nav class="navbar navbar-expand-lg navbar-light">
            <?php foreach ($students as $staff): ?>
                <?php if ($user_id == $staff['id']): ?>
                    <h2 class="navbar-brand welcome">Hi, <b><?= htmlspecialchars($staff['name']); ?></b></h2>
                <?php endif; ?>
            <?php endforeach; ?>
            <button class="btn btn-outline-danger toggle-btn" id="toggleSidebar" aria-label="Toggle Sidebar">
                <i class="fas fa-bars"></i>
            </button>
        </nav>
        <hr>
        <div class="container mt-2">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2>Staff List</h2>
                <button class="btn" style='background-color:rgb(248, 221, 222); color: #A82D2D;' data-bs-toggle="modal" data-bs-target="#createStaffModal">Create Staff</button>
            </div>
            <div class="table-responsive">
                <table id="myTable" class="display">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="studentTable">
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?= htmlspecialchars($student['name']); ?></td>
                                <td><?= htmlspecialchars($student['position']); ?></td>
                                <td>
                                <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#updateStaffModal" data-id="<?= $student['id']; ?>" data-name="<?= htmlspecialchars($student['name']); ?>" data-email="<?= htmlspecialchars($student['email']); ?>" data-password="<?= htmlspecialchars($student['password']); ?>" data-position="<?= htmlspecialchars($student['position']); ?>">
                                    <span class='badge mb-3' style='background-color: #FFD6D6; color: #A82D2D;'>Update</span>
                                </a>


                                    <a href="" class="text-decoration-none">
                                        <span class='badge mb-3 delete-staff' data-id="<?= $student['id']; ?>" style='background-color: #A82D2D; color: white;'>Delete</span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="updateStaffModal" tabindex="-1" aria-labelledby="updateStaffModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #FFD6D6;">
                <h5 class="modal-title" id="updateStaffModalLabel">Update Staff</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updateStaffForm">
                    <input type="hidden" id="updateStaffId" name="id">
                    <div class="mb-3">
                        <label for="updateStaffName" class="form-label">Name</label>
                        <input type="text" class="form-control" id="updateStaffName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="updateStaffEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="updateStaffEmail" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="updateStaffNewPassword" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="updateStaffNewPassword" name="new_password">
                    </div>
                    <div class="mb-3">
                        <label for="updateStaffPosition" class="form-label">Position</label>
                        <select class="form-select" id="updateStaffPosition" name="position" required>
                            <option value="Administrator">Administrator</option>
                            <option value="Teacher">Teacher</option>
                            <option value="Guard">Guard</option>
                        </select>
                    </div>
                    <button type="submit" class="btn w-100 mb-3" style='background-color: #A82D2D; color: white;'>Update</button>
                </form>
            </div>
        </div>
    </div>
</div>

    <div class="modal fade" id="createStaffModal" tabindex="-1" aria-labelledby="createStaffModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #FFD6D6;">
                    <h5 class="modal-title" id="createStaffModalLabel">Create Staff</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="createStaffForm">
                        <div class="mb-3">
                            <label for="staffName" class="form-label">Name</label>
                            <input type="text" class="form-control" id="staffName" required>
                        </div>
                        <div class="mb-3">
                            <label for="staffEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="staffEmail" required>
                        </div>
                        <div class="mb-3">
                            <label for="staffPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="staffPassword" required>
                        </div>
                        <div class="mb-3">
                            <label for="staffPosition" class="form-label">Position</label>
                            <select class="form-select" id="staffPosition" required>
                                <option value="Administrator">Administrator</option>
                                <option value="Teacher">Teacher</option>
                                <option value="Guard">Guard</option>
                            </select>
                        </div>
                        <button type="submit" class="btn w-100 mb-3" style='background-color: #A82D2D; color: white;'>Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
    const deleteButtons = document.querySelectorAll('.delete-staff');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const studentId = this.getAttribute('data-id');
            const studentRow = this.closest('tr'); // Locate the table row for the student

            if (confirm('Are you sure you want to delete this staff?')) {
                fetch('delete_staff.php', {
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
                        alert('Failed to delete staff: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the staff.');
                });
            }
        });
    });
});


    </script>

    <script>
$(document).on('click', '[data-bs-target="#updateStaffModal"]', function () {
    const staffId = $(this).data('id');
    const staffName = $(this).data('name');
    const staffEmail = $(this).data('email');
    const staffPosition = $(this).data('position');

    $('#updateStaffId').val(staffId);
    $('#updateStaffName').val(staffName);
    $('#updateStaffEmail').val(staffEmail);
    $('#updateStaffPosition').val(staffPosition);
    $('#updateStaffNewPassword').val(''); // Reset new password
});

$(document).ready(function () {
    $('#updateStaffForm').on('submit', function (event) {
        event.preventDefault();

        $.ajax({
            type: 'POST',
            url: 'update_staff.php',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (response) {
                const messageBox = $('#responseMessage');
                messageBox.removeClass('alert-success alert-danger').addClass(response.status === 'success' ? 'alert-success' : 'alert-danger');
                messageBox.text(response.message).fadeIn();

                setTimeout(() => {
                    messageBox.fadeOut();
                }, 5000);
            },
            error: function (xhr, status, error) {
                console.error('Error:', xhr.responseText);
                $('#responseMessage').addClass('alert-danger').text('An error occurred while processing the request.').fadeIn();

                setTimeout(() => {
                    $('#responseMessage').fadeOut();
                }, 5000);
            }
        });
    });

    $('#updateStaffModal').on('hidden.bs.modal', function () {
        $('#updateStaffForm')[0].reset(); // Clear the form when modal is closed
    });
});

</script>

    <script>
        $(document).ready(function () {
            $('#searchInput').on('keyup', function () {
                var value = $(this).val().toLowerCase();
                $('#studentTable tr').filter(function () {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });

            $('#createStaffForm').on('submit', function (event) {
                event.preventDefault();
                var formData = {
                    name: $('#staffName').val(),
                    email: $('#staffEmail').val(),
                    password: $('#staffPassword').val(),
                    position: $('#staffPosition').val()
                };

                $.ajax({
                    type: 'POST',
                    url: 'create_staff.php',
                    data: formData,
                    success: function (response) {
                        alert(response);
                        $('#createStaffModal').modal('hide');
                        location.reload();
                    },
                    error: function () {
                        alert('There was an error creating the staff member.');
                    }
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
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</body>
</html>
