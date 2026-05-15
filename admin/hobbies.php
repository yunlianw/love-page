<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/db.php';

session_start();
if (empty($_SESSION['love_uid'])) { header('Location: login.php'); exit; }

$pdo = getDB();
$currentPage = 'hobbies';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = intval($_POST['id'] ?? 0);

    if ($action === 'add' || $action === 'edit') {
        $type = $_POST['type'] ?? 'shared';
        $content = $_POST['content'] ?? '';
        $color = $_POST['color'] ?? 'pink';
        $sort_order = intval($_POST['sort_order'] ?? 0);

        if ($action === 'add') {
            $pdo->prepare("INSERT INTO love_hobbies (type, content, color, sort_order) VALUES (?, ?, ?, ?)")->execute([$type, $content, $color, $sort_order]);
            $msg = '添加成功！';
        } else {
            $pdo->prepare("UPDATE love_hobbies SET type=?, content=?, color=?, sort_order=? WHERE id=?")->execute([$type, $content, $color, $sort_order, $id]);
            $msg = '修改成功！';
        }
    } elseif ($action === 'delete' && $id) {
        $pdo->prepare("DELETE FROM love_hobbies WHERE id=?")->execute([$id]);
        $msg = '删除成功！';
    }
}

$list = $pdo->query("SELECT * FROM love_hobbies ORDER BY type, sort_order, id")->fetchAll();
$editItem = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM love_hobbies WHERE id=?");
    $stmt->execute([intval($_GET['edit'])]);
    $editItem = $stmt->fetch();
}

ob_start();
if ($msg) echo '<div class="alert alert-success">'.$msg.'</div>';
?>
<div class="card">
  <h2>爱好管理</h2>
  <form method="post">
    <input type="hidden" name="action" value="<?=$editItem?'edit':'add'?>">
    <?php if ($editItem): ?><input type="hidden" name="id" value="<?=$editItem['id']?>"><?php endif ?>
    <div style="display:grid;grid-template-columns:100px 200px 100px 100px;gap:15px;margin-bottom:15px">
      <div class="form-group"><label>类型</label><select name="type">
        <option value="left" <?=$editItem && $editItem['type']=='left'?'selected':''?>>🧑 TA</option>
        <option value="right" <?=$editItem && $editItem['type']=='right'?'selected':''?>>👩 TA</option>
        <option value="shared" <?=$editItem && $editItem['type']=='shared'?'selected':''?>>💕 共同</option>
      </select></div>
      <div class="form-group"><label>内容</label><input name="content" value="<?=h($editItem['content'] ?? '')?>" placeholder="例如：🎮 游戏"></div>
      <div class="form-group"><label>颜色</label><select name="color">
        <option value="pink" <?=$editItem && $editItem['color']=='pink'?'selected':''?>>粉色</option>
        <option value="purple" <?=$editItem && $editItem['color']=='purple'?'selected':''?>>紫色</option>
        <option value="gold" <?=$editItem && $editItem['color']=='gold'?'selected':''?>>金色</option>
        <option value="green" <?=$editItem && $editItem['color']=='green'?'selected':''?>>绿色</option>
        <option value="blue" <?=$editItem && $editItem['color']=='blue'?'selected':''?>>蓝色</option>
      </select></div>
      <div class="form-group"><label>排序</label><input type="number" name="sort_order" value="<?=h($editItem['sort_order'] ?? 0)?>"></div>
    </div>
    <button type="submit" class="btn btn-primary btn-sm"><?=$editItem?'保存':'添加'?></button>
    <?php if ($editItem): ?><a href="hobbies.php" class="btn btn-secondary btn-sm">取消</a><?php endif ?>
  </form>
</div>
<div class="card">
  <table class="table">
    <thead><tr><th>类型</th><th>内容</th><th>颜色</th><th>排序</th><th>操作</th></tr></thead>
    <tbody>
      <?php foreach ($list as $item): ?>
      <tr>
        <td><span class="type-badge type-<?=h($item['type'])?>"><?=$item['type']?></span></td>
        <td><?=h($item['content'])?></td>
        <td><span class="color-tag color-<?=h($item['color'])?>"><?=$item['color']?></span></td>
        <td><?=h($item['sort_order'])?></td>
        <td class="actions">
          <a href="?edit=<?=$item['id']?>">编辑</a>
          <form method="post" style="display:inline" onsubmit="return confirm('确定删除？')">
            <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$item['id']?>">
            <button type="submit">删除</button>
          </form>
        </td>
      </tr>
      <?php endforeach ?>
    </tbody>
  </table>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../templates/admin/layout.php';