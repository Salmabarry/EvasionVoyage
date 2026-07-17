<?php
require_once __DIR__ . '/includes/auth.php';
require_login('connexion.php');
require_once __DIR__ . '/includes/destinations.php';

$stmt = db()->prepare(
  'SELECT b.id, b.travelers, b.status, b.created_at, d.name, d.country, d.image, d.slug, d.price, d.nights
   FROM bookings b
   JOIN destinations d ON d.id = b.destination_id
   WHERE b.user_id = ?
   ORDER BY b.created_at DESC'
);
$stmt->execute([current_user()['id']]);
$bookings = $stmt->fetchAll();

$statusLabels = [
  'en_attente' => ['label' => 'En attente', 'class' => 'bg-muted text-foreground'],
  'confirmee' => ['label' => 'Confirmée', 'class' => 'bg-primary text-primary-foreground'],
  'annulee' => ['label' => 'Annulée', 'class' => 'bg-destructive/10 text-destructive'],
];

$pageTitle = 'Mes réservations — EvasionVoyage';
$pageDescription = 'Suivez vos demandes de réservation EvasionVoyage.';
$transparentNav = false;
$activePage = 'mes-reservations.php';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/page-header.php';

render_page_header(
  'Mon compte',
  'Mes réservations',
  'Suivez l\'état de vos demandes de voyage et retrouvez votre historique.'
);
?>

<section class="container-x py-16">
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
        <?php $status = $statusLabels[$b['status']] ?? $statusLabels['en_attente']; ?>
        <article class="grid gap-6 overflow-hidden rounded-3xl border border-border bg-card shadow-[var(--shadow-soft)] md:grid-cols-[220px_1fr]">
          <div class="relative h-48 md:h-auto">
            <img src="<?= htmlspecialchars($b['image']) ?>" alt="<?= htmlspecialchars($b['name']) ?>" loading="lazy" class="absolute inset-0 h-full w-full object-cover">
          </div>
          <div class="flex flex-col justify-between gap-4 p-6 md:flex-row md:items-center md:p-8">
            <div>
              <div class="flex items-center gap-1 text-xs uppercase tracking-widest text-muted-foreground">
                <i data-lucide="map-pin" class="h-3 w-3"></i> <?= htmlspecialchars($b['country']) ?>
              </div>
              <h3 class="mt-2 font-display text-2xl"><?= htmlspecialchars($b['name']) ?></h3>
              <div class="mt-2 text-sm text-muted-foreground">
                <?= (int) $b['travelers'] ?> voyageur<?= $b['travelers'] > 1 ? 's' : '' ?> · <?= (int) $b['nights'] ?> nuits · demandé le <?= date('d/m/Y', strtotime($b['created_at'])) ?>
              </div>
            </div>
            <div class="flex flex-col items-start gap-3 md:items-end">
              <span class="rounded-full px-3 py-1 text-xs font-semibold <?= $status['class'] ?>"><?= $status['label'] ?></span>
              <div class="font-display text-xl"><?= format_fcfa($b['price']) ?> <span class="text-xs font-sans text-muted-foreground">/ pers.</span></div>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
