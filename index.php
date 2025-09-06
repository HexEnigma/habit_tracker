<?php
require_once 'config.php';

if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
} else {
    header('Location: home.php');
    exit;
}
