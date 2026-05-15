<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/db.php';

session_start();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        try {
            $pdo = getDB();
            $stmt = $pdo->prepare('SELECT * FROM love_users WHERE username = ?');
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['love_uid'] = $user['id'];
                $_SESSION['love_username'] = $user['username'];
                header('Location: index.php');
                exit;
            }
        } catch (Exception $e) {}
    }
    $error = '用户名或密码错误';
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>登录 - 恋爱系统</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#fff5f7,#ffe0e8,#f5e6ff)}
.login-box{background:#fff;padding:40px;border-radius:20px;box-shadow:0 10px 40px rgba(255,107,138,0.2);width:100%;max-width:360px;text-align:center}
.login-box h1{font-size:1.8rem;color:#ff6b8a;margin-bottom:8px}
.login-box p{color:#999;margin-bottom:30px;font-size:.9rem}
.form-group{margin-bottom:16px;text-align:left}
.form-group label{display:block;font-size:.85rem;color:#666;margin-bottom:6px}
.form-group input{width:100%;padding:12px;border:2px solid #eee;border-radius:10px;font-size:1rem;font-family:inherit;transition:border-color .2s}
.form-group input:focus{outline:none;border-color:#ff6b8a}
.btn{width:100%;padding:14px;border:none;border-radius:10px;background:linear-gradient(135deg,#ff6b8a,#c77dba);color:#fff;font-size:1rem;font-weight:500;cursor:pointer;transition:transform .1s,box-shadow .1s}
.btn:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(255,107,138,0.3)}
.error{background:#ffebee;color:#c62828;padding:10px;border-radius:8px;margin-bottom:20px;font-size:.9rem}
</style>
</head>
<body>
<div class="login-box">
  <h1>💕 恋爱系统</h1>
  <p>后台管理登录</p>
  <?php if ($error): ?>
  <div class="error"><?=$error?></div>
  <?php endif ?>
  <form method="post">
    <div class="form-group">
      <label>用户名</label>
      <input type="text" name="username" value="<?=htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES)?>" required autofocus>
    </div>
    <div class="form-group">
      <label>密码</label>
      <input type="password" name="password" required>
    </div>
    <button type="submit" class="btn">登 录</button>
  </form>
</div>
</body>
</html>
