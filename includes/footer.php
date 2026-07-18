</main>

<footer class="bg-primary text-primary-foreground">
  <div class="container-x py-20">
    <div class="grid gap-12 md:grid-cols-4">
      <div class="md:col-span-2">
        <div class="flex items-center gap-2 font-display text-2xl">
          <img src="assets/img/icone-blanc.svg" alt="EvasionVoyage" class="h-10 w-10 object-contain">
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
          <li>Dakar, Sénégal</li>
          <li>+221 77 145 49 28 (WhatsApp)</li>
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

<!-- Bouton WhatsApp flottant : visible sur tout le site, même en défilant -->
<a href="https://wa.me/221771454928?text=Bonjour%20EvasionVoyage%2C%20je%20souhaite%20des%20informations"
   target="_blank" rel="noopener" aria-label="Écrivez-nous sur WhatsApp"
   style="position:fixed;bottom:24px;right:24px;z-index:60;width:56px;height:56px;border-radius:50%;background:#25d366;display:flex;align-items:center;justify-content:center;box-shadow:0 6px 18px rgba(0,0,0,.3);transition:transform .2s">
  <svg viewBox="0 0 24 24" width="30" height="30" fill="#fff"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
</a>

<script src="assets/js/main.js"></script>
<script>
  // Menu déroulant du compte (prénom dans la barre de navigation)
  (function () {
    const btn = document.getElementById('menu-compte-btn');
    const panel = document.getElementById('menu-compte-panel');
    if (btn && panel) {
      btn.addEventListener('click', function (e) {
        e.stopPropagation();
        panel.classList.toggle('hidden');
      });
      document.addEventListener('click', function (e) {
        if (!panel.contains(e.target)) panel.classList.add('hidden');
      });
    }
  })();

  // Œil afficher/masquer sur tous les champs mot de passe du site.
  // Le conteneur reprend les classes de largeur du champ (w-full, w-1/2, flex-1...)
  // pour ne pas casser la mise en page, et le champ remplit son conteneur.
  document.querySelectorAll('input[type="password"]').forEach(function (input) {
    const wrap = document.createElement('div');
    wrap.style.position = 'relative';
    ['w-full', 'w-1/2', 'w-1/3', 'flex-1', 'mt-2'].forEach(function (c) {
      if (input.classList.contains(c)) {
        input.classList.remove(c);
        wrap.classList.add(c);
      }
    });
    input.classList.add('w-full');
    input.style.paddingRight = '2.7rem'; // le texte ne passe pas sous l'œil
    input.parentNode.insertBefore(wrap, input);
    wrap.appendChild(input);

    const btn = document.createElement('button');
    btn.type = 'button';
    btn.setAttribute('aria-label', 'Afficher / masquer le mot de passe');
    btn.textContent = '👁';
    btn.style.cssText = 'position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:15px;line-height:1;opacity:.55;padding:0';
    btn.addEventListener('click', function () {
      const visible = input.type === 'text';
      input.type = visible ? 'password' : 'text';
      btn.style.opacity = visible ? '.55' : '1';
    });
    wrap.appendChild(btn);
  });
</script>
</body>
</html>
