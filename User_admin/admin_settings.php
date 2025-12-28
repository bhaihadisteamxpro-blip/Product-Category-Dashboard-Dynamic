<?php
// User_admin/admin_settings.php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../frontend/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];
$admin_id_str = $_SESSION['admin_id'];
$department = $_SESSION['department'] ?? 'General';

$success_msg = "";
$error_msg = "";

// Handle Password Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_password_action'])) {
    
    // Debug: Check connection
    if ($conn->connect_error) {
        $error_msg = "Database Connection Failed: " . $conn->connect_error;
    } else {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error_msg = "All fields are required!";
        } elseif ($new_password !== $confirm_password) {
            $error_msg = "New passwords do not match!";
        } else {
            // Verify current password first
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            if (!$stmt) {
                 $error_msg = "Prepare failed: " . $conn->error;
            } else {
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();

                if ($user && password_verify($current_password, $user['password'])) {
                    // Update to new hashed password
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                    if (!$update_stmt) {
                        $error_msg = "Update prepare failed: " . $conn->error;
                    } else {
                        $update_stmt->bind_param("si", $hashed_password, $user_id);
                        
                        if ($update_stmt->execute()) {
                            $success_msg = "Password updated successfully! You can now login with your new password.";
                            // Optional: Update current session hash if you were checking it per request, 
                            // but usually not needed for session persistence unless you regenerate session id.
                        } else {
                            $error_msg = "Error updating password in database: " . $update_stmt->error;
                        }
                    }
                } else {
                    $error_msg = "Current password is incorrect! Please try again.";
                }
            }
        }
    }
}

// Fetch current user data
$query = "SELECT * FROM users WHERE id = $user_id";
$res = $conn->query($query);
$user_data = $res->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Settings | Stock Management</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="../assets/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="../assets/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="../assets/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">
  <style>

    
    /* Password Strength Meter Styles */
    .strength-meter { height: 5px; background-color: #eee; margin-top: 5px; border-radius: 2px; transition: all 0.3s ease; width: 0; }
    .strength-text { font-size: 11px; margin-top: 2px; display: block; font-weight: 600; }
    .match-text { font-size: 12px; margin-top: 5px; display: block; font-weight: 500; }
  </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav"><li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a></li></ul>
    <ul class="navbar-nav ml-auto">
      <li class="nav-item"><a class="nav-link" href="../backend/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
  </nav>

  <?php include 'sidebar.php'; ?>

  <!-- Content -->
  <div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <h1>Account Settings</h1>
        </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        
        <?php if($success_msg): ?>
            <div class="alert alert-success alert-dismissible shadow-sm">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-check"></i> Success!</h5>
                <?php echo $success_msg; ?>
            </div>
        <?php endif; ?>

        <?php if($error_msg): ?>
            <div class="alert alert-danger alert-dismissible shadow-sm">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-ban"></i> Error!</h5>
                <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <div class="row">
          <div class="col-md-5">
            <!-- Profile Info -->
            <div class="card card-primary card-outline shadow">
              <div class="card-header border-0"><h3 class="card-title font-weight-bold">Primary Profile</h3></div>
              <div class="card-body">
                  <div class="text-center mb-4">
                      <div class="user-avatar" style="width: 80px; height: 80px; font-size: 32px; border: 3px solid #f4f4f4;"><?php echo strtoupper(substr($full_name, 0, 1)); ?></div>
                      <h4 class="mt-2 mb-0"><?php echo htmlspecialchars($full_name); ?></h4>
                      <p class="text-muted small"><?php echo $admin_id_str; ?></p>
                  </div>
                  <div class="form-group"><label class="text-muted">Username</label><div class="input-group"><div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-user"></i></span></div><input type="text" class="form-control" value="<?php echo htmlspecialchars($user_data['username']); ?>" readonly></div></div>
                  <div class="form-group"><label class="text-muted">Official Email</label><div class="input-group"><div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-envelope"></i></span></div><input type="email" class="form-control" value="<?php echo htmlspecialchars($user_data['email']); ?>" readonly></div></div>
                  <div class="form-group"><label class="text-muted">Sector / Dept</label><div class="input-group"><div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-building"></i></span></div><input type="text" class="form-control" value="<?php echo ucfirst($department); ?>" readonly></div></div>
              </div>
            </div>
          </div>
          <div class="col-md-7">
            <!-- Security / Password change -->
            <div class="card card-danger card-outline shadow">
              <div class="card-header border-0"><h3 class="card-title font-weight-bold">Security Credentials</h3></div>
              <div class="card-body">
                <form method="POST" action="" id="passwordForm">
                  <input type="hidden" name="update_password_action" value="1">
                  
                  <div class="form-group">
                      <label>Current Access Password</label>
                      <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-key"></i></span></div>
                        <input type="password" name="current_password" class="form-control" placeholder="Enter current password" required>
                      </div>
                  </div>
                  
                  <hr class="my-4">
                  
                  <div class="form-group">
                      <label>New Passphrase</label>
                      <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-lock"></i></span></div>
                        <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Minimum 6 characters" required>
                      </div>
                      <div class="strength-meter" id="strengthMeter"></div>
                      <span id="strengthText" class="strength-text"></span>
                  </div>
                  
                  <div class="form-group mt-3">
                      <label>Confirm New Passphrase</label>
                      <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-check-double"></i></span></div>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Match new passphrase" required>
                      </div>
                      <span id="matchText" class="match-text"></span>
                  </div>
                  
                  <button type="submit" id="submitBtn" class="btn btn-danger btn-block font-weight-bold mt-4 py-2" disabled>
                    UPDATE SECURITY CREDENTIALS
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</div>

<script src="../assets/plugins/jquery/jquery.min.js"></script>
<script src="../assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../assets/dist/js/adminlte.min.js"></script>
<script src="../assets/plugins/sweetalert2/sweetalert2.min.js"></script>

<script>
$(document).ready(function() {
    const newPass = $('#new_password');
    const confirmPass = $('#confirm_password');
    const strengthMeter = $('#strengthMeter');
    const strengthText = $('#strengthText');
    const matchText = $('#matchText');
    const submitBtn = $('#submitBtn');

    // Live Password Strength Check
    newPass.on('input', function() {
        const val = $(this).val();
        let strength = 0;
        
        if (val.length >= 6) strength++;
        if (val.match(/[A-Z]/)) strength++;
        if (val.match(/[0-9]/)) strength++;
        if (val.match(/[!@#$%^&*(),.?":{}|<>]/)) strength++;

        // Visual update
        switch(strength) {
            case 0:
            case 1:
                strengthMeter.css({'width': '25%', 'background-color': '#dc3545'});
                strengthText.text('Weak').css('color', '#dc3545');
                break;
            case 2:
                strengthMeter.css({'width': '50%', 'background-color': '#ffc107'});
                strengthText.text('Moderate').css('color', '#ffc107');
                break;
            case 3:
                strengthMeter.css({'width': '75%', 'background-color': '#28a745'});
                strengthText.text('Strong').css('color', '#28a745');
                break;
            case 4:
                strengthMeter.css({'width': '100%', 'background-color': '#20c997'});
                strengthText.text('Very Secure').css('color', '#20c997');
                break;
        }
        if(val.length === 0) {
            strengthMeter.css('width', '0');
            strengthText.text('');
        }
        checkMatch();
    });

    // Live Password Match Check
    confirmPass.on('input', checkMatch);

    function checkMatch() {
        if (confirmPass.val().length === 0) {
            matchText.text('');
            submitBtn.prop('disabled', true);
            return;
        }

        if (newPass.val() === confirmPass.val()) {
            matchText.text('✓ Passwords Match').css('color', '#28a745');
            if (newPass.val().length >= 1) { 
                submitBtn.prop('disabled', false);
            }
        } else {
            matchText.text('✗ Passwords Do Not Match').css('color', '#dc3545');
            submitBtn.prop('disabled', true);
        }
    }
});
</script>

<?php if($success_msg): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Security Updated!',
        text: '<?php echo $success_msg; ?>',
        confirmButtonColor: '#dc3545'
    });
</script>
<?php endif; ?>

</body>
</html>
