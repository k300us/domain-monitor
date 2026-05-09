<?php
declare(strict_types=1);

require_once __DIR__ . '/_app.php';

try {
    $alerts = $alertsRepo->latest(80);
} catch (\Throwable $e) {
    http_response_code(500);
    error_log('[DomainPriceMonitor][dashboard] alerts load failed: '
        . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    $title = 'Domain Price Monitor — Lỗi';
    $msg = $e->getMessage();
    $msg = is_string($msg) ? $msg : 'Unknown error';
    $msg = substr($msg, 0, 900);
    $content = '<div class="card">'
        . '<div style="font-size:18px;font-weight:800;margin-bottom:6px">Lỗi khi tải cảnh báo</div>'
        . '<div class="pill bad" style="white-space:normal;word-break:break-word">' . h($msg) . '</div>'
        . '</div>';
    require __DIR__ . '/_layout.php';
    exit;
}

ob_start();
?>
<div class="card">
  <div style="font-size:18px;font-weight:700">Cảnh báo (log)</div>
  <div class="muted">Các alert được tạo bởi `alert_engine/run.php`.</div>

  <div style="overflow:auto;margin-top:12px">
    <table>
      <thead>
        <tr>
          <th>Thời điểm</th>
          <th>Provider</th>
          <th>TLD</th>
          <th>Thay đổi</th>
          <th>Telegram</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($alerts as $a): ?>
          <?php
            $pct = (float)$a['percent_change'];
            $cls = $pct >= 0 ? 'good' : 'bad';
          ?>
          <tr>
            <td><?= h((string)$a['created_at']) ?></td>
            <td><?= h((string)$a['provider_name']) ?></td>
            <td><?= h((string)$a['tld']) ?></td>
            <td>
              <span class="pill <?= $cls ?>"><?= h(number_format($pct, 3)) ?>%</span>
              <div class="muted"><?= h(vnd((float)$a['old_price'])) ?> → <?= h(vnd((float)$a['new_price'])) ?></div>
            </td>
            <td><?= ((int)$a['sent_to_telegram'] === 1) ? '<span class="pill good">sent</span>' : '<span class="pill">no</span>' ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if ($alerts === []): ?>
          <tr><td colspan="5"><span class="pill">Chưa có cảnh báo</span></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php
$content = ob_get_clean();
$title = 'Domain Price Monitor — Cảnh báo';
require __DIR__ . '/_layout.php';

