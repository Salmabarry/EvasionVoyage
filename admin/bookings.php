<?php
require __DIR__ . '/includes/guard.php';
require_once __DIR__ . '/../includes/destinations.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_check($_POST['csrf_token'] ?? null)) {
    $errors[] = 'Session expirée, veuillez réessayer.';
  } else {
    $id = (int) ($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? '';
    if (in_array($status, ['en_attente', 'confirmee', 'refusee', 'annulee'], true)) {
      $stmt = db()->prepare('UPDATE bookings SET status = ? WHERE id = ?');
      $stmt->execute([$status, $id]);
      // Refus ou annulation d'une réservation payée : remboursement + restitution des places de l'offre
      if (in_array($status, ['refusee', 'annulee'], true)) {
        db()->prepare("UPDATE payments SET status = 'rembourse' WHERE booking_id = ? AND status = 'paye'")->execute([$id]);
        db()->prepare('UPDATE offers o JOIN bookings b ON b.offer_id = o.id SET o.seats = o.seats + b.travelers WHERE b.id = ?')->execute([$id]);
      }
    }
    header('Location: bookings.php');
    exit;
  }
}

$bookings = db()->query(
  'SELECT b.id, b.travelers, b.status, b.created_at, b.date_depart, b.date_retour, b.amount,
          u.first_name, u.last_name, u.email, d.name AS destination_name, d.price, o.title AS offer_title,
          p.reference AS pay_reference, p.status AS pay_status
   FROM bookings b
   JOIN users u ON u.id = b.user_id
   JOIN destinations d ON d.id = b.destination_id
   LEFT JOIN offers o ON o.id = b.offer_id
   LEFT JOIN payments p ON p.booking_id = b.id
   ORDER BY b.created_at DESC'
)->fetchAll();

$statusMeta = [
  'en_attente' => ['label' => 'En attente', 'class' => 'status-pending'],
  'confirmee' => ['label' => 'Confirmée', 'class' => 'status-success'],
  'refusee' => ['label' => 'Refusée', 'class' => 'status-danger'],
  'annulee' => ['label' => 'Annulée', 'class' => 'status-danger'],
];

$pageTitle = 'Réservations — Admin EvasionVoyage';
$activeAdmin = 'bookings';
$pageSubtitle = count($bookings) . ' réservation(s) au total';
require __DIR__ . '/includes/layout-top.php';
?>

<?php if ($errors): ?>
  <div class="admin-card mb-5 border-destructive/30 bg-destructive/10 p-4 text-sm text-destructive">
    <ul class="list-inside list-disc space-y-1">
      <?php foreach ($errors as $error): ?><li><?= htmlspecialchars($error) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<div class="admin-card">
  <?php if (!$bookings): ?>
    <div class="empty-state"><i data-lucide="calendar-x" class="h-8 w-8"></i><p>Aucune réservation pour le moment.</p></div>
  <?php else: ?>
    <div class="overflow-x-auto">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Client</th>
            <th>Destination</th>
            <th>Voyageurs</th>
            <th>Montant</th>
            <th>Demandé le</th>
            <th>Statut</th>
            <th class="text-right">Mettre à jour</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($bookings as $b): ?>
            <tr>
              <td>
                <div class="font-medium"><?= htmlspecialchars($b['first_name'] . ' ' . $b['last_name']) ?></div>
                <div class="text-xs text-muted-foreground"><?= htmlspecialchars($b['email']) ?></div>
              </td>
              <td>
                <?= htmlspecialchars($b['destination_name']) ?>
                <?php if ($b['offer_title']): ?><div class="text-xs text-muted-foreground"><?= htmlspecialchars($b['offer_title']) ?></div><?php endif; ?>
                <?php if ($b['date_depart']): ?>
                  <div class="text-xs text-muted-foreground"><?= date('d/m/y', strtotime($b['date_depart'])) ?> → <?= date('d/m/y', strtotime($b['date_retour'])) ?></div>
                <?php endif; ?>
              </td>
              <td><?= (int) $b['travelers'] ?></td>
              <td class="font-medium">
                <?= format_fcfa($b['amount'] !== null ? $b['amount'] : $b['price'] * $b['travelers']) ?>
                <?php if ($b['pay_reference']): ?>
                  <div class="text-xs <?= $b['pay_status'] === 'rembourse' ? 'text-muted-foreground' : 'text-primary' ?>">
                    <?= $b['pay_status'] === 'rembourse' ? 'Remboursé' : 'Payé' ?> · <?= htmlspecialchars($b['pay_reference']) ?>
                  </div>
                <?php endif; ?>
              </td>
              <td class="text-muted-foreground"><?= date('d/m/Y', strtotime($b['created_at'])) ?></td>
              <td><span class="status-pill <?= $statusMeta[$b['status']]['class'] ?>"><?= $statusMeta[$b['status']]['label'] ?></span></td>
              <td>
                <form method="post" action="bookings.php" class="flex items-center justify-end gap-2">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= (int) $b['id'] ?>">
                  <select name="status" class="rounded-xl border border-border bg-background px-2 py-1.5 text-xs focus:border-primary focus:outline-none">
                    <?php foreach ($statusMeta as $value => $meta): ?>
                      <option value="<?= $value ?>" <?= $b['status'] === $value ? 'selected' : '' ?>><?= $meta['label'] ?></option>
                    <?php endforeach; ?>
                  </select>
                  <button type="submit" class="rounded-full bg-accent px-3 py-1.5 text-xs font-semibold text-accent-foreground">OK</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/layout-bottom.php'; ?>
