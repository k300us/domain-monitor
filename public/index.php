<?php
declare(strict_types=1);

require_once __DIR__ . '/_app.php';

try {
    $rows = $priceRepo->latestPricesForActiveTlds();
    $providers = $providerRepo->all();
} catch (\Throwable $e) {
    http_response_code(500);
    error_log('[DomainPriceMonitor][dashboard] index load failed: '
        . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    $title = 'Domain Price Monitor — Lỗi';
    $msg = $e->getMessage();
    $msg = is_string($msg) ? $msg : 'Unknown error';
    $msg = substr($msg, 0, 900);
    $content = '<div class="card">'
        . '<div style="font-size:18px;font-weight:800;margin-bottom:6px">Lỗi khi tải dữ liệu dashboard</div>'
        . '<div class="pill bad" style="white-space:normal;word-break:break-word">' . h($msg) . '</div>'
        . '<div class="muted" style="margin-top:12px">Gợi ý: kiểm tra DB/schema và bảng `price_history`, `providers`, `tlds`.</div>'
        . '</div>';
    require __DIR__ . '/_layout.php';
    exit;
}

// Build pivot: tld => provider_code => price
$pivot = [];
$scrapedAtBy = [];
foreach ($rows as $r) {
    $tld = (string)$r['tld'];
    $pcode = (string)$r['provider_code'];
    $pivot[$tld][$pcode] = (float)$r['price'];
    $scrapedAtBy[$tld][$pcode] = (string)$r['scraped_at'];
}

ob_start();
?>
<div class="card">
  <div style="display:flex;justify-content:space-between;gap:12px;align-items:baseline;flex-wrap:wrap">
    <div>
      <div style="font-size:18px;font-weight:700">Bảng so sánh giá (mới nhất)</div>
      <div class="muted">Dữ liệu lấy từ `price_history` (bản ghi mới nhất theo provider + tld).</div>
    </div>
  </div>

  <div style="overflow:auto;margin-top:12px">
    <table>
      <thead>
        <tr>
          <th>TLD</th>
          <?php foreach ($providers as $p): ?>
            <th><?= h((string)$p['name']) ?></th>
          <?php endforeach; ?>
          <th>Chênh lệch (min → max)</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($pivot as $tld => $pricesByProvider): ?>
        <?php
          $prices = array_values($pricesByProvider);
          $min = $prices ? min($prices) : null;
          $max = $prices ? max($prices) : null;
        ?>
        <tr>
          <td>
            <div style="font-weight:700"><?= h($tld) ?></div>
            <div class="muted"><a href="/history.php?tld=<?= urlencode($tld) ?>">xem lịch sử</a></div>
          </td>
          <?php foreach ($providers as $p): ?>
            <?php $pcode = (string)$p['code']; ?>
            <td>
              <?php if (isset($pricesByProvider[$pcode])): ?>
                <div><?= h(vnd((float)$pricesByProvider[$pcode])) ?></div>
                <div class="muted"><?= h((string)($scrapedAtBy[$tld][$pcode] ?? '')) ?></div>
              <?php else: ?>
                <span class="pill">n/a</span>
              <?php endif; ?>
            </td>
          <?php endforeach; ?>
          <td>
            <?php if ($min !== null && $max !== null): ?>
              <?php $diff = $max - $min; ?>
              <span class="pill <?= $diff <= 0 ? 'good' : '' ?>"><?= h(vnd((float)$min)) ?> → <?= h(vnd((float)$max)) ?></span>
              <div class="muted">Δ <?= h(vnd((float)$diff)) ?></div>
            <?php else: ?>
              <span class="pill">n/a</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php
$content = ob_get_clean();
$title = 'Domain Price Monitor — Dashboard';
require __DIR__ . '/_layout.php';

