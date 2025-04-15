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
    $geldigheid_jaren = (int)$_POST['geldigheid_jaren'];
    $beschrijving = trim($_POST['beschrijving'] ?? '');
    $is_verlengbaar = isset($_POST['is_verlengbaar']) ? 1 : 0;

    try {
        $stmt = $pdo->prepare("
            INSERT INTO diploma_types (
                naam, geldigheid_jaren, beschrijving, is_verlengbaar
            ) VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$naam, $geldigheid_jaren, $beschrijving, $is_verlengbaar]);
        
        $typeId = $pdo->lastInsertId();
        header("Location: view.php?id=$typeId");
        exit;
        
    } catch (PDOException $e) {
        $error = "Fout bij toevoegen diploma type: " . $e->getMessage();
    }
}
?>

<h2>Nieuw Diploma Type Toevoegen</h2>

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
            <label>Geldigheid (jaren) *</label>
            <input type="number" name="geldigheid_jaren" min="1" max="20" value="5" required>
        </div>
    </div>

    <div class="form-group">
        <label>Beschrijving</label>
        <textarea name="beschrijving" rows="4"></textarea>
    </div>

    <div class="form-group checkbox">
        <input type="checkbox" name="is_verlengbaar" id="is_verlengbaar" checked>
        <label for="is_verlengbaar">Kan verlengd worden</label>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn-primary">Opslaan</button>
        <a href="index.php" class="btn">Annuleren</a>
    </div>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>