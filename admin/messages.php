<?php
require __DIR__ . '/includes/guard.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_check($_POST['csrf_token'] ?? null)) {
    $errors[] = 'Session expirée, veuillez réessayer.';
  } else {
    $id = (int) ($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? '';
    if (in_array($status, ['nouveau', 'lu', 'repondu'], true)) {
      $stmt = db()->prepare('UPDATE contact_messages SET status = ? WHERE id = ?');
      $stmt->execute([$status, $id]);
    }
    header('Location: messages.php');
    exit;
  }
}

$messages = db()->query('SELECT * FROM contact_messages ORDER BY created_at DESC')->fetchAll();

$statusMeta = [
  'nouveau' => ['label' => 'Nouveau', 'class' => 'status-info'],
  'lu' => ['label' => 'Lu', 'class' => 'status-neutral'],
  'repondu' => ['label' => 'Répondu', 'class' => 'status-success'],
];

$pageTitle = 'Messages — Admin EvasionVoyage';
$activeAdmin = 'messages';
$pageSubtitle = count($messages) . ' message(s) reçu(s)';
require __DIR__ . '/includes/layout-top.php';
?>

<?php if ($errors): ?>
  <div class="admin-card mb-5 border-destructive/30 bg-destructive/10 p-4 text-sm text-destructive">
    <ul class="list-inside list-disc space-y-1">
      <?php foreach ($errors as $error): ?><li><?= htmlspecialchars($error) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<?php if (!$messages): ?>
  <div class="admin-card">
    <div class="empty-state"><i data-lucide="mail-open" class="h-8 w-8"></i><p>Aucun message pour le moment.</p></div>
  </div>
<?php else: ?>
  <div class="space-y-4">
    <?php foreach ($messages as $m): ?>
      <details class="admin-card group overflow-hidden">
        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 p-5">
          <div class="flex min-w-0 items-center gap-3">
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-muted text-sm font-bold">
              <?= htmlspecialchars(mb_strtoupper(mb_substr($m['first_name'], 0, 1))) ?>
            </span>
            <div class="min-w-0">
              <div class="flex items-center gap-2">
                <span class="font-medium"><?= htmlspecialchars($m['first_name'] . ' ' . $m['last_name']) ?></span>
                <span class="status-pill <?= $statusMeta[$m['status']]['class'] ?>"><?= $statusMeta[$m['status']]['label'] ?></span>
              </div>
              <div class="truncate text-sm text-muted-foreground">
                <?= htmlspecialchars($m['email']) ?><?= $m['destination'] ? ' · ' . htmlspecialchars($m['destination']) : '' ?>
              </div>
            </div>
          </div>
          <div class="flex shrink-0 items-center gap-3 text-xs text-muted-foreground">
            <?= date('d/m/Y', strtotime($m['created_at'])) ?>
            <i data-lucide="chevron-down" class="h-4 w-4 transition group-open:rotate-180"></i>
          </div>
        </summary>

        <div class="border-t border-border p-5">
          <dl class="grid gap-2 text-sm sm:grid-cols-2">
            <?php if ($m['phone']): ?><div><dt class="text-xs uppercase tracking-widest text-muted-foreground">Téléphone</dt><dd><?= htmlspecialchars($m['phone']) ?></dd></div><?php endif; ?>
            <?php if ($m['budget']): ?><div><dt class="text-xs uppercase tracking-widest text-muted-foreground">Budget</dt><dd><?= htmlspecialchars($m['budget']) ?></dd></div><?php endif; ?>
          </dl>
          <p class="mt-3 whitespace-pre-line text-sm"><?= htmlspecialchars($m['message']) ?></p>

          <form method="post" action="messages.php" class="mt-4 flex items-center gap-2">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
            <select name="status" class="rounded-xl border border-border bg-background px-2 py-1.5 text-xs focus:border-primary focus:outline-none">
              <?php foreach ($statusMeta as $value => $meta): ?>
                <option value="<?= $value ?>" <?= $m['status'] === $value ? 'selected' : '' ?>><?= $meta['label'] ?></option>
              <?php endforeach; ?>
            </select>
            <button type="submit" class="rounded-full bg-accent px-3 py-1.5 text-xs font-semibold text-accent-foreground">Mettre à jour</button>
          </form>
        </div>
      </details>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/layout-bottom.php'; ?>
