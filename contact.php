<?php
require_once __DIR__ . '/includes/auth.php';

$errors = [];
$success = false;
$old = [
  'first_name' => '', 'last_name' => '', 'email' => '', 'phone' => '',
  'destination' => '', 'budget' => '', 'message' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  foreach ($old as $key => $_) {
    $old[$key] = trim($_POST[$key] ?? '');
  }

  if (!csrf_check($_POST['csrf_token'] ?? null)) {
    $errors[] = 'Session expirée, veuillez réessayer.';
  }
  if ($old['first_name'] === '' || $old['last_name'] === '') {
    $errors[] = 'Merci de renseigner votre prénom et votre nom.';
  }
  if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = "L'adresse email n'est pas valide.";
  }
  if ($old['message'] === '') {
    $errors[] = 'Merci de décrire votre projet de voyage.';
  }

  if (!$errors) {
    $stmt = db()->prepare(
      'INSERT INTO contact_messages (first_name, last_name, email, phone, destination, budget, message)
       VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
      $old['first_name'],
      $old['last_name'],
      $old['email'],
      $old['phone'] ?: null,
      $old['destination'] ?: null,
      $old['budget'] ?: null,
      $old['message'],
    ]);

    $success = true;
    $old = array_fill_keys(array_keys($old), '');
  }
}

$pageTitle = 'Contact — EvasionVoyage';
$pageDescription = 'Parlez à un concepteur de voyage. Réponse garantie sous 24h.';
$transparentNav = false;
$activePage = 'contact.php';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/page-header.php';

render_page_header(
  'Écrivez-nous',
  'Un projet, une envie ? Racontez.',
  'Un concepteur vous rappelle sous 24h avec une première proposition dessinée à la main.'
);

function contact_input(string $name, string $label, string $placeholder, string $value, string $type = 'text'): void {
  ?>
  <label class="block">
    <span class="text-[10px] uppercase tracking-widest text-muted-foreground"><?= htmlspecialchars($label) ?></span>
    <input
      type="<?= htmlspecialchars($type) ?>"
      name="<?= htmlspecialchars($name) ?>"
      value="<?= htmlspecialchars($value) ?>"
      placeholder="<?= htmlspecialchars($placeholder) ?>"
      class="mt-2 w-full rounded-2xl border border-border bg-background px-4 py-3 text-sm placeholder:text-foreground/40 focus:border-primary focus:outline-none"
    >
  </label>
  <?php
}
?>

<section class="container-x grid gap-12 py-24 md:grid-cols-[2fr_1fr]">
  <div>
    <?php if ($success): ?>
      <div class="rounded-3xl border border-border bg-card p-8 shadow-[var(--shadow-soft)] md:p-10">
        <div class="flex items-center gap-3">
          <span class="grid h-10 w-10 place-items-center rounded-xl bg-[image:var(--gradient-ember)] text-white">
            <i data-lucide="check" class="h-5 w-5"></i>
          </span>
          <h2 class="font-display text-2xl">Message envoyé</h2>
        </div>
        <p class="mt-4 text-sm text-muted-foreground">
          Merci, votre demande a bien été reçue. Un concepteur de voyage vous recontacte sous 24h.
        </p>
      </div>
    <?php else: ?>
      <?php if ($errors): ?>
        <div class="mb-6 rounded-2xl border border-destructive/30 bg-destructive/10 p-4 text-sm text-destructive">
          <ul class="list-inside list-disc space-y-1">
            <?php foreach ($errors as $error): ?>
              <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="post" action="contact.php" class="rounded-3xl border border-border bg-card p-8 shadow-[var(--shadow-soft)] md:p-10">
        <?= csrf_field() ?>
        <div class="grid gap-5 md:grid-cols-2">
          <?php
          contact_input('first_name', 'Prénom', 'Camille', $old['first_name']);
          contact_input('last_name', 'Nom', 'Lefèvre', $old['last_name']);
          contact_input('email', 'Email', 'camille@exemple.fr', $old['email'], 'email');
          contact_input('phone', 'Téléphone', '+221 77 000 00 00', $old['phone'], 'tel');
          contact_input('destination', 'Destination rêvée', 'Ex : Japon, Islande…', $old['destination']);
          contact_input('budget', 'Budget indicatif', 'Ex : 2 000 000 FCFA / pers.', $old['budget']);
          ?>
        </div>
        <label class="mt-5 block">
          <span class="text-[10px] uppercase tracking-widest text-muted-foreground">Votre projet</span>
          <textarea
            name="message"
            rows="5"
            placeholder="Parlez-nous de vos envies, dates approximatives, nombre de voyageurs, ambiance recherchée…"
            class="mt-2 w-full resize-none rounded-2xl border border-border bg-background px-4 py-3 text-sm placeholder:text-foreground/40 focus:border-primary focus:outline-none"
          ><?= htmlspecialchars($old['message']) ?></textarea>
        </label>
        <button type="submit" class="mt-6 inline-flex items-center gap-2 rounded-full bg-accent px-6 py-3 text-sm font-semibold text-accent-foreground shadow-[var(--shadow-soft)] transition hover:translate-y-[-1px]">
          Envoyer ma demande <i data-lucide="send" class="h-4 w-4"></i>
        </button>
      </form>
    <?php endif; ?>
  </div>

  <aside class="space-y-4">
    <?php
    $cards = [
      ['i' => 'map-pin', 't' => 'Bureau', 'd' => "Dakar\nSénégal"],
      ['i' => 'phone', 't' => 'Téléphone / WhatsApp', 'd' => "+221 77 145 49 28\nLun–Sam · 9h–19h"],
      ['i' => 'mail', 't' => 'Email', 'd' => "hello@evasionvoyage.travel\nRéponse sous 24h"],
      ['i' => 'clock', 't' => 'Assistance voyage', 'd' => "24h/24 · 7j/7\ndepuis n'importe où"],
    ];
    foreach ($cards as $c): ?>
      <div class="rounded-2xl border border-border bg-card p-6">
        <div class="flex items-center gap-3">
          <span class="grid h-10 w-10 place-items-center rounded-xl bg-[image:var(--gradient-ember)] text-white">
            <i data-lucide="<?= $c['i'] ?>" class="h-4 w-4"></i>
          </span>
          <div class="font-display text-lg"><?= $c['t'] ?></div>
        </div>
        <div class="mt-3 whitespace-pre-line text-sm text-muted-foreground"><?= htmlspecialchars($c['d']) ?></div>
      </div>
    <?php endforeach; ?>
  </aside>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
