<?php
require __DIR__ . '/includes/guard.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_check($_POST['csrf_token'] ?? null)) {
    $errors[] = 'Session expirée, veuillez réessayer.';
  } else {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $currentPassword = (string) ($_POST['current_password'] ?? '');
    $newPassword = (string) ($_POST['new_password'] ?? '');

    if ($firstName === '' || $lastName === '') {
      $errors[] = 'Merci de renseigner votre prénom et votre nom.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $errors[] = "L'adresse email n'est pas valide.";
    }

    $stmt = db()->prepare('SELECT password_hash FROM users WHERE id = ?');
    $stmt->execute([$admin['id']]);
    $currentHash = $stmt->fetchColumn();

    if ($newPassword !== '' || $currentPassword !== '') {
      if (!password_verify($currentPassword, $currentHash)) {
        $errors[] = 'Mot de passe actuel incorrect.';
      } elseif (strlen($newPassword) < 8) {
        $errors[] = 'Le nouveau mot de passe doit contenir au moins 8 caractères.';
      }
    }

    if (!$errors) {
      $stmt = db()->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
      $stmt->execute([$email, $admin['id']]);
      if ($stmt->fetch()) {
        $errors[] = 'Un autre compte utilise déjà cet email.';
      }
    }

    if (!$errors) {
      if ($newPassword !== '') {
        $stmt = db()->prepare('UPDATE users SET first_name = ?, last_name = ?, email = ?, password_hash = ? WHERE id = ?');
        $stmt->execute([$firstName, $lastName, $email, password_hash($newPassword, PASSWORD_DEFAULT), $admin['id']]);
      } else {
        $stmt = db()->prepare('UPDATE users SET first_name = ?, last_name = ?, email = ? WHERE id = ?');
        $stmt->execute([$firstName, $lastName, $email, $admin['id']]);
      }

      $_SESSION['user']['first_name'] = $firstName;
      $_SESSION['user']['last_name'] = $lastName;
      $_SESSION['user']['email'] = $email;
      $admin = current_user();
      $success = true;
    }
  }
}

$pageTitle = 'Paramètres — Admin EvasionVoyage';
$activeAdmin = 'parametres';
$pageSubtitle = 'Gérez votre profil administrateur';
require __DIR__ . '/includes/layout-top.php';
?>

<?php if ($success): ?>
  <div class="admin-card mb-5 border-transparent p-4 text-sm" style="background: oklch(0.8 0.13 155 / 0.15); color: oklch(0.35 0.14 155);">
    Profil mis à jour avec succès.
  </div>
<?php endif; ?>

<?php if ($errors): ?>
  <div class="admin-card mb-5 border-destructive/30 bg-destructive/10 p-4 text-sm text-destructive">
    <ul class="list-inside list-disc space-y-1">
      <?php foreach ($errors as $error): ?><li><?= htmlspecialchars($error) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<div class="grid gap-6 lg:grid-cols-3">
  <div class="admin-card p-6 text-center lg:col-span-1">
    <span class="mx-auto grid h-20 w-20 place-items-center rounded-full text-2xl font-bold" style="background: var(--gradient-ember, var(--accent)); color: var(--accent-foreground);">
      <?= htmlspecialchars(mb_strtoupper(mb_substr($admin['first_name'], 0, 1) . mb_substr($admin['last_name'], 0, 1))) ?>
    </span>
    <div class="mt-4 font-display text-xl"><?= htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']) ?></div>
    <div class="text-sm text-muted-foreground"><?= htmlspecialchars($admin['email']) ?></div>
    <div class="mt-3"><span class="status-pill status-info">Administrateur</span></div>
  </div>

  <div class="admin-card p-6 lg:col-span-2 md:p-8">
    <h2 class="font-display text-xl">Informations du profil</h2>
    <form method="post" action="parametres.php" class="mt-5 grid gap-4 sm:grid-cols-2">
      <?= csrf_field() ?>
      <label class="block text-sm">
        <span class="text-[10px] uppercase tracking-widest text-muted-foreground">Prénom</span>
        <input type="text" name="first_name" value="<?= htmlspecialchars($admin['first_name']) ?>" class="mt-1 w-full rounded-xl border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none">
      </label>
      <label class="block text-sm">
        <span class="text-[10px] uppercase tracking-widest text-muted-foreground">Nom</span>
        <input type="text" name="last_name" value="<?= htmlspecialchars($admin['last_name']) ?>" class="mt-1 w-full rounded-xl border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none">
      </label>
      <label class="block text-sm sm:col-span-2">
        <span class="text-[10px] uppercase tracking-widest text-muted-foreground">Email</span>
        <input type="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" class="mt-1 w-full rounded-xl border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none">
      </label>

      <div class="mt-2 border-t border-border pt-4 sm:col-span-2">
        <div class="text-sm font-semibold">Changer le mot de passe</div>
        <p class="text-xs text-muted-foreground">Laissez vide pour ne pas modifier le mot de passe.</p>
      </div>
      <label class="block text-sm">
        <span class="text-[10px] uppercase tracking-widest text-muted-foreground">Mot de passe actuel</span>
        <input type="password" name="current_password" class="mt-1 w-full rounded-xl border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none">
      </label>
      <label class="block text-sm">
        <span class="text-[10px] uppercase tracking-widest text-muted-foreground">Nouveau mot de passe</span>
        <input type="password" name="new_password" placeholder="8 caractères minimum" class="mt-1 w-full rounded-xl border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none">
      </label>

      <div class="sm:col-span-2">
        <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-accent px-5 py-2.5 text-sm font-semibold text-accent-foreground">
          <i data-lucide="save" class="h-4 w-4"></i> Enregistrer les modifications
        </button>
      </div>
    </form>
  </div>
</div>

<?php require __DIR__ . '/includes/layout-bottom.php'; ?>
