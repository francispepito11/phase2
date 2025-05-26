<!-- Top Navigation Bar -->
<nav class="topbar">
    <button class="menu-toggle me-3" onclick="toggleSidebar()">
        <i class="bi bi-list"></i>
    </button>
    
    <h4 class="mb-0 page-title"><?php echo isset($page_title) ? $page_title : 'Dashboard'; ?></h4>
    
    <div class="ms-auto d-flex align-items-center">
        <!-- Notifications Dropdown -->
        <div class="dropdown me-3">
            <button class="btn btn-link text-decoration-none position-relative" type="button" id="notificationsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-bell fs-5"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    3
                    <span class="visually-hidden">unread notifications</span>
                </span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationsDropdown" style="width: 300px;">
                <li><h6 class="dropdown-header">Notifications</h6></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item d-flex align-items-center py-2" href="#">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-circle">
                                <i class="bi bi-person"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="mb-0 fw-semibold">New user registered</p>
                            <p class="text-muted small mb-0">5 minutes ago</p>
                        </div>
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item d-flex align-items-center py-2" href="#">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 text-success p-2 rounded-circle">
                                <i class="bi bi-check-circle"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="mb-0 fw-semibold">Task completed</p>
                            <p class="text-muted small mb-0">2 hours ago</p>
                        </div>
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item d-flex align-items-center py-2" href="#">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 text-warning p-2 rounded-circle">
                                <i class="bi bi-exclamation-triangle"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="mb-0 fw-semibold">System alert</p>
                            <p class="text-muted small mb-0">1 day ago</p>
                        </div>
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-center small" href="#">View all notifications</a></li>
            </ul>
        </div>
        
        <!-- User Dropdown -->
        <div class="dropdown">
            <button class="btn btn-link text-decoration-none dropdown-toggle d-flex align-items-center" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="me-2 d-none d-md-block text-end">
                    <div class="fw-semibold"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></div>
                    <div class="text-muted small">Administrator</div>
                </div>
                <div class="avatar">
                    <img src="../assets/img/avatar.png" alt="User Avatar" class="rounded-circle" width="40" height="40">
                </div>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                <li><a class="dropdown-item" href="settings.php"><i class="bi bi-gear me-2"></i>Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
        </div>
    </div>
</nav>
