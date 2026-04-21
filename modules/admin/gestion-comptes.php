<?php
require_once '../../includes/header.php';
require_once '../../includes/fonctions-auth.php';

if (!hasRole('super_admin')) {
    header('Location: ../../index.php');
    exit;
}

$users = getUsers();
$error = '';
$success = '';

// Ajouter un utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];
    $name = trim($_POST['name']);

    if (empty($username) || empty($password) || empty($role)) {
        $error = 'Veuillez remplir tous les champs obligatoires';
    } elseif (!isValidRole($role)) {
        $error = 'Rôle invalide';
    } elseif (isset($users[$username])) {
        $error = 'Cet identifiant existe déjà';
    } else {
        if (addUser($username, $password, $role, $name)) {
            $success = 'Utilisateur ajouté avec succès';
            $users = getUsers(); // Recharger la liste
        } else {
            $error = 'Erreur lors de l\'ajout de l\'utilisateur';
        }
    }
}

// Supprimer un utilisateur
if (isset($_GET['delete'])) {
    $username = $_GET['delete'];

    // Empêcher la suppression de l'admin actuel
    if ($username === $currentUser['id']) {
        $error = 'Vous ne pouvez pas supprimer votre propre compte';
    } elseif (deleteUser($username)) {
        $success = 'Utilisateur supprimé avec succès';
        $users = getUsers();
    } else {
        $error = 'Erreur lors de la suppression de l\'utilisateur';
    }
}
?>

<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <i data-lucide="users" class="w-6 h-6 text-blue-600"></i>
                <h2 class="text-2xl">Gestion des Comptes Utilisateur</h2>
            </div>
            <button onclick="document.getElementById('add-user-modal').classList.remove('hidden')"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                <i data-lucide="plus" class="w-4 h-4 inline mr-2"></i>
                Ajouter un utilisateur
            </button>
        </div>

        <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
            <p class="text-red-700"><?php echo htmlspecialchars($error); ?></p>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
            <p class="text-green-700"><?php echo htmlspecialchars($success); ?></p>
        </div>
        <?php endif; ?>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3 px-4">Identifiant</th>
                        <th class="text-left py-3 px-4">Nom</th>
                        <th class="text-left py-3 px-4">Rôle</th>
                        <th class="text-left py-3 px-4">Date de création</th>
                        <th class="text-center py-3 px-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $username => $user): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-3 px-4"><?php echo htmlspecialchars($username); ?></td>
                        <td class="py-3 px-4"><?php echo htmlspecialchars($user['nom_complet'] ?? $username); ?></td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-1 rounded-full text-xs <?php
                                echo $user['role'] === 'super_admin' ? 'bg-red-100 text-red-800' :
                                     ($user['role'] === 'manager' ? 'bg-blue-100 text-blue-800' :
                                      'bg-green-100 text-green-800');
                            ?>">
                                <?php echo ROLES[$user['role']] ?? $user['role']; ?>
                            </span>
                        </td>
                        <td class="py-3 px-4"><?php echo isset($user['date_creation']) ? date('d/m/Y', strtotime($user['date_creation'])) : 'N/A'; ?></td>
                        <td class="py-3 px-4 text-center">
                            <?php if ($username !== $currentUser['id']): ?>
                            <a href="?delete=<?php echo urlencode($username); ?>"
                               class="text-red-600 hover:text-red-800"
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </a>
                            <?php else: ?>
                            <span class="text-gray-400">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-4 text-sm text-gray-600">
            Total: <?php echo count($users); ?> utilisateur(s)
        </div>
    </div>
</div>

<!-- Modal d'ajout d'utilisateur -->
<div id="add-user-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl">Ajouter un utilisateur</h3>
                <button onclick="document.getElementById('add-user-modal').classList.add('hidden')"
                        class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <form method="POST">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm mb-2 text-gray-700">Identifiant *</label>
                        <input
                            type="text"
                            name="username"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                            required
                        />
                    </div>

                    <div>
                        <label class="block text-sm mb-2 text-gray-700">Nom complet</label>
                        <input type="text" name="name"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none" />
                    </div>

                    <div>
                        <label class="block text-sm mb-2 text-gray-700">Mot de passe *</label>
                        <input
                            type="password"
                            name="password"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                            required
                        />
                    </div>

                    <div>
                        <label class="block text-sm mb-2 text-gray-700">Rôle *</label>
                        <select
                            name="role"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                            required
                        >
                            <option value="">Sélectionner un rôle</option>
                            <?php foreach (ROLES as $roleKey => $roleName): ?>
                            <option value="<?php echo $roleKey; ?>"><?php echo $roleName; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="button"
                            onclick="document.getElementById('add-user-modal').classList.add('hidden')"
                            class="flex-1 bg-gray-500 text-white py-2 rounded-lg hover:bg-gray-600 transition-colors">
                        Annuler
                    </button>
                    <button type="submit" name="add_user"
                            class="flex-1 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        Ajouter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>