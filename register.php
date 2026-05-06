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
    
    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($gender) || $age < 18) {
        $error = 'Please fill in all required fields. You must be 18 or older.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $db = getDB();
        
        // Check if email exists
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email already registered.';
        } else {
            // Handle profile photo upload
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

$pageTitle = 'Register - LoveConnect';
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
            max-width: 500px;
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
                <h2 class="fw-bold mt-2">Create Account</h2>
                <p class="text-muted">Join LoveConnect and find your match</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
                    <i class="bi bi-exclamation-circle me-2"></i><?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
                    <i class="bi bi-check-circle me-2"></i><?= $success ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label fw-500">Full Name</label>
                    <input type="text" name="name" class="form-control form-control-dating" placeholder="Enter your name" required value="<?= $_POST['name'] ?? '' ?>">
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-500">Email</label>
                    <input type="email" name="email" class="form-control form-control-dating" placeholder="Enter your email" required value="<?= $_POST['email'] ?? '' ?>">
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-500">Password</label>
                        <input type="password" name="password" class="form-control form-control-dating" placeholder="Min 6 characters" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-500">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control form-control-dating" placeholder="Repeat password" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-500">Gender</label>
                        <select name="gender" class="form-select form-control-dating" required>
                            <option value="">Select gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-500">Age</label>
                        <input type="number" name="age" class="form-control form-control-dating" min="18" max="100" placeholder="18+" required value="<?= $_POST['age'] ?? '' ?>">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-500">Bio</label>
                    <textarea name="bio" class="form-control form-control-dating" rows="3" placeholder="Tell us about yourself..."><?= $_POST['bio'] ?? '' ?></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-500">Profile Photo</label>
                    <input type="file" name="profile_photo" class="form-control form-control-dating" accept="image/*">
                </div>
                
                <input type="hidden" name="latitude" id="latitude">
                <input type="hidden" name="longitude" id="longitude">
                
                <button type="submit" class="btn btn-gradient mt-3">
                    <i class="bi bi-heart-fill me-2"></i>Create Account
                </button>
                
                <p class="text-center mt-3 mb-0">
                    Already have an account? <a href="login.php" class="text-decoration-none" style="color: #764ba2;">Sign In</a>
                </p>
            </form>
        </div>
    </div>
    
    <script>
        // Get user location
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
