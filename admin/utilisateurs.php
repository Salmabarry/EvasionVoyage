<?php
require __DIR__ . '/includes/guard.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_check($_POST['csrf_token'] ?? null)) {
    $errors[] = 'Session expirée, veuillez réessayer.';
  } else {
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'toggle_admin') {
      if ($id === (int) $admin['id']) {
        $errors[] = 'Vous ne pouvez pas modifier votre propre rôle.';
      } else {
        $stmt = db()->prepare('UPDATE users SET is_admin = NOT is_admin WHERE id = ?');
        $stmt->execute([$id]);
        header('Location: utilisateurs.php');
        exit;
      }
    }

    /* Ajouter (id=0) ou modifier (id>0) un utilisateur */
    if ($action === 'save') {
      $firstName = trim($_POST['first_name'] ?? '');
      $lastName = trim($_POST['last_name'] ?? '');
      $email = trim($_POST['email'] ?? '');
      $phone = trim($_POST['phone'] ?? '');
      $isAdmin = isset($_POST['is_admin']) ? 1 : 0;
      $password = (string) ($_POST['password'] ?? '');

      if ($firstName === '' || $lastName === '') $errors[] = 'Prénom et nom obligatoires.';
      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "L'adresse email n'est pas valide.";
      if ($id === 0 && strlen($password) < 8) $errors[] = 'Mot de passe obligatoire (8 caractères min.) pour un nouveau compte.';
      if ($password !== '' && strlen($password) < 8) $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';

      if (!$errors) {
        $stmt = db()->prepare('SELECT id FROM users WHERE email = ? AND id <> ?');
        $stmt->execute([$email, $id]);
        if ($stmt->fetch()) $errors[] = 'Un autre compte utilise déjà cet email.';
      }

      if (!$errors) {
        if ($id > 0) {
          db()->prepare('UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, is_admin = ? WHERE id = ?')
            ->execute([$firstName, $lastName, $email, $phone !== '' ? $phone : null, $id === (int) $admin['id'] ? 1 : $isAdmin, $id]);
          if ($password !== '') {
            db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([password_hash($password, PASSWORD_DEFAULT), $id]);
          }
        } else {
          db()->prepare('INSERT INTO users (first_name, last_name, email, phone, password_hash, is_admin) VALUES (?, ?, ?, ?, ?, ?)')
            ->execute([$firstName, $lastName, $email, $phone !== '' ? $phone : null, password_hash($password, PASSWORD_DEFAULT), $isAdmin]);
        }
        header('Location: utilisateurs.php');
        exit;
      }
    }

    if ($action === 'toggle_active') {
      if ($id === (int) $admin['id']) {
        $errors[] = 'Vous ne pouvez pas désactiver votre propre compte.';
      } else {
        db()->prepare('UPDATE users SET active = 1 - active WHERE id = ?')->execute([$id]);
        header('Location: utilisateurs.php');
        exit;
      }
    }

    if ($action === 'delete') {
      if ($id === (int) $admin['id']) {
        $errors[] = 'Vous ne pouvez pas supprimer votre propre compte.';
      } else {
        $stmt = db()->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);
        header('Location: utilisateurs.php');
        exit;
      }
    }
  }
}

$users = db()->query(
  "SELECT u.id, u.first_name, u.last_name, u.email, u.phone, u.is_admin, u.active, u.created_at,
          (SELECT COUNT(*) FROM bookings b WHERE b.user_id = u.id) AS bookings_count
   FROM users u
   ORDER BY u.created_at DESC"
)->fetchAll();

/* Formulaire de modification pré-rempli (?edit=ID) */
$editUser = null;
if (isset($_GET['edit'])) {
  $stmt = db()->prepare('SELECT id, first_name, last_name, email, phone, is_admin FROM users WHERE id = ?');
  $stmt->execute([(int) $_GET['edit']]);
  $editUser = $stmt->fetch() ?: null;
}

$pageTitle = 'Utilisateurs — Admin EvasionVoyage';
$activeAdmin = 'utilisateurs';
$pageSubtitle = count($users) . ' compte(s) enregistré(s)';
require __DIR__ . '/includes/layout-top.php';
?>

<!-- Ajouter / modifier un utilisateur -->
<div class="admin-card mb-6">
  <h3 class="mb-4 font-display text-lg"><?= $editUser ? 'Modifier : ' . htmlspecialchars($editUser['first_name'] . ' ' . $editUser['last_name']) : 'Ajouter un utilisateur' ?></h3>
  <form method="post" action="utilisateurs.php" class="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= $editUser ? (int) $editUser['id'] : 0 ?>">
    <input name="first_name" required placeholder="Prénom *" value="<?= htmlspecialchars($editUser['first_name'] ?? '') ?>" class="rounded-xl border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none">
    <input name="last_name" required placeholder="Nom *" value="<?= htmlspecialchars($editUser['last_name'] ?? '') ?>" class="rounded-xl border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none">
    <input name="email" type="email" required placeholder="Email *" value="<?= htmlspecialchars($editUser['email'] ?? '') ?>" class="rounded-xl border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none">
    <input name="phone" placeholder="Téléphone" value="<?= htmlspecialchars($editUser['phone'] ?? '') ?>" class="rounded-xl border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none">
    <input name="password" type="password" minlength="8" placeholder="<?= $editUser ? 'Mot de passe (vide = inchangé)' : 'Mot de passe * (8 car. min.)' ?>" class="rounded-xl border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none">
    <label class="flex items-center gap-2 text-sm text-foreground">
      <input type="checkbox" name="is_admin" <?= !empty($editUser['is_admin']) ? 'checked' : '' ?> class="h-4 w-4 accent-[var(--accent)]"> Administrateur
    </label>
    <div class="flex gap-2 md:col-span-2 lg:col-span-3">
      <button type="submit" class="rounded-full bg-accent px-5 py-2 text-sm font-semibold text-accent-foreground"><?= $editUser ? 'Enregistrer les modifications' : 'Créer le compte' ?></button>
      <?php if ($editUser): ?>
        <a href="utilisateurs.php" class="rounded-full border border-border px-5 py-2 text-sm font-medium text-foreground hover:bg-muted">Annuler</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<?php if ($errors): ?>
  <div class="admin-card mb-5 border-destructive/30 bg-destructive/10 p-4 text-sm text-destructive">
    <ul class="list-inside list-disc space-y-1">
      <?php foreach ($errors as $error): ?><li><?= htmlspecialchars($error) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<div class="admin-card">
  <?php if (!$users): ?>
    <div class="empty-state"><i data-lucide="users" class="h-8 w-8"></i><p>Aucun utilisateur pour le moment.</p></div>
  <?php else: ?>
    <div class="overflow-x-auto">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Client</th>
            <th>Téléphone</th>
            <th>Réservations</th>
            <th>Inscrit le</th>
            <th>Rôle</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <tr>
              <td>
                <div class="flex items-center gap-3">
                  <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-muted text-xs font-bold">
                    <?= htmlspecialchars(mb_strtoupper(mb_substr($u['first_name'], 0, 1) . mb_substr($u['last_name'], 0, 1))) ?>
                  </span>
                  <div class="min-w-0">
                    <div class="font-medium"><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></div>
                    <div class="text-xs text-muted-foreground"><?= htmlspecialchars($u['email']) ?></div>
                  </div>
                </div>
              </td>
              <td class="text-muted-foreground"><?= htmlspecialchars($u['phone'] ?: '—') ?></td>
              <td><?= (int) $u['bookings_count'] ?></td>
              <td class="text-muted-foreground"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
              <td>
                <span class="status-pill <?= $u['is_admin'] ? 'status-info' : 'status-neutral' ?>">
                  <?= $u['is_admin'] ? 'Administrateur' : 'Client' ?>
                </span>
                <?php if (!$u['active']): ?>
                  <span class="status-pill status-danger">Désactivé</span>
                <?php endif; ?>
              </td>
              <td class="text-right">
                <?php if ((int) $u['id'] === (int) $admin['id']): ?>
                  <span class="text-xs text-muted-foreground">Vous</span>
                <?php else: ?>
                  <a href="utilisateurs.php?edit=<?= (int) $u['id'] ?>" class="mr-2 inline-flex items-center gap-1 rounded-full border border-border px-3 py-1.5 text-xs font-medium hover:bg-muted">
                    <i data-lucide="pencil" class="h-3 w-3"></i> Modifier
                  </a>
                  <form method="post" action="utilisateurs.php" class="inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="toggle_admin">
                    <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                    <button type="submit" class="mr-2 inline-flex items-center gap-1 rounded-full border border-border px-3 py-1.5 text-xs font-medium hover:bg-muted">
                      <i data-lucide="shield" class="h-3 w-3"></i> <?= $u['is_admin'] ? 'Rétrograder' : 'Promouvoir admin' ?>
                    </button>
                  </form>
                  <form method="post" action="utilisateurs.php" class="inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="toggle_active">
                    <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                    <button type="submit" class="mr-2 inline-flex items-center gap-1 rounded-full border border-border px-3 py-1.5 text-xs font-medium hover:bg-muted">
                      <i data-lucide="power" class="h-3 w-3"></i> <?= $u['active'] ? 'Désactiver' : 'Réactiver' ?>
                    </button>
                  </form>
                  <form method="post" action="utilisateurs.php" class="inline" onsubmit="return confirm('Supprimer ce compte ? Ses réservations seront aussi supprimées.');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                    <button type="submit" class="inline-flex items-center gap-1 rounded-full border border-destructive/30 px-3 py-1.5 text-xs font-medium text-destructive hover:bg-destructive/10">
                      <i data-lucide="trash-2" class="h-3 w-3"></i> Supprimer
                    </button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/layout-bottom.php'; ?>
