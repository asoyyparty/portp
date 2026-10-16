<?php
require_once __DIR__ . '/auth.php';

if (is_authenticated()) {
    header('Location: /admin/dashboard.php');
} else {
    header('Location: /admin/login.php');
}
exit;
