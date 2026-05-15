<?php
/**
 * 通用 CRUD 控制器
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/db.php';

session_start();
if (empty($_SESSION['love_uid'])) { header('Location: login.php'); exit; }

$type = basename($_GET['type'] ?? 'travel');
$allowed = ['travel', 'gallery', 'together'];
if (!in_array($type, $allowed)) $type = 'travel';

$pdo = getDB();
$currentPage = $type;
$msg = '';

$fields = [
    'travel' => ['travel_date' => '日期', 'place' => '地点', 'description' => '描述', 'emoji' => 'Emoji', 'sort_order' => '排序'],
    'gallery' => ['title' => '标题', 'image_url' => '图片URL', 'emoji' => 'Emoji', 'sort_order' => '排序'],
    'together' => ['title' => '标题', 'description' => '描述', 'emoji' => 'Emoji', 'count_label' => '次数标签', 'sort_order' => '排序']
];

$labels = ['travel' => '旅行足迹', 'gallery' => '甜蜜相册', 'together' => '一起做的事'];
$f = $fields[$type];
$label = $labels[$type];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = intval($_POST['id'] ?? 0);

    if ($action === 'add' || $action === 'edit') {
        $values = [];
        $cols = [];
        foreach (array_keys($f) as $col) {
            $val = $_POST[$col] ?? '';
            $cols[] = $col;
            $values[] = $val;
        }

        if ($action === 'add') {
            $sql = "INSERT INTO love_{$type} (" . implode(',', $cols) . ") VALUES (" . implode(',', array_fill(0, count($cols), '?')) . ")";
            $pdo->prepare($sql)->execute($values);
            $msg = '添加成功！';
        } else {
            $set = implode('=?,', $cols) . '=?';
            $values[] = $id;
            $pdo->prepare("UPDATE love_{$type} SET {$set} WHERE id=?")->execute($values);
            $msg = '修改成功！';
        }
    } elseif ($action === 'delete' && $id) {
        $pdo->prepare("DELETE FROM love_{$type} WHERE id=?")->execute([$id]);
        $msg = '删除成功！';
    }
}

$list = $pdo->query("SELECT * FROM love_{$type} ORDER BY sort_order, id")->fetchAll();
$editItem = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM love_{$type} WHERE id=?");
    $stmt->execute([intval($_GET['edit'])]);
    $editItem = $stmt->fetch();
}

ob_start();
if ($msg) echo '<div class="alert alert-success">'.$msg.'</div>';
?>
<div class="card">
  <h2><?=$label?>管理 <small style="color:#999;font-weight:normal">（共<?=count($list)?>条）</small></h2>
  <form method="post">
    <input type="hidden" name="action" value="<?=$editItem?'edit':'add'?>">
    <?php if ($editItem): ?><input type="hidden" name="id" value="<?=$editItem['id']?>"><?php endif ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:15px;margin-bottom:15px">
      <?php foreach ($f as $col => $lbl): ?>
      <div class="form-group">
        <label><?=$lbl?></label>
        <?php if (in_array($col, ['description'])): ?>
          <textarea name="<?=$col?>" style="min-height:60px"><?=h($editItem[$col] ?? '')?></textarea>
        <?php elseif ($col === 'sort_order'): ?>
          <input type="number" name="<?=$col?>" value="<?=h($editItem[$col] ?? 0)?>">
        <?php else: ?>
          <input name="<?=$col?>" value="<?=h($editItem[$col] ?? '')?>">
        <?php endif ?>
      </div>
      <?php endforeach ?>
    </div>
    <button type="submit" class="btn btn-primary btn-sm"><?=$editItem?'保存修改':'添加'?></button>
    <?php if ($editItem): ?><a href="<?=$type?>.php" class="btn btn-secondary btn-sm">取消</a><?php endif ?>
  </form>
</div>

<div class="card">
  <table class="table">
    <thead>
      <tr>
        <?php foreach ($f as $lbl): ?>
        <th><?=$lbl?></th>
        <?php endforeach ?>
        <th width="120">操作</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($list as $item): ?>
      <tr>
        <?php foreach (array_keys($f) as $col): ?>
        <td>
          <?php if ($col === 'emoji'): ?>
            <span style="font-size:1.5rem"><?=h($item[$col])?></span>
          <?php elseif ($col === 'image_url' && $item[$col]): ?>
            <a href="<?=h($item[$col])?>" target="_blank" style="color:#ff6b8a"><?=mb_substr(h($item[$col]),0,30)?>...</a>
          <?php else: ?>
            <?=mb_substr(h($item[$col]),0,30)?>
          <?php endif ?>
        </td>
        <?php endforeach ?>
        <td class="actions">
          <a href="?type=<?=$type?>&edit=<?=$item['id']?>">编辑</a>
          <form method="post" style="display:inline" onsubmit="return confirm('确定删除？')">
            <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$item['id']?>">
            <button type="submit">删除</button>
          </form>
        </td>
      </tr>
      <?php endforeach ?>
      <?php if (empty($list)): ?>
      <tr><td colspan="<?=count($f)+1?>" style="text-align:center;color:#999;padding:30px">暂无数据</td></tr>
      <?php endif ?>
    </tbody>
  </table>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../templates/admin/layout.php';