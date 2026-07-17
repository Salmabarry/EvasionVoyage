<?php
/**
 * @param string $eyebrow
 * @param string $title
 * @param string|null $intro
 */
function render_page_header(string $eyebrow, string $title, ?string $intro = null): void {
  ?>
  <section class="relative overflow-hidden bg-[image:var(--gradient-ocean)] text-primary-foreground">
    <div class="absolute inset-0 opacity-30" style="background: radial-gradient(circle at 20% 20%, oklch(0.72 0.16 55 / 0.5), transparent 40%)"></div>
    <div class="container-x relative py-24 md:py-32">
      <div class="text-xs uppercase tracking-[0.3em] text-accent"><?= htmlspecialchars($eyebrow) ?></div>
      <h1 class="mt-4 max-w-4xl text-balance font-display text-5xl md:text-7xl"><?= $title ?></h1>
      <?php if ($intro): ?>
        <p class="mt-6 max-w-2xl text-lg text-primary-foreground/75"><?= htmlspecialchars($intro) ?></p>
      <?php endif; ?>
    </div>
  </section>
  <?php
}
