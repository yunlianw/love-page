<?php
/**
 * 基本设置（含栏目开关）
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/db.php';

session_start();
if (empty($_SESSION['love_uid'])) { header('Location: login.php'); exit; }
$pdo = getDB();
$currentPage = 'config';
$msg = '';

// 头像上传
function uploadAvatar($file, $name) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) return ['error' => '只支持 jpg/png/gif/webp'];
    if ($file['size'] > 2 * 1024 * 1024) return ['error' => '图片不能超过2MB'];
    $filename = $name . '_' . time() . '.' . $ext;
    $path = __DIR__ . '/../assets/avatars/' . $filename;
    if (move_uploaded_file($file['tmp_name'], $path)) return ['url' => SITE_URL . '/assets/avatars/' . $filename];
    return ['error' => '上传失败'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $config = $pdo->query("SELECT * FROM love_config LIMIT 1")->fetch();

    // 根据是哪个表单提交，分别处理
    $saveType = $_POST['save_type'] ?? 'config';

    if ($saveType === 'sections') {
        // 只保存栏目开关（不影响其他字段）
        $stmt = $pdo->prepare("UPDATE love_config SET 
            show_travel=?, show_gallery=?, show_hobbies=?, show_together=?,
            show_countdown=?, show_location=?, show_music_player=?, music_autoplay=?, music_hide_hours=?, page_lock=?, page_password=?
            WHERE id=1");
        $stmt->execute([
            intval($_POST['show_travel'] ?? 1),
            intval($_POST['show_gallery'] ?? 1),
            intval($_POST['show_hobbies'] ?? 1),
            intval($_POST['show_together'] ?? 1),
            intval($_POST['show_countdown'] ?? 1),
            intval($_POST['show_location'] ?? 1),
            intval($_POST['show_music_player'] ?? 1),
            intval($_POST['music_autoplay'] ?? 1),
            intval($_POST['music_hide_hours'] ?? 24),
            intval($_POST['page_lock'] ?? 0),
            trim($_POST['page_password'] ?? '')
        ]);
        $msg = '栏目设置已保存！';
    } else {
        // 保存基本设置
        $avatar_left = $config['avatar_left'] ?? '';
        $avatar_right = $config['avatar_right'] ?? '';
        $leftUploaded = false;
        $rightUploaded = false;

        if (!empty($_FILES['avatar_left_file']['name'])) {
            $result = uploadAvatar($_FILES['avatar_left_file'], 'left');
            if (isset($result['url'])) { $avatar_left = $result['url']; $leftUploaded = true; }
            elseif (isset($result['error'])) $msg = $result['error'];
        }
        if (!empty($_FILES['avatar_right_file']['name'])) {
            $result = uploadAvatar($_FILES['avatar_right_file'], 'right');
            if (isset($result['url'])) { $avatar_right = $result['url']; $rightUploaded = true; }
            elseif (isset($result['error'])) $msg = $msg ?: $result['error'];
        }
        // URL文本框优先级：仅在没有上传新文件时使用（允许清除头像）
        if (!$leftUploaded && isset($_POST['avatar_left'])) $avatar_left = trim($_POST['avatar_left']);
        if (!$rightUploaded && isset($_POST['avatar_right'])) $avatar_right = trim($_POST['avatar_right']);

        $stmt = $pdo->prepare("UPDATE love_config SET 
            title=?, subtitle=?, default_date=?, emoji_left=?, avatar_left=?,
            emoji_right=?, avatar_right=?, footer_text=?
            WHERE id=1");
        $stmt->execute([
            trim($_POST['title'] ?? '我们的小世界'),
            trim($_POST['subtitle'] ?? ''),
            $_POST['default_date'] ?? '',
            $_POST['emoji_left'] ?? '🧑',
            $avatar_left,
            $_POST['emoji_right'] ?? '👩',
            $avatar_right,
            trim($_POST['footer_text'] ?? '')
        ]);
        $msg = $msg ?: '保存成功！';
    }
}

$config = $pdo->query("SELECT * FROM love_config LIMIT 1")->fetch();

ob_start();
if ($msg) echo '<div class="alert alert-success">'.htmlspecialchars($msg).'</div>';
?>
<div class="card">
  <h2>基本设置</h2>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="save_type" value="config">
    <div class="form-group"><label>页面标题</label><input name="title" value="<?=htmlspecialchars($config['title'] ?? '')?>"></div>
    <div class="form-group"><label>副标题</label><input name="subtitle" value="<?=htmlspecialchars($config['subtitle'] ?? '')?>"></div>
    <div class="form-group"><label>在一起日期时间</label><p style="font-size:.8rem;color:#888;margin-bottom:5px">精确到分钟，显示在首页计时器</p><input type="datetime-local" name="default_date" value="<?=htmlspecialchars(substr($config['default_date'] ?? '', 0, 16))?>"></div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin:20px 0">
      <div class="form-group">
        <label>左侧头像</label>
        <?php if(!empty($config['avatar_left'])): ?>
          <div style="margin:10px 0"><img id="avatar_left_preview" src="<?=htmlspecialchars($config['avatar_left'])?>" style="width:80px;height:80px;border-radius:50%;object-fit:cover;"></div>
          <button type="button" onclick="clearAvatar('left')" style="font-size:.8rem;color:#ff6b8a;background:none;border:1px solid #ff6b8a;padding:3px 8px;border-radius:4px;cursor:pointer">清除头像</button>
        <?php endif; ?>
        <input type="file" name="avatar_left_file" accept="image/*" style="margin-bottom:5px">
        <input name="avatar_left" placeholder="或输入图片URL" value="<?=htmlspecialchars($config['avatar_left'] ?? '')?>" style="font-size:.85rem">
        <div style="font-size:.8rem;color:#888;margin-top:5px">Emoji备用：<input name="emoji_left" value="<?=htmlspecialchars($config['emoji_left'] ?? '')?>" style="width:50px;font-size:1.2rem;text-align:center"></div>
      </div>
      <div class="form-group">
        <label>右侧头像</label>
        <?php if(!empty($config['avatar_right'])): ?>
          <div style="margin:10px 0"><img id="avatar_right_preview" src="<?=htmlspecialchars($config['avatar_right'])?>" style="width:80px;height:80px;border-radius:50%;object-fit:cover;"></div>
          <button type="button" onclick="clearAvatar('right')" style="font-size:.8rem;color:#ff6b8a;background:none;border:1px solid #ff6b8a;padding:3px 8px;border-radius:4px;cursor:pointer">清除头像</button>
        <?php endif; ?>
        <input type="file" name="avatar_right_file" accept="image/*" style="margin-bottom:5px">
        <input name="avatar_right" placeholder="或输入图片URL" value="<?=htmlspecialchars($config['avatar_right'] ?? '')?>" style="font-size:.85rem">
        <div style="font-size:.8rem;color:#888;margin-top:5px">Emoji备用：<input name="emoji_right" value="<?=htmlspecialchars($config['emoji_right'] ?? '')?>" style="width:50px;font-size:1.2rem;text-align:center"></div>
      </div>
    </div>

    <div class="form-group"><label>页脚文字</label><input name="footer_text" value="<?=htmlspecialchars($config['footer_text'] ?? '')?>"></div>
    <button type="submit" class="btn btn-primary">保存设置</button>
  </form>
</div>

<div class="card">
  <h2>📐 栏目显示设置</h2>
  <p style="font-size:.85rem;color:#888;margin-bottom:15px">关闭的栏目生成页面时不会显示</p>
  <form method="post">
    <input type="hidden" name="save_type" value="sections">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px">
      <?php
      $labels = [
        'show_travel'=>'✈️ 旅行足迹',
        'show_gallery'=>'📸 甜蜜相册',
        'show_hobbies'=>'🎨 爱好',
        'show_together'=>'💝 一起做的事',
        'show_countdown'=>'📅 纪念日倒计时',
        'show_location'=>'📍 定位地图',
        'show_music_player'=>'🎵 音乐播放器'
      ];
      foreach ($labels as $field => $label): ?>
      <label style="display:flex;align-items:center;gap:8px;padding:10px;background:#fafafa;border-radius:8px;cursor:pointer">
        <input type="hidden" name="<?=$field?>" value="0">
        <input type="checkbox" name="<?=$field?>" value="1" <?=($config[$field] ?? 1) ? 'checked' : ''?>>
        <span><?=$label?></span>
      </label>
      <?php endforeach ?>
    </div>
    
    <div style="margin-top:15px;padding:15px;background:#fff5f7;border-radius:8px">
      <label style="display:flex;align-items:center;gap:8px">
        <input type="hidden" name="music_autoplay" value="0">
        <input type="checkbox" name="music_autoplay" value="1" <?=($config['music_autoplay'] ?? 1) ? 'checked' : ''?>>
        <span>🎵 打开页面自动播放音乐</span>
      </label>
      <p style="font-size:.8rem;color:#888;margin-top:5px">关闭后用户需要手动点击播放按钮</p>
    </div>
    
    <div style="margin-top:15px;padding:15px;background:#fff5f7;border-radius:8px">
      <label style="display:flex;align-items:center;gap:8px">
        <span>🔇 关闭播放器后隐藏</span>
        <input type="number" name="music_hide_hours" value="<?=intval($config['music_hide_hours'] ?? 24)?>" min="1" max="720" style="width:60px;padding:4px 8px;border:1px solid #ddd;border-radius:4px">
        <span>小时</span>
      </label>
      <p style="font-size:.8rem;color:#888;margin-top:5px">用户关闭播放器后多久不再显示（1-720小时）</p>
    </div>
    
    <div style="margin-top:20px;padding:15px;background:#fff5f7;border-radius:8px;border:2px solid #ff6b8a">
      <h3 style="color:#ff6b8a;margin-bottom:10px">🔒 页面加密保护</h3>
      <label style="display:flex;align-items:center;gap:8px">
        <input type="hidden" name="page_lock" value="0">
        <input type="checkbox" name="page_lock" value="1" <?=($config['page_lock'] ?? 0) ? 'checked' : ''?>>
        <span>开启页面加密（访问需要输入密码）</span>
      </label>
      <div style="margin-top:10px">
        <label>访问密码</label>
        <input type="text" name="page_password" value="<?=htmlspecialchars($config['page_password'] ?? '')?>" placeholder="支持数字、英文、中文" autocomplete="new-password" autocorrect="off" autocapitalize="off" style="width:260px;padding:8px 12px;border:1px solid #ddd;border-radius:6px;font-size:1rem">
      </div>
      <p style="font-size:.8rem;color:#888;margin-top:8px">开启后，用户访问首页需要输入正确密码才能查看内容。建议设置复杂密码。</p>
    </div>
    
    <div style="margin-top:15px"><button type="submit" class="btn btn-primary btn-sm">保存栏目设置</button></div>
  </form>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../templates/admin/layout.php';
