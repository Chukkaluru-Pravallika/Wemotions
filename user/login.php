<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "wemotions";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$message = "";

if ($_POST) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT id, username, password FROM user WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['loggedin'] = true;
            header("Location: dashboard.php");
            exit;
        } else {
            $message = "Invalid password!";
        }
    } else {
        $message = "User not found!";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body { font-family: Arial; max-width: 400px; margin: 50px auto; padding: 20px; }
        input { width: 100%; padding: 10px; margin: 5px 0; }
        button { width: 100%; padding: 10px; background: #007bff; color: white; border: none; }
        .message { padding: 10px; margin: 10px 0; text-align: center; background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <h2>Login</h2>
    <?php if ($message): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
    <p><a href="register.php">Create account</a></p>
</body>
</html>
<?php $conn->close(); ?>