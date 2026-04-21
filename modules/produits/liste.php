<?php
require_once '../../includes/header.php';
require_once '../../includes/fonctions-produits.php';

if (!hasRole('manager')) {
    header('Location: ../../index.php');
    exit;
}

$produits = getProduits();
$search = $_GET['search'] ?? '';

if ($search) {
    $produits = searchProduits($search);
}

// Gestion de la mise à jour du stock
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    $codeBarre = $_POST['code_barre'];
    $nouvelleQuantite = (int) $_POST['nouvelle_quantite'];

    if (updateStock($codeBarre, $nouvelleQuantite)) {
        $success = 'Stock mis à jour avec succès';
        $produits = getProduits(); // Recharger les produits
        if ($search) {
            $produits = searchProduits($search);
        }
    } else {
        $error = 'Erreur lors de la mise à jour du stock';
    }
}

// Gestion de la suppression
if (isset($_GET['delete'])) {
    $codeBarre = $_GET['delete'];
    if (deleteProduit($codeBarre)) {
        $success = 'Produit supprimé avec succès';
        $produits = getProduits();
        if ($search) {
            $produits = searchProduits($search);
        }
    } else {
        $error = 'Erreur lors de la suppression du produit';
    }
}
?>

<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <i data-lucide="file-text" class="w-6 h-6 text-blue-600"></i>
                <h2 class="text-2xl">Liste des Produits</h2>
            </div>
            <a href="enregistrer.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                <i data-lucide="plus" class="w-4 h-4 inline mr-2"></i>
                Nouveau produit
            </a>
        </div>

        <!-- Barre de recherche -->
        <div class="mb-6">
            <form method="GET" class="flex gap-4">
                <input
                    type="text"
                    name="search"
                    placeholder="Rechercher un produit..."
                    value="<?php echo htmlspecialchars($search); ?>"
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                />
                <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </button>
                <?php if ($search): ?>
                <a href="liste.php" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors">
                    Effacer
                </a>
                <?php endif; ?>
            </form>
        </div>

        <?php if (isset($error)): ?>
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
            <p class="text-red-700"><?php echo htmlspecialchars($error); ?></p>
        </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
            <p class="text-green-700"><?php echo htmlspecialchars($success); ?></p>
        </div>
        <?php endif; ?>

        <?php if (empty($produits)): ?>
        <div class="text-center py-12 text-gray-500">
            <i data-lucide="package" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
            <p><?php echo $search ? 'Aucun produit trouvé pour "' . htmlspecialchars($search) . '"' : 'Aucun produit enregistré'; ?></p>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-3 px-4">Code-barres</th>
                        <th class="text-left py-3 px-4">Nom</th>
                        <th class="text-right py-3 px-4">Prix HT</th>
                        <th class="text-center py-3 px-4">Expiration</th>
                        <th class="text-center py-3 px-4">Stock</th>
                        <th class="text-center py-3 px-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($produits as $produit): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-3 px-4"><?php echo htmlspecialchars($produit['code_barre']); ?></td>
                        <td class="py-3 px-4"><?php echo htmlspecialchars($produit['nom']); ?></td>
                        <td class="py-3 px-4 text-right"><?php echo number_format($produit['prix_unitaire_ht'], 0, ',', ' '); ?> <?php echo CURRENCY; ?></td>
                        <td class="py-3 px-4 text-center"><?php echo isset($produit['date_expiration']) ? date('d/m/Y', strtotime($produit['date_expiration'])) : 'N/A'; ?></td>
                        <td class="py-3 px-4 text-center">
                            <form method="POST" class="inline-flex items-center gap-2">
                                <input type="hidden" name="code_barre" value="<?php echo htmlspecialchars($produit['code_barre']); ?>">
                                <input
                                    type="number"
                                    name="nouvelle_quantite"
                                    value="<?php echo $produit['quantite_stock']; ?>"
                                    min="0"
                                    class="w-20 px-2 py-1 border border-gray-300 rounded text-center"
                                />
                                <button type="submit" name="update_stock" class="text-blue-600 hover:text-blue-800">
                                    <i data-lucide="save" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <a href="?delete=<?php echo urlencode($produit['code_barre']); ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>"
                               class="text-red-600 hover:text-red-800"
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-4 text-sm text-gray-600">
            Total: <?php echo count($produits); ?> produit(s)
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>