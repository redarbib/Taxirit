<?php
declare(strict_types=1);

require __DIR__ . DIRECTORY_SEPARATOR . 'auth.php';

if (currentUser()) {
    header('Location: index.php');
    exit;
}

$error = '';
$registered = isset($_GET['registered']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $statement = $pdo->prepare('SELECT id, name, email, password_hash, role FROM users WHERE email = ? AND active = 1');
    $statement->execute([trim($_POST['email'] ?? '')]);
    $user = $statement->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($_POST['password'] ?? '', $user['password_hash'])) {
        session_regenerate_id(true);
        unset($user['password_hash']);
        $_SESSION['user'] = $user;
        header('Location: index.php');
        exit;
    }
    $error = 'E-mail of wachtwoord is niet juist.';
}
?>
<!doctype html>
<html lang="nl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Inloggen - Veel Auto</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body class="login-page">
  <main class="login-box">
    <div class="eyebrow">Veel Auto · Systeem</div>
    <h1>Inloggen</h1>
    <?php if ($registered): ?><p class="login-success">Account aangemaakt. Je kunt nu inloggen.</p><?php endif; ?>
    <?php if ($error): ?><p class="login-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
    <form method="post" class="login-form">
      <label>E-mail<input type="email" name="email" required autofocus></label>
      <label>Wachtwoord<input type="password" name="password" required></label>
      <button type="submit">Inloggen</button>
    </form>
    <p class="login-footer"><a href="register.php">Nog geen account? Registreren</a></p>
  </main>
</body>
</html>