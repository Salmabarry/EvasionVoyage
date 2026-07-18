<?php
require __DIR__ . '/includes/guard.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_check($_POST['csrf_token'] ?? null)) {
    $errors[] = 'Session expirée, veuillez réessayer.';
  } else {
    $id = (int) ($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($action === 'approuver') {
      db()->prepare("UPDATE reviews SET status = 'approuve' WHERE id = ?")->execute([$id]);
    } elseif ($action === 'supprimer') {
      db()->prepare('DELETE FROM reviews WHERE id = ?')->execute([$id]);
    }
    header('Location: avis.php' . (isset($_GET['filtre']) ? '?filtre=' . urlencode($_GET['filtre']) : ''));
    exit;
  }
}

$filtre = $_GET['filtre'] ?? '';
$sql = "SELECT r.*, u.first_name, u.last_name, u.email, d.name AS destination_name, d.country
        FROM reviews r
        JOIN users u ON u.id = r.user_id
        JOIN destinations d ON d.id = r.destination_id";
$params = [];
if (in_array($filtre, ['en_attente', 'approuve'], true)) {
  $sql .= ' WHERE r.status = ?';
  $params[] = $filtre;
}
$sql .= " ORDER BY (r.status = 'en_attente') DESC, r.created_at DESC";
$stmt = db()->prepare($sql);
$stmt->execute($params);
$avis = $stmt->fetchAll();

$enAttente = (int) db()->query("SELECT COUNT(*) FROM reviews WHERE status = 'en_attente'")->fetchColumn();

$pageTitle = 'Avis clients — Admin EvasionVoyage';
$activeAdmin = 'avis';
$pageSubtitle = $enAttente . ' avis en attente de validation';
require __DIR__ . '/includes/layout-top.php';
?>

<?php if ($errors): ?>
  <div class="admin-card mb-5 border-destructive/30 bg-destructive/10 p-4 text-sm text-destructive">
    <ul class="list-inside list-disc space-y-1">
      <?php foreach ($errors as $error): ?><li><?= htmlspecialchars($error) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<div class="mb-5 flex gap-2">
  <a href="avis.php" class="rounded-full border border-border px-4 py-1.5 text-xs font-semibold <?= $filtre === '' ? 'bg-primary text-primary-foreground' : 'text-foreground' ?>">Tous</a>
  <a href="avis.php?filtre=en_attente" class="rounded-full border border-border px-4 py-1.5 text-xs font-semibold <?= $filtre === 'en_attente' ? 'bg-primary text-primary-foreground' : 'text-foreground' ?>">En attente (<?= $enAttente ?>)</a>
  <a href="avis.php?filtre=approuve" class="rounded-full border border-border px-4 py-1.5 text-xs font-semibold <?= $filtre === 'approuve' ? 'bg-primary text-primary-foreground' : 'text-foreground' ?>">Publiés</a>
</div>

<div class="admin-card">
  <?php if (!$avis): ?>
    <div class="empty-state"><i data-lucide="message-square-off" class="h-8 w-8"></i><p>Aucun avis pour ce filtre.</p></div>
  <?php else: ?>
    <div class="overflow-x-auto">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Client</th>
            <th>Destination</th>
            <th>Note</th>
            <th>Commentaire</th>
            <th>Déposé le</th>
            <th>Statut</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($avis as $a): ?>
            <tr>
              <td>
                <div class="font-medium"><?= htmlspecialchars($a['first_name'] . ' ' . $a['last_name']) ?></div>
                <div class="text-xs text-muted-foreground"><?= htmlspecialchars($a['email']) ?></div>
              </td>
              <td><?= htmlspecialchars($a['destination_name']) ?> <span class="text-xs text-muted-foreground">(<?= htmlspecialchars($a['country']) ?>)</span></td>
              <td class="text-accent"><?= str_repeat('★', (int) $a['rating']) . str_repeat('☆', 5 - (int) $a['rating']) ?></td>
              <td class="max-w-[260px] text-xs text-muted-foreground"><?= htmlspecialchars($a['comment'] ?? '(sans commentaire)') ?></td>
              <td class="text-muted-foreground"><?= date('d/m/Y', strtotime($a['created_at'])) ?></td>
              <td>
                <span class="status-pill <?= $a['status'] === 'approuve' ? 'status-success' : 'status-pending' ?>">
                  <?= $a['status'] === 'approuve' ? 'Publié' : 'En attente' ?>
                </span>
              </td>
              <td>
                <div class="flex items-center justify-end gap-2">
                  <?php if ($a['status'] === 'en_attente'): ?>
                    <form method="post" action="avis.php<?= $filtre ? '?filtre=' . urlencode($filtre) : '' ?>">
                      <?= csrf_field() ?>
                      <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                      <input type="hidden" name="action" value="approuver">
                      <button type="submit" class="rounded-full bg-accent px-3 py-1.5 text-xs font-semibold text-accent-foreground">Publier</button>
                    </form>
                  <?php endif; ?>
                  <form method="post" action="avis.php<?= $filtre ? '?filtre=' . urlencode($filtre) : '' ?>"
                        onsubmit="return confirm('Supprimer définitivement cet avis ?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                    <input type="hidden" name="action" value="supprimer">
                    <button type="submit" class="rounded-full border border-destructive/40 px-3 py-1.5 text-xs font-semibold text-destructive">Supprimer</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/layout-bottom.php'; ?>
