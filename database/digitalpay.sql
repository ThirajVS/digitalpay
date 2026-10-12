-- DigitalPay academic database
-- All records below are fictional and must never be used for real payments.

CREATE DATABASE IF NOT EXISTS digitalpay
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE digitalpay;

CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(10) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  dob DATE NOT NULL,
  gender VARCHAR(32) NOT NULL,
  address VARCHAR(500) NOT NULL,
  course_or_department VARCHAR(120) NOT NULL,
  profile_pic VARCHAR(255) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT uq_users_email UNIQUE (email),
  CONSTRAINT uq_users_phone UNIQUE (phone),
  CONSTRAINT chk_users_phone CHECK (phone REGEXP '^[0-9]{10}$')
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS transactions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  transaction_type VARCHAR(50) NOT NULL,
  recipient VARCHAR(120) NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  status ENUM('Successful', 'Pending', 'Failed', 'Refunded') NOT NULL DEFAULT 'Pending',
  transaction_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  reference_number VARCHAR(32) NOT NULL,
  CONSTRAINT uq_transactions_reference UNIQUE (reference_number),
  CONSTRAINT chk_transactions_amount CHECK (amount > 0),
  CONSTRAINT fk_transactions_user FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE CASCADE,
  INDEX idx_transactions_user_date (user_id, transaction_date),
  INDEX idx_transactions_status (status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS beneficiaries (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(100) NOT NULL,
  account_identifier VARCHAR(100) NOT NULL,
  bank_name VARCHAR(120) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT uq_beneficiary_per_user UNIQUE (user_id, account_identifier),
  CONSTRAINT fk_beneficiaries_user FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE CASCADE,
  INDEX idx_beneficiaries_user (user_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS bills (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  bill_type VARCHAR(60) NOT NULL,
  provider VARCHAR(120) NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  due_date DATE NOT NULL,
  status ENUM('Paid', 'Pending', 'Overdue') NOT NULL DEFAULT 'Pending',
  CONSTRAINT chk_bills_amount CHECK (amount > 0),
  CONSTRAINT fk_bills_user FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE CASCADE,
  INDEX idx_bills_user_due (user_id, due_date),
  INDEX idx_bills_status (status)
) ENGINE=InnoDB;

INSERT INTO users (id, name, email, phone, password_hash, dob, gender, address, course_or_department, profile_pic) VALUES
  (1, 'Alex Demo', 'alex.demo@example.test', '9000000001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', '2003-04-18', 'Non-binary', '12 Fictional Avenue, Demo City', 'Computer Science', NULL),
  (2, 'Mira Fictional', 'mira.fictional@example.test', '9000000002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', '2002-11-09', 'Female', '44 Sample Street, Learning District', 'Information Technology', NULL),
  (3, 'Aarav Demo', 'aarav.demo@example.test', '9000000003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.', '2001-07-22', 'Male', '8 Academic Road, Practice Town', 'Commerce', NULL)
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO transactions (user_id, transaction_type, recipient, amount, status, transaction_date, reference_number) VALUES
  (1, 'Money Transfer', 'Aarav Demo', 2500.00, 'Successful', '2026-09-21 10:30:00', 'DP100001'),
  (1, 'Bill Payment', 'Northstar Electricity', 1840.00, 'Pending', '2026-09-20 15:15:00', 'DP100002'),
  (1, 'Mobile Recharge', 'Sample Mobile', 499.00, 'Successful', '2026-09-19 08:40:00', 'DP100003'),
  (1, 'Shopping', 'Demo Market', 3299.00, 'Failed', '2026-09-18 19:20:00', 'DP100004'),
  (1, 'Refund', 'Demo Market Refund', 799.00, 'Refunded', '2026-09-17 12:05:00', 'DP100005'),
  (2, 'Money Transfer', 'Mira Fictional', 6200.00, 'Successful', '2026-09-16 17:10:00', 'DP100006'),
  (2, 'Bill Payment', 'Bluewave Water Demo', 920.00, 'Successful', '2026-09-15 09:45:00', 'DP100007'),
  (3, 'Shopping', 'Fictional Books', 1450.00, 'Successful', '2026-09-14 14:00:00', 'DP100008')
ON DUPLICATE KEY UPDATE recipient = VALUES(recipient), amount = VALUES(amount), status = VALUES(status);

INSERT INTO beneficiaries (user_id, name, account_identifier, bank_name) VALUES
  (1, 'Aarav Demo', 'DP-USER-2041', 'DigitalPay Demo Wallet'),
  (1, 'Northstar Internet', 'DP-BILL-6812', 'Academic Biller Network'),
  (1, 'Mira Fictional', 'DP-USER-4930', 'DigitalPay Demo Wallet'),
  (2, 'Alex Demo', 'DP-USER-1082', 'DigitalPay Demo Wallet')
ON DUPLICATE KEY UPDATE name = VALUES(name), bank_name = VALUES(bank_name);

INSERT INTO bills (user_id, bill_type, provider, amount, due_date, status) VALUES
  (1, 'Electricity', 'Northstar Electricity', 1840.00, '2026-09-24', 'Pending'),
  (1, 'Internet', 'Northstar Internet', 899.00, '2026-09-25', 'Pending'),
  (1, 'Insurance', 'Sample Secure Insurance', 4200.00, '2026-09-30', 'Pending'),
  (2, 'Water', 'Bluewave Water Demo', 920.00, '2026-09-15', 'Paid'),
  (3, 'Subscription', 'Fictional Stream', 299.00, '2026-09-10', 'Paid');
