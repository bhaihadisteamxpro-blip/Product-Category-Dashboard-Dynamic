<?php
// frontend/login.php
session_start();
require_once '../database/db.php'; // Using our new PDO connection

/*
// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] == 'super_admin') {
        header("Location: ../super_admin_dashboard/superadmin.php");
    } else {
        header("Location: ../User_admin/Admin.php");
    }
    exit();
}
*/

$error = '';
$login_type = isset($_GET['type']) ? $_GET['type'] : 'super_admin';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $user_type = $_POST['user_type'];
    
    if (empty($username) || empty($password)) {
        $error = "Please enter username and password!";
    } else {
        try {
            if ($user_type == 'super_admin') {
                // Super Admin credentials (hardcoded for demo)
                if ($username == 'superadmin' && $password == 'admin123') {
                    $_SESSION['user_id'] = 1;
                    $_SESSION['username'] = 'superadmin';
                    $_SESSION['full_name'] = 'Super Admin';
                    $_SESSION['user_role'] = 'super_admin';
                    $_SESSION['admin_id'] = 'SUPER001';
                    $_SESSION['department'] = 'management';
                    
                    header("Location: ../super_admin_dashboard/superadmin.php");
                    exit();
                } else {
                    $error = "Invalid super admin credentials!";
                }
            } else {
                // Admin credentials - Check from database using PDO
                $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role = 'admin' AND status = 'active'");
                $stmt->execute([$username]);
                $user = $stmt->fetch();
                
                if ($user) {
                    // Verify password
                    if (password_verify($password, $user['password'])) {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['full_name'] = $user['full_name'];
                        $_SESSION['user_role'] = $user['role'];
                        $_SESSION['admin_id'] = $user['admin_id'];
                        $_SESSION['department'] = $user['department'];
                        
                        // Update last login
                        $update_stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                        $update_stmt->execute([$user['id']]);
                        
                        header("Location: ../User_admin/Admin.php");
                        exit();
                    } else {
                        $error = "Invalid password!";
                    }
                } else {
                    $error = "Admin not found or inactive!";
                }
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Stock Management System</title>
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../assets/plugins/fontawesome-free/css/all.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../assets/dist/css/adminlte.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Source Sans Pro', sans-serif;
            margin: 0;
            overflow: hidden;
        }
        .login-box {
            width: 100%;
            max-width: 450px;
            animation: slideIn 0.6s ease-out;
            position: relative;
            z-index: 2;
        }
        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-logo h1 {
            color: white;
            font-size: 42px;
            font-weight: 700;
            text-shadow: 2px 2px 10px rgba(0,0,0,0.3);
            margin-bottom: 10px;
        }
        .login-logo p {
            color: rgba(255,255,255,0.9);
            font-size: 18px;
            font-weight: 300;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            overflow: hidden;
            backdrop-filter: blur(10px);
        }
        .login-header {
            background: linear-gradient(to right, #007bff, #6610f2);
            color: white;
            padding: 25px;
            text-align: center;
        }
        .login-header h2 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }
        .login-header p {
            margin: 10px 0 0;
            opacity: 0.9;
            font-size: 16px;
        }
        .login-tabs {
            display: flex;
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }
        .login-tab {
            flex: 1;
            padding: 18px;
            text-align: center;
            background: transparent;
            border: none;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #495057;
            border-bottom: 3px solid transparent;
        }
        .login-tab.active {
            background: white;
            color: #007bff;
            border-bottom: 3px solid #007bff;
        }
        .login-tab:hover:not(.active) {
            background: #e9ecef;
        }
        .login-tab i {
            margin-right: 8px;
            font-size: 18px;
        }
        .login-body {
            padding: 35px;
        }
        .login-icon {
            text-align: center;
            margin-bottom: 25px;
        }
        .login-icon i {
            font-size: 70px;
            color: #007bff;
            background: linear-gradient(135deg, #007bff, #6610f2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .input-group {
            margin-bottom: 25px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }
        .input-group:focus-within {
            box-shadow: 0 5px 15px rgba(0,123,255,0.2);
            transform: translateY(-2px);
        }
        .input-group-prepend .input-group-text {
            background: #f8f9fa;
            border: none;
            border-right: 1px solid #e9ecef;
            padding: 15px;
            color: #6c757d;
        }
        .form-control {
            border: none;
            border-left: 0;
            padding: 15px;
            font-size: 16px;
            background: white;
        }
        .form-control:focus {
            box-shadow: none;
            background: #fff;
        }
        .password-toggle {
            cursor: pointer;
            background: #f8f9fa !important;
            border: none !important;
            color: #007bff !important;
        }
        .password-toggle:hover {
            background: #e9ecef !important;
        }
        .login-btn {
            background: linear-gradient(to right, #007bff, #6610f2);
            border: none;
            padding: 15px;
            font-size: 18px;
            font-weight: 700;
            width: 100%;
            border-radius: 10px;
            transition: all 0.3s ease;
            margin-top: 10px;
            box-shadow: 0 5px 15px rgba(0,123,255,0.3);
            color: white;
        }
        .login-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,123,255,0.4);
            color: white;
        }
        .login-btn:active {
            transform: translateY(-1px);
        }
        .login-btn i {
            margin-right: 10px;
        }
        .demo-credentials {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-top: 25px;
            border-left: 4px solid #007bff;
        }
        .demo-credentials h5 {
            color: #333;
            margin-bottom: 15px;
            font-weight: 700;
            display: flex;
            align-items: center;
        }
        .demo-credentials h5 i {
            margin-right: 10px;
            color: #007bff;
        }
        .demo-credentials ul {
            margin: 0;
            padding-left: 20px;
        }
        .demo-credentials li {
            margin-bottom: 8px;
            color: #495057;
        }
        .demo-credentials strong {
            color: #007bff;
        }
        .login-footer {
            margin-top: 25px;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
        }
        .login-footer a {
            color: #007bff;
            text-decoration: none;
            font-weight: 600;
        }
        .login-footer a:hover {
            text-decoration: underline;
        }
        .alert {
            border-radius: 10px;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            animation: shake 0.5s ease-in-out;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        /* Video background */
        .video-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
            overflow: hidden;
        }
        .video-bg video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .video-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.55);
            z-index: -1;
        }
    </style>
</head>
<body class="hold-transition login-page">
    <div class="video-bg">
        <video autoplay muted loop playsinline>
            <source src="../assets/Business footage diverse team _ Footage in office, meeting room _ Free download stock footage.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </div>
    <div class="video-overlay"></div>

    <div class="login-box">
        <div class="login-logo">
            <h1>STOCK MANAGEMENT</h1>
            <p>Inventory Control System</p>
        </div>
 
        <div class="card login-card">
            <div class="login-header">
                <h2>SECURE LOGIN</h2>
                <p>Access your dashboard</p>
            </div>
            
            <div class="login-tabs">
                <button type="button" class="login-tab <?php echo $login_type == 'super_admin' ? 'active' : ''; ?>" 
                        onclick="switchTab('super_admin')">
                    <i class="fas fa-user-shield"></i> SUPER ADMIN
                </button>
                <button type="button" class="login-tab <?php echo $login_type == 'admin' ? 'active' : ''; ?>" 
                        onclick="switchTab('admin')">
                    <i class="fas fa-user-cog"></i> ADMIN
                </button>
            </div>
            
            <div class="login-body">
                <div class="login-icon">
                    <i class="fas <?php echo $login_type == 'super_admin' ? 'fa-user-shield' : 'fa-user-cog'; ?>"></i>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong><i class="fas fa-exclamation-triangle"></i> Login Failed!</strong>
                        <br><?php echo $error; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" id="loginForm">
                    <input type="hidden" name="user_type" id="user_type" value="<?php echo $login_type; ?>">
                    
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas <?php echo $login_type == 'super_admin' ? 'fa-user-shield' : 'fa-user-cog'; ?>" id="userIcon"></i>
                            </span>
                        </div>
                        <input type="text" class="form-control" name="username" placeholder="Enter Username" required autofocus>
                    </div>
                    
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        </div>
                        <input type="password" class="form-control" name="password" id="password" placeholder="Enter Password" required>
                        <div class="input-group-append">
                            <span class="input-group-text password-toggle" onclick="togglePassword()">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </span>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block login-btn">
                        <i class="fas fa-sign-in-alt"></i> SIGN IN
                    </button>
                </form>
                
                <div class="demo-credentials" id="demoBox">
                    <h5><i class="fas fa-info-circle"></i> Demo Credentials:</h5>
                    <ul>
                        <?php if ($login_type == 'super_admin'): ?>
                            <li><strong>Super Admin:</strong> superadmin / admin123</li>
                        <?php else: ?>
                            <li><strong>Admin:</strong> admin / admin123</li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div class="login-footer">
                    <p class="mb-0">© 2024 Stock Management System. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/plugins/jquery/jquery.min.js"></script>
    <script src="../assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/dist/js/adminlte.min.js"></script>
    
    <script>
        function switchTab(tab) {
            document.getElementById('user_type').value = tab;
            document.querySelectorAll('.login-tab').forEach(t => t.classList.remove('active'));
            event.target.classList.add('active');
            
            const icon = document.querySelector('.login-icon i');
            const userIcon = document.getElementById('userIcon');
            const demoBox = document.getElementById('demoBox');

            if (tab === 'super_admin') {
                icon.className = 'fas fa-user-shield';
                userIcon.className = 'fas fa-user-shield';
                demoBox.innerHTML = `<h5><i class="fas fa-info-circle"></i> Demo Credentials:</h5><ul><li><strong>Super Admin:</strong> superadmin / admin123</li></ul>`;
            } else {
                icon.className = 'fas fa-user-cog';
                userIcon.className = 'fas fa-user-cog';
                demoBox.innerHTML = `<h5><i class="fas fa-info-circle"></i> Demo Credentials:</h5><ul><li><strong>Admin:</strong> admin / admin123</li></ul>`;
            }
        }
        
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
