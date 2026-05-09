<?php
declare(strict_types=1);

/** @var string $title */
/** @var string $content */
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= h($title) ?></title>
  <style>
    :root { color-scheme: light; }
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; margin: 24px; color: #1f2937; }
    a { color: #0f62fe; text-decoration: none; }
    a:hover { text-decoration: underline; }
    .nav { display:flex; gap: 14px; margin-bottom: 18px; }
    .card { border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px; background: #fff; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 10px 8px; border-bottom: 1px solid #e5e7eb; text-align: left; vertical-align: top; }
    th { font-size: 12px; text-transform: uppercase; letter-spacing: .04em; color: #6b7280; }
    .muted { color: #6b7280; font-size: 12px; }
    .pill { display:inline-block; padding: 2px 8px; border-radius: 999px; font-size: 12px; background: #f3f4f6; }
    .good { background:#ecfdf5; color:#065f46; }
    .bad { background:#fef2f2; color:#991b1b; }
    .row { display:flex; gap: 12px; flex-wrap: wrap; }
    .row > .card { flex: 1 1 420px; }
    input, select { padding: 8px; border-radius: 8px; border: 1px solid #d1d5db; }
    .btn { padding: 8px 12px; border-radius: 10px; border: 1px solid #d1d5db; background: #111827; color: #fff; cursor: pointer; }
  </style>
</head>
<body>
  <div class="nav">
    <a href="/">So sánh</a>
    <a href="/history.php">Lịch sử</a>
    <a href="/alerts.php">Cảnh báo</a>
  </div>

  <?= $content ?>

  <p class="muted" style="margin-top:18px">
    Tip: chạy `php scraper/run_all.php` để có dữ liệu mới nhất.
  </p>
</body>
</html>

