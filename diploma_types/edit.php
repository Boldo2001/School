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

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$typeId = $_GET['id'];

// Haal diploma type gegevens op
$stmt = $pdo->prepare("SELECT * FROM diploma_types WHERE id = ?");
$stmt->execute([$typeId]);
$diplomaType = $stmt->fetch();

if (!$diplomaType) {
    echo '<div class="alert error">Diploma type niet gevonden</div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

// Verwerk formulier verzending
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $naam = $_POST['naam'];
    $geldigheid_jaren = $_POST['geldigheid_jaren'];
    $beschrijving = $_POST['beschrijving'] ?? null;

    $stmt = $pdo->prepare("
        UPDATE diploma_types 
        SET naam = ?, 
            geldigheid_jaren = ?,
            beschrijving = ?
        WHERE id = ?
    ");
    $stmt->execute([$naam, $geldigheid_jaren, $beschrijving, $typeId]);
    
    header('Location: view.php?id=' . $typeId);
    exit;
}
?>

<h2>Diploma Type Bewerken: <?= htmlspecialchars($diplomaType['naam']) ?></h2>

<form method="POST">
    <div class="form-group">
        <label>Naam</label>
        <input type="text" name="naam" value="<?= htmlspecialchars($diplomaType['naam']) ?>" required>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Geldigheid (jaren)</label>
            <input type="number" name="geldigheid_jaren" min="1" max="20" 
                   value="<?= htmlspecialchars($diplomaType['geldigheid_jaren']) ?>" required>
        </div>
    </div>

    <div class="form-group">
        <label>Beschrijving</label>
        <textarea name="beschrijving" rows="4"><?= htmlspecialchars($diplomaType['beschrijving']) ?></textarea>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn-primary">Opslaan</button>
        <a href="view.php?id=<?= $diplomaType['id'] ?>" class="btn">Annuleren</a>
    </div>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>