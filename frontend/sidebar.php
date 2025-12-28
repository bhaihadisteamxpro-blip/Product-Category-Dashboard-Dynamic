<?php
// frontend/sidebar.php
$full_name = $_SESSION['full_name'];
$admin_id = $_SESSION['admin_id'];
?>
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="dashboard.php" class="brand-link">
      <span class="brand-text font-weight-light" style="font-weight: bold; font-family: times; color: white; text-align: center; margin-left: 26px; font-size: 25px;">
        SUPER ADMIN
      </span>
    </a>
    <div class="sidebar">
      <div class="user-info-sidebar">
        <div class="user-avatar"><?php echo strtoupper(substr($full_name, 0, 1)); ?></div>
        <div class="user-name"><?php echo $full_name; ?></div>
        <div class="user-role">SUPER ADMIN</div>
      </div>
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <li class="nav-item">
            <a href="dashboard.php" class="nav-link">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </a>
          </li>
          <li class="nav-item menu-open">
            <a href="#" class="nav-link active">
              <i class="nav-icon fa fa-archive"></i>
              <p>Categories <i class="fas fa-angle-left right"></i></p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="add_category.php" class="nav-link">
                  <i class="fa fa-plus nav-icon"></i>
                  <p>Add Category</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="manage_categories.php" class="nav-link active">
                  <i class="fa fa-cog nav-icon"></i>
                  <p>Manage Categories</p>
                </a>
              </li>
            </ul> 
          </li>
          <li class="nav-item">
            <a href="../backend/logout.php" class="nav-link">
              <i class="nav-icon fa fa-sign-out-alt"></i>
              <p>Logout</p>
            </a>
          </li>
        </ul>
      </nav>
    </div>
</aside>
