<?php
require_once 'includes/config.php';
require_once 'includes/init_db.php';
requireLogin();

$db = getDB();

// Get current user
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$currentUser = $stmt->fetch();

// Get all other users (exclude admin and self)
$stmt = $db->prepare("SELECT * FROM users WHERE id != ? AND email != 'Romar' ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$users = $stmt->fetchAll();

$pageTitle = 'Dashboard - LoveConnect';
include 'includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Discover People</h3>
            <p class="text-muted mb-0">Find someone special near you</p>
        </div>
        <span class="badge <?= $currentUser['account_type'] === 'premium' ? 'badge-premium' : 'badge-free' ?>">
            <i class="bi <?= $currentUser['account_type'] === 'premium' ? 'bi-star-fill' : 'bi-person' ?> me-1"></i>
            <?= ucfirst($currentUser['account_type']) ?>
        </span>
    </div>
    
    <?php if (empty($users)): ?>
        <div class="text-center py-5">
            <i class="bi bi-people fs-1 text-muted"></i>
            <h5 class="mt-3 text-muted">No users found yet</h5>
            <p class="text-muted">Be the first to invite your friends!</p>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($users as $user): ?>
                <?php
                $distance = calculateDistance(
                    $currentUser['latitude'], $currentUser['longitude'],
                    $user['latitude'], $user['longitude']
                );
                ?>
                <div class="col-sm-6 col-lg-4 col-xl-3">
                    <div class="card card-dating h-100">
                        <img src="uploads/<?= sanitize($user['profile_photo']) ?>" 
                             class="profile-card-img" 
                             alt="<?= sanitize($user['name']) ?>"
                             onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($user['name']) ?>&size=250&background=764ba2&color=fff'">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title fw-bold mb-0">
                                    <?= sanitize($user['name']) ?>, <?= $user['age'] ?>
                                </h5>
                                <?php if ($user['account_type'] === 'premium'): ?>
                                    <i class="bi bi-patch-check-fill text-warning" title="Premium"></i>
                                <?php endif; ?>
                            </div>
                            <p class="text-muted small mb-2">
                                <i class="bi bi-gender-ambiguous me-1"></i><?= ucfirst(sanitize($user['gender'])) ?>
                            </p>
                            <p class="card-text small text-muted mb-2">
                                <?= strlen($user['bio']) > 80 ? substr(sanitize($user['bio']), 0, 80) . '...' : sanitize($user['bio']) ?>
                            </p>
                            <?php if ($distance !== null): ?>
                                <span class="distance-badge">
                                    <i class="bi bi-geo-alt me-1"></i><?= $distance ?> km away
                                </span>
                            <?php else: ?>
                                <span class="distance-badge">
                                    <i class="bi bi-geo-alt me-1"></i>Location unknown
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-white border-0 p-3 pt-0">
                            <a href="chat.php?user=<?= $user['id'] ?>" class="btn btn-gradient w-100 py-2">
                                <i class="bi bi-chat-heart me-1"></i> Message
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Location Update Script -->
<script>
if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(position) {
        fetch('update_location.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'latitude=' + position.coords.latitude + '&longitude=' + position.coords.longitude
        });
    });
}
</script>

<?php include 'includes/footer.php'; ?>
