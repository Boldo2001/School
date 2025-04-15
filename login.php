<?php
session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db_connect.php';

if (isset($_SESSION['ingelogd'])) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gebruikersnaam = trim($_POST['gebruikersnaam']);
    $wachtwoord = $_POST['wachtwoord'];

    $stmt = $pdo->prepare("SELECT * FROM gebruikers WHERE gebruikersnaam = ?");
    $stmt->execute([$gebruikersnaam]);
    $gebruiker = $stmt->fetch();

    if ($gebruiker && hash('sha256', $wachtwoord) === $gebruiker['wachtwoord']) {
        $_SESSION['ingelogd'] = true;
        $_SESSION['gebruiker_id'] = $gebruiker['id'];
        $_SESSION['gebruikersnaam'] = $gebruiker['gebruikersnaam'];
        $_SESSION['rol'] = $gebruiker['rol'];
        header('Location: index.php');
        exit;
    } else {
        $error = "Ongeldige gebruikersnaam of wachtwoord.";
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Inloggen</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f1f5f9;
            font-family: Arial, sans-serif;
        }

        .login-container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 6px;
            border: 1px solid #ccc;
            background-color: #eef2ff;
            font-size: 15px;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #0056b3;
        }

        .error-message {
            color: red;
            margin-top: 15px;
            text-align: center;
        }
    </style>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="login-container">
        <h2>Inloggen</h2>
        <form method="post">
            <input type="text" name="gebruikersnaam" placeholder="Gebruikersnaam" required>
            <input type="password" name="wachtwoord" placeholder="Wachtwoord" required>
            <button type="submit">Inloggen</button>
        </form>
        <?php if ($error): ?>
            <p class="error-message"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
    </div>
</body>
</html>
