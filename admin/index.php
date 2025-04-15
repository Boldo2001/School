<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';

// Alleen toegang voor admins
if (!isset($_SESSION['ingelogd']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-panel">
    <h1>Admin Paneel</h1>
    
    <div class="admin-grid">
        <div class="admin-card">
            <h2>Gebruikersbeheer</h2>
            <a href="gebruikers/" class="btn">Beheren</a>
        </div>
        
        <div class="admin-card">
            <h2>Systeeminstellingen</h2>
            <a href="instellingen/" class="btn">Bekijken</a>
        </div>
        
        <div class="admin-card">
            <h2>Audit Logs</h2>
            <a href="logs/" class="btn">Bekijken</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>