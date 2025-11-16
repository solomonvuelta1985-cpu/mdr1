<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Notification Test</title>
</head>
<body>
    <h1>Session Debug</h1>
    <pre><?php print_r($_SESSION); ?></pre>

    <h2>Checks:</h2>
    <p>Is logged in: <?php echo isset($_SESSION['user_id']) ? 'YES' : 'NO'; ?></p>
    <p>User Role: <?php echo $_SESSION['user_role'] ?? 'NOT SET'; ?></p>
    <p>Show Notifications: <?php
        $show = isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'staff');
        echo $show ? 'YES' : 'NO';
    ?></p>
</body>
</html>
