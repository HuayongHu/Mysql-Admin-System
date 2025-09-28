<?php
include 'config.php';

// 检查登录状态
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

// 处理退出登录
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>数据库管理系统 - 控制面板</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
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
        .logout-btn {
            background: #dc3545;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
        }
        .logout-btn:hover {
            background: #c82333;
        }
        .table-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }
        .table-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            transition: transform 0.2s;
        }
        .table-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .table-btn {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            margin-top: 10px;
        }
        .table-btn:hover {
            background: #0056b3;
        }
        .table-description {
            color: #666;
            margin: 10px 0;
            line-height: 1.5;
        }
        .welcome {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>数据库管理系统</h1>
            <a href="?logout=1" class="logout-btn">退出登录</a>
        </div>
        
        <div class="welcome">
            <strong>欢迎，管理员！</strong> 请选择要管理的数据表。
        </div>
        
        <div class="table-grid">
            <div class="table-card">
                <h3>Reports 表</h3>
                <div class="table-description">
                    管理举报信息，包含举报者IP、举报时间、详情、图片路径、联系方式、等内容。
                </div>
                <a href="manage_table.php?table=reports" class="table-btn">管理 Reports</a>
            </div>
            
            <div class="table-card">
                <h3>Comments 表</h3>
                <div class="table-description">
                    管理用户评论信息，包含评论ID、关联举报ID、评论者姓名、内容和IP地址等内容。
                </div>
                <a href="manage_table.php?table=comments" class="table-btn">管理 Comments</a>
            </div>
        </div>
    </div>
</body>
</html>