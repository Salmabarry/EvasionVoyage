<?php
require __DIR__ . '/includes/guard.php';
require_once __DIR__ . '/../includes/destinations.php';

$totalUsers = (int) db()->query("SELECT COUNT(*) FROM users WHERE is_admin = 0")->fetchColumn();
$totalBookings = (int) db()->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$totalDestinations = (int) db()->query("SELECT COUNT(*) FROM destinations")->fetchColumn();
$unreadMessagesCount = (int) db()->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'nouveau'")->fetchColumn();

$revenue = (float) db()->query(
  "SELECT COALESCE(SUM(d.price * b.travelers), 0)
   FROM bookings b JOIN destinations d ON d.id = b.destination_id
   WHERE b.status = 'confirmee'"
)->fetchColumn();

$statusCounts = ['en_attente' => 0, 'confirmee' => 0, 'annulee' => 0];
foreach (db()->query("SELECT status, COUNT(*) c FROM bookings GROUP BY status") as $row) {
  $statusCounts[$row['status']] = (int) $row['c'];
}

$monthLabels = ['01' => 'Jan', '02' => 'Fév', '03' => 'Mar', '04' => 'Avr', '05' => 'Mai', '06' => 'Juin', '07' => 'Juil', '08' => 'Août', '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Déc'];
$monthlyRaw = db()->query(
  "SELECT DATE_FORMAT(created_at, '%Y-%m') ym, COUNT(*) c
   FROM bookings
   GROUP BY ym ORDER BY ym ASC"
)->fetchAll();
$monthlyMap = [];
foreach ($monthlyRaw as $row) { $monthlyMap[$row['ym']] = (int) $row['c']; }
$chartLabels = [];
$chartData = [];
for ($i = 5; $i >= 0; $i--) {
  $ym = date('Y-m', strtotime("-{$i} months"));
  $chartLabels[] = $monthLabels[substr($ym, 5, 2)] ?? $ym;
  $chartData[] = $monthlyMap[$ym] ?? 0;
}

$recentBookings = db()->query(
  "SELECT b.id, b.travelers, b.status, b.created_at, u.first_name, u.last_name, d.name AS destination_name, d.price
   FROM bookings b
   JOIN users u ON u.id = b.user_id
   JOIN destinations d ON d.id = b.destination_id
   ORDER BY b.created_at DESC LIMIT 6"
)->fetchAll();

$recentMessages = db()->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5")->fetchAll();

$statusMeta = [
  'en_attente' => ['label' => 'En attente', 'class' => 'status-pending'],
  'confirmee' => ['label' => 'Confirmée', 'class' => 'status-success'],
  'annulee' => ['label' => 'Annulée', 'class' => 'status-danger'],
];
$messageStatusMeta = [
  'nouveau' => ['label' => 'Nouveau', 'class' => 'status-info'],
  'lu' => ['label' => 'Lu', 'class' => 'status-neutral'],
  'repondu' => ['label' => 'Répondu', 'class' => 'status-success'],
];

$pageTitle = 'Vue d\'ensemble — Admin EvasionVoyage';
$activeAdmin = 'index';
$pageSubtitle = 'Bonjour ' . $admin['first_name'] . ', voici l\'activité de la plateforme.';
require __DIR__ . '/includes/layout-top.php';
?>

<div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
  <div class="admin-card kpi-card">
    <div>
      <div class="text-xs uppercase tracking-widest text-muted-foreground">Utilisateurs</div>
      <div class="mt-2 font-display text-3xl"><?= number_format($totalUsers, 0, ',', ' ') ?></div>
      <div class="mt-2 kpi-trend kpi-trend--flat"><i data-lucide="users" class="h-3.5 w-3.5"></i> comptes voyageurs</div>
    </div>
    <span class="kpi-icon kpi-icon--primary"><i data-lucide="users" class="h-5 w-5"></i></span>
  </div>

  <div class="admin-card kpi-card">
    <div>
      <div class="text-xs uppercase tracking-widest text-muted-foreground">Réservations</div>
      <div class="mt-2 font-display text-3xl"><?= number_format($totalBookings, 0, ',', ' ') ?></div>
      <div class="mt-2 kpi-trend kpi-trend--up"><i data-lucide="clock" class="h-3.5 w-3.5"></i> <?= $statusCounts['en_attente'] ?> en attente</div>
    </div>
    <span class="kpi-icon kpi-icon--accent"><i data-lucide="calendar-check" class="h-5 w-5"></i></span>
  </div>

  <div class="admin-card kpi-card">
    <div>
      <div class="text-xs uppercase tracking-widest text-muted-foreground">Revenu confirmé</div>
      <div class="mt-2 font-display text-3xl"><?= format_fcfa($revenue) ?></div>
      <div class="mt-2 kpi-trend kpi-trend--up"><i data-lucide="check-circle-2" class="h-3.5 w-3.5"></i> <?= $statusCounts['confirmee'] ?> confirmées</div>
    </div>
    <span class="kpi-icon kpi-icon--success"><i data-lucide="wallet" class="h-5 w-5"></i></span>
  </div>

  <div class="admin-card kpi-card">
    <div>
      <div class="text-xs uppercase tracking-widest text-muted-foreground">Messages non lus</div>
      <div class="mt-2 font-display text-3xl"><?= number_format($unreadMessagesCount, 0, ',', ' ') ?></div>
      <div class="mt-2 kpi-trend kpi-trend--flat"><i data-lucide="map-pinned" class="h-3.5 w-3.5"></i> <?= $totalDestinations ?> destinations actives</div>
    </div>
    <span class="kpi-icon kpi-icon--info"><i data-lucide="mail" class="h-5 w-5"></i></span>
  </div>
</div>

<div class="mt-6 grid gap-5 lg:grid-cols-3">
  <div class="admin-card p-6 lg:col-span-2">
    <div class="flex items-center justify-between">
      <h2 class="font-display text-xl">Réservations — 6 derniers mois</h2>
    </div>
    <div class="mt-4 h-64">
      <canvas id="chart-bookings"></canvas>
    </div>
  </div>

  <div class="admin-card p-6">
    <h2 class="font-display text-xl">Statuts des réservations</h2>
    <div class="mt-4 h-64">
      <canvas id="chart-status"></canvas>
    </div>
  </div>
</div>

<div class="mt-6 grid gap-5 lg:grid-cols-3">
  <div class="admin-card lg:col-span-2">
    <div class="flex items-center justify-between p-6 pb-0">
      <h2 class="font-display text-xl">Réservations récentes</h2>
      <a href="bookings.php" class="text-sm font-semibold text-primary hover:text-accent">Tout voir →</a>
    </div>
    <?php if ($recentBookings): ?>
      <div class="mt-4 overflow-x-auto">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Client</th>
              <th>Destination</th>
              <th>Voyageurs</th>
              <th>Statut</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recentBookings as $b): ?>
              <tr>
                <td class="font-medium"><?= htmlspecialchars($b['first_name'] . ' ' . $b['last_name']) ?></td>
                <td><?= htmlspecialchars($b['destination_name']) ?></td>
                <td><?= (int) $b['travelers'] ?></td>
                <td><span class="status-pill <?= $statusMeta[$b['status']]['class'] ?>"><?= $statusMeta[$b['status']]['label'] ?></span></td>
                <td class="text-muted-foreground"><?= date('d/m/Y', strtotime($b['created_at'])) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="empty-state"><i data-lucide="inbox" class="h-8 w-8"></i><p>Aucune réservation pour le moment.</p></div>
    <?php endif; ?>
  </div>

  <div class="admin-card p-6">
    <div class="flex items-center justify-between">
      <h2 class="font-display text-xl">Messages récents</h2>
      <a href="messages.php" class="text-sm font-semibold text-primary hover:text-accent">Tout voir →</a>
    </div>
    <?php if ($recentMessages): ?>
      <ul class="mt-4 space-y-4">
        <?php foreach ($recentMessages as $m): ?>
          <li class="flex items-start gap-3">
            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-muted text-xs font-bold">
              <?= htmlspecialchars(mb_strtoupper(mb_substr($m['first_name'], 0, 1))) ?>
            </span>
            <div class="min-w-0 flex-1">
              <div class="flex items-center justify-between gap-2">
                <span class="truncate text-sm font-semibold"><?= htmlspecialchars($m['first_name'] . ' ' . $m['last_name']) ?></span>
                <span class="status-pill <?= $messageStatusMeta[$m['status']]['class'] ?> shrink-0"><?= $messageStatusMeta[$m['status']]['label'] ?></span>
              </div>
              <p class="mt-1 line-clamp-2 text-xs text-muted-foreground"><?= htmlspecialchars($m['message']) ?></p>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <div class="empty-state"><i data-lucide="mail-open" class="h-8 w-8"></i><p>Aucun message pour le moment.</p></div>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/includes/layout-bottom.php'; ?>

<script>
  (function () {
    new Chart(document.getElementById('chart-bookings'), {
      type: 'bar',
      data: {
        labels: <?= json_encode($chartLabels) ?>,
        datasets: [{
          label: 'Réservations',
          data: <?= json_encode($chartData) ?>,
          backgroundColor: 'oklch(0.72 0.16 55 / 0.7)',
          borderRadius: 8,
          maxBarThickness: 42,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: 'oklch(0.9 0.015 85)' } },
          x: { grid: { display: false } }
        }
      }
    });

    new Chart(document.getElementById('chart-status'), {
      type: 'doughnut',
      data: {
        labels: ['En attente', 'Confirmée', 'Annulée'],
        datasets: [{
          data: [<?= $statusCounts['en_attente'] ?>, <?= $statusCounts['confirmee'] ?>, <?= $statusCounts['annulee'] ?>],
          backgroundColor: ['oklch(0.85 0.13 80)', 'oklch(0.72 0.16 55)', 'oklch(0.6 0.22 27)'],
          borderWidth: 0,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '68%',
        plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } }
      }
    });
  })();
</script>
