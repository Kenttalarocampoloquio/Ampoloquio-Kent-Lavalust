<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Student Home' ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #0a0a0a;
            color: #fff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        header {
            padding: 20px 40px;
            background: #0a0a0a;
            border-bottom: 1px solid #1a1a1a;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo { font-weight: 800; font-size: 1.2rem; }
        .logo span { color: #ff6b35; }
        main {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px 40px;
            max-width: 600px;
            margin: 0 auto;
            width: 100%;
        }
        h1 {
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 16px;
            line-height: 1.2;
        }
        p {
            color: #888;
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 32px;
        }
        a.btn {
            display: inline-block;
            background: #ff6b35;
            color: #fff;
            padding: 12px 28px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
        }
        a.btn:hover { background: #e85d2a; }
    </style>
</head>
<body>
    <header>
        <div class="logo">Student<span>&nbsp;Home</span></div>
    </header>
    <main>
        <h1>Mabuhay Mahaltana!<br>Welcome sa aking student page.</h1>
        <p>Click ang nakahighlight na link para ma-view ang aking student profile.</p>
        <a href="<?= site_url('student/profile') ?>" class="btn">View Profile</a>
    </main>
</body>
</html>
