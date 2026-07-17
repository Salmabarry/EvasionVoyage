<?php
require __DIR__ . '/includes/guard.php';

$errors = [];
$editing = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_check($_POST['csrf_token'] ?? null)) {
    $errors[] = 'Session expirée, veuillez réessayer.';
  }

  $action = $_POST['action'] ?? '';

  if ($action === 'delete' && !$errors) {
    $stmt = db()->prepare('DELETE FROM destinations WHERE id = ?');
    $stmt->execute([(int) ($_POST['id'] ?? 0)]);
    header('Location: destinations.php');
    exit;
  }

  if (in_array($action, ['create', 'update'], true)) {
    $data = [
      'slug' => trim($_POST['slug'] ?? ''),
      'name' => trim($_POST['name'] ?? ''),
      'country' => trim($_POST['country'] ?? ''),
      'image' => trim($_POST['image'] ?? ''),
      'tagline' => trim($_POST['tagline'] ?? ''),
      'price' => (int) ($_POST['price'] ?? 0),
      'nights' => (int) ($_POST['nights'] ?? 0),
      'category' => trim($_POST['category'] ?? ''),
      'rating' => (float) ($_POST['rating'] ?? 0),
    ];

    if ($data['slug'] === '' || $data['name'] === '' || $data['country'] === '') {
      $errors[] = 'Slug, nom et pays sont obligatoires.';
    }

    if (!$errors && $action === 'create') {
      $stmt = db()->prepare(
        'INSERT INTO destinations (slug, name, country, image, tagline, price, nights, category, rating)
         VALUES (:slug, :name, :country, :image, :tagline, :price, :nights, :category, :rating)'
      );
      $stmt->execute($data);
      header('Location: destinations.php');
      exit;
    }

    if (!$errors && $action === 'update') {
      $data['id'] = (int) ($_POST['id'] ?? 0);
      $stmt = db()->prepare(
        'UPDATE destinations SET slug=:slug, name=:name, country=:country, image=:image, tagline=:tagline,
         price=:price, nights=:nights, category=:category, rating=:rating WHERE id=:id'
      );
      $stmt->execute($data);
      header('Location: destinations.php');
      exit;
    }

    if ($errors) {
      $editing = $data;
      $editing['id'] = (int) ($_POST['id'] ?? 0);
    }
  }
}

if (!$editing && isset($_GET['edit'])) {
  $stmt = db()->prepare('SELECT * FROM destinations WHERE id = ?');
  $stmt->execute([(int) $_GET['edit']]);
  $editing = $stmt->fetch() ?: null;
}

require_once __DIR__ . '/../includes/destinations.php';

$pageTitle = 'Destinations — Admin EvasionVoyage';
$activeAdmin = 'destinations';
$pageSubtitle = count($destinations) . ' destination(s) publiée(s)';
require __DIR__ . '/includes/layout-top.php';
?>

<?php if ($errors): ?>
  <div class="admin-card mb-5 border-destructive/30 bg-destructive/10 p-4 text-sm text-destructive">
    <ul class="list-inside list-disc space-y-1">
      <?php foreach ($errors as $error): ?><li><?= htmlspecialchars($error) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<div class="admin-card p-6 md:p-8">
  <h2 class="font-display text-xl"><?= $editing ? 'Modifier la destination' : 'Ajouter une destination' ?></h2>
  <form method="post" action="destinations.php" class="mt-4 grid gap-4 md:grid-cols-3">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="<?= $editing ? 'update' : 'create' ?>">
    <?php if ($editing): ?><input type="hidden" name="id" value="<?= (int) $editing['id'] ?>"><?php endif; ?>

    <label class="block text-sm">
      <span class="text-[10px] uppercase tracking-widest text-muted-foreground">Slug</span>
      <input type="text" name="slug" value="<?= htmlspecialchars($editing['slug'] ?? '') ?>" placeholder="santorini" class="mt-1 w-full rounded-xl border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none">
    </label>
    <label class="block text-sm">
      <span class="text-[10px] uppercase tracking-widest text-muted-foreground">Nom</span>
      <input type="text" name="name" value="<?= htmlspecialchars($editing['name'] ?? '') ?>" placeholder="Santorin" class="mt-1 w-full rounded-xl border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none">
    </label>
    <label class="block text-sm">
      <span class="text-[10px] uppercase tracking-widest text-muted-foreground">Pays / Région</span>
      <input type="text" name="country" value="<?= htmlspecialchars($editing['country'] ?? '') ?>" placeholder="Grèce" class="mt-1 w-full rounded-xl border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none">
    </label>
    <label class="block text-sm md:col-span-2">
      <span class="text-[10px] uppercase tracking-widest text-muted-foreground">Image (chemin)</span>
      <input type="text" name="image" value="<?= htmlspecialchars($editing['image'] ?? '') ?>" placeholder="assets/img/dest-xxx.jpg" class="mt-1 w-full rounded-xl border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none">
    </label>
    <label class="block text-sm">
      <span class="text-[10px] uppercase tracking-widest text-muted-foreground">Catégorie</span>
      <input type="text" name="category" value="<?= htmlspecialchars($editing['category'] ?? '') ?>" placeholder="Plage, Culture…" class="mt-1 w-full rounded-xl border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none">
    </label>
    <label class="block text-sm md:col-span-3">
      <span class="text-[10px] uppercase tracking-widest text-muted-foreground">Accroche</span>
      <input type="text" name="tagline" value="<?= htmlspecialchars($editing['tagline'] ?? '') ?>" placeholder="Coucher de soleil sur l'Égée" class="mt-1 w-full rounded-xl border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none">
    </label>
    <label class="block text-sm">
      <span class="text-[10px] uppercase tracking-widest text-muted-foreground">Prix (FCFA)</span>
      <input type="number" name="price" value="<?= htmlspecialchars((string) ($editing['price'] ?? '')) ?>" placeholder="845000" class="mt-1 w-full rounded-xl border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none">
    </label>
    <label class="block text-sm">
      <span class="text-[10px] uppercase tracking-widest text-muted-foreground">Nuits</span>
      <input type="number" name="nights" value="<?= htmlspecialchars((string) ($editing['nights'] ?? '')) ?>" placeholder="7" class="mt-1 w-full rounded-xl border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none">
    </label>
    <label class="block text-sm">
      <span class="text-[10px] uppercase tracking-widest text-muted-foreground">Note (/5)</span>
      <input type="number" step="0.1" min="0" max="5" name="rating" value="<?= htmlspecialchars((string) ($editing['rating'] ?? '')) ?>" placeholder="4.9" class="mt-1 w-full rounded-xl border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none">
    </label>

    <div class="flex items-end gap-2 md:col-span-3">
      <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-accent px-5 py-2.5 text-sm font-semibold text-accent-foreground">
        <i data-lucide="<?= $editing ? 'save' : 'plus' ?>" class="h-4 w-4"></i> <?= $editing ? 'Enregistrer' : 'Ajouter' ?>
      </button>
      <?php if ($editing): ?>
        <a href="destinations.php" class="rounded-full border border-border px-5 py-2.5 text-sm font-medium text-foreground hover:bg-muted">Annuler</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<div class="admin-card mt-6">
  <div class="overflow-x-auto">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Nom</th>
          <th>Pays</th>
          <th>Catégorie</th>
          <th>Prix</th>
          <th>Nuits</th>
          <th>Note</th>
          <th class="text-right">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($destinations as $d): ?>
          <tr>
            <td class="font-medium"><?= htmlspecialchars($d['name']) ?></td>
            <td class="text-muted-foreground"><?= htmlspecialchars($d['country']) ?></td>
            <td><span class="status-pill status-neutral"><?= htmlspecialchars($d['category']) ?></span></td>
            <td><?= format_fcfa($d['price']) ?></td>
            <td><?= (int) $d['nights'] ?></td>
            <td><i data-lucide="star" class="inline h-3.5 w-3.5 fill-accent text-accent"></i> <?= htmlspecialchars($d['rating']) ?></td>
            <td class="text-right">
              <a href="destinations.php?edit=<?= (int) $d['id'] ?>" class="mr-2 inline-flex items-center gap-1 rounded-full border border-border px-3 py-1.5 text-xs font-medium hover:bg-muted">
                <i data-lucide="pencil" class="h-3 w-3"></i> Modifier
              </a>
              <form method="post" action="destinations.php" class="inline" onsubmit="return confirm('Supprimer cette destination ? Les réservations liées seront aussi supprimées.');">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int) $d['id'] ?>">
                <button type="submit" class="inline-flex items-center gap-1 rounded-full border border-destructive/30 px-3 py-1.5 text-xs font-medium text-destructive hover:bg-destructive/10">
                  <i data-lucide="trash-2" class="h-3 w-3"></i> Supprimer
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/includes/layout-bottom.php'; ?>
