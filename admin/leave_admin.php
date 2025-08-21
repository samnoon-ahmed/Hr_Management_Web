<?php
// leave_admin.php: Admin Leave Management
require_once '../php/db.php';
$msg = '';

// Handle approve/cancel actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['leave_id'], $_POST['action'])) {
    $leave_id = intval($_POST['leave_id']);
    $action = $_POST['action'];
    if ($action === 'approve') {
        $conn->query("UPDATE leave_applications SET status='approved' WHERE id=$leave_id");
        $msg = 'Leave approved.';
    } elseif ($action === 'cancel') {
        $conn->query("UPDATE leave_applications SET status='cancelled' WHERE id=$leave_id");
        $msg = 'Leave cancelled.';
    }
}
// Fetch all leave applications with employee name
$leaves = [];
$sql = "SELECT l.id, l.reason, l.start_date, l.end_date, l.status, u.name as employee_name FROM leave_applications l JOIN users u ON l.user_id = u.id ORDER BY l.id DESC";
$res = $conn->query($sql);
while ($row = $res->fetch_assoc()) {
    $leaves[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Leave Applications (Admin)</title>
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
        .leave-container {
            max-width: 1000px;
            margin: 0 auto 2.5rem auto;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 18px 0 rgba(31, 38, 135, 0.08);
            padding: 2.2rem 2rem 2rem 2rem;
        }
        .leave-title {
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
        .status-approved {
            color: #1bbf5c;
            font-weight: 600;
        }
        .status-pending {
            color: #f39c12;
            font-weight: 600;
        }
        .status-cancelled {
            color: #c00;
            font-weight: 600;
        }
        .action-btn {
            background: #2d6cdf;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 0.4rem 1rem;
            font-size: 1rem;
            cursor: pointer;
            margin-right: 0.5rem;
            transition: background 0.2s;
        }
        .action-btn.cancel {
            background: #c00;
        }
        .action-btn:disabled {
            background: #b3c6e6;
            cursor: not-allowed;
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
        <a href="employee_list.php" class="menu-link">Employee List</a>
        <a href="leave_admin.php" class="menu-link active">Leave</a>
        <a href="../dashboard_admin.php" class="menu-link">Dashboard</a>
        <a href="notice_admin.php" class="menu-link">Notice</a>
        <a href="requests_admin.php" class="menu-link">Requests</a>
        <a href="../logout.php" class="menu-link">Logout</a>
    </nav>
    <div class="page-top">
        <div class="page-breadcrumb">Dashboard &gt; Leave Applications</div>
        <div class="page-title">Leave Applications</div>
    </div>
    <div class="leave-container">
        <?php if ($msg): ?><div class="msg"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
        <table>
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Reason</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($leaves as $leave): ?>
                <tr>
                    <td><?php echo htmlspecialchars($leave['employee_name']); ?></td>
                    <td><?php echo htmlspecialchars($leave['reason']); ?></td>
                    <td><?php echo htmlspecialchars($leave['start_date']); ?></td>
                    <td><?php echo htmlspecialchars($leave['end_date']); ?></td>
                    <td class="status-<?php echo strtolower($leave['status']); ?>"><?php echo ucfirst($leave['status']); ?></td>
                    <td>
                        <?php if ($leave['status'] === 'pending'): ?>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="leave_id" value="<?php echo $leave['id']; ?>">
                            <button type="submit" name="action" value="approve" class="action-btn">Approve</button>
                            <button type="submit" name="action" value="cancel" class="action-btn cancel">Cancel</button>
                        </form>
                        <?php else: ?>
                        <button class="action-btn" disabled>Approve</button>
                        <button class="action-btn cancel" disabled>Cancel</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($leaves)): ?>
                <tr><td colspan="6">No leave applications found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html> 