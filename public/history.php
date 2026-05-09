<?php
declare(strict_types=1);

require_once __DIR__ . '/_app.php';

$tlds = $tldRepo->active();
$providers = $providerRepo->all();

$selectedTld = (string)($_GET['tld'] ?? ($tlds[0]['tld'] ?? '.com'));
$selectedProvider = (string)($_GET['provider'] ?? '');

try {
    $history = $priceRepo->history($selectedTld, $selectedProvider !== '' ? $selectedProvider : null, 200);
} catch (\Throwable $e) {
    http_response_code(500);
    error_log('[DomainPriceMonitor][dashboard] history load failed: '
        . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    $title = 'Domain Price Monitor — Lỗi';
    $msg = $e->getMessage();
    $msg = is_string($msg) ? $msg : 'Unknown error';
    $msg = substr($msg, 0, 900);
    $content = '<div class="card">'
        . '<div style="font-size:18px;font-weight:800;margin-bottom:6px">Lỗi khi tải lịch sử</div>'
        . '<div class="pill bad" style="white-space:normal;word-break:break-word">' . h($msg) . '</div>'
        . '<div class="muted" style="margin-top:12px">Gợi ý: kiểm tra DB/schema và giá trị `tlds` trong bảng `tlds`.</div>'
        . '</div>';
    require __DIR__ . '/_layout.php';
    exit;
}

ob_start();
?>
<div class="row">
  <div class="card">
    <div style="font-size:18px;font-weight:700">Lịch sử giá</div>
    <div class="muted">Chọn TLD và (tuỳ chọn) nhà cung cấp.</div>

    <form method="get" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap;margin-top:12px">
      <div>
        <div class="muted">TLD</div>
        <select name="tld">
          <?php foreach ($tlds as $t): ?>
            <?php $tld = (string)$t['tld']; ?>
            <option value="<?= h($tld) ?>" <?= $tld === $selectedTld ? 'selected' : '' ?>><?= h($tld) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <div class="muted">Provider</div>
        <select name="provider">
          <option value="">(tất cả)</option>
          <?php foreach ($providers as $p): ?>
            <option value="<?= h((string)$p['code']) ?>" <?= (string)$p['code'] === $selectedProvider ? 'selected' : '' ?>>
              <?= h((string)$p['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <button class="btn" type="submit">Xem</button>
    </form>

    <div style="overflow:auto;margin-top:12px">
      <table>
        <thead>
          <tr>
            <th>Thời điểm</th>
            <th>Provider</th>
            <th>Giá</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($history as $r): ?>
            <tr>
              <td><?= h((string)$r['scraped_at']) ?></td>
              <td><?= h((string)$r['provider_name']) ?></td>
              <td><?= h(vnd((float)$r['price'])) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if ($history === []): ?>
            <tr><td colspan="3"><span class="pill">Chưa có dữ liệu</span></td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card">
    <div style="font-size:18px;font-weight:700">Gợi ý</div>
    <ul>
      <li>Chạy `php scraper/run_all.php` để ghi thêm dữ liệu.</li>
      <li>Chạy `php alert_engine/run.php` để phát hiện thay đổi giá và log cảnh báo.</li>
    </ul>
  </div>
</div>
<?php
$content = ob_get_clean();
$title = 'Domain Price Monitor — Lịch sử';
require __DIR__ . '/_layout.php';

