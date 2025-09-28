<?php
include 'config.php';

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
if ($_POST && isset($_POST['search_id'])) {
    $search_id = $_POST['search_id'];
    $stmt = $pdo->prepare("SELECT * FROM $table WHERE id = ?");
    $stmt->execute([$search_id]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
}

// 处理删除请求
if ($_POST && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM $table WHERE id = ?");
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>管理 <?= $table_title ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }
        h1 {
            color: #333;
            margin: 0;
        }
        .back-btn {
            background: #6c757d;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 5px;
        }
        .back-btn:hover {
            background: #545b62;
        }
        .search-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .form-inline {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        input[type="number"] {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 150px;
        }
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background: #007bff;
            color: white;
        }
        .btn-primary:hover {
            background: #0056b3;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .record-display {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }
        .field-row {
            display: flex;
            margin-bottom: 15px;
            align-items: center;
        }
        .field-label {
            font-weight: bold;
            width: 150px;
            color: #495057;
        }
        .field-value {
            flex: 1;
            margin-right: 10px;
        }
        .field-value input, .field-value textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .field-value textarea {
            height: 80px;
            resize: vertical;
        }
        .no-record {
            text-align: center;
            color: #666;
            padding: 40px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .delete-section {
            background: #f8d7da;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            text-align: center;
        }
        .delete-warning {
            color: #721c24;
            font-weight: bold;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>管理 <?= $table_title ?></h1>
            <a href="dashboard.php" class="back-btn">返回控制面板</a>
        </div>
        
        <?php if (isset($success_message)): ?>
            <div class="message success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="message error"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>
        
        <div class="search-section">
            <h3>查询记录</h3>
            <form method="POST" class="form-inline">
                <label for="search_id">输入ID：</label>
                <input type="text" id="search_id" name="search_id" value="<?= htmlspecialchars($search_id) ?>" required>
                <button type="submit" class="btn btn-primary">查询</button>
            </form>
        </div>
        
        <?php if ($search_id && !$record): ?>
            <div class="no-record">
                <h3>未找到记录</h3>
                <p>ID为 <?= htmlspecialchars($search_id) ?> 的记录不存在。</p>
            </div>
        <?php elseif ($record): ?>
            <div class="record-display">
                <h3>记录详情 (ID: <?= htmlspecialchars($record['id']) ?>)</h3>
                <form id="editForm">
                    <?php foreach ($fields as $field): ?>
                        <div class="field-row">
                            <div class="field-label"><?= htmlspecialchars($field) ?>:</div>
                            <div class="field-value">
                                <?php if (in_array($field, ['report_details', 'content'])): ?>
                                    <textarea name="<?= $field ?>" data-field="<?= $field ?>"><?= htmlspecialchars($record[$field] ?? '') ?></textarea>
                                <?php else: ?>
                                    <input type="text" name="<?= $field ?>" value="<?= htmlspecialchars($record[$field] ?? '') ?>" data-field="<?= $field ?>" <?= $field === 'id' ? 'readonly' : '' ?>>
                                <?php endif; ?>
                            </div>
                            <?php if ($field !== 'id'): ?>
                                <button type="button" class="btn btn-primary" onclick="saveField('<?= $field ?>', <?= $record['id'] ?>)">保存</button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </form>
                
                <div class="delete-section">
                    <div class="delete-warning">⚠️ 危险操作：删除记录</div>
                    <p>删除操作不可撤销，请谨慎操作！</p>
                    <form method="POST" onsubmit="return confirm('确定要删除这条记录吗？此操作不可撤销！')">
                        <input type="hidden" name="delete_id" value="<?= $record['id'] ?>">
                        <button type="submit" class="btn btn-danger">删除此记录</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        function saveField(fieldName, recordId) {
            const input = document.querySelector(`[data-field="${fieldName}"]`);
            const value = input.value;
            
            fetch('update_field.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `table=<?= $table ?>&field=${fieldName}&value=${encodeURIComponent(value)}&id=${recordId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('保存成功！');
                } else {
                    alert('保存失败：' + data.error);
                }
            })
            .catch(error => {
                alert('保存失败：网络错误');
            });
        }
    </script>
</body>
</html>