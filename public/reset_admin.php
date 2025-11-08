<?php
include '../includes/config.php';

// Only run this once then delete the file
echo "<h3>Admin Password Reset</h3>";

$new_password = 'admin123';
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

echo "New Password: " . $new_password . "<br>";
echo "Hashed Password: " . $hashed_password . "<br>";

// Update admin password
try {
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
    $stmt->execute([$hashed_password]);
    
    if ($stmt->rowCount() > 0) {
        echo "<strong style='color: green;'>Admin password reset successfully!</strong>";
    } else {
        echo "<strong style='color: red;'>Admin user not found!</strong>";
    }
} catch (PDOException $e) {
    echo "<strong style='color: red;'>Error: " . $e->getMessage() . "</strong>";
}

echo "<br><br><strong>DELETE THIS FILE AFTER USE!</strong>";
?>