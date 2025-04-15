<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/header.php';

// Verwerk formulierinzending
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $voornaam = trim($_POST['voornaam']);
    $achternaam = trim($_POST['achternaam']);
    $geboortedatum = $_POST['geboortedatum'] ?: null;
    $email = trim($_POST['email'] ?? '');
    $telefoon = trim($_POST['telefoon'] ?? '');

    try {
        $stmt = $pdo->prepare("
            INSERT INTO personen (
                voornaam, achternaam, geboortedatum, email, telefoon
            ) VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$voornaam, $achternaam, $geboortedatum, $email, $telefoon]);
        
        $persoonId = $pdo->lastInsertId();
        header("Location: view.php?id=$persoonId");
        exit;
        
    } catch (PDOException $e) {
        $error = "Fout bij toevoegen persoon: " . $e->getMessage();
    }
}
?>

<h2>Nieuwe Persoon Toevoegen</h2>

<?php if (isset($error)): ?>
    <div class="alert error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST">
    <div class="form-row">
        <div class="form-group">
            <label>Voornaam *</label>
            <input type="text" name="voornaam" required>
        </div>
        <div class="form-group">
            <label>Achternaam *</label>
            <input type="text" name="achternaam" required>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Geboortedatum</label>
            <input type="date" name="geboortedatum" max="<?= date('Y-m-d') ?>">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>E-mailadres</label>
            <input type="email" name="email">
        </div>
        <div class="form-group">
            <label>Telefoonnummer</label>
            <input type="tel" name="telefoon">
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn-primary">Opslaan</button>
        <a href="index.php" class="btn">Annuleren</a>
    </div>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>