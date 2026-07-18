<?php
require_once __DIR__ . '/includes/auth.php';
require_login('connexion.php');
require_once __DIR__ . '/includes/destinations.php';

$stmt = db()->prepare(
  "SELECT b.id, b.travelers, b.status, b.created_at, b.date_depart, b.date_retour, b.amount,
          d.name, d.country, o.title AS offer_title
   FROM bookings b
   JOIN destinations d ON d.id = b.destination_id
   LEFT JOIN offers o ON o.id = b.offer_id
   WHERE b.user_id = ?
   ORDER BY b.created_at DESC"
);
$stmt->execute([current_user()['id']]);
$bookings = $stmt->fetchAll();

$statusLabels = [
  'en_attente' => ['label' => 'En attente', 'class' => 'bg-muted text-foreground'],
  'confirmee' => ['label' => 'Confirmée', 'class' => 'bg-primary text-primary-foreground'],
  'refusee' => ['label' => 'Refusée', 'class' => 'bg-destructive/10 text-destructive'],
  'annulee' => ['label' => 'Annulée', 'class' => 'bg-destructive/10 text-destructive'],
];

$pageTitle = 'Historique des voyages — EvasionVoyage';
$pageDescription = 'Toutes vos réservations, de la plus récente à la plus ancienne.';
$transparentNav = false;
$activePage = 'mes-reservations.php';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/page-header.php';

render_page_header(
  'Mon compte',
  'Historique des voyages',
  'Toutes vos réservations et le suivi de leur état, actualisés en temps réel.'
);
?>

<section class="container-x py-16">
  <div class="mb-4 flex items-center justify-between text-sm text-muted-foreground">
    <div><?= count($bookings) ?> réservation(s) — actualisé à <?= date('H:i:s') ?></div>
    <a href="tableau-de-bord.php" class="font-medium text-primary hover:underline">← Tableau de bord</a>
  </div>

  <div class="overflow-x-auto rounded-3xl border border-border bg-card shadow-[var(--shadow-soft)]">
    <table class="w-full text-sm">
      <thead>
        <tr class="border-b border-border text-left text-xs uppercase tracking-widest text-muted-foreground">
          <th class="px-6 py-4">Voyage</th>
          <th class="px-6 py-4">Destination</th>
          <th class="px-6 py-4">Dates</th>
          <th class="px-6 py-4">Voyageurs</th>
          <th class="px-6 py-4">Montant</th>
          <th class="px-6 py-4">Statut</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$bookings): ?>
          <tr><td colspan="6" class="px-6 py-10 text-center text-muted-foreground">Aucun voyage dans votre historique pour le moment.</td></tr>
        <?php else: ?>
          <?php foreach ($bookings as $b): ?>
            <?php $st = $statusLabels[$b['status']] ?? $statusLabels['en_attente']; ?>
            <tr class="border-b border-border/60 last:border-0">
              <td class="px-6 py-4 font-medium"><?= htmlspecialchars($b['offer_title'] ?: 'Sur mesure — ' . $b['name']) ?></td>
              <td class="px-6 py-4 text-muted-foreground"><i data-lucide="map-pin" class="mr-1 inline h-3.5 w-3.5 align-[-2px] text-primary"></i><?= htmlspecialchars($b['name'] . ', ' . $b['country']) ?></td>
              <td class="px-6 py-4 text-muted-foreground">
                <?= $b['date_depart'] ? date('d/m/Y', strtotime($b['date_depart'])) . ' → ' . date('d/m/Y', strtotime($b['date_retour'])) : '—' ?>
              </td>
              <td class="px-6 py-4"><?= (int) $b['travelers'] ?></td>
              <td class="px-6 py-4 font-semibold"><?= format_fcfa($b['amount'] ?? 0) ?></td>
              <td class="px-6 py-4"><span class="rounded-full px-3 py-1 text-xs font-semibold <?= $st['class'] ?>"><?= $st['label'] ?></span></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>

<script>
  // Suivi en temps réel : la page se recharge toutes les 15 s et au retour sur l'onglet
  setInterval(function () { window.location.reload(); }, 15000);
  window.addEventListener('focus', function () { window.location.reload(); });
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
