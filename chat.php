<?php
require_once 'includes/config.php';
require_once 'includes/init_db.php';
requireLogin();

$db = getDB();

// Get current user
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$currentUser = $stmt->fetch();

$selectedUserId = intval($_GET['user'] ?? 0);
$selectedUser = null;
$messages = [];

// Get conversation list
$stmt = $db->prepare("
    SELECT DISTINCT 
        CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END as other_user_id
    FROM messages 
    WHERE sender_id = ? OR receiver_id = ?
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
$conversationIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Add selected user if not in list
if ($selectedUserId && !in_array($selectedUserId, $conversationIds)) {
    $conversationIds[] = $selectedUserId;
}

$conversations = [];
foreach ($conversationIds as $userId) {
    $stmt = $db->prepare("SELECT id, name, profile_photo, account_type FROM users WHERE id = ? AND email != 'Romar'");
    $stmt->execute([$userId]);
    $u = $stmt->fetch();
    if ($u) {
        // Get last message
        $stmt2 = $db->prepare("
            SELECT message, created_at FROM messages 
            WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmt2->execute([$_SESSION['user_id'], $userId, $userId, $_SESSION['user_id']]);
        $lastMsg = $stmt2->fetch();
        $u['last_message'] = $lastMsg ? $lastMsg['message'] : '';
        $u['last_time'] = $lastMsg ? $lastMsg['created_at'] : '';
        $conversations[] = $u;
    }
}

// Get selected user and messages
if ($selectedUserId) {
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$selectedUserId]);
    $selectedUser = $stmt->fetch();
    
    if ($selectedUser) {
        $stmt = $db->prepare("
            SELECT * FROM messages 
            WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
            ORDER BY created_at ASC
        ");
        $stmt->execute([$_SESSION['user_id'], $selectedUserId, $selectedUserId, $_SESSION['user_id']]);
        $messages = $stmt->fetchAll();
    }
}

// Handle message send
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $selectedUserId) {
    $message = trim($_POST['message'] ?? '');
    $photo = null;
    
    // Handle photo upload
    if (isset($_FILES['chat_photo']) && $_FILES['chat_photo']['error'] === UPLOAD_ERR_OK) {
        if ($currentUser['account_type'] !== 'premium') {
            // Free user - can't send photos
            header("Location: chat.php?user=$selectedUserId&error=upgrade");
            exit;
        }
        $upload = uploadImage($_FILES['chat_photo'], 'uploads/chat/');
        if ($upload['success']) {
            $photo = $upload['filename'];
        }
    }
    
    if (!empty($message) || $photo) {
        $stmt = $db->prepare("INSERT INTO messages (sender_id, receiver_id, message, photo) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $selectedUserId, $message, $photo]);
    }
    
    header("Location: chat.php?user=$selectedUserId");
    exit;
}

$showUpgradeModal = isset($_GET['error']) && $_GET['error'] === 'upgrade';

$pageTitle = 'Chat - LoveConnect';
include 'includes/header.php';
?>

<div class="container-fluid py-0" style="height: calc(100vh - 56px);">
    <div class="row h-100">
        <!-- Conversation List -->
        <div class="col-md-4 col-lg-3 border-end p-0 bg-white <?= $selectedUserId ? 'd-none d-md-block' : '' ?>" style="overflow-y: auto; height: 100%;">
            <div class="p-3 border-bottom">
                <h5 class="fw-bold mb-0"><i class="bi bi-chat-heart-fill me-2"></i>Messages</h5>
            </div>
            <?php if (empty($conversations)): ?>
                <div class="text-center py-5 px-3">
                    <i class="bi bi-chat-dots fs-1 text-muted"></i>
                    <p class="text-muted mt-2">No conversations yet.<br>Visit the dashboard to start chatting!</p>
                </div>
            <?php else: ?>
                <?php foreach ($conversations as $conv): ?>
                    <a href="chat.php?user=<?= $conv['id'] ?>" class="text-decoration-none">
                        <div class="d-flex align-items-center p-3 border-bottom <?= $selectedUserId == $conv['id'] ? 'bg-light' : '' ?>" style="transition: background 0.2s;">
                            <img src="uploads/<?= sanitize($conv['profile_photo']) ?>" 
                                 class="rounded-circle me-3" 
                                 style="width: 50px; height: 50px; object-fit: cover;"
                                 onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($conv['name']) ?>&size=50&background=764ba2&color=fff'">
                            <div class="flex-grow-1 overflow-hidden">
                                <h6 class="mb-0 fw-600 text-dark"><?= sanitize($conv['name']) ?></h6>
                                <small class="text-muted text-truncate d-block"><?= sanitize(substr($conv['last_message'], 0, 30)) ?><?= strlen($conv['last_message']) > 30 ? '...' : '' ?></small>
                            </div>
                            <?php if ($conv['last_time']): ?>
                                <small class="text-muted"><?= date('H:i', strtotime($conv['last_time'])) ?></small>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Chat Area -->
        <div class="col-md-8 col-lg-9 p-0 d-flex flex-column <?= !$selectedUserId ? 'd-none d-md-flex' : '' ?>" style="height: 100%;">
            <?php if ($selectedUser): ?>
                <!-- Chat Header -->
                <div class="p-3 border-bottom bg-white d-flex align-items-center">
                    <a href="chat.php" class="btn btn-light btn-sm rounded-circle me-2 d-md-none">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <img src="uploads/<?= sanitize($selectedUser['profile_photo']) ?>" 
                         class="rounded-circle me-3"
                         style="width: 45px; height: 45px; object-fit: cover;"
                         onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($selectedUser['name']) ?>&size=45&background=764ba2&color=fff'">
                    <div>
                        <h6 class="mb-0 fw-bold"><?= sanitize($selectedUser['name']) ?></h6>
                        <small class="text-muted"><?= ucfirst($selectedUser['gender']) ?>, <?= $selectedUser['age'] ?></small>
                    </div>
                </div>
                
                <!-- Messages -->
                <div class="flex-grow-1 p-3" style="overflow-y: auto; background: #f8f9fa;" id="chatMessages">
                    <?php if (empty($messages)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-chat-dots fs-1 text-muted"></i>
                            <p class="text-muted mt-2">No messages yet. Say hello!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($messages as $msg): ?>
                            <div class="d-flex <?= $msg['sender_id'] == $_SESSION['user_id'] ? 'justify-content-end' : 'justify-content-start' ?> mb-2">
                                <div class="chat-bubble <?= $msg['sender_id'] == $_SESSION['user_id'] ? 'chat-bubble-sent' : 'chat-bubble-received' ?>">
                                    <?php if ($msg['photo']): ?>
                                        <img src="uploads/chat/<?= sanitize($msg['photo']) ?>" class="img-fluid rounded mb-2" style="max-width: 200px;">
                                    <?php endif; ?>
                                    <?php if ($msg['message']): ?>
                                        <p class="mb-0"><?= sanitize($msg['message']) ?></p>
                                    <?php endif; ?>
                                    <div class="chat-time"><?= date('M j, g:i a', strtotime($msg['created_at'])) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Message Input -->
                <div class="p-3 border-top bg-white">
                    <form method="POST" enctype="multipart/form-data" class="d-flex gap-2 align-items-center">
                        <button type="button" class="btn btn-light rounded-circle" onclick="document.getElementById('chatPhotoInput').click();" title="Send Photo">
                            <i class="bi bi-image"></i>
                        </button>
                        <input type="file" id="chatPhotoInput" name="chat_photo" accept="image/*" class="d-none" onchange="this.form.submit();">
                        <input type="text" name="message" class="form-control rounded-pill" placeholder="Type a message..." autocomplete="off">
                        <button type="submit" class="btn btn-gradient rounded-circle" style="width: 45px; height: 45px; padding: 0;">
                            <i class="bi bi-send-fill"></i>
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <div class="d-flex align-items-center justify-content-center h-100">
                    <div class="text-center">
                        <i class="bi bi-chat-heart fs-1 text-muted"></i>
                        <h5 class="mt-3 text-muted">Select a conversation</h5>
                        <p class="text-muted">Choose someone to chat with</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Upgrade Modal -->
<div class="modal fade <?= $showUpgradeModal ? 'show' : '' ?>" id="upgradeModal" tabindex="-1" <?= $showUpgradeModal ? 'style="display:block;" aria-modal="true" role="dialog"' : '' ?>>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none;">
            <div class="modal-body text-center p-5">
                <div class="mb-4">
                    <i class="bi bi-lock-fill fs-1" style="color: #764ba2;"></i>
                </div>
                <h4 class="fw-bold mb-3">Upgrade Required</h4>
                <p class="text-muted mb-4">Upgrade your account to unlock photo sending. Premium members can send and receive photos in chat!</p>
                <div class="d-grid gap-2">
                    <a href="upgrade.php" class="btn btn-gradient-pink py-2">
                        <i class="bi bi-star-fill me-2"></i>Upgrade to Premium
                    </a>
                    <button type="button" class="btn btn-light" onclick="window.location.href='chat.php?user=<?= $selectedUserId ?>'">
                        Maybe Later
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($showUpgradeModal): ?>
<div class="modal-backdrop fade show"></div>
<?php endif; ?>

<script>
// Scroll to bottom of chat
var chatDiv = document.getElementById('chatMessages');
if (chatDiv) chatDiv.scrollTop = chatDiv.scrollHeight;
</script>

<?php include 'includes/footer.php'; ?>
