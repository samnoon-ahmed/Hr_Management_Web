<?php
// login_employee.php: Employee Login Page
session_start();
$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', 'Roboto', Arial, sans-serif;
        }
        .container {
            width: 100%;
            max-width: 420px;
            margin: auto;
        }
        .card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
            padding: 2.5rem 2rem 2rem 2rem;
            border: none;
        }
        .card h2 {
            color: #2d6cdf;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }
        .form-label {
            font-weight: 500;
            color: #333;
        }
        .form-control {
            width: 100%;
            padding: 0.75rem 0rem;
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            margin-bottom: 1.2rem;
            font-size: 1rem;
            background: #f8fafc;
            transition: border 0.2s;
        }
        .form-control:focus {
            outline: none;
            border: 1.5px solid #2d6cdf;
            background: #fff;
        }
        .btn-primary {
            background: linear-gradient(90deg, #2d6cdf 0%, #5b9df9 100%);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 0.85rem 0.85rem;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, box-shadow 0.2s;
            box-shadow: 0 2px 8px rgba(45, 108, 223, 0.08);
        }
        .btn-primary:hover {
            background: linear-gradient(90deg, #1b4fa0 0%, #3576c9 100%);
        }
        .text-primary {
            color: #2d6cdf !important;
        }
        .text-center {
            text-align: center;
        }
        .text-decoration-none {
            text-decoration: none;
        }
        .rounded-4 {
            border-radius: 18px;
        }
        .shadow-lg {
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
        }
        .footer {
            text-align: center;
            margin-top: 2rem;
            color: #888;
            font-size: 0.95rem;
        }
        @media (max-width: 600px) {
            .container {
                padding: 0 1rem;
            }
            .card {
                padding: 1.5rem 1rem 1rem 1rem;
            }
        }
        .error-message {
            background: #ffe0e0;
            color: #c00;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
            text-align: center;
            font-weight: 500;
            border: 1px solid #f5c2c7;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card shadow-lg rounded-4">
            <h2 class="text-center text-primary">Employee Login</h2>
            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="post" action="php/login_check.php">
                <input type="hidden" name="role" value="employee">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn-primary w-100 mb-3">Login</button>
                <div class="text-center">
                    <a href="php/forgot_password.php?role=employee" class="text-decoration-none text-primary">Forgot Password?</a>
                </div>
            </form>
        </div>
        <div class="footer">&copy; 2024 HR Management</div>
    </div>
</body>
</html> 