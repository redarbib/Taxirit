<?php
declare(strict_types=1);

require __DIR__ . DIRECTORY_SEPARATOR . 'auth.php';
$user = requireLogin();

$counts = [
    'planned' => (int) $pdo->query("SELECT COUNT(*) FROM ritten WHERE status = 'Gepland'")->fetchColumn(),
    'assigned' => (int) $pdo->query("SELECT COUNT(*) FROM ritten WHERE status = 'Toegewezen'")->fetchColumn(),
    'done' => (int) $pdo->query("SELECT COUNT(*) FROM ritten WHERE status = 'Afgerond'")->fetchColumn(),
];
$recent = $pdo->query('SELECT id, customer, pickup, destination, ride_time, status FROM ritten ORDER BY id DESC LIMIT 8')->fetchAll(PDO::FETCH_ASSOC);

function cleanDashboard(string $value): string
{
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="nl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard - Veel Auto</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="shell">
  <aside class="sidebar">
    <div class="brand"><div class="brand-title">Veel Auto</div><div class="brand-sub">Taxibedrijf - Systeem</div></div>
    <div class="nav-label">Werkruimte</div>
    <nav class="nav">
      <a class="nav-item active" href="index.php"><div class="nav-main">Dashboard</div><div class="nav-sub">Live overzicht</div></a>
      <a class="nav-item" href="<?= $user['role'] === 'admin' ? 'admin.php' : 'chauffeur.php' ?>"><div class="nav-main"><?= $user['role'] === 'admin' ? 'Admin' : 'Chauffeur' ?></div><div class="nav-sub"><?= $user['role'] === 'admin' ? 'Ritten beheren' : 'Mijn ritten' ?></div></a>
      <a class="nav-item" href="logout.php"><div class="nav-main">Uitloggen</div><div class="nav-sub">Sessie afsluiten</div></a>
    </nav>
    <div class="side-footer"><div class="statusline"><i class="dot"></i><span><?= cleanDashboard($user['name']) ?></span></div><div class="stamp">Ingelogd</div></div>
  </aside>
  <section class="app">
    <header class="topbar"><div class="top-left"><span class="top-title">Dashboard</span><span class="top-note">- Live data</span></div><div class="top-right"><span class="email"><?= cleanDashboard($user['email']) ?></span><span class="avatar"><?= $user['role'] === 'admin' ? 'AD' : 'CH' ?></span></div></header>
    <main class="content">
      <section class="hero"><div><div class="eyebrow">Veel Auto - Dashboard</div><h1>Welkom, <?= cleanDashboard($user['name']) ?></h1><p>Actuele ritten uit de database</p></div><a class="btn-black" href="<?= $user['role'] === 'admin' ? 'admin.php' : 'chauffeur.php' ?>">Ritten openen</a></section>
      <section class="kpis"><div class="kpi"><div class="kpi-label">Gepland</div><div class="kpi-value"><?= $counts['planned'] ?></div><div class="kpi-note">wachten op actie</div></div><div class="kpi dark"><div class="kpi-label">Toegewezen</div><div class="kpi-value"><?= $counts['assigned'] ?></div><div class="kpi-note">in behandeling</div></div><div class="kpi"><div class="kpi-label">Afgerond</div><div class="kpi-value"><?= $counts['done'] ?></div><div class="kpi-note">voltooide ritten</div></div></section>
      <section class="panel"><div class="panel-head"><div class="panel-title">Recente ritten</div><a class="panel-link" href="ritten.php">Alle ritten -&gt;</a></div><table class="table"><thead><tr><th>Rit</th><th>Klant</th><th>Ophaaladres</th><th>Bestemming</th><th>Tijd</th><th>Status</th></tr></thead><tbody>
      <?php foreach ($recent as $ride): ?><tr><td class="strong">R-<?= str_pad((string) $ride['id'], 3, '0', STR_PAD_LEFT) ?></td><td><?= cleanDashboard($ride['customer']) ?></td><td class="muted"><?= cleanDashboard($ride['pickup']) ?></td><td class="muted"><?= cleanDashboard($ride['destination']) ?></td><td class="muted"><?= cleanDashboard(substr((string) ($ride['ride_time'] ?? ''), 0, 5)) ?></td><td><span class="badge <?= $ride['status'] === 'Afgerond' ? 'done' : ($ride['status'] === 'Toegewezen' ? 'assigned' : 'plan') ?>"><?= cleanDashboard($ride['status']) ?></span></td></tr><?php endforeach; ?>
      <?php if (!$recent): ?><tr><td colspan="6" class="empty-state">Nog geen ritten aangemaakt.</td></tr><?php endif; ?>
      </tbody></table></section>
    </main>
  </section>
</div>
</body>
</html>
