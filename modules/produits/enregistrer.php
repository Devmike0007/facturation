<?php
require_once '../../includes/header.php';
require_once '../../includes/fonctions-produits.php';

if (!hasRole('manager')) {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

$error   = '';
$success = '';
$produitExistant = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codeBarre      = trim($_POST['code_barre'] ?? '');
    $nom            = trim($_POST['nom'] ?? '');
    $prixUnitaireHT = trim($_POST['prix_unitaire_ht'] ?? '');
    $dateExpiration = trim($_POST['date_expiration'] ?? '');
    $quantiteStock  = trim($_POST['quantite_stock'] ?? '');

    // Validation
    if (empty($codeBarre) || empty($nom) || $prixUnitaireHT === '' || empty($dateExpiration) || $quantiteStock === '') {
        $error = 'Veuillez remplir tous les champs';
    } elseif (!is_numeric($prixUnitaireHT) || (float)$prixUnitaireHT <= 0) {
        $error = 'Le prix doit être un nombre positif';
    } elseif (!is_numeric($quantiteStock) || (int)$quantiteStock < 0) {
        $error = 'La quantité doit être un entier positif ou nul';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateExpiration) || !checkdate(
            (int)substr($dateExpiration, 5, 2),
            (int)substr($dateExpiration, 8, 2),
            (int)substr($dateExpiration, 0, 4)
        )) {
        $error = 'Format de date invalide (AAAA-MM-JJ attendu)';
    } elseif (produitExists($codeBarre)) {
        $error = 'Un produit avec ce code-barres existe déjà';
        $produitExistant = getProduitByCode($codeBarre);
    } else {
        if (saveProduit($codeBarre, $nom, (float)$prixUnitaireHT, $dateExpiration, (int)$quantiteStock)) {
            $success = 'Produit enregistré avec succès';
            $_POST   = [];
        } else {
            $error = 'Erreur lors de l\'enregistrement du produit';
        }
    }
}
?>

<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center gap-3 mb-6">
            <i data-lucide="package" class="w-6 h-6 text-blue-600"></i>
            <h2 class="text-2xl">Enregistrer un Produit</h2>
        </div>

        <!-- Scanner caméra -->
        <div class="mb-6">
            <button id="btn-scanner" onclick="toggleScanner()"
                class="flex items-center gap-2 px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-800 transition-colors">
                <i data-lucide="camera" class="w-4 h-4"></i>
                Activer la caméra
            </button>
            <div id="scanner-container" class="hidden mt-4">
                <div id="interactive" class="viewport w-full rounded-lg overflow-hidden" style="max-height:300px;"></div>
                <button onclick="toggleScanner()" class="mt-2 text-sm text-red-600 hover:underline">Arrêter la caméra</button>
            </div>
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

        <?php if ($produitExistant): ?>
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
            <p class="text-blue-700 mb-2">Ce produit est déjà enregistré :</p>
            <ul class="text-sm text-blue-600 space-y-1">
                <li>Nom : <?php echo htmlspecialchars($produitExistant['nom']); ?></li>
                <li>Prix HT : <?php echo number_format($produitExistant['prix_unitaire_ht'], 0, ',', ' '); ?> <?php echo CURRENCY; ?></li>
                <li>Stock : <?php echo $produitExistant['quantite_stock']; ?> unités</li>
                <li>Expiration : <?php echo htmlspecialchars($produitExistant['date_expiration'] ?? 'N/A'); ?></li>
            </ul>
        </div>
        <?php endif; ?>

        <form method="POST" class="space-y-5">
            <div>
                <label class="block text-sm mb-2 text-gray-700">Code-barres *</label>
                <input type="text" name="code_barre" id="code_barre"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
                    placeholder="Scannez ou entrez le code-barres"
                    value="<?php echo htmlspecialchars($_POST['code_barre'] ?? ''); ?>" required />
            </div>

            <div>
                <label class="block text-sm mb-2 text-gray-700">Nom du produit *</label>
                <input type="text" name="nom"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
                    placeholder="Entrez le nom du produit"
                    value="<?php echo htmlspecialchars($_POST['nom'] ?? ''); ?>" required />
            </div>

            <div>
                <label class="block text-sm mb-2 text-gray-700">Prix unitaire HT (<?php echo CURRENCY; ?>) *</label>
                <input type="number" name="prix_unitaire_ht" step="0.01" min="0.01"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
                    placeholder="0.00"
                    value="<?php echo htmlspecialchars($_POST['prix_unitaire_ht'] ?? ''); ?>" required />
            </div>

            <div>
                <label class="block text-sm mb-2 text-gray-700">Date d'expiration (AAAA-MM-JJ) *</label>
                <input type="date" name="date_expiration"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
                    value="<?php echo htmlspecialchars($_POST['date_expiration'] ?? ''); ?>" required />
            </div>

            <div>
                <label class="block text-sm mb-2 text-gray-700">Quantité en stock *</label>
                <input type="number" name="quantite_stock" min="0"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
                    placeholder="0"
                    value="<?php echo htmlspecialchars($_POST['quantite_stock'] ?? ''); ?>" required />
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition-colors">
                Enregistrer le produit
            </button>
        </form>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>
<script>
let scannerRunning = false;

function toggleScanner() {
    const container = document.getElementById('scanner-container');
    if (scannerRunning) {
        Quagga.stop();
        container.classList.add('hidden');
        scannerRunning = false;
    } else {
        container.classList.remove('hidden');
        Quagga.init({
            inputStream: {
                name: 'Live',
                type: 'LiveStream',
                target: document.querySelector('#interactive'),
                constraints: { facingMode: 'environment' }
            },
            decoder: { readers: ['ean_reader', 'ean_8_reader', 'code_128_reader', 'code_39_reader'] }
        }, function(err) {
            if (err) { alert('Impossible d\'accéder à la caméra : ' + err); return; }
            Quagga.start();
            scannerRunning = true;
        });

        Quagga.onDetected(function(result) {
            const code = result.codeResult.code;
            document.getElementById('code_barre').value = code;
            Quagga.stop();
            document.getElementById('scanner-container').classList.add('hidden');
            scannerRunning = false;
        });
    }
}
</script>

<?php require_once '../../includes/footer.php'; ?>
