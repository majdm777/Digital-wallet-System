-- Create the database
CREATE DATABASE IF NOT EXISTS wallet_db;
USE wallet_db;

-- =========================
-- Table: admins (Super Admins)
-- =========================
CREATE TABLE IF NOT EXISTS admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- =========================
-- Table: managers
-- =========================
CREATE TABLE IF NOT EXISTS managers (
    manager_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    admin_id INT NOT NULL,  
    FOREIGN KEY (admin_id) REFERENCES admins(admin_id)
);


-- =========================
-- Table: users (Core Wallet Users)
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
-- Relationship: users (1) -- (1) wallets
-- =========================
CREATE TABLE IF NOT EXISTS wallets (
    wallet_id INT AUTO_INCREMENT PRIMARY KEY,
    User_id INT NOT NULL UNIQUE,
    balance DECIMAL(15,2) DEFAULT 0,
    currency VARCHAR(10) DEFAULT 'USD',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (User_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- =========================
-- Table: transfers
-- Relationship: users (1) -- (N) transfers (as sender/receiver)
-- =========================
CREATE TABLE IF NOT EXISTS transfers (
    transfer_id INT NOT NULL PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    
    Operation ENUM("Cash-Send","Cash-Out","Deposit","undefined") NOT NULL DEFAULT "undefined",
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(user_id),
    FOREIGN KEY (receiver_id) REFERENCES users(user_id)
);
 
-- =========================
-- Table: deposits (User requests to add funds)
-- Relationship: users (1) -- (N) deposits
-- =========================
CREATE TABLE IF NOT EXISTS deposits (
    deposit_id INT AUTO_INCREMENT PRIMARY KEY,
    User_id INT NOT NULL,
    manager_id INT,
    amount DECIMAL(15,2) NOT NULL,
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (User_id) REFERENCES users(user_id),
    FOREIGN KEY (manager_id) REFERENCES managers(manager_id)
);
-- =========================
-- Table: withdrawals (User requests to pull funds)
-- Relationship: users (1) -- (N) withdrawals
-- =========================
CREATE TABLE IF NOT EXISTS withdrawals (
    withdrawal_id INT AUTO_INCREMENT PRIMARY KEY,
    User_id INT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    manager_id INT NULL,
    description VARCHAR(255),
    status ENUM('pending','handled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (User_id) REFERENCES users(user_id),
    FOREIGN KEY (manager_id) REFERENCES managers(manager_id)
);
----------------------------------------------------------------------------------------------------------------------------
DELIMITER $$

CREATE FUNCTION CheckUserEmailExists(email VARCHAR(150))
RETURNS BOOLEAN
DETERMINISTIC
BEGIN
    DECLARE existsFlag BOOLEAN;

    SELECT (COUNT(*) > 0) INTO existsFlag
    FROM users
    WHERE Email = email;

    RETURN existsFlag;
END $$

DELIMITER ;

DELIMITER $$

CREATE FUNCTION CheckUserIdExists(user_ID INT)
RETURNS BOOLEAN
DETERMINISTIC
BEGIN
    DECLARE existsFlag BOOLEAN;

    SELECT (COUNT(*) > 0) INTO existsFlag
    FROM users
    WHERE user_id = user_ID;

    RETURN existsFlag;
END $$

DELIMITER ;



DELIMITER$$

CREATE FUNCTION GetPassword(user_Email VARCHAR(150))
RETURNS VARCHAR(255)
DETERMINISTIC
BEGIN
    DECLARE pass VARCHAR(255);

    SELECT password INTO pass
    FROM users
    WHERE Email=user_Email;

    RETURN pass;
END $$

DELIMITER ;








-----------------------------------------------------------------------------------------------------------------
DELIMITER $$

CREATE PROCEDURE InsertNewUser(
    IN p_user_id INT,
    IN p_FirstName VARCHAR(100),
    IN p_LastName VARCHAR(100),
    IN p_Email VARCHAR(150),
    IN p_Password VARCHAR(255),
    IN p_Nationality VARCHAR(255),
    IN p_Birthday DATE,
    IN p_Phone VARCHAR(50),
    IN p_Income_source VARCHAR(100),
    IN p_Type_Of_Account VARCHAR(255),
    IN p_Address VARCHAR(254)
)
BEGIN
    INSERT INTO users(user_id, FirstName, LastName, Email, Password, Nationality, Birthday, Phone, Income_source, Type_Of_Account, Address)
    VALUES (p_user_id, p_FirstName, p_LastName, p_Email, p_Password, p_Nationality, p_Birthday, p_Phone, p_Income_source, p_Type_Of_Account, p_Address);

    INSERT INTO wallets(User_id)
    VALUES (p_user_id);
END $$

DELIMITER ;



-- =====================================================
-- PROCEDURE: transfer_money
-- Description: Transfers money between two users instantly.
-- Inputs: sender_user, receiver_user, amount, description
-- =====================================================

DELIMITER $$
--not complete until the database is ready

CREATE PROCEDURE money_transfer(
    IN transfer_code INT,
    IN  p_sender_id   INT,
    IN  p_receiver_id INT,
    IN  p_amount      DECIMAL(15,2),
    IN  p_type        VARCHAR(50),
    OUT p_success     BOOLEAN
)
BEGIN
    DECLARE sender_balance DECIMAL(15,2);

    SET p_success = FALSE;

    START TRANSACTION;

    -- Lock sender wallet
    SELECT balance
    INTO sender_balance
    FROM wallets
    WHERE User_id = p_sender_id
    FOR UPDATE;

    IF sender_balance >= p_amount THEN

        UPDATE wallets
        SET balance = balance - p_amount
        WHERE User_id = p_sender_id;

        UPDATE wallets
        SET balance = balance + p_amount
        WHERE User_id = p_receiver_id;

        INSERT INTO transfers (transfer_id,sender_id, receiver_id, amount, Operation)
        VALUES (transfer_code,p_sender_id, p_receiver_id, p_amount, p_type);

        UPDATE users
        SET Transactions_count = Transactions_count + 1
        WHERE user_id =p_sender_id;

        UPDATE users
        SET Transactions_count = Transactions_count + 1
        WHERE user_id =p_receiver_id;

        COMMIT;
        SET p_success = TRUE;
    ELSE
        ROLLBACK;
    END IF;

END$$

DELIMITER 



DELIMITER $$

DELIMITER $$

CREATE PROCEDURE CashOut(
    IN p_user_id INT,
    IN p_amount DECIMAL(15,2),
    OUT p_success BOOLEAN
)
BEGIN
    DECLARE sender_balance DECIMAL(15,2);

    SET p_success = FALSE;

    START TRANSACTION;

    -- Lock wallet row
    SELECT balance
    INTO sender_balance
    FROM wallets
    WHERE User_id = p_user_id
    FOR UPDATE;

    IF sender_balance >= p_amount THEN

        INSERT INTO withdrawals (User_id, amount, status)
        VALUES (p_user_id, p_amount, 'pending');

        UPDATE wallets
        SET balance = balance - p_amount
        WHERE User_id = p_user_id;

        COMMIT;
        SET p_success = TRUE;
    ELSE
        ROLLBACK;
    END IF;

END$$

DELIMITER ;

DELIMITER $$

CREATE PROCEDURE DeleteUserAccount(
    IN p_user_id INT
)
BEGIN
    START TRANSACTION;


    DELETE FROM wallets
    WHERE User_id = p_user_id;

    DELETE FROM users
    WHERE user_id = p_user_id;

    COMMIT;
END $$

DELIMITER ;


DELIMITER $$

CREATE PROCEDURE GetUserTransactions(IN p_user_id INT)
BEGIN
    SELECT 
        t.transfer_id,
        t.sender_id,
        s.FirstName AS sender_name,
        t.receiver_id,
        r.FirstName AS receiver_name,
        t.amount,
        t.Operation,
        t.created_at
    FROM transfers t
    JOIN users s ON t.sender_id = s.user_id
    JOIN users r ON t.receiver_id = r.user_id
    WHERE t.sender_id = p_user_id OR t.receiver_id = p_user_id
    ORDER BY t.created_at DESC;
END $$

DELIMITER ;

--===========================================================================--
CREATE PROCEDURE RemoveRequest(
    IN p_user_id INT,
    OUT flag BOOLEAN
)
BEGIN
    DECLARE p_amount DECIMAL(15,2);

    SET flag = FALSE;
    START TRANSACTION;

    IF EXISTS (
        SELECT 1 FROM withdrawals 
        WHERE User_id = p_user_id AND status = 'pending'
    ) THEN

        SELECT amount 
        INTO p_amount
        FROM withdrawals 
        WHERE User_id = p_user_id AND status = 'pending'
        LIMIT 1;

        UPDATE wallets 
        SET balance = balance + p_amount 
        WHERE User_id = p_user_id;

        DELETE FROM withdrawals 
        WHERE User_id = p_user_id AND status = 'pending';

        COMMIT;
        SET flag = TRUE;
    ELSE
        ROLLBACK;
    END IF;
END$$

DELIMITER ;



-- -- =====================================================
-- -- PROCEDURE: request_deposit
-- -- Description: Creates a pending deposit request for admin approval.
-- -- Inputs: user_id, amount, description
-- -- =====================================================

-- DELIMITER $$

-- CREATE PROCEDURE deposit(
--     IN User_id INT,
--     IN amount DECIMAL(15,2),
--     IN description VARCHAR(255)
-- )
-- BEGIN
--     -- DECLARE wallet INT;

--     -- Get wallet ID
--     -- SELECT wallet_id INTO wallet FROM wallets WHERE user_id = user_id;

--     -- Insert pending deposit
--     INSERT INTO deposits (User_id, amount, description)
--     VALUES (user_id, amount, description);
--     UPDATE users SET transactions_count = transactions_count + 1 WHERE user_id = User_id;
--     UPDATE wallets SET balance = balance + amount WHERE user_id = User_id;
-- END$$

-- DELIMITER ;


-- -- =====================================================
-- -- PROCEDURE: approve_deposit
-- -- Description: Admin accepts a pending deposit, updates wallet balance.
-- -- Inputs: dep_id (deposit_id)
-- -- =====================================================

-- DELIMITER $$

-- -- CREATE PROCEDURE approve_deposit(
-- --     IN dep_id INT
-- -- )
-- -- BEGIN
-- --     DECLARE wallet INT;
-- --     DECLARE amount DECIMAL(15,2);
-- --     DECLARE usr INT;

-- --     -- Get wallet & amount
-- --     SELECT wallet_id, amount INTO wallet, amount 
-- --     FROM deposits WHERE deposit_id = dep_id;

-- --     -- Add money
-- --     UPDATE wallets SET balance = balance + amount WHERE wallet_id = wallet;

-- --     -- Mark deposit as accepted
-- --     UPDATE deposits SET status = 'accepted' WHERE deposit_id = dep_id;

-- --     -- Update user transaction count
-- --     SELECT user_id INTO usr FROM wallets WHERE wallet_id = wallet;
-- --     UPDATE users SET transactions_count = transactions_count + 1 WHERE user_id = usr;

-- -- END$$

-- DELIMITER ;


-- -- =====================================================
-- -- PROCEDURE: request_withdrawal
-- -- Description: Creates a pending withdrawal request for admin approval.
-- -- Inputs: user_id, amount, description
-- -- =====================================================

-- DELIMITER $$

-- CREATE PROCEDURE request_withdrawal(
--     IN user_id INT,
--     IN amount DECIMAL(15,2),
--     IN description VARCHAR(255)
-- )
-- BEGIN
--     DECLARE wallet INT;

--     -- Get wallet
--     SELECT wallet_id INTO wallet FROM wallets WHERE User_id = user_id;

--     -- Insert pending withdrawal
--     INSERT INTO withdrawals (User_id, amount, description, status)
--     VALUES (user_id, amount, description, 'pending');
-- END$$

-- DELIMITER ;


-- -- =====================================================
-- -- PROCEDURE: approve_withdrawal
-- -- Description: Admin confirms a withdrawal and deducts wallet balance.
-- -- Inputs: user-id
-- -- =====================================================

-- DELIMITER $$

-- CREATE PROCEDURE approve_withdrawal(
--     IN userID INT
-- )
-- BEGIN
--     DECLARE wallet INT;
--     DECLARE amount DECIMAL(15,2);
    

--     -- Get wallet & amount
--     SELECT  amount INTO amount 
--     FROM withdrawals WHERE User_id =userID ;

--     -- Deduct money
--     UPDATE wallets SET balance = balance - amount WHERE User_id = userID;

--     -- Mark withdrawal as accepted
--     UPDATE withdrawals SET status = 'accepted' WHERE User_id = userID;

--     -- Update user transaction count
--     -- SELECT user_id INTO usr FROM wallets WHERE wallet_id = wallet;
--     UPDATE users SET transactions_count = transactions_count + 1 WHERE user_id = userID;

-- END$$

-- DELIMITER ;