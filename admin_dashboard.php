<?php
require_once 'includes/config.php';
require_once 'includes/init_db.php';
requireAdmin();

$db = getDB();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = intval($_POST['user_id'] ?? 0);
    
    if ($userId) {
        switch ($action) {
            case 'delete':
                $stmt = $db->prepare("DELETE FROM messages WHERE sender_id = ? OR receiver_id = ?");
                $stmt->execute([$userId, $userId]);
                $stmt = $db->prepare("DELETE FROM users WHERE id = ? AND email != 'Romar'");
                $stmt->execute([$userId]);
                break;
            case 'upgrade':
                $stmt = $db->prepare("UPDATE users SET account_type = 'premium' WHERE id = ?");
                $stmt->execute([$userId]);
                break;
            case 'downgrade':
                $stmt = $db->prepare("UPDATE users SET account_type = 'free' WHERE id = ?");
                $stmt->execute([$userId]);
                break;
        }
    }
    
    header('Location: admin_dashboard.php');
    exit;
}

// Get statistics
$totalUsers = $db->query("SELECT COUNT(*) FROM users WHERE email != 'Romar'")->fetchColumn();
$premiumUsers = $db->query("SELECT COUNT(*) FROM users WHERE account_type = 'premium' AND email != 'Romar'")->fetchColumn();
$freeUsers = $db->query("SELECT COUNT(*) FROM users WHERE account_type = 'free' AND email != 'Romar'")->fetchColumn();
$totalMessages = $db->query("SELECT COUNT(*) FROM messages")->fetchColumn();

// Get all users
$stmt = $db->query("SELECT * FROM users WHERE email != 'Romar' ORDER BY created_at DESC");
$users = $stmt->fetchAll();

$pageTitle = 'Admin Dashboard - LoveConnect';
include 'includes/header.php';
?>

<div class="container py-4">
    <h3 class="fw-bold mb-4"><i class="bi bi-speedometer2 me-2"></i>Admin Dashboard</h3>
    
    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                <i class="bi bi-people-fill fs-3"></i>
                <h2 class="fw-bold mb-0 mt-2"><?= $totalUsers ?></h2>
                <small>Total Users</small>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #f5af19, #f12711);">
                <i class="bi bi-star-fill fs-3"></i>
                <h2 class="fw-bold mb-0 mt-2"><?= $premiumUsers ?></h2>
                <small>Premium Users</small>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #11998e, #38ef7d);">
                <i class="bi bi-person-fill fs-3"></i>
                <h2 class="fw-bold mb-0 mt-2"><?= $freeUsers ?></h2>
                <small>Free Users</small>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                <i class="bi bi-chat-dots-fill fs-3"></i>
                <h2 class="fw-bold mb-0 mt-2"><?= $totalMessages ?></h2>
                <small>Total Messages</small>
            </div>
        </div>
    </div>
    
    <!-- Users Table -->
    <div class="card card-dating">
        <div class="card-header bg-white p-3 border-0">
            <h5 class="fw-bold mb-0"><i class="bi bi-people me-2"></i>All Users</h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($users)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-people fs-1 text-muted"></i>
                    <p class="text-muted mt-2">No registered users yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Gender</th>
                                <th>Age</th>
                                <th>Account</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="uploads/<?= sanitize($user['profile_photo']) ?>" 
                                                 class="rounded-circle me-2"
                                                 style="width: 40px; height: 40px; object-fit: cover;"
                                                 onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($user['name']) ?>&size=40&background=764ba2&color=fff'">
                                            <span class="fw-500"><?= sanitize($user['name']) ?></span>
                                        </div>
                                    </td>
                                    <td><?= sanitize($user['email']) ?></td>
                                    <td><?= ucfirst(sanitize($user['gender'])) ?></td>
                                    <td><?= $user['age'] ?></td>
                                    <td>
                                        <span class="badge <?= $user['account_type'] === 'premium' ? 'badge-premium' : 'badge-free' ?>">
                                            <?= ucfirst($user['account_type']) ?>
                                        </span>
                                    </td>
                                    <td><small><?= date('M j, Y', strtotime($user['created_at'])) ?></small></td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <?php if ($user['account_type'] === 'free'): ?>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                    <input type="hidden" name="action" value="upgrade">
                                                    <button type="submit" class="btn btn-sm btn-warning rounded-pill" title="Upgrade to Premium">
                                                        <i class="bi bi-star-fill"></i>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                    <input type="hidden" name="action" value="downgrade">
                                                    <button type="submit" class="btn btn-sm btn-secondary rounded-pill" title="Downgrade to Free">
                                                        <i class="bi bi-star"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <button type="submit" class="btn btn-sm btn-danger rounded-pill" title="Delete User">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
