<?php
// Get current page for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar Component -->
<div class="d-flex flex-column flex-shrink-0 sidebar" id="sidebar-wrapper">
    <div class="sidebar-header p-3 text-center">
        <img src="../images/dict-logo.png" alt  ="DICT Logo" class="img-fluid rounded-circle mb-2" style="width: 70px; height: 70px; border: 2px solid rgba(255,255,255,0.2); box-shadow: 0 2px 10px rgba(0,0,0,0.2);">
        <div class="sidebar-title">
            <h5 class="text-white mb-0 fw-bold">ILCDB</h5>
            <p class="text-white-50 small">Admin Dashboard</p>
        </div>
    </div>
    
    <hr class="sidebar-divider my-0 bg-light opacity-25">
    
    <ul class="nav nav-pills flex-column mb-auto px-2 py-2">
        <li class="nav-item">
            <a href="dashboard.php" class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2 me-2"></i>
                <span>Dashboard</span>
            </a>
        </li>
        
        <!-- Tech Supports Dropdown -->
        <li class="nav-item">
            <a class="nav-link dropdown-toggle <?php echo in_array($current_page, ['service-requests.php', 'support-summary.php', 'foot-tracking.php']) ? 'active' : ''; ?>" href="#" role="button" data-bs-toggle="collapse" data-bs-target="#techSupportsSubmenu" aria-expanded="<?php echo in_array($current_page, ['service-requests.php', 'support-summary.php', 'foot-tracking.php']) ? 'true' : 'false'; ?>">
                <i class="bi bi-headset me-2"></i>
                <span>Tech Supports</span>
            </a>
            <div class="collapse <?php echo in_array($current_page, ['service-requests.php', 'support-summary.php', 'foot-tracking.php']) ? 'show' : ''; ?>" id="techSupportsSubmenu">
                <ul class="nav nav-pills flex-column sidebar-submenu">
                    <li class="nav-item">
                        <a href="service-requests.php" class="nav-link <?php echo ($current_page == 'service-requests.php') ? 'active' : ''; ?>">
                            <i class="bi bi-list-check me-2"></i>
                            <span>All Requests</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="support-summary.php" class="nav-link <?php echo ($current_page == 'support-summary.php') ? 'active' : ''; ?>">
                            <i class="bi bi-pie-chart me-2"></i>
                            <span>Summary</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="foot-tracking.php" class="nav-link <?php echo ($current_page == 'foot-tracking.php') ? 'active' : ''; ?>">
                            <i class="bi bi-people me-2"></i>
                            <span>Foot Tracking</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>
        
        <li class="nav-item">
            <a href="reports.php" class="nav-link <?php echo ($current_page == 'reports.php') ? 'active' : ''; ?>">
                <i class="bi bi-file-earmark-text me-2"></i>
                <span>Reports</span>
            </a>
        </li>
        
        <li class="nav-item">
            <a href="users.php" class="nav-link <?php echo ($current_page == 'users.php') ? 'active' : ''; ?>">
                <i class="bi bi-people me-2"></i>
                <span>Users</span>
            </a>
        </li>
        
        <li class="nav-item">
            <a href="settings.php" class="nav-link <?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>">
                <i class="bi bi-gear me-2"></i>
                <span>Settings</span>
            </a>
        </li>
    </ul>
    
    <hr class="sidebar-divider bg-light opacity-25">
    
    <div class="sidebar-footer p-3">
        <a href="logout.php" class="nav-link text-white-50 d-flex align-items-center">
            <i class="bi bi-box-arrow-right me-2"></i>
            <span>Logout</span>
        </a>
    </div>
</div>

<!-- Mobile Overlay -->
<div class="mobile-overlay" id="mobileOverlay"></div>

<!-- Topbar -->
<nav class="navbar navbar-expand navbar-light bg-white topbar" id="topbar">
    <div class="container-fluid">
        <button class="btn btn-link rounded-circle me-3" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        
        <h4 class="mb-0 text-gray-800"><?php echo isset($page_title) ? $page_title : 'Dashboard'; ?></h4>
        
        <ul class="navbar-nav ms-auto">
            <!-- Notifications Dropdown -->
            <li class="nav-item dropdown no-arrow mx-1">
                <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-bell fs-5"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        3+
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-end shadow animated--grow-in" aria-labelledby="alertsDropdown" style="min-width: 300px;">
                    <h6 class="dropdown-header">Notifications Center</h6>
                    <a class="dropdown-item d-flex align-items-center py-2" href="#">
                        <div class="me-3">
                            <div class="icon-circle bg-primary">
                                <i class="bi bi-person-plus text-white"></i>
                            </div>
                        </div>
                        <div>
                            <div class="small text-gray-500">December 12, 2023</div>
                            <span>A new user has registered</span>
                        </div>
                    </a>
                    <a class="dropdown-item d-flex align-items-center py-2" href="#">
                        <div class="me-3">
                            <div class="icon-circle bg-success">
                                <i class="bi bi-check-circle text-white"></i>
                            </div>
                        </div>
                        <div>
                            <div class="small text-gray-500">December 7, 2023</div>
                            <span>Task completed successfully</span>
                        </div>
                    </a>
                    <a class="dropdown-item d-flex align-items-center py-2" href="#">
                        <div class="me-3">
                            <div class="icon-circle bg-warning">
                                <i class="bi bi-exclamation-triangle text-white"></i>
                            </div>
                        </div>
                        <div>
                            <div class="small text-gray-500">December 2, 2023</div>
                            <span>System warning: Low disk space</span>
                        </div>
                    </a>
                    <a class="dropdown-item text-center small text-gray-500" href="#">Show All Alerts</a>
                </div>
            </li>
            
            <!-- User Information -->
            <li class="nav-item dropdown no-arrow">
                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="me-2 d-none d-lg-inline text-gray-600 small"><?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin'; ?></span>
                    <img class="img-profile rounded-circle" src="../assets/img/avatar.png" width="32" height="32">
                </a>
                <div class="dropdown-menu dropdown-menu-end shadow animated--grow-in" aria-labelledby="userDropdown">
                    <a class="dropdown-item" href="profile.php">
                        <i class="bi bi-person me-2 text-gray-400"></i>
                        Profile
                    </a>
                    <a class="dropdown-item" href="settings.php">
                        <i class="bi bi-gear me-2 text-gray-400"></i>
                        Settings
                    </a>
                    <a class="dropdown-item" href="activity-log.php">
                        <i class="bi bi-list me-2 text-gray-400"></i>
                        Activity Log
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="logout.php">
                        <i class="bi bi-box-arrow-right me-2 text-gray-400"></i>
                        Logout
                    </a>
                </div>
            </li>
        </ul>
    </div>
</nav>

<!-- Main Content Wrapper -->
<div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
        <!-- Page content goes here -->

<style>
:root {
    --sidebar-width: 250px;
    --sidebar-collapsed-width: 70px;
    --sidebar-bg: #1e3a8a;
    --sidebar-color: #fff;
    --topbar-height: 60px;
}

/* Sidebar Styles */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: var(--sidebar-width);
    height: 100vh;
    background: var(--sidebar-bg);
    color: var(--sidebar-color);
    z-index: 1040;
    transition: all 0.3s ease;
    overflow-y: auto;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
}

.sidebar.toggled {
    width: var(--sidebar-collapsed-width);
}

.sidebar-header {
    transition: all 0.3s ease;
}

.sidebar.toggled .sidebar-title,
.sidebar.toggled .nav-link span {
    display: none;
}

.sidebar .nav-link {
    color: rgba(255, 255, 255, 0.8);
    border-radius: 5px;
    margin: 2px 0;
    transition: all 0.2s ease;
}

.sidebar .nav-link:hover {
    color: #fff;
    background-color: rgba(255, 255, 255, 0.1);
}

.sidebar .nav-link.active {
    color: #fff;
    background-color: rgba(255, 255, 255, 0.2);
    font-weight: 600;
}

.sidebar .nav-link i {
    width: 24px;
    text-align: center;
}

.sidebar-submenu {
    padding-left: 1rem;
}

.sidebar-submenu .nav-link {
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
    font-size: 0.9rem;
}

.sidebar-footer {
    margin-top: auto;
}

/* Topbar Styles */
.topbar {
    position: fixed;
    top: 0;
    right: 0;
    left: var(--sidebar-width);
    height: var(--topbar-height);
    z-index: 1030;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.topbar.toggled {
    left: var(--sidebar-collapsed-width);
}

/* Content Wrapper */
#content-wrapper {
    margin-left: var(--sidebar-width);
    margin-top: var(--topbar-height);
    min-height: calc(100vh - var(--topbar-height));
    transition: all 0.3s ease;
}

#content-wrapper.toggled {
    margin-left: var(--sidebar-collapsed-width);
}

/* Icon Circles */
.icon-circle {
    height: 2.5rem;
    width: 2.5rem;
    border-radius: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Mobile Overlay */
.mobile-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1035;
}

.mobile-overlay.show {
    display: block;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
    }
    
    .sidebar.toggled {
        transform: translateX(0);
        width: var(--sidebar-width);
    }
    
    .sidebar.toggled .sidebar-title,
    .sidebar.toggled .nav-link span {
        display: inline-block;
    }
    
    .topbar {
        left: 0;
    }
    
    .topbar.toggled {
        left: 0;
    }
    
    #content-wrapper {
        margin-left: 0;
    }
    
    #content-wrapper.toggled {
        margin-left: 0;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    const topbar = document.querySelector('.topbar');
    const contentWrapper = document.getElementById('content-wrapper');
    const mobileOverlay = document.getElementById('mobileOverlay');
    
    // Check for saved sidebar state
    const sidebarState = localStorage.getItem('sidebarState');
    if (sidebarState === 'toggled') {
        sidebar.classList.add('toggled');
        topbar.classList.add('toggled');
        contentWrapper.classList.add('toggled');
    }
    
    // Toggle sidebar
    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('toggled');
        topbar.classList.toggle('toggled');
        contentWrapper.classList.toggle('toggled');
        
        if (window.innerWidth <= 768) {
            mobileOverlay.classList.toggle('show');
        }
        
        // Save sidebar state
        if (sidebar.classList.contains('toggled')) {
            localStorage.setItem('sidebarState', 'toggled');
        } else {
            localStorage.setItem('sidebarState', 'expanded');
        }
    });
    
    // Close sidebar when clicking on mobile overlay
    mobileOverlay.addEventListener('click', function() {
        sidebar.classList.remove('toggled');
        mobileOverlay.classList.remove('show');
        localStorage.setItem('sidebarState', 'expanded');
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            mobileOverlay.classList.remove('show');
        }
    });
});
</script>
