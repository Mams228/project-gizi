<?php
// includes/session.php
require_once __DIR__ . '/../config.php';

// Start session dengan security
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

/**
 * Cek apakah user sudah login
 */
function isLoggedIn() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_username']);
}

/**
 * Require login - redirect ke login jika belum login
 */
function requireLogin() {
    if (!isLoggedIn()) {
        redirect(BASE_URL . 'admin/login.php');
    }
    
    // Cek session timeout
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_destroy();
        redirect(BASE_URL . 'admin/login.php?timeout=1');
    }
    
    $_SESSION['last_activity'] = time();
}

/**
 * Login admin
 */
function loginAdmin($username, $password) {
    $db = getDB();
    $stmt = $db->prepare("SELECT id, username, password, nama_lengkap FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_nama'] = $admin['nama_lengkap'];
            $_SESSION['last_activity'] = time();
            
            logActivity($admin['id'], 'Login', 'Login berhasil');
            return true;
        }
    }
    
    return false;
}

/**
 * Logout admin
 */
function logoutAdmin() {
    if (isLoggedIn()) {
        logActivity($_SESSION['admin_id'], 'Logout', 'Logout');
    }
    
    session_destroy();
    redirect(BASE_URL . 'admin/login.php');
}

/**
 * Get current admin info
 */
function getCurrentAdmin() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['admin_id'],
        'username' => $_SESSION['admin_username'],
        'nama' => $_SESSION['admin_nama']
    ];
}
?>