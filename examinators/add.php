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
    $naam = trim($_POST['naam']);
    $organisatie = trim($_POST['organisatie'] ?? '');
    $contact_info = trim($_POST['contact_info'] ?? '');

    try {
        $stmt = $pdo->prepare("
            INSERT INTO examinateurs (
                naam, organisatie, contact_info
            ) VALUES (?, ?, ?)
        ");
        $stmt->execute([$naam, $organisatie, $contact_info]);
        
        $examinatorId = $pdo->lastInsertId();
        header("Location: view.php?id=$examinatorId");
        exit;
        
    } catch (PDOException $e) {
        $error = "Fout bij toevoegen examinator: " . $e->getMessage();
    }
}
?>

<h2>Nieuwe Examinator Toevoegen</h2>

<?php if (isset($error)): ?>
    <div class="alert error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST">
    <div class="form-row">
        <div class="form-group">
            <label>Naam *</label>
            <input type="text" name="naam" required>
        </div>
        <div class="form-group">
            <label>Organisatie</label>
            <input type="text" name="organisatie">
        </div>
    </div>

    <div class="form-group">
        <label>Contactinformatie</label>
        <textarea name="contact_info" rows="4" placeholder="E-mail, telefoonnummer, etc."></textarea>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn-primary">Opslaan</button>
        <a href="index.php" class="btn">Annuleren</a>
    </div>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>