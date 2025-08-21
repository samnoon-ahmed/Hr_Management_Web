<?php
// directory.php: Employee Directory
require_once '../php/db.php';
$employees = [];
$res = $conn->query("SELECT name, designation, phone, email FROM employees WHERE status='active' ORDER BY name ASC");
while ($row = $res->fetch_assoc()) {
    $employees[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee Directory</title>
    <style>
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
        .dashboard-top {
            background: linear-gradient(90deg, #5b9df9 0%, #6a82fb 100%);
            padding: 2.2rem 0 1.5rem 0;
            color: #fff;
            border-bottom-left-radius: 32px;
            border-bottom-right-radius: 32px;
            margin-bottom: 2.5rem;
        }
        .dashboard-breadcrumb {
            font-size: 1rem;
            opacity: 0.85;
            margin-left: 2.5rem;
            margin-bottom: 0.5rem;
        }
        .dashboard-title {
            font-size: 2.1rem;
            font-weight: 700;
            margin-left: 2.5rem;
            margin-bottom: 1.2rem;
        }
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
        .directory-container {
            max-width: 800px;
            margin: 2.5rem auto;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 18px 0 rgba(31, 38, 135, 0.08);
            padding: 2.2rem 2rem 2rem 2rem;
        }
        .directory-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #2d6cdf;
            margin-bottom: 1.2rem;
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
        <a href="../dashboard_employee.php" class="menu-link">Dashboard</a>
        <a href="leave_employee.php" class="menu-link">Leave Application</a>
        <a href="request_employee.php" class="menu-link">Request Application</a>
        <a href="directory.php" class="menu-link active">Employee Directory</a>
        <a href="../logout.php" class="menu-link">Logout</a>
    </nav>
    <div class="dashboard-top">
        <div class="dashboard-breadcrumb">Dashboard &gt; Employee Directory</div>
        <div class="dashboard-title">Employee Directory</div>
    </div>
    <div class="directory-container">
        <div class="directory-title">Employee Directory</div>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Designation</th>
                    <th>Phone</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employees as $emp): ?>
                <tr>
                    <td><?php echo htmlspecialchars($emp['name']); ?></td>
                    <td><?php echo htmlspecialchars($emp['designation']); ?></td>
                    <td><?php echo htmlspecialchars($emp['phone']); ?></td>
                    <td><?php echo htmlspecialchars($emp['email']); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($employees)): ?>
                <tr><td colspan="4">No employees found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html> 