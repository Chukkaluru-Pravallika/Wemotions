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
$sql = "SELECT username, display_name FROM user WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Get all posts from the post table
$posts_sql = "SELECT * FROM post ORDER BY created_at DESC";
$posts_result = $conn->query($posts_sql);

// Debug: Check if we're getting any posts
$post_count = $posts_result->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Posts - WeMotions</title>
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

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            display: inline-block;
            text-align: center;
            font-size: 1rem;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #764ba2;
            transform: translateY(-2px);
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

        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            text-align: center;
        }

        .page-title {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }

        .page-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .posts-container {
            display: grid;
            gap: 1.5rem;
        }
        
        .post-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #667eea;
        }
        
        .post-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .post-user {
            display: flex;
            align-items: center;
            gap: 0.5rem;
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
        
        .post-meta {
            color: #666;
            font-size: 0.9rem;
        }
        
        .post-title {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #333;
        }
        
        .post-content {
            margin-bottom: 1rem;
            line-height: 1.6;
            white-space: pre-line;
        }
        
        .post-image {
            max-width: 100%;
            border-radius: 8px;
            margin-bottom: 1rem;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .post-actions {
            display: flex;
            gap: 1rem;
            border-top: 1px solid #e9ecef;
            padding-top: 1rem;
        }
        
        .action-btn {
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: color 0.3s;
            padding: 0.5rem;
        }
        
        .action-btn:hover {
            color: #667eea;
        }
        
        .no-posts {
            text-align: center;
            padding: 3rem;
            color: #666;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .debug-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            font-family: monospace;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .nav-links {
                gap: 1rem;
            }
            
            .page-title {
                font-size: 2rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
            
            .post-actions {
                flex-wrap: wrap;
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
            <a href="create-post.php">Create Post</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </nav>

    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Community Posts</h1>
            <p class="page-subtitle">See what everyone is sharing</p>
        </div>

        <!-- Debug Information -->
        <div class="debug-info">
            <strong>Debug Info:</strong><br>
            Posts found in database: <?php echo $post_count; ?><br>
            User: <?php echo htmlspecialchars($user['username']); ?><br>
            User ID: <?php echo $user_id; ?>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons" style="margin-bottom: 2rem; display: flex; gap: 1rem;">
            <a href="create-post.php" class="btn btn-primary">➕ Create New Post</a>
            <a href="dashboard.php" class="btn btn-outline">📊 Dashboard</a>
        </div>

        <!-- Posts Container -->
        <div class="posts-container">
            <?php if ($posts_result && $posts_result->num_rows > 0): ?>
                <?php while($post = $posts_result->fetch_assoc()): ?>
                    <div class="post-card">
                        <div class="post-header">
                            <div class="post-user">
                                <div class="user-avatar">
                                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                </div>
                                <div>
                                    <strong><?php echo htmlspecialchars($user['display_name'] ?: $user['username']); ?></strong>
                                    <div class="post-meta">
                                        <?php echo date('F j, Y \a\t g:i A', strtotime($post['created_at'])); ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($post['title'])): ?>
                            <div class="post-title"><?php echo htmlspecialchars($post['title']); ?></div>
                        <?php endif; ?>

                        <div class="post-content">
                            <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                        </div>

                        <?php if (!empty($post['image'])): ?>
                            <img src="../<?php echo htmlspecialchars($post['image']); ?>" 
                                 alt="Post image" class="post-image"
                                 onerror="this.style.display='none'">
                        <?php endif; ?>

                        <div class="post-actions">
                            <button class="action-btn">❤️ Like</button>
                            <button class="action-btn">💬 Comment</button>
                            <button class="action-btn">🔄 Share</button>
                            <button class="action-btn" onclick="copyPostLink(<?php echo $post['id']; ?>)">🔗 Copy Link</button>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-posts">
                    <h3>No posts yet</h3>
                    <p>Be the first to share something with the community!</p>
                    <a href="create-post.php" class="btn btn-primary" style="margin-top: 1rem;">Create Your First Post</a>
                    
                    <!-- Debug help -->
                    <div style="margin-top: 2rem; padding: 1rem; background: #f8f9fa; border-radius: 5px;">
                        <strong>Debug Help:</strong>
                        <p>If you just created a post but don't see it here:</p>
                        <ul style="text-align: left; margin: 0.5rem 0;">
                            <li>Check if the post was saved to the database</li>
                            <li>Verify the post table has data</li>
                            <li>Check for any error messages</li>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function copyPostLink(postId) {
            const link = window.location.origin + '/Wemotions/user/view-post.php?id=' + postId;
            navigator.clipboard.writeText(link).then(function() {
                alert('Post link copied to clipboard!');
            }, function() {
                alert('Failed to copy link. Please copy manually: ' + link);
            });
        }

        // Add some interactivity
        document.addEventListener('DOMContentLoaded', function() {
            const likeButtons = document.querySelectorAll('.action-btn');
            likeButtons.forEach(btn => {
                if (btn.textContent.includes('Like')) {
                    btn.addEventListener('click', function() {
                        this.textContent = this.textContent === '❤️ Like' ? '❤️ Liked' : '❤️ Like';
                    });
                }
            });
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>