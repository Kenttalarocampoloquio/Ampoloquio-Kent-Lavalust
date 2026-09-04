<?php
/**
 * @var string $student_id
 * @var string $name
 * @var string $course
 * @var string $year
 * @var string $section
 * @var string $email
 * @var string $address
 * @var string $contact_number
 * @var string $hobbies 
 * @var array  $social_media
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ang aking Student Profile</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #0a0a0a;
            color: #fff;
            min-height: 100vh;
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
        header a {
            color: #666;
            text-decoration: none;
            font-size: 0.9rem;
        }
        header a:hover { color: #ff6b35; }
        main {
            max-width: 640px;
            margin: 48px auto;
            padding: 0 20px;
        }
        .name-block { margin-bottom: 32px; }
        .name-block h1 { font-size: 1.8rem; font-weight: 800; }
        .name-block p { color: #888; margin-top: 4px; font-size: 0.95rem; }
        .id-tag {
            display: inline-block;
            margin-top: 8px;
            background: #111;
            border: 1px solid #1a1a1a;
            border-radius: 4px;
            padding: 3px 10px;
            font-size: 0.8rem;
            color: #ff6b35;
            font-weight: 600;
        }
        .section-label {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #555;
            margin-bottom: 12px;
            margin-top: 28px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .info-item {
            background: #111;
            border: 1px solid #1a1a1a;
            border-radius: 8px;
            padding: 14px 18px;
        }
        .info-item.full { grid-column: 1 / -1; }
        .info-label {
            font-size: 0.72rem;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .info-value { font-size: 0.95rem; font-weight: 600; color: #fff; }
        .social-links { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 12px; }
        .social-links a {
            background: #111;
            border: 1px solid #1a1a1a;
            color: #fff;
            padding: 8px 18px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 600;
        }
        .social-links a:hover {
            border-color: #ff6b35;
            color: #ff6b35;
        }
        .back {
            display: inline-block;
            margin-top: 36px;
            color: #666;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .back:hover { color: #ff6b35; }
    </style>
</head>
<body>
    <header>
        <div class="logo">Student<span>&nbsp;Profile</span></div>
    </header>
    <main>
        <div class="name-block">
            <h1><?= $name ?></h1>
            <p><?= $course ?> — <?= $year ?>, Section <?= $section ?></p>
            <span class="id-tag"><?= $student_id ?></span>
        </div>

        <div class="section-label">Personal Information</div>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Email</div>
                <div class="info-value"><?= $email ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Contact Number</div>
                <div class="info-value"><?= $contact_number ?></div>
            </div>
            <div class="info-item full">
                <div class="info-label">Address</div>
                <div class="info-value"><?= $address ?></div>
            </div>
            <div class="info-item full">
                <div class="info-label">Hobbies</div>
                <div class="info-value"><?= $hobbies ?></div>
            </div>
        </div>

        <div class="section-label">Social Media</div>
        <div class="social-links">
            <?php foreach ($social_media as $platform => $link): ?>
                <a href="<?= $link ?>" target="_blank"><?= ucfirst($platform) ?></a>
            <?php endforeach; ?>
        </div>

        <a href="<?= site_url('student') ?>" class="back">← Back to Home</a>
    </main>
</body>
</html>
