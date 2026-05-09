<?php
declare(strict_types=1);

function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function vnd(float $v): string
{
    return number_format($v, 0, '.', ',') . ' VND';
}

// Basic bootstrap for dashboard pages.
// This file must never fatally fail without showing an error (HTTP 500 is opaque on hosting).
try {
    $baseDir = realpath(__DIR__ . '/..');
    if ($baseDir === false) {
        throw new \RuntimeException('Cannot resolve project base directory.');
    }

    $configPath = $baseDir . '/config/config.php';
    if (!file_exists($configPath)) {
        throw new \RuntimeException('Missing config at: ' . $configPath);
    }

    $config = require $configPath;
    date_default_timezone_set((string)($config['app']['timezone'] ?? 'UTC'));
    $debug = (bool)($config['app']['debug'] ?? false);

    $dbClassPath = $baseDir . '/src/Database.php';
    if (!file_exists($dbClassPath)) {
        throw new \RuntimeException(
            'Missing `src/Database.php` on hosting. Expected at: ' . $dbClassPath
            . '. Please upload the full project so folders `public/`, `src/`, `config/` are at the same level.'
        );
    }

    require_once $baseDir . '/src/Database.php';
    require_once $baseDir . '/src/Repositories/ProviderRepository.php';
    require_once $baseDir . '/src/Repositories/TldRepository.php';
    require_once $baseDir . '/src/Repositories/PriceHistoryRepository.php';
    require_once $baseDir . '/src/Repositories/AlertRuleRepository.php';
    require_once $baseDir . '/src/Repositories/AlertsLogRepository.php';
    require_once $baseDir . '/src/Services/TelegramService.php';

    $db = new Database($config);
    $pdo = $db->pdo();

    $providerRepo = new ProviderRepository($pdo);
    $tldRepo = new TldRepository($pdo);
    $priceRepo = new PriceHistoryRepository($pdo);
    $alertsRepo = new AlertsLogRepository($pdo);
} catch (\Throwable $e) {
    http_response_code(500);
    error_log('[DomainPriceMonitor][dashboard] bootstrap failed: '
        . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());

    $title = 'Domain Price Monitor — Lỗi';
    $msg = $e->getMessage();
    $msg = is_string($msg) ? $msg : 'Unknown error';
    $msg = substr($msg, 0, 900);

    $extra = '';
    if (isset($debug) && $debug) {
        $extra = '<pre style="white-space:pre-wrap;word-break:break-word;margin:8px 0 0;color:#374151;background:#f3f4f6;padding:10px;border-radius:10px">'
            . h($e->getTraceAsString())
            . '</pre>';
    }

    $content = '<div class="card">'
        . '<div style="font-size:18px;font-weight:800;margin-bottom:6px">Không thể kết nối/khởi tạo dashboard</div>'
        . '<div class="muted" style="margin-bottom:10px">Thông báo lỗi (để debug):</div>'
        . '<div class="pill bad" style="white-space:normal;word-break:break-word">' . h($msg) . '</div>'
        . $extra
        . '<div class="muted" style="margin-top:12px">Kiểm tra `config/config.php` đúng DB name/host/user và đã import `database/schema.sql` chưa.</div>'
        . '</div>';

    require __DIR__ . '/_layout.php';
    exit;
}

