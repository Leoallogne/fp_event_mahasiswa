<?php
session_start();
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../modules/users/Auth.php';

$auth = new Auth();
$auth->logout();

header('Location: login.php');
exit;

