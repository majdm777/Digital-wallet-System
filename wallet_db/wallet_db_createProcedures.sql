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
    START TRANSACTION;
        INSERT INTO users(user_id, FirstName, LastName, Email, Password, Nationality, Birthday, Phone, Income_source, Type_Of_Account, Address)
        VALUES (p_user_id, p_FirstName, p_LastName, p_Email, p_Password, p_Nationality, p_Birthday, p_Phone, p_Income_source, p_Type_Of_Account, p_Address);

        INSERT INTO wallets(User_id)
        VALUES (p_user_id);
    commit;
END $$

DELIMITER ;

-- transfer money

DELIMITER $$

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

DELIMITER ;

-- =============================================================================================================== -- 

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

-- ================================================================================================================ --


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

-- ====================================================================================================



DELIMITER $$

CREATE PROCEDURE GetUserTransactions(IN p_user_id INT)
BEGIN
    SELECT *
    FROM v_user_transactions   -- using view
    WHERE (sender_id = p_user_id OR receiver_id = p_user_id)
      AND created_at >= CURDATE() - INTERVAL 30 DAY
    ORDER BY created_at DESC;
END $$

DELIMITER ;



-- ===================================================================

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

-- ====================================================================================================
DELIMITER $$
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

-- ================================================================================================================================
DELIMITER $$

CREATE PROCEDURE getPendingWithdrawals()
BEGIN
    SELECT *
    FROM v_pending_withdrawals; -- EDITED
END$$

DELIMITER ;
-- ======================================================================================
DELIMITER $$

CREATE PROCEDURE getManagers()
BEGIN
    SELECT *
    FROM v_manager_performance; -- EDITED
END$$

DELIMITER ;
-- ========================================================================================
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
-- ===============================================================================================
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

-- ===============================================================================================
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
    ) t,
    users u                      -- 🔴 
    WHERE u.user_id = t.other_user_id   -- 🔴 
    GROUP BY u.user_id, u.FirstName, u.LastName, u.Email
    ORDER BY first_interaction ASC;
END $$

DELIMITER ;
-- =======================================================================================================

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

-- ====================================================================================================

DELIMITER $$

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

-- ===========================================================================================================


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

-- =======================================================================================================

DELIMITER $$
CREATE PROCEDURE globalBalance(
    OUT p_balance DECIMAL(15,2)
)
BEGIN
    SELECT SUM(balance) INTO p_balance
    FROM wallets;
END$$

DELIMITER ;
-- ===========================================================================================================
DELIMITER $$
CREATE PROCEDURE totalUsers(
    OUT p_usersTotal INT
)
BEGIN
    SELECT COUNT(*) INTO p_usersTotal
    FROM users
    WHERE status!="deleted";
END$$

DELIMITER ;

-- =================================================================================================================

-- ============================================================================================================================
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
-- ===============================================================================================================================

-- DELIMITER $$
-- CREATE PROCEDURE getManagers(
-- )
-- BEGIN
--     SELECT 
--     m.manager_id, 
--     m.username, 
--     m.email, 
--     m.status,
--     m.created_at,
--     COUNT(w.withdrawal_id) AS handled_count
-- FROM managers m
-- LEFT JOIN withdrawals w ON m.manager_id = w.manager_id AND w.status = 'handled'
-- GROUP BY m.manager_id;
-- END$$

-- DELIMITER ;
-- ===============================================================================================

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
-- ==============================================================================================
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
-- ====================================================================================================
DELIMITER $$

CREATE PROCEDURE getUserBasicInfo(IN p_user_id INT)
BEGIN
    SELECT *
    FROM v_user_basic_info   -- EDITED
    WHERE user_id = p_user_id;
END$$

DELIMITER ;



