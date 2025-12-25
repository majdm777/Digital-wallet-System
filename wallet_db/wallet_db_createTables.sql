
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
    Address VARCHAR(254) NOT NULL,
    status ENUM("active","deleted","suspended") DEFAULT "active"
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