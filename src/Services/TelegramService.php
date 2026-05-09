<?php
declare(strict_types=1);

final class TelegramService
{
    /** @var array<string,mixed> */
    private $telegramConfig;

    /**
     * @param array<string,mixed> $telegramConfig
     */
    public function __construct(array $telegramConfig)
    {
        $this->telegramConfig = $telegramConfig;
    }

    public function enabled(): bool
    {
        return (bool)($this->telegramConfig['enabled'] ?? false);
    }

    public function sendMessage(string $text): bool
    {
        if (!$this->enabled()) {
            return false;
        }

        $botToken = (string)($this->telegramConfig['bot_token'] ?? '');
        $chatId = (string)($this->telegramConfig['chat_id'] ?? '');
        $apiBase = rtrim((string)($this->telegramConfig['api_base'] ?? 'https://api.telegram.org'), '/');

        if ($botToken === '' || $chatId === '') {
            return false;
        }

        $url = $apiBase . "/bot{$botToken}/sendMessage";
        $payload = http_build_query([
            'chat_id' => $chatId,
            'text' => $text,
            'disable_web_page_preview' => true,
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT => 10,
        ]);
        $resp = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $resp !== false && $httpCode >= 200 && $httpCode < 300;
    }
}

