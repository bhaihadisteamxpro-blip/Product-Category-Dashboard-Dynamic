<?php
// User_admin/sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);

// Ensure we have user info, falling back to session if variables aren't set in the parent script
$sidebar_full_name = isset($full_name) ? $full_name : ($_SESSION['full_name'] ?? 'Admin');
$sidebar_admin_id = isset($admin_id) ? $admin_id : ($_SESSION['admin_id'] ?? 'ID-XXXX');
$sidebar_user_initial = strtoupper(substr($sidebar_full_name, 0, 1));
$sidebar_role = strtoupper($_SESSION['department'] ?? 'ADMIN');
?>
<style>
  /* Sidebar Custom Styles */
  .main-sidebar {
    background: #1a1f26 !important; /* Deeper dark background */
    box-shadow: 4px 0 15px rgba(0,0,0,0.2) !important;
  }
  
  .brand-link {
    background: linear-gradient(45deg, #007bff, #00d2ff) !important;
    border-bottom: none !important;
    display: flex !important;
    align-items: center;
    justify-content: center;
    padding: 15px 10px !important;
  }

  .brand-text {
    font-family: 'Source Sans Pro', sans-serif !important;
    font-weight: 700 !important;
    font-size: 1.2rem !important;
    letter-spacing: 1px;
    text-shadow: 0 1px 2px rgba(0,0,0,0.2);
    margin-left: 0 !important;
  }

  .sidebar {
    padding-top: 10px;
  }

  /* User Info Panel */
  .user-info-sidebar {
    text-align: center;
    padding: 20px 15px;
    background: rgba(255,255,255,0.05); /* Glass-like effect */
    border-radius: 12px;
    margin: 15px 10px 25px 10px;
    border: 1px solid rgba(255,255,255,0.05);
    backdrop-filter: blur(5px);
  }

  .user-avatar {
    width: 65px;
    height: 65px;
    background: linear-gradient(135deg, #dc3545, #ff6b6b);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    font-weight: bold;
    margin: 0 auto 12px;
    box-shadow: 0 4px 10px rgba(220, 53, 69, 0.4);
    border: 2px solid rgba(255,255,255,0.2);
  }

  .user-name {
    font-size: 16px;
    font-weight: 600;
    color: #fff;
    margin-bottom: 6px;
    letter-spacing: 0.5px;
  }

  .user-role-badge {
    display: inline-block;
    padding: 4px 12px;
    background: rgba(220, 53, 69, 0.2);
    color: #ff6b6b;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    border: 1px solid rgba(220, 53, 69, 0.3);
  }

  .user-id-text {
    color: #8a9ab0;
    font-size: 11px;
    margin-top: 8px;
    font-family: monospace;
  }

  /* Sidebar Navigation */
  .nav-sidebar .nav-item {
    margin-bottom: 4px;
  }

  .nav-sidebar .nav-link {
    border-radius: 8px !important;
    color: #c2c7d0 !important;
    padding: 10px 15px;
    transition: all 0.2s ease;
  }

  .nav-sidebar .nav-link:hover {
    background-color: rgba(255,255,255,0.05) !important;
    color: #fff !important;
    transform: translateX(4px);
  }

  .nav-sidebar .nav-link.active {
    background: linear-gradient(90deg, #007bff, #0056b3) !important;
    color: #fff !important;
    box-shadow: 0 4px 10px rgba(0, 123, 255, 0.3);
    font-weight: 600;
    transform: translateX(0);
  }

  .nav-sidebar .nav-icon {
    margin-right: 12px;
    font-size: 1.1rem;
    width: 20px;
    text-align: center;
    opacity: 0.8;
  }
  
  .nav-item.menu-open > .nav-link {
    background-color: rgba(255,255,255,0.03);
    color: #fff !important;
  }

  .nav-treeview {
    padding-left: 15px;
    background: transparent;
  }
  
  .nav-treeview .nav-link {
    font-size: 0.9rem;
    padding: 8px 15px;
  }

  .nav-treeview .nav-icon {
    font-size: 0.8rem;
  }
</style>

<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <!-- Brand Logo -->
  <a href="Admin.php" class="brand-link">
    <i class="fas fa-user-tie mr-2" style="font-size: 20px; color: white;"></i>
    <span class="brand-text">ADMIN PANEL</span>
  </a>

  <!-- Sidebar -->
  <div class="sidebar">
    <!-- Sidebar user panel -->
    <div class="user-info-sidebar">
      <div class="user-avatar">
        <?php echo $sidebar_user_initial; ?>
      </div>
      <div class="user-name"><?php echo htmlspecialchars($sidebar_full_name); ?></div>
      <div class="user-role-badge"><?php echo $sidebar_role; ?> ADMIN</div>
      <div class="user-id-text">ID: <?php echo $sidebar_admin_id; ?></div>
    </div>
    
    <!-- Sidebar Menu -->
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        
        <!-- Dashboard -->
        <li class="nav-item">
          <a href="Admin.php" class="nav-link <?php echo ($current_page == 'Admin.php') ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
          </a>
        </li>
        
        <!-- Categories -->
        <li class="nav-item <?php echo in_array($current_page, ['view_assigned_categories.php', 'edit_assigned_categories.php']) ? 'menu-open' : ''; ?>">
          <a href="#" class="nav-link <?php echo in_array($current_page, ['view_assigned_categories.php', 'edit_assigned_categories.php']) ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-tags"></i>
            <p>
              Categories
              <i class="fas fa-angle-left right"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="view_assigned_categories.php" class="nav-link <?php echo ($current_page == 'view_assigned_categories.php') ? 'active' : ''; ?>">
                <i class="fas fa-eye nav-icon"></i>
                <p>View Assigned</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="edit_assigned_categories.php" class="nav-link <?php echo ($current_page == 'edit_assigned_categories.php') ? 'active' : ''; ?>">
                <i class="fas fa-edit nav-icon"></i>
                <p>Edit Assigned</p>
              </a>
            </li>
          </ul> 
        </li>

        <!-- Settings -->
        <li class="nav-header">SETTINGS</li>
        <li class="nav-item">
          <a href="admin_settings.php" class="nav-link <?php echo ($current_page == 'admin_settings.php') ? 'active' : ''; ?>">
            <i class="nav-icon fas fa-cog"></i>
            <p>Settings</p>
          </a>
        </li>
        
        <!-- Logout -->
        <li class="nav-item">
          <a href="../backend/logout.php" class="nav-link text-danger">
            <i class="nav-icon fas fa-sign-out-alt"></i>
            <p>Logout</p>
          </a>
        </li>

      </ul>
    </nav>
  </div>
</aside>
