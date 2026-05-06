<?php
require_once 'includes/config.php';
require_once 'includes/init_db.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $gender = sanitize($_POST['gender'] ?? '');
    $age = intval($_POST['age'] ?? 0);
    $bio = sanitize($_POST['bio'] ?? '');
    $latitude = floatval($_POST['latitude'] ?? 0);
    $longitude = floatval($_POST['longitude'] ?? 0);
    
    if (empty($name) || empty($email) || empty($password) || empty($gender) || $age < 18) {
        $error = 'Please fill in all required fields. You must be 18 or older.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $db = getDB();
        
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email already registered.';
        } else {
            $photoFilename = 'default.png';
            if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
                $upload = uploadImage($_FILES['profile_photo']);
                if ($upload['success']) {
                    $photoFilename = $upload['filename'];
                } else {
                    $error = $upload['error'];
                }
            }
            
            if (empty($error)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO users (name, email, password, gender, age, bio, profile_photo, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $email, $hashedPassword, $gender, $age, $bio, $photoFilename, $latitude ?: null, $longitude ?: null]);
                
                $success = 'Registration successful! You can now login.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LoveConnect - Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            background: #fff;
            display: flex;
            flex-direction: column;
        }
        
        .header-section {
            background: linear-gradient(135deg, #e91e63 0%, #ff5252 30%, #ff8a80 60%, #fce4ec 100%);
            padding: 40px 20px 70px;
            text-align: center;
            position: relative;
            border-radius: 0 0 50% 50% / 0 0 15% 15%;
        }
        
        .header-section::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 50px;
            background: white;
            border-radius: 50% 50% 0 0;
        }
        
        .app-logo {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            margin: 0 auto 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 30px rgba(233, 30, 99, 0.3);
            position: relative;
            z-index: 2;
        }
        
        .app-logo i {
            font-size: 2.2rem;
            color: #e91e63;
        }
        
        .app-title {
            font-size: 1.6rem;
            font-weight: 800;
            color: white;
            letter-spacing: 2px;
            position: relative;
            z-index: 2;
        }
        
        .app-subtitle {
            color: rgba(255,255,255,0.9);
            font-size: 0.85rem;
            position: relative;
            z-index: 2;
        }
        
        .form-section {
            flex: 1;
            padding: 20px 25px 30px;
            max-width: 500px;
            margin: 0 auto;
            width: 100%;
        }
        
        .input-group-custom {
            position: relative;
            margin-bottom: 14px;
        }
        
        .input-group-custom .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 1.1rem;
            z-index: 3;
        }
        
        .input-group-custom .form-control,
        .input-group-custom .form-select {
            padding: 14px 18px 14px 48px;
            border-radius: 12px;
            border: 2px solid #e8e8e8;
            font-size: 0.95rem;
            background: #f8f9fa;
            transition: all 0.3s;
        }
        
        .input-group-custom .form-control:focus,
        .input-group-custom .form-select:focus {
            border-color: #e91e63;
            background: white;
            box-shadow: 0 0 0 4px rgba(233, 30, 99, 0.08);
        }
        
        .input-group-custom .form-control::placeholder {
            color: #aaa;
        }
        
        .btn-register-submit {
            width: 100%;
            padding: 16px;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 700;
            border: none;
            background: linear-gradient(135deg, #4caf50, #66bb6a);
            color: white;
            margin-bottom: 12px;
            transition: all 0.3s;
            box-shadow: 0 4px 20px rgba(76, 175, 80, 0.3);
        }
        
        .btn-register-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(76, 175, 80, 0.4);
            color: white;
        }
        
        .btn-back-login {
            width: 100%;
            padding: 16px;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            border: 2px solid #e91e63;
            background: white;
            color: #e91e63;
            transition: all 0.3s;
        }
        
        .btn-back-login:hover {
            background: #e91e63;
            color: white;
        }
        
        .section-title {
            font-weight: 700;
            color: #333;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            margin-top: 15px;
        }
        
        .footer-section {
            text-align: center;
            padding: 15px;
            background: linear-gradient(135deg, #e91e63, #c2185b);
            color: white;
            margin-top: auto;
        }
        
        .footer-section p {
            margin: 0;
            font-size: 0.8rem;
            opacity: 0.9;
        }
        
        .alert { border-radius: 12px; border: none; font-size: 0.9rem; }
        
        .photo-upload {
            border: 2px dashed #e8e8e8;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: #f8f9fa;
        }
        
        .photo-upload:hover {
            border-color: #e91e63;
            background: #fce4ec;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header-section">
        <div class="app-logo">
            <i class="bi bi-heart-fill"></i>
        </div>
        <h1 class="app-title">LOVECONNECT</h1>
        <p class="app-subtitle">Create Your Account</p>
    </div>
    
    <!-- Form -->
    <div class="form-section">
        <?php if ($error): ?>
            <div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i><?= $error ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i><?= $success ?> <a href="login.php">Login now</a></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <p class="section-title"><i class="bi bi-person me-1"></i> Personal Info</p>
            
            <div class="input-group-custom">
                <i class="bi bi-person-fill input-icon"></i>
                <input type="text" name="name" class="form-control" placeholder="Full Name" required value="<?= $_POST['name'] ?? '' ?>">
            </div>
            
            <div class="input-group-custom">
                <i class="bi bi-envelope-fill input-icon"></i>
                <input type="email" name="email" class="form-control" placeholder="Email Address" required value="<?= $_POST['email'] ?? '' ?>">
            </div>
            
            <div class="row g-2">
                <div class="col-6">
                    <div class="input-group-custom">
                        <i class="bi bi-gender-ambiguous input-icon"></i>
                        <select name="gender" class="form-select" required style="padding-left: 48px;">
                            <option value="">Gender</option>
                            <option value="male" <?= ($_POST['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= ($_POST['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                            <option value="other" <?= ($_POST['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                </div>
                <div class="col-6">
                    <div class="input-group-custom">
                        <i class="bi bi-calendar-fill input-icon"></i>
                        <input type="number" name="age" class="form-control" placeholder="Age (18+)" min="18" max="100" required value="<?= $_POST['age'] ?? '' ?>">
                    </div>
                </div>
            </div>
            
            <div class="input-group-custom">
                <i class="bi bi-chat-quote-fill input-icon" style="top: 30%;"></i>
                <textarea name="bio" class="form-control" placeholder="Tell us about yourself..." rows="2" style="padding-top: 14px;"><?= $_POST['bio'] ?? '' ?></textarea>
            </div>
            
            <p class="section-title"><i class="bi bi-shield-lock me-1"></i> Security</p>
            
            <div class="input-group-custom">
                <i class="bi bi-lock-fill input-icon"></i>
                <input type="password" name="password" class="form-control" placeholder="Password (min 6 chars)" required>
            </div>
            
            <div class="input-group-custom">
                <i class="bi bi-lock-fill input-icon"></i>
                <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required>
            </div>
            
            <p class="section-title"><i class="bi bi-camera me-1"></i> Profile Photo</p>
            
            <div class="photo-upload mb-3" onclick="document.getElementById('photoInput').click();">
                <i class="bi bi-camera-fill fs-3 text-muted"></i>
                <p class="mb-0 text-muted small" id="photoLabel">Tap to upload your photo</p>
                <input type="file" id="photoInput" name="profile_photo" accept="image/*" class="d-none" onchange="document.getElementById('photoLabel').textContent = this.files[0].name;">
            </div>
            
            <input type="hidden" name="latitude" id="latitude">
            <input type="hidden" name="longitude" id="longitude">
            
            <button type="submit" class="btn btn-register-submit mt-2">
                <i class="bi bi-heart-fill me-2"></i>Create Account
            </button>
            <a href="login.php" class="btn btn-back-login">
                <i class="bi bi-arrow-left me-2"></i>Back to Login
            </a>
        </form>
    </div>
    
    <!-- Footer -->
    <div class="footer-section">
        <p>By registering, you agree to our Terms of Use</p>
        <p class="mt-1">Version 1.0.0</p>
    </div>
    
    <script>
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                document.getElementById('latitude').value = position.coords.latitude;
                document.getElementById('longitude').value = position.coords.longitude;
            });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
