CREATE VIEW v_user_transactions AS
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
JOIN users r ON t.receiver_id = r.user_id;



CREATE VIEW v_pending_withdrawals AS
SELECT
    w.withdrawal_id,
    u.user_id,
    CONCAT(u.FirstName,' ',u.LastName) AS name, 
    w.amount,
    w.description,                             
    w.created_at
FROM withdrawals w
JOIN users u ON w.User_id = u.user_id
WHERE w.status = 'pending';


CREATE OR REPLACE VIEW v_manager_performance AS
SELECT
    m.manager_id,
    m.username,
    m.email,          
    m.status,         
    m.created_at,     
    COUNT(w.withdrawal_id) AS handled_count
FROM managers m
LEFT JOIN withdrawals w
    ON m.manager_id = w.manager_id AND w.status='handled'
GROUP BY m.manager_id;


CREATE OR REPLACE VIEW v_user_basic_info AS
SELECT
    u.user_id,
    CONCAT(u.FirstName,' ',u.LastName) AS name,
    u.Email,
    w.balance,
    u.status
FROM users u
JOIN wallets w ON u.user_id = w.user_id
WHERE u.status != 'deleted';
