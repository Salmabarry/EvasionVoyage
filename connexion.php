<?php
require_once __DIR__ . '/includes/auth.php';

$errors = [];
$oldEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $oldEmail = trim($_POST['email'] ?? '');
  $password = (string) ($_POST['password'] ?? '');

  if (!csrf_check($_POST['csrf_token'] ?? null)) {
    $errors[] = 'Session expirée, veuillez réessayer.';
  }

  if (!$errors) {
    $stmt = db()->prepare('SELECT id, first_name, last_name, email, password_hash, is_admin FROM users WHERE email = ?');
    $stmt->execute([$oldEmail]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
      $errors[] = 'Email ou mot de passe incorrect.';
    } else {
      login_user($user);
      $defaultRedirect = $user['is_admin'] ? 'admin/index.php' : 'index.php';
      $redirect = $_SESSION['redirect_after_login'] ?? $defaultRedirect;
      unset($_SESSION['redirect_after_login']);
      header('Location: ' . $redirect);
      exit;
    }
  }
}

$pageTitle = 'Connexion — EvasionVoyage';
$pageDescription = 'Accédez à votre espace voyageur EvasionVoyage.';
$transparentNav = true;
$activePage = 'connexion.php';
require __DIR__ . '/includes/header.php';
?>

<section class="grid min-h-screen md:grid-cols-2">
  <div class="relative hidden md:block">
    <img src="assets/img/dest-maldives.jpg" alt="" class="absolute inset-0 h-full w-full object-cover">
    <div class="absolute inset-0 bg-gradient-to-tr from-primary/80 to-transparent"></div>
    <div class="absolute inset-0 flex flex-col justify-end p-12 text-white">
      <div class="max-w-md">
        <div class="text-xs uppercase tracking-[0.3em] text-accent">Bon retour</div>
        <h2 class="mt-3 font-display text-5xl">Le monde vous attend, encore.</h2>
        <p class="mt-4 text-white/80">Retrouvez vos itinéraires, vos billets et vos souvenirs.</p>
      </div>
    </div>
  </div>

  <div class="flex items-center justify-center px-6 py-24 md:px-16">
    <div class="w-full max-w-md">
      <div class="text-xs uppercase tracking-[0.3em] text-accent">Connexion</div>
      <h1 class="mt-3 font-display text-4xl">Se reconnecter à EvasionVoyage.</h1>
      <p class="mt-2 text-sm text-muted-foreground">
        Pas encore de compte ?
        <a href="inscription.php" class="font-semibold text-primary underline underline-offset-4">Créer un compte</a>
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

      <form method="post" action="connexion.php" class="mt-10 space-y-4">
        <?= csrf_field() ?>
        <label class="flex items-center gap-3 rounded-2xl border border-border bg-background px-4 py-3 focus-within:border-primary">
          <span class="text-muted-foreground"><i data-lucide="mail" class="h-4 w-4"></i></span>
          <input type="email" name="email" value="<?= htmlspecialchars($oldEmail) ?>" placeholder="Votre email" class="w-full bg-transparent text-sm placeholder:text-foreground/40 focus:outline-none">
        </label>
        <label class="flex items-center gap-3 rounded-2xl border border-border bg-background px-4 py-3 focus-within:border-primary">
          <span class="text-muted-foreground"><i data-lucide="lock" class="h-4 w-4"></i></span>
          <input type="password" name="password" placeholder="Mot de passe" class="w-full bg-transparent text-sm placeholder:text-foreground/40 focus:outline-none">
        </label>

        <div class="flex items-center justify-between text-sm">
          <label class="flex items-center gap-2 text-muted-foreground">
            <input type="checkbox" class="accent-[var(--accent)]"> Se souvenir de moi
          </label>
          <a href="#" class="font-medium text-primary hover:text-accent">Oublié ?</a>
        </div>

        <button type="submit" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-full bg-primary px-6 py-3.5 text-sm font-semibold text-primary-foreground transition hover:bg-primary/90">
          Se connecter <i data-lucide="arrow-right" class="h-4 w-4"></i>
        </button>
      </form>

      <div class="my-8 flex items-center gap-4 text-xs uppercase tracking-widest text-muted-foreground">
        <div class="h-px flex-1 bg-border"></div> ou <div class="h-px flex-1 bg-border"></div>
      </div>

      <div class="grid gap-2">
        <button class="rounded-full border border-border bg-background px-5 py-3 text-sm font-medium hover:bg-muted">Continuer avec Google</button>
        <button class="rounded-full border border-border bg-background px-5 py-3 text-sm font-medium hover:bg-muted">Continuer avec Apple</button>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
