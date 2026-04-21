<?php
require_once '../config/config.php';
require_once 'session.php';
require_once '../includes/fonctions-auth.php';

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

$error   = '';
$timeout = isset($_GET['timeout']) && $_GET['timeout'] == '1';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Veuillez remplir tous les champs';
    } else {
        $user = authenticateUser($username, $password);
        if ($user) {
            $_SESSION['user_id']       = $user['id'];
            $_SESSION['role']          = $user['role'];
            $_SESSION['user_name']     = $user['name'];
            $_SESSION['last_activity'] = time();
            header('Location: ' . BASE_URL . 'index.php');
            exit;
        } else {
            $error = 'Identifiant ou mot de passe incorrect';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Connexion</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-blue-100">
    <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600 rounded-full mb-4">
                <i data-lucide="lock" class="w-8 h-8 text-white"></i>
            </div>
            <h1 class="text-3xl mb-2"><?php echo APP_NAME; ?></h1>
            <p class="text-gray-600">Connectez-vous pour continuer</p>
        </div>

        <?php if ($timeout): ?>
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-lg mb-4">
            Votre session a expiré. Veuillez vous reconnecter.
        </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-sm mb-2 text-gray-700">Identifiant</label>
                <div class="relative">
                    <i data-lucide="user" class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"></i>
                    <input type="text" name="username"
                        class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                        placeholder="Entrez votre identifiant" required
                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" />
                </div>
            </div>

            <div>
                <label class="block text-sm mb-2 text-gray-700">Mot de passe</label>
                <div class="relative">
                    <i data-lucide="lock" class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"></i>
                    <input type="password" name="password"
                        class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                        placeholder="Entrez votre mot de passe" required />
                </div>
            </div>

            <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition-colors">
                Se connecter
            </button>
        </form>

        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
            <p class="text-xs text-gray-600 mb-2">Comptes de démonstration :</p>
            <p class="text-xs text-gray-500">• admin / admin123 (Super Admin)</p>
            <p class="text-xs text-gray-500">• manager / manager123 (Manager)</p>
            <p class="text-xs text-gray-500">• caissier / caissier123 (Caissier)</p>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
