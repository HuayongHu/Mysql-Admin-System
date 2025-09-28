<?php
include 'config.php';

// 设置响应头为JSON
header('Content-Type: application/json');

// 检查登录状态
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'error' => '未登录']);
    exit();
}

// 检查请求参数
if (!$_POST || !isset($_POST['table'], $_POST['field'], $_POST['value'], $_POST['id'])) {
    echo json_encode(['success' => false, 'error' => '缺少必要参数']);
    exit();
}

$table = $_POST['table'];
$field = $_POST['field'];
$value = $_POST['value'];
$id = $_POST['id'];

// 验证表名
if (!in_array($table, ['reports', 'comments'])) {
    echo json_encode(['success' => false, 'error' => '无效的表名']);
    exit();
}

// 验证字段名
$allowed_fields = [
    'reports' => ['reporter_ip', 'report_time', 'report_details', 'image_path', 'reporter_contact', 'likes', 'hugs'],
    'comments' => ['report_id', 'name', 'content', 'commenter_ip']
];

if (!in_array($field, $allowed_fields[$table])) {
    echo json_encode(['success' => false, 'error' => '无效的字段名']);
    exit();
}

try {
    $pdo = getDBConnection();
    
    // 准备并执行更新语句
    $sql = "UPDATE $table SET $field = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([$value, $id]);
    
    if ($result && $stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => '更新失败或记录不存在']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => '数据库错误：' . $e->getMessage()]);
}
?>