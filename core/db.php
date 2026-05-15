<?php
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER, DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
    }
    return $pdo;
}

function h(?string $s): string {
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

function checkLogin(): bool {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return !empty($_SESSION['love_uid']);
}

function requireLogin(): void {
    if (!checkLogin()) {
        header('Location: ' . SITE_URL . '/' . ADMIN_DIR . '/login.php');
        exit;
    }
}

function render(string $template, array $data = []): string {
    extract($data);
    ob_start();
    include ROOT_PATH . '/templates/admin/' . $template . '.php';
    return ob_get_clean();
}