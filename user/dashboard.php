<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// Include database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "wemotions";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user data
$user_id = $_SESSION['user_id'];
$sql = "SELECT username, email, display_name, phone, bio, profile_picture, created_at, is_verified FROM user WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Get user stats
$stats_sql = "SELECT 
    (SELECT COUNT(*) FROM user) as total_users,
    (SELECT COUNT(*) FROM user WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) as new_this_week,
    (SELECT COUNT(*) FROM post) as total_posts,
    (SELECT COUNT(*) FROM post WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) as new_posts_this_week";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();

// Get recent posts
$recent_posts_sql = "SELECT * FROM post ORDER BY created_at DESC LIMIT 5";
$recent_posts_result = $conn->query($recent_posts_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - WeMotions</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }

        .navbar {
            background: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: #667eea;
        }

        .logo span {
            color: #764ba2;
        }

        .nav-links {
            display: flex;
            gap: 1.5rem;
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

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: #667eea;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #764ba2;
        }

        .btn-outline {
            border: 2px solid #667eea;
            color: #667eea;
            background: transparent;
        }

        .btn-outline:hover {
            background: #667eea;
            color: white;
        }

        .btn-danger {
            background: #e74c3c;
            color: white;
        }

        .btn-danger:hover {
            background: #c0392b;
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .welcome-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            text-align: center;
        }

        .welcome-section h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .welcome-section p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        /* Tabs Navigation */
        .tabs {
            display: flex;
            background: white;
            border-radius: 10px 10px 0 0;
            overflow: hidden;
            margin-bottom: 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .tab {
            padding: 1rem 2rem;
            background: #f8f9fa;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
            flex: 1;
            text-align: center;
        }

        .tab:hover {
            background: #e9ecef;
        }

        .tab.active {
            background: white;
            border-bottom: 3px solid #667eea;
            color: #667eea;
        }

        .tab-content {
            display: none;
            background: white;
            padding: 2rem;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .tab-content.active {
            display: block;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .card h2 {
            color: #333;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 0.5rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #667eea;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }

        .profile-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }

        .info-item {
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .info-label {
            font-weight: bold;
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .info-value {
            color: #333;
            font-size: 1.1rem;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .action-btn {
            padding: 1rem;
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            text-decoration: none;
            color: #333;
            text-align: center;
            transition: all 0.3s;
        }

        .action-btn:hover {
            border-color: #667eea;
            background: #667eea;
            color: white;
            transform: translateY(-2px);
        }

        .recent-activity {
            list-style: none;
        }

        .activity-item {
            padding: 1rem;
            border-left: 3px solid #667eea;
            background: #f8f9fa;
            margin-bottom: 0.5rem;
            border-radius: 0 5px 5px 0;
        }

        .activity-time {
            font-size: 0.8rem;
            color: #666;
            margin-top: 0.5rem;
        }

        .verified-badge {
            background: #27ae60;
            color: white;
            padding: 0.2rem 0.5rem;
            border-radius: 15px;
            font-size: 0.8rem;
            margin-left: 0.5rem;
        }

        .not-verified {
            background: #e74c3c;
        }

        /* Posts Styles */
        .posts-container {
            display: grid;
            gap: 1rem;
        }

        .post-card {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }

        .post-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .post-title {
            font-size: 1.2rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .post-content {
            color: #666;
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .post-meta {
            color: #999;
            font-size: 0.9rem;
        }

        .post-image {
            max-width: 100%;
            border-radius: 8px;
            margin: 1rem 0;
        }

        .no-posts {
            text-align: center;
            padding: 3rem;
            color: #666;
            background: #f8f9fa;
            border-radius: 8px;
        }

        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .tabs {
                flex-direction: column;
            }
            
            .nav-links {
                gap: 1rem;
            }
            
            .welcome-section h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="logo">We<span>Motions</span></div>
        <div class="nav-links">
            <a href="../index.php">Home</a>
            <a href="create-post.php">Posts</a>
            <div class="user-menu">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                </div>
                <span>Welcome, <?php echo htmlspecialchars($user['display_name'] ?: $user['username']); ?></span>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <h1>Welcome back, <?php echo htmlspecialchars($user['display_name'] ?: $user['username']); ?>! 👋</h1>
            <p>We're glad to see you again. Here's what's happening in your community.</p>
        </div>

        <!-- Tabs Navigation -->
        <div class="tabs">
            <button class="tab active" onclick="openTab('home')">🏠 Home</button>
            <button class="tab" onclick="openTab('posts')">📝 Posts</button>
            <button class="tab" onclick="openTab('profile')">👤 Profile</button>
        </div>

        <!-- Home Tab -->
        <div id="home" class="tab-content active">
            <!-- Stats Overview -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_users']; ?></div>
                    <div class="stat-label">Total Community Members</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['new_this_week']; ?></div>
                    <div class="stat-label">New Members This Week</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_posts']; ?></div>
                    <div class="stat-label">Total Posts</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['new_posts_this_week']; ?></div>
                    <div class="stat-label">New Posts This Week</div>
                </div>
            </div>

            <div class="dashboard-grid">
                <!-- Main Content -->
                <div class="main-content">


                    <!-- Recent Activity -->
                    <div class="card">
                        <h2>📈 Recent Activity</h2>
                        <ul class="recent-activity">
                            <li class="activity-item">
                                <strong>Account Created</strong>
                                <p>Welcome to WeMotions! Your journey begins here.</p>
                                <div class="activity-time"><?php echo date('F j, Y \a\t g:i A', strtotime($user['created_at'])); ?></div>
                            </li>
                            <li class="activity-item">
                                <strong>Profile Setup</strong>
                                <p>Your profile has been created successfully.</p>
                                <div class="activity-time"><?php echo date('F j, Y \a\t g:i A', strtotime($user['created_at'])); ?></div>
                            </li>
                            <li class="activity-item">
                                <strong>Community Access</strong>
                                <p>You now have access to our growing community.</p>
                                <div class="activity-time">Just now</div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="sidebar">
                    <!-- Quick Stats -->
                    <div class="card">
                        <h2>🎯 Quick Stats</h2>
                        <div style="text-align: center; padding: 1rem;">
                            <div style="font-size: 3rem; margin-bottom: 1rem;">🚀</div>
                            <h3>Your Journey</h3>
                            <p style="color: #666; margin-bottom: 1rem;">You're one of <?php echo $stats['total_users']; ?> amazing members!</p>
                            <a href="create-post.php" class="btn btn-primary" style="width: 100%;">Create Your First Post</a>
                        </div>
                    </div>

                    <!-- Community Tips -->
                    <div class="card">
                        <h2>💡 Community Tips</h2>
                        <div style="margin-top: 1rem;">
                            <div style="background: #e8f4fd; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                                <strong>Complete your profile</strong>
                                <p style="font-size: 0.9rem; margin-top: 0.5rem; color: #666;">Add a bio and profile picture to get more connections.</p>
                            </div>
                            <div style="background: #f0f8f0; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                                <strong>Explore the community</strong>
                                <p style="font-size: 0.9rem; margin-top: 0.5rem; color: #666;">Connect with other members who share similar interests.</p>
                            </div>
                            <div style="background: #fff8e1; padding: 1rem; border-radius: 8px;">
                                <strong>Stay active</strong>
                                <p style="font-size: 0.9rem; margin-top: 0.5rem; color: #666;">Regular engagement helps you build better connections.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Posts Tab -->
        <div id="posts" class="tab-content">
            <div class="card">
                <h2>📝 Recent Community Posts</h2>
                <div style="margin-bottom: 1.5rem;">
                    <a href="create-post.php" class="btn btn-primary">➕ Create New Post</a>
                    <a href="posts.php" class="btn btn-outline">📖 View All Posts</a>
                </div>

                <div class="posts-container">
                    <?php if ($recent_posts_result && $recent_posts_result->num_rows > 0): ?>
                        <?php while($post = $recent_posts_result->fetch_assoc()): ?>
                            <div class="post-card">
                                <div class="post-header">
                                    <div class="post-title"><?php echo htmlspecialchars($post['title'] ?: 'Untitled Post'); ?></div>
                                    <div class="post-meta">
                                        <?php echo date('M j, Y g:i A', strtotime($post['created_at'])); ?>
                                    </div>
                                </div>
                                
                                <div class="post-content">
                                    <?php echo nl2br(htmlspecialchars(substr($post['content'], 0, 200))); ?>
                                    <?php if (strlen($post['content']) > 200): ?>...<?php endif; ?>
                                </div>

                                <?php if (!empty($post['image'])): ?>
                                    <img src="../<?php echo htmlspecialchars($post['image']); ?>" 
                                         alt="Post image" class="post-image"
                                         onerror="this.style.display='none'">
                                <?php endif; ?>

                                <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                                    <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">❤️ Like</button>
                                    <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">💬 Comment</button>
                                    <button class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">🔄 Share</button>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="no-posts">
                            <h3>No posts yet</h3>
                            <p>Be the first to share something with the community!</p>
                            <a href="create-post.php" class="btn btn-primary" style="margin-top: 1rem;">Create Your First Post</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Profile Tab -->
        <div id="profile" class="tab-content">
            <div class="card">
                <h2>👤 Your Profile Information</h2>
                <div class="profile-info">
                    <div class="info-item">
                        <div class="info-label">Username</div>
                        <div class="info-value">@<?php echo htmlspecialchars($user['username']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Display Name</div>
                        <div class="info-value"><?php echo htmlspecialchars($user['display_name'] ?: 'Not set'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Account Status</div>
                        <div class="info-value">
                            <?php echo $user['is_verified'] ? 'Verified' : 'Not Verified'; ?>
                            <span class="verified-badge <?php echo $user['is_verified'] ? '' : 'not-verified'; ?>">
                                <?php echo $user['is_verified'] ? '✓' : '✗'; ?>
                            </span>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Phone</div>
                        <div class="info-value"><?php echo htmlspecialchars($user['phone'] ?: 'Not set'); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Member Since</div>
                        <div class="info-value"><?php echo date('F j, Y', strtotime($user['created_at'])); ?></div>
                    </div>
                </div>

                <?php if ($user['bio']): ?>
                <div style="margin-top: 1.5rem;">
                    <div class="info-label">Your Bio</div>
                    <div class="info-value" style="background: #f8f9fa; padding: 1rem; border-radius: 8px; font-style: italic;">
                        "<?php echo htmlspecialchars($user['bio']); ?>"
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Tab functionality
        function openTab(tabName) {
            // Hide all tab contents
            const tabContents = document.getElementsByClassName('tab-content');
            for (let i = 0; i < tabContents.length; i++) {
                tabContents[i].classList.remove('active');
            }

            // Remove active class from all tabs
            const tabs = document.getElementsByClassName('tab');
            for (let i = 0; i < tabs.length; i++) {
                tabs[i].classList.remove('active');
            }

            // Show the specific tab content and activate the tab
            document.getElementById(tabName).classList.add('active');
            event.currentTarget.classList.add('active');
        }

        // Add some interactivity
        document.addEventListener('DOMContentLoaded', function() {
            // Animate stat cards on load
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';
                    card.style.transition = 'all 0.5s ease';
                    
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 100);
                }, index * 100);
            });

            // Add click effects to action buttons
            const actionBtns = document.querySelectorAll('.action-btn');
            actionBtns.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 150);
                });
            });
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>