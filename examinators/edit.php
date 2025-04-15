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

// Controleer of ID parameter aanwezig is
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$examinatorId = $_GET['id'];

// Haal examinator gegevens op
$stmt = $pdo->prepare("SELECT * FROM examinateurs WHERE id = ?");
$stmt->execute([$examinatorId]);
$examinator = $stmt->fetch();

if (!$examinator) {
    echo '<div class="alert error">Examinator niet gevonden</div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

// Verwerk formulier update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $naam = $_POST['naam'];
    $organisatie = $_POST['organisatie'] ?? null;
    $contact_info = $_POST['contact_info'] ?? null;

    $stmt = $pdo->prepare("
        UPDATE examinateurs 
        SET naam = ?, 
            organisatie = ?,
            contact_info = ?
        WHERE id = ?
    ");
    $stmt->execute([$naam, $organisatie, $contact_info, $examinatorId]);
    
    header('Location: view.php?id=' . $examinatorId);
    exit;
}
?>

<h2>Examinator Bewerken</h2>

<form method="POST">
    <div class="form-group">
        <label>Naam</label>
        <input type="text" name="naam" value="<?= htmlspecialchars($examinator['naam']) ?>" required>
    </div>

    <div class="form-group">
        <label>Organisatie</label>
        <input type="text" name="organisatie" value="<?= htmlspecialchars($examinator['organisatie']) ?>">
    </div>

    <div class="form-group">
        <label>Contactinformatie</label>
        <textarea name="contact_info" rows="4"><?= htmlspecialchars($examinator['contact_info']) ?></textarea>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn-primary">Opslaan</button>
        <a href="view.php?id=<?= $examinator['id'] ?>" class="btn">Annuleren</a>
    </div>
</form>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>