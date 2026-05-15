<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/Generator.php';

session_start();
if (empty($_SESSION['love_uid'])) { header('Location: login.php'); exit; }

$pdo = getDB();
$currentPage = 'generate';
$error = '';
$stats = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $gen = new LoveGenerator($pdo);
        $stats = $gen->generate();
    } catch (Exception $e) {
        $error = '生成失败：' . $e->getMessage();
    }
}

ob_start();
if ($error) echo '<div class="alert alert-error">'.$error.'</div>';
if ($stats): ?>
<div class="alert alert-success">
  ✅ 生成成功！文件大小 <?=round($stats['file_size']/1024, 1)?>KB
  <br><br>
  <a href="<?=SITE_URL?>/" target="_blank" class="btn btn-primary">查看页面</a>
</div>
<?php endif ?>

<div class="card">
  <h2>一键生成静态页面</h2>
  <p style="margin-bottom:20px;color:#666">
    点击下方按钮，将当前数据库中的配置生成为纯静态HTML页面。<br>
    生成后的文件：/index.html
  </p>
  <form method="post">
    <button type="submit" class="btn btn-success" onclick="this.textContent='正在生成...'">生成页面</button>
  </form>
</div>

<div class="card">
  <h2>当前数据统计</h2>
  <div class="stats">
    <?php
    $counts = [
        'travel' => $pdo->query("SELECT COUNT(*) FROM love_travel")->fetchColumn(),
        'gallery' => $pdo->query("SELECT COUNT(*) FROM love_gallery")->fetchColumn(),
        'hobbies' => $pdo->query("SELECT COUNT(*) FROM love_hobbies")->fetchColumn(),
        'together' => $pdo->query("SELECT COUNT(*) FROM love_together")->fetchColumn(),
    ];
    foreach ($counts as $k => $v): ?>
    <div class="stat-card"><div class="num"><?=$v?></div><div class="label"><?=$k?></div></div>
    <?php endforeach ?>
  </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../templates/admin/layout.php';