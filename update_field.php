<?php
include 'config.php';
session_start();

header('Content-Type: application/json; charset=utf-8');

// 登录校验
if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'error' => '未登录']);
    exit();
}

// 参数校验
if ($_SERVER['REQUEST_METHOD'] !== 'POST' ||
    !isset($_POST['table'], $_POST['field'], $_POST['value'], $_POST['id'])) {
    echo json_encode(['success' => false, 'error' => '缺少必要参数']);
    exit();
}

$table = $_POST['table'];
$field = $_POST['field'];
$value = $_POST['value'];
$id    = trim((string)$_POST['id']); // 支持任意字符的 ID

if ($id === '') {
    echo json_encode(['success' => false, 'error' => 'ID 不能为空']);
    exit();
}

// 白名单校验
$allowed_fields = [
    'reports'  => ['reporter_ip', 'report_time', 'report_details', 'image_path', 'reporter_contact', 'likes', 'hugs'],
    'comments' => ['report_id', 'name', 'content', 'commenter_ip']
];

if (!array_key_exists($table, $allowed_fields)) {
    echo json_encode(['success' => false, 'error' => '无效的表名']);
    exit();
}
if (!in_array($field, $allowed_fields[$table], true)) {
    echo json_encode(['success' => false, 'error' => '无效的字段名']);
    exit();
}

// 数字字段统一收敛（不影响 ID）
$numeric_fields = ['likes', 'hugs', 'report_id'];
if (in_array($field, $numeric_fields, true)) {
    $value = (string)(int)$value;
}

try {
    $pdo = getDBConnection();
    // 安全设置
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    // 事务保证原子性
    $pdo->beginTransaction();

    // 确认存在（按字符串 ID 精确匹配）
    $sqlSelect = "SELECT {$field} FROM {$table} WHERE id = :id LIMIT 1";
    $stmtSelect = $pdo->prepare($sqlSelect);
    $stmtSelect->bindValue(':id', $id, PDO::PARAM_STR);
    $stmtSelect->execute();
    $current = $stmtSelect->fetch(PDO::FETCH_ASSOC);

    if (!$current) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => '记录不存在']);
        exit();
    }

    // 值未变化则不更新
    if ((string)$current[$field] === (string)$value) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => '未更新（值未变化）']);
        exit();
    }

    // 仅更新该行（防御性 LIMIT 1）
    $sqlUpdate = "UPDATE {$table} SET {$field} = :value WHERE id = :id LIMIT 1";
    $stmtUpdate = $pdo->prepare($sqlUpdate);
    $stmtUpdate->bindValue(':value', $value, PDO::PARAM_STR);
    $stmtUpdate->bindValue(':id', $id, PDO::PARAM_STR);
    $stmtUpdate->execute();

    // 成功即提交
    $pdo->commit();

    // 注意：rowCount 在某些驱动可能为 0（触发器/重复值等），我们已通过前面比较规避
    echo json_encode([
        'success' => true,
        'message' => '更新成功',
        'id'      => $id,
        'field'   => $field,
        'value'   => $value
    ]);
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'error' => '数据库错误: ' . $e->getMessage()]);
}
