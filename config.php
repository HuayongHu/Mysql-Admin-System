<?php
// 数据库配置信息
$servername = "localhost";
$username = "username";
$password = "dbpwd";
$dbname = "bdname";

// 管理员登录信息
$admin_username = "web_name";
$admin_password = "web_pwd";
$admin_auxiliary = "other_text";

// 创建数据库连接
function getDBConnection() {
    global $servername, $username, $password, $dbname;
    
    try {
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch(PDOException $e) {
        die("连接失败: " . $e->getMessage());
    }
}

// 验证管理员登录
function verifyAdmin($user, $pass, $aux) {
    global $admin_username, $admin_password, $admin_auxiliary;
    return ($user === $admin_username && $pass === $admin_password && $aux === $admin_auxiliary);
}

// 开启会话
session_start();
?>
