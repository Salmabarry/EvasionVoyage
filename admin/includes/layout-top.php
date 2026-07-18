<?php
/**
 * Attend en entrée : $pageTitle, $activeAdmin (slug courant), $pageSubtitle (optionnel)
 * Nécessite que admin/includes/guard.php ait déjà été inclus (fournit $admin).
 */
$pageTitle = $pageTitle ?? 'Administration — EvasionVoyage';
$activeAdmin = $activeAdmin ?? 'index';
$pageSubtitle = $pageSubtitle ?? '';

$unreadMessages = (int) db()->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'nouveau'")->fetchColumn();
$pendingBookings = (int) db()->query("SELECT COUNT(*) FROM bookings WHERE status = 'en_attente'")->fetchColumn();
$pendingReviews = (int) db()->query("SELECT COUNT(*) FROM reviews WHERE status = 'en_attente'")->fetchColumn();

$adminNav = [
  ['slug' => 'index', 'href' => 'index.php', 'label' => 'Vue d\'ensemble', 'icon' => 'layout-dashboard'],
  ['slug' => 'bookings', 'href' => 'bookings.php', 'label' => 'Réservations', 'icon' => 'calendar-check', 'badge' => $pendingBookings],
  ['slug' => 'offres', 'href' => 'offres.php', 'label' => 'Offres', 'icon' => 'tags'],
  ['slug' => 'avis', 'href' => 'avis.php', 'label' => 'Avis clients', 'icon' => 'message-square-heart', 'badge' => $pendingReviews],
  ['slug' => 'utilisateurs', 'href' => 'utilisateurs.php', 'label' => 'Utilisateurs', 'icon' => 'users'],
  ['slug' => 'destinations', 'href' => 'destinations.php', 'label' => 'Destinations', 'icon' => 'map-pinned'],
  ['slug' => 'messages', 'href' => 'messages.php', 'label' => 'Messages', 'icon' => 'mail', 'badge' => $unreadMessages],
  ['slug' => 'parametres', 'href' => 'parametres.php', 'label' => 'Paramètres', 'icon' => 'settings'],
];

$initials = strtoupper(mb_substr($admin['first_name'], 0, 1) . mb_substr($admin['last_name'], 0, 1));
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($pageTitle) ?></title>
<meta name="robots" content="noindex, nofollow">
<link rel="icon" href="../assets/img/favicon.ico" sizes="any">
<link rel="icon" href="../assets/img/favicon.png" type="image/png">

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
          primary: { DEFAULT: 'var(--primary)', foreground: 'var(--primary-foreground)' },
          secondary: { DEFAULT: 'var(--secondary)', foreground: 'var(--secondary-foreground)' },
          muted: { DEFAULT: 'var(--muted)', foreground: 'var(--muted-foreground)' },
          accent: { DEFAULT: 'var(--accent)', foreground: 'var(--accent-foreground)' },
          destructive: { DEFAULT: 'var(--destructive)', foreground: 'var(--destructive-foreground)' },
          border: 'var(--border)',
        },
        borderRadius: {
          lg: 'var(--radius)',
          xl: 'calc(var(--radius) + 4px)',
          '2xl': 'calc(var(--radius) + 8px)',
          '3xl': 'calc(var(--radius) + 12px)',
        },
      }
    }
  };
</script>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/admin.css">
<script src="https://unpkg.com/lucide@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
</head>
<body class="admin-body bg-background text-foreground">

<div class="admin-shell">
  <aside class="admin-sidebar" id="admin-sidebar">
    <div class="admin-sidebar-brand">
      <img src="../assets/img/logo-icon.png" alt="EvasionVoyage" class="h-9 w-9 object-contain">
      <div>
        <div class="font-display text-lg leading-tight text-white">EvasionVoyage</div>
        <div class="text-[11px] uppercase tracking-[0.2em] text-white/50">Espace admin</div>
      </div>
    </div>

    <nav class="admin-nav">
      <?php foreach ($adminNav as $item): ?>
        <a href="<?= $item['href'] ?>" class="admin-nav-link<?= $activeAdmin === $item['slug'] ? ' is-active' : '' ?>">
          <i data-lucide="<?= $item['icon'] ?>" class="h-[18px] w-[18px]"></i>
          <span><?= $item['label'] ?></span>
          <?php if (!empty($item['badge'])): ?>
            <span class="admin-nav-badge"><?= $item['badge'] ?></span>
          <?php endif; ?>
        </a>
      <?php endforeach; ?>
    </nav>

    <div class="admin-sidebar-footer">
      <a href="../index.php" class="admin-nav-link">
        <i data-lucide="external-link" class="h-[18px] w-[18px]"></i>
        <span>Voir le site</span>
      </a>
      <a href="../logout.php" class="admin-nav-link admin-nav-link--danger">
        <i data-lucide="log-out" class="h-[18px] w-[18px]"></i>
        <span>Déconnexion</span>
      </a>
    </div>
  </aside>

  <div class="admin-main">
    <header class="admin-topbar">
      <div class="flex items-center gap-3">
        <button class="admin-menu-toggle md:hidden" id="admin-sidebar-toggle" aria-label="Menu">
          <i data-lucide="menu" class="h-5 w-5"></i>
        </button>
        <div>
          <h1 class="font-display text-2xl leading-tight"><?= htmlspecialchars($pageTitle) ?></h1>
          <?php if ($pageSubtitle): ?>
            <p class="text-sm text-muted-foreground"><?= htmlspecialchars($pageSubtitle) ?></p>
          <?php endif; ?>
        </div>
      </div>

      <div class="flex items-center gap-4">
        <button class="admin-icon-btn" aria-label="Notifications">
          <i data-lucide="bell" class="h-[18px] w-[18px]"></i>
          <?php if ($unreadMessages > 0): ?><span class="admin-icon-dot"></span><?php endif; ?>
        </button>
        <div class="admin-user-chip">
          <span class="admin-avatar"><?= htmlspecialchars($initials) ?></span>
          <div class="hidden text-left sm:block">
            <div class="text-sm font-semibold leading-tight"><?= htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']) ?></div>
            <div class="text-xs text-muted-foreground">Administrateur</div>
          </div>
        </div>
      </div>
    </header>

    <main class="admin-content">
