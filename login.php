<?php
require_once 'includes/config.php';
require_once 'includes/init_db.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['account_type'] = $user['account_type'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

$pageTitle = 'Login - LoveConnect';
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }
        .auth-card {
            background: white;
            border-radius: 25px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 420px;
        }
        .form-control-dating {
            border-radius: 12px;
            padding: 12px 18px;
            border: 2px solid #e0e0e0;
            transition: all 0.3s ease;
        }
        .form-control-dating:focus {
            border-color: #764ba2;
            box-shadow: 0 0 0 3px rgba(118, 75, 162, 0.1);
        }
        .btn-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            font-weight: 600;
            border-radius: 25px;
            padding: 12px 30px;
            transition: all 0.3s ease;
            width: 100%;
        }
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="text-center mb-4">
                <i class="bi bi-heart-fill text-danger fs-1"></i>
                <h2 class="fw-bold mt-2">Welcome Back</h2>
                <p class="text-muted">Sign in to continue your journey</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
                    <i class="bi bi-exclamation-circle me-2"></i><?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-500">Email</label>
                    <div class="input-group">
                        <span class="input-group-text rounded-start-3 border-end-0" style="border: 2px solid #e0e0e0; border-right: none;">
                            <i class="bi bi-envelope text-muted"></i>
                        </span>
                        <input type="text" name="email" class="form-control form-control-dating border-start-0" placeholder="Enter your email" required value="<?= $_POST['email'] ?? '' ?>">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-500">Password</label>
                    <div class="input-group">
                        <span class="input-group-text rounded-start-3 border-end-0" style="border: 2px solid #e0e0e0; border-right: none;">
                            <i class="bi bi-lock text-muted"></i>
                        </span>
                        <input type="password" name="password" class="form-control form-control-dating border-start-0" placeholder="Enter your password" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-gradient">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                </button>
                
                <p class="text-center mt-3 mb-0">
                    Don't have an account? <a href="register.php" class="text-decoration-none" style="color: #764ba2;">Sign Up</a>
                </p>
                <p class="text-center mt-2 mb-0">
                    <a href="admin_login.php" class="text-decoration-none text-muted small">Admin Login</a>
                </p>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
