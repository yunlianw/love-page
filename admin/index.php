<?php
/**
 * 仪表盘 - 使用教程 + 备忘录
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/db.php';

session_start();
if (empty($_SESSION['love_uid'])) { header('Location: login.php'); exit; }
$pdo = getDB();
$currentPage = 'index';
$msg = '';

// 保存备忘录
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notes'])) {
    $stmt = $pdo->prepare("UPDATE love_notes SET content=? WHERE id=1");
    $stmt->execute([$_POST['notes'] ?? '']);
    $msg = '备忘录已保存！';
}

// 获取统计数据
$stats = [
    'travel' => $pdo->query("SELECT COUNT(*) FROM love_travel")->fetchColumn(),
    'gallery' => $pdo->query("SELECT COUNT(*) FROM love_gallery")->fetchColumn(),
    'hobbies' => $pdo->query("SELECT COUNT(*) FROM love_hobbies")->fetchColumn(),
    'together' => $pdo->query("SELECT COUNT(*) FROM love_together")->fetchColumn(),
    'countdown' => $pdo->query("SELECT COUNT(*) FROM love_countdown")->fetchColumn(),
    'music' => $pdo->query("SELECT COUNT(*) FROM love_music WHERE is_active=1")->fetchColumn(),
];

$config = $pdo->query("SELECT * FROM love_config LIMIT 1")->fetch();
$notes = $pdo->query("SELECT content FROM love_notes LIMIT 1")->fetchColumn();

ob_start();
if ($msg) echo '<div class="alert alert-success">'.htmlspecialchars($msg).'</div>';
?>

<!-- 统计卡片 -->
<div class="stats">
  <div class="stat-card"><div class="num"><?=$stats['travel']?></div><div class="label">旅行足迹</div></div>
  <div class="stat-card"><div class="num"><?=$stats['gallery']?></div><div class="label">甜蜜相册</div></div>
  <div class="stat-card"><div class="num"><?=$stats['hobbies']?></div><div class="label">爱好标签</div></div>
  <div class="stat-card"><div class="num"><?=$stats['together']?></div><div class="label">一起做的事</div></div>
  <div class="stat-card"><div class="num"><?=$stats['countdown']?></div><div class="label">纪念日</div></div>
  <div class="stat-card"><div class="num"><?=$stats['music']?></div><div class="label">背景音乐</div></div>
</div>

<!-- 使用教程 -->
<div class="card">
  <h2>📖 使用教程</h2>
  <div style="background:#fff5f7;padding:20px;border-radius:10px;margin-bottom:15px">
    <p style="font-size:1rem;font-weight:500;color:#ff6b8a;margin-bottom:15px">⚡ 快速开始</p>
    <ol style="line-height:2;color:#4a3347">
      <li><strong>基本设置</strong> - 设置页面标题、副标题、头像、在一起日期</li>
      <li><strong>添加内容</strong> - 在各栏目管理页添加旅行、相册、爱好等内容</li>
      <li><strong>背景音乐</strong> - 上传或添加音乐链接，前台自动播放</li>
      <li><strong>一键生成</strong> - ⚠️ <span style="color:#ff6b8a;font-weight:500">点击「一键生成」更新首页</span>（每次修改后都要点）</li>
      <li><strong>访问前台</strong> - 打开网站查看效果</li>
    </ol>
    <p style="font-size:.9rem;color:#888;margin-top:15px">💡 提示：栏目开关可以控制前台显示/隐藏，关闭的栏目生成页面时不显示</p>
  </div>
</div>

<!-- 备忘录 -->
<div class="card">
  <h2>📝 备忘录</h2>
  <p style="font-size:.85rem;color:#888;margin-bottom:10px">记录重要事项，保存后随时查看</p>
  <form method="post">
    <textarea name="notes" style="width:100%;min-height:200px;resize:vertical;font-size:.95rem;line-height:1.8;padding:15px;border:2px solid #ffb3c6;border-radius:10px" placeholder="在这里记录备忘内容..."><?=htmlspecialchars($notes)?></textarea>
    <div style="margin-top:10px"><button type="submit" class="btn btn-primary">保存备忘录</button></div>
  </form>
</div>

<!-- 快捷操作 -->
<div class="card">
  <h2>⚡ 快捷操作</h2>
  <div style="display:flex;gap:10px;flex-wrap:wrap">
    <a href="config.php" class="btn btn-primary btn-sm">⚙️ 基本设置</a>
    <a href="travel.php" class="btn btn-sm" style="background:#e3f2fd;color:#1565c0">✈️ 旅行足迹</a>
    <a href="gallery.php" class="btn btn-sm" style="background:#fce4ec;color:#c2185b">📸 甜蜜相册</a>
    <a href="music.php" class="btn btn-sm" style="background:#f3e5f5;color:#7b1fa2">🎵 背景音乐</a>
    <a href="countdown.php" class="btn btn-sm" style="background:#fff8e1;color:#f57f17">📅 纪念日</a>
    <a href="generate.php" class="btn btn-success btn-sm">⚡ 一键生成</a>
  </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../templates/admin/layout.php';