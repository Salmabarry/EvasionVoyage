<?php
require __DIR__ . '/includes/guard.php';
require_once __DIR__ . '/../includes/destinations.php';

$errors = [];
$categories = ['Vacances', 'Affaires', 'Aventure', 'Famille', 'Luxe'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_check($_POST['csrf_token'] ?? null)) {
    $errors[] = 'Session expirée, veuillez réessayer.';
  } else {
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'basculer') {
      db()->prepare('UPDATE offers SET active = 1 - active WHERE id = ?')->execute([$id]);
    } elseif ($action === 'supprimer') {
      db()->prepare('DELETE FROM offers WHERE id = ?')->execute([$id]);
    } elseif ($action === 'ajouter') {
      $destinationId = (int) ($_POST['destination_id'] ?? 0);
      $title = trim($_POST['title'] ?? '');
      $description = trim($_POST['description'] ?? '');
      $category = in_array($_POST['category'] ?? '', $categories, true) ? $_POST['category'] : 'Vacances';
      $price = max(0, (int) ($_POST['price'] ?? 0));
      $dateDepart = $_POST['date_depart'] ?? '';
      $dateRetour = $_POST['date_retour'] ?? '';
      $seats = max(1, min(200, (int) ($_POST['seats'] ?? 20)));

      if ($destinationId < 1 || $title === '' || $price < 1 || $dateDepart === '' || $dateRetour === '') {
        $errors[] = 'Destination, titre, tarif et dates sont obligatoires.';
      } elseif ($dateRetour <= $dateDepart) {
        $errors[] = 'La date de retour doit être après la date de départ.';
      } else {
        $stmt = db()->prepare(
          'INSERT INTO offers (destination_id, title, description, category, price, date_depart, date_retour, seats)
           VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$destinationId, $title, $description !== '' ? $description : null, $category, $price, $dateDepart, $dateRetour, $seats]);
      }
    }

    if (!$errors) {
      header('Location: offres.php');
      exit;
    }
  }
}

$offers = db()->query(
  'SELECT o.*, d.name AS destination_name, d.country
   FROM offers o JOIN destinations d ON d.id = o.destination_id
   ORDER BY o.date_depart ASC'
)->fetchAll();

$pageTitle = 'Offres — Admin EvasionVoyage';
$activeAdmin = 'offres';
$pageSubtitle = count($offers) . ' offre(s) à dates fixes';
require __DIR__ . '/includes/layout-top.php';
?>

<?php if ($errors): ?>
  <div class="admin-card mb-5 border-destructive/30 bg-destructive/10 p-4 text-sm text-destructive">
    <ul class="list-inside list-disc space-y-1">
      <?php foreach ($errors as $error): ?><li><?= htmlspecialchars($error) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<!-- Ajouter une offre -->
<div class="admin-card mb-6">
  <h3 class="mb-4 font-display text-lg">Ajouter une offre</h3>
  <form method="post" action="offres.php" class="grid gap-3 md:grid-cols-2 lg:grid-cols-4">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="ajouter">
    <select name="destination_id" required class="rounded-xl border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none">
      <option value="">Destination *</option>
      <?php foreach ($destinations as $d): ?>
        <option value="<?= (int) $d['id'] ?>"><?= htmlspecialchars($d['name'] . ' (' . $d['country'] . ')') ?></option>
      <?php endforeach; ?>
    </select>
    <input name="title" required placeholder="Titre de l'offre *" class="rounded-xl border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none lg:col-span-2">
    <select name="category" class="rounded-xl border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none">
      <?php foreach ($categories as $c): ?><option><?= $c ?></option><?php endforeach; ?>
    </select>
    <input name="price" type="number" min="1" required placeholder="Tarif FCFA / pers. *" class="rounded-xl border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none">
    <input name="date_depart" type="date" required class="rounded-xl border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none">
    <input name="date_retour" type="date" required class="rounded-xl border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none">
    <input name="seats" type="number" min="1" max="200" value="20" placeholder="Places" class="rounded-xl border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none">
    <input name="description" placeholder="Description courte" class="rounded-xl border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none md:col-span-2 lg:col-span-3">
    <button type="submit" class="rounded-full bg-accent px-5 py-2 text-sm font-semibold text-accent-foreground">Créer l'offre</button>
  </form>
</div>

<div class="admin-card">
  <?php if (!$offers): ?>
    <div class="empty-state"><i data-lucide="tags" class="h-8 w-8"></i><p>Aucune offre pour le moment.</p></div>
  <?php else: ?>
    <div class="overflow-x-auto">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Offre</th>
            <th>Destination</th>
            <th>Type</th>
            <th>Tarif / pers.</th>
            <th>Dates</th>
            <th>Places</th>
            <th>État</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($offers as $o): ?>
            <tr>
              <td class="font-medium"><?= htmlspecialchars($o['title']) ?></td>
              <td><?= htmlspecialchars($o['destination_name']) ?></td>
              <td><?= htmlspecialchars($o['category']) ?></td>
              <td class="font-medium"><?= format_fcfa($o['price']) ?></td>
              <td class="text-muted-foreground"><?= date('d/m/y', strtotime($o['date_depart'])) ?> → <?= date('d/m/y', strtotime($o['date_retour'])) ?></td>
              <td><?= (int) $o['seats'] ?></td>
              <td><span class="status-pill <?= $o['active'] ? 'status-success' : 'status-pending' ?>"><?= $o['active'] ? 'Active' : 'Inactive' ?></span></td>
              <td>
                <div class="flex items-center justify-end gap-2">
                  <form method="post" action="offres.php">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int) $o['id'] ?>">
                    <input type="hidden" name="action" value="basculer">
                    <button type="submit" class="rounded-full border border-border px-3 py-1.5 text-xs font-semibold text-foreground hover:bg-muted">
                      <?= $o['active'] ? 'Désactiver' : 'Activer' ?>
                    </button>
                  </form>
                  <form method="post" action="offres.php" onsubmit="return confirm('Supprimer cette offre ?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int) $o['id'] ?>">
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
