-- USERS
CREATE INDEX idx_users_email ON users(Email);
CREATE INDEX idx_users_phone ON users(Phone);
CREATE INDEX idx_users_status ON users(status);

-- WALLETS
CREATE INDEX idx_wallets_user_id ON wallets(User_id);

-- TRANSFERS
CREATE INDEX idx_transfers_sender ON transfers(sender_id);
CREATE INDEX idx_transfers_receiver ON transfers(receiver_id);
CREATE INDEX idx_transfers_date ON transfers(created_at);

-- WITHDRAWALS
CREATE INDEX idx_withdrawals_user ON withdrawals(User_id);
CREATE INDEX idx_withdrawals_status ON withdrawals(status);

-- DEPOSITS
CREATE INDEX idx_deposits_user ON deposits(User_id);

-- MANAGERS
CREATE INDEX idx_managers_admin ON managers(admin_id);
