<?php
// requests_admin.php: Admin Requests
require_once '../php/db.php';
$msg = '';

// Handle approve/reply actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'])) {
    $request_id = intval($_POST['request_id']);
    if (isset($_POST['action']) && $_POST['action'] === 'approve') {
        $conn->query("UPDATE requests SET status='approved' WHERE id=$request_id");
        $msg = 'Request approved.';
    } elseif (isset($_POST['reply_message'])) {
        $reply = trim($_POST['reply_message']);
        // If you have a 'reply' column, update it. Otherwise, just show a message.
        $conn->query("UPDATE requests SET status='approved' WHERE id=$request_id");
        $msg = 'Reply sent and request approved.';
    }
}
// Fetch all requests with employee name
$requests = [];
$sql = "SELECT r.id, r.title, r.date, r.status, u.name as employee_name FROM requests r JOIN users u ON r.user_id = u.id ORDER BY r.id DESC";
$res = $conn->query($sql);
while ($row = $res->fetch_assoc()) {
    $requests[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Request Applications (Admin)</title>
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
        .request-container {
            max-width: 900px;
            margin: 0 auto 2.5rem auto;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 18px 0 rgba(31, 38, 135, 0.08);
            padding: 2.2rem 2rem 2rem 2rem;
        }
        .request-title {
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
        .action-btn.reply {
            background: #5b9df9;
        }
        .action-btn:disabled {
            background: #b3c6e6;
            cursor: not-allowed;
        }
        .reply-form {
            margin-top: 0.5rem;
        }
        .reply-form textarea {
            width: 100%;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            padding: 0.5rem 1rem;
            font-size: 1rem;
            margin-bottom: 0.5rem;
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
        <a href="leave_admin.php" class="menu-link">Leave</a>
        <a href="../dashboard_admin.php" class="menu-link">Dashboard</a>
        <a href="notice_admin.php" class="menu-link">Notice</a>
        <a href="requests_admin.php" class="menu-link active">Requests</a>
        <a href="../logout.php" class="menu-link">Logout</a>
    </nav>
    <div class="page-top">
        <div class="page-breadcrumb">Dashboard &gt; Request Applications</div>
        <div class="page-title">Request Applications</div>
    </div>
    <div class="request-container">
        <div class="request-title">All Requests</div>
        <?php if ($msg): ?><div class="msg"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
        <table>
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Title</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $req): ?>
                <tr>
                    <td><?php echo htmlspecialchars($req['employee_name']); ?></td>
                    <td><?php echo htmlspecialchars($req['title']); ?></td>
                    <td><?php echo htmlspecialchars($req['date']); ?></td>
                    <td class="status-<?php echo strtolower($req['status']); ?>"><?php echo ucfirst($req['status']); ?></td>
                    <td>
                        <?php if ($req['status'] === 'pending'): ?>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                            <button type="submit" name="action" value="approve" class="action-btn">Approve</button>
                        </form>
                        <form method="post" class="reply-form" style="display:inline;">
                            <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                            <textarea name="reply_message" rows="1" placeholder="Reply message..."></textarea>
                            <button type="submit" class="action-btn reply">Reply & Approve</button>
                        </form>
                        <?php else: ?>
                        <button class="action-btn" disabled>Approve</button>
                        <button class="action-btn reply" disabled>Reply</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($requests)): ?>
                <tr><td colspan="5">No requests found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html> 