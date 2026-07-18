<?php
require_once __DIR__ . '/includes/auth.php';
require_login('connexion.php');
require_once __DIR__ . '/includes/destinations.php';

$userId = current_user()['id'];

/* ----- Statistiques du voyageur ----- */
$stats = db()->prepare(
  "SELECT COUNT(*) AS total,
          SUM(status = 'confirmee') AS confirmees,
          SUM(status = 'confirmee' AND date_depart >= CURDATE()) AS a_venir,
          COALESCE(SUM(CASE WHEN status = 'confirmee' THEN amount END), 0) AS depense
   FROM bookings WHERE user_id = ?"
);
$stats->execute([$userId]);
$s = $stats->fetch();

/* ----- Prochains voyages confirmés ----- */
$stmt = db()->prepare(
  "SELECT b.id, b.travelers, b.date_depart, b.date_retour, b.amount,
          d.name, d.country, d.image, o.title AS offer_title
   FROM bookings b
   JOIN destinations d ON d.id = b.destination_id
   LEFT JOIN offers o ON o.id = b.offer_id
   WHERE b.user_id = ? AND b.status = 'confirmee' AND b.date_depart >= CURDATE()
   ORDER BY b.date_depart ASC LIMIT 3"
);
$stmt->execute([$userId]);
$prochains = $stmt->fetchAll();

$pageTitle = 'Tableau de bord — EvasionVoyage';
$pageDescription = 'Votre espace voyageur EvasionVoyage.';
$transparentNav = false;
$activePage = 'mes-reservations.php';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/page-header.php';

render_page_header(
  'Mon compte',
  'Bonjour ' . current_user()['first_name'] . ' 👋',
  'Vue d\'ensemble de vos voyages et accès rapides à votre espace.'
);
?>

<section class="container-x py-16">
  <!-- Statistiques -->
  <div class="grid gap-4 md:grid-cols-4">
    <?php
      $cartes = [
        ['icone' => 'ticket', 'valeur' => (int) $s['total'], 'libelle' => 'Réservations au total'],
        ['icone' => 'plane-takeoff', 'valeur' => (int) $s['a_venir'], 'libelle' => 'Voyages à venir'],
        ['icone' => 'badge-check', 'valeur' => (int) $s['confirmees'], 'libelle' => 'Réservations confirmées'],
        ['icone' => 'wallet', 'valeur' => format_fcfa($s['depense']), 'libelle' => 'Total voyages confirmés'],
      ];
    ?>
    <?php foreach ($cartes as $c): ?>
      <div class="rounded-3xl border border-border bg-card p-6 shadow-[var(--shadow-soft)]">
        <span class="grid h-10 w-10 place-items-center rounded-xl bg-primary/10 text-primary">
          <i data-lucide="<?= $c['icone'] ?>" class="h-5 w-5"></i>
        </span>
        <div class="mt-4 font-display text-3xl"><?= $c['valeur'] ?></div>
        <div class="mt-1 text-sm text-muted-foreground"><?= $c['libelle'] ?></div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="mt-12 grid gap-10 lg:grid-cols-[1fr_320px]">
    <!-- Prochains voyages -->
    <div>
      <h2 class="font-display text-2xl">Mes prochains voyages</h2>
      <?php if (!$prochains): ?>
        <div class="mt-4 rounded-3xl border border-border bg-card p-8 text-center text-muted-foreground">
          Aucun voyage à venir pour le moment.
          <a href="offres.php" class="font-semibold text-primary">Trouvez votre prochaine destination !</a>
        </div>
      <?php else: ?>
        <div class="mt-4 space-y-4">
          <?php foreach ($prochains as $p): ?>
            <article class="grid gap-4 overflow-hidden rounded-3xl border border-border bg-card shadow-[var(--shadow-soft)] md:grid-cols-[160px_1fr]">
              <div class="relative h-32 md:h-auto">
                <img src="<?= htmlspecialchars($p['image']) ?>" alt="" class="absolute inset-0 h-full w-full object-cover">
              </div>
              <div class="p-5">
                <div class="text-xs uppercase tracking-widest text-muted-foreground"><?= htmlspecialchars($p['country']) ?></div>
                <div class="mt-1 font-display text-xl"><?= htmlspecialchars($p['offer_title'] ?: $p['name']) ?></div>
                <div class="mt-2 text-sm text-muted-foreground">
                  <i data-lucide="calendar" class="mr-1 inline h-3.5 w-3.5 align-[-2px]"></i>
                  du <?= date('d/m/Y', strtotime($p['date_depart'])) ?> au <?= date('d/m/Y', strtotime($p['date_retour'])) ?>
                  · <?= (int) $p['travelers'] ?> voyageur<?= $p['travelers'] > 1 ? 's' : '' ?>
                  · <span class="font-semibold text-foreground"><?= format_fcfa($p['amount']) ?></span>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Accès rapides -->
    <div>
      <h2 class="font-display text-2xl">Accès rapides</h2>
      <div class="mt-4 space-y-3">
        <?php
          $liens = [
            ['href' => 'offres.php', 'icone' => 'search', 'label' => 'Rechercher un voyage'],
            ['href' => 'destinations.php', 'icone' => 'calendar-heart', 'label' => 'Voyage sur mesure à mes dates'],
            ['href' => 'mes-reservations.php', 'icone' => 'ticket', 'label' => 'Mes réservations'],
            ['href' => 'historique.php', 'icone' => 'history', 'label' => 'Historique des voyages'],
            ['href' => 'profil.php', 'icone' => 'user-round-cog', 'label' => 'Modifier mon profil'],
          ];
        ?>
        <?php foreach ($liens as $l): ?>
          <a href="<?= $l['href'] ?>" class="flex items-center gap-3 rounded-2xl border border-border bg-card px-5 py-4 text-sm font-medium text-foreground transition hover:border-primary/40 hover:bg-muted">
            <i data-lucide="<?= $l['icone'] ?>" class="h-4 w-4 text-primary"></i> <?= $l['label'] ?>
            <i data-lucide="arrow-right" class="ml-auto h-4 w-4 text-muted-foreground"></i>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
