<?php

require_once __DIR__ . '/../config/auth.php';

requireRole('owner');

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Owner</title>
</head>

<body>

    <h1>Dashboard Owner</h1>

    <p>
        Selamat datang,
        <?= htmlspecialchars(getNamaUser()) ?>
    </p>

    <p>
        Role:
        <?= htmlspecialchars(getRoleUser()) ?>
    </p>

    <a href="../logout.php">
        Logout
    </a>

</body>

</html>