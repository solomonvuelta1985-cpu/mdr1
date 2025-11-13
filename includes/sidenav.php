<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MDRRM-ARMS - Municipal Disaster Risk Reduction and Management – Annex Reporting and Monitoring System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fb;
        }

        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #0dcaf0;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 70px;
        }

        /* Sidebar Styles */
        #sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            position: fixed;
            height: 100vh;
            transition: all 0.3s;
            z-index: 1000;
            box-shadow: 3px 0 10px rgba(0, 0, 0, 0.1);
            top: 0;
            left: 0;
        }

        #sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        #sidebar.collapsed .sidebar-header h3,
        #sidebar.collapsed .nav-link span,
        #sidebar.collapsed .sidebar-header p {
            display: none;
        }

        #sidebar.collapsed .nav-link {
            justify-content: center;
            padding: 15px 10px;
        }

        #sidebar.collapsed .nav-link i {
            margin-right: 0;
            font-size: 1.2rem;
        }

        #sidebar.collapsed .dropdown-toggle::after {
            display: none;
        }

        .sidebar-header {
            padding: 20px;
            background: rgba(0, 0, 0, 0.2);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header h3 {
            margin: 0;
            font-weight: 600;
            font-size: 1.3rem;
        }

        .sidebar-header p {
            margin: 5px 0 0;
            font-size: 0.85rem;
            opacity: 0.8;
        }

        .sidebar-content {
            padding: 20px 0;
            height: calc(100vh - 80px);
            overflow-y: auto;
        }

        #sidebar.collapsed .sidebar-content {
            overflow-y: visible;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            border-left: 3px solid transparent;
            transition: all 0.2s;
            display: flex;
            align-items: center;
        }

        .nav-link:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            border-left: 3px solid var(--info-color);
        }

        .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.15);
            border-left: 3px solid var(--warning-color);
        }

        .nav-link i {
            margin-right: 10px;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        .dropdown-menu {
            background: rgba(30, 60, 114, 0.95);
            border: none;
            border-radius: 0;
            margin: 0;
            padding: 0;
        }

        .dropdown-item {
            color: rgba(255, 255, 255, 0.8);
            padding: 10px 20px 10px 40px;
            border-left: 3px solid transparent;
        }

        .dropdown-item:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            border-left: 3px solid var(--info-color);
        }

        .dropdown-item.active {
            color: white;
            background: rgba(255, 255, 255, 0.15);
            border-left: 3px solid var(--warning-color);
        }

        .dropdown-toggle::after {
            margin-left: auto;
        }

        /* Main Content Styles */
        #content {
            margin-left: var(--sidebar-width);
            transition: all 0.3s;
            min-height: 100vh;
            background-color: #f5f7fb;
        }

        #content.expanded {
            margin-left: var(--sidebar-collapsed-width);
        }

        .top-navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 15px 30px;
            position: sticky;
            top: 0;
            z-index: 100;
            margin: 0;
        }

        #sidebarCollapse {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--primary-color);
            cursor: pointer;
            transition: all 0.3s;
            padding: 0;
            margin: 0;
        }

        #sidebarCollapse:hover {
            color: var(--info-color);
            transform: rotate(90deg);
        }

        .main-content-container {
            margin: 0;
            padding: 0;
        }

        /* Page Header Styles */
        .page-header {
            padding: 30px;
            background: white;
            margin: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .page-header h1 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 10px;
        }

        .page-header p {
            color: var(--secondary-color);
            font-size: 1.1rem;
            margin-bottom: 0;
        }

        .dashboard-cards {
            padding: 0 20px 20px;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            margin-bottom: 20px;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .card-body {
            padding: 25px;
        }

        .card-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            opacity: 0.8;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .card-text {
            color: var(--secondary-color);
            font-size: 0.9rem;
            margin-bottom: 15px;
        }

        .card-link {
            color: var(--primary-color);
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .card-link i {
            margin-left: 5px;
            transition: transform 0.3s;
        }

        .card-link:hover i {
            transform: translateX(5px);
        }

        /* Mobile Overlay Styles */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .sidebar-overlay.active {
            display: block;
        }

        /* Mobile Header Styles */
        .mobile-header {
            display: none;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 15px 20px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .mobile-header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .mobile-header h4 {
            margin: 0;
            font-size: 1.2rem;
        }

        .mobile-header p {
            margin: 5px 0 0;
            font-size: 0.8rem;
            opacity: 0.8;
        }

        #mobileSidebarToggle {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            #sidebar {
                transform: translateX(-100%);
                width: 280px;
            }
            
            #sidebar.active {
                transform: translateX(0);
            }
            
            #content {
                margin-left: 0;
            }
            
            #content.expanded {
                margin-left: 0;
            }
            
            .top-navbar {
                display: none;
            }
            
            .mobile-header {
                display: block;
            }
            
            .page-header {
                margin: 10px;
                padding: 20px;
            }
            
            .dashboard-cards {
                padding: 0 10px 20px;
            }
            
            /* Disable tooltips on mobile */
            .tooltip {
                display: none !important;
            }

            /* Prevent body scroll when sidebar is open */
            body.sidebar-open {
                overflow: hidden;
            }

            /* Disable floating submenus on mobile */
            #sidebar.collapsed .nav-item:hover > ul.collapse {
                display: none !important;
            }
        }

        /* Scrollbar Styling */
        .sidebar-content::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar-content::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar-content::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 10px;
        }

        .sidebar-content::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }

        /* Animation for sidebar items */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateX(-10px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .nav-item {
            animation: fadeIn 0.5s ease forwards;
        }

        .nav-item:nth-child(1) { animation-delay: 0.1s; }
        .nav-item:nth-child(2) { animation-delay: 0.2s; }
        .nav-item:nth-child(3) { animation-delay: 0.3s; }
        .nav-item:nth-child(4) { animation-delay: 0.4s; }
        .nav-item:nth-child(5) { animation-delay: 0.5s; }
        .nav-item:nth-child(6) { animation-delay: 0.6s; }
        .nav-item:nth-child(7) { animation-delay: 0.7s; }
        .nav-item:nth-child(8) { animation-delay: 0.8s; }

        /* Extended Styles for Collapsed Sidebar with Floating Popouts */
        #sidebar.collapsed .nav-item {
            position: relative;
        }

        #sidebar.collapsed ul.collapse {
            position: absolute;
            left: var(--sidebar-collapsed-width);
            top: 0;
            width: max-content;
            min-width: 280px;
            background: white;
            color: #1e3c72;
            border-radius: 0 10px 10px 0;
            box-shadow: 5px 0 15px rgba(0, 0, 0, 0.3);
            display: none !important;
            z-index: 1001;
            padding: 10px 0;
            border-left: 1px solid rgba(0, 0, 0, 0.1);
        }

        #sidebar.collapsed .nav-item:hover > ul.collapse {
            display: block !important;
        }

        #sidebar.collapsed ul.collapse .dropdown-item {
            padding: 10px 20px;
            border-left: none;
            display: flex;
            align-items: center;
            white-space: nowrap;
            color: #1e3c72;
        }

        #sidebar.collapsed ul.collapse .dropdown-item + .dropdown-item {
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }

        #sidebar.collapsed ul.collapse .dropdown-item i {
            margin-right: 10px;
            width: auto;
            color: #1e3c72;
        }

        #sidebar.collapsed ul.collapse .dropdown-item:hover {
            background: rgba(30, 60, 114, 0.1);
            color: #0d6efd;
        }

        #sidebar.collapsed ul.collapse .dropdown-item.active {
            color: #0d6efd;
            background: rgba(30, 60, 114, 0.15);
            border-left: 3px solid var(--warning-color);
        }

        /* Font smoothing for clear text rendering in dropdown items */
        .dropdown-item, 
        .dropdown-item span {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
            font-weight: 500;
        }
    </style>
</head>
<body style="margin: 0; padding: 0;">
    <?php
        // Load annex access control functions
        require_once __DIR__ . '/check_annex_access.php';

        // Get current page for active states
        $currentPage = basename($_SERVER['PHP_SELF']);

        // Define page groups for each dropdown
        $incidentPages = ['annex1.php', 'annex2.php', 'annex3.php', 'annex1_records.php'];
        $damagePages = ['annex4.php', 'annex5.php', 'annex6.php', 'annex7.php'];
        $statusPages = ['annex8.php', 'annex9.php', 'annex11.php', 'annex14.php', 'annex15.php'];
        $evacuationPages = ['annex17.php', 'annex18.php'];
        $assistancePages = ['annex19.php', 'annex20.php', 'annex21.php'];

        // Check which section is active
        $incidentActive = in_array($currentPage, $incidentPages);
        $damageActive = in_array($currentPage, $damagePages);
        $statusActive = in_array($currentPage, $statusPages);
        $evacuationActive = in_array($currentPage, $evacuationPages);
        $assistanceActive = in_array($currentPage, $assistancePages);
    ?>

    <!-- Mobile Header -->
    <div class="mobile-header">
        <div class="mobile-header-content">
            <div>
                <h4>MDRRM-ARMS</h4>
                <p>Memorandum Circular No. 05, s. 2025</p>
            </div>
            <button type="button" id="mobileSidebarToggle" class="btn">
                <i class="bi bi-list"></i>
            </button>
        </div>
    </div>

    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar Navigation -->
    <nav id="sidebar">
        <div class="sidebar-header">
            <h3>MDRRM-ARMS</h3>
            <p>Memorandum Circular No. 05, s. 2025</p>
        </div>

        <div class="sidebar-content">
            <ul class="nav flex-column">
                <!-- Dashboard Link -->
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <!-- Records Management Link -->
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'records.php' ? 'active' : ''; ?>" href="records.php">
                        <i class="bi bi-folder2-open"></i>
                        <span>Records Management</span>
                    </a>
                </li>

                <!-- Incident Reports Dropdown -->
                <li class="nav-item">
                    <a class="nav-link dropdown-toggle <?php echo $incidentActive ? 'active' : ''; ?>" 
                       href="#incidentsSubmenu" 
                       data-bs-toggle="collapse" 
                       aria-expanded="<?php echo $incidentActive ? 'true' : 'false'; ?>">
                        <i class="bi bi-exclamation-triangle"></i>
                        <span>Incident Reports</span>
                    </a>
                    <ul class="collapse list-unstyled <?php echo $incidentActive ? 'show' : ''; ?>" id="incidentsSubmenu">
                        <li>
                            <a class="dropdown-item <?php echo $currentPage == 'annex1.php' ? 'active' : ''; ?>" href="annex1.php">
                                <span>Annex 1: Related Incident</span>
                            </a>
                        </li>
                        <div class="dropdown-divider"></div>
                        <li>
                            <a class="dropdown-item <?php echo $currentPage == 'annex2.php' ? 'active' : ''; ?>" href="annex2.php">
                                <span>Annex 2: Affected Population</span>
                            </a>
                        </li>
                        <div class="dropdown-divider"></div>
                        <li>
                            <a class="dropdown-item <?php echo $currentPage == 'annex3.php' ? 'active' : ''; ?>" href="annex3.php">
                                <span>Annex 3: Casualties</span>
                            </a>
                        </li>
                        <div class="dropdown-divider"></div>
                        <li>
                            <a class="dropdown-item <?php echo $currentPage == 'annex1_records.php' ? 'active' : ''; ?>" href="annex1_records.php">
                                <span>Annex 1 Records</span>
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Damage Assessment Dropdown -->
                <li class="nav-item">
                    <a class="nav-link dropdown-toggle <?php echo $damageActive ? 'active' : ''; ?>" 
                       href="#damageSubmenu" 
                       data-bs-toggle="collapse" 
                       aria-expanded="<?php echo $damageActive ? 'true' : 'false'; ?>">
                        <i class="bi bi-house"></i>
                        <span>Damage Assessment</span>
                    </a>
                    <ul class="collapse list-unstyled <?php echo $damageActive ? 'show' : ''; ?>" id="damageSubmenu">
                        <li>
                            <a class="dropdown-item <?php echo $currentPage == 'annex4.php' ? 'active' : ''; ?>" href="annex4.php">
                                <span>Annex 4: Damaged Houses</span>
                            </a>
                        </li>
                        <div class="dropdown-divider"></div>
                        <li>
                            <a class="dropdown-item <?php echo $currentPage == 'annex5.php' ? 'active' : ''; ?>" href="annex5.php">
                                <span>Annex 5: Agriculture</span>
                            </a>
                        </li>
                        <div class="dropdown-divider"></div>
                        <li>
                            <a class="dropdown-item <?php echo $currentPage == 'annex6.php' ? 'active' : ''; ?>" href="annex6.php">
                                <span>Annex 6: Infrastructure</span>
                            </a>
                        </li>
                        <div class="dropdown-divider"></div>
                        <li>
                            <a class="dropdown-item <?php echo $currentPage == 'annex7.php' ? 'active' : ''; ?>" href="annex7.php">
                                <span>Annex 7: Other Assets</span>
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Status Reports Dropdown -->
                <li class="nav-item">
                    <a class="nav-link dropdown-toggle <?php echo $statusActive ? 'active' : ''; ?>" 
                       href="#statusSubmenu" 
                       data-bs-toggle="collapse" 
                       aria-expanded="<?php echo $statusActive ? 'true' : 'false'; ?>">
                        <i class="bi bi-clipboard-check"></i>
                        <span>Status Reports</span>
                    </a>
                    <ul class="collapse list-unstyled <?php echo $statusActive ? 'show' : ''; ?>" id="statusSubmenu">
                        <li>
                            <a class="dropdown-item <?php echo $currentPage == 'annex8.php' ? 'active' : ''; ?>" href="annex8.php">
                                <span>Annex 8: Roads & Bridges</span>
                            </a>
                        </li>
                        <div class="dropdown-divider"></div>
                        <li>
                            <a class="dropdown-item <?php echo $currentPage == 'annex9.php' ? 'active' : ''; ?>" href="annex9.php">
                                <span>Annex 9: Power Supply</span>
                            </a>
                        </li>
                        <div class="dropdown-divider"></div>
                        <li>
                            <a class="dropdown-item <?php echo $currentPage == 'annex11.php' ? 'active' : ''; ?>" href="annex11.php">
                                <span>Annex 11: Communication</span>
                            </a>
                        </li>
                        <?php if (can_access_annex('14')): ?>
                        <div class="dropdown-divider"></div>
                        <li>
                            <a class="dropdown-item <?php echo $currentPage == 'annex14.php' ? 'active' : ''; ?>" href="annex14.php">
                                <span>Annex 14: Work Suspension</span>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (can_access_annex('15')): ?>
                        <?php if (can_access_annex('14')): ?>
                        <div class="dropdown-divider"></div>
                        <?php endif; ?>
                        <li>
                            <a class="dropdown-item <?php echo $currentPage == 'annex15.php' ? 'active' : ''; ?>" href="annex15.php">
                                <span>Annex 15: Class Suspension</span>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>

                <!-- Evacuation Reports Dropdown -->
                <li class="nav-item">
                    <a class="nav-link dropdown-toggle <?php echo $evacuationActive ? 'active' : ''; ?>"
                       href="#evacuationSubmenu"
                       data-bs-toggle="collapse"
                       aria-expanded="<?php echo $evacuationActive ? 'true' : 'false'; ?>">
                        <i class="bi bi-people-fill"></i>
                        <span>Evacuation Reports</span>
                    </a>
                    <ul class="collapse list-unstyled <?php echo $evacuationActive ? 'show' : ''; ?>" id="evacuationSubmenu">
                        <li>
                            <a class="dropdown-item <?php echo $currentPage == 'annex17.php' ? 'active' : ''; ?>" href="annex17.php">
                                <span>Annex 17: Evacuation (People)</span>
                            </a>
                        </li>
                        <div class="dropdown-divider"></div>
                        <li>
                            <a class="dropdown-item <?php echo $currentPage == 'annex18.php' ? 'active' : ''; ?>" href="annex18.php">
                                <span>Annex 18: Evacuation (Animals)</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Assistance Reports Dropdown -->
                <?php if (can_access_annex('19') || can_access_annex('20') || can_access_annex('21')): ?>
                <li class="nav-item">
                    <a class="nav-link dropdown-toggle <?php echo $assistanceActive ? 'active' : ''; ?>"
                       href="#assistanceSubmenu"
                       data-bs-toggle="collapse"
                       aria-expanded="<?php echo $assistanceActive ? 'true' : 'false'; ?>">
                        <i class="bi bi-heart"></i>
                        <span>Assistance Reports</span>
                    </a>
                    <ul class="collapse list-unstyled <?php echo $assistanceActive ? 'show' : ''; ?>" id="assistanceSubmenu">
                        <?php if (can_access_annex('19')): ?>
                        <li>
                            <a class="dropdown-item <?php echo $currentPage == 'annex19.php' ? 'active' : ''; ?>" href="annex19.php">
                                <span>Annex 19: Families Assisted</span>
                            </a>
                        </li>
                        <?php if (can_access_annex('20') || can_access_annex('21')): ?>
                        <div class="dropdown-divider"></div>
                        <?php endif; ?>
                        <?php endif; ?>
                        <?php if (can_access_annex('20')): ?>
                        <li>
                            <a class="dropdown-item <?php echo $currentPage == 'annex20.php' ? 'active' : ''; ?>" href="annex20.php">
                                <span>Annex 20: To Families</span>
                            </a>
                        </li>
                        <?php if (can_access_annex('21')): ?>
                        <div class="dropdown-divider"></div>
                        <?php endif; ?>
                        <?php endif; ?>
                        <?php if (can_access_annex('21')): ?>
                        <li>
                            <a class="dropdown-item <?php echo $currentPage == 'annex21.php' ? 'active' : ''; ?>" href="annex21.php">
                                <span>Annex 21: To LGUs/Agencies</span>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>
                
                <!-- Settings Dropdown (Admin Only) -->
                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link dropdown-toggle <?php echo in_array($currentPage, ['settings.php', 'sitrep_settings.php', 'user_management.php']) ? 'active' : ''; ?>"
                       href="#settingsSubmenu"
                       data-bs-toggle="collapse"
                       aria-expanded="<?php echo in_array($currentPage, ['settings.php', 'sitrep_settings.php', 'user_management.php']) ? 'true' : 'false'; ?>">
                        <i class="bi bi-gear"></i>
                        <span>Settings</span>
                    </a>
                    <ul class="collapse list-unstyled <?php echo in_array($currentPage, ['settings.php', 'sitrep_settings.php', 'user_management.php']) ? 'show' : ''; ?>" id="settingsSubmenu">
                        <li>
                            <a class="dropdown-item <?php echo $currentPage == 'sitrep_settings.php' ? 'active' : ''; ?>" href="sitrep_settings.php">
                                <span>SITREP Settings</span>
                            </a>
                        </li>
                        <div class="dropdown-divider"></div>
                        <li>
                            <a class="dropdown-item <?php echo $currentPage == 'user_management.php' ? 'active' : ''; ?>" href="user_management.php">
                                <span>User Management</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>
                
                <!-- Help & Support Link -->
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'help.php' ? 'active' : ''; ?>" href="help.php">
                        <i class="bi bi-question-circle"></i>
                        <span>Help & Support</span>
                    </a>
                </li>
                
                <!-- Logout Link -->
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main Content Area -->
    <div id="content">
        <!-- Top Navigation Bar -->
        <nav class="top-navbar">
            <div class="container-fluid" style="padding: 0;">
                <div class="d-flex align-items-center">
                    <!-- Sidebar Toggle Button -->
                    <button type="button" id="sidebarCollapse" class="btn">
                        <i class="bi bi-list"></i>
                    </button>
                    <div class="ms-3">
                        <h4 class="mb-0">MDRRM-ARMS</h4>
                        <small class="text-muted">
                            Welcome, <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?> 
                            (<?php echo htmlspecialchars($_SESSION['user_role'] ?? 'User'); ?>)
                            <?php if (!empty($_SESSION['barangay'])): ?>
                                - Barangay: <?php echo htmlspecialchars($_SESSION['barangay']); ?>
                            <?php endif; ?>
                        </small>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Dynamic Content Section -->
        <div class="main-content-container">
            <?php 
            // Check if content variable is set from the including page
            if (isset($content)) {
                echo $content;
            } else {
                // Default content or include your page-specific content here
                ?>
                <div class="page-header">
                    <h1>Welcome to MDRRM-ARMS</h1>
                    <p>Select a menu item to begin</p>
                </div>
                <div class="dashboard-cards">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <i class="bi bi-exclamation-triangle card-icon text-warning"></i>
                                    <h5 class="card-title">Incident Reports</h5>
                                    <p class="card-text">Submit and manage incident reports including affected populations and casualties.</p>
                                    <a href="annex1.php" class="card-link">Get Started <i class="bi bi-arrow-right"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <i class="bi bi-house card-icon text-danger"></i>
                                    <h5 class="card-title">Damage Assessment</h5>
                                    <p class="card-text">Record damage to houses, infrastructure, agriculture, and other assets.</p>
                                    <a href="annex4.php" class="card-link">View Forms <i class="bi bi-arrow-right"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <i class="bi bi-clipboard-check card-icon text-success"></i>
                                    <h5 class="card-title">Status Reports</h5>
                                    <p class="card-text">Monitor status of roads, bridges, power supply, and communications.</p>
                                    <a href="annex8.php" class="card-link">Check Status <i class="bi bi-arrow-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-light mt-2 py-2">
        <div class="container text-center">
            <p class="text-muted mb-1 small">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            <p class="text-muted mb-0 small">Developed by <span class="badge bg-primary rounded-pill small">Richmond Rosete</span></p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript for Sidebar Functionality -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar toggle functionality
            const sidebar = document.getElementById('sidebar');
            const content = document.getElementById('content');
            const sidebarCollapse = document.getElementById('sidebarCollapse');
            const mobileSidebarToggle = document.getElementById('mobileSidebarToggle');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            
            let tooltipInstances = [];

            // AUTO-CLOSE OTHER DROPDOWNS FIX
            const dropdownToggles = document.querySelectorAll('#sidebar .dropdown-toggle');
            
            dropdownToggles.forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    // Only handle this in expanded mode (not collapsed)
                    if (!sidebar.classList.contains('collapsed')) {
                        e.preventDefault(); // Prevent default anchor behavior
                        
                        const targetId = this.getAttribute('href');
                        const targetCollapse = document.querySelector(targetId);
                        
                        // Check if target is currently shown
                        const isCurrentlyShown = targetCollapse.classList.contains('show');
                        
                        // Close all other collapse menus
                        document.querySelectorAll('#sidebar .collapse.show').forEach(collapse => {
                            if (collapse !== targetCollapse) {
                                const bsCollapse = bootstrap.Collapse.getInstance(collapse) || new bootstrap.Collapse(collapse, { toggle: false });
                                bsCollapse.hide();
                            }
                        });
                        
                        // Toggle the clicked menu
                        if (!isCurrentlyShown) {
                            const bsTargetCollapse = bootstrap.Collapse.getInstance(targetCollapse) || new bootstrap.Collapse(targetCollapse, { toggle: false });
                            bsTargetCollapse.show();
                            this.setAttribute('aria-expanded', 'true');
                        } else {
                            const bsTargetCollapse = bootstrap.Collapse.getInstance(targetCollapse) || new bootstrap.Collapse(targetCollapse, { toggle: false });
                            bsTargetCollapse.hide();
                            this.setAttribute('aria-expanded', 'false');
                        }
                        
                        // Update aria-expanded for all other toggles
                        dropdownToggles.forEach(otherToggle => {
                            if (otherToggle !== this) {
                                otherToggle.setAttribute('aria-expanded', 'false');
                            }
                        });
                    }
                });
            });

            function enableTooltipsAndHoverMode() {
                // Enable tooltips for nav-links
                document.querySelectorAll('#sidebar .nav-link:not(.dropdown-toggle)').forEach(el => {
                    const span = el.querySelector('span');
                    if (span) {
                        el.setAttribute('title', span.textContent.trim());
                        el.setAttribute('data-bs-placement', 'right');
                        const tooltip = new bootstrap.Tooltip(el, {
                            trigger: 'hover'
                        });
                        tooltipInstances.push(tooltip);
                    }
                });

                // For dropdown toggles, also add tooltip
                document.querySelectorAll('#sidebar .dropdown-toggle').forEach(el => {
                    const span = el.querySelector('span');
                    if (span) {
                        el.setAttribute('title', span.textContent.trim());
                        el.setAttribute('data-bs-placement', 'right');
                        const tooltip = new bootstrap.Tooltip(el, {
                            trigger: 'hover'
                        });
                        tooltipInstances.push(tooltip);
                    }
                });

                // Disable collapse toggle for dropdowns in collapsed mode
                document.querySelectorAll('#sidebar .dropdown-toggle').forEach(tog => {
                    tog.setAttribute('data-bs-toggle-bak', tog.getAttribute('data-bs-toggle') || 'collapse');
                    tog.removeAttribute('data-bs-toggle');
                });
            }

            function disableTooltipsAndHoverMode() {
                // Disable tooltips
                tooltipInstances.forEach(tip => tip.dispose());
                tooltipInstances = [];

                // Re-enable collapse toggle for dropdowns
                document.querySelectorAll('#sidebar .dropdown-toggle').forEach(tog => {
                    tog.setAttribute('data-bs-toggle', tog.getAttribute('data-bs-toggle-bak') || 'collapse');
                    tog.removeAttribute('data-bs-toggle-bak');
                });
            }

            // Mobile sidebar toggle functionality
            function toggleMobileSidebar() {
                sidebar.classList.toggle('active');
                sidebarOverlay.classList.toggle('active');
                document.body.classList.toggle('sidebar-open');
            }

            // Close mobile sidebar when clicking on overlay
            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('active');
                document.body.classList.remove('sidebar-open');
            });

            // Close mobile sidebar only when clicking on actual page links (not dropdown toggles)
            document.querySelectorAll('#sidebar .nav-link:not(.dropdown-toggle), #sidebar .dropdown-item').forEach(link => {
                link.addEventListener('click', function(e) {
                    // Only close if it's a direct link (not a dropdown toggle)
                    if (!this.classList.contains('dropdown-toggle') && window.innerWidth < 768) {
                        sidebar.classList.remove('active');
                        sidebarOverlay.classList.remove('active');
                        document.body.classList.remove('sidebar-open');
                    }
                });
            });

            // Persist collapsed state
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';

            if (isCollapsed) {
                sidebar.classList.add('collapsed');
                content.classList.add('expanded');
                enableTooltipsAndHoverMode();
            } else {
                disableTooltipsAndHoverMode();
            }

            // Toggle sidebar collapse/expand on desktop button click
            sidebarCollapse.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                content.classList.toggle('expanded');

                const collapsed = sidebar.classList.contains('collapsed');
                localStorage.setItem('sidebarCollapsed', collapsed);

                if (collapsed) {
                    // Close all open submenus
                    document.querySelectorAll('.collapse.show').forEach(el => {
                        el.classList.remove('show');
                    });
                    enableTooltipsAndHoverMode();
                } else {
                    disableTooltipsAndHoverMode();
                }
            });

            // Toggle mobile sidebar
            mobileSidebarToggle.addEventListener('click', toggleMobileSidebar);
            
            // Handle window resize
            function handleResize() {
                if (window.innerWidth < 768) {
                    // Mobile behavior - disable collapsed mode and floating submenus
                    sidebar.classList.remove('collapsed');
                    content.classList.remove('expanded');
                    sidebar.classList.remove('active');
                    sidebarOverlay.classList.remove('active');
                    document.body.classList.remove('sidebar-open');
                    disableTooltipsAndHoverMode();
                } else {
                    // Desktop behavior - respect user's collapsed preference
                    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
                    if (isCollapsed) {
                        sidebar.classList.add('collapsed');
                        content.classList.add('expanded');
                        enableTooltipsAndHoverMode();
                    } else {
                        sidebar.classList.remove('collapsed');
                        content.classList.remove('expanded');
                        disableTooltipsAndHoverMode();
                    }
                }
            }

            // Initial check
            handleResize();
            
            // Handle window resize
            window.addEventListener('resize', handleResize);

            // Update active nav link when clicking on dropdown items
            const dropdownItems = document.querySelectorAll('.dropdown-item');
            dropdownItems.forEach(item => {
                item.addEventListener('click', function() {
                    // Remove active class from all nav links
                    document.querySelectorAll('.nav-link').forEach(link => {
                        link.classList.remove('active');
                    });
                    
                    // Add active class to parent dropdown toggle
                    const parentDropdown = this.closest('.nav-item').querySelector('.nav-link');
                    if (parentDropdown) {
                        parentDropdown.classList.add('active');
                    }
                });
            });
        });
    </script>
</body>
</html>