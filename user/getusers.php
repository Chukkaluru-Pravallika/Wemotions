<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "wemotions";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to get all users
$sql = "SELECT * FROM user ORDER BY created_at DESC";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html>
<head>
    <title>All Users - WeMotions</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 14px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; position: sticky; top: 0; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        tr:hover { background-color: #f5f5f5; }
        .count { color: #666; margin-bottom: 20px; }
        .no-users { padding: 20px; background: #ffe6e6; border: 1px solid #ffcccc; }
        .bio { max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    </style>
</head>
<body>
    <h1>WeMotions - All Users</h1>
    
    <?php
    if ($result->num_rows > 0) {
        echo "<div class='count'>Total users: " . $result->num_rows . "</div>";
        echo "<table>";
        echo "<tr>
                <th>ID</th>
                <th>Username</th>
                <th>Display Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Bio</th>
                <th>Roles</th>
                <th>Created At</th>
                <th>Updated At</th>
              </tr>";
        
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row["id"] . "</td>";
            echo "<td><strong>" . htmlspecialchars($row["username"]) . "</strong></td>";
            echo "<td>" . htmlspecialchars($row["display_name"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["email"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["phone"]) . "</td>";
            echo "<td class='bio' title='" . htmlspecialchars($row["bio"]) . "'>" . htmlspecialchars(substr($row["bio"], 0, 50)) . "</td>";
            echo "<td>" . htmlspecialchars($row["roles"]) . "</td>";
            echo "<td>" . $row["created_at"] . "</td>";
            echo "<td>" . $row["updated_at"] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='no-users'>No users found in the database.</div>";
    }
    
    $conn->close();
    ?>
</body>
</html>