<?php
declare(strict_types=1);

require __DIR__ . DIRECTORY_SEPARATOR . 'auth.php';

if (currentUser()) {
    header('Location: index.php');
    exit;
}

$error = '';
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $passwordConfirmation = $_POST['password_confirmation'] ?? '';

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Vul een geldige naam en e-mail in.';
    } elseif (strlen($password) < 8) {
        $error = 'Het wachtwoord moet minimaal 8 tekens bevatten.';
    } elseif ($password !== $passwordConfirmation) {
        $error = 'De wachtwoorden zijn niet gelijk.';
    } else {
        $statement = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $statement->execute([$email]);

        if ($statement->fetch()) {
            $error = 'Dit e-mailadres is al geregistreerd.';
        } else {
            $statement = $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
            $statement->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), 'driver']);
            header('Location: login.php?registered=1');
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="nl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registreren - Veel Auto</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body class="login-page">
  <main class="login-box">
    <div class="eyebrow">Veel Auto · Chauffeur</div>
    <h1>Registreren</h1>
    <?php if ($error): ?><p class="login-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
    <form method="post" class="login-form">
      <label>Naam<input type="text" name="name" required value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"></label>
      <label>E-mail<input type="email" name="email" required value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"></label>
      <label>Wachtwoord<input type="password" name="password" minlength="8" required></label>
      <label>Herhaal wachtwoord<input type="password" name="password_confirmation" minlength="8" required></label>
      <button type="submit">Account maken</button>
    </form>
    <p class="login-footer"><a href="login.php">Al een account? Inloggen</a></p>
  </main>
</body>
</html>