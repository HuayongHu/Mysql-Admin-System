<?php
include 'config.php';
session_start();

// 检查登录状态
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

// 获取表名
$table = $_GET['table'] ?? '';
if (!in_array($table, ['reports', 'comments'])) {
    header("Location: dashboard.php");
    exit();
}

// 定义表字段
$table_fields = [
    'reports' => ['id', 'reporter_ip', 'report_time', 'report_details', 'image_path', 'reporter_contact', 'likes', 'hugs'],
    'comments' => ['id', 'report_id', 'name', 'content', 'commenter_ip']
];

$fields = $table_fields[$table];
$pdo = getDBConnection();

// 处理查询请求
$record = null;
$search_id = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_id'])) {
    $search_id = trim($_POST['search_id']);
    if ($search_id !== '') {
        $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE id = ?");
        $stmt->execute([$search_id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// 处理删除请求
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = trim($_POST['delete_id']);
    try {
        $stmt = $pdo->prepare("DELETE FROM {$table} WHERE id = ?");
        $stmt->execute([$delete_id]);
        $success_message = "记录删除成功！";
        $record = null;
        $search_id = '';
    } catch (Exception $e) {
        $error_message = "删除失败：" . $e->getMessage();
    }
}

$table_title = $table === 'reports' ? 'Reports 表' : 'Comments 表';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
    <title>管理 <?= htmlspecialchars($table_title) ?></title>
    <meta name="color-scheme" content="light dark">
    <style>
        :root{
            --bg1:#eef2f3; --bg2:#dfe9f3;
            --text:#111827; --muted:#6b7280;
            --card:#ffffff; --border:#e5e7eb;
            --accent:#2563eb; --accent2:#22c55e;
            --danger:#ef4444; --danger-dark:#dc2626;
            --success:#10b981;
            --shadow: 0 10px 24px rgba(0,0,0,0.12);
            --input:#f8fafc;
        }
        @media (prefers-color-scheme: dark) {
            :root{
                --bg1:#0f172a; --bg2:#111827;
                --text:#e5e7eb; --muted:#9ca3af;
                --card:rgba(255,255,255,0.08); --border:rgba(255,255,255,0.15);
                --accent:#4cc9f0; --accent2:#7b2cbf;
                --danger:#ff4d6d; --danger-dark:#e03154;
                --success:#34d399;
                --shadow: 0 14px 28px rgba(0,0,0,0.35);
                --input:rgba(255,255,255,0.06);
            }
        }
        *{box-sizing:border-box}
        body{
            margin:0;
            font-family: -apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Arial;
            color:var(--text);
            background: linear-gradient(135deg, var(--bg1), var(--bg2));
            min-height:100vh;
            display:flex;
            flex-direction:column;
        }
        .navbar{
            position:sticky; top:0;
            display:flex; align-items:center; gap:10px;
            padding:12px 14px;
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border);
            z-index: 10;
        }
        @media (prefers-color-scheme: dark){
            .navbar{ background: rgba(17,24,39,0.6); }
        }
        .nav-title{
            font-size:16px; font-weight:700; margin:0;
        }
        .nav-sub{ font-size:12px; color:var(--muted); margin-left:auto }
        .back-btn{
            display:inline-flex; align-items:center; gap:6px;
            color:var(--text); text-decoration:none; font-size:14px;
            padding:8px 10px; border:1px solid var(--border); border-radius:10px;
        }
        .container{ padding:14px; flex:1; display:grid; gap:12px }
        .panel{
            background: var(--card);
            border:1px solid var(--border);
            border-radius:14px;
            box-shadow: var(--shadow);
            padding:14px;
        }
        .panel h3{
            margin:0 0 8px; font-size:15px;
        }
        .message{
            padding:10px 12px; border-radius:10px; font-size:13px;
        }
        .success{ background: rgba(16,185,129,0.12); border:1px solid rgba(16,185,129,0.35); color:var(--success) }
        .error{ background: rgba(239,68,68,0.12); border:1px solid rgba(239,68,68,0.35); color:var(--danger) }

        .form-row{ display:grid; gap:8px; }
        .label{ font-size:12px; color:var(--muted) }
        .input{
            display:flex; align-items:center; gap:10px;
            background: var(--input);
            border:1px solid var(--border);
            border-radius:12px;
            padding:10px 12px;
        }
        .input input, .input textarea{
            width:100%; border:none; outline:none; background:transparent; color:var(--text);
            font-size:14px;
        }
        .input textarea{ min-height:100px; resize: vertical }
        .actions{
            display:flex; gap:10px; align-items:center;
        }
        .btn{
            display:inline-flex; align-items:center; justify-content:center; gap:6px;
            border:none; cursor:pointer; user-select:none;
            font-weight:600; font-size:14px;
            padding:10px 12px; border-radius:12px;
        }
        .btn-primary{ color:#fff; background: linear-gradient(135deg, var(--accent), var(--accent2)) }
        .btn-danger{ color:#fff; background: var(--danger) }
        .btn-outline{
            color:var(--text); background:transparent; border:1px solid var(--border)
        }

        .grid{ display:grid; gap:12px }
        .record-card{
            background: var(--card);
            border:1px solid var(--border);
            border-radius:14px;
            box-shadow: var(--shadow);
            padding:12px;
        }
        .record-header{
            display:flex; align-items:center; justify-content:space-between;
            margin-bottom:8px;
        }
        .record-header h4{ margin:0; font-size:14px }
        .field-card{
            display:grid; gap:8px; padding:10px; border:1px dashed var(--border); border-radius:12px;
        }
        .hint{ font-size:12px; color:var(--muted) }

        .thumb{
            width:100%; max-height:200px; object-fit:cover;
            border-radius:10px; border:1px solid var(--border);
        }
        .danger-zone{
            background: rgba(239,68,68,0.08);
            border:1px solid rgba(239,68,68,0.25);
            border-radius:12px; padding:12px; margin-top:10px;
        }
        .danger-zone .title{ font-weight:700; color:var(--danger); margin:0 0 6px; font-size:14px }
        .danger-zone p{ margin:0 0 10px; color:var(--muted); font-size:12px }

        /* Touch-friendly tap targets */
        a, button{ -webkit-tap-highlight-color: transparent }
    </style>
</head>
<body>
    <div class="navbar">
        <a class="back-btn" href="dashboard.php">← 返回</a>
        <h1 class="nav-title">管理 <?= htmlspecialchars($table_title) ?></h1>
        <div class="nav-sub">爱你宝贝老婆</div>
    </div>

    <div class="container">
        <?php if (isset($success_message)): ?>
            <div class="message success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <div class="message error"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <section class="panel">
            <h3>查询记录</h3>
            <form method="POST" class="grid" novalidate>
                <div class="form-row">
                    <div class="label">输入 ID</div>
                    <div class="input">
                        <input
                            id="search_id"
                            name="search_id"
                            type="text"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            placeholder="例如：XPP7RX01"
                            value="<?= htmlspecialchars($search_id) ?>"
                            required
                        >
                    </div>
                    <div class="actions">
                        <button type="submit" class="btn btn-primary">查询</button>
                        <a href="?table=<?= urlencode($table) ?>" class="btn btn-outline">重置</a>
                    </div>
                </div>
            </form>
        </section>

        <?php if ($search_id && !$record): ?>
            <section class="panel">
                <h3>未找到记录</h3>
                <div class="hint">ID 为 <?= htmlspecialchars($search_id) ?> 的记录不存在。</div>
            </section>
        <?php elseif ($record): ?>
            <section class="record-card">
                <div class="record-header">
                    <h4>记录详情 · ID: <?= htmlspecialchars($record['id']) ?></h4>
                    <span class="hint"><?= htmlspecialchars($table_title) ?></span>
                </div>

                <div class="grid">
                    <?php foreach ($fields as $field): ?>
                        <div class="field-card">
                            <div class="label"><?= htmlspecialchars($field) ?></div>

                            <?php if ($field === 'image_path' && !empty($record[$field])): ?>
                                
                                <div class="hint">当前图片预览（基于 image_path）</div>
                            <?php endif; ?>

                            <?php if (in_array($field, ['report_details', 'content'])): ?>
                                <div class="input">
                                    <textarea
                                        name="<?= htmlspecialchars($field) ?>"
                                        data-field="<?= htmlspecialchars($field) ?>"
                                        placeholder="请输入文本内容"
                                    ><?= htmlspecialchars($record[$field] ?? '') ?></textarea>
                                </div>
                            <?php else: ?>
                                <div class="input">
                                    <input
                                        type="text"
                                        name="<?= htmlspecialchars($field) ?>"
                                        value="<?= htmlspecialchars($record[$field] ?? '') ?>"
                                        data-field="<?= htmlspecialchars($field) ?>"
                                        <?= $field === 'id' ? 'readonly' : '' ?>
                                        placeholder="<?= $field === 'id' ? 'ID不可编辑' : '请输入值' ?>"
                                    >
                                </div>
                            <?php endif; ?>

                            <div class="actions">
                                <?php if ($field !== 'id'): ?>
                                    <button type="button" class="btn btn-primary" onclick="saveField('<?= htmlspecialchars($field) ?>', '<?= htmlspecialchars($record['id'], ENT_QUOTES) ?>')">保存</button>
                                <?php else: ?>
                                    <span class="hint">系统字段不可编辑</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="danger-zone">
                    <p class="title">⚠️ 危险操作：删除记录</p>
                    <p>删除操作不可撤销，请谨慎操作！</p>
                    <form method="POST" onsubmit="return confirm('确定要删除这条记录吗？此操作不可撤销！')">
                        <input type="hidden" name="delete_id" value="<?=$record['id'] ?>">
                        <button type="submit" class="btn btn-danger">删除此记录</button>
                    </form>
                </div>
            </section>
        <?php endif; ?>
    </div>

    <script>
        function saveField(fieldName, recordId) {
            const input = document.querySelector(`[data-field="${fieldName}"]`);
            if (!input) return;
            const value = input.value;

            const params = new URLSearchParams();
            params.set('table', '<?= $table ?>');
            params.set('field', fieldName);
            params.set('value', value);
            params.set('id', recordId);

            fetch('update_field.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: params.toString()
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    toast('保存成功');
                } else {
                    toast('保存失败：' + (data.error || '未知错误'), true);
                }
            })
            .catch(() => toast('保存失败：网络错误', true));
        }

        // 轻量级移动端 toast
        function toast(msg, danger=false) {
            let el = document.createElement('div');
            el.textContent = msg;
            el.style.position = 'fixed';
            el.style.left = '50%';
            el.style.bottom = '18px';
            el.style.transform = 'translateX(-50%)';
            el.style.padding = '10px 14px';
            el.style.borderRadius = '12px';
            el.style.color = '#fff';
            el.style.fontSize = '14px';
            el.style.boxShadow = '0 10px 24px rgba(0,0,0,0.2)';
            el.style.zIndex = '9999';
            el.style.background = danger ? '#ef4444' : '#10b981';
            document.body.appendChild(el);
            setTimeout(() => {
                el.style.transition = 'opacity .3s';
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 300);
            }, 1600);
        }
    </script>
</body>
</html>
