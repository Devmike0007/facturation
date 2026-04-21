<?php
require_once __DIR__ . '/../config/config.php';

function getProduits() {
    $content = file_get_contents(PRODUITS_FILE);
    return $content ? json_decode($content, true) : [];
}

function getProduitByCode($codeBarre) {
    $produits = getProduits();
    return $produits[$codeBarre] ?? null;
}

function saveProduit($codeBarre, $nom, $prixUnitaireHT, $dateExpiration, $quantiteStock) {
    $produits = getProduits();

    $produits[$codeBarre] = [
        'code_barre'        => $codeBarre,
        'nom'               => $nom,
        'prix_unitaire_ht'  => (float) $prixUnitaireHT,
        'date_expiration'   => $dateExpiration,
        'quantite_stock'    => (int) $quantiteStock,
        'date_enregistrement' => date('Y-m-d')
    ];

    return file_put_contents(PRODUITS_FILE, json_encode($produits, JSON_PRETTY_PRINT)) !== false;
}

function updateStock($codeBarre, $nouvelleQuantite) {
    $produits = getProduits();
    if (!isset($produits[$codeBarre])) return false;

    $produits[$codeBarre]['quantite_stock'] = (int) $nouvelleQuantite;
    return file_put_contents(PRODUITS_FILE, json_encode($produits, JSON_PRETTY_PRINT)) !== false;
}

function deleteProduit($codeBarre) {
    $produits = getProduits();
    if (!isset($produits[$codeBarre])) return false;

    unset($produits[$codeBarre]);
    return file_put_contents(PRODUITS_FILE, json_encode($produits, JSON_PRETTY_PRINT)) !== false;
}

function searchProduits($query) {
    $produits = getProduits();
    $resultats = [];
    foreach ($produits as $codeBarre => $produit) {
        if (stripos($produit['nom'], $query) !== false) {
            $resultats[$codeBarre] = $produit;
        }
    }
    return $resultats;
}

function produitExists($codeBarre) {
    return isset(getProduits()[$codeBarre]);
}
?>
