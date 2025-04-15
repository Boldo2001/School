<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$persoonId = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM personen WHERE id = ?");
$stmt->execute([$persoonId]);
$persoon = $stmt->fetch();

if (!$persoon) {
    echo '<div class="alert error">Persoon niet gevonden</div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

$stmt = $pdo->prepare("
    SELECT d.*, dt.naam AS diploma_type_naam
    FROM diplomas d
    JOIN diploma_types dt ON d.diploma_type_id = dt.id
    WHERE d.persoon_id = ?
    ORDER BY d.geldig_tot DESC
");
$stmt->execute([$persoonId]);
$diplomas = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Persoonsdossier</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            background-color: #f8fafc;
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            color: #1f2937;
        }

        header {
            background-color: #111827;
            color: white;
            padding: 16px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            margin: 0;
            font-size: 22px;
            letter-spacing: 0.5px;
        }

        nav a {
            color: #e5e7eb;
            margin-left: 20px;
            text-decoration: none;
            font-size: 15px;
        }

        nav a:hover {
            text-decoration: underline;
        }

        .badge {
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 600;
            color: white;
        }

        .badge.green { background-color: #22c55e; }
        .badge.red { background-color: #ef4444; }

        .card {
            background: white;
            padding: 40px;
            border-radius: 14px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.06);
            margin-top: 50px;
        }

        .card h2 {
            margin-top: 0;
            font-size: 24px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 12px;
        }

        .info p {
            font-size: 16px;
            margin: 8px 0;
        }

        .info strong {
            display: inline-block;
            width: 140px;
        }

        .actions {
            margin-top: 30px;
        }

        .actions button {
            margin-right: 10px;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }

        th, td {
            padding: 12px;
            border: 1px solid #e5e7eb;
        }

        th {
            background-color: #f1f5f9;
            text-align: left;
        }

        button {
            background-color: #3b82f6;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 18px;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        button:hover {
            background-color: #2563eb;
        }
    </style>
</head>
<body>

<header>
    <h1>Diploma Verificatie Systeem</h1>
    <nav></nav>
</header>

<div class="container">
    <div class="card">
        <h2>Persoonsgegevens</h2>
        <div class="info">
            <p><strong>Naam:</strong> <?= htmlspecialchars($persoon['voornaam'] . ' ' . $persoon['achternaam']) ?></p>
            <p><strong>Geboortedatum:</strong> <?= htmlspecialchars($persoon['geboortedatum']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($persoon['email']) ?></p>
        </div>

        <h3 style="margin-top: 40px;">Diploma's</h3>
        <?php if ($diplomas): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Behaald op</th>
                            <th>Geldig tot</th>
                            <th>Status</th>
                            <th>Acties</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($diplomas as $diploma): 
                            $verlopen = strtotime($diploma['geldig_tot']) < time();
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($diploma['diploma_type_naam']) ?></td>
                                <td><?= date('d-m-Y', strtotime($diploma['behaald_datum'])) ?></td>
                                <td><?= date('d-m-Y', strtotime($diploma['geldig_tot'])) ?></td>
                                <td>
                                    <span class="badge <?= $verlopen ? 'red' : 'green' ?>">
                                        <?= $verlopen ? 'Verlopen' : 'Geldig' ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="../diplomas/view.php?id=<?= $diploma['id'] ?>">
                                        <button>Bekijken</button>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="alert">Geen diploma's gevonden voor deze persoon.</p>
        <?php endif; ?>

        <div class="actions">
            <a href="index.php"><button>← Terug naar overzicht</button></a>
            <a href="edit.php?id=<?= $persoonId ?>"><button>Bewerken</button></a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
