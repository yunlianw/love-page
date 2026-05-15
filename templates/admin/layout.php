<?php
if (!function_exists('h')) {
    function h(?string $s): string {
        return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?=h($pageTitle ?? '后台管理')?> - 恋爱系统</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;background:#f5f5f5;min-height:100vh;display:flex;flex-direction:column}

/* 顶栏 */
.header{background:linear-gradient(135deg,#ff6b8a,#c77dba);color:#fff;padding:12px 20px;display:flex;align-items:center;justify-content:space-between;box-shadow:0 2px 10px rgba(255,107,138,0.3);flex-shrink:0}
.header h1{font-size:1.2rem;font-weight:500;white-space:nowrap}
.header-nav{display:flex;gap:8px;flex-wrap:wrap}
.header-nav a{color:#fff;text-decoration:none;padding:6px 14px;border-radius:20px;background:rgba(255,255,255,0.15);transition:background .2s;font-size:.9rem;white-space:nowrap}
.header-nav a:hover,.header-nav a.on{background:rgba(255,255,255,0.25)}
.menu-btn{display:none;background:none;border:none;color:#fff;font-size:1.5rem;cursor:pointer;padding:4px 8px;line-height:1}

/* 内容区 */
.main{padding:20px;max-width:1200px;margin:0 auto;width:100%;flex:1}
.card{background:#fff;border-radius:12px;padding:20px;margin-bottom:20px;box-shadow:0 2px 8px rgba(0,0,0,0.08)}
.card h2{font-size:1.1rem;color:#333;margin-bottom:15px;padding-bottom:10px;border-bottom:1px solid #eee}
.form-group{margin-bottom:15px}
.form-group label{display:block;font-size:.9rem;color:#666;margin-bottom:6px}
.form-group input,.form-group textarea,.form-group select{width:100%;padding:10px 12px;border:1px solid #ddd;border-radius:8px;font-size:.95rem;font-family:inherit;transition:border-color .2s,box-shadow .2s}
.form-group input:focus,.form-group textarea:focus,.form-group select:focus{outline:none;border-color:#ff6b8a;box-shadow:0 0 0 3px rgba(255,107,138,0.1)}
.form-group textarea{min-height:80px;resize:vertical}
.btn{display:inline-block;padding:10px 24px;border:none;border-radius:8px;cursor:pointer;font-size:.95rem;font-weight:500;text-decoration:none;transition:transform .1s,box-shadow .1s}
.btn:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(0,0,0,0.15)}
.btn-primary{background:linear-gradient(135deg,#ff6b8a,#c77dba);color:#fff}
.btn-success{background:#4caf50;color:#fff}
.btn-danger{background:#f44336;color:#fff}
.btn-secondary{background:#9e9e9e;color:#fff}
.btn-sm{padding:6px 12px;font-size:.85rem}
.table{width:100%;border-collapse:collapse;display:block;overflow-x:auto}
.table th,.table td{padding:12px;text-align:left;border-bottom:1px solid #eee;white-space:nowrap}
.table th{font-weight:500;color:#666;background:#fafafa}
.table tr:hover{background:#fafafa}
.actions{display:flex;gap:8px;align-items:center}
.actions a,.actions button{color:#ff6b8a;text-decoration:none;border:none;background:none;cursor:pointer;font-size:.9rem}
.actions a:hover,.actions button:hover{text-decoration:underline}
.alert{padding:12px 16px;border-radius:8px;margin-bottom:20px;white-space:pre-line}
.alert-success{background:#e8f5e9;color:#2e7d32}
.alert-error{background:#ffebee;color:#c62828}
.stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:15px;margin-bottom:20px}
.stat-card{background:linear-gradient(135deg,#fff5f7,#ffe0e8);padding:15px;border-radius:10px;text-align:center}
.stat-card .num{font-size:2rem;font-weight:700;color:#ff6b8a}
.stat-card .label{font-size:.85rem;color:#666}
.color-tag{display:inline-block;padding:4px 12px;border-radius:15px;font-size:.8rem;font-weight:500}
.color-pink{background:#ffb3c6;color:#e84573}
.color-purple{background:#e1bee7;color:#8e24aa}
.color-gold{background:#f4c87d;color:#f57f17}
.color-green{background:#a5d6a7;color:#2e7d32}
.color-blue{background:#90caf9;color:#1565c0}
.type-badge{font-size:.75rem;padding:2px 8px;border-radius:10px}
.type-left{background:#e3f2fd;color:#1565c0}
.type-right{background:#fce4ec;color:#c2185b}
.type-shared{background:#f3e5f5;color:#7b1fa2}

/* 手机侧边栏 */
.sidebar{display:none;position:fixed;top:0;left:0;width:260px;height:100%;background:linear-gradient(180deg,#ff6b8a,#c77dba);flex-direction:column;padding:50px 15px 20px;gap:4px;z-index:999;overflow-y:auto;box-shadow:4px 0 20px rgba(0,0,0,0.2)}
.sidebar.open{display:flex}
.sidebar a{color:#fff;text-decoration:none;padding:12px 16px;border-radius:10px;font-size:.95rem;transition:background .2s}
.sidebar a:hover,.sidebar a.on{background:rgba(255,255,255,0.2)}
.sidebar-close{position:absolute;top:12px;right:12px;background:none;border:none;color:#fff;font-size:1.5rem;cursor:pointer}
.overlay{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.4);z-index:998}
.overlay.show{display:block}

/* 平板 */
@media(max-width:900px){
  .header-nav a{padding:6px 10px;font-size:.8rem}
}

/* 手机 */
@media(max-width:768px){
  .menu-btn{display:block}
  .header-nav{display:none}
  .header{padding:10px 15px}
  .header h1{font-size:1rem}
  .main{padding:15px}
  .card{padding:15px;border-radius:10px}
  .stats{grid-template-columns:repeat(2,1fr);gap:10px}
  .stat-card{padding:12px}
  .stat-card .num{font-size:1.5rem}
  .form-group input,.form-group textarea,.form-group select{font-size:16px}
}
@media(max-width:400px){
  .stats{grid-template-columns:1fr 1fr}
  .card{padding:12px}
  .btn{padding:10px 16px;font-size:.9rem}
}
</style>
</head>
<body>
<header class="header">
  <div style="display:flex;align-items:center;gap:12px">
    <button class="menu-btn" onclick="toggleMenu()">☰</button>
    <h1>💕 恋爱系统后台</h1>
  </div>
  <nav class="header-nav">
    <a href="index.php" class="<?=$currentPage==='index'?'on':''?>">仪表盘</a>
    <a href="config.php" class="<?=$currentPage==='config'?'on':''?>">基本设置</a>
    <a href="travel.php" class="<?=$currentPage==='travel'?'on':''?>">旅行足迹</a>
    <a href="gallery.php" class="<?=$currentPage==='gallery'?'on':''?>">甜蜜相册</a>
    <a href="hobbies.php" class="<?=$currentPage==='hobbies'?'on':''?>">爱好</a>
    <a href="together.php" class="<?=$currentPage==='together'?'on':''?>">一起做的事</a>
    <a href="countdown.php" class="<?=$currentPage==='countdown'?'on':''?>">纪念日</a>
    <a href="music.php" class="<?=$currentPage==='music'?'on':''?>">音乐</a>
    <a href="generate.php" class="<?=$currentPage==='generate'?'on':''?>">一键生成</a>
    <a href="emoji.php" class="<?=$currentPage==='emoji'?'on':''?>">Emoji库</a>
    <a href="settings.php" class="<?=$currentPage==='settings'?'on':''?>">系统设置</a>
    <a href="logout.php">退出</a>
  </nav>
</header>

<div class="overlay" id="overlay" onclick="toggleMenu()"></div>
<nav class="sidebar" id="sidebar">
  <button class="sidebar-close" onclick="toggleMenu()">✕</button>
  <a href="index.php" class="<?=$currentPage==='index'?'on':''?>" onclick="closeMenu()">🏠 仪表盘</a>
  <a href="config.php" class="<?=$currentPage==='config'?'on':''?>" onclick="closeMenu()">⚙️ 基本设置</a>
  <a href="travel.php" class="<?=$currentPage==='travel'?'on':''?>" onclick="closeMenu()">✈️ 旅行足迹</a>
  <a href="gallery.php" class="<?=$currentPage==='gallery'?'on':''?>" onclick="closeMenu()">📸 甜蜜相册</a>
  <a href="hobbies.php" class="<?=$currentPage==='hobbies'?'on':''?>" onclick="closeMenu()">🎨 爱好</a>
  <a href="together.php" class="<?=$currentPage==='together'?'on':''?>" onclick="closeMenu()">💝 一起做的事</a>
  <a href="countdown.php" class="<?=$currentPage==='countdown'?'on':''?>" onclick="closeMenu()">📅 纪念日</a>
  <a href="music.php" class="<?=$currentPage==='music'?'on':''?>" onclick="closeMenu()">🎵 背景音乐</a>
  <a href="generate.php" class="<?=$currentPage==='generate'?'on':''?>" onclick="closeMenu()">⚡ 一键生成</a>
  <a href="emoji.php" class="<?=$currentPage==='emoji'?'on':''?>" onclick="closeMenu()">😀 Emoji库</a>
  <a href="settings.php" class="<?=$currentPage==='settings'?'on':''?>" onclick="closeMenu()">🔒 系统设置</a>
  <a href="logout.php">🚪 退出登录</a>
</nav>

<main class="main"><?=$content?></main>

<script>
function toggleMenu(){var s=document.getElementById('sidebar'),o=document.getElementById('overlay');s.classList.toggle('open');o.classList.toggle('show');document.body.style.overflow=s.classList.contains('open')?'hidden':''}
function closeMenu(){var s=document.getElementById('sidebar'),o=document.getElementById('overlay');s.classList.remove('open');o.classList.remove('show');document.body.style.overflow=''}
</script>
</body>
</html>