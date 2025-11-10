<?php
include 'config.php';
session_start();

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
    <title>四季合合数据库管理系统 - 控制面板</title>
    <style>
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            background: linear-gradient(135deg, #74ebd5, #ACB6E5);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .navbar {
            position: sticky;
            top: 0;
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(10px);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 18px;
            border-bottom: 1px solid #e5e7eb;
            z-index: 10;
        }
        .navbar h1 {
            font-size: 18px;
            margin: 0;
            color: #333;
        }
        .logout-btn {
            background: #ef4444;
            color: #fff;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 14px;
            text-decoration: none;
            font-weight: 600;
        }
        .logout-btn:hover {
            background: #dc2626;
        }
        .container {
            flex: 1;
            padding: 16px;
        }
        .welcome {
            background: #d1fae5;
            border: 1px solid #a7f3d0;
            color: #065f46;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 18px;
            text-align: center;
            font-size: 14px;
        }
        .table-grid {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .table-card {
            background: #fff;
            border-radius: 12px;
            padding: 18px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        .table-card h3 {
            margin: 0 0 8px;
            font-size: 16px;
            color: #111827;
        }
        .table-description {
            font-size: 13px;
            color: #4b5563;
            line-height: 1.4;
            margin-bottom: 12px;
        }
        .table-btn {
            align-self: stretch;
            text-align: center;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: #fff;
            padding: 12px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .table-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(37,99,235,0.3);
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>四季合合数据后台</h1>
        <a href="?logout=1" class="logout-btn">退出</a>
    </div>

    <div class="container">
        <div class="welcome">
            <strong>欢迎，雨欣宝贝！</strong> 请选择要管理的数据表。
        </div>

        <div class="table-grid">
            <div class="table-card">
                <h3>Reports 表</h3>
                <div class="table-description">
                    管理举报信息，包含举报者IP、举报时间、详情、图片路径、联系方式等内容。
                </div>
                <a href="manage_table.php?table=reports" class="table-btn">进入 Reports</a>
            </div>

            <div class="table-card">
                <h3>Comments 表</h3>
                <div class="table-description">
                    管理用户评论信息，包含评论ID、关联举报ID、评论者姓名、内容和IP地址等内容。
                </div>
                <a href="manage_table.php?table=comments" class="table-btn">进入 Comments</a>
            </div>
        </div>
    </div>
</body>
</html>
