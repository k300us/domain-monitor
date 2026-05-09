# Domain Price Monitor (PHP + MySQL)

Kiến trúc đúng theo sơ đồ `domain_monitor_architecture.svg`:

- **Cron Job** gọi `scraper/run_all.php` mỗi ngày
- **Scraper**: `scraper/pavietnam.php`, `scraper/inet.php`, `scraper/matbao.php` (dễ mở rộng thêm đối thủ)
- **MySQL**: `providers`, `tlds`, `price_history`, `alert_rules`, `alerts_log`
- **Dashboard (PHP)**: `public/` (bảng so sánh, lịch sử, cảnh báo)
- **Alert Engine (PHP)**: `alert_engine/run.php` (phát hiện thay đổi giá)
- **Telegram Bot**: `telegram_bot/send.php` (gửi alert)

## 1) Yêu cầu

- PHP 8.1+ (khuyên dùng 8.2/8.3)
- MySQL 8+ (hoặc MariaDB tương đương)
- Extension PHP: `pdo_mysql`, `curl`

## 2) Cấu hình
Hiện tại `config/config.php` đang `require` từ `config/config.example.php`.

Vì vậy bạn chỉ cần sửa các giá trị trong `config/config.example.php`:
- `db.host`, `db.port`, `db.name`, `db.user`, `db.pass`
- `telegram.enabled`, `telegram.bot_token`, `telegram.chat_id` (nếu dùng Telegram)
- `app.debug` (bật `true` để xem lỗi chi tiết trên dashboard khi debug hosting)

## 3) Tạo database

Tạo DB rỗng, rồi import schema:

```bash
mysql -u root -p < database/schema.sql
```

Sau đó chỉnh lại `db.name` trong `config/config.example.php` (mặc định `domain_monitor`).

## 4) Chạy scraper (thủ công)

```bash
php scraper/run_all.php
```

> Lưu ý: các scraper hiện để **stub** (demo) trả giá giả lập để bạn thấy luồng hoạt động; phần “scrape thật” bạn chỉ cần thay code trong từng file scraper.

## 5) Chạy alert engine

```bash
php alert_engine/run.php
```

## 6) Chạy dashboard

Trong thư mục project:

```bash
php -S localhost:8000 -t public
```

Mở `http://localhost:8000`.

## 7) Cron mẫu

Ví dụ chạy 2:00 AM mỗi ngày:

```cron
0 2 * * * /usr/bin/php /path/to/project/scraper/run_all.php >> /path/to/project/storage/cron.log 2>&1
```

## Mở rộng thêm nhà cung cấp

- Thêm `scraper/<provider>.php`
- Thêm provider vào bảng `providers`
- Gọi thêm trong `scraper/run_all.php`

