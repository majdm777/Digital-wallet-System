-- Create the database
CREATE DATABASE IF NOT EXISTS wallet_db;
USE wallet_db;

-- =========================
-- Table: super_admins
-- =========================
CREATE TABLE IF NOT EXISTS admins (
    admin_id INT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- =========================
-- Table: admins
-- =========================
CREATE TABLE IF NOT EXISTS manager (
    manager_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- Table: users
-- =========================
CREATE TABLE IF NOT EXISTS users (
    user_id INT NOT NULL PRIMARY KEY,
    FirstName VARCHAR(100) NOT NULL,
    LastName VARCHAR(100) NOT NULL,
    Email VARCHAR(150) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    Nationality VARCHAR(255) NOT NULL,
    Birthday DATE NOT NULL,                   
    Transactions_count INT DEFAULT 0,
    Phone VARCHAR(50) NOT NULL UNIQUE,         
    Income_source VARCHAR(100),
    Type_Of_Account VARCHAR(255) NOT NULL,
    Address VARCHAR(254) NOT NULL        
   );


-- =========================
-- Table: wallets
-- =========================
CREATE TABLE IF NOT EXISTS wallets (
    wallet_id INT AUTO_INCREMENT PRIMARY KEY,
    User_id INT NOT NULL,
    balance DECIMAL(15,2) DEFAULT 0,
    currency VARCHAR(10) DEFAULT 'USD',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (User_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE transfers (
    transfer_id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    -- description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(user_id),
    FOREIGN KEY (receiver_id) REFERENCES users(user_id)
);

CREATE TABLE deposits (
    deposit_id INT AUTO_INCREMENT PRIMARY KEY,
    User_id INT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    description VARCHAR(255),
    -- status ENUM('pending','accepted','rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (User_id) REFERENCES user(user_id)
);
CREATE TABLE withdrawals (
    withdrawal_id INT AUTO_INCREMENT PRIMARY KEY,
    User_id INT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    description VARCHAR(255),
    status ENUM('pending','accepted','rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (User-id) REFERENCES users(user_id)
);

-- =====================================================
-- PROCEDURE: transfer_money
-- Description: Transfers money between two users instantly.
-- Inputs: sender_user, receiver_user, amount, description
-- =====================================================

DELIMITER $$

CREATE PROCEDURE transfer_money(
    IN sender_user INT,
    IN receiver_user INT,
    IN amount DECIMAL(15,2),
    IN description VARCHAR(255)
)
BEGIN
    DECLARE sender_wallet INT;
    DECLARE receiver_wallet INT;

    -- Get wallet IDs
    SELECT wallet_id INTO sender_wallet FROM wallets WHERE user_id = sender_user;
    SELECT wallet_id INTO receiver_wallet FROM wallets WHERE user_id = receiver_user;

    -- Insert transfer
    INSERT INTO transfers (sender_id, receiver_id, amount, description)
    VALUES (sender_user, receiver_user, amount, description);

    -- Update balances
    UPDATE wallets SET balance = balance - amount WHERE wallet_id = sender_wallet;
    UPDATE wallets SET balance = balance + amount WHERE wallet_id = receiver_wallet;

    -- Update transaction count
    UPDATE users SET transactions_count = transactions_count + 1 WHERE user_id = sender_user;
    UPDATE users SET transactions_count = transactions_count + 1 WHERE user_id = receiver_user;

END$$

DELIMITER ;


-- =====================================================
-- PROCEDURE: request_deposit
-- Description: Creates a pending deposit request for admin approval.
-- Inputs: user_id, amount, description
-- =====================================================

DELIMITER $$

CREATE PROCEDURE deposit(
    IN User_id INT,
    IN amount DECIMAL(15,2),
    IN description VARCHAR(255)
)
BEGIN
    -- DECLARE wallet INT;

    -- Get wallet ID
    -- SELECT wallet_id INTO wallet FROM wallets WHERE user_id = user_id;

    -- Insert pending deposit
    INSERT INTO deposits (User_id, amount, description)
    VALUES (user_id, amount, description);
    UPDATE users SET transactions_count = transactions_count + 1 WHERE user_id = User_id;
    UPDATE wallets SET balance = balance + amount WHERE user_id = User_id;
END$$

DELIMITER ;


-- =====================================================
-- PROCEDURE: approve_deposit
-- Description: Admin accepts a pending deposit, updates wallet balance.
-- Inputs: dep_id (deposit_id)
-- =====================================================

DELIMITER $$

-- CREATE PROCEDURE approve_deposit(
--     IN dep_id INT
-- )
-- BEGIN
--     DECLARE wallet INT;
--     DECLARE amount DECIMAL(15,2);
--     DECLARE usr INT;

--     -- Get wallet & amount
--     SELECT wallet_id, amount INTO wallet, amount 
--     FROM deposits WHERE deposit_id = dep_id;

--     -- Add money
--     UPDATE wallets SET balance = balance + amount WHERE wallet_id = wallet;

--     -- Mark deposit as accepted
--     UPDATE deposits SET status = 'accepted' WHERE deposit_id = dep_id;

--     -- Update user transaction count
--     SELECT user_id INTO usr FROM wallets WHERE wallet_id = wallet;
--     UPDATE users SET transactions_count = transactions_count + 1 WHERE user_id = usr;

-- END$$

DELIMITER ;


-- =====================================================
-- PROCEDURE: request_withdrawal
-- Description: Creates a pending withdrawal request for admin approval.
-- Inputs: user_id, amount, description
-- =====================================================

DELIMITER $$

CREATE PROCEDURE request_withdrawal(
    IN user_id INT,
    IN amount DECIMAL(15,2),
    IN description VARCHAR(255)
)
BEGIN
    DECLARE wallet INT;

    -- Get wallet
    SELECT wallet_id INTO wallet FROM wallets WHERE User_id = user_id;

    -- Insert pending withdrawal
    INSERT INTO withdrawals (User_id, amount, description, status)
    VALUES (user_id, amount, description, 'pending');
END$$

DELIMITER ;


-- =====================================================
-- PROCEDURE: approve_withdrawal
-- Description: Admin confirms a withdrawal and deducts wallet balance.
-- Inputs: user-id
-- =====================================================

DELIMITER $$

CREATE PROCEDURE approve_withdrawal(
    IN userID INT
)
BEGIN
    DECLARE wallet INT;
    DECLARE amount DECIMAL(15,2);
    

    -- Get wallet & amount
    SELECT  amount INTO amount 
    FROM withdrawals WHERE User_id =userID ;

    -- Deduct money
    UPDATE wallets SET balance = balance - amount WHERE User_id = userID;

    -- Mark withdrawal as accepted
    UPDATE withdrawals SET status = 'accepted' WHERE User_id = userID;

    -- Update user transaction count
    -- SELECT user_id INTO usr FROM wallets WHERE wallet_id = wallet;
    UPDATE users SET transactions_count = transactions_count + 1 WHERE user_id = userID;

END$$

DELIMITER ;