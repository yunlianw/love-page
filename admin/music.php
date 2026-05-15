<?php
/**
 * 背景音乐管理
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/db.php';

session_start();
if (empty($_SESSION['love_uid'])) { header('Location: login.php'); exit; }
$pdo = getDB();
$currentPage = 'music';
$msg = '';

// 上传音频
function uploadAudio($file) {
    $allowed = ['mp3', 'ogg', 'wav', 'm4a', 'aac'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) return ['error' => '只支持 mp3/ogg/wav/m4a/aac'];
    if ($file['size'] > 20 * 1024 * 1024) return ['error' => '不能超过20MB'];
    
    $dir = __DIR__ . '/../assets/music';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    
    $filename = 'music_' . time() . '_' . rand(100,999) . '.' . $ext;
    if (move_uploaded_file($file['tmp_name'], $dir . '/' . $filename)) {
        return ['url' => SITE_URL . '/assets/music/' . $filename];
    }
    return ['error' => '上传失败'];
}

// 处理请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = intval($_POST['id'] ?? 0);
    
    if ($action === 'add' || $action === 'edit') {
        $audio_url = trim($_POST['audio_url'] ?? '');
        
        // 优先使用上传文件
        if (!empty($_FILES['audio_file']['tmp_name']) && $_FILES['audio_file']['error'] === 0) {
            $result = uploadAudio($_FILES['audio_file']);
            if (isset($result['url'])) {
                $audio_url = $result['url'];
            } else {
                $msg = $result['error'];
            }
        }
        
        if ($audio_url && !$msg) {
            $data = [
                $_POST['title'] ?? '未知歌曲',
                $_POST['artist'] ?? '',
                $audio_url,
                $_POST['cover_url'] ?? '',
                intval($_POST['sort_order'] ?? 0),
                intval($_POST['is_active'] ?? 1)
            ];
            
            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO love_music (title,artist,audio_url,cover_url,sort_order,is_active) VALUES (?,?,?,?,?,?)");
            } else {
                $data[] = $id;
                $stmt = $pdo->prepare("UPDATE love_music SET title=?,artist=?,audio_url=?,cover_url=?,sort_order=?,is_active=? WHERE id=?");
            }
            $stmt->execute($data);
            $msg = $action === 'add' ? '添加成功！' : '修改成功！';
        }
    }
    
    if ($action === 'delete' && $id) {
        $pdo->prepare("DELETE FROM love_music WHERE id=?")->execute([$id]);
        $msg = '删除成功！';
    }
    
    if ($action === 'batch') {
        $urls = preg_split('/[\r\n]+/', $_POST['batch_urls'] ?? '');
        $count = 0;
        foreach ($urls as $url) {
            $url = trim($url);
            if ($url && preg_match('/\.(mp3|ogg|wav|m4a|aac)/i', $url)) {
                $pdo->prepare("INSERT INTO love_music (title, audio_url, sort_order) VALUES (?, ?, ?)")
                    ->execute(['歌曲' . (++$count), $url, $count]);
            }
        }
        $msg = "批量添加 {$count} 首";
    }
    
    if ($action === 'toggle_all') {
        $val = intval($_POST['toggle_val'] ?? 1);
        $pdo->exec("UPDATE love_music SET is_active=$val");
        $msg = $val ? '全部启用' : '全部禁用';
    }
}

$list = $pdo->query("SELECT * FROM love_music ORDER BY sort_order, id")->fetchAll();

// 编辑数据
$editItem = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM love_music WHERE id=?");
    $stmt->execute([intval($_GET['edit'])]);
    $editItem = $stmt->fetch();
}

ob_start();
if ($msg) echo '<div class="alert alert-success">'.htmlspecialchars($msg).'</div>';
?>
<div class="card">
  <h2>🎵 背景音乐管理 <small style="color:#999;font-weight:normal">（共<?=count($list)?>首）</small></h2>
  <p style="font-size:.85rem;color:#888;margin-bottom:15px">添加歌曲后，在「基本设置」里开启「音乐播放器」显示，并勾选「自动播放」即可前台播放</p>
  
  <form method="post" enctype="multipart/form-data" id="musicForm">
    <input type="hidden" name="action" value="<?=$editItem?'edit':'add'?>">
    <?php if ($editItem): ?><input type="hidden" name="id" value="<?=$editItem['id']?>"><?php endif ?>
    
    <div style="display:grid;grid-template-columns:1fr 1fr 2fr;gap:15px;margin-bottom:15px">
      <div class="form-group">
        <label>歌曲名称</label>
        <input name="title" value="<?=htmlspecialchars($editItem['title'] ?? '')?>" placeholder="如：告白气球">
      </div>
      <div class="form-group">
        <label>歌手</label>
        <input name="artist" value="<?=htmlspecialchars($editItem['artist'] ?? '')?>" placeholder="如：周杰伦">
      </div>
      <div class="form-group">
        <label>音频地址（或上传）</label>
        <input name="audio_url" value="<?=htmlspecialchars($editItem['audio_url'] ?? '')?>" placeholder="MP3链接">
      </div>
    </div>
    
    <div style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:15px;margin-bottom:15px">
      <div class="form-group">
        <label>上传音频文件（mp3/ogg/wav/m4a/aac，≤20MB）</label>
        <input type="file" name="audio_file" accept=".mp3,.ogg,.wav,.m4a,.aac,audio/*" onchange="handleAudioUpload(this)">
        <span id="audioUploadHint" style="font-size:.8rem;color:#888;display:none">🎵 文件已选择，点击保存按钮上传</span>
      </div>
      <div class="form-group">
        <label>封面图片(可选)</label>
        <input name="cover_url" value="<?=htmlspecialchars($editItem['cover_url'] ?? '')?>" placeholder="图片URL">
      </div>
      <div class="form-group">
        <label>排序</label>
        <input type="number" name="sort_order" value="<?=htmlspecialchars($editItem['sort_order'] ?? 0)?>">
      </div>
    </div>
    
    <div style="margin-bottom:15px">
      <label style="display:flex;align-items:center;gap:6px">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" <?=($editItem['is_active'] ?? 1)?'checked':''?>> 启用播放
      </label>
    </div>
    
    <button type="submit" class="btn btn-primary"><?=$editItem?'保存修改':'添加歌曲'?></button>
    <?php if ($editItem): ?><a href="music.php" class="btn btn-secondary">取消</a><?php endif ?>
  </form>
</div>

<div class="card">
  <h2>📦 批量添加</h2>
  <p style="font-size:.85rem;color:#888;margin-bottom:10px">粘贴多个音频URL，每行一个（识别 .mp3/.ogg/.wav/.m4a/.aac 结尾的链接）</p>
  <form method="post">
    <input type="hidden" name="action" value="batch">
    <textarea name="batch_urls" style="width:100%;height:100px;resize:vertical" placeholder="https://example.com/song1.mp3
https://example.com/song2.mp3"></textarea>
    <div style="margin-top:10px"><button type="submit" class="btn btn-success btn-sm">批量添加</button></div>
  </form>
</div>

<?php if (!empty($list)): ?>
<div class="card">
  <h2>🎵 歌曲列表</h2>
  <div style="margin-bottom:10px">
    <form method="post" style="display:inline">
      <input type="hidden" name="action" value="toggle_all">
      <input type="hidden" name="toggle_val" value="1">
      <button type="submit" class="btn btn-sm" style="padding:4px 12px;font-size:.8rem">全部启用</button>
    </form>
    <form method="post" style="display:inline">
      <input type="hidden" name="action" value="toggle_all">
      <input type="hidden" name="toggle_val" value="0">
      <button type="submit" class="btn btn-sm btn-secondary" style="padding:4px 12px;font-size:.8rem">全部禁用</button>
    </form>
  </div>
  <table class="table">
    <thead><tr><th>歌曲</th><th>歌手</th><th>状态</th><th>操作</th></tr></thead>
    <tbody>
      <?php foreach ($list as $item): ?>
      <tr>
        <td><?=htmlspecialchars($item['title'])?></td>
        <td><?=htmlspecialchars($item['artist'])?></td>
        <td><?=$item['is_active']?'✅':'❌'?></td>
        <td class="actions">
          <a href="<?=$item['audio_url']?>" target="_blank" style="color:#888">▶</a>
          <a href="?edit=<?=$item['id']?>">编辑</a>
          <form method="post" style="display:inline" onsubmit="return confirm('确定删除？')">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?=$item['id']?>">
            <button type="submit">删除</button>
          </form>
        </td>
      </tr>
      <?php endforeach ?>
    </tbody>
  </table>
</div>
<?php else: ?>
<div class="card"><p style="text-align:center;color:#999;padding:30px">暂无歌曲，请添加</p></div>
<?php endif ?>
<?php
$content = ob_get_clean();
require __DIR__ . '/../templates/admin/layout.php';
