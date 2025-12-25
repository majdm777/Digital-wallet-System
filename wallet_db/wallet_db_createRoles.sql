-- Roles
CREATE ROLE admin_role;
CREATE ROLE manager_role;
CREATE ROLE user_role;

-- Admin
GRANT ALL PRIVILEGES ON wallet_db.* TO admin_role;

-- Manager
GRANT SELECT, UPDATE ON users TO manager_role;
GRANT SELECT, UPDATE ON withdrawals TO manager_role;
GRANT SELECT ON wallets TO manager_role;

-- User
GRANT SELECT ON wallets TO user_role;
GRANT SELECT ON transfers TO user_role;
GRANT EXECUTE ON PROCEDURE money_transfer TO user_role;
