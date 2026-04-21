<?php
require_once(__DIR__ . '/../config/config.php');

session_start();

// Fonction pour vérifier si l'utilisateur est connecté
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

// Fonction pour vérifier le rôle de l'utilisateur
function hasRole($requiredRole) {
    if (!isLoggedIn()) return false;

    $userRole = $_SESSION['role'];

    // Hiérarchie des rôles
    $roleHierarchy = [
        'caissier' => 1,
        'manager' => 2,
        'super_admin' => 3
    ];

    return isset($roleHierarchy[$userRole]) &&
           isset($roleHierarchy[$requiredRole]) &&
           $roleHierarchy[$userRole] >= $roleHierarchy[$requiredRole];
}

// Fonction pour obtenir les informations de l'utilisateur connecté
function getCurrentUser() {
    if (!isLoggedIn()) return null;

    return [
        'id' => $_SESSION['user_id'],
        'role' => $_SESSION['role'],
        'name' => $_SESSION['user_name'] ?? $_SESSION['user_id']
    ];
}

// Fonction pour vérifier le timeout de session
function checkSessionTimeout() {
    if (isset($_SESSION['last_activity']) &&
        (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_destroy();
        header('Location: ' . BASE_URL . 'auth/login.php?timeout=1');
        exit;
    }
    $_SESSION['last_activity'] = time();
}

// Fonction pour régénérer l'ID de session
function regenerateSession() {
    if (!isset($_SESSION['regenerated'])) {
        session_regenerate_id(true);
        $_SESSION['regenerated'] = true;
    }
}

// Vérifier la session à chaque chargement de page
if (isLoggedIn()) {
    checkSessionTimeout();
    regenerateSession();
}
?>