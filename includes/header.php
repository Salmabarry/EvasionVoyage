<?php
/**
 * Attend en entrée (optionnels) :
 * $pageTitle, $pageDescription, $transparentNav (bool), $activePage (chemin courant, ex: "index.php")
 */
require_once __DIR__ . '/auth.php';
$authUser = current_user();

// Comptage des visiteurs : une visite par session (statistiques admin)
if (empty($_SESSION['visite_comptee'])) {
  $_SESSION['visite_comptee'] = true;
  try {
    db()->exec("INSERT INTO visits (day, counter) VALUES (CURDATE(), 1) ON DUPLICATE KEY UPDATE counter = counter + 1");
  } catch (Throwable $e) { /* la statistique ne doit jamais bloquer le site */ }
}

$pageTitle = $pageTitle ?? 'EvasionVoyage — Voyages sur mesure & réservation en ligne';
$pageDescription = $pageDescription ?? "EvasionVoyage conçoit des voyages sur mesure vers les plus belles destinations. Réservez séjours, vols et expériences en toute sécurité.";
$transparentNav = $transparentNav ?? false;
$activePage = $activePage ?? 'index.php';

$navLinks = [
  ['href' => 'index.php', 'label' => 'Accueil'],
  ['href' => 'destinations.php', 'label' => 'Destinations'],
  ['href' => 'offres.php', 'label' => 'Offres'],
  ['href' => 'a-propos.php', 'label' => 'À propos'],
  ['href' => 'contact.php', 'label' => 'Contact'],
];

$isHome = $activePage === 'index.php';
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($pageTitle) ?></title>
<meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
<meta name="author" content="EvasionVoyage">
<meta property="og:title" content="EvasionVoyage — Voyages sur mesure">
<meta property="og:description" content="Réservez des séjours d'exception vers les plus belles destinations.">
<meta property="og:type" content="website">
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" href="assets/img/favicon.ico" sizes="any">
<link rel="icon" href="assets/img/favicon.png" type="image/png">
<link rel="apple-touch-icon" href="assets/img/apple-touch-icon.png">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,300;9..144,400;9..144,500;9..144,600&family=Inter:wght@400;500;600;700&display=swap">

<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        fontFamily: {
          display: ['Fraunces', 'ui-serif', 'Georgia', 'serif'],
          sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        },
        colors: {
          background: 'var(--background)',
          foreground: 'var(--foreground)',
          card: { DEFAULT: 'var(--card)', foreground: 'var(--card-foreground)' },
          popover: { DEFAULT: 'var(--popover)', foreground: 'var(--popover-foreground)' },
          primary: { DEFAULT: 'var(--primary)', foreground: 'var(--primary-foreground)' },
          secondary: { DEFAULT: 'var(--secondary)', foreground: 'var(--secondary-foreground)' },
          muted: { DEFAULT: 'var(--muted)', foreground: 'var(--muted-foreground)' },
          accent: { DEFAULT: 'var(--accent)', foreground: 'var(--accent-foreground)' },
          destructive: { DEFAULT: 'var(--destructive)', foreground: 'var(--destructive-foreground)' },
          border: 'var(--border)',
          input: 'var(--input)',
          ring: 'var(--ring)',
          ocean: 'var(--ocean)',
          'ocean-deep': 'var(--ocean-deep)',
          sand: 'var(--sand)',
          ember: 'var(--ember)',
          cream: 'var(--cream)',
        },
        borderRadius: {
          sm: 'calc(var(--radius) - 4px)',
          md: 'calc(var(--radius) - 2px)',
          lg: 'var(--radius)',
          xl: 'calc(var(--radius) + 4px)',
          '2xl': 'calc(var(--radius) + 8px)',
          '3xl': 'calc(var(--radius) + 12px)',
        },
      }
    }
  };
</script>
<link rel="stylesheet" href="assets/css/style.css">
<script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="flex min-h-screen flex-col">

<header class="site-header<?= $isHome ? ' is-ondark' : '' ?>" id="site-header">
  <div class="container-x flex items-center justify-between">
    <a href="index.php" class="nav-brand flex items-center gap-2 font-display text-xl tracking-tight">
      <img src="assets/img/icone-couleur.svg" alt="EvasionVoyage" class="h-9 w-9 object-contain">
      <span>EvasionVoyage</span>
    </a>

    <nav class="hidden items-center gap-1 md:flex">
      <?php foreach ($navLinks as $l): ?>
        <a href="<?= $l['href'] ?>" class="nav-link rounded-full px-4 py-2 text-sm font-medium<?= $activePage === $l['href'] ? ' is-active' : '' ?>">
          <?= $l['label'] ?>
        </a>
      <?php endforeach; ?>
    </nav>

    <div class="hidden items-center gap-2 md:flex">
      <?php if ($authUser): ?>
        <?php if (!empty($authUser['is_admin'])): ?>
          <a href="admin/index.php" class="nav-cta-secondary text-sm font-medium">
            <i data-lucide="shield" class="mr-1 inline h-4 w-4 align-[-2px]"></i>Admin
          </a>
        <?php else: ?>
          <!-- Menu compte : toutes les pages de l'espace voyageur -->
          <div class="relative">
            <button type="button" id="menu-compte-btn" class="nav-cta-secondary flex items-center gap-1 text-sm font-medium">
              <i data-lucide="user-round" class="h-4 w-4"></i><?= htmlspecialchars($authUser['first_name']) ?>
              <i data-lucide="chevron-down" class="h-3.5 w-3.5"></i>
            </button>
            <div id="menu-compte-panel" class="absolute right-0 z-50 mt-2 hidden w-60 rounded-2xl border border-border bg-background p-2 shadow-[var(--shadow-lift)]">
              <a href="tableau-de-bord.php" class="flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-medium text-foreground hover:bg-muted"><i data-lucide="layout-dashboard" class="h-4 w-4 text-primary"></i> Tableau de bord</a>
              <a href="mes-reservations.php" class="flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-medium text-foreground hover:bg-muted"><i data-lucide="ticket" class="h-4 w-4 text-primary"></i> Mes réservations</a>
              <a href="historique.php" class="flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-medium text-foreground hover:bg-muted"><i data-lucide="history" class="h-4 w-4 text-primary"></i> Historique des voyages</a>
              <a href="profil.php" class="flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-medium text-foreground hover:bg-muted"><i data-lucide="user-round-cog" class="h-4 w-4 text-primary"></i> Mon profil</a>
              <div class="my-1 border-t border-border"></div>
              <a href="logout.php" class="flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-medium text-destructive hover:bg-destructive/10"><i data-lucide="log-out" class="h-4 w-4"></i> Déconnexion</a>
            </div>
          </div>
        <?php endif; ?>
        <?php if (!empty($authUser['is_admin'])): ?>
        <a href="logout.php" class="rounded-full bg-accent px-4 py-2 text-sm font-semibold text-accent-foreground shadow-[var(--shadow-soft)] transition hover:translate-y-[-1px]">
          Déconnexion
        </a>
        <?php endif; ?>
      <?php else: ?>
        <a href="connexion.php" class="nav-cta-secondary text-sm font-medium">Connexion</a>
        <a href="inscription.php" class="rounded-full bg-accent px-4 py-2 text-sm font-semibold text-accent-foreground shadow-[var(--shadow-soft)] transition hover:translate-y-[-1px]">
          Réserver
        </a>
      <?php endif; ?>
    </div>

    <button class="nav-toggle md:hidden rounded-full p-2" id="mobile-menu-toggle" aria-label="Menu">
      <i data-lucide="menu" class="h-5 w-5" id="mobile-menu-icon"></i>
    </button>
  </div>

  <div class="mobile-menu md:hidden mt-3 mx-6 rounded-2xl border border-border/60 bg-background/95 p-3 shadow-[var(--shadow-lift)] backdrop-blur-xl" id="mobile-menu">
    <?php foreach ($navLinks as $l): ?>
      <a href="<?= $l['href'] ?>" class="block rounded-xl px-4 py-3 text-sm font-medium text-foreground hover:bg-muted"><?= $l['label'] ?></a>
    <?php endforeach; ?>
    <div class="mt-2 flex gap-2 border-t border-border pt-3">
      <?php if ($authUser): ?>
        <?php if (!empty($authUser['is_admin'])): ?>
          <a href="admin/index.php" class="flex-1 rounded-xl border border-border px-4 py-2 text-center text-sm font-medium text-foreground">Admin</a>
          <a href="logout.php" class="flex-1 rounded-xl bg-accent px-4 py-2 text-center text-sm font-semibold text-accent-foreground">Déconnexion</a>
        <?php else: ?>
          <div class="w-full space-y-1">
            <a href="tableau-de-bord.php" class="block rounded-xl px-4 py-2.5 text-sm font-medium text-foreground hover:bg-muted">Tableau de bord</a>
            <a href="mes-reservations.php" class="block rounded-xl px-4 py-2.5 text-sm font-medium text-foreground hover:bg-muted">Mes réservations</a>
            <a href="historique.php" class="block rounded-xl px-4 py-2.5 text-sm font-medium text-foreground hover:bg-muted">Historique des voyages</a>
            <a href="profil.php" class="block rounded-xl px-4 py-2.5 text-sm font-medium text-foreground hover:bg-muted">Mon profil</a>
            <a href="logout.php" class="block rounded-xl bg-accent px-4 py-2.5 text-center text-sm font-semibold text-accent-foreground">Déconnexion</a>
          </div>
        <?php endif; ?>
      <?php else: ?>
        <a href="connexion.php" class="flex-1 rounded-xl border border-border px-4 py-2 text-center text-sm font-medium text-foreground">Connexion</a>
        <a href="inscription.php" class="flex-1 rounded-xl bg-accent px-4 py-2 text-center text-sm font-semibold text-accent-foreground">Réserver</a>
      <?php endif; ?>
    </div>
  </div>
</header>

<main class="flex-1<?= $transparentNav ? '' : ' pt-24' ?>">
