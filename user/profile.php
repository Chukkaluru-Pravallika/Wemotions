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
$sql = "SELECT * FROM user WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Handle profile updates
$update_message = "";
$update_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $display_name = trim($_POST['display_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $bio = trim($_POST['bio'] ?? '');

    // Update user profile
    $update_sql = "UPDATE user SET display_name = ?, phone = ?, bio = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("sssi", $display_name, $phone, $bio, $user_id);

    if ($stmt->execute()) {
        $update_message = "Profile updated successfully!";
        // Refresh user data
        $sql = "SELECT * FROM user WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
    } else {
        $update_error = "Error updating profile: " . $stmt->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - WeMotions</title>
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

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            display: inline-block;
            text-align: center;
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
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            text-align: center;
            position: relative;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #667eea;
            font-size: 3rem;
            font-weight: bold;
            margin: 0 auto 1rem;
            border: 5px solid white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .profile-name {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }

        .profile-username {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 1rem;
        }

        .profile-stats {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-top: 1.5rem;
        }

        .stat {
            text-align: center;
        }

        .stat-number {
            font-size: 1.5rem;
            font-weight: bold;
            display: block;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .profile-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
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

        .info-grid {
            display: grid;
            gap: 1rem;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }

        .info-label {
            font-weight: bold;
            color: #666;
            min-width: 120px;
        }

        .info-value {
            color: #333;
            flex: 1;
            text-align: right;
        }

        .bio-content {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            border-left: 4px solid #28a745;
            font-style: italic;
            line-height: 1.8;
        }

        .empty-bio {
            color: #666;
            font-style: italic;
            text-align: center;
            padding: 2rem;
            background: #f8f9fa;
            border-radius: 8px;
            border: 2px dashed #ddd;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e9ecef;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        .message {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            text-align: center;
        }

        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .badge {
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .badge-success {
            background: #28a745;
            color: white;
        }

        .badge-warning {
            background: #ffc107;
            color: #212529;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        @media (max-width: 768px) {
            .profile-content {
                grid-template-columns: 1fr;
            }
            
            .profile-stats {
                flex-direction: column;
                gap: 1rem;
            }
            
            .nav-links {
                gap: 1rem;
            }
            
            .profile-name {
                font-size: 2rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
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
            <a href="dashboard.php">Dashboard</a>
            <a href="getusers.php">Community</a>
            <div class="user-menu">
                <span>Welcome, <?php echo htmlspecialchars($user['display_name'] ?: $user['username']); ?></span>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-avatar">
                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
            </div>
            <h1 class="profile-name"><?php echo htmlspecialchars($user['display_name'] ?: $user['username']); ?></h1>
            <div class="profile-username">@<?php echo htmlspecialchars($user['username']); ?></div>
            
            <div class="profile-stats">
                <div class="stat">
                    <span class="stat-number">1</span>
                    <span class="stat-label">Profile</span>
                </div>
                <div class="stat">
                    <span class="stat-number">0</span>
                    <span class="stat-label">Connections</span>
                </div>
                <div class="stat">
                    <span class="stat-number">0</span>
                    <span class="stat-label">Posts</span>
                </div>
                <div class="stat">
                    <span class="stat-number"><?php echo date('M Y', strtotime($user['created_at'])); ?></span>
                    <span class="stat-label">Member Since</span>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <?php if ($update_message): ?>
            <div class="message success"><?php echo $update_message; ?></div>
        <?php endif; ?>

        <?php if ($update_error): ?>
            <div class="message error"><?php echo $update_error; ?></div>
        <?php endif; ?>

        <div class="profile-content">
            <!-- Profile Information -->
            <div class="card">
                <h2>📋 Profile Information</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Username:</span>
                        <span class="info-value">@<?php echo htmlspecialchars($user['username']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Email:</span>
                        <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Phone:</span>
                        <span class="info-value"><?php echo htmlspecialchars($user['phone'] ?: 'Not set'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Account Status:</span>
                        <span class="info-value">
                            <?php echo $user['is_verified'] ? 'Verified' : 'Not Verified'; ?>
                            <span class="badge <?php echo $user['is_verified'] ? 'badge-success' : 'badge-warning'; ?>">
                                <?php echo $user['is_verified'] ? '✓' : '✗'; ?>
                            </span>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Member Since:</span>
                        <span class="info-value"><?php echo date('F j, Y', strtotime($user['created_at'])); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Last Updated:</span>
                        <span class="info-value"><?php echo date('F j, Y \a\t g:i A', strtotime($user['updated_at'])); ?></span>
                    </div>
                </div>
            </div>

            <!-- Bio Section -->
            <div class="card">
                <h2>📖 About Me</h2>
                <?php if ($user['bio']): ?>
                    <div class="bio-content">
                        "<?php echo htmlspecialchars($user['bio']); ?>"
                    </div>
                <?php else: ?>
                    <div class="empty-bio">
                        <p>You haven't added a bio yet.</p>
                        <p>Tell the community about yourself!</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Edit Profile Form -->
            <div class="card" style="grid-column: 1 / -1;">
                <h2>✏️ Edit Profile</h2>
                <form method="POST" action="">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="display_name">Display Name</label>
                            <input type="text" id="display_name" name="display_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['display_name'] ?? ''); ?>" 
                                   placeholder="Enter your display name">
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" 
                                   placeholder="Enter your phone number">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="bio">Bio</label>
                        <textarea id="bio" name="bio" class="form-control" 
                                  placeholder="Tell the community about yourself..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                    </div>
                    <div class="action-buttons">
                        <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                        <a href="dashboard.php" class="btn btn-outline">Back to Dashboard</a>
                        <a href="getusers.php" class="btn btn-outline">Browse Community</a>
                    </div>
                </form>
            </div>

            <!-- Account Security -->
            <div class="card">
                <h2>🔒 Account Security</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Password:</span>
                        <span class="info-value">
                            ••••••••
                            <a href="change-password.php" style="margin-left: 10px; color: #667eea; text-decoration: none;">Change</a>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Last Login:</span>
                        <span class="info-value">Recently</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <h2>⚡ Quick Actions</h2>
                <div class="action-buttons">
                    <a href="dashboard.php" class="btn btn-primary">Dashboard</a>
                    <a href="getusers.php" class="btn btn-outline">Community</a>
                    <a href="../index.php" class="btn btn-outline">Homepage</a>
                    <a href="logout.php" class="btn btn-danger">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add some interactivity
        document.addEventListener('DOMContentLoaded', function() {
            // Animate profile cards on load
            const cards = document.querySelectorAll('.card');
            cards.forEach((card, index) => {
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

            // Character counter for bio
            const bioTextarea = document.getElementById('bio');
            if (bioTextarea) {
                const charCount = document.createElement('div');
                charCount.style.textAlign = 'right';
                charCount.style.fontSize = '0.8rem';
                charCount.style.color = '#666';
                charCount.style.marginTop = '0.5rem';
                bioTextarea.parentNode.appendChild(charCount);

                function updateCharCount() {
                    const length = bioTextarea.value.length;
                    charCount.textContent = `${length}/500 characters`;
                    
                    if (length > 450) {
                        charCount.style.color = '#e74c3c';
                    } else if (length > 400) {
                        charCount.style.color = '#f39c12';
                    } else {
                        charCount.style.color = '#666';
                    }
                }

                bioTextarea.addEventListener('input', updateCharCount);
                updateCharCount(); // Initial count
            }
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>