<?php
// Configuration de l'application
define('APP_NAME', 'Système de Caisse');
define('APP_VERSION', '1.0.0');

// Configuration de la base de données (si utilisée plus tard)
define('DB_HOST', 'localhost');
define('DB_NAME', 'facturation_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// URL de base de l'application
define('BASE_URL', '/facturation/');

// Chemins des fichiers de données
define('DATA_DIR', __DIR__ . '/../data/');
define('PRODUITS_FILE', DATA_DIR . 'produits.json');
define('FACTURES_FILE', DATA_DIR . 'factures.json');
define('UTILISATEURS_FILE', DATA_DIR . 'utilisateurs.json');

// Configuration de session
define('SESSION_TIMEOUT', 3600); // 1 heure

// Taux de TVA
define('TVA_RATE', 0.18);

// Devise
define('CURRENCY', 'CDF');

// Rôles utilisateur
define('ROLES', [
    'super_admin' => 'Super Administrateur',
    'manager' => 'Manager',
    'caissier' => 'Caissier'
]);

// Comptes de démonstration
define('DEMO_USERS', [
    'admin' => [
        'password' => 'admin123',
        'role' => 'super_admin',
        'name' => 'Super Administrateur'
    ],
    'manager' => [
        'password' => 'manager123',
        'role' => 'manager',
        'name' => 'Manager'
    ],
    'caissier' => [
        'password' => 'caissier123',
        'role' => 'caissier',
        'name' => 'Caissier'
    ]
]);
?>