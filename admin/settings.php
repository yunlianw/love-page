<?php
/**
 * 后台设置 - 修改密码、修改后台目录
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/db.php';

session_start();
if (empty($_SESSION['love_uid'])) {
    header('Location: login.php');
    exit;
}

$pdo = getDB();
$currentPage = 'settings';
$msg = '';
$msgType = 'err';

// 获取当前管理员信息
$admin = $pdo->query("SELECT * FROM love_users WHERE id=" . intval($_SESSION['love_uid']))->fetch();

// ============ 修改密码 ============
if (isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $oldPwd = $_POST['old_password'] ?? '';
    $newPwd = $_POST['new_password'] ?? '';
    $cfmPwd = $_POST['cfm_password'] ?? '';
    $newUser = trim($_POST['new_username'] ?? $admin['username']);

    if (empty($oldPwd) || empty($newPwd) || empty($cfmPwd)) {
        $msg = '请填写所有字段';
    } elseif (!password_verify($oldPwd, $admin['password'])) {
        $msg = '原密码错误';
    } elseif (mb_strlen($newPwd) < 6) {
        $msg = '新密码至少6个字符';
    } elseif ($newPwd !== $cfmPwd) {
        $msg = '两次输入的新密码不一致';
    } else {
        $hash = password_hash($newPwd, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE love_users SET password=?, username=? WHERE id=?")
            ->execute([$hash, $newUser, $admin['id']]);
        $_SESSION['love_username'] = $newUser;
        $msg = '账户信息修改成功';
        $msgType = 'ok';
        $admin = $pdo->query("SELECT * FROM love_users WHERE id=" . intval($_SESSION['love_uid']))->fetch();
    }
}

// ============ 修改后台目录 ============
if (isset($_POST['action']) && $_POST['action'] === 'change_dir') {
    $newDir = trim($_POST['new_dir'] ?? '');
    $currentDir = $admin['admin_dir'];

    if (empty($newDir)) {
        $msg = '目录名不能为空';
    } elseif (!preg_match('/^[a-zA-Z0-9_-]+$/', $newDir)) {
        $msg = '目录名只允许字母、数字、下划线和横线';
    } elseif ($newDir === $currentDir) {
        $msg = '新目录名与当前相同';
    } elseif (is_dir(ROOT_PATH . '/' . $newDir)) {
        $msg = '该目录名已存在，请换一个';
    } else {
        $oldPath = ROOT_PATH . '/' . $currentDir;
        $newPath = ROOT_PATH . '/' . $newDir;

        // 重命名目录
        if (rename($oldPath, $newPath)) {
            // 更新数据库
            $pdo->prepare("UPDATE love_users SET admin_dir=? WHERE id=?")->execute([$newDir, $admin['id']]);
            $msg = "后台目录已修改为: {$newDir}\n\n新后台地址: " . SITE_URL . "/{$newDir}/login.php\n\n页面即将跳转...";
            $msgType = 'ok';
            // 跳转到新地址
            header("Refresh: 3; url=" . SITE_URL . "/{$newDir}/settings.php");
        } else {
            $msg = '目录重命名失败，请检查权限';
        }
    }
}

ob_start();
if ($msg): ?>
<div class="alert alert-<?=$msgType==='ok'?'success':'error'?>" style="white-space:pre-line"><?=h($msg)?></div>
<?php endif ?>

<div class="card">
  <h2>修改账户信息</h2>
  <form method="post">
    <input type="hidden" name="action" value="change_password">
    <div class="form-group"><label>用户名</label><input name="new_username" value="<?=h($admin['username'])?>" placeholder="登录用户名"></div>
    <div class="form-group"><label>原密码</label><input type="password" name="old_password" required></div>
    <div class="form-group"><label>新密码</label><input type="password" name="new_password" required placeholder="至少6位"></div>
    <div class="form-group"><label>确认新密码</label><input type="password" name="cfm_password" required></div>
    <button type="submit" class="btn btn-primary">保存修改</button>
  </form>
</div>

<div class="card">
  <h2>修改后台目录</h2>
  <p style="margin-bottom:15px;color:#666">
    当前后台目录：<strong style="color:#ff6b8a"><?=h($admin['admin_dir'])?></strong><br>
    后台地址：<strong><?=SITE_URL?>/<?=h($admin['admin_dir'])?>/login.php</strong>
  </p>
  <form method="post" onsubmit="return confirm('⚠️ 确定要修改后台目录吗？修改后需要用新地址访问后台。')">
    <input type="hidden" name="action" value="change_dir">
    <div class="form-group"><label>新后台目录名</label><input name="new_dir" placeholder="例如：admin888" pattern="[a-zA-Z0-9_-]+" required></div>
    <button type="submit" class="btn btn-danger">确认修改</button>
  </form>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../templates/admin/layout.php';