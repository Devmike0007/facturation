<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/fonctions-produits.php';

function generateInvoiceNumber() {
    $date     = date('Ymd');
    $factures = getFactures();
    $count    = 0;
    foreach ($factures as $f) {
        if (strpos($f['id_facture'], 'FAC-' . $date) === 0) $count++;
    }
    return 'FAC-' . $date . '-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
}

function getFactures() {
    $content = file_get_contents(FACTURES_FILE);
    return ($content && $content !== '[]') ? json_decode($content, true) : [];
}

function getFactureByNumero($numero) {
    foreach (getFactures() as $facture) {
        if ($facture['id_facture'] === $numero) return $facture;
    }
    return null;
}

function calculateTotals($articles) {
    $total_ht = 0;
    foreach ($articles as $item) {
        $total_ht += $item['sous_total_ht'];
    }
    $tva       = round($total_ht * TVA_RATE);
    $total_ttc = $total_ht + $tva;
    return compact('total_ht', 'tva', 'total_ttc');
}

function createFacture($articles, $caissier) {
    $id_facture = generateInvoiceNumber();
    $totals     = calculateTotals($articles);

    $facture = [
        'id_facture' => $id_facture,
        'date'       => date('Y-m-d'),
        'heure'      => date('H:i:s'),
        'caissier'   => $caissier,
        'articles'   => $articles,
        'total_ht'   => $totals['total_ht'],
        'tva'        => $totals['tva'],
        'total_ttc'  => $totals['total_ttc']
    ];

    // Décrémenter le stock
    foreach ($articles as $item) {
        $produit = getProduitByCode($item['code_barre']);
        if ($produit) {
            updateStock($item['code_barre'], $produit['quantite_stock'] - $item['quantite']);
        }
    }

    $factures   = getFactures();
    $factures[] = $facture;

    return file_put_contents(FACTURES_FILE, json_encode($factures, JSON_PRETTY_PRINT)) !== false ? $facture : false;
}

function getFacturesByPeriod($dateDebut, $dateFin) {
    return array_values(array_filter(getFactures(), function($f) use ($dateDebut, $dateFin) {
        return $f['date'] >= $dateDebut && $f['date'] <= $dateFin;
    }));
}

function getFacturesToday() {
    $today = date('Y-m-d');
    return getFacturesByPeriod($today, $today);
}

function getSalesStats($dateDebut, $dateFin) {
    $factures = getFacturesByPeriod($dateDebut, $dateFin);
    $stats = [
        'nombre_factures'  => count($factures),
        'total_ventes'     => 0,
        'total_ht'         => 0,
        'total_tva'        => 0,
        'produits_vendus'  => []
    ];

    foreach ($factures as $facture) {
        $stats['total_ventes'] += $facture['total_ttc'];
        $stats['total_ht']     += $facture['total_ht'];
        $stats['total_tva']    += $facture['tva'];

        foreach ($facture['articles'] as $item) {
            $code = $item['code_barre'];
            if (!isset($stats['produits_vendus'][$code])) {
                $stats['produits_vendus'][$code] = ['nom' => $item['nom'], 'quantite' => 0, 'total' => 0];
            }
            $stats['produits_vendus'][$code]['quantite'] += $item['quantite'];
            $stats['produits_vendus'][$code]['total']    += $item['sous_total_ht'];
        }
    }
    return $stats;
}
?>
