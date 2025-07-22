-- Sample database schema based on the original system
-- This creates the basic tables needed for the system to work

CREATE DATABASE IF NOT EXISTS wealth_creation_erp;
USE wealth_creation_erp;

-- Staff table
CREATE TABLE IF NOT EXISTS staffs (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    department VARCHAR(50) NOT NULL,
    level VARCHAR(20) DEFAULT 'staff',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Other staff table
CREATE TABLE IF NOT EXISTS staffs_others (
    id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    department VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Accounts table
CREATE TABLE IF NOT EXISTS accounts (
    acct_id VARCHAR(20) PRIMARY KEY,
    acct_desc VARCHAR(100) NOT NULL,
    acct_alias VARCHAR(50),
    acct_table_name VARCHAR(50) NOT NULL,
    active ENUM('Yes', 'No') DEFAULT 'Yes',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Main transaction table
CREATE TABLE IF NOT EXISTS account_general_transaction_new (
    id VARCHAR(20) PRIMARY KEY,
    date_of_payment DATE NOT NULL,
    ticket_category VARCHAR(50),
    transaction_desc TEXT NOT NULL,
    receipt_no VARCHAR(20) UNIQUE NOT NULL,
    amount_paid DECIMAL(15,2) NOT NULL,
    remitting_id VARCHAR(20),
    remitting_staff VARCHAR(100),
    posting_officer_id VARCHAR(20) NOT NULL,
    posting_officer_name VARCHAR(100) NOT NULL,
    posting_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    leasing_post_status VARCHAR(20) DEFAULT '',
    approval_status VARCHAR(20) DEFAULT '',
    verification_status VARCHAR(20) DEFAULT '',
    debit_account VARCHAR(20) NOT NULL,
    credit_account VARCHAR(20) NOT NULL,
    payment_category VARCHAR(50) NOT NULL,
    no_of_tickets INT DEFAULT NULL,
    plate_no VARCHAR(20) DEFAULT NULL,
    sticker_no VARCHAR(20) DEFAULT NULL,
    no_of_nights INT DEFAULT NULL,
    no_of_days INT DEFAULT NULL,
    remit_id VARCHAR(20) DEFAULT '',
    income_line VARCHAR(50) NOT NULL,
    it_status VARCHAR(20) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cash remittance table
CREATE TABLE IF NOT EXISTS cash_remittance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    remit_id VARCHAR(20) UNIQUE NOT NULL,
    remitting_officer_id VARCHAR(20) NOT NULL,
    category VARCHAR(50) NOT NULL,
    amount_paid DECIMAL(15,2) NOT NULL,
    date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Till table (example)
CREATE TABLE IF NOT EXISTS till_account (
    id VARCHAR(20) PRIMARY KEY,
    acct_id VARCHAR(20) NOT NULL,
    date DATE NOT NULL,
    receipt_no VARCHAR(20) NOT NULL,
    trans_desc TEXT NOT NULL,
    debit_amount DECIMAL(15,2) DEFAULT 0,
    credit_amount DECIMAL(15,2) DEFAULT 0,
    balance DECIMAL(15,2) DEFAULT 0,
    approval_status VARCHAR(20) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Car park revenue table (example)
CREATE TABLE IF NOT EXISTS carpark_revenue (
    id VARCHAR(20) PRIMARY KEY,
    acct_id VARCHAR(20) NOT NULL,
    date DATE NOT NULL,
    receipt_no VARCHAR(20) NOT NULL,
    trans_desc TEXT NOT NULL,
    debit_amount DECIMAL(15,2) DEFAULT 0,
    credit_amount DECIMAL(15,2) DEFAULT 0,
    balance DECIMAL(15,2) DEFAULT 0,
    approval_status VARCHAR(20) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample data
INSERT IGNORE INTO staffs (user_id, full_name, department, level) VALUES
(1, 'John Doe', 'Wealth Creation', 'staff'),
(2, 'Jane Smith', 'Accounts', 'manager'),
(3, 'Bob Johnson', 'Wealth Creation', 'staff');

INSERT IGNORE INTO staffs_others (id, full_name, department) VALUES
(1, 'Alice Brown', 'Security'),
(2, 'Charlie Davis', 'Maintenance'),
(3, 'Eva Wilson', 'Cleaning');

INSERT IGNORE INTO accounts (acct_id, acct_desc, acct_alias, acct_table_name) VALUES
('10103', 'Account Till', 'till', 'till_account'),
('40001', 'Car Park Revenue', 'carpark', 'carpark_revenue'),
('40002', 'Loading Revenue', 'loading', 'loading_revenue'),
('40003', 'Hawkers Revenue', 'hawkers', 'hawkers_revenue');

-- Sample remittance data
INSERT IGNORE INTO cash_remittance (remit_id, remitting_officer_id, category, amount_paid, date) VALUES
('REM001', '1', 'Other Collection', 50000.00, CURDATE());