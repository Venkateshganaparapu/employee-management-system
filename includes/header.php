<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MediCore</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- AOS CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    
    <style>
        /* Top Navbar */
        .top-navbar {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            padding: 15px 20px;
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        body.dark-mode .top-navbar {
            background: rgba(30, 41, 59, 0.9);
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }

        .avatar {
            width: 40px;
            height: 40px;
            background: var(--bg-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        
        /* Sidebar styles added to header to apply globally to all dashboard pages */
        .sidebar {
            background: white;
            box-shadow: var(--shadow-md);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 105;
            padding-top: 20px;
        }
        body.dark-mode .sidebar {
            background: #1E293B;
            border-right: 1px solid rgba(255,255,255,0.05);
        }

        .sidebar-brand {
            padding: 0 20px 20px 20px;
            font-size: 1.5rem;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--primary-deep-blue);
            border-bottom: 1px solid rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }

        .sidebar-nav {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .nav-item-c {
            padding: 0 15px;
        }

        .nav-link-c {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 15px;
            border-radius: 12px;
            color: var(--text-muted);
            text-decoration: none;
            transition: var(--transition-fast);
            font-weight: 500;
        }
        
        .nav-link-c:hover, .nav-link-c.active {
            background: var(--primary-teal);
            color: white;
            box-shadow: var(--shadow-glow);
        }
        
        .nav-link-c i {
            transition: var(--transition-fast);
        }
        
        .nav-link-c:hover i {
            transform: scale(1.1);
        }

        /* Responsive */
        .toggle-btn {
            background: transparent;
            border: none;
            color: var(--text-dark);
            font-size: 1.5rem;
            cursor: pointer;
            display: none;
        }

        @media (max-width: 768px) {
            .toggle-btn { display: block; }
        }
        
        /* Page Load Animation (Fade In) */
        .page-content-wrapper {
            animation: fadeIn 0.5s ease-in-out;
            padding: 20px;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <div class="d-flex">
