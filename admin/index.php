<?php
require_once __DIR__ . '/auth.php';

if (is_authenticated()) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}
exit;
