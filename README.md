# Digital Wallet System

A full-stack digital wallet and banking-style web application built with PHP, MySQL, HTML, CSS, and JavaScript. The project includes user authentication, wallet/account management, fund transfers, transaction history, and manager-side admin controls.

## Overview

This repository contains a wallet system where:

- Users can sign up and log in
- Users can view balance and transaction history
- Managers can manage user accounts and requests
- Admin-like operations support approvals, account suspension, and fund top-ups
- Data is stored in a MySQL database using stored procedures and SQL logic

The app is designed to run in a local PHP environment such as XAMPP or WAMP.

## Tech Stack

- PHP
- MySQL
- JavaScript
- HTML/CSS
- XAMPP for local development

## Main Features

- User registration and login
- Password validation and session-based authentication
- Manager login flow for wallet operations
- Deposit/withdrawal handling
- Transaction tracking
- Wallet balance management
- User suspension/deletion workflows
- Manager dashboard and account control pages

## Project Structure

```text
Digital-wallet-System/
├── Account.css
├── Account.html
├── Account.js
├── Account.php
├── admin.css
├── admin.html
├── admin.js
├── adminData.php
├── ForgetPass.css
├── ForgetPass.js
├── ForgetPass.php
├── History.css
├── History.html
├── History.js
├── HistoryDataHandle.php
├── IMAGES/
├── index.php
├── insert_users_aliPractice.php
├── LOGIN.js
├── Main.html
├── Main.js
├── Maindata.php
├── MainPage.css
├── manager.css
├── manager.html
├── manager.js
├── managerData.php
├── package.json
├── server.js
├── SIGNUP.html
├── SIGNUP.js
├── SIGNUP-1.php
├── SIGNUP-2.php
├── SIGNUP-2.css
├── SIGNUP_stylesheet.CSS
├── signup_process.php
├── signup_success.html
├── STYLESHEET.css
├── test.php
├── tester.html
├── tester2.html
├── transaction.php
├── Transactions.CSS
├── wallet_db.sql
└── README.md
```

## Prerequisites

Before running the project, make sure you have:

- XAMPP installed
- Apache and MySQL running
- PHP 7+ or compatible version
- A browser such as Chrome or Edge

## Setup Instructions

1. Clone or download this repository into your local web server folder.

   Example:

   ```bash
   C:\xampp\htdocs\Digital-wallet-System
   ```

2. Start Apache and MySQL in XAMPP.

3. Import the database:

   - Open phpMyAdmin
   - Create a database named `wallet_db`
   - Import the file `wallet_db.sql`

4. Open the application in the browser:

   ```text
   http://localhost/Digital-wallet-System/index.php
   ```

5. If your local MySQL credentials are different from the defaults used in the code, update the database connection settings in the PHP files before running the app.

## Database Notes

The project expects a database named `wallet_db` and connects using the default local credentials:

```php
new mysqli('localhost', 'root', '', 'wallet_db')
```

If your XAMPP installation uses a password for the root user, update the connection strings in the relevant PHP files to match your environment.

## Login Flow

The login page supports:

- Normal user accounts
- Manager accounts with email addresses ending in `@wallet.com`

User sessions are created using PHP `$_SESSION`, and successful sign-ins redirect to the relevant dashboard pages.

## Important Notes

- This is a local project intended for development and learning.
- Some files appear to be experimental or partially complete, so functionality may vary by page.
- The repository includes both front-end pages and backend database logic.
- Some database procedures and schema elements may require further refinement depending on your environment.

## Recommended Development Workflow

1. Launch XAMPP
2. Start MySQL and Apache
3. Import `wallet_db.sql`
4. Access the app through `index.php`
5. Test signup/login flows first before using manager features

## Future Improvements

Potential improvements for this project include:

- Better security with password hashing and session validation
- Clean separation of front-end and back-end logic
- Error logging and production-ready configuration
- Improved UI consistency across pages
- More complete transaction validation and audit trails

## License

This project is intended for educational and local development use.

## Contact

If you are continuing development on this project, you can use the repository as a base for a wallet platform, payment dashboard, or internal finance management system.
