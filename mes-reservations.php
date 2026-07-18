<?php
require_once __DIR__ . '/includes/auth.php';
require_login('connexion.php');
require_once __DIR__ . '/includes/destinations.php';

$errors = [];

/* ----- Annulation d'une réservation (avec remboursement du paiement) ----- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'annuler') {
  if (!csrf_check($_POST['csrf_token'] ?? null)) {
    $errors[] = 'Session expirée, veuillez réessayer.';
  } else {
    $id = (int) ($_POST['id'] ?? 0);
    $stmt = db()->prepare("UPDATE bookings SET status = 'annulee' WHERE id = ? AND user_id = ? AND status IN ('en_attente','confirmee')");
    $stmt->execute([$id, current_user()['id']]);
    if ($stmt->rowCount() > 0) {
      db()->prepare("UPDATE payments SET status = 'rembourse' WHERE booking_id = ? AND status = 'paye'")->execute([$id]);
      // Si la réservation venait d'une offre à places limitées : on rend les places
      db()->prepare('UPDATE offers o JOIN bookings b ON b.offer_id = o.id SET o.seats = o.seats + b.travelers WHERE b.id = ?')->execute([$id]);
    }
    header('Location: mes-reservations.php');
    exit;
  }
}

$stmt = db()->prepare(
  'SELECT b.id, b.travelers, b.status, b.created_at, b.date_depart, b.date_retour, b.amount,
          d.name, d.country, d.image, d.slug, d.price, d.nights, o.title AS offer_title,
          p.reference AS pay_reference, p.method AS pay_method, p.status AS pay_status
   FROM bookings b
   JOIN destinations d ON d.id = b.destination_id
   LEFT JOIN offers o ON o.id = b.offer_id
   LEFT JOIN payments p ON p.booking_id = b.id
   WHERE b.user_id = ?
   ORDER BY b.created_at DESC'
);
$stmt->execute([current_user()['id']]);
$bookings = $stmt->fetchAll();

$statusLabels = [
  'en_attente' => ['label' => 'En attente', 'class' => 'bg-muted text-foreground'],
  'confirmee' => ['label' => 'Confirmée', 'class' => 'bg-primary text-primary-foreground'],
  'refusee' => ['label' => 'Refusée', 'class' => 'bg-destructive/10 text-destructive'],
  'annulee' => ['label' => 'Annulée', 'class' => 'bg-destructive/10 text-destructive'],
];
$methodLabels = ['carte' => 'Carte bancaire', 'wave' => 'Wave', 'orange_money' => 'Orange Money'];

$pageTitle = 'Mes réservations — EvasionVoyage';
$pageDescription = 'Suivez vos réservations EvasionVoyage.';
$transparentNav = false;
$activePage = 'mes-reservations.php';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/page-header.php';

render_page_header(
  'Mon compte',
  'Mes réservations',
  'Suivez l\'état de vos voyages, imprimez vos confirmations et retrouvez votre historique.'
);
?>

<style>
  #zone-impression { display: none; }
  @media print {
    body > *:not(#zone-impression) { display: none !important; }
    #zone-impression { display: block !important; padding: 2rem; font-family: Arial, sans-serif; }
    #zone-impression table { width: 100%; border-collapse: collapse; }
    #zone-impression th { text-align: left; width: 40%; padding: 6px 0; color: #555; }
    #zone-impression td { padding: 6px 0; }
  }
</style>

<section class="container-x py-16">
  <div class="mb-6 flex items-center justify-between">
    <div class="text-sm text-muted-foreground"><?= count($bookings) ?> réservation(s)</div>
    <a href="profil.php" class="inline-flex items-center gap-2 rounded-full border border-border px-4 py-2 text-sm font-medium text-foreground hover:bg-muted">
      <i data-lucide="user-round-cog" class="h-4 w-4"></i> Modifier mon profil
    </a>
  </div>

  <?php if ($errors): ?>
    <div class="mb-6 rounded-2xl border border-destructive/30 bg-destructive/10 p-4 text-sm text-destructive">
      <?php foreach ($errors as $error): ?><div><?= htmlspecialchars($error) ?></div><?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if (!$bookings): ?>
    <div class="rounded-3xl border border-border bg-card p-10 text-center">
      <p class="text-muted-foreground">Vous n'avez pas encore de réservation.</p>
      <a href="offres.php" class="mt-4 inline-flex items-center gap-2 rounded-full bg-accent px-5 py-3 text-sm font-semibold text-accent-foreground">
        Découvrir nos offres <i data-lucide="arrow-right" class="h-4 w-4"></i>
      </a>
    </div>
  <?php else: ?>
    <div class="space-y-6">
      <?php foreach ($bookings as $b): ?>
        <?php
          $status = $statusLabels[$b['status']] ?? $statusLabels['en_attente'];
          $montant = $b['amount'] !== null ? (int) $b['amount'] : (int) $b['price'] * (int) $b['travelers'];
        ?>
        <article class="grid gap-6 overflow-hidden rounded-3xl border border-border bg-card shadow-[var(--shadow-soft)] md:grid-cols-[220px_1fr]">
          <div class="relative h-48 md:h-auto">
            <img src="<?= htmlspecialchars($b['image']) ?>" alt="<?= htmlspecialchars($b['name']) ?>" loading="lazy" class="absolute inset-0 h-full w-full object-cover">
          </div>
          <div class="p-6 md:p-8">
            <div class="flex flex-col justify-between gap-4 md:flex-row md:items-start">
              <div>
                <div class="flex items-center gap-1 text-xs uppercase tracking-widest text-muted-foreground">
                  <i data-lucide="map-pin" class="h-3 w-3"></i> <?= htmlspecialchars($b['country']) ?>
                </div>
                <h3 class="mt-2 font-display text-2xl"><?= htmlspecialchars($b['offer_title'] ?: $b['name']) ?></h3>
                <?php if ($b['offer_title']): ?><div class="text-xs text-muted-foreground"><?= htmlspecialchars($b['name']) ?> · offre à dates fixes</div><?php endif; ?>
                <div class="mt-2 text-sm text-muted-foreground">
                  <?php if ($b['date_depart'] && $b['date_retour']): ?>
                    <i data-lucide="calendar" class="mr-1 inline h-3.5 w-3.5 align-[-2px]"></i>
                    du <?= date('d/m/Y', strtotime($b['date_depart'])) ?> au <?= date('d/m/Y', strtotime($b['date_retour'])) ?> ·
                  <?php endif; ?>
                  <?= (int) $b['travelers'] ?> voyageur<?= $b['travelers'] > 1 ? 's' : '' ?> ·
                  réservation n°<?= (int) $b['id'] ?> du <?= date('d/m/Y', strtotime($b['created_at'])) ?>
                </div>
                <div class="mt-3 flex items-center gap-3">
                  <span class="font-display text-xl"><?= format_fcfa($montant) ?></span>
                  <?php if ($b['pay_reference']): ?>
                    <span class="text-xs <?= $b['pay_status'] === 'rembourse' ? 'text-muted-foreground' : 'text-primary' ?>">
                      <i data-lucide="<?= $b['pay_status'] === 'rembourse' ? 'rotate-ccw' : 'badge-check' ?>" class="mr-1 inline h-3.5 w-3.5 align-[-2px]"></i>
                      <?= $b['pay_status'] === 'rembourse' ? 'Remboursé' : 'Payé' ?> — <?= $methodLabels[$b['pay_method']] ?? $b['pay_method'] ?>
                    </span>
                  <?php endif; ?>
                </div>
              </div>
              <span class="rounded-full px-3 py-1 text-xs font-semibold <?= $status['class'] ?>"><?= $status['label'] ?></span>
            </div>

            <div class="mt-5 flex flex-wrap gap-3 border-t border-border pt-5">
              <?php if ($b['status'] === 'confirmee'): ?>
                <button type="button"
                        class="btn-imprimer inline-flex items-center gap-2 rounded-full bg-primary px-4 py-2 text-xs font-semibold text-primary-foreground"
                        data-id="RES-<?= str_pad((string) $b['id'], 6, '0', STR_PAD_LEFT) ?>"
                        data-nom="<?= htmlspecialchars($b['name'] . ', ' . $b['country']) ?>"
                        data-dates="<?= $b['date_depart'] ? 'du ' . date('d/m/Y', strtotime($b['date_depart'])) . ' au ' . date('d/m/Y', strtotime($b['date_retour'])) : '—' ?>"
                        data-voyageurs="<?= (int) $b['travelers'] ?>"
                        data-montant="<?= format_fcfa($montant) ?>"
                        data-reference="<?= htmlspecialchars($b['pay_reference'] ?? '—') ?>"
                        data-methode="<?= $methodLabels[$b['pay_method']] ?? '—' ?>">
                  <i data-lucide="printer" class="h-3.5 w-3.5"></i> Imprimer
                </button>
              <?php endif; ?>
              <?php if (in_array($b['status'], ['en_attente', 'confirmee'], true)): ?>
                <form method="post" action="mes-reservations.php"
                      onsubmit="return confirm('Voulez-vous vraiment annuler cette réservation ?');">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="annuler">
                  <input type="hidden" name="id" value="<?= (int) $b['id'] ?>">
                  <button type="submit" class="inline-flex items-center gap-2 rounded-full border border-destructive/40 px-4 py-2 text-xs font-semibold text-destructive hover:bg-destructive/10">
                    <i data-lucide="x-circle" class="h-3.5 w-3.5"></i> Annuler
                  </button>
                </form>
              <?php endif; ?>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<!-- Confirmation imprimable (remplie au clic sur Imprimer) -->
<div id="zone-impression"></div>

<script>
  document.querySelectorAll('.btn-imprimer').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const d = btn.dataset;
      document.getElementById('zone-impression').innerHTML =
        '<div style="text-align:center"><h2 style="color:#084F86;margin-bottom:4px">EvasionVoyage</h2>' +
        '<p style="color:#777;font-size:13px;margin-bottom:18px">Confirmation officielle de réservation</p></div><hr>' +
        '<table>' +
        '<tr><th>N° de réservation</th><td><b>' + d.id + '</b></td></tr>' +
        '<tr><th>Client</th><td><?= htmlspecialchars(current_user()['first_name'] . ' ' . current_user()['last_name']) ?></td></tr>' +
        '<tr><th>Destination</th><td>' + d.nom + '</td></tr>' +
        '<tr><th>Dates du séjour</th><td>' + d.dates + '</td></tr>' +
        '<tr><th>Voyageurs</th><td>' + d.voyageurs + '</td></tr>' +
        '<tr><th>Montant payé</th><td><b style="color:#F59E0B">' + d.montant + '</b></td></tr>' +
        '<tr><th>Mode de paiement</th><td>' + d.methode + '</td></tr>' +
        '<tr><th>Référence paiement</th><td>' + d.reference + '</td></tr>' +
        '<tr><th>Statut</th><td><b style="color:#16a34a">Confirmée ✔</b></td></tr>' +
        '</table><hr>' +
        '<p style="text-align:center;color:#777;font-size:12px">Présentez ce document lors de votre départ. Bon voyage !<br>' +
        'EvasionVoyage — evasionvoyage.com</p>';
      window.print();
    });
  });
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
