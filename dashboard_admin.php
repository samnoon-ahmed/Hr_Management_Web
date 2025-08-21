<?php
// dashboard_admin.php: Admin Dashboard
session_start();
require_once 'php/db.php';

date_default_timezone_set('Asia/Kolkata'); // Set your timezone
$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));

// Handle warning request action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['warning_user_id'])) {
    $user_id = intval($_POST['warning_user_id']);
    $date = $_POST['warning_date'] ?? $today;
    $title = 'Attendance Warning';
    $desc = 'Warning for missed or irregular attendance on ' . htmlspecialchars($date);
    $stmt = $conn->prepare("INSERT INTO requests (user_id, title, description, date, status) VALUES (?, ?, ?, ?, 'pending')");
    $stmt->bind_param('isss', $user_id, $title, $desc, $date);
    $stmt->execute();
    $stmt->close();
    $warning_msg = 'Warning request sent.';
}

// Total active employees
$total_employees = 0;
$res = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='employee' AND status='active'");
if ($row = $res->fetch_assoc()) {
    $total_employees = (int)$row['total'];
}

// Present today
$present_today = 0;
$res = $conn->query("SELECT COUNT(DISTINCT user_id) as total FROM attendance WHERE date='$today'");
if ($row = $res->fetch_assoc()) {
    $present_today = (int)$row['total'];
}

// On leave today
$on_leave = 0;
$res = $conn->query("SELECT COUNT(DISTINCT user_id) as total FROM leave_applications WHERE status='approved' AND '$today' BETWEEN start_date AND end_date");
if ($row = $res->fetch_assoc()) {
    $on_leave = (int)$row['total'];
}

// Absent = total - present - on leave
$absent = max(0, $total_employees - $present_today - $on_leave);

// Total approved leave days taken by employees
$total_leave_days = 0;
$res = $conn->query("SELECT COALESCE(SUM(DATEDIFF(end_date, start_date) + 1), 0) AS total_days FROM leave_applications WHERE status='approved'");
if ($row = $res->fetch_assoc()) {
	$total_leave_days = (int)$row['total_days'];
}

// Real-time Attendance Table (today)
$attendance_today = [];
$sql = "SELECT u.id, u.name, e.designation, a.in_time, a.out_time, a.location
        FROM users u
        LEFT JOIN employees e ON u.email = e.email
        LEFT JOIN attendance a ON u.id = a.user_id AND a.date = '$today'
        WHERE u.role = 'employee' AND u.status = 'active'";
$res = $conn->query($sql);
while ($row = $res->fetch_assoc()) {
    // Check if on leave
    $leave_sql = "SELECT 1 FROM leave_applications WHERE user_id = {$row['id']} AND status = 'approved' AND '$today' BETWEEN start_date AND end_date LIMIT 1";
    $leave_res = $conn->query($leave_sql);
    $is_on_leave = $leave_res->num_rows > 0;
    $row['status'] = $is_on_leave ? 'On Leave' : ($row['in_time'] ? 'Present' : 'Absent');
    $attendance_today[] = $row;
}

// Missed Attendance (Yesterday): Absent employees
$missed_attendance = [];
$sql = "SELECT u.id, u.name, e.designation
        FROM users u
        LEFT JOIN employees e ON u.email = e.email
        WHERE u.role = 'employee' AND u.status = 'active' AND u.id NOT IN (
            SELECT user_id FROM attendance WHERE date = '$yesterday'
            UNION
            SELECT user_id FROM leave_applications WHERE status = 'approved' AND '$yesterday' BETWEEN start_date AND end_date
        )";
$res = $conn->query($sql);
while ($row = $res->fetch_assoc()) {
    $missed_attendance[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
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
        .header-icons {
            display: flex;
            align-items: center;
            gap: 1.2rem;
        }
        .header-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #f0f4fa;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2d6cdf;
            font-size: 1.2rem;
            cursor: pointer;
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
        .overview-cards {
            display: flex;
            gap: 1.5rem;
            justify-content: flex-start;
            margin-left: 2.5rem;
            margin-bottom: 1.5rem;
        }
        .card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 18px 0 rgba(31, 38, 135, 0.10);
            padding: 1.2rem 2rem 1.2rem 1.5rem;
            min-width: 210px;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 1.1rem;
        }
        .card-icon {
            font-size: 2.1rem;
            color: #5b9df9;
            background: #e6f0ff;
            border-radius: 50%;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card-content {
            display: flex;
            flex-direction: column;
        }
        .card-value {
            font-size: 1.7rem;
            font-weight: 700;
            color: #222;
        }
        .card-label {
            font-size: 1.05rem;
            color: #666;
            letter-spacing: 0.5px;
        }
        .main-content {
            display: flex;
            gap: 2rem;
            align-items: flex-start;
            max-width: 1400px;
            margin: 0 auto;
        }
        .attendance-table-container {
            flex: 2.5;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 18px 0 rgba(31, 38, 135, 0.08);
            padding: 2rem 2rem 2rem 2rem;
            margin-bottom: 2rem;
        }
        .attendance-table-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #2d6cdf;
            margin-bottom: 1.2rem;
        }
        .attendance-table-controls {
            display: flex;
            align-items: center;
            gap: 1.2rem;
            margin-bottom: 1.2rem;
        }
        .search-bar {
            flex: 1;
            background: #f0f4fa;
            border: none;
            border-radius: 8px;
            padding: 0.7rem 1.2rem;
            font-size: 1rem;
            outline: none;
        }
        .status-chips {
            display: flex;
            gap: 0.5rem;
        }
        .chip {
            background: #e6f0ff;
            color: #2d6cdf;
            border-radius: 999px;
            padding: 0.3rem 0.9rem;
            font-size: 0.98rem;
            font-weight: 500;
            cursor: pointer;
            border: none;
            outline: none;
            transition: background 0.15s, color 0.15s;
        }
        .chip.active, .chip:hover {
            background: #5b9df9;
            color: #fff;
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
        .status-present {
            color: #1bbf5c;
            font-weight: 600;
        }
        .status-onleave {
            color: #f39c12;
            font-weight: 600;
        }
        .status-absent {
            color: #c00;
            font-weight: 600;
        }
        .action-btn {
            background: #ffb347;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 0.4rem 1rem;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        .action-btn:hover {
            background: #ff8800;
        }
        .side-panel {
            flex: 1.2;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 18px 0 rgba(31, 38, 135, 0.08);
            padding: 2rem 1.2rem 2rem 1.2rem;
            min-width: 320px;
        }
        .side-panel-title {
            font-size: 1.15rem;
            font-weight: 600;
            color: #2d6cdf;
            margin-bottom: 1.2rem;
        }
        .side-panel-date {
            font-size: 0.98rem;
            color: #888;
            margin-bottom: 0.7rem;
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
        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #e6e6e6;
            display: inline-block;
            margin-right: 0.7rem;
            vertical-align: middle;
            object-fit: cover;
        }
        .emp-info {
            display: flex;
            align-items: center;
        }
        .emp-meta {
            font-size: 0.95rem;
            color: #888;
        }
        @media (max-width: 1100px) {
            .main-content {
                flex-direction: column;
            }
            .side-panel {
                margin-top: 2rem;
            }
        }
        @media (max-width: 700px) {
            .overview-cards, .main-content {
                flex-direction: column;
                gap: 1.2rem;
                margin-left: 0;
            }
            .dashboard-title, .dashboard-breadcrumb {
                margin-left: 1rem;
            }
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
        <a href="admin/employee_list.php" class="menu-link">Employee List</a>
        <a href="admin/leave_admin.php" class="menu-link">Leave</a>
        <a href="dashboard_admin.php" class="menu-link active">Dashboard</a>
        <a href="admin/notice_admin.php" class="menu-link">Notice</a>
        <a href="admin/requests_admin.php" class="menu-link">Requests</a>
        <a href="logout.php" class="menu-link">Logout</a>
    </nav>
    <div class="dashboard-top">
        <div class="dashboard-breadcrumb">Dashboard > Attendance</div>
        <div class="dashboard-title">Dashboard</div>
        <div class="overview-cards">
            <div class="card">
                <div class="card-icon">&#128337;</div>
                <div class="card-content">
                    <div class="card-value"><?php echo $present_today; ?></div>
                    <div class="card-label">Present Today</div>
                </div>
            </div>
            <div class="card">
                <div class="card-icon">&#128221;</div>
                <div class="card-content">
                    <div class="card-value"><?php echo $absent; ?></div>
                    <div class="card-label">Absent</div>
                </div>
            </div>
            <div class="card">
                <div class="card-icon">&#128197;</div>
                <div class="card-content">
                    <div class="card-value"><?php echo $on_leave; ?></div>
                    <div class="card-label">On Leave</div>
                </div>
            </div>
            <div class="card">
                <div class="card-icon">&#128202;</div>
                <div class="card-content">
                    <div class="card-value"><?php echo $total_leave_days; ?></div>
                    <div class="card-label">Total Leave Days Taken</div>
                </div>
            </div>
        </div>
    </div>
    <?php if (!empty($warning_msg)): ?>
        <div class="msg" style="max-width: 900px; margin: 1rem auto;"> <?php echo htmlspecialchars($warning_msg); ?> </div>
    <?php endif; ?>
    <div class="main-content">
        <div class="attendance-table-container">
            <div class="attendance-table-title">Quick View</div>
            <div class="attendance-table-controls">
                <input type="text" class="search-bar" placeholder="Search..." />
                <div class="status-chips">
                    <button class="chip active">All</button>
                    <button class="chip">P</button>
                    <button class="chip">D</button>
                    <button class="chip">A</button>
                    <button class="chip">L</button>
                </div>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Designation</th>
                        <th>In Time</th>
                        <th>Out Time</th>
                        <th>Status</th>
                        <th>Time</th>
                        <th>Location</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attendance_today as $row): ?>
                        <?php if ($row['status'] === 'Present' || $row['status'] === 'On Leave'): ?>
                        <tr>
                            <td>
                                <div class="emp-info">
                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($row['name']); ?>&background=e6e6e6&color=222&size=36" class="avatar" alt="avatar" />
                                    <div>
                                        <?php echo htmlspecialchars($row['name']); ?><br>
                                        <span class="emp-meta">Area71 HQ</span>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($row['designation']); ?></td>
                            <td><?php echo $row['in_time'] ? htmlspecialchars(substr($row['in_time'],0,5)) : '-'; ?></td>
                            <td><?php echo $row['out_time'] ? htmlspecialchars(substr($row['out_time'],0,5)) : '-'; ?></td>
                            <td class="status-<?php echo strtolower(str_replace(' ', '', $row['status'])); ?>"><?php echo $row['status']; ?></td>
                            <td>
                                <?php
                                if ($row['in_time'] && $row['out_time']) {
                                    $in = strtotime($row['in_time']);
                                    $out = strtotime($row['out_time']);
                                    $diff = $out - $in;
                                    $hours = floor($diff / 3600);
                                    $mins = floor(($diff % 3600) / 60);
                                    echo $hours . 'h ' . $mins . 'm';
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <td><?php echo $row['location'] ? htmlspecialchars($row['location']) : '-'; ?></td>
                            <td>
                                <form method="post" style="margin:0;">
                                    <input type="hidden" name="warning_user_id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="warning_date" value="<?php echo $today; ?>">
                                    <button type="submit" class="action-btn" title="Send Warning">&#9888; Warning</button>
                                </form>
                            </td>
                        </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="side-panel">
            <div class="side-panel-title">Attendance Missed</div>
            <div class="side-panel-date"><?php echo date('d-m-Y', strtotime('-1 day')); ?></div>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Name</th>
                        
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($missed_attendance as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($yesterday); ?></td>
                        <td>
                            <div class="emp-info">
                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($row['name']); ?>&background=e6e6e6&color=222&size=36" class="avatar" alt="avatar" />
                                <div>
                                    <?php echo htmlspecialchars($row['name']); ?><br>
                                    <span class="emp-meta">Area71 HQ</span>
                                </div>
                            </div>
                        </td>
                        
                        <td class="status-absent">Absent</td>
                        <td>
                            <form method="post" style="margin:0;">
                                <input type="hidden" name="warning_user_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="warning_date" value="<?php echo $yesterday; ?>">
                                <button type="submit" class="action-btn" title="Send Warning">&#9888; Warning</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html> 