<?php
$pageTitle = 'Offres de voyage — EvasionVoyage';
$pageDescription = 'Recherchez et comparez nos offres de séjours par destination, dates, budget et type de voyage.';
$transparentNav = false;
$activePage = 'offres.php';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/page-header.php';
require __DIR__ . '/includes/destinations.php';

$types = ['Vacances', 'Affaires', 'Aventure', 'Famille', 'Luxe'];
$defaultBudget = 1300000;

render_page_header(
  'Réservation',
  'Composez le voyage qui vous ressemble.',
  'Filtrez par pays, dates, budget ou type de séjour. Nos experts s\'occupent du reste.'
);
?>

<section class="container-x relative z-10 -mt-16 md:-mt-20">
  <div class="rounded-3xl border border-border bg-card p-6 shadow-[var(--shadow-lift)] md:p-8">
    <div class="grid gap-4 md:grid-cols-3 lg:grid-cols-6">
      <label class="flex items-center gap-3 rounded-2xl bg-muted px-4 py-3">
        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-background text-primary"><i data-lucide="map-pin" class="h-4 w-4"></i></span>
        <span class="min-w-0 flex-1">
          <span class="block text-[10px] uppercase tracking-widest text-muted-foreground">Pays / Ville</span>
          <input type="text" placeholder="Ex : Grèce" class="w-full bg-transparent text-sm font-medium placeholder:text-foreground/40 focus:outline-none">
        </span>
      </label>
      <label class="flex items-center gap-3 rounded-2xl bg-muted px-4 py-3">
        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-background text-primary"><i data-lucide="calendar" class="h-4 w-4"></i></span>
        <span class="min-w-0 flex-1">
          <span class="block text-[10px] uppercase tracking-widest text-muted-foreground">Départ</span>
          <input type="date" placeholder="jj/mm/aaaa" class="w-full bg-transparent text-sm font-medium placeholder:text-foreground/40 focus:outline-none">
        </span>
      </label>
      <label class="flex items-center gap-3 rounded-2xl bg-muted px-4 py-3">
        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-background text-primary"><i data-lucide="calendar" class="h-4 w-4"></i></span>
        <span class="min-w-0 flex-1">
          <span class="block text-[10px] uppercase tracking-widest text-muted-foreground">Retour</span>
          <input type="date" placeholder="jj/mm/aaaa" class="w-full bg-transparent text-sm font-medium placeholder:text-foreground/40 focus:outline-none">
        </span>
      </label>
      <label class="flex items-center gap-3 rounded-2xl bg-muted px-4 py-3">
        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-background text-primary"><i data-lucide="users" class="h-4 w-4"></i></span>
        <span class="min-w-0 flex-1">
          <span class="block text-[10px] uppercase tracking-widest text-muted-foreground">Voyageurs</span>
          <input type="text" placeholder="2 adultes" class="w-full bg-transparent text-sm font-medium placeholder:text-foreground/40 focus:outline-none">
        </span>
      </label>

      <div class="rounded-2xl bg-muted px-4 py-3">
        <div class="flex items-center gap-2 text-[10px] uppercase tracking-widest text-muted-foreground">
          <i data-lucide="wallet" class="h-3 w-3"></i> Budget max
        </div>
        <div class="mt-1 flex items-center justify-between text-sm font-semibold">
          <span id="budget-value"><?= format_fcfa($defaultBudget) ?></span>
          <span class="text-muted-foreground">/ pers.</span>
        </div>
        <input
          type="range"
          min="300000"
          max="1700000"
          step="25000"
          value="<?= $defaultBudget ?>"
          id="budget-range"
          class="mt-2 w-full accent-[var(--accent)]"
        >
      </div>

      <button type="button" class="flex items-center justify-center gap-2 rounded-2xl bg-primary px-6 py-3 text-sm font-semibold text-primary-foreground transition hover:bg-primary/90">
        <i data-lucide="plane" class="h-4 w-4"></i> Rechercher
      </button>
    </div>

    <div class="mt-6 flex flex-wrap gap-2 border-t border-border pt-6" id="type-filters">
      <span class="text-xs uppercase tracking-widest text-muted-foreground">Type</span>
      <?php foreach ($types as $t): ?>
        <button
          type="button"
          data-type="<?= htmlspecialchars($t) ?>"
          class="type-btn rounded-full px-4 py-1.5 text-xs font-semibold transition <?= $t === 'Vacances' ? 'bg-accent text-accent-foreground is-active' : 'bg-muted text-foreground hover:bg-secondary' ?>"
        >
          <?= htmlspecialchars($t) ?>
        </button>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="container-x py-20">
  <div class="mb-8 flex items-center justify-between">
    <h2 class="font-display text-3xl md:text-4xl">
      <span id="offer-count"><?= count(array_filter($destinations, fn($d) => $d['price'] <= $defaultBudget)) ?></span> offres correspondent à vos critères
    </h2>
    <div class="hidden text-sm text-muted-foreground md:block">Tri : recommandé</div>
  </div>

  <div class="space-y-6" id="offer-list">
    <?php foreach ($destinations as $d): ?>
      <article data-price="<?= $d['price'] ?>" class="offer-card grid gap-6 overflow-hidden rounded-3xl border border-border bg-card shadow-[var(--shadow-soft)] md:grid-cols-[320px_1fr]">
        <div class="relative h-64 md:h-auto">
          <img src="<?= $d['image'] ?>" alt="<?= htmlspecialchars($d['name']) ?>" loading="lazy" class="absolute inset-0 h-full w-full object-cover">
          <div class="absolute left-4 top-4 rounded-full bg-white/95 px-3 py-1 text-xs font-semibold text-primary">
            <?= htmlspecialchars($d['category']) ?>
          </div>
        </div>
        <div class="grid gap-6 p-6 md:grid-cols-[1fr_auto] md:p-8">
          <div>
            <div class="flex items-center gap-1 text-xs uppercase tracking-widest text-muted-foreground">
              <i data-lucide="map-pin" class="h-3 w-3"></i> <?= htmlspecialchars($d['country']) ?>
            </div>
            <h3 class="mt-2 font-display text-3xl"><?= htmlspecialchars($d['name']) ?></h3>
            <p class="mt-2 text-muted-foreground"><?= htmlspecialchars($d['tagline']) ?></p>
            <div class="mt-4 flex flex-wrap gap-2">
              <?php foreach (['Vols inclus', $d['nights'] . ' nuits', 'Petit-déj', 'Guide privé'] as $tag): ?>
                <span class="rounded-full bg-muted px-3 py-1 text-xs font-medium"><?= htmlspecialchars($tag) ?></span>
              <?php endforeach; ?>
            </div>
            <div class="mt-4 flex items-center gap-1 text-sm">
              <i data-lucide="star" class="h-4 w-4 fill-accent text-accent"></i>
              <span class="font-semibold"><?= $d['rating'] ?></span>
              <span class="text-muted-foreground">· 320 avis vérifiés</span>
            </div>
          </div>
          <div class="flex flex-col justify-between gap-4 border-t border-border pt-6 md:border-l md:border-t-0 md:pl-8 md:pt-0">
            <div>
              <div class="text-xs text-muted-foreground">à partir de</div>
              <div class="font-display text-4xl"><?= format_fcfa($d['price']) ?></div>
              <div class="text-xs text-muted-foreground">/ pers. · tout inclus</div>
            </div>
            <a href="reserver.php?slug=<?= urlencode($d['slug']) ?>" class="inline-flex items-center justify-center gap-2 rounded-full bg-accent px-5 py-3 text-sm font-semibold text-accent-foreground">
              Réserver <i data-lucide="arrow-right" class="h-4 w-4"></i>
            </a>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
