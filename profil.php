<?php
require_once __DIR__ . '/includes/auth.php';
require_login('connexion.php');

$errors = [];
$success = false;

$stmt = db()->prepare('SELECT first_name, last_name, email, phone, created_at FROM users WHERE id = ?');
$stmt->execute([current_user()['id']]);
$profil = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_check($_POST['csrf_token'] ?? null)) {
    $errors[] = 'Session expirée, veuillez réessayer.';
  }

  $firstName = trim($_POST['first_name'] ?? '');
  $lastName = trim($_POST['last_name'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $password = $_POST['password'] ?? '';
  $confirm = $_POST['password_confirm'] ?? '';

  if ($firstName === '' || $lastName === '') {
    $errors[] = 'Le prénom et le nom sont obligatoires.';
  }
  if ($password !== '') {
    if (strlen($password) < 8) {
      $errors[] = 'Le nouveau mot de passe doit contenir au moins 8 caractères.';
    } elseif ($password !== $confirm) {
      $errors[] = 'Les mots de passe ne correspondent pas.';
    }
  }

  if (!$errors) {
    $stmt = db()->prepare('UPDATE users SET first_name = ?, last_name = ?, phone = ? WHERE id = ?');
    $stmt->execute([$firstName, $lastName, $phone !== '' ? $phone : null, current_user()['id']]);
    if ($password !== '') {
      $stmt = db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
      $stmt->execute([password_hash($password, PASSWORD_DEFAULT), current_user()['id']]);
    }
    // Mettre à jour la session pour que le header affiche le nouveau prénom
    $_SESSION['user']['first_name'] = $firstName;
    $_SESSION['user']['last_name'] = $lastName;
    $profil['first_name'] = $firstName;
    $profil['last_name'] = $lastName;
    $profil['phone'] = $phone;
    $success = true;
  }
}

$pageTitle = 'Mon profil — EvasionVoyage';
$pageDescription = 'Modifiez vos informations personnelles.';
$transparentNav = false;
$activePage = 'mes-reservations.php';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/page-header.php';

render_page_header(
  'Mon compte',
  'Mon profil',
  'Modifiez vos informations personnelles et votre mot de passe.'
);
?>

<section class="container-x py-16">
  <div class="mx-auto max-w-xl rounded-3xl border border-border bg-card p-8 shadow-[var(--shadow-soft)] md:p-10">
    <?php if ($success): ?>
      <div class="mb-6 rounded-2xl border border-primary/30 bg-primary/5 p-4 text-sm text-foreground">
        <i data-lucide="check-circle" class="mr-1 inline h-4 w-4 text-primary"></i> Profil mis à jour avec succès.
      </div>
    <?php endif; ?>
    <?php if ($errors): ?>
      <div class="mb-6 rounded-2xl border border-destructive/30 bg-destructive/10 p-4 text-sm text-destructive">
        <ul class="list-inside list-disc space-y-1">
          <?php foreach ($errors as $error): ?><li><?= htmlspecialchars($error) ?></li><?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="post" action="profil.php" class="space-y-4">
      <?= csrf_field() ?>
      <div class="flex gap-3">
        <label class="block w-1/2">
          <span class="text-[10px] uppercase tracking-widest text-muted-foreground">Prénom *</span>
          <input name="first_name" required value="<?= htmlspecialchars($profil['first_name']) ?>"
                 class="mt-2 w-full rounded-2xl border border-border bg-background px-4 py-3 text-sm focus:border-primary focus:outline-none">
        </label>
        <label class="block w-1/2">
          <span class="text-[10px] uppercase tracking-widest text-muted-foreground">Nom *</span>
          <input name="last_name" required value="<?= htmlspecialchars($profil['last_name']) ?>"
                 class="mt-2 w-full rounded-2xl border border-border bg-background px-4 py-3 text-sm focus:border-primary focus:outline-none">
        </label>
      </div>
      <label class="block">
        <span class="text-[10px] uppercase tracking-widest text-muted-foreground">E-mail (non modifiable)</span>
        <input value="<?= htmlspecialchars($profil['email']) ?>" disabled
               class="mt-2 w-full cursor-not-allowed rounded-2xl border border-border bg-muted px-4 py-3 text-sm text-muted-foreground">
      </label>
      <label class="block">
        <span class="text-[10px] uppercase tracking-widest text-muted-foreground">Téléphone</span>
        <input name="phone" value="<?= htmlspecialchars($profil['phone'] ?? '') ?>" placeholder="+221 ..."
               class="mt-2 w-full rounded-2xl border border-border bg-background px-4 py-3 text-sm focus:border-primary focus:outline-none">
      </label>

      <div class="border-t border-border pt-4">
        <div class="text-sm font-semibold">Changer de mot de passe <span class="font-normal text-muted-foreground">(facultatif)</span></div>
        <div class="mt-3 flex gap-3">
          <input type="password" name="password" minlength="8" placeholder="Nouveau mot de passe (8 car. min.)"
                 class="w-1/2 rounded-2xl border border-border bg-background px-4 py-3 text-sm focus:border-primary focus:outline-none">
          <input type="password" name="password_confirm" placeholder="Confirmation"
                 class="w-1/2 rounded-2xl border border-border bg-background px-4 py-3 text-sm focus:border-primary focus:outline-none">
        </div>
      </div>

      <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-accent px-6 py-3.5 text-sm font-semibold text-accent-foreground shadow-[var(--shadow-soft)] transition hover:translate-y-[-1px]">
        Enregistrer les modifications <i data-lucide="check" class="h-4 w-4"></i>
      </button>
    </form>

    <p class="mt-4 text-xs text-muted-foreground">Membre depuis le <?= date('d/m/Y', strtotime($profil['created_at'])) ?></p>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
