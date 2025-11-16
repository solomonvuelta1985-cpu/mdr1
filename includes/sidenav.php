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
            color: rgba(255, 255, 255, 0.85);
            padding: 8px 20px 8px 40px;
            border-left: 3px solid transparent;
            transition: all 0.2s ease;
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

        .dropdown-divider {
            height: 0;
            margin: 0;
            padding: 0;
            overflow: hidden;
            border: none;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            opacity: 0.6;
        }

        li:has(> .dropdown-divider) {
            margin: 2px 20px;
            padding: 0;
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
            padding: 8px 20px;
            border-left: none;
            display: flex;
            align-items: center;
            white-space: nowrap;
            color: #1e3c72;
            transition: all 0.2s ease;
        }

        #sidebar.collapsed ul.collapse .dropdown-item + .dropdown-item {
            border-top: none;
        }

        #sidebar.collapsed ul.collapse .dropdown-item i {
            margin-right: 10px;
            width: auto;
            color: #1e3c72;
        }

        #sidebar.collapsed ul.collapse .dropdown-item:hover {
            background: rgba(30, 60, 114, 0.08);
            color: #0d6efd;
        }

        #sidebar.collapsed ul.collapse .dropdown-item.active {
            color: #0d6efd;
            background: rgba(30, 60, 114, 0.12);
            border-left: 3px solid var(--warning-color);
        }

        #sidebar.collapsed ul.collapse .dropdown-divider {
            border: none;
            border-top: 1px solid rgba(30, 60, 114, 0.15);
            opacity: 0.5;
            margin: 0;
        }

        #sidebar.collapsed ul.collapse li:has(> .dropdown-divider) {
            margin: 3px 15px;
            padding: 0;
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
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item <?php echo $currentPage == 'annex2.php' ? 'active' : ''; ?>" href="annex2.php">
                                <span>Annex 2: Affected Population</span>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item <?php echo $currentPage == 'annex3.php' ? 'active' : ''; ?>" href="annex3.php">
                                <span>Annex 3: Casualties</span>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
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
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item <?php echo $currentPage == 'annex5.php' ? 'active' : ''; ?>" href="annex5.php">
                                <span>Annex 5: Agriculture</span>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item <?php echo $currentPage == 'annex6.php' ? 'active' : ''; ?>" href="annex6.php">
                                <span>Annex 6: Infrastructure</span>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
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
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item <?php echo $currentPage == 'annex9.php' ? 'active' : ''; ?>" href="annex9.php">
                                <span>Annex 9: Power Supply</span>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item <?php echo $currentPage == 'annex11.php' ? 'active' : ''; ?>" href="annex11.php">
                                <span>Annex 11: Communication</span>
                            </a>
                        </li>
                        <?php if (can_access_annex('14')): ?>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item <?php echo $currentPage == 'annex14.php' ? 'active' : ''; ?>" href="annex14.php">
                                <span>Annex 14: Work Suspension</span>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (can_access_annex('15')): ?>
                        <li><hr class="dropdown-divider"></li>
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
                        <li><hr class="dropdown-divider"></li>
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
                        <?php endif; ?>
                        <?php if (can_access_annex('20')): ?>
                        <?php if (can_access_annex('19')): ?>
                        <li><hr class="dropdown-divider"></li>
                        <?php endif; ?>
                        <li>
                            <a class="dropdown-item <?php echo $currentPage == 'annex20.php' ? 'active' : ''; ?>" href="annex20.php">
                                <span>Annex 20: To Families</span>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php if (can_access_annex('21')): ?>
                        <?php if (can_access_annex('19') || can_access_annex('20')): ?>
                        <li><hr class="dropdown-divider"></li>
                        <?php endif; ?>
                        <li>
                            <a class="dropdown-item <?php echo $currentPage == 'annex21.php' ? 'active' : ''; ?>" href="annex21.php">
                                <span>Annex 21: To LGUs/Agencies</span>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </li>
                <?php endif; ?>

                <!-- Manage Disaster Events (Admin & Special Access Only) -->
                <?php if (in_array($_SESSION['user_role'], ['admin', 'special_access'])): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'manage_disaster_events.php' ? 'active' : ''; ?>" href="manage_disaster_events.php">
                        <i class="bi bi-calendar-event"></i>
                        <span>Disaster Events</span>
                    </a>
                </li>
                <?php endif; ?>

                <!-- Terminal Report Generator (Admin & Special Access Only) -->
                <?php if (in_array($_SESSION['user_role'], ['admin', 'special_access'])): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo $currentPage == 'generate_terminal_report.php' ? 'active' : ''; ?>" href="generate_terminal_report.php">
                        <i class="bi bi-file-earmark-text"></i>
                        <span>Terminal Report</span>
                    </a>
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
                        <li><hr class="dropdown-divider"></li>
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
                <div class="d-flex align-items-center justify-content-between w-100">
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

                    <?php
                    // Show notification bell for admin and staff only
                    $show_notifications = isset($_SESSION['user_role']) &&
                                        ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'staff');
                    ?>

                    <?php if ($show_notifications): ?>
                    <div class="d-flex align-items-center gap-3">
                        <!-- Notification Bell -->
                        <div class="notification-bell-container" style="position: relative;">
                            <button class="btn btn-link p-2" id="notificationBell" type="button" style="position: relative; color: #6c757d; transition: all 0.3s; background: #f8f9fa; border-radius: 8px; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-bell" style="font-size: 1.2rem;"></i>
                                <span class="notification-badge" id="notificationBadge" style="display: none; position: absolute; top: 2px; right: 2px; background: #dc3545; color: white; border-radius: 50%; width: 18px; height: 18px; font-size: 0.65rem; font-weight: bold; display: flex; align-items: center; justify-content: center; border: 2px solid white;">0</span>
                            </button>

                            <div class="notification-dropdown" id="notificationDropdown" style="display: none; position: absolute; top: calc(100% + 8px); right: 0; background: white; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.12); width: 400px; max-height: 550px; overflow: hidden; z-index: 1050; border: 1px solid #e5e7eb;">
                                <div class="notification-header" style="padding: 16px 20px; border-bottom: 1px solid #e5e7eb; background: #f8f9fa;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                        <h6 style="margin: 0; font-size: 15px; font-weight: 600; color: #111827;">Notifications</h6>
                                        <span id="notificationCount" style="font-size: 12px; color: #6c757d; font-weight: 500;">0 new</span>
                                    </div>
                                    <div style="display: flex; gap: 8px;">
                                        <button class="btn btn-link btn-sm" id="markAllRead" style="padding: 4px 8px; color: #0d6efd; font-size: 12px; text-decoration: none; font-weight: 500; flex: 1; background: white; border: 1px solid #dee2e6; border-radius: 4px;">
                                            <i class="bi bi-check-all me-1"></i>Mark all read
                                        </button>
                                        <button class="btn btn-link btn-sm" id="clearAllNotifications" style="padding: 4px 8px; color: #dc3545; font-size: 12px; text-decoration: none; font-weight: 500; flex: 1; background: white; border: 1px solid #dee2e6; border-radius: 4px;">
                                            <i class="bi bi-trash me-1"></i>Clear all
                                        </button>
                                    </div>
                                </div>
                                <div class="notification-list" id="notificationList" style="max-height: 400px; overflow-y: auto;">
                                    <div style="padding: 40px 20px; text-align: center; color: #9ca3af;">
                                        <i class="bi bi-bell-slash" style="font-size: 3rem; margin-bottom: 12px; opacity: 0.3;"></i>
                                        <p style="margin: 0; font-size: 14px;">No notifications</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Barangay Activity Monitor Button -->
                        <div class="activity-monitor-container" style="position: relative;">
                            <button class="btn btn-link p-2" id="activityMonitorBtn" type="button" data-bs-toggle="modal" data-bs-target="#activityMonitorModal" style="position: relative; color: #6c757d; transition: all 0.3s; background: #f8f9fa; border-radius: 8px; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-bar-chart-line" style="font-size: 1.2rem;"></i>
                                <span class="activity-badge" id="activityBadge" style="display: none; position: absolute; top: 2px; right: 2px; background: #f59e0b; color: white; border-radius: 50%; width: 18px; height: 18px; font-size: 0.65rem; font-weight: bold; display: flex; align-items: center; justify-content: center; border: 2px solid white;">0</span>
                            </button>
                        </div>
                    </div>

                    <style>
                        /* Enhanced hover effects */
                        #notificationBell:hover,
                        #activityMonitorBtn:hover {
                            background: #e9ecef !important;
                            color: #0d6efd !important;
                            transform: translateY(-1px);
                        }

                        #notificationBell:active,
                        #activityMonitorBtn:active {
                            transform: translateY(0);
                        }

                        /* Badge animation */
                        .notification-badge,
                        .activity-badge {
                            animation: pulse 2s infinite;
                        }

                        @keyframes pulse {
                            0%, 100% {
                                opacity: 1;
                            }
                            50% {
                                opacity: 0.7;
                            }
                        }

                        /* Smooth dropdown animation */
                        .notification-dropdown {
                            animation: slideDown 0.2s ease-out;
                        }

                        @keyframes slideDown {
                            from {
                                opacity: 0;
                                transform: translateY(-10px);
                            }
                            to {
                                opacity: 1;
                                transform: translateY(0);
                            }
                        }
                    </style>
                    <?php endif; ?>
                </div>
            </div>
        </nav>

        <!-- Dynamic Content Section (Only for dashboard.php with $content variable) -->
        <?php if (isset($content)): ?>
        <div class="main-content-container">
            <?php echo $content; ?>
        </div>
        <?php endif; ?>
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

    <?php if ($show_notifications ?? false): ?>
    <!-- Notification System JavaScript -->
    <script>
        (function() {
            const notificationBell = document.getElementById('notificationBell');
            const notificationDropdown = document.getElementById('notificationDropdown');
            const notificationBadge = document.getElementById('notificationBadge');
            const notificationList = document.getElementById('notificationList');
            const notificationCount = document.getElementById('notificationCount');
            const markAllReadBtn = document.getElementById('markAllRead');
            const clearAllBtn = document.getElementById('clearAllNotifications');

            if (!notificationBell) return; // Exit if elements not found

            let notifications = [];
            let dropdownOpen = false;

            // Toggle dropdown
            notificationBell.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdownOpen = !dropdownOpen;
                notificationDropdown.style.display = dropdownOpen ? 'block' : 'none';
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (dropdownOpen && !notificationDropdown.contains(e.target) && e.target !== notificationBell) {
                    dropdownOpen = false;
                    notificationDropdown.style.display = 'none';
                }
            });

            // Fetch notifications
            function fetchNotifications() {
                console.log('Fetching notifications from: ../api/notifications_fetch.php');
                fetch('../api/notifications_fetch.php')
                    .then(response => {
                        console.log('Response status:', response.status);
                        if (!response.ok) {
                            throw new Error('HTTP error! status: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Received notification data:', data);
                        notifications = data.notifications || [];
                        updateBadge(data.count || 0);
                        renderNotifications();
                    })
                    .catch(error => {
                        console.error('Failed to fetch notifications:', error);
                        console.error('Error details:', error.message);
                    });
            }

            // Update badge count
            function updateBadge(count) {
                if (count > 0) {
                    notificationBadge.textContent = count > 99 ? '99+' : count;
                    notificationBadge.style.display = 'block';
                } else {
                    notificationBadge.style.display = 'none';
                }

                // Update notification count text
                const totalCount = notifications.length;
                const unreadCount = notifications.filter(n => !n.is_read).length;
                if (totalCount > 0) {
                    notificationCount.textContent = unreadCount > 0 ? `${unreadCount} new` : `${totalCount} total`;
                } else {
                    notificationCount.textContent = '0 new';
                }
            }

            // Render notifications list
            function renderNotifications() {
                if (notifications.length === 0) {
                    notificationList.innerHTML = `
                        <div style="padding: 40px 20px; text-align: center; color: #9ca3af;">
                            <i class="bi bi-bell-slash" style="font-size: 3rem; margin-bottom: 12px; opacity: 0.3;"></i>
                            <p>No notifications</p>
                        </div>
                    `;
                    return;
                }

                const html = notifications.map(notif => {
                    const bgColor = !notif.is_read ? '#eff6ff' : 'white';
                    const borderLeft = notif.is_priority ? '4px solid #dc3545' : '0';
                    const priorityBadge = notif.is_priority ? '<span style="display: inline-block; padding: 2px 6px; font-size: 10px; font-weight: 600; border-radius: 3px; margin-left: 6px; background: #fee2e2; color: #991b1b;">PRIORITY</span>' : '';

                    return `
                        <div class="notification-item"
                             data-id="${notif.id}"
                             data-record-id="${notif.record_id}"
                             data-annex="${notif.annex_number}"
                             style="padding: 12px 16px; border-bottom: 1px solid #f3f4f6; cursor: pointer; background: ${bgColor}; border-left: ${borderLeft}; transition: background 0.2s;"
                             onmouseover="this.style.background='#f9fafb'"
                             onmouseout="this.style.background='${bgColor}'">
                            <div style="font-size: 13px; color: #374151; margin-bottom: 4px;">
                                <strong>Barangay ${notif.barangay}</strong> ${notif.action_text} in
                                <strong>Annex ${notif.annex_number}</strong>${priorityBadge}
                            </div>
                            <div style="font-size: 13px; color: #374151; margin-bottom: 4px;">
                                ${notif.message}
                            </div>
                            <div style="font-size: 11px; color: #6b7280; display: flex; justify-content: space-between; align-items: center;">
                                <span>By: ${notif.submitted_by_name}</span>
                                <span style="color: #9ca3af;">${notif.time_ago}</span>
                            </div>
                        </div>
                    `;
                }).join('');

                notificationList.innerHTML = html;

                // Add click handlers to notification items
                notificationList.querySelectorAll('.notification-item').forEach(item => {
                    item.addEventListener('click', function() {
                        const notifId = this.dataset.id;
                        const recordId = this.dataset.recordId;
                        const annexNumber = this.dataset.annex;

                        // Mark as read
                        markAsRead(notifId);

                        // Navigate to the record
                        const recordsPage = `annex${annexNumber}_records.php`;
                        window.location.href = recordsPage + '?highlight=' + recordId;
                    });
                });
            }

            // Mark single notification as read
            function markAsRead(notificationId) {
                fetch('../api/notifications_mark_read.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'mark_one',
                        notification_id: notificationId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const notif = notifications.find(n => n.id == notificationId);
                        if (notif) {
                            notif.is_read = true;
                        }
                        fetchNotifications();
                    }
                })
                .catch(error => {
                    console.error('Failed to mark notification as read:', error);
                });
            }

            // Mark all notifications as read
            markAllReadBtn.addEventListener('click', function(e) {
                e.stopPropagation();

                fetch('../api/notifications_mark_read.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'mark_all'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        notifications.forEach(n => n.is_read = true);
                        fetchNotifications();
                    }
                })
                .catch(error => {
                    console.error('Failed to mark all as read:', error);
                });
            });

            // Auto-refresh every 30 seconds
            fetchNotifications(); // Initial fetch
            setInterval(fetchNotifications, 30000); // Refresh every 30 seconds
        })();
    </script>
    <?php endif; ?>

    <?php if ($show_notifications ?? false): ?>
    <!-- Barangay Activity Monitor Modal -->
    <div class="modal fade" id="activityMonitorModal" tabindex="-1" aria-labelledby="activityMonitorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="activityMonitorModalLabel">
                        <i class="bi bi-graph-up me-2"></i>Barangay Activity Monitor
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-md-2">
                            <div class="card border-success" style="border-width: 2px;">
                                <div class="card-body text-center p-2">
                                    <h4 class="text-success mb-0" id="activeOnlineCount">-</h4>
                                    <small class="text-muted" style="font-size: 0.7rem;">🟢 Online & Active</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card border-info" style="border-width: 2px;">
                                <div class="card-body text-center p-2">
                                    <h4 class="text-info mb-0" id="onlineQuietCount">-</h4>
                                    <small class="text-muted" style="font-size: 0.7rem;">🔵 Online</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card border-success" style="border-width: 1px;">
                                <div class="card-body text-center p-2">
                                    <h4 class="text-success mb-0" id="activeCount">-</h4>
                                    <small class="text-muted" style="font-size: 0.7rem;">Active</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card border-warning">
                                <div class="card-body text-center p-2">
                                    <h4 class="text-warning mb-0" id="quietCount">-</h4>
                                    <small class="text-muted" style="font-size: 0.7rem;">⚠️ Quiet</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card border-danger">
                                <div class="card-body text-center p-2">
                                    <h4 class="text-danger mb-0" id="criticalCount">-</h4>
                                    <small class="text-muted" style="font-size: 0.7rem;">🚨 Critical</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card border-secondary">
                                <div class="card-body text-center p-2">
                                    <h4 class="text-secondary mb-0" id="noActivityCount">-</h4>
                                    <small class="text-muted" style="font-size: 0.7rem;">⚫ None</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Active Online Barangays -->
                    <div id="activeOnlineSection" style="display: none;">
                        <h6 class="text-success"><i class="bi bi-check-circle-fill me-2"></i>🟢 ACTIVE ONLINE - Recently Submitted</h6>
                        <div class="list-group mb-3" id="activeOnlineList"></div>
                    </div>

                    <!-- Online Quiet Barangays -->
                    <div id="onlineQuietSection" style="display: none;">
                        <h6 class="text-info"><i class="bi bi-info-circle-fill me-2"></i>🔵 ONLINE - No Recent Submission</h6>
                        <div class="list-group mb-3" id="onlineQuietList"></div>
                    </div>

                    <!-- Critical Barangays -->
                    <div id="criticalSection" style="display: none;">
                        <h6 class="text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>🚨 CRITICAL - Needs Immediate Attention</h6>
                        <div class="list-group mb-3" id="criticalList"></div>
                    </div>

                    <!-- Quiet Barangays -->
                    <div id="quietSection" style="display: none;">
                        <h6 class="text-warning"><i class="bi bi-exclamation-circle-fill me-2"></i>⚠️ QUIET - Needs Check</h6>
                        <div class="list-group mb-3" id="quietList"></div>
                    </div>

                    <!-- Active Barangays -->
                    <div id="activeSection" style="display: none;">
                        <h6 class="text-success"><i class="bi bi-check-circle-fill me-2"></i>ACTIVE - Recently Updated</h6>
                        <div class="list-group mb-3" id="activeList"></div>
                    </div>

                    <!-- No Activity Barangays -->
                    <div id="noActivitySection" style="display: none;">
                        <h6 class="text-secondary"><i class="bi bi-dash-circle-fill me-2"></i>NO ACTIVITY RECORDED</h6>
                        <div class="list-group mb-3" id="noActivityList"></div>
                    </div>

                    <!-- Loading State -->
                    <div id="activityLoading" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading barangay activity...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <small class="text-muted me-auto">Last updated: <span id="lastUpdated">-</span></small>
                    <button type="button" class="btn btn-sm btn-primary" id="refreshActivity">
                        <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                    </button>
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Monitor JavaScript -->
    <script>
        (function() {
            const activityModal = document.getElementById('activityMonitorModal');
            const activityBadge = document.getElementById('activityBadge');
            const refreshBtn = document.getElementById('refreshActivity');

            let activityData = null;

            // Fetch activity data
            function fetchActivityData() {
                document.getElementById('activityLoading').style.display = 'block';

                fetch('../api/barangay_activity_fetch.php')
                    .then(response => response.json())
                    .then(data => {
                        activityData = data;
                        updateBadge(data.alert_count);
                        renderActivityModal(data);
                        document.getElementById('activityLoading').style.display = 'none';
                        document.getElementById('lastUpdated').textContent = new Date().toLocaleTimeString();
                    })
                    .catch(error => {
                        console.error('Failed to fetch barangay activity:', error);
                        document.getElementById('activityLoading').style.display = 'none';
                    });
            }

            // Update badge
            function updateBadge(count) {
                if (count > 0) {
                    activityBadge.textContent = count > 99 ? '99+' : count;
                    activityBadge.style.display = 'block';
                } else {
                    activityBadge.style.display = 'none';
                }
            }

            // Render activity data in modal
            function renderActivityModal(data) {
                // Update summary cards
                document.getElementById('activeOnlineCount').textContent = data.active_online_count;
                document.getElementById('onlineQuietCount').textContent = data.online_quiet_count;
                document.getElementById('activeCount').textContent = data.active_count;
                document.getElementById('quietCount').textContent = data.quiet_count;
                document.getElementById('criticalCount').textContent = data.critical_count;
                document.getElementById('noActivityCount').textContent = data.no_activity_count;

                // Render active online barangays
                renderEnhancedSection('active_online', data.active_online, 'activeOnlineSection', 'activeOnlineList', 'success');

                // Render online quiet barangays
                renderEnhancedSection('online_quiet', data.online_quiet, 'onlineQuietSection', 'onlineQuietList', 'info');

                // Render critical barangays
                renderEnhancedSection('critical', data.critical, 'criticalSection', 'criticalList', 'danger');

                // Render quiet barangays
                renderEnhancedSection('quiet', data.quiet, 'quietSection', 'quietList', 'warning');

                // Render active barangays
                renderEnhancedSection('active', data.active, 'activeSection', 'activeList', 'success');

                // Render no activity barangays
                renderEnhancedSection('no_activity', data.no_activity, 'noActivitySection', 'noActivityList', 'secondary');
            }

            // Render enhanced section with login + submission data
            function renderEnhancedSection(type, items, sectionId, listId, colorClass) {
                const section = document.getElementById(sectionId);
                const list = document.getElementById(listId);

                if (items.length === 0) {
                    section.style.display = 'none';
                    return;
                }

                section.style.display = 'block';
                list.innerHTML = items.map(item => {
                    let statusIcon = '';
                    let statusText = '';
                    let detailsHTML = '';

                    // Online indicator
                    if (item.is_online) {
                        statusIcon = '<span class="badge bg-success" style="font-size: 0.7rem;">● ONLINE</span>';
                    }

                    // Build details based on what data is available
                    const details = [];

                    if (item.last_submission) {
                        const hours = parseFloat(item.hours_since_submission);
                        details.push(`Last submit: ${item.last_annex || 'Unknown'} (${hours.toFixed(1)}h ago)`);
                    }

                    if (item.last_login && item.activity_type === 'login') {
                        const hours = parseFloat(item.hours_since_login);
                        details.push(`Last login: ${hours.toFixed(1)}h ago`);
                    }

                    if (details.length === 0) {
                        details.push('No activity recorded');
                    }

                    detailsHTML = details.join(' • ');

                    // Time badge
                    let timeBadge = '';
                    if (item.hours_since_activity !== null) {
                        timeBadge = `${item.hours_since_activity.toFixed(1)}h ago`;
                    } else {
                        timeBadge = 'No data';
                    }

                    return `
                        <div class="list-group-item list-group-item-${colorClass} d-flex justify-content-between align-items-start">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold">
                                    ${item.barangay}
                                    ${statusIcon}
                                </div>
                                <small style="font-size: 0.75rem;">${detailsHTML}</small>
                            </div>
                            <span class="badge bg-${colorClass} rounded-pill">${timeBadge}</span>
                        </div>
                    `;
                }).join('');
            }

            // Refresh button click
            refreshBtn.addEventListener('click', function() {
                fetchActivityData();
            });

            // Fetch when modal is opened
            activityModal.addEventListener('show.bs.modal', function() {
                fetchActivityData();
            });

            // Initial fetch for badge
            fetchActivityData();

            // Auto-refresh every 60 seconds
            setInterval(fetchActivityData, 60000);
        })();
    </script>
    <?php endif; ?>
</body>
</html>