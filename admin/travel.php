<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/db.php';
session_start();
if (empty($_SESSION['love_uid'])) { header('Location: login.php'); exit; }
$currentPage = 'travel';
header("Location: crud.php?type=travel");
exit;