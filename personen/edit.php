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

$persoonId = $_GET['id'];

// Haal persoon gegevens op
$stmt = $pdo->prepare("SELECT * FROM personen WHERE id = ?");
$stmt->execute([$persoonId]);
$persoon = $stmt->fetch();

if (!$persoon) {
    echo '<div class="alert error">Persoon niet gevonden</div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

// Verwerk formulier verzending
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $voornaam = $_POST['voornaam'];
    $achternaam = $_POST['achternaam'];
    $geboortedatum = $_POST['geboortedatum'] ?: null;

    $stmt = $pdo->prepare("
        UPDATE personen 
        SET voornaam = ?, 
            achternaam = ?,
            geboortedatum = ?
        WHERE id = ?
    ");
    $stmt->execute([$voornaam, $achternaam, $geboortedatum, $persoonId]);
    
    header('Location: view.php?id=' . $persoonId);
    exit;
}
?>

<h2>Persoon Bewerken: <?= htmlspecialchars($persoon['voornaam'] . ' ' . $persoon['achternaam']) ?></h2>

<form method="POST">
    <div class="form-row">
        <div class="form-group">
            <label>Voornaam</label>
            <input type="text" name="voornaam" value="<?= htmlspecialchars($persoon['voornaam']) ?>" required>
        </div>
        <div class="form-group">
            <label>Achternaam</label>
            <input type="text" name="achternaam" value="<?= htmlspecialchars($persoon['achternaam']) ?>" required>
        </div>
    </div>

    <div class="form-group">
        <label>Geboortedatum</label>
        <input type="date" name="geboortedatum" value="<?= htmlspecialchars($persoon['geboortedatum']) ?>">
    </div>

    <div class="form-actions">
        <button type="submit" class="btn-primary">Opslaan</button>
        <a href="view.php?id=<?= $persoon['id'] ?>" class="btn">Annuleren</a>
    </div>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>