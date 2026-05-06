<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'LoveConnect - Dating App' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --accent-color: #764ba2;
            --soft-pink: #fff0f3;
            --soft-purple: #f3e8ff;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }
        
        .navbar-dating {
            background: var(--primary-gradient);
            box-shadow: 0 2px 15px rgba(102, 126, 234, 0.3);
        }
        
        .navbar-dating .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: white !important;
        }
        
        .navbar-dating .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .navbar-dating .nav-link:hover {
            color: white !important;
            transform: translateY(-1px);
        }
        
        .btn-gradient {
            background: var(--primary-gradient);
            border: none;
            color: white;
            font-weight: 600;
            border-radius: 25px;
            padding: 10px 30px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .btn-gradient-pink {
            background: var(--secondary-gradient);
            border: none;
            color: white;
            font-weight: 600;
            border-radius: 25px;
            padding: 10px 30px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(245, 87, 108, 0.3);
        }
        
        .btn-gradient-pink:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(245, 87, 108, 0.4);
            color: white;
        }
        
        .card-dating {
            border: none;
            border-radius: 20px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .card-dating:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12);
        }
        
        .profile-card-img {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }
        
        .badge-premium {
            background: linear-gradient(135deg, #f5af19 0%, #f12711 100%);
            color: white;
            font-weight: 600;
            padding: 5px 12px;
            border-radius: 15px;
        }
        
        .badge-free {
            background: #e0e0e0;
            color: #666;
            font-weight: 500;
            padding: 5px 12px;
            border-radius: 15px;
        }
        
        .distance-badge {
            background: var(--soft-purple);
            color: var(--accent-color);
            font-weight: 500;
            padding: 4px 10px;
            border-radius: 10px;
            font-size: 0.85rem;
        }
        
        .chat-bubble {
            max-width: 75%;
            padding: 12px 18px;
            border-radius: 20px;
            margin-bottom: 10px;
            position: relative;
        }
        
        .chat-bubble-sent {
            background: var(--primary-gradient);
            color: white;
            margin-left: auto;
            border-bottom-right-radius: 5px;
        }
        
        .chat-bubble-received {
            background: #f0f0f0;
            color: #333;
            margin-right: auto;
            border-bottom-left-radius: 5px;
        }
        
        .chat-time {
            font-size: 0.7rem;
            opacity: 0.7;
            margin-top: 4px;
        }
        
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-gradient);
        }
        
        .auth-card {
            background: white;
            border-radius: 25px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 450px;
        }
        
        .form-control-dating {
            border-radius: 12px;
            padding: 12px 18px;
            border: 2px solid #e0e0e0;
            transition: all 0.3s ease;
        }
        
        .form-control-dating:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(118, 75, 162, 0.1);
        }
        
        .stat-card {
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            color: white;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: scale(1.05);
        }
        
        .hero-section {
            min-height: 100vh;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: pulse 4s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .floating-hearts {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
        }
        
        .floating-hearts i {
            position: absolute;
            color: rgba(255, 255, 255, 0.15);
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(10deg); }
        }
    </style>
</head>
<body>
<?php if (isLoggedIn() && !isAdmin()): ?>
<nav class="navbar navbar-expand-lg navbar-dating sticky-top">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">
            <i class="bi bi-heart-fill me-2"></i>LoveConnect
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <i class="bi bi-list text-white fs-4"></i>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php"><i class="bi bi-grid-fill me-1"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="profile.php"><i class="bi bi-person-fill me-1"></i> Profile</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="chat.php"><i class="bi bi-chat-heart-fill me-1"></i> Chat</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right me-1"></i> Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<?php elseif (isAdmin()): ?>
<nav class="navbar navbar-expand-lg navbar-dating sticky-top">
    <div class="container">
        <a class="navbar-brand" href="admin_dashboard.php">
            <i class="bi bi-shield-fill me-2"></i>Admin Panel
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <i class="bi bi-list text-white fs-4"></i>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="admin_dashboard.php"><i class="bi bi-speedometer2 me-1"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right me-1"></i> Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<?php endif; ?>
