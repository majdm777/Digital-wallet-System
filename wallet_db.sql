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
    status ENUM("active","inactive")  DEFAULT "inactive",
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
CREATE TABLE transfers (
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
CREATE TABLE deposits (
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
CREATE TABLE withdrawals (
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

CREATE FUNCTION CheckUserIdExists(user_ID VARCHAR(150))
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

-- DELIMITER $$

-- CREATE PROCEDURE DeleteUserAccount(
--     IN p_user_id INT
-- )
-- BEGIN
--     START TRANSACTION;


--     DELETE FROM wallets
--     WHERE User_id = p_user_id;

--     DELETE FROM users
--     WHERE user_id = p_user_id;

--     COMMIT;
-- END $$

-- DELIMITER ;


DELIMITER $$

CREATE PROCEDURE DeleteUserAccountSafely(
    IN p_user_id INT,
    IN p_manager_id INT, 
    OUT flag BOOLEAN
)
BEGIN
    DECLARE remaining_balance DECIMAL(15,2);
    SET flag = FALSE;
    START TRANSACTION;

    -- Get remaining wallet balance
    SELECT balance INTO remaining_balance 
    FROM wallets 
    WHERE User_id= p_user_id;

    -- Record final withdrawal
    INSERT INTO withdrawals (User_id, amount, manager_id, status, description)
        VALUES(p_user_id, remaining_balance, p_manager_id, "handled", "final withdrawal before deleting account");

    -- Delete the wallet
    DELETE FROM wallets WHERE User_id = p_user_id;

    -- Mark user as deleted
    UPDATE users SET status = "deleted" WHERE user_id = p_user_id;

    COMMIT;
    SET flag = TRUE;
END$$

DELIMITER ;
--=======================================================================================--

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
    WHERE (t.sender_id = p_user_id OR t.receiver_id = p_user_id) AND t.created_at >= CURDATE() - INTERVAL 30 DAY
    ORDER BY t.created_at DESC;
END $$

DELIMITER ;

--===============================================================================--


DELIMITER $$

CREATE PROCEDURE SuspendUserAccount(
    IN p_user_id INT,
    IN p_manager_id INT,
    OUT flag INT
)
BEGIN
    DECLARE v_status VARCHAR(20);

    SET flag = 0;

    START TRANSACTION;

    -- Get current status
    SELECT status
    INTO v_status
    FROM users
    WHERE user_id = p_user_id
    FOR UPDATE;

    -- User not found
    IF v_status IS NULL THEN
        ROLLBACK;
        SET flag = -1;   -- user not found

    -- Unsuspend
    ELSEIF v_status = 'suspended' THEN
        UPDATE users
        SET status = 'active'
        WHERE user_id = p_user_id;

        COMMIT;
        SET flag = 1;    -- unsuspended

    -- Suspend
    ELSEIF v_status = 'active' THEN
        UPDATE users
        SET status = 'suspended'
        WHERE user_id = p_user_id;

        COMMIT;
        SET flag = 2;    -- suspended

    -- Deleted or invalid state
    ELSE
        ROLLBACK;
        SET flag = 0;    -- no action allowed
    END IF;

END$$

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
--===================================================================================--
DELIMITER $$
CREATE PROCEDURE getPendingWithdrawals()
BEGIN 
    SELECT 
        w.withdrawal_id,
        w.user_id,
        CONCAT(u.FirstName, ' ', u.LastName) AS name,
        w.amount,
        w.description,
        w.created_at
    FROM withdrawals w
    JOIN users u ON w.user_id= u.user_id
    WHERE w.status = 'pending';
END$$

DELIMITER ;



--=======================================================================================--
DELIMITER $$


CREATE PROCEDURE getUserBasicInfo(
    IN p_user_id INT
)
BEGIN
    SELECT 
        u.user_id,
        CONCAT(u.FirstName, ' ',u.LastName) AS name,
        u.Email,
        w.balance,
        u.status

    FROM users u
    JOIN wallets w ON u.user_id= w.user_id
    WHERE u.user_id = p_user_id AND u.status!="deleted";
END$$

DELIMITER ;
--=======================================================================================--
DELIMITER $$

CREATE PROCEDURE AddBalanceToWallet(
    IN p_user_id INT,
    IN p_amount DECIMAL(15,2),
    OUT p_new_balance DECIMAL(15,2)
)
BEGIN
    START TRANSACTION;

    UPDATE wallets
    SET balance = balance + p_amount
    WHERE User_id = p_user_id;

    SELECT balance
    INTO p_new_balance
    FROM wallets
    WHERE User_id = p_user_id;

    COMMIT;
END$$

DELIMITER ;

--====================================================================================--

DELIMITER $$

CREATE PROCEDURE GetUserTransferStats(
    IN p_user_id INT,
    IN p_days INT
)
BEGIN
    SELECT
        COUNT(*) AS total_transfers,

        SUM(CASE WHEN receiver_id = p_user_id THEN amount ELSE 0 END) AS total_received,

        SUM(CASE WHEN sender_id = p_user_id THEN amount ELSE 0 END) AS total_spent
    FROM transfers
    WHERE (sender_id = p_user_id OR receiver_id = p_user_id)
    AND created_at >= CURDATE() - INTERVAL p_days DAY;
END $$

DELIMITER ;
--========================================================================

DELIMITER $$

CREATE PROCEDURE GetInteractedUsers(
    IN p_user_id INT
)
BEGIN
    SELECT
        u.user_id,
        CONCAT(u.FirstName, ' ', u.LastName) AS name,
        u.Email,
        MIN(t.created_at) AS first_interaction
    FROM (
        SELECT receiver_id AS other_user_id, created_at
        FROM transfers
        WHERE sender_id = p_user_id

        UNION ALL

        SELECT sender_id AS other_user_id, created_at
        FROM transfers
        WHERE receiver_id = p_user_id
    ) t
    JOIN users u ON u.user_id = t.other_user_id
    GROUP BY u.user_id, u.FirstName, u.LastName, u.Email
    ORDER BY first_interaction ASC;
END $$

DELIMITER ;



--=========================================================================--



DELIMITER $$

CREATE PROCEDURE GetTransactionsBetweenUsers(
    IN p_my_id INT,
    IN p_other_id INT
)
BEGIN
    SELECT
        transfer_id,
        amount,
        created_at,
        CASE
            WHEN sender_id = p_my_id THEN 'Sent'
            WHEN receiver_id = p_my_id THEN 'Received'
        END AS direction
    FROM transfers
    WHERE
        (sender_id = p_my_id AND receiver_id = p_other_id)
        OR
        (sender_id = p_other_id AND receiver_id = p_my_id)
    ORDER BY created_at DESC;
END$$

DELIMITER ;




--==========================================================================================majd






CREATE PROCEDURE handleRequests(
    IN p_manager_id INT,
    IN p_withdrawal_id INT,
    IN p_transfer_id INT
)
BEGIN
    -- 1. Declare variables at the TOP
    DECLARE v_user_id INT;
    DECLARE v_amount DECIMAL(15,2);

    -- 2. Fetch User ID and Amount before updating/inserting
    SELECT User_id, amount INTO v_user_id, v_amount 
    FROM withdrawals 
    WHERE withdrawal_id = p_withdrawal_id;

    -- 3. Update the withdrawal status (Use COMMA, not AND)
    UPDATE withdrawals
    SET status = 'handled', 
        manager_id = p_manager_id 
    WHERE withdrawal_id = p_withdrawal_id;

    -- 4. Insert into transfers 
    -- NOTE: This will fail if transfers.sender_id has a FOREIGN KEY to users.user_id
    -- because p_manager_id is NOT in the users table.
    INSERT INTO transfers (transfer_id, sender_id, receiver_id, amount, Operation)
    VALUES (p_transfer_id, v_user_id, v_user_id, v_amount, 'Cash-Out');
    
END $$

DELIMITER ;



--=======================================================================================--
DELIMITER $$

CREATE PROCEDURE addFunds(
    IN  p_transfer_id INT,
    IN  p_user_id INT,
    IN  p_amount DECIMAL(15,2),
    OUT flag INT
)
BEGIN
    DECLARE v_balance DECIMAL(15,2);
    DECLARE wallet_exists INT;

    SET flag = 0;

    -- 1. Reject invalid amounts
    IF p_amount <= 0 THEN
        SET flag = -1;
    ELSE
        -- 2. Check if wallet exists
        SELECT COUNT(*) INTO wallet_exists
        FROM wallets
        WHERE user_id = p_user_id;

        IF wallet_exists = 0 THEN
            SET flag = 0;
        ELSE
            -- 3. Get current balance
            SELECT balance INTO v_balance
            FROM wallets
            WHERE user_id = p_user_id
            FOR UPDATE;

            -- 4. Update wallet balance
            UPDATE wallets
            SET balance = v_balance + p_amount
            WHERE user_id = p_user_id;

            -- 5. Insert transfer record (sender = receiver = user)
            INSERT INTO transfers (transfer_id, sender_id, receiver_id, amount, Operation)
            VALUES (p_transfer_id, p_user_id, p_user_id, p_amount, 'Deposit');

            -- 6. Set success flag
            SET flag = 1;
        END IF;
    END IF;
END$$

DELIMITER ;


--=========================================================================================--
DELIMITER $$
CREATE PROCEDURE globalBalance(
    OUT p_balance DECIMAL(15,2)
)
BEGIN
    SELECT SUM(balance) INTO p_balance
    FROM wallets;
END$$

DELIMITER ;

--=========================================================================================--
DELIMITER $$
CREATE PROCEDURE totalUsers(
    OUT p_usersTotal INT
)
BEGIN
    SELECT COUNT(*) INTO p_usersTotal
    FROM users;
    WHERE status!="deleted";
END$$

DELIMITER ;
--=========================================================================================--
DELIMITER $$
CREATE PROCEDURE totalWithdrawals(
    OUT p_withdrawalsTotal INT
)
BEGIN
    SELECT COUNT(*) INTO p_withdrawalsTotal
    FROM withdrawals 
    WHERE status = 'pending';
END$$

DELIMITER ;
--=========================================================================================--
DELIMITER $$
CREATE PROCEDURE totalWithdrawals(
    OUT p_withdrawalsTotal INT
)
BEGIN
    SELECT COUNT(*) INTO p_withdrawalsTotal
    FROM withdrawals 
    WHERE status = 'pending';
END$$

DELIMITER ;
--=========================================================================================--
DELIMITER $$
CREATE PROCEDURE getManagers(
)
BEGIN
    SELECT 
    m.manager_id, 
    m.username, 
    m.email, 
    m.status,
    m.created_at,
    COUNT(w.withdrawal_id) AS handled_count
FROM managers m
LEFT JOIN withdrawals w ON m.manager_id = w.manager_id AND w.status = 'handled'
GROUP BY m.manager_id;
END$$

DELIMITER ;
--=========================================================================================--
DELIMITER $$
CREATE PROCEDURE addNewManager(
    IN p_user_name VARCHAR(50),
    IN p_email VARCHAR(255),
    IN p_password VARCHAR(255)
)
BEGIN
    
    INSERT INTO managers(username, email, password, admin_id)
    VALUES (p_user_name, p_email, p_password, 1);
END$$

DELIMITER ;

--=========================================================================================--
DELIMITER $$
CREATE PROCEDURE invokeManager(
    IN p_manager_id INT
)
BEGIN
    DECLARE p_status VARCHAR(20);
    
    SELECT status INTO p_status
    FROM managers
    WHERE manager_id = p_manager_id;
    
    IF p_status = 'active' THEN
        UPDATE managers
        SET status = 'inactive'
        WHERE manager_id = p_manager_id;
    ELSE
        UPDATE managers
        SET status = 'active'
        WHERE manager_id = p_manager_id;
    END IF;

END$$

DELIMITER ;
