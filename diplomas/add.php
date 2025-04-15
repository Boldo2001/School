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

// Haal opties op voor dropdowns
$personen = $pdo->query("SELECT id, CONCAT(voornaam, ' ', achternaam) AS naam FROM personen ORDER BY achternaam")->fetchAll();
$types = $pdo->query("SELECT id, naam FROM diploma_types ORDER BY naam")->fetchAll();
$examinateurs = $pdo->query("SELECT id, naam FROM examinateurs ORDER BY naam")->fetchAll();

// Verwerk formulierinzending
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        
        $diplomaNummer = 'DIP' . date('YmdHis');
        $persoonId = $_POST['persoon_id'];
        $typeId = $_POST['diploma_type_id'];
        $examinatorId = $_POST['examinator_id'];
        $behaaldDatum = $_POST['behaald_datum'];
        
        // Bereken geldigheidsdatum
        $stmt = $pdo->prepare("SELECT geldigheid_jaren FROM diploma_types WHERE id = ?");
        $stmt->execute([$typeId]);
        $geldigheid = $stmt->fetchColumn();
        $geldigTot = date('Y-m-d', strtotime($behaaldDatum . " + $geldigheid years"));
        
        // Voeg diploma toe
        $stmt = $pdo->prepare("
            INSERT INTO diplomas (
                diploma_nummer, persoon_id, diploma_type_id, examinator_id,
                behaald_datum, geldig_tot, security_code
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $securityCode = bin2hex(random_bytes(4)); // Genereer random code
        $stmt->execute([
            $diplomaNummer,
            $persoonId,
            $typeId,
            $examinatorId,
            $behaaldDatum,
            $geldigTot,
            $securityCode
        ]);
        
        $diplomaId = $pdo->lastInsertId();
        
        // Voeg resultaten toe
        if (isset($_POST['onderdeel'])) {
            foreach ($_POST['onderdeel'] as $index => $onderdeel) {
                if (!empty($onderdeel)) {
                    $stmt = $pdo->prepare("
                        INSERT INTO resultaten (
                            diploma_id, onderdeel, score, opmerking
                        ) VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $diplomaId,
                        $onderdeel,
                        $_POST['score'][$index],
                        $_POST['opmerking'][$index] ?? null
                    ]);
                }
            }
        }
        
        $pdo->commit();
        header("Location: view.php?id=$diplomaId");
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Fout bij toevoegen diploma: " . $e->getMessage();
    }
}
?>

<h2>Nieuw Diploma Toevoegen</h2>

<?php if (isset($error)): ?>
    <div class="alert error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST">
    <div class="form-row">
        <div class="form-group">
            <label>Persoon</label>
            <select name="persoon_id" required>
                <option value="">Selecteer persoon</option>
                <?php foreach ($personen as $persoon): ?>
                    <option value="<?= $persoon['id'] ?>"><?= htmlspecialchars($persoon['naam']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Diploma Type</label>
            <select name="diploma_type_id" required id="diplomaType">
                <option value="">Selecteer type</option>
                <?php foreach ($types as $type): ?>
                    <option value="<?= $type['id'] ?>" data-jaren="<?= $type['geldigheid_jaren'] ?>">
                        <?= htmlspecialchars($type['naam']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Examinateur</label>
            <select name="examinator_id" required>
                <option value="">Selecteer examinateur</option>
                <?php foreach ($examinateurs as $examinator): ?>
                    <option value="<?= $examinator['id'] ?>"><?= htmlspecialchars($examinator['naam']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Behaald op</label>
            <input type="date" name="behaald_datum" required id="behaaldDatum" 
                   value="<?= date('Y-m-d') ?>">
        </div>
    </div>

    <div class="form-group">
        <label>Geldig tot</label>
        <input type="date" name="geldig_tot" id="geldigTot" readonly>
    </div>

    <h3>Resultaten</h3>
    <div id="resultatenContainer">
        <div class="resultaat-row">
            <input type="text" name="onderdeel[]" placeholder="Onderdeel">
            <input type="number" name="score[]" placeholder="Score" min="0" max="100" step="0.1">
            <input type="text" name="opmerking[]" placeholder="Opmerking">
            <button type="button" class="btn-remove" onclick="removeResult(this)">×</button>
        </div>
    </div>
    <button type="button" class="btn" onclick="addResult()">+ Onderdeel toevoegen</button>

    <div class="form-actions">
        <button type="submit" class="btn-primary">Opslaan</button>
        <a href="index.php" class="btn">Annuleren</a>
    </div>
</form>

<script>
// Voeg resultaatrij toe
function addResult() {
    const container = document.getElementById('resultatenContainer');
    const newRow = document.createElement('div');
    newRow.className = 'resultaat-row';
    newRow.innerHTML = `
        <input type="text" name="onderdeel[]" placeholder="Onderdeel">
        <input type="number" name="score[]" placeholder="Score" min="0" max="100" step="0.1">
        <input type="text" name="opmerking[]" placeholder="Opmerking">
        <button type="button" class="btn-remove" onclick="removeResult(this)">×</button>
    `;
    container.appendChild(newRow);
}

// Verwijder resultaatrij
function removeResult(button) {
    if (document.querySelectorAll('.resultaat-row').length > 1) {
        button.parentElement.remove();
    }
}

// Bereken geldigheidsdatum
document.getElementById('diplomaType').addEventListener('change', updateGeldigTot);
document.getElementById('behaaldDatum').addEventListener('change', updateGeldigTot);

function updateGeldigTot() {
    const typeSelect = document.getElementById('diplomaType');
    const behaaldDatum = document.getElementById('behaaldDatum').value;
    const geldigTot = document.getElementById('geldigTot');
    
    if (typeSelect.value && behaaldDatum) {
        const jaren = typeSelect.options[typeSelect.selectedIndex].dataset.jaren;
        const datum = new Date(behaaldDatum);
        datum.setFullYear(datum.getFullYear() + parseInt(jaren));
        geldigTot.value = datum.toISOString().split('T')[0];
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>