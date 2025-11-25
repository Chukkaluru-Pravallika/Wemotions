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
$sql = "SELECT password, username, email FROM user WHERE id = ?";
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
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate inputs
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_message = "All fields are required.";
    } elseif (!password_verify($current_password, $user['password'])) {
        $error_message = "Current password is incorrect.";
    } elseif (strlen($new_password) < 6) {
        $error_message = "New password must be at least 6 characters long.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "New passwords do not match.";
    } elseif ($current_password === $new_password) {
        $error_message = "New password cannot be the same as current password.";
    } else {
        // Hash new password and update
        $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        $update_sql = "UPDATE user SET password = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("si", $hashed_new_password, $user_id);
        
        if ($stmt->execute()) {
            $success_message = "Password changed successfully!";
            
            // Send email notification (optional)
            sendPasswordChangeEmail($user['email'], $user['username']);
            
            // Clear form
            $_POST = array();
        } else {
            $error_message = "Error updating password: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Function to send password change notification
function sendPasswordChangeEmail($email, $username) {
    $subject = "Password Changed - WeMotions";
    $message = "
    Hello " . $username . ",
    
    Your WeMotions account password was successfully changed.
    
    If you didn't make this change, please contact support immediately.
    
    Time: " . date('Y-m-d H:i:s') . "
    IP Address: " . $_SERVER['REMOTE_ADDR'] . "
    
    Best regards,
    WeMotions Team
    ";
    
    $headers = "From: security@wemotions.com\r\n";
    
    // For now, just log it (you can enable actual email later)
    file_put_contents('../password_change_log.txt', 
        "Password changed for: " . $email . "\n" .
        "Username: " . $username . "\n" .
        "Time: " . date('Y-m-d H:i:s') . "\n" .
        "IP: " . $_SERVER['REMOTE_ADDR'] . "\n" .
        "------------------------\n", 
        FILE_APPEND
    );
    
    return true; // mail($email, $subject, $message, $headers);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - WeMotions</title>
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

        .btn-danger {
            background: #e74c3c;
            color: white;
        }

        .btn-danger:hover {
            background: #c0392b;
        }

        .container {
            max-width: 600px;
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
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .password-strength {
            margin-top: 0.5rem;
            height: 4px;
            background: #e9ecef;
            border-radius: 2px;
            overflow: hidden;
        }

        .strength-bar {
            height: 100%;
            width: 0%;
            transition: all 0.3s;
        }

        .strength-weak { background: #e74c3c; width: 33%; }
        .strength-medium { background: #f39c12; width: 66%; }
        .strength-strong { background: #27ae60; width: 100%; }

        .password-requirements {
            font-size: 0.8rem;
            color: #666;
            margin-top: 0.5rem;
        }

        .requirement {
            display: flex;
            align-items: center;
            margin-bottom: 0.25rem;
        }

        .requirement.valid {
            color: #27ae60;
        }

        .requirement.invalid {
            color: #e74c3c;
        }

        .requirement-icon {
            margin-right: 0.5rem;
            font-size: 0.9rem;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .security-notice {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .security-notice h4 {
            color: #856404;
            margin-bottom: 0.5rem;
        }

        .security-notice p {
            color: #856404;
            font-size: 0.9rem;
            margin: 0;
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
            <a href="profile.php">Profile</a>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </nav>

    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Change Password</h1>
            <p class="page-subtitle">Secure your account with a new password</p>
        </div>

        <!-- Messages -->
        <?php if ($success_message): ?>
            <div class="message success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Security Notice -->
        <div class="security-notice">
            <h4>🔒 Security Tips</h4>
            <p>• Use a strong, unique password you haven't used elsewhere<br>
               • Avoid common words and personal information<br>
               • Consider using a password manager</p>
        </div>

        <!-- Change Password Form -->
        <div class="card">
            <h2>🔑 Update Your Password</h2>
            <form method="POST" action="" id="passwordForm">
                <div class="form-group">
                    <label for="current_password">Current Password *</label>
                    <input type="password" id="current_password" name="current_password" class="form-control" 
                           required autocomplete="current-password">
                </div>

                <div class="form-group">
                    <label for="new_password">New Password *</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" 
                           required autocomplete="new-password" minlength="6">
                    <div class="password-strength">
                        <div class="strength-bar" id="strengthBar"></div>
                    </div>
                    <div class="password-requirements">
                        <div class="requirement" id="reqLength">
                            <span class="requirement-icon">📏</span>
                            At least 6 characters
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                           required autocomplete="new-password" minlength="6">
                    <div class="password-requirements">
                        <div class="requirement" id="reqMatch">
                            <span class="requirement-icon">✅</span>
                            Passwords match
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button type="submit" class="btn btn-primary" id="submitBtn">Change Password</button>
                    <a href="profile.php" class="btn btn-outline">Back to Profile</a>
                    <a href="dashboard.php" class="btn btn-outline">Dashboard</a>
                </div>
            </form>
        </div>

        <!-- Account Security Info -->
        <div class="card">
            <h2>📊 Account Security</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <div style="padding: 1rem; background: #f8f9fa; border-radius: 5px;">
                    <strong>Username</strong>
                    <div><?php echo htmlspecialchars($user['username']); ?></div>
                </div>
                <div style="padding: 1rem; background: #f8f9fa; border-radius: 5px;">
                    <strong>Email</strong>
                    <div><?php echo htmlspecialchars($user['email']); ?></div>
                </div>
                <div style="padding: 1rem; background: #f8f9fa; border-radius: 5px;">
                    <strong>Last Updated</strong>
                    <div>Just now</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const newPasswordInput = document.getElementById('new_password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const strengthBar = document.getElementById('strengthBar');
            const reqLength = document.getElementById('reqLength');
            const reqMatch = document.getElementById('reqMatch');
            const submitBtn = document.getElementById('submitBtn');
            const form = document.getElementById('passwordForm');

            // Password strength checker
            newPasswordInput.addEventListener('input', function() {
                const password = this.value;
                
                // Check length
                if (password.length >= 6) {
                    reqLength.classList.add('valid');
                    reqLength.classList.remove('invalid');
                } else {
                    reqLength.classList.add('invalid');
                    reqLength.classList.remove('valid');
                }
                
                // Update strength bar
                let strength = 0;
                if (password.length >= 6) strength += 1;
                if (password.length >= 8) strength += 1;
                if (/[A-Z]/.test(password)) strength += 1;
                if (/[0-9]/.test(password)) strength += 1;
                if (/[^A-Za-z0-9]/.test(password)) strength += 1;
                
                strengthBar.className = 'strength-bar';
                if (strength <= 1) {
                    strengthBar.classList.add('strength-weak');
                } else if (strength <= 3) {
                    strengthBar.classList.add('strength-medium');
                } else {
                    strengthBar.classList.add('strength-strong');
                }
                
                checkFormValidity();
            });

            // Password match checker
            confirmPasswordInput.addEventListener('input', function() {
                const newPassword = newPasswordInput.value;
                const confirmPassword = this.value;
                
                if (confirmPassword && newPassword === confirmPassword) {
                    reqMatch.classList.add('valid');
                    reqMatch.classList.remove('invalid');
                } else {
                    reqMatch.classList.add('invalid');
                    reqMatch.classList.remove('valid');
                }
                
                checkFormValidity();
            });

            // Form validation
            function checkFormValidity() {
                const newPassword = newPasswordInput.value;
                const confirmPassword = confirmPasswordInput.value;
                
                const isLengthValid = newPassword.length >= 6;
                const isMatchValid = newPassword === confirmPassword && confirmPassword.length > 0;
                
                submitBtn.disabled = !(isLengthValid && isMatchValid);
            }

            // Initial check
            checkFormValidity();

            // Form submission confirmation
            form.addEventListener('submit', function(e) {
                const currentPassword = document.getElementById('current_password').value;
                const newPassword = newPasswordInput.value;
                
                if (!currentPassword) {
                    e.preventDefault();
                    alert('Please enter your current password.');
                    return;
                }
                
                if (currentPassword === newPassword) {
                    e.preventDefault();
                    alert('New password cannot be the same as current password.');
                    return;
                }
                
                // Additional confirmation for security
                if (!confirm('Are you sure you want to change your password?')) {
                    e.preventDefault();
                    return;
                }
            });

            // Show/hide password functionality (optional)
            function togglePasswordVisibility(inputId) {
                const input = document.getElementById(inputId);
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
            }

            // Add show/hide buttons (you can add these if needed)
            // Example: <button type="button" onclick="togglePasswordVisibility('new_password')">👁️</button>
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>