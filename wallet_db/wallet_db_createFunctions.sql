DELIMITER $$

CREATE FUNCTION CheckUserEmailExists(email VARCHAR(150))
RETURNS BOOLEAN
DETERMINISTIC
BEGIN
    DECLARE existsFlag BOOLEAN;

    SELECT (COUNT(*) > 0) INTO existsFlag
    FROM users
    WHERE Email = email ;

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



DELIMITER $$

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