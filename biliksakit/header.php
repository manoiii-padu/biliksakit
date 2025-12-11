<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Bilik Sakit Asrama KVSP1'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            overflow-x: hidden;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 260px;
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            padding: 0;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            overflow-y: auto;
        }
        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }
        .sidebar-header h4 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 700;
        }
        .sidebar-header i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        .sidebar-user {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            background: rgba(0,0,0,0.1);
        }
        .sidebar-user .user-name {
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 0.25rem;
        }
        .sidebar-user .user-role {
            font-size: 0.85rem;
            opacity: 0.9;
        }
        .sidebar-menu {
            padding: 1rem 0;
        }
        .menu-item {
            display: block;
            padding: 0.875rem 1.5rem;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        .menu-item:hover {
            background: rgba(255,255,255,0.1);
            border-left-color: white;
            color: white;
        }
        .menu-item.active {
            background: rgba(255,255,255,0.2);
            border-left-color: white;
            font-weight: 600;
        }
        .menu-item i {
            width: 20px;
            margin-right: 0.75rem;
        }
        .menu-divider {
            padding: 0.5rem 1.5rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            opacity: 0.7;
            font-weight: 600;
            margin-top: 1rem;
        }
        .main-content {
            margin-left: 260px;
            padding: 2rem;
            min-height: 100vh;
        }
        .top-bar {
            background: white;
            padding: 1rem 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .top-bar h5 {
            margin: 0;
            color: #2c3e50;
            font-weight: 600;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .stat-card {
            border-left: 4px solid;
        }
        .stat-card.total { border-left-color: #3498db; }
        .stat-card.menunggu { border-left-color: #ffc107; }
        .stat-card.diluluskan { border-left-color: #28a745; }
        .stat-card.ditolak { border-left-color: #dc3545; }
        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.3;
        }
        .badge-menunggu { background-color: #ffc107; color: #000; }
        .badge-diluluskan { background-color: #28a745; }
        .badge-ditolak { background-color: #dc3545; }
        .bilik-card {
            transition: transform 0.3s;
        }
        .bilik-card:hover {
            transform: translateY(-5px);
        }
        .form-control:focus, .form-select:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        .btn-primary {
            background-color: #3498db;
            border-color: #3498db;
        }
        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }
        .btn-logout {
            color: #ff6b6b;
        }
        .btn-logout:hover {
            background: rgba(255,107,107,0.2);
            color: #ff6b6b;
        }
        .menu-toggle {
            display: none;
            background: #3498db;
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
            .menu-toggle {
                display: block !important;
            }
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>

<!-- Main Content -->
<div class="main-content">
    <div class="top-bar">
        <div>
            <button class="menu-toggle" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <h5 class="d-inline ms-3"><?php echo isset($page_title) ? $page_title : 'Dashboard'; ?></h5>
        </div>
        <div>
            <span class="text-muted">Selamat Datang, <strong><?php echo htmlspecialchars($_SESSION['nama']); ?></strong></span>
        </div>
    </div>

