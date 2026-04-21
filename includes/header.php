<?php
require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/../auth/session.php');

// Vérifier l'authentification
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
}

$currentUser = getCurrentUser();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - <?php echo ucfirst($currentPage); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <button id="menu-btn" class="lg:hidden p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <div>
                    <h1 class="text-2xl"><?php echo APP_NAME; ?></h1>
                    <p class="text-sm text-gray-600">Super Marché</p>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center gap-3 px-4 py-2 bg-gray-50 rounded-lg">
                    <i data-lucide="user" class="w-5 h-5 text-gray-600"></i>
                    <div class="text-right">
                        <p class="text-sm"><?php echo htmlspecialchars($currentUser['name']); ?></p>
                        <p class="text-xs text-gray-600"><?php echo ROLES[$currentUser['role']] ?? $currentUser['role']; ?></p>
                    </div>
                </div>
                <a href="<?php echo BASE_URL; ?>auth/logout.php" class="flex items-center gap-2 px-4 py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-colors">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">Déconnexion</span>
                </a>
            </div>
        </div>
    </header>

    <div class="flex">
        <!-- Sidebar -->
        <aside id="sidebar" class="fixed lg:static inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 transform -translate-x-full lg:translate-x-0 transition-transform">
            <div class="p-6 border-b border-gray-200 flex items-center justify-between lg:justify-center">
                <h2 class="text-xl">Menu</h2>
                <button id="close-sidebar" class="lg:hidden p-2 hover:bg-gray-100 rounded-lg">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <nav class="p-4 space-y-2">
                <?php
                $menuItems = [
                    ['id' => 'facturation', 'label' => 'Facturation', 'icon' => 'shopping-cart', 'roles' => ['caissier', 'manager', 'super_admin'], 'url' => BASE_URL . 'modules/facturation/nouvelle-facture.php'],
                    ['id' => 'produits', 'label' => 'Enregistrer Produit', 'icon' => 'package', 'roles' => ['manager', 'super_admin'], 'url' => BASE_URL . 'modules/produits/enregistrer.php'],
                    ['id' => 'liste-produits', 'label' => 'Liste Produits', 'icon' => 'file-text', 'roles' => ['manager', 'super_admin'], 'url' => BASE_URL . 'modules/produits/liste.php'],
                    ['id' => 'rapports', 'label' => 'Rapports', 'icon' => 'bar-chart-3', 'roles' => ['manager', 'super_admin'], 'url' => BASE_URL . 'rapports/rapport-journalier.php'],
                    ['id' => 'utilisateurs', 'label' => 'Gestion Comptes', 'icon' => 'users', 'roles' => ['super_admin'], 'url' => BASE_URL . 'modules/admin/gestion-comptes.php'],
                ];

                foreach ($menuItems as $item) {
                    if (in_array($currentUser['role'], $item['roles'])) {
                        $isActive = strpos($_SERVER['REQUEST_URI'], $item['id']) !== false;
                        $activeClass = $isActive ? 'bg-blue-600 text-white' : 'hover:bg-gray-100 text-gray-700';
                        echo "<a href='{$item['url']}' class='w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {$activeClass}'>";
                        echo "<i data-lucide='{$item['icon']}' class='w-5 h-5'></i>";
                        echo "<span>{$item['label']}</span>";
                        echo "</a>";
                    }
                }
                ?>
            </nav>
        </aside>

        <!-- Overlay for mobile -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden hidden" onclick="closeSidebar()"></div>

        <!-- Main Content -->
        <main class="flex-1 p-6">