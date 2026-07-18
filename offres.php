<?php
require_once __DIR__ . '/includes/destinations.php';

/* ---------- Recherche multicritères (fonctionnelle) ---------- */
$types = ['Vacances', 'Affaires', 'Aventure', 'Famille', 'Luxe'];
$q = trim($_GET['q'] ?? '');
$depart = trim($_GET['depart'] ?? '');
$retour = trim($_GET['retour'] ?? '');
$voyageurs = max(0, (int) ($_GET['voyageurs'] ?? 0));
$budget = (int) ($_GET['budget'] ?? 1700000);
if ($budget < 300000 || $budget > 1700000) $budget = 1700000;
$type = in_array($_GET['type'] ?? '', $types, true) ? $_GET['type'] : '';

$sql = "SELECT o.*, d.name AS destination_name, d.country, d.image, d.rating, d.slug
        FROM offers o
        JOIN destinations d ON d.id = o.destination_id
        WHERE o.active = 1 AND o.date_depart >= CURDATE()";
$params = [];
if ($q !== '')        { $sql .= ' AND (d.country LIKE ? OR d.name LIKE ? OR o.title LIKE ?)'; array_push($params, "%$q%", "%$q%", "%$q%"); }
if ($depart !== '')   { $sql .= ' AND o.date_depart >= ?'; $params[] = $depart; }
if ($retour !== '')   { $sql .= ' AND o.date_retour <= ?'; $params[] = $retour; }
if ($voyageurs > 0)   { $sql .= ' AND o.seats >= ?'; $params[] = $voyageurs; }
$sql .= ' AND o.price <= ?'; $params[] = $budget;
if ($type !== '')     { $sql .= ' AND o.category = ?'; $params[] = $type; }
$sql .= ' ORDER BY o.date_depart ASC';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$offers = $stmt->fetchAll();

$pageTitle = 'Offres de voyage — EvasionVoyage';
$pageDescription = 'Recherchez et comparez nos offres de séjours à dates fixes par destination, dates, budget et type de voyage.';
$transparentNav = false;
$activePage = 'offres.php';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/page-header.php';

render_page_header(
  'Réservation',
  'Composez le voyage qui vous ressemble.',
  'Nos offres à dates fixes, négociées par nos experts — ou choisissez vos propres dates depuis la page Destinations.'
);
?>

<section class="container-x relative z-10 -mt-16 md:-mt-20">
  <form method="get" action="offres.php" class="rounded-3xl border border-border bg-card p-6 shadow-[var(--shadow-lift)] md:p-8">
    <div class="grid gap-4 md:grid-cols-3 lg:grid-cols-6">
      <label class="flex items-center gap-3 rounded-2xl bg-muted px-4 py-3">
        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-background text-primary"><i data-lucide="map-pin" class="h-4 w-4"></i></span>
        <span class="min-w-0 flex-1">
          <span class="block text-[10px] uppercase tracking-widest text-muted-foreground">Pays / Destination</span>
          <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Ex : Grèce" list="suggestions-pays" class="w-full bg-transparent text-sm font-medium placeholder:text-foreground/40 focus:outline-none">
        </span>
      </label>
      <label class="flex items-center gap-3 rounded-2xl bg-muted px-4 py-3">
        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-background text-primary"><i data-lucide="calendar" class="h-4 w-4"></i></span>
        <span class="min-w-0 flex-1">
          <span class="block text-[10px] uppercase tracking-widest text-muted-foreground">Départ après le</span>
          <input type="date" name="depart" value="<?= htmlspecialchars($depart) ?>" class="w-full bg-transparent text-sm font-medium focus:outline-none">
        </span>
      </label>
      <label class="flex items-center gap-3 rounded-2xl bg-muted px-4 py-3">
        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-background text-primary"><i data-lucide="calendar" class="h-4 w-4"></i></span>
        <span class="min-w-0 flex-1">
          <span class="block text-[10px] uppercase tracking-widest text-muted-foreground">Retour avant le</span>
          <input type="date" name="retour" value="<?= htmlspecialchars($retour) ?>" class="w-full bg-transparent text-sm font-medium focus:outline-none">
        </span>
      </label>
      <label class="flex items-center gap-3 rounded-2xl bg-muted px-4 py-3">
        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-background text-primary"><i data-lucide="users" class="h-4 w-4"></i></span>
        <span class="min-w-0 flex-1">
          <span class="block text-[10px] uppercase tracking-widest text-muted-foreground">Voyageurs</span>
          <input type="number" name="voyageurs" min="1" max="20" value="<?= $voyageurs ?: '' ?>" placeholder="2" class="w-full bg-transparent text-sm font-medium placeholder:text-foreground/40 focus:outline-none">
        </span>
      </label>

      <div class="rounded-2xl bg-muted px-4 py-3">
        <div class="flex items-center gap-2 text-[10px] uppercase tracking-widest text-muted-foreground">
          <i data-lucide="wallet" class="h-3 w-3"></i> Budget max
        </div>
        <div class="mt-1 flex items-center justify-between text-sm font-semibold">
          <span id="budget-value"><?= format_fcfa($budget) ?></span>
          <span class="text-muted-foreground">/ pers.</span>
        </div>
        <input type="range" name="budget" min="300000" max="1700000" step="25000" value="<?= $budget ?>" id="budget-range" class="mt-2 w-full accent-[var(--accent)]">
      </div>

      <button type="submit" class="flex items-center justify-center gap-2 rounded-2xl bg-primary px-6 py-3 text-sm font-semibold text-primary-foreground transition hover:bg-primary/90">
        <i data-lucide="plane" class="h-4 w-4"></i> Rechercher
      </button>
    </div>

    <div class="mt-6 flex flex-wrap items-center gap-2 border-t border-border pt-6">
      <span class="text-xs uppercase tracking-widest text-muted-foreground">Type</span>
      <button type="submit" name="type" value="" class="rounded-full px-4 py-1.5 text-xs font-semibold transition <?= $type === '' ? 'bg-accent text-accent-foreground' : 'bg-muted text-foreground hover:bg-secondary' ?>">Tous</button>
      <?php foreach ($types as $t): ?>
        <button type="submit" name="type" value="<?= htmlspecialchars($t) ?>" class="rounded-full px-4 py-1.5 text-xs font-semibold transition <?= $type === $t ? 'bg-accent text-accent-foreground' : 'bg-muted text-foreground hover:bg-secondary' ?>">
          <?= htmlspecialchars($t) ?>
        </button>
      <?php endforeach; ?>
      <a href="offres.php" class="ml-auto text-xs font-medium text-muted-foreground hover:text-foreground">Réinitialiser</a>
    </div>
  </form>

  <datalist id="suggestions-pays">
    <?php foreach (db()->query('SELECT DISTINCT country FROM destinations ORDER BY country')->fetchAll() as $c): ?>
      <option value="<?= htmlspecialchars($c['country']) ?>">
    <?php endforeach; ?>
  </datalist>
</section>

<section class="container-x py-20">
  <div class="mb-8 flex items-center justify-between">
    <h2 class="font-display text-3xl md:text-4xl"><?= count($offers) ?> offre<?= count($offers) > 1 ? 's' : '' ?> correspond<?= count($offers) > 1 ? 'ent' : '' ?> à vos critères</h2>
    <a href="destinations.php" class="hidden text-sm text-muted-foreground hover:text-foreground md:block">Dates libres ? Voir les destinations →</a>
  </div>

  <?php if (!$offers): ?>
    <div class="rounded-3xl border border-border bg-card p-10 text-center text-muted-foreground">
      Aucune offre ne correspond à vos critères — élargissez votre recherche,
      ou <a href="destinations.php" class="font-semibold text-primary">composez un voyage sur mesure à vos dates</a>.
    </div>
  <?php endif; ?>

  <div class="space-y-6">
    <?php foreach ($offers as $o): ?>
      <article class="grid gap-6 overflow-hidden rounded-3xl border border-border bg-card shadow-[var(--shadow-soft)] md:grid-cols-[320px_1fr]">
        <div class="relative h-64 md:h-auto">
          <img src="<?= $o['image'] ?>" alt="<?= htmlspecialchars($o['destination_name']) ?>" loading="lazy" class="absolute inset-0 h-full w-full object-cover">
          <div class="absolute left-4 top-4 rounded-full bg-white/95 px-3 py-1 text-xs font-semibold text-primary">
            <?= htmlspecialchars($o['category']) ?>
          </div>
        </div>
        <div class="grid gap-6 p-6 md:grid-cols-[1fr_auto] md:p-8">
          <div>
            <div class="flex items-center gap-1 text-xs uppercase tracking-widest text-muted-foreground">
              <i data-lucide="map-pin" class="h-3 w-3"></i> <?= htmlspecialchars($o['destination_name']) ?>, <?= htmlspecialchars($o['country']) ?>
            </div>
            <h3 class="mt-2 font-display text-3xl"><?= htmlspecialchars($o['title']) ?></h3>
            <p class="mt-2 text-muted-foreground"><?= htmlspecialchars($o['description']) ?></p>
            <div class="mt-4 flex flex-wrap gap-2">
              <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">
                <i data-lucide="calendar" class="mr-1 inline h-3 w-3 align-[-1px]"></i>
                du <?= date('d/m/Y', strtotime($o['date_depart'])) ?> au <?= date('d/m/Y', strtotime($o['date_retour'])) ?>
              </span>
              <span class="rounded-full bg-muted px-3 py-1 text-xs font-medium"><?= (int) $o['seats'] ?> places restantes</span>
              <span class="rounded-full bg-muted px-3 py-1 text-xs font-medium">Vols inclus</span>
            </div>
            <div class="mt-4 flex items-center gap-1 text-sm">
              <i data-lucide="star" class="h-4 w-4 fill-accent text-accent"></i>
              <span class="font-semibold"><?= $o['rating'] ?></span>
            </div>
          </div>
          <div class="flex flex-col justify-between gap-4 border-t border-border pt-6 md:border-l md:border-t-0 md:pl-8 md:pt-0">
            <div>
              <div class="text-xs text-muted-foreground">prix fixe</div>
              <div class="font-display text-4xl"><?= format_fcfa($o['price']) ?></div>
              <div class="text-xs text-muted-foreground">/ pers. · tout inclus</div>
            </div>
            <?php if ((int) $o['seats'] < 1): ?>
              <span class="inline-flex items-center justify-center rounded-full bg-muted px-5 py-3 text-sm font-semibold text-muted-foreground">Complet</span>
            <?php else: ?>
              <a href="reserver.php?offer=<?= (int) $o['id'] ?>" class="inline-flex items-center justify-center gap-2 rounded-full bg-accent px-5 py-3 text-sm font-semibold text-accent-foreground">
                Réserver <i data-lucide="arrow-right" class="h-4 w-4"></i>
              </a>
            <?php endif; ?>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>

<script>
  document.getElementById('budget-range').addEventListener('input', function (e) {
    document.getElementById('budget-value').textContent = parseInt(e.target.value, 10).toLocaleString('fr-FR') + ' FCFA';
  });
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
