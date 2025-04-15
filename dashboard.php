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
require_once 'includes/config.php';
require_once 'includes/db_connect.php';

// Redirect naar login als niet ingelogd
if (!isset($_SESSION['ingelogd'])) {
    header('Location: login.php');
    exit;
}

require_once 'includes/header.php';
?>

<div class="dashboard">
    <h1>Welkom, <?= htmlspecialchars($_SESSION['gebruikersnaam']) ?></h1>
    
    <div class="dashboard-cards">
        <?php if ($_SESSION['rol'] === 'admin'): ?>
            <a href="admin/" class="card admin">
                <h2>Admin Paneel</h2>
                <p>Systeeminstellingen beheren</p>
            </a>
        <?php endif; ?>
        
        <a href="diplomas/" class="card">
            <h2>Diploma's</h2>
            <p>Bekijk en beheer diploma's</p>
        </a>
        
        <a href="personen/" class="card">
            <h2>Personen</h2>
            <p>Beheer personenregistratie</p>
        </a>
        
        <a href="logout.php" class="card logout">
            <h2>Uitloggen</h2>
            <p>Afmelden uit het systeem</p>
        </a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
</body>
</html>