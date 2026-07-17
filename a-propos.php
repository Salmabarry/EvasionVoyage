<?php
$pageTitle = 'À propos — EvasionVoyage';
$pageDescription = 'Depuis 2012, EvasionVoyage conçoit des voyages sur mesure pensés comme des œuvres. Notre histoire, notre équipe, notre engagement.';
$transparentNav = false;
$activePage = 'a-propos.php';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/page-header.php';

render_page_header(
  'Notre maison',
  'Voyager, c\'est apprendre à regarder autrement.',
  'Fondée en 2012 par une famille d\'explorateurs, EvasionVoyage est devenue la référence française du voyage sur mesure haut de gamme.'
);
?>

<section class="container-x grid gap-16 py-24 md:grid-cols-2 md:items-center md:py-32">
  <div class="relative aspect-[4/5] overflow-hidden rounded-[2.5rem]">
    <img src="assets/img/about-traveler.jpg" alt="Voyageuse au sommet" loading="lazy" class="h-full w-full object-cover">
    <div class="absolute bottom-6 left-6 rounded-2xl bg-white/95 px-5 py-4 shadow-[var(--shadow-lift)] backdrop-blur">
      <div class="font-display text-3xl">14 ans</div>
      <div class="text-xs uppercase tracking-widest text-muted-foreground">à écrire des voyages</div>
    </div>
  </div>
  <div>
    <div class="text-xs uppercase tracking-[0.3em] text-accent">Notre histoire</div>
    <h2 class="mt-3 font-display text-4xl md:text-5xl">Un voyage se dessine — il ne se vend pas.</h2>
    <p class="mt-6 text-muted-foreground">
      Nous croyons qu'un voyage réussi ne se mesure pas au nombre d'étapes ni à la longueur des brochures, mais à la justesse d'un instant : un lever de soleil trouvé au bon endroit, une rencontre imprévue, un silence.
    </p>
    <p class="mt-4 text-muted-foreground">
      Notre équipe de dix concepteurs-voyageurs sillonne le monde pour cartographier ces moments, puis les tisse dans des itinéraires uniques, à votre image.
    </p>
  </div>
</section>

<section class="bg-cream py-24">
  <div class="container-x">
    <h2 class="max-w-3xl font-display text-4xl md:text-5xl">Ce qui nous tient debout.</h2>
    <div class="mt-14 grid gap-6 md:grid-cols-4">
      <?php
      $pillars = [
        ['i' => 'sparkles', 't' => 'Curation extrême', 'd' => 'Un lieu sur cinq entre dans notre sélection.'],
        ['i' => 'globe-2', 't' => 'Voyage responsable', 'd' => 'Partenaires locaux, empreinte compensée à 100%.'],
        ['i' => 'users', 't' => 'Concierge dédié', 'd' => 'Un interlocuteur unique, avant, pendant, après.'],
        ['i' => 'award', 't' => 'Récompensé', 'd' => "Trophée du Tourisme d'Exception 2024 & 2025."],
      ];
      foreach ($pillars as $v): ?>
        <div class="rounded-3xl border border-border bg-card p-8">
          <i data-lucide="<?= $v['i'] ?>" class="h-6 w-6 text-accent"></i>
          <div class="mt-4 font-display text-xl"><?= $v['t'] ?></div>
          <p class="mt-2 text-sm text-muted-foreground"><?= $v['d'] ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="container-x py-24">
  <div class="grid gap-16 md:grid-cols-2">
    <div>
      <div class="text-xs uppercase tracking-[0.3em] text-accent">L'équipe</div>
      <h2 class="mt-3 font-display text-4xl md:text-5xl">Dix voix. Un même sens du détail.</h2>
      <p class="mt-6 text-muted-foreground">
        De Tokyo à Reykjavik, chaque membre d'EvasionVoyage est expert d'une région qu'il ou elle a arpentée pendant des années. Un savoir vécu, jamais lu.
      </p>
    </div>
    <ul class="grid gap-4 sm:grid-cols-2">
      <?php
      $team = [
        ['Camille L.', 'Directrice · Asie'],
        ['Yasmine A.', 'Maghreb & Moyen-Orient'],
        ['Julien P.', 'Grand Nord'],
        ['Inès R.', 'Méditerranée'],
        ['Pierre D.', 'Amériques'],
        ['Sarah M.', 'Concierge en chef'],
      ];
      foreach ($team as [$n, $r]): ?>
        <li class="rounded-2xl border border-border bg-card p-5">
          <div class="font-display text-lg"><?= htmlspecialchars($n) ?></div>
          <div class="text-xs uppercase tracking-widest text-muted-foreground"><?= htmlspecialchars($r) ?></div>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
