<?php
session_start();

function isLoggedIn()
{
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/admin/login.php');
        exit;
    }
}

function login($username, $password)
{
    $db = getDB();
    $username = $db->real_escape_string($username);
    $result = $db->query("SELECT * FROM admins WHERE username = '$username' OR email = '$username' LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['full_name'];
            $_SESSION['admin_role'] = $admin['role'];
            $_SESSION['admin_username'] = $admin['username'];
            return true;
        }
    }
    return false;
}

function logout()
{
    session_destroy();
    header('Location: ' . BASE_URL . '/admin/login.php');
    exit;
}

function currentAdmin()
{
    return [
        'id' => $_SESSION['admin_id'] ?? null,
        'name' => $_SESSION['admin_name'] ?? '',
        'role' => $_SESSION['admin_role'] ?? '',
        'username' => $_SESSION['admin_username'] ?? ''
    ];
}
?>