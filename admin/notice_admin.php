<?php
// notice_admin.php: Admin Notice Board
require_once '../php/db.php';
$msg = '';

// Handle notice publishing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'], $_POST['date'], $_POST['description'])) {
    $title = trim($_POST['title']);
    $date = $_POST['date'];
    $desc = trim($_POST['description']);
    if ($title && $date && $desc) {
        $stmt = $conn->prepare("INSERT INTO notices (title, description, published_date) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $title, $desc, $date);
        if ($stmt->execute()) {
            $msg = 'Notice published!';
        } else {
            $msg = 'Failed to publish notice.';
        }
        $stmt->close();
    } else {
        $msg = 'Please fill all fields.';
    }
}
// Fetch all notices
$notices = [];
$res = $conn->query("SELECT title, description, published_date FROM notices ORDER BY published_date DESC, id DESC");
while ($row = $res->fetch_assoc()) {
    $notices[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notice Board (Admin)</title>
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
        .notice-container {
            max-width: 900px;
            margin: 0 auto 2.5rem auto;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 18px 0 rgba(31, 38, 135, 0.08);
            padding: 2.2rem 2rem 2rem 2rem;
        }
        .notice-title {
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
        .notice-form {
            display: flex;
            flex-direction: column;
            gap: 1.1rem;
            margin-bottom: 2rem;
        }
        .notice-form label {
            font-weight: 500;
            color: #333;
            margin-bottom: 0.3rem;
        }
        .notice-form input, .notice-form textarea {
            padding: 0.7rem 1rem;
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            font-size: 1rem;
            background: #f8fafc;
            transition: border 0.2s;
        }
        .notice-form input:focus, .notice-form textarea:focus {
            outline: none;
            border: 1.5px solid #2d6cdf;
            background: #fff;
        }
        .btn {
            background: linear-gradient(90deg, #2d6cdf 0%, #5b9df9 100%);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 0.85rem 1.5rem;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, box-shadow 0.2s;
            box-shadow: 0 2px 8px rgba(45, 108, 223, 0.08);
        }
        .btn:hover {
            background: linear-gradient(90deg, #1b4fa0 0%, #3576c9 100%);
        }
        .notice-list-title {
            font-size: 1.1rem;
            color: #2d6cdf;
            font-weight: 600;
            margin-bottom: 0.7rem;
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
        <a href="employee_list.php" class="menu-link">Employee List</a>
        <a href="leave_admin.php" class="menu-link">Leave</a>
        <a href="../dashboard_admin.php" class="menu-link">Dashboard</a>
        <a href="notice_admin.php" class="menu-link active">Notice</a>
        <a href="requests_admin.php" class="menu-link">Requests</a>
        <a href="../logout.php" class="menu-link">Logout</a>
    </nav>
    <div class="page-top">
        <div class="page-breadcrumb">Dashboard &gt; Notice Board</div>
        <div class="page-title">Notice Board</div>
    </div>
    <div class="notice-container">
        <div class="notice-title">Publish Notice</div>
        <?php if ($msg): ?><div class="msg"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
        <form method="post" class="notice-form">
            <div>
                <label>Title</label>
                <input type="text" name="title" required>
            </div>
            <div>
                <label>Date</label>
                <input type="date" name="date" required>
            </div>
            <div>
                <label>Description</label>
                <textarea name="description" rows="2" required></textarea>
            </div>
            <button type="submit" class="btn">Publish Notice</button>
        </form>
        <div class="notice-list-title">All Notices</div>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Date</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($notices as $notice): ?>
                <tr>
                    <td><?php echo htmlspecialchars($notice['title']); ?></td>
                    <td><?php echo htmlspecialchars($notice['published_date']); ?></td>
                    <td><?php echo htmlspecialchars($notice['description']); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($notices)): ?>
                <tr><td colspan="3">No notices found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html> 