<?php
require_once 'includes/config.php';
require_once 'includes/init_db.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$pageTitle = 'LoveConnect - Find Your Match';
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
        .hero-section {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        .btn-hero {
            border-radius: 25px;
            padding: 12px 35px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        .btn-hero:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .feature-card {
            border: none;
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
        }
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.12);
        }
        .feature-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 1.8rem;
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero-section text-white">
        <div class="floating-hearts">
            <i class="bi bi-heart-fill" style="top:10%; left:10%; font-size:2rem; animation-delay:0s;"></i>
            <i class="bi bi-heart-fill" style="top:20%; left:80%; font-size:1.5rem; animation-delay:1s;"></i>
            <i class="bi bi-heart-fill" style="top:60%; left:15%; font-size:1.8rem; animation-delay:2s;"></i>
            <i class="bi bi-heart-fill" style="top:70%; left:75%; font-size:2.2rem; animation-delay:0.5s;"></i>
            <i class="bi bi-heart-fill" style="top:40%; left:50%; font-size:1.3rem; animation-delay:1.5s;"></i>
            <i class="bi bi-heart-fill" style="top:85%; left:40%; font-size:1.6rem; animation-delay:3s;"></i>
        </div>
        <div class="container text-center position-relative">
            <h1 class="display-3 fw-bold mb-4">Find Your Perfect Match</h1>
            <p class="lead mb-5 fs-4 opacity-90">Connect with amazing people near you. Start your love story today.</p>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="register.php" class="btn btn-light btn-hero text-purple">
                    <i class="bi bi-heart-fill me-2"></i>Get Started
                </a>
                <a href="login.php" class="btn btn-outline-light btn-hero">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                </a>
            </div>
            <div class="mt-5 pt-4">
                <div class="row justify-content-center">
                    <div class="col-auto text-center px-4">
                        <h3 class="fw-bold mb-0">10K+</h3>
                        <small class="opacity-75">Active Users</small>
                    </div>
                    <div class="col-auto text-center px-4">
                        <h3 class="fw-bold mb-0">5K+</h3>
                        <small class="opacity-75">Matches Made</small>
                    </div>
                    <div class="col-auto text-center px-4">
                        <h3 class="fw-bold mb-0">98%</h3>
                        <small class="opacity-75">Satisfaction</small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5 my-5">
        <div class="container">
            <h2 class="text-center fw-bold mb-2">Why LoveConnect?</h2>
            <p class="text-center text-muted mb-5">Everything you need to find your soulmate</p>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card bg-white h-100">
                        <div class="feature-icon" style="background: linear-gradient(135deg, #667eea22, #764ba222);">
                            <i class="bi bi-geo-alt-fill text-purple"></i>
                        </div>
                        <h5 class="fw-bold">Nearby Matches</h5>
                        <p class="text-muted">Find people near you with our smart location-based matching system.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card bg-white h-100">
                        <div class="feature-icon" style="background: linear-gradient(135deg, #f093fb22, #f5576c22);">
                            <i class="bi bi-chat-heart-fill" style="color: #f5576c;"></i>
                        </div>
                        <h5 class="fw-bold">Real-time Chat</h5>
                        <p class="text-muted">Connect instantly with your matches through our seamless messaging system.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card bg-white h-100">
                        <div class="feature-icon" style="background: linear-gradient(135deg, #f5af1922, #f1271122);">
                            <i class="bi bi-shield-fill-check" style="color: #f5af19;"></i>
                        </div>
                        <h5 class="fw-bold">Safe & Secure</h5>
                        <p class="text-muted">Your privacy and safety are our top priority. Verified profiles only.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="container text-center text-white py-4">
            <h2 class="fw-bold mb-3">Ready to Find Love?</h2>
            <p class="mb-4 opacity-90">Join thousands of people who found their perfect match on LoveConnect</p>
            <a href="register.php" class="btn btn-light btn-hero text-purple">Create Free Account</a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-4 bg-dark text-white text-center">
        <p class="mb-0">&copy; 2024 LoveConnect. Made with <i class="bi bi-heart-fill text-danger"></i></p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
