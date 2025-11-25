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

// Handle form submission
$success_message = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $emotion = $_POST['emotion'] ?? '';
    $visibility = $_POST['visibility'] ?? 'public';
    
    // Handle image upload
    $image_path = null;
    
    if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = $_FILES['post_image']['type'];
        $file_size = $_FILES['post_image']['size'];
        
        if (in_array($file_type, $allowed_types)) {
            if ($file_size <= 10 * 1024 * 1024) { // 10MB limit
                $upload_dir = "../uploads/posts/";
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_extension = pathinfo($_FILES['post_image']['name'], PATHINFO_EXTENSION);
                $filename = "post_" . $user_id . "_" . time() . "." . $file_extension;
                $destination = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['post_image']['tmp_name'], $destination)) {
                    $image_path = "uploads/posts/" . $filename;
                } else {
                    $error_message = "Failed to upload image.";
                }
            } else {
                $error_message = "Image must be less than 10MB.";
            }
        } else {
            $error_message = "Only JPG, PNG, GIF, and WebP images are allowed.";
        }
    }
    
    // Validate inputs
    if (empty($error_message)) {
        if (empty($title)) {
            $error_message = "Post title is required.";
        } elseif (empty($content)) {
            $error_message = "Post content is required.";
        } elseif (strlen($title) > 255) {
            $error_message = "Post title must be less than 255 characters.";
        } else {
            // Insert post using your existing table structure
            $insert_sql = "INSERT INTO post (title, content, image, created_at, updated_at) 
                          VALUES (?, ?, ?, NOW(), NOW())";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("sss", $title, $content, $image_path);
            
            if ($stmt->execute()) {
                $success_message = "Post created successfully!";
                // Clear form
                $_POST = array();
            } else {
                $error_message = "Error creating post: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Post - WeMotions</title>
    <style>
        /* Your existing CSS styles remain the same */
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

        .card h2 {
            color: #333;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 0.5rem;
        }

        .message {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1.5rem;
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
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 150px;
        }

        .file-upload {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .file-upload-input {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-upload-label {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            border: 2px dashed #e9ecef;
            border-radius: 5px;
            background: #f8f9fa;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }

        .file-upload-label:hover {
            border-color: #667eea;
            background: #f0f4ff;
        }

        .image-preview {
            margin-top: 1rem;
            text-align: center;
        }

        .image-preview img {
            max-width: 100%;
            max-height: 300px;
            border-radius: 8px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        .char-count {
            text-align: right;
            font-size: 0.8rem;
            color: #666;
            margin-top: 0.5rem;
        }

        .char-count.warning {
            color: #f39c12;
        }

        .char-count.error {
            color: #e74c3c;
        }

        .emotion-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .emotion-option {
            padding: 0.5rem;
            border: 2px solid #e9ecef;
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .emotion-option:hover {
            border-color: #667eea;
            background: #f0f4ff;
        }

        .emotion-option.selected {
            border-color: #667eea;
            background: #667eea;
            color: white;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .form-help {
            font-size: 0.8rem;
            color: #666;
            margin-top: 0.25rem;
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
            <a href="posts.php">My Posts</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </nav>

    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Share Your Feelings</h1>
            <p class="page-subtitle">Express what's on your mind with the WeMotions community</p>
        </div>

        <!-- Messages -->
        <?php if ($success_message): ?>
            <div class="message success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Create Post Form -->
        <div class="card">
            <h2>💬 Create New Post</h2>
            <form method="POST" action="" enctype="multipart/form-data" id="postForm">
                <!-- Post Title -->
                <div class="form-group">
                    <label for="title">Post Title *</label>
                    <input type="text" id="title" name="title" class="form-control" 
                           value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" 
                           placeholder="Give your post a title..."
                           maxlength="255" required>
                    <div class="char-count" id="titleCount">0/255</div>
                </div>

                <!-- Post Content -->
                <div class="form-group">
                    <label for="content">What's on your mind? *</label>
                    <textarea id="content" name="content" class="form-control" 
                              placeholder="Share your thoughts, feelings, or experiences with the community..."
                              maxlength="1000" required><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
                    <div class="char-count" id="contentCount">0/1000</div>
                </div>

                <!-- Emotion Selection (Optional - stored in content or title) -->
                <div class="form-group">
                    <label for="emotion">How are you feeling? (Optional)</label>
                    <div class="emotion-options" id="emotionOptions">
                        <div class="emotion-option" data-emotion="😊 Happy">😊 Happy</div>
                        <div class="emotion-option" data-emotion="😢 Sad">😢 Sad</div>
                        <div class="emotion-option" data-emotion="😡 Angry">😡 Angry</div>
                        <div class="emotion-option" data-emotion="😴 Tired">😴 Tired</div>
                        <div class="emotion-option" data-emotion="😃 Excited">😃 Excited</div>
                        <div class="emotion-option" data-emotion="😌 Relaxed">😌 Relaxed</div>
                        <div class="emotion-option" data-emotion="😰 Anxious">😰 Anxious</div>
                        <div class="emotion-option" data-emotion="😍 Loved">😍 Loved</div>
                        <div class="emotion-option" data-emotion="😔 Lonely">😔 Lonely</div>
                        <div class="emotion-option" data-emotion="🤔 Thoughtful">🤔 Thoughtful</div>
                    </div>
                    <input type="hidden" id="emotion" name="emotion" value="<?php echo htmlspecialchars($_POST['emotion'] ?? ''); ?>">
                </div>

                <!-- Image Upload -->
                <div class="form-group">
                    <label>Add an Image (Optional)</label>
                    <div class="file-upload">
                        <input type="file" id="post_image" name="post_image" 
                               class="file-upload-input" accept="image/*">
                        <label for="post_image" class="file-upload-label">
                            <span>📷 Choose Image</span>
                        </label>
                    </div>
                    <div class="form-help">Max file size: 10MB. Supported formats: JPG, PNG, GIF, WebP</div>
                    
                    <div class="image-preview" id="imagePreview" style="display: none;">
                        <img id="previewImage" src="" alt="Preview">
                        <div class="form-help" style="margin-top: 0.5rem;">Click the image to remove</div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button type="submit" class="btn btn-primary" id="submitBtn">Publish Post</button>
                    <a href="posts.php" class="btn btn-outline">View My Posts</a>
                    <a href="dashboard.php" class="btn btn-outline">Dashboard</a>
                    <button type="reset" class="btn btn-outline" id="resetBtn">Clear Form</button>
                </div>
            </form>
        </div>

        <!-- Posting Guidelines -->
        <div class="card">
            <h2>📝 Community Guidelines</h2>
            <div style="display: grid; gap: 1rem;">
                <div style="padding: 1rem; background: #e8f4fd; border-radius: 5px;">
                    <strong>✅ Do</strong>
                    <ul style="margin-top: 0.5rem; margin-left: 1.5rem;">
                        <li>Share your genuine feelings and experiences</li>
                        <li>Be respectful and supportive of others</li>
                        <li>Use appropriate language</li>
                        <li>Respect privacy and confidentiality</li>
                    </ul>
                </div>
                <div style="padding: 1rem; background: #f8d7da; border-radius: 5px;">
                    <strong>❌ Don't</strong>
                    <ul style="margin-top: 0.5rem; margin-left: 1.5rem;">
                        <li>Share harmful or offensive content</li>
                        <li>Bully or harass other members</li>
                        <li>Share personal information of others</li>
                        <li>Post spam or advertisements</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const titleInput = document.getElementById('title');
            const contentTextarea = document.getElementById('content');
            const titleCount = document.getElementById('titleCount');
            const contentCount = document.getElementById('contentCount');
            const emotionOptions = document.querySelectorAll('.emotion-option');
            const emotionInput = document.getElementById('emotion');
            const postImageInput = document.getElementById('post_image');
            const imagePreview = document.getElementById('imagePreview');
            const previewImage = document.getElementById('previewImage');
            const submitBtn = document.getElementById('submitBtn');
            const resetBtn = document.getElementById('resetBtn');
            const form = document.getElementById('postForm');

            // Title character counter
            titleInput.addEventListener('input', function() {
                const length = this.value.length;
                titleCount.textContent = `${length}/255`;
                
                if (length > 200) {
                    titleCount.className = 'char-count error';
                } else if (length > 150) {
                    titleCount.className = 'char-count warning';
                } else {
                    titleCount.className = 'char-count';
                }
                
                checkFormValidity();
            });

            // Content character counter
            contentTextarea.addEventListener('input', function() {
                const length = this.value.length;
                contentCount.textContent = `${length}/1000`;
                
                if (length > 900) {
                    contentCount.className = 'char-count error';
                } else if (length > 800) {
                    contentCount.className = 'char-count warning';
                } else {
                    contentCount.className = 'char-count';
                }
                
                checkFormValidity();
            });

            // Emotion selection
            emotionOptions.forEach(option => {
                option.addEventListener('click', function() {
                    // Remove selected class from all options
                    emotionOptions.forEach(opt => opt.classList.remove('selected'));
                    
                    // Add selected class to clicked option
                    this.classList.add('selected');
                    
                    // Update hidden input
                    emotionInput.value = this.getAttribute('data-emotion');
                });
            });

            // Image preview
            postImageInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImage.src = e.target.result;
                        imagePreview.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Remove image on click
            previewImage.addEventListener('click', function() {
                postImageInput.value = '';
                imagePreview.style.display = 'none';
            });

            // Form validation
            function checkFormValidity() {
                const title = titleInput.value.trim();
                const content = contentTextarea.value.trim();
                submitBtn.disabled = !(title && content);
            }

            // Reset form
            resetBtn.addEventListener('click', function() {
                emotionOptions.forEach(opt => opt.classList.remove('selected'));
                emotionInput.value = '';
                imagePreview.style.display = 'none';
                setTimeout(checkFormValidity, 100);
            });

            // Initial check
            checkFormValidity();

            // Form submission confirmation
            form.addEventListener('submit', function(e) {
                const title = titleInput.value.trim();
                const content = contentTextarea.value.trim();
                
                if (!title) {
                    e.preventDefault();
                    alert('Please enter a title for your post.');
                    titleInput.focus();
                    return;
                }
                
                if (!content) {
                    e.preventDefault();
                    alert('Please enter some content for your post.');
                    contentTextarea.focus();
                    return;
                }
                
                if (!confirm('Are you ready to share this with the community?')) {
                    e.preventDefault();
                    return;
                }
            });
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>