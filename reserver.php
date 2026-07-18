<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/destinations.php';

/* Deux modes de réservation :
   - ?offer=ID → offre à DATES FIXES (prix et dates de l'offre, places limitées)
   - ?slug=... → destination à DATES LIBRES (le client choisit ses dates)   */
$offerId = (int) ($_GET['offer'] ?? $_POST['offer'] ?? 0);
$offre = null;
if ($offerId > 0) {
  $stmt = db()->prepare('SELECT o.*, d.slug AS dest_slug FROM offers o JOIN destinations d ON d.id = o.destination_id WHERE o.id = ? AND o.active = 1');
  $stmt->execute([$offerId]);
  $offre = $stmt->fetch();
  if (!$offre) {
    header('Location: offres.php');
    exit;
  }
  $slug = $offre['dest_slug'];
} else {
  $slug = trim($_GET['slug'] ?? $_POST['slug'] ?? '');
}

if ($slug === '') {
  header('Location: offres.php');
  exit;
}

$stmt = db()->prepare('SELECT * FROM destinations WHERE slug = ?');
$stmt->execute([$slug]);
$destination = $stmt->fetch();

if (!$destination) {
  header('Location: offres.php');
  exit;
}

require_login('connexion.php');

/* Un administrateur gère les voyages, il ne les réserve pas :
   ses réservations fausseraient les statistiques. */
$estAdmin = is_admin();

/* ---------- Validation Luhn côté serveur (numéros de carte) ---------- */
function luhn_valide(string $numero): bool {
  $chiffres = preg_replace('/\D/', '', $numero);
  if (strlen($chiffres) !== 16) return false;
  $somme = 0; $alterne = false;
  for ($i = strlen($chiffres) - 1; $i >= 0; $i--) {
    $n = (int) $chiffres[$i];
    if ($alterne) { $n *= 2; if ($n > 9) $n -= 9; }
    $somme += $n;
    $alterne = !$alterne;
  }
  return $somme % 10 === 0;
}

function telephone_valide(string $tel): bool {
  return (bool) preg_match('/^\+?[0-9][0-9 ]{6,17}[0-9]$/', trim($tel));
}

$errors = [];
$etape = 'details';           // details -> paiement -> succes
$reference = null;
$avisEnvoye = false;

$travelers = max(1, min(20, (int) ($_POST['travelers'] ?? 1)));
if ($offre) {
  // Offre : les dates et le prix sont ceux de l'offre, non modifiables
  $dateDepart = $offre['date_depart'];
  $dateRetour = $offre['date_retour'];
  $prixUnitaire = (int) $offre['price'];
} else {
  $dateDepart = trim($_POST['date_depart'] ?? '');
  $dateRetour = trim($_POST['date_retour'] ?? '');
  $prixUnitaire = (int) $destination['price'];
}
$amount = $prixUnitaire * $travelers;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$estAdmin) {
  $step = $_POST['step'] ?? '';

  if (!csrf_check($_POST['csrf_token'] ?? null)) {
    $errors[] = 'Session expirée, veuillez réessayer.';
  }

  /* ----- Dépôt d'un avis (modéré par l'admin avant publication) ----- */
  if (!$errors && $step === 'avis') {
    $rating = (int) ($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');
    if ($rating < 1 || $rating > 5) {
      $errors[] = 'La note doit être comprise entre 1 et 5.';
    } else {
      $stmt = db()->prepare('INSERT INTO reviews (user_id, destination_id, rating, comment) VALUES (?, ?, ?, ?)');
      $stmt->execute([current_user()['id'], $destination['id'], $rating, $comment !== '' ? $comment : null]);
      $avisEnvoye = true;
    }
  }

  /* ----- Étape 1 validée : on passe au paiement ----- */
  if (!$errors && $step === 'details') {
    if ($offre) {
      // Dates fixées par l'offre : seul le nombre de places est à vérifier
      if ((int) $offre['seats'] < $travelers) {
        $errors[] = 'Il ne reste que ' . (int) $offre['seats'] . ' place(s) sur cette offre.';
      } else {
        $etape = 'paiement';
      }
    } else {
      $demain = new DateTimeImmutable('tomorrow');
      $dep = DateTimeImmutable::createFromFormat('Y-m-d', $dateDepart) ?: null;
      $ret = DateTimeImmutable::createFromFormat('Y-m-d', $dateRetour) ?: null;
      if (!$dep || !$ret) {
        $errors[] = 'Choisissez vos dates de départ et de retour.';
      } elseif ($dep < $demain) {
        $errors[] = 'La date de départ doit être dans le futur.';
      } elseif ($ret <= $dep) {
        $errors[] = 'La date de retour doit être après la date de départ.';
      } else {
        $etape = 'paiement';
      }
    }
  }

  /* ----- Étape 2 : paiement (simulé) + création de la réservation ----- */
  if (!$errors && $step === 'paiement') {
    $dep = DateTimeImmutable::createFromFormat('Y-m-d', $dateDepart) ?: null;
    $ret = DateTimeImmutable::createFromFormat('Y-m-d', $dateRetour) ?: null;
    $method = $_POST['method'] ?? '';

    if (!$dep || !$ret || $ret <= $dep) {
      $errors[] = 'Dates invalides, veuillez recommencer.';
      $etape = 'details';
    } elseif ($offre && (int) $offre['seats'] < $travelers) {
      $errors[] = 'Il ne reste que ' . (int) $offre['seats'] . ' place(s) sur cette offre.';
      $etape = 'details';
    } elseif (!in_array($method, ['carte', 'wave', 'orange_money'], true)) {
      $errors[] = 'Choisissez une méthode de paiement.';
      $etape = 'paiement';
    } else {
      // Validation des informations selon la méthode
      if ($method === 'carte') {
        $exp = trim($_POST['carte_exp'] ?? '');
        if (!luhn_valide($_POST['carte_numero'] ?? '')) {
          $errors[] = 'Numéro de carte invalide (16 chiffres — essayez 4242 4242 4242 4242 pour la démonstration).';
        }
        if (!preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $exp, $m)) {
          $errors[] = 'Date d\'expiration au format MM/AA attendue.';
        } elseif (mktime(0, 0, 0, (int) $m[1] + 1, 0, 2000 + (int) $m[2]) < time()) {
          $errors[] = 'Cette carte est expirée.';
        }
        if (!preg_match('/^[0-9]{3,4}$/', $_POST['carte_cvc'] ?? '')) {
          $errors[] = 'CVC invalide (3 ou 4 chiffres).';
        }
        if (mb_strlen(trim($_POST['carte_nom'] ?? '')) < 3) {
          $errors[] = 'Indiquez le nom figurant sur la carte.';
        }
      } elseif (!telephone_valide($_POST['mobile_tel'] ?? '')) {
        $errors[] = 'Indiquez un numéro de téléphone valide pour le paiement mobile.';
      }

      if ($errors) {
        $etape = 'paiement';
      } else {
        $reference = 'PAY-' . time() . '-' . random_int(1000, 9999);
        $pdo = db();
        $pdo->beginTransaction();
        try {
          $stmt = $pdo->prepare(
            'INSERT INTO bookings (user_id, destination_id, offer_id, travelers, date_depart, date_retour, amount, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, "confirmee")'
          );
          $stmt->execute([current_user()['id'], $destination['id'], $offre ? (int) $offre['id'] : null, $travelers, $dep->format('Y-m-d'), $ret->format('Y-m-d'), $amount]);
          $bookingId = (int) $pdo->lastInsertId();
          $stmt = $pdo->prepare('INSERT INTO payments (booking_id, amount, method, reference) VALUES (?, ?, ?, ?)');
          $stmt->execute([$bookingId, $amount, $method, $reference]);
          if ($offre) {
            $pdo->prepare('UPDATE offers SET seats = seats - ? WHERE id = ?')->execute([$travelers, (int) $offre['id']]);
          }
          $pdo->commit();
          $etape = 'succes';
        } catch (Throwable $e) {
          $pdo->rollBack();
          $errors[] = 'Une erreur est survenue, veuillez réessayer.';
          $etape = 'paiement';
        }
      }
    }
  }
}

/* ----- Avis approuvés de cette destination (les autres restent invisibles) ----- */
$stmt = db()->prepare(
  "SELECT r.rating, r.comment, r.created_at, u.first_name
   FROM reviews r JOIN users u ON u.id = r.user_id
   WHERE r.destination_id = ? AND r.status = 'approuve'
   ORDER BY r.created_at DESC LIMIT 9"
);
$stmt->execute([$destination['id']]);
$avisApprouves = $stmt->fetchAll();

$methodLabels = ['carte' => 'Carte bancaire', 'wave' => 'Wave', 'orange_money' => 'Orange Money'];

$pageTitle = 'Réserver ' . $destination['name'] . ' — EvasionVoyage';
$pageDescription = 'Choisissez vos dates et payez en ligne pour ' . $destination['name'] . '.';
$transparentNav = false;
$activePage = 'offres.php';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/page-header.php';

render_page_header(
  'Réservation',
  $offre ? $offre['title'] : ($destination['name'] . ', ' . $destination['country']),
  $offre ? ($destination['name'] . ', ' . $destination['country'] . ' — dates fixes') : $destination['tagline']
);
?>

<section class="container-x py-16">
  <div class="mx-auto max-w-2xl overflow-hidden rounded-3xl border border-border bg-card shadow-[var(--shadow-soft)]">
    <div class="relative h-56">
      <img src="<?= htmlspecialchars($destination['image']) ?>" alt="<?= htmlspecialchars($destination['name']) ?>" class="absolute inset-0 h-full w-full object-cover">
    </div>

    <div class="p-8 md:p-10">
      <?php if ($errors): ?>
        <div class="mb-6 rounded-2xl border border-destructive/30 bg-destructive/10 p-4 text-sm text-destructive">
          <ul class="list-inside list-disc space-y-1">
            <?php foreach ($errors as $error): ?>
              <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <?php if ($estAdmin): ?>
        <!-- Un compte administrateur ne réserve pas -->
        <div class="flex items-center gap-3">
          <span class="grid h-10 w-10 place-items-center rounded-xl bg-muted text-foreground">
            <i data-lucide="shield" class="h-5 w-5"></i>
          </span>
          <h2 class="font-display text-2xl">Réservation désactivée</h2>
        </div>
        <p class="mt-4 text-sm text-muted-foreground">
          Un compte administrateur gère les voyages, il ne les réserve pas — ses réservations fausseraient
          les statistiques. Utilisez un compte client pour tester la réservation.
        </p>

      <?php elseif ($etape === 'succes'): ?>
        <!-- Étape 3 : confirmation -->
        <div class="flex items-center gap-3">
          <span class="grid h-10 w-10 place-items-center rounded-xl bg-[image:var(--gradient-ember)] text-white">
            <i data-lucide="check" class="h-5 w-5"></i>
          </span>
          <h2 class="font-display text-2xl">Réservation confirmée !</h2>
        </div>
        <p class="mt-4 text-sm text-muted-foreground">
          Merci <?= htmlspecialchars(current_user()['first_name']) ?> — votre paiement de
          <strong><?= format_fcfa($amount) ?></strong> pour <strong><?= htmlspecialchars($destination['name']) ?></strong>
          (du <?= date('d/m/Y', strtotime($dateDepart)) ?> au <?= date('d/m/Y', strtotime($dateRetour)) ?>,
          <?= $travelers ?> voyageur<?= $travelers > 1 ? 's' : '' ?>) a été accepté.<br>
          Référence de paiement : <strong><?= htmlspecialchars($reference) ?></strong>
        </p>
        <a href="mes-reservations.php" class="mt-6 inline-flex items-center gap-2 rounded-full bg-accent px-6 py-3 text-sm font-semibold text-accent-foreground">
          Voir mes réservations <i data-lucide="arrow-right" class="h-4 w-4"></i>
        </a>

      <?php elseif ($etape === 'paiement'): ?>
        <!-- Étape 2 : paiement (simulé — aucune passerelle réelle) -->
        <h2 class="font-display text-2xl">Paiement sécurisé</h2>
        <p class="mt-2 text-xs text-muted-foreground"><i data-lucide="lock" class="inline h-3 w-3"></i>
          Démonstration — aucun débit réel.
          Du <?= date('d/m/Y', strtotime($dateDepart)) ?> au <?= date('d/m/Y', strtotime($dateRetour)) ?> ·
          <?= $travelers ?> voyageur<?= $travelers > 1 ? 's' : '' ?></p>
        <div class="mt-4 font-display text-3xl"><?= format_fcfa($amount) ?></div>

        <form method="post" action="reserver.php" class="mt-6 space-y-4" id="form-paiement">
          <?= csrf_field() ?>
          <input type="hidden" name="slug" value="<?= htmlspecialchars($slug) ?>">
          <?php if ($offre): ?><input type="hidden" name="offer" value="<?= (int) $offre['id'] ?>"><?php endif; ?>
          <input type="hidden" name="step" value="paiement">
          <input type="hidden" name="travelers" value="<?= $travelers ?>">
          <input type="hidden" name="date_depart" value="<?= htmlspecialchars($dateDepart) ?>">
          <input type="hidden" name="date_retour" value="<?= htmlspecialchars($dateRetour) ?>">
          <input type="hidden" name="method" id="champ-method" value="carte">

          <div class="flex gap-2">
            <?php foreach ($methodLabels as $value => $label): ?>
              <button type="button" data-methode="<?= $value ?>"
                class="btn-methode flex-1 rounded-full border border-border px-3 py-2 text-xs font-semibold <?= $value === 'carte' ? 'bg-primary text-primary-foreground' : 'text-foreground' ?>">
                <?= $label ?>
              </button>
            <?php endforeach; ?>
          </div>

          <div id="volet-carte" class="volet space-y-3">
            <input name="carte_numero" id="carte-numero" placeholder="Numéro de carte (ex : 4242 4242 4242 4242)" maxlength="19" inputmode="numeric"
                   class="w-full rounded-2xl border border-border bg-background px-4 py-3 text-sm focus:border-primary focus:outline-none">
            <div class="flex gap-3">
              <input name="carte_exp" id="carte-exp" placeholder="MM/AA" maxlength="5" inputmode="numeric"
                     class="w-1/2 rounded-2xl border border-border bg-background px-4 py-3 text-sm focus:border-primary focus:outline-none">
              <input name="carte_cvc" id="carte-cvc" placeholder="CVC" maxlength="4" inputmode="numeric"
                     class="w-1/2 rounded-2xl border border-border bg-background px-4 py-3 text-sm focus:border-primary focus:outline-none">
            </div>
            <input name="carte_nom" placeholder="Nom sur la carte"
                   class="w-full rounded-2xl border border-border bg-background px-4 py-3 text-sm focus:border-primary focus:outline-none">
          </div>

          <div id="volet-mobile" class="volet hidden space-y-3 text-center">
            <img id="qr-paiement" src="" alt="QR code de paiement" width="150" height="150" class="mx-auto rounded-xl border border-border p-1">
            <p class="text-xs text-muted-foreground">Scannez ce code avec votre application <span id="qr-libelle">Wave</span>,
               ou saisissez votre numéro :</p>
            <input name="mobile_tel" placeholder="Numéro de téléphone (ex : +221 77 000 00 00)" inputmode="tel"
                   class="w-full rounded-2xl border border-border bg-background px-4 py-3 text-sm focus:border-primary focus:outline-none">
          </div>

          <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-accent px-6 py-3.5 text-sm font-semibold text-accent-foreground shadow-[var(--shadow-soft)] transition hover:translate-y-[-1px]">
            Payer <?= format_fcfa($amount) ?> <i data-lucide="arrow-right" class="h-4 w-4"></i>
          </button>
        </form>

        <script>
          (function () {
            const champMethode = document.getElementById('champ-method');
            const voletCarte = document.getElementById('volet-carte');
            const voletMobile = document.getElementById('volet-mobile');
            const qr = document.getElementById('qr-paiement');
            const qrLibelle = document.getElementById('qr-libelle');
            const montant = <?= (int) $amount ?>;

            document.querySelectorAll('.btn-methode').forEach(function (btn) {
              btn.addEventListener('click', function () {
                document.querySelectorAll('.btn-methode').forEach(function (b) {
                  b.classList.remove('bg-primary', 'text-primary-foreground');
                });
                btn.classList.add('bg-primary', 'text-primary-foreground');
                const methode = btn.dataset.methode;
                champMethode.value = methode;
                const mobile = methode !== 'carte';
                voletCarte.classList.toggle('hidden', mobile);
                voletMobile.classList.toggle('hidden', !mobile);
                if (mobile) {
                  qrLibelle.textContent = methode === 'wave' ? 'Wave' : 'Orange Money (Maxit)';
                  const donnees = 'EVASIONVOYAGE|' + methode.toUpperCase() + '|<?= htmlspecialchars($slug) ?>|' + montant + ' FCFA';
                  qr.src = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' + encodeURIComponent(donnees);
                }
              });
            });

            // Formatage automatique de la saisie carte
            document.getElementById('carte-numero').addEventListener('input', function (e) {
              e.target.value = e.target.value.replace(/\D/g, '').substring(0, 16).replace(/(.{4})/g, '$1 ').trim();
            });
            document.getElementById('carte-exp').addEventListener('input', function (e) {
              let v = e.target.value.replace(/\D/g, '').substring(0, 4);
              e.target.value = v.length > 2 ? v.substring(0, 2) + '/' + v.substring(2) : v;
            });
            document.getElementById('carte-cvc').addEventListener('input', function (e) {
              e.target.value = e.target.value.replace(/\D/g, '');
            });
          })();
        </script>

      <?php else: ?>
        <!-- Étape 1 : offre = dates fixes / destination = le client choisit SES dates -->
        <div class="flex items-end justify-between border-b border-border pb-6">
          <div>
            <?php if ($offre): ?>
              <div class="text-xs text-muted-foreground"><?= htmlspecialchars($offre['title']) ?> · prix fixe</div>
            <?php else: ?>
              <div class="text-xs text-muted-foreground">séjour indicatif <?= (int) $destination['nights'] ?> nuits · dès</div>
            <?php endif; ?>
            <div class="font-display text-3xl"><?= format_fcfa($prixUnitaire) ?></div>
            <div class="text-xs text-muted-foreground">/ pers. · tout inclus</div>
          </div>
          <div class="flex items-center gap-1 text-sm">
            <i data-lucide="star" class="h-4 w-4 fill-accent text-accent"></i>
            <span class="font-semibold"><?= htmlspecialchars($destination['rating']) ?></span>
          </div>
        </div>

        <form method="post" action="reserver.php" class="mt-6 space-y-4">
          <?= csrf_field() ?>
          <input type="hidden" name="slug" value="<?= htmlspecialchars($slug) ?>">
          <?php if ($offre): ?><input type="hidden" name="offer" value="<?= (int) $offre['id'] ?>"><?php endif; ?>
          <input type="hidden" name="step" value="details">
          <?php if ($offre): ?>
            <div class="rounded-2xl bg-muted px-4 py-3 text-sm">
              <i data-lucide="calendar" class="mr-1 inline h-4 w-4 align-[-2px] text-primary"></i>
              Dates fixes de l'offre : <strong>du <?= date('d/m/Y', strtotime($offre['date_depart'])) ?>
              au <?= date('d/m/Y', strtotime($offre['date_retour'])) ?></strong>
              · <?= (int) $offre['seats'] ?> place(s) restante(s)
            </div>
          <?php else: ?>
          <div class="flex gap-3">
            <label class="block w-1/2">
              <span class="text-[10px] uppercase tracking-widest text-muted-foreground">Date de départ</span>
              <input type="date" name="date_depart" id="date-depart" required min="<?= date('Y-m-d', strtotime('tomorrow')) ?>"
                     value="<?= htmlspecialchars($dateDepart) ?>"
                     class="mt-2 w-full rounded-2xl border border-border bg-background px-4 py-3 text-sm focus:border-primary focus:outline-none">
            </label>
            <label class="block w-1/2">
              <span class="text-[10px] uppercase tracking-widest text-muted-foreground">Date de retour</span>
              <input type="date" name="date_retour" id="date-retour" required min="<?= date('Y-m-d', strtotime('tomorrow')) ?>"
                     value="<?= htmlspecialchars($dateRetour) ?>"
                     class="mt-2 w-full rounded-2xl border border-border bg-background px-4 py-3 text-sm focus:border-primary focus:outline-none">
            </label>
          </div>
          <?php endif; ?>
          <label class="block">
            <span class="text-[10px] uppercase tracking-widest text-muted-foreground">Nombre de voyageurs</span>
            <input type="number" name="travelers" id="champ-voyageurs" min="1" max="20" value="<?= $travelers ?>"
                   class="mt-2 w-full rounded-2xl border border-border bg-background px-4 py-3 text-sm focus:border-primary focus:outline-none">
          </label>
          <p class="text-sm text-muted-foreground">Total estimé :
            <strong id="total-estime" class="text-foreground"><?= format_fcfa($amount) ?></strong></p>
          <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-accent px-6 py-3.5 text-sm font-semibold text-accent-foreground shadow-[var(--shadow-soft)] transition hover:translate-y-[-1px]">
            Continuer vers le paiement <i data-lucide="arrow-right" class="h-4 w-4"></i>
          </button>
        </form>

        <script>
          (function () {
            const prix = <?= (int) $prixUnitaire ?>;
            const voyageurs = document.getElementById('champ-voyageurs');
            const total = document.getElementById('total-estime');
            const depart = document.getElementById('date-depart');
            const retour = document.getElementById('date-retour');
            voyageurs.addEventListener('input', function () {
              const nb = Math.max(1, parseInt(voyageurs.value || '1', 10));
              total.textContent = (nb * prix).toLocaleString('fr-FR') + ' FCFA';
            });
            if (depart) depart.addEventListener('change', function () { if (depart.value) retour.min = depart.value; });
          })();
        </script>
      <?php endif; ?>
    </div>
  </div>

  <?php if (!$offre): ?>
    <?php
    // Offres à dates fixes disponibles pour cette destination
    $stmtOffres = db()->prepare('SELECT * FROM offers WHERE destination_id = ? AND active = 1 AND date_depart >= CURDATE() ORDER BY date_depart LIMIT 4');
    $stmtOffres->execute([$destination['id']]);
    $offresDestination = $stmtOffres->fetchAll();
    ?>
    <?php if ($offresDestination): ?>
      <div class="mx-auto mt-12 max-w-2xl">
        <h3 class="font-display text-2xl">Nos offres à dates fixes pour <?= htmlspecialchars($destination['name']) ?></h3>
        <div class="mt-4 space-y-3">
          <?php foreach ($offresDestination as $od): ?>
            <a href="reserver.php?offer=<?= (int) $od['id'] ?>" class="flex items-center justify-between gap-4 rounded-2xl border border-border bg-card p-5 transition hover:border-primary/40 hover:bg-muted">
              <div>
                <div class="text-sm font-semibold"><?= htmlspecialchars($od['title']) ?></div>
                <div class="mt-1 text-xs text-muted-foreground">
                  <i data-lucide="calendar" class="mr-1 inline h-3.5 w-3.5 align-[-2px]"></i>
                  du <?= date('d/m/Y', strtotime($od['date_depart'])) ?> au <?= date('d/m/Y', strtotime($od['date_retour'])) ?>
                  · <?= (int) $od['seats'] ?> place(s)
                </div>
              </div>
              <div class="text-right">
                <div class="font-display text-lg"><?= format_fcfa($od['price']) ?></div>
                <div class="text-xs text-muted-foreground">/ pers.</div>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  <?php endif; ?>

  <!-- Avis des voyageurs (seuls les avis validés par l'admin sont visibles) -->
  <div class="mx-auto mt-12 max-w-2xl">
    <h3 class="font-display text-2xl">Avis des voyageurs</h3>
    <?php if ($avisEnvoye): ?>
      <div class="mt-4 rounded-2xl border border-primary/30 bg-primary/5 p-4 text-sm text-foreground">
        <i data-lucide="hourglass" class="mr-1 inline h-4 w-4"></i>
        Merci ! Votre avis sera publié après validation par notre équipe.
      </div>
    <?php endif; ?>

    <?php if ($avisApprouves): ?>
      <div class="mt-6 grid gap-4 md:grid-cols-2">
        <?php foreach ($avisApprouves as $avis): ?>
          <div class="rounded-2xl border border-border bg-card p-5">
            <div class="text-accent"><?= str_repeat('★', (int) $avis['rating']) . str_repeat('☆', 5 - (int) $avis['rating']) ?></div>
            <?php if ($avis['comment']): ?>
              <p class="mt-2 text-sm italic text-muted-foreground">« <?= htmlspecialchars($avis['comment']) ?> »</p>
            <?php endif; ?>
            <p class="mt-3 text-xs text-muted-foreground"><strong><?= htmlspecialchars($avis['first_name']) ?></strong> — <?= date('d/m/Y', strtotime($avis['created_at'])) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="mt-4 text-sm text-muted-foreground">Aucun avis publié pour cette destination — soyez le premier !</p>
    <?php endif; ?>

    <?php if (!$estAdmin && !$avisEnvoye): ?>
      <form method="post" action="reserver.php" class="mt-6 rounded-2xl border border-border bg-card p-6">
        <?= csrf_field() ?>
        <input type="hidden" name="slug" value="<?= htmlspecialchars($slug) ?>">
        <input type="hidden" name="step" value="avis">
        <div class="text-sm font-semibold">Donner mon avis</div>
        <div class="mt-3 flex gap-3">
          <select name="rating" class="w-40 rounded-2xl border border-border bg-background px-3 py-2.5 text-sm focus:border-primary focus:outline-none">
            <option value="5">★★★★★</option><option value="4">★★★★</option><option value="3">★★★</option>
            <option value="2">★★</option><option value="1">★</option>
          </select>
          <input name="comment" placeholder="Partagez votre expérience…" maxlength="500"
                 class="flex-1 rounded-2xl border border-border bg-background px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
          <button type="submit" class="rounded-full bg-accent px-5 py-2.5 text-sm font-semibold text-accent-foreground">Publier</button>
        </div>
        <p class="mt-2 text-xs text-muted-foreground">Votre avis sera vérifié par notre équipe avant publication.</p>
      </form>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
