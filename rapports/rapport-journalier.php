<?php
require_once '../includes/header.php';
require_once '../includes/fonctions-factures.php';

if (!hasRole('manager')) {
    header('Location: ../index.php');
    exit;
}

$date = $_GET['date'] ?? date('Y-m-d');
$factures = getFacturesByPeriod($date, $date);
$stats = getSalesStats($date, $date);
?>

<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center gap-3 mb-6">
            <i data-lucide="bar-chart-3" class="w-6 h-6 text-blue-600"></i>
            <h2 class="text-2xl">Rapport Journalier</h2>
        </div>

        <!-- Sélecteur de date -->
        <div class="mb-6">
            <form method="GET" class="flex gap-4 items-center">
                <label class="text-sm text-gray-700">Date:</label>
                <input
                    type="date"
                    name="date"
                    value="<?php echo $date; ?>"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                />
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    Afficher
                </button>
            </form>
        </div>

        <!-- Statistiques générales -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-blue-600"><?php echo $stats['nombre_factures']; ?></div>
                <div class="text-sm text-gray-600">Factures</div>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-green-600"><?php echo number_format($stats['total_ventes'], 0, ',', ' '); ?> <?php echo CURRENCY; ?></div>
                <div class="text-sm text-gray-600">Ventes TTC</div>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-yellow-600"><?php echo number_format($stats['total_ht'], 0, ',', ' '); ?> <?php echo CURRENCY; ?></div>
                <div class="text-sm text-gray-600">Ventes HT</div>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg">
                <div class="text-2xl font-bold text-purple-600"><?php echo number_format($stats['total_tva'], 0, ',', ' '); ?> <?php echo CURRENCY; ?></div>
                <div class="text-sm text-gray-600">TVA collectée</div>
            </div>
        </div>

        <!-- Liste des factures -->
        <div class="mb-6">
            <h3 class="text-lg mb-4">Factures du <?php echo date('d/m/Y', strtotime($date)); ?></h3>

            <?php if (empty($factures)): ?>
            <div class="text-center py-8 text-gray-500">
                <i data-lucide="file-text" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                <p>Aucune facture pour cette date</p>
            </div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-3 px-4">N° Facture</th>
                            <th class="text-left py-3 px-4">Caissier</th>
                            <th class="text-center py-3 px-4">Heure</th>
                            <th class="text-right py-3 px-4">Total HT</th>
                            <th class="text-right py-3 px-4">TVA</th>
                            <th class="text-right py-3 px-4">Total TTC</th>
                            <th class="text-center py-3 px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($factures as $facture): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4"><?php echo htmlspecialchars($facture['numero']); ?></td>
                            <td class="py-3 px-4"><?php echo htmlspecialchars($facture['caissier']); ?></td>
                            <td class="py-3 px-4 text-center"><?php echo $facture['heure']; ?></td>
                            <td class="py-3 px-4 text-right"><?php echo number_format($facture['total_ht'], 0, ',', ' '); ?> <?php echo CURRENCY; ?></td>
                            <td class="py-3 px-4 text-right"><?php echo number_format($facture['tva'], 0, ',', ' '); ?> <?php echo CURRENCY; ?></td>
                            <td class="py-3 px-4 text-right font-semibold"><?php echo number_format($facture['total_ttc'], 0, ',', ' '); ?> <?php echo CURRENCY; ?></td>
                            <td class="py-3 px-4 text-center">
                                <a href="../modules/facturation/afficher-facture.php?numero=<?php echo urlencode($facture['numero']); ?>"
                                   class="text-blue-600 hover:text-blue-800">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <!-- Produits vendus -->
        <?php if (!empty($stats['produits_vendus'])): ?>
        <div>
            <h3 class="text-lg mb-4">Produits vendus</h3>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-3 px-4">Produit</th>
                            <th class="text-center py-3 px-4">Quantité vendue</th>
                            <th class="text-right py-3 px-4">Total HT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats['produits_vendus'] as $produit): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4"><?php echo htmlspecialchars($produit['nom']); ?></td>
                            <td class="py-3 px-4 text-center"><?php echo $produit['quantite']; ?></td>
                            <td class="py-3 px-4 text-right"><?php echo number_format($produit['total'], 0, ',', ' '); ?> <?php echo CURRENCY; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>