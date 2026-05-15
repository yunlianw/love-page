<?php
/**
 * 恋爱单页系统 - 安装向导
 */
session_start();
$step = isset($_GET['step']) ? intval($_GET['step']) : 1;
$error = '';
$success = '';

// 安装锁定检测
if (file_exists(__DIR__ . '/../install.lock')) {
    header('Location: /');
    exit;
}

// 连接测试
function testDB($host, $port, $user, $pass, $name) {
    try {
        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        return ['ok' => true, 'msg' => '连接成功'];
    } catch (PDOException $e) {
        return ['ok' => false, 'msg' => $e->getMessage()];
    }
}

// 步骤3：执行安装
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 3) {
    $host = trim($_POST['db_host'] ?? 'localhost');
    $port = intval($_POST['db_port'] ?? 3306);
    $user = trim($_POST['db_user'] ?? '');
    $pass = $_POST['db_pass'] ?? '';
    $name = trim($_POST['db_name'] ?? '');
    $prefix = trim($_POST['db_prefix'] ?? 'love_');
    $site_url = rtrim(trim($_POST['site_url'] ?? ''), '/');
    $admin_user = trim($_POST['admin_user'] ?? 'admin');
    $admin_pass = $_POST['admin_pass'] ?? '';
    $admin_dir = trim($_POST['admin_dir'] ?? 'admin');

    if (empty($host) || empty($user) || empty($name)) {
        $error = '请填写完整的数据库信息';
    } elseif (empty($admin_pass)) {
        $error = '请设置管理员密码';
    } elseif (strlen($admin_pass) < 6) {
        $error = '管理员密码至少6位';
    } else {
        $test = testDB($host, $port, $user, $pass, $name);
        if (!$test['ok']) {
            $error = '数据库连接失败: ' . $test['msg'];
        } else {
            $pdo = new PDO("mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

            // 读取SQL文件
            $sqlFile = __DIR__ . '/install.sql';
            if (!file_exists($sqlFile)) {
                $error = 'install.sql 文件不存在';
            } else {
                $sql = file_get_contents($sqlFile);
                // 替换表前缀
                $sql = str_replace('love_', $prefix, $sql);

                // 执行SQL
                try {
                    $pdo->exec($sql);
                } catch (PDOException $e) {
                    $error = 'SQL执行失败: ' . $e->getMessage();
                }

                if (empty($error)) {
                    // 生成config.php
                    $configContent = <<<CONF
<?php
/**
 * 数据库配置（安装程序自动生成）
 */
define('DB_HOST', '{$host}');
define('DB_PORT', {$port});
define('DB_USER', '{$user}');
define('DB_PASS', '{$pass}');
define('DB_NAME', '{$name}');
define('DB_PREFIX', '{$prefix}');
define('SITE_URL', '{$site_url}');
define('ROOT_PATH', __DIR__ . '/..');
CONF;

                    file_put_contents(__DIR__ . '/../config/config.php', $configContent);

                    // 更新管理员密码
                    $hash = password_hash($admin_pass, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE {$prefix}users SET username=?, password=? WHERE id=1");
                    $stmt->execute([$admin_user, $hash]);

                    // 更新站点URL和头像路径
                    $stmt = $pdo->prepare("UPDATE {$prefix}config SET avatar_left=REPLACE(avatar_left, 'https://9255.9255.net', ?), avatar_right=REPLACE(avatar_right, 'https://9255.9255.net', ?) WHERE id=1");
                    $stmt->execute([$site_url, $site_url]);

                    // 重命名后台目录
                    $actualAdminDir = 'admin'; // 默认值
                    if ($admin_dir !== 'admin' && !empty($admin_dir)) {
                        $oldDir = __DIR__ . '/../admin';
                        $newDir = __DIR__ . '/../' . $admin_dir;
                        if (is_dir($oldDir) && !is_dir($newDir)) {
                            rename($oldDir, $newDir);
                            $actualAdminDir = $admin_dir;
                        }
                    }

                    // 创建install.lock
                    file_put_contents(__DIR__ . '/../install.lock', date('Y-m-d H:i:s'));

                    // 保存安装信息到session，供步骤4显示
                    $_SESSION['install_admin_dir'] = $actualAdminDir;
                    $_SESSION['install_admin_user'] = $admin_user;

                    $step = 4;
                }
            }
        }
    }
}

// 步骤4：完成页
if ($step === 4) {
    // 已在安装逻辑中处理
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>安装 - 恋爱单页系统</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Noto Sans SC',sans-serif;background:linear-gradient(135deg,#fff5f7 0%,#ffe0e8 50%,#f5e6ff 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.install-box{background:#fff;border-radius:20px;box-shadow:0 20px 60px rgba(255,107,138,0.15);max-width:560px;width:100%;overflow:hidden}
.install-header{background:linear-gradient(135deg,#ff6b8a,#c77dba);color:#fff;padding:30px;text-align:center}
.install-header h1{font-size:1.8rem;margin-bottom:5px}
.install-header p{opacity:.9;font-size:.9rem}
.install-body{padding:30px}
.steps{display:flex;justify-content:center;gap:8px;margin-bottom:30px}
.step-dot{width:36px;height:36px;border-radius:50%;background:#f0f0f0;display:flex;align-items:center;justify-content:center;font-size:.85rem;font-weight:600;color:#999;transition:all .3s}
.step-dot.active{background:linear-gradient(135deg,#ff6b8a,#c77dba);color:#fff}
.step-dot.done{background:#98d4a0;color:#fff}
.form-group{margin-bottom:18px}
.form-group label{display:block;font-weight:500;margin-bottom:6px;color:#4a3347;font-size:.9rem}
.form-group input,.form-group select{width:100%;padding:10px 14px;border:2px solid #e8e8e8;border-radius:10px;font-size:.95rem;outline:none;transition:border-color .3s;font-family:inherit}
.form-group input:focus{border-color:#ff6b8a}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.form-hint{font-size:.8rem;color:#999;margin-top:4px}
.alert{padding:12px 16px;border-radius:10px;margin-bottom:20px;font-size:.9rem}
.alert-error{background:#ffe5e5;color:#c0392b;border:1px solid #ffcdcd}
.alert-success{background:#e5ffe5;color:#27ae60;border:1px solid #cdffcd}
.btn{display:inline-block;padding:12px 32px;border:none;border-radius:30px;background:linear-gradient(135deg,#ff6b8a,#c77dba);color:#fff;font-size:1rem;cursor:pointer;transition:transform .2s;text-decoration:none}
.btn:hover{transform:scale(1.02)}
.btn-outline{background:transparent;border:2px solid #ff6b8a;color:#ff6b8a}
.btn-outline:hover{background:#ff6b8a;color:#fff}
.success-icon{font-size:80px;text-align:center;margin-bottom:20px}
.success-title{font-size:1.5rem;color:#ff6b8a;text-align:center;margin-bottom:10px}
.success-info{background:#f9f9f9;border-radius:12px;padding:15px;font-size:.9rem;color:#666;line-height:1.8}
.success-info strong{color:#4a3347}
@media(max-width:480px){.form-row{grid-template-columns:1fr}.install-body{padding:20px}}
</style>
</head>
<body>
<div class="install-box">
    <div class="install-header">
        <h1>💕 恋爱单页系统</h1>
        <p>安装向导</p>
    </div>
    <div class="install-body">
        <div class="steps">
            <div class="step-dot <?=($step>=1?'active':'')?>">1</div>
            <div class="step-dot <?=($step>=2?'active':'')?>">2</div>
            <div class="step-dot <?=($step>=3?'active':'')?>">3</div>
            <div class="step-dot <?=($step>=4?'done':'')?>">✓</div>
        </div>

<?php if ($error): ?>
        <div class="alert alert-error"><?=htmlspecialchars($error)?></div>
<?php endif; ?>

<?php if ($step === 1): ?>
        <h2 style="margin-bottom:20px;color:#4a3347">🛠 环境检测</h2>
        <?php
        $checks = [
            ['PHP版本 >= 7.4', version_compare(PHP_VERSION, '7.4', '>=')],
            ['PDO扩展', extension_loaded('pdo')],
            ['PDO MySQL扩展', extension_loaded('pdo_mysql')],
            ['MBString扩展', extension_loaded('mbstring')],
            ['JSON扩展', extension_loaded('json')],
            ['config目录可写', is_writable(__DIR__ . '/../config')],
            ['assets目录可写', is_writable(__DIR__ . '/../assets')],
        ];
        $allPass = true;
        foreach ($checks as $c) {
            $ok = $c[1];
            if (!$ok) $allPass = false;
            echo '<div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid #f5f5f5">';
            echo '<span style="color:' . ($ok ? '#27ae60' : '#e74c3c') . ';font-size:1.2rem">' . ($ok ? '✅' : '❌') . '</span>';
            echo '<span style="color:#4a3347">' . $c[0] . '</span>';
            echo '</div>';
        }
        ?>
        <div style="margin-top:25px;text-align:center">
        <?php if ($allPass): ?>
            <a href="?step=2" class="btn">下一步：数据库配置</a>
        <?php else: ?>
            <p style="color:#e74c3c">请先修复以上问题</p>
        <?php endif; ?>
        </div>

<?php elseif ($step === 2): ?>
        <h2 style="margin-bottom:20px;color:#4a3347">💾 数据库配置</h2>
        <form method="post" action="?step=3">
            <div class="form-row">
                <div class="form-group">
                    <label>数据库主机</label>
                    <input name="db_host" value="localhost" placeholder="localhost">
                </div>
                <div class="form-group">
                    <label>端口</label>
                    <input name="db_port" value="3306" type="number">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>数据库用户名</label>
                    <input name="db_user" placeholder="root">
                </div>
                <div class="form-group">
                    <label>数据库密码</label>
                    <input name="db_pass" type="password" placeholder="数据库密码">
                </div>
            </div>
            <div class="form-group">
                <label>数据库名</label>
                <input name="db_name" placeholder="love" value="love">
                <p class="form-hint">需提前创建好数据库</p>
            </div>
            <div class="form-group">
                <label>表前缀</label>
                <input name="db_prefix" value="love_" placeholder="love_">
            </div>
            <div class="form-group">
                <label>网站地址</label>
                <input name="site_url" id="siteUrl" placeholder="https://example.com">
                <p class="form-hint">访问首页的完整地址</p>
            </div>
            <hr style="border:none;border-top:1px dashed #eee;margin:20px 0">
            <h3 style="margin-bottom:15px;color:#4a3347">👤 管理员设置</h3>
            <div class="form-row">
                <div class="form-group">
                    <label>管理员用户名</label>
                    <input name="admin_user" value="admin">
                </div>
                <div class="form-group">
                    <label>管理员密码</label>
                    <input name="admin_pass" type="password" placeholder="至少6位" required>
                </div>
            </div>
            <div class="form-group">
                <label>后台目录名</label>
                <input name="admin_dir" value="admin" placeholder="admin">
                <p class="form-hint">自定义后台地址，安装后不可更改</p>
            </div>
            <div style="text-align:center;margin-top:20px">
                <a href="?step=1" class="btn btn-outline" style="margin-right:10px">上一步</a>
                <button type="submit" class="btn">开始安装</button>
            </div>
        </form>
        <script>document.getElementById('siteUrl').value = location.protocol + '//' + location.host;</script>

<?php elseif ($step === 3): ?>
        <h2 style="margin-bottom:20px;color:#4a3347">⚙️ 正在安装...</h2>
        <p>安装完成后会自动跳转</p>

<?php elseif ($step === 4): 
        $finalAdminDir = $_SESSION['install_admin_dir'] ?? 'admin';
        $finalAdminUser = $_SESSION['install_admin_user'] ?? 'admin';
        // 清除session中的安装信息
        unset($_SESSION['install_admin_dir'], $_SESSION['install_admin_user']);
?>
        <div class="success-icon">🎉</div>
        <div class="success-title">安装成功！</div>
        <div class="success-info">
            <p>✅ 数据库已初始化（含示例数据）</p>
            <p>✅ 配置文件已生成</p>
            <p>✅ 管理员账号已创建</p>
            <p><strong>管理员用户名：</strong><?=htmlspecialchars($finalAdminUser)?></p>
            <p><strong>后台地址：</strong><?=htmlspecialchars($finalAdminDir)?>/</p>
        </div>
        <div style="margin-top:20px;text-align:center">
            <a href="/" class="btn" style="margin-right:10px">访问首页</a>
            <a href="/<?=htmlspecialchars($finalAdminDir)?>/" class="btn btn-outline">进入后台</a>
        </div>
        <div style="margin-top:20px;padding:15px;background:#fff9e6;border-radius:10px;text-align:center">
            <p style="color:#8b6b7d;font-size:.85rem">⚠️ 安装完成后请删除 <strong>install/</strong> 目录</p>
        </div>
<?php endif; ?>
    </div>
</div>
</body>
</html>
