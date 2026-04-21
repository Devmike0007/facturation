<?php
require_once '../../includes/header.php';
require_once '../../includes/fonctions-produits.php';
require_once '../../includes/fonctions-factures.php';

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
$cart    = &$_SESSION['cart'];
$error   = '';
$success = '';

// Scan code-barres
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['scan_barcode'])) {
    $barcode = trim($_POST['scan_barcode']);
    if (empty($barcode)) {
        $error = 'Veuillez entrer un code-barres';
    } else {
        $produit = getProduitByCode($barcode);
        $_SESSION['current_product'] = $produit ?: null;
        if (!$produit) $error = 'Produit non trouvé. Veuillez demander au Manager de l\'enregistrer.';
    }
}

// Ajouter au panier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $codeBarre = $_POST['code_barre'];
    $quantite  = (int) $_POST['quantite'];
    $produit   = getProduitByCode($codeBarre);

    if (!$produit) {
        $error = 'Produit introuvable';
    } elseif ($quantite <= 0) {
        $error = 'La quantité doit être supérieure à zéro';
    } elseif ($quantite > $produit['quantite_stock']) {
        $error = 'Stock insuffisant. Disponible : ' . $produit['quantite_stock'] . ' unités';
    } else {
        $cart[] = [
            'code_barre'      => $codeBarre,
            'nom'             => $produit['nom'],
            'prix_unitaire_ht'=> $produit['prix_unitaire_ht'],
            'quantite'        => $quantite,
            'sous_total_ht'   => $produit['prix_unitaire_ht'] * $quantite
        ];
        $_SESSION['current_product'] = null;
        $success = 'Article ajouté';
    }
}

// Supprimer un article
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $index = (int) $_GET['remove'];
    if (isset($cart[$index])) {
        unset($cart[$index]);
        $cart    = array_values($cart);
        $success = 'Article supprimé';
    }
}

// Valider la facture
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['validate_invoice'])) {
    if (empty($cart)) {
        $error = 'Le panier est vide';
    } else {
        $facture = createFacture($cart, $currentUser['name']);
        if ($facture) {
            $cart = [];
            header('Location: afficher-facture.php?numero=' . urlencode($facture['id_facture']));
            exit;
        } else {
            $error = 'Erreur lors de la création de la facture';
        }
    }
}

$totals         = calculateTotals($cart);
$currentProduct = $_SESSION['current_product'] ?? null;
?>

<div class="max-w-6xl mx-auto">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Scan et ajout produit -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center gap-3 mb-6">
                <i data-lucide="shopping-cart" class="w-6 h-6 text-blue-600"></i>
                <h2 class="text-2xl">Nouvelle Facture</h2>
            </div>

            <!-- Bouton caméra -->
            <div class="mb-4">
                <button onclick="toggleScanner()"
                    class="flex items-center gap-2 px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-800 transition-colors">
                    <i data-lucide="camera" class="w-4 h-4"></i>
                    Scanner avec la caméra
                </button>
                <div id="scanner-container" class="hidden mt-3">
                    <div id="interactive" class="viewport w-full rounded-lg overflow-hidden" style="max-height:250px;"></div>
                    <button onclick="toggleScanner()" class="mt-1 text-sm text-red-600 hover:underline">Arrêter</button>
                </div>
            </div>

            <form method="POST" class="mb-4">
                <label class="block text-sm mb-2 text-gray-700">Code-barres</label>
                <div class="flex gap-2">
                    <input type="text" name="scan_barcode" id="barcode-input"
                        class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
                        placeholder="Scannez ou entrez le code-barres"
                        value="<?php echo htmlspecialchars($_POST['scan_barcode'] ?? ''); ?>" />
                    <button type="submit" class="bg-blue-600 text-white px-4 py-3 rounded-lg hover:bg-blue-700 transition-colors">
                        <i data-lucide="search" class="w-5 h-5"></i>
                    </button>
                </div>
            </form>

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

            <?php if ($currentProduct): ?>
            <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <h3 class="mb-3">Produit trouvé</h3>
                <div class="space-y-1 text-sm mb-4">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Nom :</span>
                        <span><?php echo htmlspecialchars($currentProduct['nom']); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Prix HT :</span>
                        <span><?php echo number_format($currentProduct['prix_unitaire_ht'], 0, ',', ' '); ?> <?php echo CURRENCY; ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Stock :</span>
                        <span><?php echo $currentProduct['quantite_stock']; ?> unités</span>
                    </div>
                </div>
                <form method="POST">
                    <input type="hidden" name="code_barre" value="<?php echo htmlspecialchars($currentProduct['code_barre']); ?>">
                    <div class="mb-3">
                        <label class="block text-sm mb-1 text-gray-700">Quantité</label>
                        <input type="number" name="quantite" value="1" min="1"
                            max="<?php echo $currentProduct['quantite_stock']; ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" required />
                    </div>
                    <button type="submit" name="add_to_cart"
                        class="w-full flex items-center justify-center gap-2 bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition-colors">
                        <i data-lucide="plus" class="w-5 h-5"></i>
                        Ajouter à la facture
                    </button>
                </form>
            </div>
            <?php endif; ?>
        </div>

        <!-- Panier -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-2xl mb-6">Articles (<?php echo count($cart); ?>)</h2>

            <?php if (empty($cart)): ?>
            <div class="text-center py-12 text-gray-500">
                <i data-lucide="shopping-cart" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                <p>Aucun article ajouté</p>
            </div>
            <?php else: ?>
            <div class="space-y-3 mb-6 max-h-96 overflow-y-auto">
                <?php foreach ($cart as $index => $item): ?>
                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                    <div class="flex-1">
                        <p><?php echo htmlspecialchars($item['nom']); ?></p>
                        <p class="text-sm text-gray-600">
                            <?php echo number_format($item['prix_unitaire_ht'], 0, ',', ' '); ?> <?php echo CURRENCY; ?> × <?php echo $item['quantite']; ?>
                        </p>
                    </div>
                    <div class="text-right">
                        <p><?php echo number_format($item['sous_total_ht'], 0, ',', ' '); ?> <?php echo CURRENCY; ?></p>
                    </div>
                    <a href="?remove=<?php echo $index; ?>"
                       class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                       onclick="return confirm('Supprimer cet article ?')">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="border-t border-gray-200 pt-4 space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-600">Total HT :</span>
                    <span><?php echo number_format($totals['total_ht'], 0, ',', ' '); ?> <?php echo CURRENCY; ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">TVA (<?php echo (TVA_RATE * 100); ?>%) :</span>
                    <span><?php echo number_format($totals['tva'], 0, ',', ' '); ?> <?php echo CURRENCY; ?></span>
                </div>
                <div class="flex justify-between pt-2 border-t-2 border-gray-300">
                    <span class="text-xl">Net à payer :</span>
                    <span class="text-xl"><?php echo number_format($totals['total_ttc'], 0, ',', ' '); ?> <?php echo CURRENCY; ?></span>
                </div>
            </div>

            <form method="POST" class="mt-6">
                <button type="submit" name="validate_invoice"
                    class="w-full bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 transition-colors">
                    Valider la facture
                </button>
            </form>
            <?php endif; ?>
        </div>
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
            document.getElementById('barcode-input').value = code;
            Quagga.stop();
            container.classList.add('hidden');
            scannerRunning = false;
            document.querySelector('form [name="scan_barcode"]').closest('form').submit();
        });
    }
}
</script>

<?php require_once '../../includes/footer.php'; ?>
