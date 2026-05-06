<?php
require_once 'includes/config.php';
require_once 'includes/init_db.php';

if (isAdmin()) {
    header('Location: admin_dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($email === 'Romar' && $password === 'Romar') {
        $_SESSION['is_admin'] = true;
        $_SESSION['user_id'] = 0;
        $_SESSION['user_name'] = 'Admin';
        header('Location: admin_dashboard.php');
        exit;
    } else {
        $error = 'Invalid admin credentials.';
    }
}

$pageTitle = 'Admin Login - LoveConnect';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            padding: 20px;
        }
        .auth-card {
            background: white;
            border-radius: 25px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 420px;
        }
        .form-control-dating {
            border-radius: 12px;
            padding: 12px 18px;
            border: 2px solid #e0e0e0;
        }
        .form-control-dating:focus {
            border-color: #1a1a2e;
            box-shadow: 0 0 0 3px rgba(26, 26, 46, 0.1);
        }
        .btn-admin {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            border: none;
            color: white;
            font-weight: 600;
            border-radius: 25px;
            padding: 12px 30px;
            width: 100%;
            transition: all 0.3s ease;
        }
        .btn-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(26, 26, 46, 0.4);
            color: white;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="text-center mb-4">
                <i class="bi bi-shield-lock-fill fs-1" style="color: #1a1a2e;"></i>
                <h2 class="fw-bold mt-2">Admin Panel</h2>
                <p class="text-muted">Authorized access only</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger rounded-3">
                    <i class="bi bi-exclamation-circle me-2"></i><?= $error ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-500">Email</label>
                    <input type="text" name="email" class="form-control form-control-dating" placeholder="Admin email" required>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-500">Password</label>
                    <input type="password" name="password" class="form-control form-control-dating" placeholder="Admin password" required>
                </div>
                <button type="submit" class="btn btn-admin">
                    <i class="bi bi-shield-check me-2"></i>Admin Login
                </button>
                <p class="text-center mt-3 mb-0">
                    <a href="login.php" class="text-decoration-none text-muted">Back to User Login</a>
                </p>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
