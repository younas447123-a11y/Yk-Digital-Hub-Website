<?php
/**
 * Admin Login Page
 * 
 * Allows administrators/editors to authenticate using email and password.
 * On successful login, redirects to admin/index.php.
 */

// Include configuration and authentication helpers
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

// If already logged in, go straight to the admin index
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    // Basic validation
    if (empty($email) || empty($password)) {
        $error = 'Invalid email or password.';
    } else {
        // Use the global $db from database.php
        global $db;

        // Prepare statement to fetch user by email
        $stmt = $db->prepare("SELECT id, name, email, password, role, status FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify user exists, password matches, and account is active
        if ($user && password_verify($password, $user['password'])) {
            // Check if user is active (status = 1)
            if ((int)$user['status'] !== 1) {
                $error = 'Your account is currently inactive. Please contact support.';
            } else {
                // Authentication successful
                // Regenerate session ID for security
                session_regenerate_id(true);

                // Store user data in session
                $_SESSION['user_id']   = (int)$user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];

                // Update last_login timestamp
                $updateStmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
                $updateStmt->execute([':id' => $user['id']]);

                // Redirect to admin dashboard (temporary test page)
                header('Location: index.php');
                exit;
            }
        } else {
            // Generic error to avoid revealing user existence
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - YK Digital Hub</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .login-container {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-logo {
            width: 72px;
            height: 72px;
            object-fit: contain;
            display: block;
            margin: 0 auto 14px;
        }
        .login-header h1 {
            font-size: 26px;
            color: #1a1a2e;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        .login-header .brand {
            display: block;
            font-size: 14px;
            color: #888;
            margin-top: 4px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 6px;
            color: #333;
        }
        .form-group input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
            transition: border-color 0.2s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.15);
        }
        .btn {
            display: block;
            width: 100%;
            padding: 12px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn:hover {
            background: #0056b3;
        }
        .error-msg {
            background: #f8d7da;
            color: #721c24;
            padding: 10px 14px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            display: <?= empty($error) ? 'none' : 'block' ?>;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            color: #999;
        }
        .footer a {
            color: #007bff;
            text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <img src="<?= BASE_URL ?>/uploads/components/logo.png" alt="YK Digital Hub logo" class="login-logo">
            <h1>YK Digital Hub</h1>
            <span class="brand">Admin Login</span>
        </div>

        <!-- Error message area -->
        <div class="error-msg" id="errorMessage">
            <?= htmlspecialchars($error) ?>
        </div>

        <!-- Login form -->
        <form method="POST" action="" autocomplete="off">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required autofocus 
                       placeholder="admin@ykdigitalhub.com" value="">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required 
                       placeholder="••••••••">
            </div>
            <button type="submit" class="btn">Sign In</button>
        </form>

        <div class="footer">
            &copy; <?= date('Y') ?> YK Digital Hub
        </div>
    </div>
</body>
</html>