</main>

<footer class="bg-primary text-primary-foreground">
  <div class="container-x py-20">
    <div class="grid gap-12 md:grid-cols-4">
      <div class="md:col-span-2">
        <div class="flex items-center gap-2 font-display text-2xl">
          <img src="assets/img/logo-icon.png" alt="EvasionVoyage" class="h-10 w-10 object-contain">
          EvasionVoyage
        </div>
        <p class="mt-4 max-w-md text-primary-foreground/70">
          Concepteur de voyages sur mesure depuis 2012. Nous dessinons des séjours pensés comme des œuvres — précis, sensoriels, mémorables.
        </p>
        <div class="mt-6 flex gap-3">
          <?php foreach (['instagram', 'twitter', 'facebook'] as $icon): ?>
            <a href="#" class="grid h-10 w-10 place-items-center rounded-full border border-primary-foreground/20 transition hover:bg-accent hover:text-accent-foreground hover:border-transparent">
              <i data-lucide="<?= $icon ?>" class="h-4 w-4"></i>
            </a>
          <?php endforeach; ?>
        </div>
      </div>

      <div>
        <div class="mb-4 text-xs uppercase tracking-[0.2em] text-primary-foreground/60">Explorer</div>
        <ul class="space-y-3 text-sm">
          <li><a href="destinations.php" class="hover:text-accent">Destinations</a></li>
          <li><a href="offres.php" class="hover:text-accent">Offres de voyage</a></li>
          <li><a href="a-propos.php" class="hover:text-accent">À propos</a></li>
          <li><a href="contact.php" class="hover:text-accent">Contact</a></li>
        </ul>
      </div>

      <div>
        <div class="mb-4 text-xs uppercase tracking-[0.2em] text-primary-foreground/60">Contact</div>
        <ul class="space-y-3 text-sm text-primary-foreground/80">
          <li>12 rue des Voyages</li>
          <li>75008 Paris, France</li>
          <li>+33 1 89 00 00 00</li>
          <li>hello@evasionvoyage.travel</li>
        </ul>
      </div>
    </div>

    <div class="mt-16 flex flex-col items-start justify-between gap-4 border-t border-primary-foreground/10 pt-8 text-xs text-primary-foreground/50 md:flex-row md:items-center">
      <div>© 2026 EvasionVoyage. Tous droits réservés.</div>
      <div class="flex gap-6">
        <a href="#" class="hover:text-accent">Mentions légales</a>
        <a href="#" class="hover:text-accent">Confidentialité</a>
        <a href="#" class="hover:text-accent">CGV</a>
      </div>
    </div>
  </div>
</footer>

<script src="assets/js/main.js"></script>
</body>
</html>
