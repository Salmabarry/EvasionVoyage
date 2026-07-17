<?php
$pageTitle = 'Destinations — EvasionVoyage';
$pageDescription = 'Explorez nos 120+ destinations sélectionnées à travers le monde.';
$transparentNav = false;
$activePage = 'destinations.php';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/page-header.php';
require __DIR__ . '/includes/destinations.php';

$categories = ['Toutes', 'Plage', 'Culture', 'Aventure', 'Luxe', 'Nature'];

render_page_header(
  'Explorer',
  '120 destinations. Une seule obsession : l\'exception.',
  "De la Méditerranée aux confins du Pacifique, chaque destination est visitée, validée et racontée par nos experts avant de rejoindre la sélection."
);
?>

<section class="container-x py-16">
  <div class="mb-10 flex flex-wrap gap-2" id="destination-filters">
    <?php foreach ($categories as $c): ?>
      <button
        type="button"
        data-filter="<?= htmlspecialchars($c) ?>"
        class="filter-btn rounded-full border px-5 py-2 text-sm font-medium transition <?= $c === 'Toutes' ? 'border-primary bg-primary text-primary-foreground is-active' : 'border-border bg-background text-foreground hover:border-primary/40' ?>"
      >
        <?= htmlspecialchars($c) ?>
      </button>
    <?php endforeach; ?>
  </div>

  <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3" id="destination-grid">
    <?php foreach ($destinations as $d): ?>
      <a href="offres.php" data-category="<?= htmlspecialchars($d['category']) ?>" class="destination-card group flex flex-col overflow-hidden rounded-3xl border border-border bg-card shadow-[var(--shadow-soft)] transition hover:-translate-y-1 hover:shadow-[var(--shadow-lift)]">
        <div class="relative aspect-[16/9] overflow-hidden">
          <img src="<?= $d['image'] ?>" alt="<?= htmlspecialchars($d['name']) ?>" loading="lazy" class="h-full w-full object-cover transition-transform duration-[1200ms] group-hover:scale-110">
          <div class="absolute right-4 top-4 rounded-full bg-white/95 px-3 py-1 text-xs font-semibold text-primary backdrop-blur">
            <?= htmlspecialchars($d['category']) ?>
          </div>
          <div class="absolute left-4 top-4 flex items-center gap-1 rounded-full bg-black/40 px-3 py-1 text-xs font-medium text-white backdrop-blur">
            <i data-lucide="star" class="h-3 w-3 fill-accent text-accent"></i> <?= $d['rating'] ?>
          </div>
        </div>
        <div class="flex flex-1 flex-col p-6">
          <div class="flex items-center gap-1 text-xs uppercase tracking-widest text-muted-foreground">
            <i data-lucide="map-pin" class="h-3 w-3"></i> <?= htmlspecialchars($d['country']) ?>
          </div>
          <h3 class="mt-2 font-display text-2xl"><?= htmlspecialchars($d['name']) ?></h3>
          <p class="mt-1 text-sm text-muted-foreground"><?= htmlspecialchars($d['tagline']) ?></p>
          <div class="mt-6 flex items-end justify-between border-t border-border pt-4">
            <div>
              <div class="text-xs text-muted-foreground"><?= $d['nights'] ?> nuits · dès</div>
              <div class="font-display text-2xl"><?= format_fcfa($d['price']) ?></div>
            </div>
            <span class="grid h-11 w-11 place-items-center rounded-full bg-primary text-primary-foreground transition group-hover:bg-accent group-hover:text-accent-foreground">
              <i data-lucide="arrow-up-right" class="h-4 w-4"></i>
            </span>
          </div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
