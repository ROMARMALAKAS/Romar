<?php
require_once 'includes/config.php';
require_once 'includes/init_db.php';
requireLogin();

$pageTitle = 'Upgrade - LoveConnect';
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold">Upgrade to Premium</h2>
        <p class="text-muted">Unlock all features and find your match faster</p>
    </div>
    
    <div class="row justify-content-center g-4">
        <!-- Free Plan -->
        <div class="col-md-5 col-lg-4">
            <div class="card card-dating h-100 text-center">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <i class="bi bi-person-circle fs-1 text-muted"></i>
                    </div>
                    <h4 class="fw-bold">Free</h4>
                    <h2 class="fw-bold text-muted">$0<small class="fs-6">/month</small></h2>
                    <hr>
                    <ul class="list-unstyled text-start">
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>View profiles</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Send messages</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>See distance</li>
                        <li class="mb-2"><i class="bi bi-x-circle-fill text-danger me-2"></i>Send photos in chat</li>
                        <li class="mb-2"><i class="bi bi-x-circle-fill text-danger me-2"></i>Priority visibility</li>
                        <li class="mb-2"><i class="bi bi-x-circle-fill text-danger me-2"></i>Unlimited likes</li>
                    </ul>
                    <button class="btn btn-outline-secondary w-100 mt-3 rounded-pill" disabled>Current Plan</button>
                </div>
            </div>
        </div>
        
        <!-- Premium Plan -->
        <div class="col-md-5 col-lg-4">
            <div class="card card-dating h-100 text-center border-0" style="background: linear-gradient(135deg, #667eea08, #764ba208); border: 2px solid #764ba2 !important;">
                <div class="card-body p-4">
                    <span class="badge badge-premium mb-3">MOST POPULAR</span>
                    <div class="mb-3">
                        <i class="bi bi-star-fill fs-1" style="color: #f5af19;"></i>
                    </div>
                    <h4 class="fw-bold">Premium</h4>
                    <h2 class="fw-bold" style="color: #764ba2;">$9.99<small class="fs-6">/month</small></h2>
                    <hr>
                    <ul class="list-unstyled text-start">
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>View profiles</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Send messages</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>See distance</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i><strong>Send photos in chat</strong></li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i><strong>Priority visibility</strong></li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i><strong>Unlimited likes</strong></li>
                    </ul>
                    <button class="btn btn-gradient w-100 mt-3" data-bs-toggle="modal" data-bs-target="#contactModal">
                        <i class="bi bi-star-fill me-2"></i>Upgrade Now
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Contact Admin Modal -->
<div class="modal fade" id="contactModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px;">
            <div class="modal-body text-center p-5">
                <i class="bi bi-info-circle-fill fs-1 text-primary mb-3"></i>
                <h5 class="fw-bold">Contact Admin</h5>
                <p class="text-muted">To upgrade to Premium, please contact our admin. They will activate your premium account.</p>
                <button class="btn btn-gradient" data-bs-dismiss="modal">Got it!</button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
