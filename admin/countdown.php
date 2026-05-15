<?php
/**
 * 纪念日倒计时 & 定位地图管理
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/db.php';

session_start();
if (empty($_SESSION['love_uid'])) { header('Location: login.php'); exit; }
$pdo = getDB();
$currentPage = 'countdown';
$msg = '';

// ============ 倒计时 CRUD ============
if (isset($_POST['cd_action'])) {
    $action = $_POST['cd_action'];
    $id = intval($_POST['cd_id'] ?? 0);

    if ($action === 'add' || $action === 'edit') {
        $stmt = $pdo->prepare($action === 'add'
            ? "INSERT INTO love_countdown (name,target_date,description,emoji,bg_color,is_active,sort_order) VALUES (?,?,?,?,?,?,?)"
            : "UPDATE love_countdown SET name=?,target_date=?,description=?,emoji=?,bg_color=?,is_active=?,sort_order=? WHERE id=?");
        $params = [
            $_POST['cd_name'] ?? '纪念日',
            $_POST['cd_date'] ?? '',
            $_POST['cd_desc'] ?? '',
            $_POST['cd_emoji'] ?? '🎉',
            $_POST['cd_color'] ?? 'pink',
            intval($_POST['cd_active'] ?? 1)
        ];
        $params[] = intval($_POST['cd_sort'] ?? 0);
        if ($action === 'edit') $params[] = $id;
        $stmt->execute($params);
        $msg = $action === 'add' ? '倒计时添加成功！' : '倒计时修改成功！';
    } elseif ($action === 'delete' && $id) {
        $pdo->prepare("DELETE FROM love_countdown WHERE id=?")->execute([$id]);
        $msg = '倒计时删除成功！';
    }
}

// ============ 地图 CRUD ============
if (isset($_POST['loc_action'])) {
    $action = $_POST['loc_action'];
    $id = intval($_POST['loc_id'] ?? 0);

    if ($action === 'add' || $action === 'edit') {
        $stmt = $pdo->prepare($action === 'add'
            ? "INSERT INTO love_location (name,address,lat,lng,map_type,is_show,sort_order) VALUES (?,?,?,?,?,?,?)"
            : "UPDATE love_location SET name=?,address=?,lat=?,lng=?,map_type=?,is_show=?,sort_order=? WHERE id=?");
        $params = [
            $_POST['loc_name'] ?? '',
            $_POST['loc_address'] ?? '',
            floatval($_POST['loc_lat'] ?? 0),
            floatval($_POST['loc_lng'] ?? 0),
            $_POST['loc_map_type'] ?? 'baidu',
            intval($_POST['loc_show'] ?? 1)
        ];
        $params[] = intval($_POST['loc_sort'] ?? 0);
        if ($action === 'edit') $params[] = $id;
        $stmt->execute($params);
        $msg = $msg ?: ($action === 'add' ? '地点添加成功！' : '地点修改成功！');
    } elseif ($action === 'delete' && $id) {
        $pdo->prepare("DELETE FROM love_location WHERE id=?")->execute([$id]);
        $msg = $msg ?: '地点删除成功！';
    }
}

// 查询数据
$countdownList = $pdo->query("SELECT *, DATEDIFF(target_date, CURDATE()) AS days_left FROM love_countdown ORDER BY sort_order, id")->fetchAll();
$locationList = $pdo->query("SELECT * FROM love_location ORDER BY sort_order, id")->fetchAll();

// 编辑中的数据
$editCD = null;
if (isset($_GET['edit_cd'])) {
    $stmt = $pdo->prepare("SELECT * FROM love_countdown WHERE id=?");
    $stmt->execute([intval($_GET['edit_cd'])]);
    $editCD = $stmt->fetch();
}
$editLoc = null;
if (isset($_GET['edit_loc'])) {
    $stmt = $pdo->prepare("SELECT * FROM love_location WHERE id=?");
    $stmt->execute([intval($_GET['edit_loc'])]);
    $editLoc = $stmt->fetch();
}

ob_start();
if ($msg) echo '<div class="alert alert-success">'.htmlspecialchars($msg).'</div>';
?>

<!-- ============ 纪念日倒计时管理 ============ -->
<div class="card">
  <h2>📅 纪念日倒计时 <small style="color:#999;font-weight:normal">（共<?=count($countdownList)?>条）</small></h2>
  <form method="post">
    <input type="hidden" name="cd_action" value="<?=$editCD?'edit':'add'?>">
    <?php if ($editCD): ?><input type="hidden" name="cd_id" value="<?=$editCD['id']?>"><?php endif ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:15px;margin-bottom:15px">
      <div class="form-group">
        <label>倒计时名称</label>
        <input name="cd_name" value="<?=h($editCD['name'] ?? '结婚日')?>" placeholder="如：结婚日、婚礼日">
      </div>
      <div class="form-group">
        <label>目标日期</label>
        <input type="date" name="cd_date" value="<?=h($editCD['target_date'] ?? '')?>">
      </div>
      <div class="form-group">
        <label>描述</label>
        <input name="cd_desc" value="<?=h($editCD['description'] ?? '')?>" placeholder="如：我们要结婚啦！">
      </div>
      <div class="form-group">
        <label>图标</label>
        <input name="cd_emoji" value="<?=h($editCD['emoji'] ?? '🎉')?>" style="max-width:80px;font-size:1.3rem">
      </div>
      <div class="form-group">
        <label>卡片颜色</label>
        <select name="cd_color">
          <option value="pink" <?=($editCD['bg_color']??'')==='pink'?'selected':''?>>粉色</option>
          <option value="purple" <?=($editCD['bg_color']??'')==='purple'?'selected':''?>>紫色</option>
          <option value="gold" <?=($editCD['bg_color']??'')==='gold'?'selected':''?>>金色</option>
          <option value="blue" <?=($editCD['bg_color']??'')==='blue'?'selected':''?>>蓝色</option>
        </select>
      </div>
      <div class="form-group">
        <label>排序</label>
        <input type="number" name="cd_sort" value="<?=h($editCD['sort_order'] ?? 0)?>">
      </div>
      <div class="form-group">
        <label>启用</label>
        <label style="display:flex;align-items:center;gap:6px;margin-top:5px">
          <input type="checkbox" name="cd_active" value="1" <?=empty($editCD) || ($editCD['is_active']??1) ? 'checked' : ''?>> 显示
        </label>
      </div>
    </div>
    <button type="submit" class="btn btn-primary btn-sm"><?=$editCD?'保存修改':'添加倒计时'?></button>
    <?php if ($editCD): ?><a href="countdown.php" class="btn btn-secondary btn-sm">取消</a><?php endif ?>
  </form>
</div>

<?php if (!empty($countdownList)): ?>
<div class="card">
  <table class="table">
    <thead>
      <tr><th>图标</th><th>名称</th><th>目标日期</th><th>剩余/已过</th><th>颜色</th><th>状态</th><th>操作</th></tr>
    </thead>
    <tbody>
      <?php foreach ($countdownList as $item): ?>
      <tr>
        <td><span style="font-size:1.5rem"><?=h($item['emoji'])?></span></td>
        <td><?=h($item['name'])?></td>
        <td><?=h($item['target_date'])?></td>
        <td>
          <?php if ($item['days_left'] > 0): ?>
            <span style="color:#ff6b8a">还有 <?=$item['days_left']?> 天</span>
          <?php elseif ($item['days_left'] == 0): ?>
            <span style="color:#e84573;font-weight:bold">就是今天！🎉</span>
          <?php else: ?>
            <span style="color:#999">已过 <?=abs($item['days_left'])?> 天</span>
          <?php endif ?>
        </td>
        <td><?=h($item['bg_color'])?></td>
        <td><?=($item['is_active'] ?? 1) ? '✅ 显示' : '❌ 隐藏'?></td>
        <td class="actions">
          <a href="?edit_cd=<?=$item['id']?>">编辑</a>
          <form method="post" style="display:inline" onsubmit="return confirm('确定删除？')">
            <input type="hidden" name="cd_action" value="delete"><input type="hidden" name="cd_id" value="<?=$item['id']?>">
            <button type="submit">删除</button>
          </form>
        </td>
      </tr>
      <?php endforeach ?>
    </tbody>
  </table>
</div>
<?php endif ?>

<!-- ============ 定位地图管理 ============ -->
<div class="card">
  <h2>📍 定位地图 <small style="color:#999;font-weight:normal">（共<?=count($locationList)?>条）</small></h2>
  <form method="post">
    <input type="hidden" name="loc_action" value="<?=$editLoc?'edit':'add'?>">
    <?php if ($editLoc): ?><input type="hidden" name="loc_id" value="<?=$editLoc['id']?>"><?php endif ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:15px;margin-bottom:15px">
      <div class="form-group">
        <label>地点名称</label>
        <input name="loc_name" value="<?=h($editLoc['name'] ?? '')?>" placeholder="如：我们的婚礼现场">
      </div>
      <div class="form-group">
        <label>详细地址</label>
        <input name="loc_address" value="<?=h($editLoc['address'] ?? '')?>" placeholder="地址文字">
      </div>
      <div class="form-group">
        <label>纬度</label>
        <input type="number" step="any" name="loc_lat" value="<?=h($editLoc['lat'] ?? '')?>" placeholder="如：23.129110">
      </div>
      <div class="form-group">
        <label>经度</label>
        <input type="number" step="any" name="loc_lng" value="<?=h($editLoc['lng'] ?? '')?>" placeholder="如：113.264385">
      </div>
      <div class="form-group">
        <label>地图类型</label>
        <select name="loc_map_type">
          <option value="baidu" <?=($editLoc['map_type']??'')==='baidu'?'selected':''?>>百度地图</option>
          <option value="gaode" <?=($editLoc['map_type']??'')==='gaode'?'selected':''?>>高德地图</option>
        </select>
      </div>
      <div class="form-group">
        <label>排序</label>
        <input type="number" name="loc_sort" value="<?=h($editLoc['sort_order'] ?? 0)?>">
      </div>
      <div class="form-group">
        <label>显示地图</label>
        <label style="display:flex;align-items:center;gap:6px;margin-top:5px">
          <input type="checkbox" name="loc_show" value="1" <?=empty($editLoc) || ($editLoc['is_show']??1) ? 'checked' : ''?>> 显示
        </label>
      </div>
    </div>
    <p style="font-size:.8rem;color:#888;margin:-10px 0 15px">💡 坐标获取：<a href="https://api.map.baidu.com/lbsapi/getpoint/" target="_blank">百度拾取坐标</a> | <a href="https://lbs.amap.com/tools/picker" target="_blank">高德拾取坐标</a></p>
    <button type="submit" class="btn btn-primary btn-sm"><?=$editLoc?'保存修改':'添加地点'?></button>
    <?php if ($editLoc): ?><a href="countdown.php" class="btn btn-secondary btn-sm">取消</a><?php endif ?>
  </form>
</div>

<?php if (!empty($locationList)): ?>
<div class="card">
  <table class="table">
    <thead>
      <tr><th>地点</th><th>地址</th><th>坐标</th><th>地图</th><th>状态</th><th>操作</th></tr>
    </thead>
    <tbody>
      <?php foreach ($locationList as $item): ?>
      <tr>
        <td><?=h($item['name'])?></td>
        <td><?=h($item['address'])?></td>
        <td><?=h($item['lat'])?>, <?=h($item['lng'])?></td>
        <td><?=$item['map_type']==='baidu'?'百度':'高德'?></td>
        <td><?=($item['is_show'] ?? 1) ? '✅ 显示' : '❌ 隐藏'?></td>
        <td class="actions">
          <a href="?edit_loc=<?=$item['id']?>">编辑</a>
          <form method="post" style="display:inline" onsubmit="return confirm('确定删除？')">
            <input type="hidden" name="loc_action" value="delete"><input type="hidden" name="loc_id" value="<?=$item['id']?>">
            <button type="submit">删除</button>
          </form>
        </td>
      </tr>
      <?php endforeach ?>
    </tbody>
  </table>
</div>
<?php endif ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/../templates/admin/layout.php';
