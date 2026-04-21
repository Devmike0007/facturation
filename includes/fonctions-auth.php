<?php
require_once __DIR__ . '/../config/config.php';

function authenticateUser($username, $password) {
    $users = json_decode(file_get_contents(UTILISATEURS_FILE), true) ?? [];

    if (isset($users[$username]) && $users[$username]['actif'] === true) {
        if (password_verify($password, $users[$username]['mot_de_passe'])) {
            return [
                'id'   => $username,
                'role' => $users[$username]['role'],
                'name' => $users[$username]['nom_complet'] ?? $username
            ];
        }
    }
    return false;
}

function addUser($username, $password, $role, $nom_complet = null) {
    $users = json_decode(file_get_contents(UTILISATEURS_FILE), true) ?? [];

    if (isset($users[$username])) return false;

    $users[$username] = [
        'identifiant'   => $username,
        'mot_de_passe'  => password_hash($password, PASSWORD_DEFAULT),
        'role'          => $role,
        'nom_complet'   => $nom_complet ?? $username,
        'date_creation' => date('Y-m-d'),
        'actif'         => true
    ];

    return file_put_contents(UTILISATEURS_FILE, json_encode($users, JSON_PRETTY_PRINT)) !== false;
}

function deleteUser($username) {
    $users = json_decode(file_get_contents(UTILISATEURS_FILE), true) ?? [];

    if (!isset($users[$username])) return false;

    unset($users[$username]);
    return file_put_contents(UTILISATEURS_FILE, json_encode($users, JSON_PRETTY_PRINT)) !== false;
}

function getUsers() {
    return json_decode(file_get_contents(UTILISATEURS_FILE), true) ?? [];
}

function isValidRole($role) {
    return array_key_exists($role, ROLES);
}
?>
