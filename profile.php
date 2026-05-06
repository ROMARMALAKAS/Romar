<?php
require_once 'includes/config.php';
require_once 'includes/init_db.php';
requireLogin();

$db = getDB();
$error = '';
$success = '';

// Get current user
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $age = intval($_POST['age'] ?? 0);
    $gender = sanitize($_POST['gender'] ?? '');
    $bio = sanitize($_POST['bio'] ?? '');
    
    if (empty($name) || $age < 18 || empty($gender)) {
        $error = 'Please fill in all required fields.';
    } else {
        $photoFilename = $user['profile_photo'];
        
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $upload = uploadImage($_FILES['profile_photo']);
            if ($upload['success']) {
                $photoFilename = $upload['filename'];
            } else {
                $error = $upload['error'];
            }
        }
        
        if (empty($error)) {
            $stmt = $db->prepare("UPDATE users SET name = ?, age = ?, gender = ?, bio = ?, profile_photo = ? WHERE id = ?");
            $stmt->execute([$name, $age, $gender, $bio, $photoFilename, $_SESSION['user_id']]);
            $_SESSION['user_name'] = $name;
            $success = 'Profile updated successfully!';
            
            // Refresh user data
            $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
        }
    }
}

$pageTitle = 'My Profile - LoveConnect';
include 'includes/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-dating">
                <div class="card-body p-4">
                    <h3 class="fw-bold mb-4"><i class="bi bi-person-circle me-2"></i>My Profile</h3>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger rounded-3"><i class="bi bi-exclamation-circle me-2"></i><?= $error ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success rounded-3"><i class="bi bi-check-circle me-2"></i><?= $success ?></div>
                    <?php endif; ?>
                    
                    <div class="text-center mb-4">
                        <img src="uploads/<?= sanitize($user['profile_photo']) ?>" 
                             class="rounded-circle border border-3 border-light shadow"
                             style="width: 150px; height: 150px; object-fit: cover;"
                             alt="Profile"
                             onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($user['name']) ?>&size=150&background=764ba2&color=fff'">
                        <div class="mt-2">
                            <span class="badge <?= $user['account_type'] === 'premium' ? 'badge-premium' : 'badge-free' ?>">
                                <i class="bi <?= $user['account_type'] === 'premium' ? 'bi-star-fill' : 'bi-person' ?> me-1"></i>
                                <?= ucfirst($user['account_type']) ?> Account
                            </span>
                        </div>
                    </div>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-500">Full Name</label>
                                <input type="text" name="name" class="form-control form-control-dating" value="<?= sanitize($user['name']) ?>" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-500">Age</label>
                                <input type="number" name="age" class="form-control form-control-dating" min="18" value="<?= $user['age'] ?>" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-500">Gender</label>
                                <select name="gender" class="form-select form-control-dating" required>
                                    <option value="male" <?= $user['gender'] === 'male' ? 'selected' : '' ?>>Male</option>
                                    <option value="female" <?= $user['gender'] === 'female' ? 'selected' : '' ?>>Female</option>
                                    <option value="other" <?= $user['gender'] === 'other' ? 'selected' : '' ?>>Other</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-500">Bio</label>
                            <textarea name="bio" class="form-control form-control-dating" rows="4"><?= sanitize($user['bio']) ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-500">Profile Photo</label>
                            <input type="file" name="profile_photo" class="form-control form-control-dating" accept="image/*">
                            <small class="text-muted">Leave empty to keep current photo</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-500">Email</label>
                            <input type="email" class="form-control form-control-dating" value="<?= sanitize($user['email']) ?>" disabled>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-500">Latitude</label>
                                <input type="text" class="form-control form-control-dating" value="<?= $user['latitude'] ?? 'Not set' ?>" disabled>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-500">Longitude</label>
                                <input type="text" class="form-control form-control-dating" value="<?= $user['longitude'] ?? 'Not set' ?>" disabled>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-gradient mt-2">
                            <i class="bi bi-check-circle me-2"></i>Update Profile
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
