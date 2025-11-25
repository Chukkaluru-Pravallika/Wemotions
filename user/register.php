<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "wemotions";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";
$success = "";

// Handle both JSON and form data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Check if JSON data is sent
    $contentType = $_SERVER["CONTENT_TYPE"] ?? '';
    
    if (strpos($contentType, 'application/json') !== false) {
        // Get JSON input
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $username = trim($data['username'] ?? '');
    } else {
        // Get form data
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $username = trim($_POST['username'] ?? '');
    }

    // Validate inputs
    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Check if username or email already exists
        $check_sql = "SELECT id FROM user WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Username or email already exists.";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $verification_token = bin2hex(random_bytes(16));
            $current_time = date('Y-m-d H:i:s');
            $is_verified = 0;
            $roles = json_encode(['ROLE_USER']);

            // Insert new user
            $insert_sql = "INSERT INTO user (email, password, username, roles, verification_token, is_verified, created_at, updated_at) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("sssssiss", $email, $hashed_password, $username, $roles, $verification_token, $is_verified, $current_time, $current_time);
            
            if ($stmt->execute()) {
                $success = "Registration successful! You can now login.";
                // Clear form
                $email = $username = "";
            } else {
                $error = "Error: " . $stmt->error;
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - WeMotions</title>
    <style>
        body { font-family: Arial; max-width: 400px; margin: 50px auto; padding: 20px; }
        .form-group { margin: 15px 0; }
        input { width: 100%; padding: 10px; margin: 5px 0; }
        button { width: 100%; padding: 10px; background: #007bff; color: white; border: none; }
        .message { padding: 10px; margin: 10px 0; text-align: center; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <h2>Register for WeMotions</h2>
    
    <?php if ($error): ?>
        <div class="message error"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="message success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <input type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
        </div>
        <div class="form-group">
            <input type="text" name="username" placeholder="Username" value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
        </div>
        <div class="form-group">
            <input type="password" name="password" placeholder="Password" required>
        </div>
        <button type="submit">Register</button>
    </form>
    
    <p><a href="login.php">Already have an account? Login here</a></p>
</body>
</html>
<?php
$conn->close();
?>