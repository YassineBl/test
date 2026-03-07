<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/auth.php';

if (isAdminLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        if (login($username, $password)) {
            redirect('dashboard.php');
        } else {
            $error = 'Invalid username or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - OML PARA</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }
        .login-form {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
        }
        .login-form h1 {
            text-align: center;
            margin-bottom: 30px;
            color: var(--dark-color);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: var(--dark-color);
            font-weight: 600;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            font-size: 14px;
        }
        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 5px rgba(46, 204, 113, 0.3);
        }
        .error {
            color: #e74c3c;
            background: #fadbd8;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-login:hover {
            background: #27ae60;
        }
        .login-info {
            background: #ecf0f1;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 13px;
            color: var(--dark-color);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <form class="login-form" method="POST">
            <h1><i class="fas fa-shield-alt"></i> Admin Login</h1>
            
            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn-login">Login</button>
            
            <div class="login-info">
                <strong><i class="fas fa-info-circle"></i> Demo Credentials:</strong><br>
                Username: <strong>admin</strong><br>
                Password: <strong>admin123</strong>
            </div>
        </form>
    </div>
</body>
</html>
