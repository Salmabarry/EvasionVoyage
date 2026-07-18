<?php
$pageTitle = 'EvasionVoyage — Voyages sur mesure & réservation en ligne';
$pageDescription = "EvasionVoyage conçoit des voyages sur mesure vers les plus belles destinations. Réservez séjours, vols et expériences en toute sécurité.";
$transparentNav = true;
$activePage = 'index.php';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/destinations.php';

$featured = array_slice($destinations, 0, 6);
?>

<!-- HERO -->
<section class="relative min-h-screen w-full overflow-hidden">
  <img
    src="assets/img/hero-santorini.jpg"
    alt="Coucher de soleil sur Santorin"
    width="1920"
    height="1200"
    class="absolute inset-0 h-full w-full object-cover"
  >
  <div class="absolute inset-0" style="background: var(--gradient-hero)"></div>
  <div class="absolute inset-0 bg-gradient-to-t from-primary/70 via-transparent to-primary/40"></div>

  <div class="container-x relative flex min-h-screen flex-col justify-end pb-16 pt-40 text-white md:pb-24">
    <div class="max-w-3xl">
      <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-white/25 bg-white/10 px-4 py-1.5 text-xs uppercase tracking-[0.25em] backdrop-blur-md">
        <i data-lucide="sparkles" class="h-3 w-3 text-accent"></i>
        Nouvelle saison 2026
      </div>
      <h1 class="text-balance font-display text-5xl leading-[1.05] md:text-8xl">
        L'art de partir <em class="not-italic text-accent">ailleurs.</em>
      </h1>
      <p class="mt-6 max-w-xl text-lg text-white/85">
        Des voyages dessinés à la main vers les lieux les plus inspirants du monde. Réservation simple, séjours d'exception.
      </p>
    </div>

    <!-- Search card -->
    <div class="mt-12 rounded-3xl border border-white/20 bg-white/95 p-3 shadow-[var(--shadow-lift)] backdrop-blur-xl md:p-4">
      <div class="grid grid-cols-1 gap-2 md:grid-cols-[1.4fr_1fr_1fr_auto]">
        <?php
        $searchFields = [
          ['icon' => 'map-pin', 'label' => 'Destination', 'placeholder' => 'Où partez-vous ?'],
          ['icon' => 'calendar', 'label' => 'Dates', 'placeholder' => 'Ajouter les dates'],
          ['icon' => 'users', 'label' => 'Voyageurs', 'placeholder' => '2 adultes'],
        ];
        foreach ($searchFields as $f): ?>
          <label class="group flex items-center gap-3 rounded-2xl px-4 py-3 transition hover:bg-muted">
            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-muted text-primary group-hover:bg-background">
              <i data-lucide="<?= $f['icon'] ?>" class="h-4 w-4"></i>
            </span>
            <span class="min-w-0 flex-1">
              <span class="block text-[10px] uppercase tracking-widest text-muted-foreground"><?= $f['label'] ?></span>
              <input class="w-full bg-transparent text-sm font-medium text-foreground placeholder:text-foreground/40 focus:outline-none" placeholder="<?= $f['placeholder'] ?>">
            </span>
          </label>
        <?php endforeach; ?>
        <a href="offres.php" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-primary px-6 py-4 text-sm font-semibold text-primary-foreground transition hover:bg-primary/90">
          <i data-lucide="search" class="h-4 w-4"></i> Rechercher
        </a>
      </div>
    </div>
  </div>
</section>

<!-- STATS strip -->
<section class="border-y border-border bg-cream">
  <div class="container-x grid grid-cols-2 gap-8 py-10 md:grid-cols-4">
    <?php
    $stats = [
      ['k' => '120+', 'v' => 'Destinations'],
      ['k' => '48 000', 'v' => 'Voyageurs heureux'],
      ['k' => '14 ans', 'v' => "D'expertise"],
      ['k' => '4.9/5', 'v' => 'Note moyenne'],
    ];
    foreach ($stats as $s): ?>
      <div>
        <div class="font-display text-3xl md:text-4xl"><?= $s['k'] ?></div>
        <div class="mt-1 text-xs uppercase tracking-widest text-muted-foreground"><?= $s['v'] ?></div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- FEATURED DESTINATIONS -->
<section class="container-x py-24 md:py-32">
  <div class="flex items-end justify-between gap-6">
    <div>
      <div class="text-xs uppercase tracking-[0.3em] text-accent">Sélection</div>
      <h2 class="mt-3 max-w-2xl text-balance font-display text-4xl md:text-6xl">
        Destinations qui font battre le cœur.
      </h2>
    </div>
    <a href="destinations.php" class="hidden shrink-0 items-center gap-2 text-sm font-semibold text-primary hover:text-accent md:inline-flex">
      Voir toutes les destinations <i data-lucide="arrow-up-right" class="h-4 w-4"></i>
    </a>
  </div>

  <div class="mt-14 grid gap-6 md:grid-cols-6">
    <?php foreach ($featured as $i => $d): ?>
      <a
        href="destinations.php"
        class="group relative overflow-hidden rounded-3xl bg-muted <?= $i === 0 ? 'md:col-span-4 md:row-span-2' : 'md:col-span-2' ?>"
        style="min-height: <?= $i === 0 ? 560 : 270 ?>px"
      >
        <img
          src="<?= $d['image'] ?>"
          alt="<?= htmlspecialchars($d['name']) ?>"
          loading="lazy"
          class="absolute inset-0 h-full w-full object-cover transition-transform duration-[1200ms] group-hover:scale-105"
        >
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/10 to-transparent"></div>
        <div class="absolute inset-x-0 bottom-0 p-6 text-white md:p-8">
          <div class="flex items-center gap-2 text-xs uppercase tracking-widest text-white/70">
            <i data-lucide="map-pin" class="h-3 w-3"></i> <?= htmlspecialchars($d['country']) ?>
          </div>
          <div class="mt-2 flex items-end justify-between gap-4">
            <h3 class="font-display text-2xl md:text-4xl"><?= htmlspecialchars($d['name']) ?></h3>
            <div class="text-right text-sm">
              <div class="text-white/70">dès</div>
              <div class="font-display text-xl"><?= format_fcfa($d['price']) ?></div>
            </div>
          </div>
          <div class="mt-1 text-sm text-white/80"><?= htmlspecialchars($d['tagline']) ?></div>
        </div>
        <div class="absolute right-5 top-5 grid h-10 w-10 place-items-center rounded-full bg-white/20 backdrop-blur-md transition group-hover:bg-accent">
          <i data-lucide="arrow-up-right" class="h-4 w-4 text-white"></i>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<!-- VALUES -->
<section class="bg-primary py-24 text-primary-foreground md:py-32">
  <div class="container-x grid gap-16 md:grid-cols-[1fr_2fr]">
    <div>
      <div class="text-xs uppercase tracking-[0.3em] text-accent">Notre promesse</div>
      <h2 class="mt-3 font-display text-4xl md:text-5xl">Un voyage n'est pas un produit. C'est un souvenir en devenir.</h2>
    </div>
    <div class="grid gap-8 sm:grid-cols-2">
      <?php
      $values = [
        ['i' => 'plane', 't' => 'Sur mesure', 'd' => 'Chaque itinéraire dessiné selon vos envies, votre rythme, votre budget.'],
        ['i' => 'shield-check', 't' => '100% sécurisé', 'd' => "Paiement crypté, garantie annulation, assistance 24/7 à l'étranger."],
        ['i' => 'heart', 't' => 'Curation humaine', 'd' => 'Nos experts ont vécu chaque destination avant de la proposer.'],
        ['i' => 'sparkles', 't' => 'Expériences rares', 'd' => 'Accès privé, hôtes locaux, adresses jamais dans les guides.'],
      ];
      foreach ($values as $v): ?>
        <div class="rounded-2xl border border-primary-foreground/10 p-6">
          <i data-lucide="<?= $v['i'] ?>" class="h-6 w-6 text-accent"></i>
          <div class="mt-4 font-display text-xl"><?= $v['t'] ?></div>
          <p class="mt-2 text-sm text-primary-foreground/70"><?= $v['d'] ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- TESTIMONIALS -->
<section class="container-x py-24 md:py-32">
  <div class="text-xs uppercase tracking-[0.3em] text-accent">Voix des voyageurs</div>
  <h2 class="mt-3 max-w-3xl font-display text-4xl md:text-6xl">Ils sont revenus transformés.</h2>
  <div class="mt-14 grid gap-6 md:grid-cols-3">
    <?php
    // Avis réels approuvés par l'admin ; à défaut, témoignages de présentation
    $avisAccueil = db()->query(
      "SELECT r.rating, r.comment, u.first_name, d.name AS dest
       FROM reviews r JOIN users u ON u.id = r.user_id JOIN destinations d ON d.id = r.destination_id
       WHERE r.status = 'approuve' AND r.comment IS NOT NULL
       ORDER BY r.created_at DESC LIMIT 3"
    )->fetchAll();

    $testimonials = [];
    foreach ($avisAccueil as $a) {
      $testimonials[] = ['n' => htmlspecialchars($a['first_name']), 'd' => htmlspecialchars($a['dest']), 'q' => htmlspecialchars($a['comment']), 'r' => (int) $a['rating']];
    }
    if (!$testimonials) {
      $testimonials = [
        ['n' => 'Claire M.', 'd' => 'Kyoto, 10 jours', 'q' => "Un itinéraire pensé au millimètre. On a vécu le Japon comme des habitants, pas comme des touristes.", 'r' => 5],
        ['n' => 'Sofiane B.', 'd' => 'Islande, 7 jours', 'q' => "Le lodge sous les aurores, seuls au monde. Une nuit que je raconterai toute ma vie.", 'r' => 5],
        ['n' => 'Léa & Tom', 'd' => 'Maldives, 8 jours', 'q' => "Notre voyage de noces. EvasionVoyage a orchestré chaque détail avec une délicatesse rare.", 'r' => 5],
      ];
    }
    foreach ($testimonials as $t): ?>
      <figure class="rounded-3xl border border-border bg-card p-8 shadow-[var(--shadow-soft)]">
        <div class="flex gap-0.5 text-accent">
          <?php for ($i = 0; $i < $t['r']; $i++): ?>
            <i data-lucide="star" class="h-4 w-4 fill-current"></i>
          <?php endfor; ?>
        </div>
        <blockquote class="mt-4 font-display text-xl leading-snug text-balance">« <?= $t['q'] ?> »</blockquote>
        <figcaption class="mt-6 text-sm">
          <div class="font-semibold"><?= $t['n'] ?></div>
          <div class="text-muted-foreground"><?= $t['d'] ?></div>
        </figcaption>
      </figure>
    <?php endforeach; ?>
  </div>
</section>

<!-- CTA -->
<section class="container-x pb-24">
  <div class="relative overflow-hidden rounded-[2.5rem] bg-[image:var(--gradient-ocean)] p-10 text-primary-foreground md:p-20">
    <div class="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-accent/40 blur-3xl"></div>
    <div class="relative grid gap-10 md:grid-cols-[2fr_1fr] md:items-end">
      <div>
        <h2 class="max-w-2xl text-balance font-display text-4xl md:text-6xl">Prêt à composer votre prochain voyage ?</h2>
        <p class="mt-4 max-w-xl text-primary-foreground/75">
          Parlez-nous de vos envies, on vous répond sous 24h avec une première proposition sur mesure.
        </p>
      </div>
      <div class="flex flex-wrap gap-3 md:justify-end">
        <a href="contact.php" class="rounded-full bg-accent px-6 py-3 text-sm font-semibold text-accent-foreground">
          Nous écrire
        </a>
        <a href="destinations.php" class="rounded-full border border-white/30 px-6 py-3 text-sm font-semibold">
          Explorer
        </a>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
