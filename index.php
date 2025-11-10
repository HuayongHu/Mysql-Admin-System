<?php
include 'config.php';
session_start();

/* ---------- Math captcha ---------- */
function generateMathCaptcha() {
    $num1 = rand(1, 9);
    $num2 = rand(1, 9);
    $operators = ['+', '-', '×'];
    $op = $operators[array_rand($operators)];

    switch ($op) {
        case '+': $answer = $num1 + $num2; break;
        case '−': // never used, keep ASCII minus consistent
        case '-':
            if ($num1 < $num2) { [$num1, $num2] = [$num2, $num1]; }
            $answer = $num1 - $num2;
            $op = '−'; // display typographic minus
            break;
        case '×': $answer = $num1 * $num2; break;
    }

    $_SESSION['captcha_answer'] = $answer;
    $_SESSION['captcha_question'] = "{$num1} {$op} {$num2} = ?";
    $_SESSION['captcha_ts'] = time(); // bust caches
}

function ensureCaptcha() {
    if (!isset($_SESSION['captcha_question']) || !isset($_SESSION['captcha_answer'])) {
        generateMathCaptcha();
    }
}

/* ---------- Refresh captcha via GET ---------- */
if (isset($_GET['refresh']) && $_GET['refresh'] === '1') {
    generateMathCaptcha();
    header("Location: " . strtok($_SERVER['REQUEST_URI'], '?'));
    exit();
}

/* ---------- Handle login POST ---------- */
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input_username  = trim($_POST['username'] ?? '');
    $input_password  = trim($_POST['password'] ?? '');
    $input_auxiliary = trim($_POST['auxiliary'] ?? '');
    $input_captcha   = trim($_POST['captcha'] ?? '');

    if (!isset($_SESSION['captcha_answer']) || $input_captcha === '' || intval($input_captcha) !== intval($_SESSION['captcha_answer'])) {
        $error = "验证码错误，请重试。";
        generateMathCaptcha(); // regenerate on error
    } else if (verifyAdmin($input_username, $input_password, $input_auxiliary)) {
        $_SESSION['admin_logged_in'] = true;
        unset($_SESSION['captcha_question'], $_SESSION['captcha_answer'], $_SESSION['captcha_ts']);
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "口令、密钥或证书错误。";
        generateMathCaptcha(); // regenerate on error
    }
}

ensureCaptcha();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
    <title>四季合合 ID 查询系统维护后台 · 登录</title>
    <meta name="color-scheme" content="light dark">
    <style>
        :root{
            --bg1:#0f2027; --bg2:#203a43; --bg3:#2c5364;
            --card-bg: rgba(255,255,255,0.12);
            --card-border: rgba(255,255,255,0.25);
            --text:#ffffff;
            --muted: rgba(255,255,255,0.75);
            --accent:#4cc9f0;
            --accent-2:#7b2cbf;
            --danger:#ff4d6d;
            --input-bg: rgba(255,255,255,0.08);
            --shadow: 0 18px 40px rgba(0,0,0,0.35);
        }
        @media (prefers-color-scheme: light) {
            :root{
                --bg1:#eef2f3; --bg2:#dfe9f3; --bg3:#cfd9df;
                --card-bg: rgba(255,255,255,0.9);
                --card-border: rgba(0,0,0,0.06);
                --text:#1f2937;
                --muted:#4b5563;
                --accent:#2563eb;
                --accent-2:#22c55e;
                --danger:#dc2626;
                --input-bg: #f8fafc;
                --shadow: 0 18px 40px rgba(0,0,0,0.12);
            }
        }
        *{box-sizing:border-box}
        html,body{height:100%}
        body{
            margin:0;
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
            color:var(--text);
            background:
                radial-gradient(800px 800px at 10% 10%, rgba(255,255,255,0.08), transparent 60%),
                radial-gradient(800px 800px at 90% 90%, rgba(255,255,255,0.06), transparent 60%),
                linear-gradient(135deg, var(--bg1), var(--bg2) 50%, var(--bg3));
            display:flex;
            align-items:center;
            justify-content:center;
            padding:24px;
        }
        .aurora{
            position: fixed; inset: 0; pointer-events:none; filter: blur(60px); opacity:.35;
            background:
                radial-gradient(closest-side, rgba(76,201,240,.6), transparent),
                radial-gradient(closest-side, rgba(123,44,191,.6), transparent),
                radial-gradient(closest-side, rgba(34,197,94,.6), transparent);
            mix-blend-mode: screen;
            animation: float 18s ease-in-out infinite alternate;
        }
        @keyframes float {
            0%{transform: translate(-3%, -2%)}
            100%{transform: translate(3%, 2%)}
        }
        .card{
            position:relative;
            width: 380px;
            max-width: 92vw;
            padding: 30px 26px;
            border-radius: 18px;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            backdrop-filter: blur(14px) saturate(1.2);
            box-shadow: var(--shadow);
            animation: rise .6s ease both;
        }
        @keyframes rise { from{opacity:0; transform: translateY(14px)} to{opacity:1; transform:translateY(0)} }
        .brand{
            display:flex; align-items:center; gap:12px; margin-bottom:18px;
        }
        .brand .logo{
            width:36px; height:36px; border-radius:10px;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            display:grid; place-items:center; color:#fff; font-weight:800;
            box-shadow: 0 8px 18px rgba(0,0,0,0.2);
        }
        .brand h1{
            font-size:18px; margin:0; letter-spacing:.4px;
        }
        .subtitle{margin:4px 0 14px; color:var(--muted); font-size:13px}
        .error{
            display:flex; align-items:center; gap:8px;
            margin:10px 0 14px; padding:10px 12px; border-radius:12px;
            background: rgba(255,0,0,0.08); color:var(--danger);
            border: 1px solid rgba(255,0,0,0.15);
        }
        form{display:grid; gap:14px}
        .field{display:grid; gap:6px}
        label{font-size:13px; color:var(--muted)}
        .input{
            display:flex; align-items:center; gap:10px;
            background: var(--input-bg);
            border: 1px solid var(--card-border);
            border-radius: 12px;
            padding: 10px 12px;
            transition: border-color .2s, box-shadow .2s, transform .05s;
        }
        .input:focus-within{
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(76,201,240,.25);
        }
        .input input{
            width:100%; border:none; outline:none;
            background: transparent; color: var(--text);
            font-size:14px; padding:6px 4px;
        }
        .icon{
            width:18px; height:18px; opacity:.8;
        }
        .captcha-box{
            display:grid; gap:8px;
        }
        .captcha-question{
            display:flex; align-items:center; justify-content:space-between;
            background: linear-gradient(135deg, rgba(76,201,240,.12), rgba(123,44,191,.12));
            border: 1px dashed rgba(76,201,240,.35);
            padding: 10px 12px; border-radius: 12px;
            font-weight: 600;
        }
        .captcha-actions{
            display:flex; gap:8px; justify-content:flex-end;
        }
        .btn{
            display:inline-flex; align-items:center; justify-content:center; gap:8px;
            border:none; cursor:pointer; user-select:none;
            font-weight:600; font-size:14px;
            padding: 10px 14px; border-radius: 12px;
            transition: transform .12s ease, box-shadow .2s ease, filter .2s ease;
        }
        .btn-primary{
            color:#fff; background: linear-gradient(135deg, var(--accent), var(--accent-2));
            box-shadow: 0 10px 24px rgba(0,0,0,0.18);
        }
        .btn-primary:hover{transform: translateY(-1px); filter: brightness(1.05)}
        .btn-ghost{
            color: var(--text); background: transparent; border: 1px solid var(--card-border);
        }
        .footer{
            margin-top:10px; color:var(--muted); font-size:12px; text-align:center;
        }
        .hint{font-size:12px; color:var(--muted)}
        a.link{color:var(--accent); text-decoration:none}
        a.link:hover{text-decoration:underline}
    </style>
</head>
<body>
    <div class="aurora" aria-hidden="true"></div>
    <main class="card" role="main" aria-label="登录卡片">
        <div class="brand">
            
            <div>
                <h1>四季合合 · 管理员登录</h1>
                <div class="subtitle">ID 查询系统维护后台</div>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="error">
                <!-- alert icon -->
                <svg class="icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M12 2a10 10 0 100 20 10 10 0 000-20zm1 14h-2v2h2v-2zm0-8h-2v6h2V8z"/>
                </svg>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="field">
                <label for="username">口令</label>
                <div class="input">
                    <svg class="icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 12a5 5 0 100-10 5 5 0 000 10zm-8 9a8 8 0 0116 0H4z"/>
                    </svg>
                    <input id="username" name="username" type="text" autocomplete="username" required placeholder="输入口令">
                </div>
            </div>

            <div class="field">
                <label for="password">密钥</label>
                <div class="input">
                    <svg class="icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 17a2 2 0 100-4 2 2 0 000 4zm7-8a7 7 0 10-14 0v3h14V9z"/>
                    </svg>
                    <input id="password" name="password" type="password" autocomplete="current-password" required placeholder="输入密钥">
                </div>
            </div>

            <div class="field">
                <label for="auxiliary">证书</label>
                <div class="input">
                    <svg class="icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M6 2h9a2 2 0 012 2v11l-4-2-4 2-4-2v-9a2 2 0 012-2z"/>
                    </svg>
                    <input id="auxiliary" name="auxiliary" type="text" required placeholder="输入证书">
                </div>
            </div>

            <div class="field captcha-box">
                <label for="captcha">验证码</label>
                <div class="captcha-question" aria-live="polite">
                    <span><?= htmlspecialchars($_SESSION['captcha_question']) ?></span>
                    <span class="hint">请计算后输入</span>
                </div>

                <div class="input">
                    <svg class="icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M5 3h14a2 2 0 012 2v6a2 2 0 01-2 2h-6l-4 4v-4H5a2 2 0 01-2-2V5a2 2 0 012-2z"/>
                    </svg>
                    <input id="captcha" name="captcha" type="text" inputmode="numeric" pattern="[0-9]*" required placeholder="请输入计算结果">
                </div>

                <div class="captcha-actions">
                    <a class="btn btn-ghost link" href="?refresh=1" id="refreshLink">
                        <svg class="icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M12 6V3L8 7l4 4V8a4 4 0 110 8 4 4 0 01-4-4H6a6 6 0 1012 0 6 6 0 00-6-6z"/>
                        </svg>
                        刷新验证码
                    </a>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <svg class="icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M5 12l5 5L20 7" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/>
                </svg>
                登录
            </button>
            <div class="footer">
                <a target="_blank" href="https://beian.miit.gov.cn/"><p>皖ICP备2024038731号-2</p></a>
                <p>&copy; 2025 小笨蛋. 保留所有权利.</p>
            </div>
        </form>
    </main>
    <script>
        // Optional: smooth refresh without keeping ?refresh=1 in URL
        const refresh = document.getElementById('refreshLink');
        if (refresh) {
            refresh.addEventListener('click', function (e) {
                // allow normal navigation if user Ctrl/Meta clicks
                if (e.ctrlKey || e.metaKey) return;
                e.preventDefault();
                // navigate to refresh=1 to regenerate, server will redirect back
                const url = new URL(window.location.href);
                url.searchParams.set('refresh', '1');
                window.location.href = url.toString();
            });
        }
    </script>
</body>
</html>
