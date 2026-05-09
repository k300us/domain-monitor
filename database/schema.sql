-- Domain Price Monitor schema (MySQL 8+)
CREATE DATABASE IF NOT EXISTS domain_monitor
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE domain_monitor;

CREATE TABLE IF NOT EXISTS providers (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  code VARCHAR(50) NOT NULL,
  name VARCHAR(255) NOT NULL,
  base_url VARCHAR(255) NULL,
  is_primary TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_providers_code (code)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS tlds (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  tld VARCHAR(50) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_tlds_tld (tld)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS price_history (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  provider_id INT UNSIGNED NOT NULL,
  tld_id INT UNSIGNED NOT NULL,
  price DECIMAL(12,2) NOT NULL,
  currency VARCHAR(10) NOT NULL DEFAULT 'VND',
  raw_source MEDIUMTEXT NULL,
  scraped_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_price_history_pt (provider_id, tld_id, scraped_at),
  CONSTRAINT fk_price_history_provider FOREIGN KEY (provider_id) REFERENCES providers(id),
  CONSTRAINT fk_price_history_tld FOREIGN KEY (tld_id) REFERENCES tlds(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS alert_rules (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  provider_id INT UNSIGNED NOT NULL,
  tld_id INT UNSIGNED NOT NULL,
  percent_threshold DECIMAL(6,2) NOT NULL DEFAULT 5.00,
  is_enabled TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_alert_rules_provider_tld (provider_id, tld_id),
  CONSTRAINT fk_alert_rules_provider FOREIGN KEY (provider_id) REFERENCES providers(id),
  CONSTRAINT fk_alert_rules_tld FOREIGN KEY (tld_id) REFERENCES tlds(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS alerts_log (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  rule_id INT UNSIGNED NOT NULL,
  provider_id INT UNSIGNED NOT NULL,
  tld_id INT UNSIGNED NOT NULL,
  old_price DECIMAL(12,2) NOT NULL,
  new_price DECIMAL(12,2) NOT NULL,
  percent_change DECIMAL(8,3) NOT NULL,
  message TEXT NOT NULL,
  sent_to_telegram TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_alerts_log_created_at (created_at),
  CONSTRAINT fk_alerts_log_rule FOREIGN KEY (rule_id) REFERENCES alert_rules(id),
  CONSTRAINT fk_alerts_log_provider FOREIGN KEY (provider_id) REFERENCES providers(id),
  CONSTRAINT fk_alerts_log_tld FOREIGN KEY (tld_id) REFERENCES tlds(id)
) ENGINE=InnoDB;

-- Seed providers
INSERT INTO providers (code, name, base_url, is_primary) VALUES
  ('pavietnam', 'PA Việt Nam', 'https://pavietnam.vn', 1),
  ('inet', 'iNet', 'https://inet.vn', 0),
  ('matbao', 'Mắt Bão', 'https://matbao.net', 0)
ON DUPLICATE KEY UPDATE name=VALUES(name), base_url=VALUES(base_url), is_primary=VALUES(is_primary), updated_at=CURRENT_TIMESTAMP;

-- Seed TLDs (you can edit these)
INSERT INTO tlds (tld, is_active) VALUES
  ('.com', 1),
  ('.net', 1),
  ('.vn', 1),
  ('.com.vn', 1)
ON DUPLICATE KEY UPDATE is_active=VALUES(is_active), updated_at=CURRENT_TIMESTAMP;

-- Seed alert rules (default 5% change)
INSERT INTO alert_rules (provider_id, tld_id, percent_threshold, is_enabled)
SELECT p.id, t.id, 5.00, 1
FROM providers p
CROSS JOIN tlds t
WHERE t.is_active = 1
ON DUPLICATE KEY UPDATE percent_threshold=VALUES(percent_threshold), is_enabled=VALUES(is_enabled), updated_at=CURRENT_TIMESTAMP;

