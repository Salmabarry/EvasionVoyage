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
  "SELECT u.id, u.first_name, u.last_name, u.email, u.phone, u.is_admin, u.created_at,
          (SELECT COUNT(*) FROM bookings b WHERE b.user_id = u.id) AS bookings_count
   FROM users u
   ORDER BY u.created_at DESC"
)->fetchAll();

$pageTitle = 'Utilisateurs — Admin EvasionVoyage';
$activeAdmin = 'utilisateurs';
$pageSubtitle = count($users) . ' compte(s) enregistré(s)';
require __DIR__ . '/includes/layout-top.php';
?>

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
              </td>
              <td class="text-right">
                <?php if ((int) $u['id'] === (int) $admin['id']): ?>
                  <span class="text-xs text-muted-foreground">Vous</span>
                <?php else: ?>
                  <form method="post" action="utilisateurs.php" class="inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="toggle_admin">
                    <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                    <button type="submit" class="mr-2 inline-flex items-center gap-1 rounded-full border border-border px-3 py-1.5 text-xs font-medium hover:bg-muted">
                      <i data-lucide="shield" class="h-3 w-3"></i> <?= $u['is_admin'] ? 'Rétrograder' : 'Promouvoir admin' ?>
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
