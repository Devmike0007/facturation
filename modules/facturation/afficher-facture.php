<?php
require_once '../../includes/header.php';
require_once '../../includes/fonctions-factures.php';

$numero = $_GET['numero'] ?? '';
$facture = null;

if ($numero) {
    $facture = getFactureByNumero($numero);
}

if (!$facture) {
    echo '<div class="max-w-4xl mx-auto bg-white rounded-xl shadow-sm p-8 text-center">';
    echo '<p class="text-red-600">Facture non trouvée</p>';
    echo '<a href="nouvelle-facture.php" class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded-lg">Retour</a>';
    echo '</div>';
    require_once '../../includes/footer.php';
    exit;
}
?>

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-8">
        <div class="flex items-center justify-center gap-3 mb-6">
            <i data-lucide="check-circle" class="w-8 h-8 text-green-600"></i>
            <h2 class="text-2xl text-green-600">Facture validée</h2>
        </div>

        <div class="border-2 border-gray-200 rounded-lg p-6 mb-6">
            <div class="text-center mb-6">
                <h1 class="text-3xl mb-2">FACTURE</h1>
                <p class="text-gray-600">Super Marché</p>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-6 text-sm">
                <div>
                    <p class="text-gray-600">Numéro de facture:</p>
                    <p><?php echo htmlspecialchars($facture['id_facture']); ?></p>
                </div>
                <div>
                    <p class="text-gray-600">Caissier:</p>
                    <p><?php echo htmlspecialchars($facture['caissier']); ?></p>
                </div>
                <div>
                    <p class="text-gray-600">Date:</p>
                    <p><?php echo date('d/m/Y', strtotime($facture['date'])); ?></p>
                </div>
                <div>
                    <p class="text-gray-600">Heure:</p>
                    <p><?php echo $facture['heure']; ?></p>
                </div>
            </div>

            <div class="border-t border-gray-200 pt-4 mb-4">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-2">Désignation</th>
                            <th class="text-right py-2">Prix unit. HT</th>
                            <th class="text-center py-2">Qté</th>
                            <th class="text-right py-2">Sous-total HT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($facture['articles'] as $item): ?>
                        <tr class="border-b">
                            <td class="py-2"><?php echo htmlspecialchars($item['nom']); ?></td>
                            <td class="text-right"><?php echo number_format($item['prix_unitaire_ht'], 0, ',', ' '); ?> <?php echo CURRENCY; ?></td>
                            <td class="text-center"><?php echo $item['quantite']; ?></td>
                            <td class="text-right"><?php echo number_format($item['sous_total_ht'], 0, ',', ' '); ?> <?php echo CURRENCY; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="space-y-2 text-right">
                <div class="flex justify-end gap-4">
                    <span>Total HT:</span>
                    <span class="w-32"><?php echo number_format($facture['total_ht'], 0, ',', ' '); ?> <?php echo CURRENCY; ?></span>
                </div>
                <div class="flex justify-end gap-4">
                    <span>TVA (<?php echo (TVA_RATE * 100); ?>%):</span>
                    <span class="w-32"><?php echo number_format($facture['tva'], 0, ',', ' '); ?> <?php echo CURRENCY; ?></span>
                </div>
                <div class="flex justify-end gap-4 pt-2 border-t-2 border-gray-300">
                    <span class="text-xl">Net à payer:</span>
                    <span class="text-xl w-32"><?php echo number_format($facture['total_ttc'], 0, ',', ' '); ?> <?php echo CURRENCY; ?></span>
                </div>
            </div>
        </div>

        <div class="flex gap-4">
            <button onclick="window.print()" class="flex-1 flex items-center justify-center gap-2 bg-gray-600 text-white py-3 rounded-lg hover:bg-gray-700 transition-colors">
                <i data-lucide="printer" class="w-5 h-5"></i>
                Imprimer
            </button>
            <a href="nouvelle-facture.php" class="flex-1 bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition-colors text-center">
                Nouvelle facture
            </a>
        </div>
    </div>
</div>

<style media="print">
    @page {
        margin: 1cm;
        size: A4;
    }

    body * {
        visibility: hidden;
    }

    .bg-white.rounded-xl.shadow-sm.p-8,
    .bg-white.rounded-xl.shadow-sm.p-8 * {
        visibility: visible;
    }

    .bg-white.rounded-xl.shadow-sm.p-8 {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }

    .flex.gap-4 {
        display: none !important;
    }
</style>

<?php require_once '../../includes/footer.php'; ?>