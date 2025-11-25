<?php
session_start();

// Debug: Clear any existing session for testing (remove this in production)
// session_destroy();

// Check if user is actually logged in properly
$is_logged_in = false;
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && isset($_SESSION['user_id'])) {
    $is_logged_in = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WeMotions - Connect, Share, Feel</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95);
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            color: #667eea;
        }

        .logo span {
            color: #764ba2;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: #667eea;
        }

        .btn {
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-outline {
            border: 2px solid #667eea;
            color: #667eea;
        }

        .btn-outline:hover {
            background: #667eea;
            color: white;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #764ba2;
            transform: translateY(-2px);
        }

        .hero {
            padding: 120px 2rem 80px;
            text-align: center;
            color: white;
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
        }

        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            font-weight: bold;
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }

        .features {
            padding: 80px 2rem;
            background: white;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-title h2 {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 1rem;
        }

        .section-title p {
            color: #666;
            font-size: 1.1rem;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 10px;
            text-align: center;
            transition: transform 0.3s;
        }

        .feature-card:hover {
            transform: translateY(-5px);
        }

        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .feature-card h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #333;
        }

        .feature-card p {
            color: #666;
        }

        .cta {
            padding: 80px 2rem;
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            color: white;
            text-align: center;
        }

        .cta h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .cta p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .footer {
            background: #333;
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .user-greeting {
            background: #e6f3ff;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            color: #667eea;
            font-weight: 500;
        }

        /* Ensure buttons are visible for guests */
        .guest-buttons {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        @media (max-width: 768px) {
            .nav-links {
                gap: 1rem;
            }
            
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .guest-buttons {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">We<span>Motions</span></div>
            <div class="nav-links">
                <?php if ($is_logged_in): ?>
                    <!-- Show this for LOGGED IN users -->
                    <span class="user-greeting">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                    <a href="user/dashboard.php">Dashboard</a>
                    <a href="user/profile.php">Profile</a>
                    <a href="user/logout.php">Logout</a>
                <?php else: ?>
                    <!-- Show this for GUESTS (not logged in) -->
                    <div class="guest-buttons">
                        <a href="#features">Features</a>
                        <a href="#about">About</a>
                        <a href="user/login.php" class="btn btn-outline">Login</a>
                        <a href="user/register.php" class="btn btn-primary">Sign Up</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Connect, Share, and Feel Together</h1>
            <p>Join WeMotions - A community where emotions matter. Share your feelings, connect with others, and build meaningful relationships in a supportive environment.</p>
            
            <?php if (!$is_logged_in): ?>
                <!-- Show these buttons for GUESTS -->
                <div class="hero-buttons">
                    <a href="user/register.php" class="btn btn-primary" style="background: white; color: #667eea;">Get Started</a>
                    <a href="#features" class="btn btn-outline" style="border-color: white; color: white;">Learn More</a>
                </div>
            <?php else: ?>
                <!-- Show these buttons for LOGGED IN users -->
                <div class="hero-buttons">
                    <a href="user/dashboard.php" class="btn btn-primary" style="background: white; color: #667eea;">Go to Dashboard</a>
                    <a href="user/getusers.php" class="btn btn-outline" style="border-color: white; color: white;">Explore Community</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <div class="section-title">
                <h2>Why Join WeMotions?</h2>
                <p>Discover what makes our community special</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">💬</div>
                    <h3>Share Your Feelings</h3>
                    <p>Express yourself freely in a safe and supportive environment. Your emotions matter here.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">👥</div>
                    <h3>Connect with Others</h3>
                    <p>Meet like-minded people who understand and support you on your emotional journey.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🔒</div>
                    <h3>Safe & Private</h3>
                    <p>Your privacy is our priority. Share what you want, when you want, with who you want.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🌟</div>
                    <h3>Supportive Community</h3>
                    <p>Get and give support in a community that cares about mental and emotional well-being.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📱</div>
                    <h3>Easy to Use</h3>
                    <p>Simple, intuitive interface that lets you focus on what matters - connecting with others.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🚀</div>
                    <h3>Always Growing</h3>
                    <p>Join a rapidly growing community of people who believe in the power of emotional connection.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="cta" id="about">
        <div class="container">
            <h2>Ready to Start Your Journey?</h2>
            <p>Join thousands of users who have found connection and support through WeMotions.</p>
            <?php if (!$is_logged_in): ?>
                <a href="user/register.php" class="btn btn-primary" style="background: white; color: #667eea;">Join Now - It's Free!</a>
            <?php else: ?>
                <a href="user/dashboard.php" class="btn btn-primary" style="background: white; color: #667eea;">Continue to Dashboard</a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 WeMotions. All rights reserved. | Connect, Share, Feel Together</p>
            <p style="margin-top: 0.5rem; opacity: 0.8;">
                <a href="#" style="color: white; margin: 0 10px;">Privacy Policy</a> |
                <a href="#" style="color: white; margin: 0 10px;">Terms of Service</a> |
                <a href="#" style="color: white; margin: 0 10px;">Contact Us</a>
            </p>
        </div>
    </footer>

    <script>
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add scroll effect to navbar
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 100) {
                navbar.style.background = 'rgba(255, 255, 255, 0.98)';
            } else {
                navbar.style.background = 'rgba(255, 255, 255, 0.95)';
            }
        });

        // Debug: Show login status
        console.log('User logged in: <?php echo $is_logged_in ? "true" : "false"; ?>');
    </script>
</body>
</html>