<?php
// admin/logout.php
session_start();
require_once '../config.php';
require_once '../includes/functions.php';

// Destroy session
session_unset();
session_destroy();

// Set flash message
session_start();
set_flash('success', 'Anda telah berhasil logout');

// Redirect to login
redirect('admin/login.php');
?>
