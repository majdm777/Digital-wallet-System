
-- check user status before transfer 
DELIMITER $$

CREATE TRIGGER trg_check_user_status_before_transfer
BEFORE INSERT ON transfers
FOR EACH ROW
BEGIN
    DECLARE sender_status VARCHAR(20);
    DECLARE receiver_status VARCHAR(20);

    SELECT status INTO sender_status FROM users WHERE user_id = NEW.sender_id;
    SELECT status INTO receiver_status FROM users WHERE user_id = NEW.receiver_id;

    IF sender_status != 'active' OR receiver_status != 'active' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Transfer not allowed: user not active';
    END IF;
END$$

DELIMITER ;



-- Prevent withdrawal if user is suspended/deleted
DELIMITER $$


CREATE TRIGGER trg_check_user_status_before_withdrawal
BEFORE INSERT ON withdrawals
FOR EACH ROW
BEGIN
    DECLARE u_status VARCHAR(20);

    SELECT status INTO u_status
    FROM users
    WHERE user_id = NEW.User_id;

    IF u_status != 'active' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Withdrawal not allowed: user not active';
    END IF;
END$$

DELIMITER ;



-- Auto-increment Transactions_count after transfer

DELIMITER $$

CREATE TRIGGER trg_after_transfer_count
AFTER INSERT ON transfers
FOR EACH ROW
BEGIN
    UPDATE users SET Transactions_count = Transactions_count + 1
    WHERE user_id IN (NEW.sender_id, NEW.receiver_id);
END$$

DELIMITER ;
