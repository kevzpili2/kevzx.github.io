<?php
require 'db_connect.php';

$new_password = 'admin123';
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
$email = 'admin@ignisense.ph';

try {
    // 1. Check if admin exists
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    
    if ($check->rowCount() > 0) {
        // 2. Update existing admin
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->execute([$hashed_password, $email]);
        echo "<h1>Success!</h1>";
        echo "<p>Admin password reset to: <strong>admin123</strong></p>";
    } else {
        // 3. Create admin if missing
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES ('System Admin', ?, ?, 'admin')");
        $stmt->execute([$email, $hashed_password]);
        echo "<h1>Success!</h1>";
        echo "<p>Admin account created with password: <strong>admin123</strong></p>";
    }
    echo "<br><a href='index.php'>Go to Login</a>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>