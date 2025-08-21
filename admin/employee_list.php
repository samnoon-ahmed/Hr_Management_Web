<?php
// employee_list.php: Admin Employee List
require_once '../php/db.php';
$msg = '';

// Handle add new employee
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_employee'])) {
    $name = trim($_POST['name']);
    $dob = $_POST['dob'];
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $designation = trim($_POST['designation']);
    $department = trim($_POST['department']);
    $join_date = $_POST['join_date'];
    $password = trim($_POST['password']);
    $status = 'active'; // Always set to active
    
    if ($name && $dob && $email && $phone && $designation && $department && $join_date && $password) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // First insert into users table
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, 'employee', 'active')");
        $stmt->bind_param('sss', $name, $email, $hashed_password);
        if ($stmt->execute()) {
            // Then insert into employees table
            $stmt2 = $conn->prepare("INSERT INTO employees (name, dob, email, phone, designation, department, join_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt2->bind_param('ssssssss', $name, $dob, $email, $phone, $designation, $department, $join_date, $status);
            if ($stmt2->execute()) {
                $msg = 'Employee added successfully! They can now login with their email and password.';
            } else {
                $msg = 'Employee profile created but failed to add employee details.';
            }
            $stmt2->close();
        } else {
            $msg = 'Failed to create user account.';
        }
        $stmt->close();
    } else {
        $msg = 'Please fill all fields including password.';
    }
}
// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = intval($_POST['delete_id']);
    $conn->query("DELETE FROM employees WHERE id=$id");
    $msg = 'Employee deleted.';
}
// Handle warning request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['warning_id'])) {
    $emp_id = intval($_POST['warning_id']);
    $title = 'Admin Warning';
    $desc = 'Warning issued by admin.';
    $date = date('Y-m-d');
    // Find user_id from employees.email -> users.email
    $res = $conn->query("SELECT u.id FROM users u JOIN employees e ON u.email = e.email WHERE e.id = $emp_id LIMIT 1");
    if ($row = $res->fetch_assoc()) {
        $user_id = $row['id'];
        $stmt = $conn->prepare("INSERT INTO requests (user_id, title, description, date, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->bind_param('isss', $user_id, $title, $desc, $date);
        $stmt->execute();
        $stmt->close();
        $msg = 'Warning request sent.';
    } else {
        $msg = 'User not found for warning.';
    }
}
// Fetch all employees
$employees = [];
$res = $conn->query("SELECT * FROM employees ORDER BY id DESC");
while ($row = $res->fetch_assoc()) {
    $employees[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee List (Admin)</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            background: #f6f8fb;
            min-height: 100vh;
            font-family: 'Segoe UI', 'Roboto', Arial, sans-serif;
            margin: 0;
        }
        .header-bar {
            background: #fff;
            box-shadow: 0 2px 12px 0 rgba(31, 38, 135, 0.07);
            padding: 0.7rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .header-left {
            display: flex;
            align-items: center;
            gap: 1.2rem;
        }
        .logo {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #e6f0ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.3rem;
            color: #2d6cdf;
        }
        .brand {
            font-size: 1.35rem;
            font-weight: 700;
            color: #222;
        }
        .main-menu {
            display: flex;
            justify-content: center;
            gap: 2.5rem;
            background: #fff;
            box-shadow: 0 2px 12px 0 rgba(31, 38, 135, 0.07);
            padding: 0.7rem 0 0.7rem 0;
            margin-bottom: 2.2rem;
        }
        .menu-link {
            color: #2d6cdf;
            text-decoration: none;
            font-size: 1.08rem;
            font-weight: 500;
            padding: 0.5rem 1.2rem;
            border-radius: 8px;
            transition: background 0.18s, color 0.18s;
        }
        .menu-link:hover, .menu-link.active {
            background: #e6f0ff;
            color: #174a8b;
        }
        @media (max-width: 700px) {
            .main-menu {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
        .page-top {
            background: linear-gradient(90deg, #5b9df9 0%, #6a82fb 100%);
            padding: 2.2rem 0 1.5rem 0;
            color: #fff;
            border-bottom-left-radius: 32px;
            border-bottom-right-radius: 32px;
            margin-bottom: 2.5rem;
        }
        .page-breadcrumb {
            font-size: 1rem;
            opacity: 0.85;
            margin-left: 2.5rem;
            margin-bottom: 0.5rem;
        }
        .page-title {
            font-size: 2.1rem;
            font-weight: 700;
            margin-left: 2.5rem;
            margin-bottom: 1.2rem;
        }
        .employee-container {
            max-width: 1100px;
            margin: 0 auto 2.5rem auto;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 18px 0 rgba(31, 38, 135, 0.08);
            padding: 2.2rem 2rem 2rem 2rem;
        }
        .employee-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #2d6cdf;
            margin-bottom: 1.2rem;
        }
        .msg {
            background: #e0ffe0;
            color: #1a7f1a;
            border-radius: 8px;
            padding: 0.7rem 1rem;
            margin-bottom: 1rem;
            text-align: center;
            font-weight: 500;
            border: 1px solid #b2e6b2;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }
        th, td {
            padding: 0.7rem 0.5rem;
            text-align: left;
        }
        th {
            background: #f0f4fa;
            color: #2d6cdf;
            font-weight: 600;
        }
        tr:nth-child(even) td {
            background: #f8fafc;
        }
        .action-btn {
            background: #2d6cdf;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 0.4rem 0.8rem;
            font-size: 1rem;
            cursor: pointer;
            margin-right: 0.3rem;
            transition: background 0.2s;
        }
        .action-btn.delete {
            background: #c00;
        }
        .action-btn.warning {
            background: #ffb347;
            color: #fff;
        }
        .add-employee-form {
            margin-top: 2.5rem;
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.5rem 1rem;
        }
        .add-employee-form label {
            font-weight: 500;
            color: #333;
            margin-bottom: 0.3rem;
            display: block;
        }
        .add-employee-form input, .add-employee-form select {
            padding: 0.7rem 1rem;
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            font-size: 1rem;
            background: #fff;
            margin-bottom: 1rem;
            width: 100%;
        }
        .add-employee-form input:focus, .add-employee-form select:focus {
            outline: none;
            border: 1.5px solid #2d6cdf;
        }
        .add-employee-form .btn {
            width: auto;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="header-bar">
        <div class="header-left">
            <div class="logo">HR</div>
            <span class="brand">HR Management</span>
        </div>
    </div>
    <nav class="main-menu">
        <a href="employee_list.php" class="menu-link active">Employee List</a>
        <a href="leave_admin.php" class="menu-link">Leave</a>
        <a href="../dashboard_admin.php" class="menu-link">Dashboard</a>
        <a href="notice_admin.php" class="menu-link">Notice</a>
        <a href="requests_admin.php" class="menu-link">Requests</a>
        <a href="../logout.php" class="menu-link">Logout</a>
    </nav>
    <div class="page-top">
        <div class="page-breadcrumb">Dashboard &gt; Employee List</div>
        <div class="page-title">Employee List</div>
    </div>
    <div class="employee-container">
        <div class="employee-title">All Employees</div>
        <?php if ($msg): ?><div class="msg"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Designation</th>
                    <th>DOB</th>
                    <th>Join Date</th>
                    <th>Email</th>
                    <th>Mobile</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employees as $emp): ?>
                <tr>
                    <td><?php echo htmlspecialchars($emp['id']); ?></td>
                    <td><?php echo htmlspecialchars($emp['name']); ?></td>
                    <td><?php echo htmlspecialchars($emp['designation']); ?></td>
                    <td><?php echo htmlspecialchars($emp['dob']); ?></td>
                    <td><?php echo htmlspecialchars($emp['join_date']); ?></td>
                    <td><?php echo htmlspecialchars($emp['email']); ?></td>
                    <td><?php echo htmlspecialchars($emp['phone']); ?></td>
                    <td><?php echo htmlspecialchars($emp['status']); ?></td>
                    <td>
                        <button class="action-btn" title="Edit">✏️</button>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="delete_id" value="<?php echo $emp['id']; ?>">
                            <button type="submit" class="action-btn delete" title="Delete" onclick="return confirm('Delete this employee?');">❌</button>
                        </form>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="warning_id" value="<?php echo $emp['id']; ?>">
                            <button type="submit" class="action-btn warning" title="Warning">⚠️</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($employees)): ?>
                <tr><td colspan="9">No employees found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <form method="post" class="add-employee-form">
            <h3>Add New Employee</h3>
            <label>Name</label>
            <input type="text" name="name" required>
            <label>Date of Birth</label>
            <input type="date" name="dob" required>
            <label>Email</label>
            <input type="email" name="email" required>
            <label>Password</label>
            <input type="password" name="password" required>
            <label>Mobile</label>
            <input type="text" name="phone" required>
            <label>Designation</label>
            <input type="text" name="designation" required>
            <label>Department</label>
            <input type="text" name="department" required>
            <label>Join Date</label>
            <input type="date" name="join_date" required>
            <button type="submit" name="add_employee" class="btn">➕ Add Employee</button>
        </form>
    </div>
</body>
</html> 