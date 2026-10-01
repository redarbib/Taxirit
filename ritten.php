<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'auth.php';

$user = requireLogin();
$role = $user['role'];
$driverName = $user['name'];
$action = $_POST['action'] ?? '';
$editRide = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'delete' && $role === 'admin') {
        $statement = $pdo->prepare('DELETE FROM ritten WHERE id = ?');
        $statement->execute([$id]);
    } elseif ($action === 'save' && $role === 'admin') {
        $customer = trim($_POST['customer'] ?? '');
        $pickup = trim($_POST['pickup'] ?? '');
        $destination = trim($_POST['destination'] ?? '');
        $date = trim($_POST['date'] ?? '') ?: null;
        $time = trim($_POST['time'] ?? '') ?: null;
        $status = trim($_POST['status'] ?? 'Gepland');
        $assignedDriver = trim($_POST['driver_name'] ?? '') ?: null;

        if ($customer !== '' && $pickup !== '' && $destination !== '') {
            if ($id !== 0) {
                $statement = $pdo->prepare('UPDATE ritten SET customer = ?, pickup = ?, destination = ?, ride_date = ?, ride_time = ?, status = ?, driver_name = ? WHERE id = ?');
                $statement->execute([$customer, $pickup, $destination, $date, $time, $status, $assignedDriver, $id]);
            } else {
                $statement = $pdo->prepare('INSERT INTO ritten (customer, pickup, destination, ride_date, ride_time, status, driver_name) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $statement->execute([$customer, $pickup, $destination, $date, $time, $status, $assignedDriver]);
            }
        }
    } elseif ($action === 'accept' && $role === 'driver') {
        $statement = $pdo->prepare("UPDATE ritten SET status = 'Toegewezen', driver_name = ? WHERE id = ? AND status = 'Gepland'");
        $statement->execute([$driverName, $id]);
    } elseif ($action === 'complete' && $role === 'driver') {
        $statement = $pdo->prepare("UPDATE ritten SET status = 'Afgerond' WHERE id = ? AND driver_name = ? AND status = 'Toegewezen'");
        $statement->execute([$id, $driverName]);
    }

    header('Location: ritten.php');
    exit;
}

if ($role === 'admin' && isset($_GET['edit'])) {
    $statement = $pdo->prepare('SELECT * FROM ritten WHERE id = ?');
    $statement->execute([(int) $_GET['edit']]);
    $editRide = $statement->fetch(PDO::FETCH_ASSOC) ?: null;
}

$query = $role === 'driver'
    ? "SELECT id, customer, pickup, destination, ride_date, ride_time, status, driver_name FROM ritten WHERE status <> 'Afgerond' AND (status = 'Gepland' OR driver_name = ?) ORDER BY ride_date, ride_time, id"
    : 'SELECT id, customer, pickup, destination, ride_date, ride_time, status, driver_name FROM ritten ORDER BY id DESC';
$statement = $pdo->prepare($query);
$statement->execute($role === 'driver' ? [$driverName] : []);
$rides = $statement->fetchAll(PDO::FETCH_ASSOC);
$counts = [
    'new' => (int) $pdo->query("SELECT COUNT(*) FROM ritten WHERE status = 'Gepland'")->fetchColumn(),
    'assigned' => (int) $pdo->query("SELECT COUNT(*) FROM ritten WHERE status = 'Toegewezen'")->fetchColumn(),
    'done' => (int) $pdo->query("SELECT COUNT(*) FROM ritten WHERE status = 'Afgerond'")->fetchColumn(),
];

function clean(string $value): string
{
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="nl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $role === 'driver' ? 'Chauffeur' : 'Admin' ?> - Ritten</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="shell">
  <aside class="sidebar">
    <div class="brand"><div class="brand-title">Veel Auto</div><div class="brand-sub">Taxibedrijf - Systeem</div></div>
    <div class="nav-label">Werkruimte</div>
    <nav class="nav">
      <a class="nav-item <?= $role === 'admin' ? 'active' : '' ?>" href="<?= $role === 'admin' ? 'admin.php' : 'chauffeur.php' ?>"><div class="nav-main"><?= $role === 'admin' ? 'Admin' : 'Chauffeur' ?></div><div class="nav-sub"><?= $role === 'admin' ? 'Ritten beheren' : 'Mijn ritten' ?></div></a>
      <a class="nav-item" href="index.php"><div class="nav-main">Dashboard</div><div class="nav-sub">Overzicht</div></a>
      <a class="nav-item" href="logout.php"><div class="nav-main">Uitloggen</div><div class="nav-sub">Sessie afsluiten</div></a>
    </nav>
    <div class="side-footer"><div class="statusline"><i class="dot"></i><span><?= clean($user['name']) ?></span></div><div class="stamp">Ingelogd</div></div>
  </aside>
  <section class="app">
    <header class="topbar"><div class="top-left"><span class="top-title"><?= $role === 'driver' ? 'Chauffeur' : 'Admin' ?></span><span class="top-note">- Rittenbeheer</span></div><div class="top-right"><span class="email"><?= clean($user['email']) ?></span><span class="avatar"><?= $role === 'driver' ? 'CH' : 'AD' ?></span></div></header>
    <main class="rides-wrap">
      <section class="rides-toolbar"><div><div class="eyebrow"><?= $role === 'driver' ? 'Operationeel overzicht' : 'Admin beheer' ?></div><h1><?= $role === 'driver' ? 'Mijn ritten' : 'Alle ritten' ?></h1></div><div class="role-label"><?= $role === 'driver' ? 'Chauffeur' : 'Admin' ?></div></section>
      <section class="ride-stats"><div><strong><?= $counts['new'] ?></strong><span>Nieuwe ritten</span></div><div><strong><?= $counts['assigned'] ?></strong><span>Onderweg</span></div><div><strong><?= $counts['done'] ?></strong><span>Afgerond</span></div></section>

      <?php if ($role === 'admin'): ?>
      <form method="post" class="crud-form">
        <input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int) ($editRide['id'] ?? 0) ?>">
        <input name="customer" placeholder="Klant" required value="<?= clean((string) ($editRide['customer'] ?? '')) ?>"><input name="pickup" placeholder="Ophaaladres" required value="<?= clean((string) ($editRide['pickup'] ?? '')) ?>"><input name="destination" placeholder="Bestemming" required value="<?= clean((string) ($editRide['destination'] ?? '')) ?>"><input type="date" name="date" value="<?= clean((string) ($editRide['ride_date'] ?? '')) ?>"><input type="time" name="time" value="<?= clean((string) ($editRide['ride_time'] ?? '')) ?>"><input name="driver_name" placeholder="Chauffeur" value="<?= clean((string) ($editRide['driver_name'] ?? '')) ?>">
        <select name="status"><?php foreach (['Gepland', 'Toegewezen', 'Afgerond'] as $status): ?><option <?= ($editRide['status'] ?? 'Gepland') === $status ? 'selected' : '' ?>><?= $status ?></option><?php endforeach; ?></select><button type="submit"><?= $editRide ? 'Opslaan' : '+ Nieuwe rit' ?></button><?php if ($editRide): ?><a class="cancel-link" href="ritten.php">Annuleren</a><?php endif; ?>
      </form>
      <?php else: ?>
      <div class="driver-note"><strong>Chauffeur: <?= clean($driverName) ?></strong><span>Nieuwe ritten aannemen en eigen ritten afronden.</span></div>
      <?php endif; ?>

      <section class="rides-table-wrap"><table class="table ride-table"><thead><tr><th>Rit</th><th>Klant</th><th>Ophaaladres</th><th>Bestemming</th><th>Datum</th><th>Tijd</th><th>Chauffeur</th><th>Status</th><th>Actie</th></tr></thead><tbody>
      <?php foreach ($rides as $ride): ?>
        <?php $statusClass = $ride['status'] === 'Afgerond' ? 'done' : ($ride['status'] === 'Toegewezen' ? 'assigned' : 'plan'); ?>
        <tr><td class="strong">R-<?= str_pad((string) $ride['id'], 3, '0', STR_PAD_LEFT) ?></td><td><?= clean($ride['customer']) ?></td><td class="muted"><?= clean($ride['pickup']) ?></td><td class="muted"><?= clean($ride['destination']) ?></td><td><?= clean((string) ($ride['ride_date'] ?? '')) ?></td><td><?= clean(substr((string) ($ride['ride_time'] ?? ''), 0, 5)) ?></td><td><?= $ride['driver_name'] ? clean($ride['driver_name']) : '<span class="muted">Vrij</span>' ?></td><td><span class="badge <?= $statusClass ?>"><?= clean($ride['status']) ?></span></td><td class="actions">
          <?php if ($role === 'admin'): ?><a href="ritten.php?edit=<?= (int) $ride['id'] ?>">Bewerk</a><form method="post"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $ride['id'] ?>"><button type="submit" class="link-button">Verwijder</button></form>
          <?php elseif ($ride['status'] === 'Gepland'): ?><form method="post"><input type="hidden" name="action" value="accept"><input type="hidden" name="id" value="<?= (int) $ride['id'] ?>"><button type="submit">Aannemen</button></form>
          <?php elseif ($ride['driver_name'] === $driverName): ?><form method="post"><input type="hidden" name="action" value="complete"><input type="hidden" name="id" value="<?= (int) $ride['id'] ?>"><button type="submit" class="done-button">Markeer als klaar</button></form>
          <?php else: ?><span class="muted">Toegewezen</span><?php endif; ?></td></tr>
      <?php endforeach; ?>
      <?php if (!$rides): ?><tr><td colspan="9" class="empty-state">Geen ritten in deze weergave.</td></tr><?php endif; ?>
      </tbody></table></section>
      <footer class="statusbar"><?= count($rides) ?> ritten weergegeven</footer>
    </main>
  </section>
</div>
</body>
</html>
