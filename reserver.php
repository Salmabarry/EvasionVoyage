<?php
require_once __DIR__ . '/includes/auth.php';

$slug = trim($_GET['slug'] ?? $_POST['slug'] ?? '');
if ($slug === '') {
  header('Location: offres.php');
  exit;
}

$stmt = db()->prepare('SELECT * FROM destinations WHERE slug = ?');
$stmt->execute([$slug]);
$destination = $stmt->fetch();

if (!$destination) {
  header('Location: offres.php');
  exit;
}

require_login('connexion.php');

$errors = [];
$success = false;
$travelers = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $travelers = max(1, (int) ($_POST['travelers'] ?? 1));

  if (!csrf_check($_POST['csrf_token'] ?? null)) {
    $errors[] = 'Session expirée, veuillez réessayer.';
  }

  if (!$errors) {
    $stmt = db()->prepare(
      'INSERT INTO bookings (user_id, destination_id, travelers, status) VALUES (?, ?, ?, "en_attente")'
    );
    $stmt->execute([current_user()['id'], $destination['id'], $travelers]);
    $success = true;
  }
}

$pageTitle = 'Réserver ' . $destination['name'] . ' — EvasionVoyage';
$pageDescription = 'Confirmez votre réservation pour ' . $destination['name'] . '.';
$transparentNav = false;
$activePage = 'offres.php';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/page-header.php';

require_once __DIR__ . '/includes/destinations.php';

render_page_header(
  'Réservation',
  $destination['name'] . ', ' . $destination['country'],
  $destination['tagline']
);
?>

<section class="container-x py-16">
  <div class="mx-auto max-w-2xl overflow-hidden rounded-3xl border border-border bg-card shadow-[var(--shadow-soft)]">
    <div class="relative h-56">
      <img src="<?= htmlspecialchars($destination['image']) ?>" alt="<?= htmlspecialchars($destination['name']) ?>" class="absolute inset-0 h-full w-full object-cover">
    </div>

    <div class="p-8 md:p-10">
      <?php if ($success): ?>
        <div class="flex items-center gap-3">
          <span class="grid h-10 w-10 place-items-center rounded-xl bg-[image:var(--gradient-ember)] text-white">
            <i data-lucide="check" class="h-5 w-5"></i>
          </span>
          <h2 class="font-display text-2xl">Réservation enregistrée</h2>
        </div>
        <p class="mt-4 text-sm text-muted-foreground">
          Merci <?= htmlspecialchars(current_user()['first_name']) ?>, votre demande de réservation pour
          <strong><?= htmlspecialchars($destination['name']) ?></strong> (<?= (int) $travelers ?> voyageur<?= $travelers > 1 ? 's' : '' ?>)
          a bien été enregistrée. Un concepteur de voyage vous contacte sous 24h pour finaliser les détails.
        </p>
        <a href="offres.php" class="mt-6 inline-flex items-center gap-2 rounded-full border border-border px-5 py-3 text-sm font-semibold text-foreground hover:bg-muted">
          Voir d'autres offres <i data-lucide="arrow-right" class="h-4 w-4"></i>
        </a>
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

        <div class="flex items-end justify-between border-b border-border pb-6">
          <div>
            <div class="text-xs text-muted-foreground"><?= (int) $destination['nights'] ?> nuits · dès</div>
            <div class="font-display text-3xl"><?= format_fcfa($destination['price']) ?></div>
            <div class="text-xs text-muted-foreground">/ pers. · tout inclus</div>
          </div>
          <div class="flex items-center gap-1 text-sm">
            <i data-lucide="star" class="h-4 w-4 fill-accent text-accent"></i>
            <span class="font-semibold"><?= htmlspecialchars($destination['rating']) ?></span>
          </div>
        </div>

        <form method="post" action="reserver.php" class="mt-6 space-y-4">
          <?= csrf_field() ?>
          <input type="hidden" name="slug" value="<?= htmlspecialchars($slug) ?>">
          <label class="block">
            <span class="text-[10px] uppercase tracking-widest text-muted-foreground">Nombre de voyageurs</span>
            <input
              type="number" name="travelers" min="1" max="20" value="<?= (int) $travelers ?>"
              class="mt-2 w-full rounded-2xl border border-border bg-background px-4 py-3 text-sm focus:border-primary focus:outline-none"
            >
          </label>
          <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-accent px-6 py-3.5 text-sm font-semibold text-accent-foreground shadow-[var(--shadow-soft)] transition hover:translate-y-[-1px]">
            Confirmer la réservation <i data-lucide="arrow-right" class="h-4 w-4"></i>
          </button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
