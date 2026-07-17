<?php
require_once __DIR__ . '/includes/auth.php';

$errors = [];
$old = ['first_name' => '', 'last_name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $old['first_name'] = trim($_POST['first_name'] ?? '');
  $old['last_name'] = trim($_POST['last_name'] ?? '');
  $old['email'] = trim($_POST['email'] ?? '');
  $password = (string) ($_POST['password'] ?? '');
  $termsAccepted = isset($_POST['terms']);

  if (!csrf_check($_POST['csrf_token'] ?? null)) {
    $errors[] = 'Session expirée, veuillez réessayer.';
  }
  if ($old['first_name'] === '' || $old['last_name'] === '') {
    $errors[] = 'Merci de renseigner votre prénom et votre nom.';
  }
  if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = "L'adresse email n'est pas valide.";
  }
  if (strlen($password) < 8) {
    $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
  }
  if (!$termsAccepted) {
    $errors[] = 'Merci d\'accepter les conditions générales et la politique de confidentialité.';
  }

  if (!$errors) {
    $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$old['email']]);
    if ($stmt->fetch()) {
      $errors[] = 'Un compte existe déjà avec cet email.';
    }
  }

  if (!$errors) {
    $stmt = db()->prepare(
      'INSERT INTO users (first_name, last_name, email, password_hash) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([
      $old['first_name'],
      $old['last_name'],
      $old['email'],
      password_hash($password, PASSWORD_DEFAULT),
    ]);

    login_user([
      'id' => (int) db()->lastInsertId(),
      'first_name' => $old['first_name'],
      'last_name' => $old['last_name'],
      'email' => $old['email'],
    ]);

    $redirect = $_SESSION['redirect_after_login'] ?? 'index.php';
    unset($_SESSION['redirect_after_login']);
    header('Location: ' . $redirect);
    exit;
  }
}

$pageTitle = 'Créer un compte — EvasionVoyage';
$pageDescription = 'Ouvrez votre espace voyageur EvasionVoyage en quelques secondes.';
$transparentNav = true;
$activePage = 'inscription.php';
require __DIR__ . '/includes/header.php';
?>

<section class="grid min-h-screen md:grid-cols-2">
  <div class="flex items-center justify-center px-6 py-24 md:px-16">
    <div class="w-full max-w-md">
      <div class="text-xs uppercase tracking-[0.3em] text-accent">Inscription</div>
      <h1 class="mt-3 font-display text-4xl">Ouvrez votre carnet de voyage.</h1>
      <p class="mt-2 text-sm text-muted-foreground">
        Déjà membre ?
        <a href="connexion.php" class="font-semibold text-primary underline underline-offset-4">Se connecter</a>
      </p>

      <?php if ($errors): ?>
        <div class="mt-6 rounded-2xl border border-destructive/30 bg-destructive/10 p-4 text-sm text-destructive">
          <ul class="list-inside list-disc space-y-1">
            <?php foreach ($errors as $error): ?>
              <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="post" action="inscription.php" class="mt-10 space-y-4">
        <?= csrf_field() ?>
        <div class="grid gap-4 sm:grid-cols-2">
          <label class="flex items-center gap-3 rounded-2xl border border-border bg-background px-4 py-3 focus-within:border-primary">
            <span class="text-muted-foreground"><i data-lucide="user" class="h-4 w-4"></i></span>
            <input type="text" name="first_name" value="<?= htmlspecialchars($old['first_name']) ?>" placeholder="Prénom" class="w-full bg-transparent text-sm placeholder:text-foreground/40 focus:outline-none">
          </label>
          <label class="flex items-center gap-3 rounded-2xl border border-border bg-background px-4 py-3 focus-within:border-primary">
            <span class="text-muted-foreground"><i data-lucide="user" class="h-4 w-4"></i></span>
            <input type="text" name="last_name" value="<?= htmlspecialchars($old['last_name']) ?>" placeholder="Nom" class="w-full bg-transparent text-sm placeholder:text-foreground/40 focus:outline-none">
          </label>
        </div>
        <label class="flex items-center gap-3 rounded-2xl border border-border bg-background px-4 py-3 focus-within:border-primary">
          <span class="text-muted-foreground"><i data-lucide="mail" class="h-4 w-4"></i></span>
          <input type="email" name="email" value="<?= htmlspecialchars($old['email']) ?>" placeholder="Email" class="w-full bg-transparent text-sm placeholder:text-foreground/40 focus:outline-none">
        </label>
        <label class="flex items-center gap-3 rounded-2xl border border-border bg-background px-4 py-3 focus-within:border-primary">
          <span class="text-muted-foreground"><i data-lucide="lock" class="h-4 w-4"></i></span>
          <input type="password" name="password" placeholder="Mot de passe (8+ caractères)" class="w-full bg-transparent text-sm placeholder:text-foreground/40 focus:outline-none">
        </label>

        <label class="flex items-start gap-2 text-xs text-muted-foreground">
          <input type="checkbox" name="terms" class="mt-1 accent-[var(--accent)]">
          J'accepte les conditions générales et la politique de confidentialité.
        </label>

        <button type="submit" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-full bg-accent px-6 py-3.5 text-sm font-semibold text-accent-foreground shadow-[var(--shadow-soft)] transition hover:translate-y-[-1px]">
          Créer mon compte <i data-lucide="arrow-right" class="h-4 w-4"></i>
        </button>
      </form>

      <p class="mt-6 text-xs text-muted-foreground">
        En créant un compte, vous rejoignez 48 000 voyageurs qui composent leurs séjours avec EvasionVoyage.
      </p>
    </div>
  </div>

  <div class="relative hidden md:block">
    <img src="assets/img/dest-bali.jpg" alt="" class="absolute inset-0 h-full w-full object-cover">
    <div class="absolute inset-0 bg-gradient-to-tl from-primary/80 to-transparent"></div>
    <div class="absolute inset-0 flex flex-col justify-end p-12 text-white">
      <div class="max-w-md">
        <div class="text-xs uppercase tracking-[0.3em] text-accent">Bienvenue</div>
        <h2 class="mt-3 font-display text-5xl">Le monde n'attend que vous.</h2>
        <p class="mt-4 text-white/80">Un compte, tous vos voyages, votre concierge dédié.</p>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
