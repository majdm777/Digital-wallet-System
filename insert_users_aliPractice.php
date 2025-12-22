<?php


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// rest of your code

// Script to insert sample users into the wallet_db database using PDO
// Run this once to populate the users table with test data

try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=wallet_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}

// Sample user data (passwords are hashed with password_hash for security)
$users = [
    [
        'user_id' => 1,
        'FirstName' => 'John',
        'LastName' => 'Doe',
        'Email' => 'john.doe@example.com',
        'Password' => password_hash('password123', PASSWORD_DEFAULT),
        'Nationality' => 'American',
        'Birthday' => '1990-01-01',
        'Transactions_count' => 0,
        'Phone' => '1234567890',
        'Income_source' => 'Salary',
        'Type_Of_Account' => 'Savings',
        'Address' => '123 Main St, City, State'
    ],
    [
        'user_id' => 2,
        'FirstName' => 'Jane',
        'LastName' => 'Smith',
        'Email' => 'jane.smith@example.com',
        'Password' => password_hash('password123', PASSWORD_DEFAULT),
        'Nationality' => 'Canadian',
        'Birthday' => '1985-05-15',
        'Transactions_count' => 0,
        'Phone' => '0987654321',
        'Income_source' => 'Freelance',
        'Type_Of_Account' => 'Checking',
        'Address' => '456 Elm St, City, State'
    ],
    [
        'user_id' => 3,
        'FirstName' => 'Alice',
        'LastName' => 'Johnson',
        'Email' => 'alice.johnson@example.com',
        'Password' => password_hash('password123', PASSWORD_DEFAULT),
        'Nationality' => 'British',
        'Birthday' => '1992-03-20',
        'Transactions_count' => 0,
        'Phone' => '1122334455',
        'Income_source' => 'Business',
        'Type_Of_Account' => 'Business',
        'Address' => '789 Oak St, City, State'
    ]
];

// Prepare the insert statement
$stmt = $pdo->prepare("INSERT INTO users (user_id, FirstName, LastName, Email, Password, Nationality, Birthday, Transactions_count, Phone, Income_source, Type_Of_Account, Address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($users as $user) {
    try {
        $stmt->execute([
            $user['user_id'],
            $user['FirstName'],
            $user['LastName'],
            $user['Email'],
            $user['Password'],
            $user['Nationality'],
            $user['Birthday'],
            $user['Transactions_count'],
            $user['Phone'],
            $user['Income_source'],
            $user['Type_Of_Account'],
            $user['Address']
        ]);
        echo "Inserted user: " . $user['FirstName'] . " " . $user['LastName'] . "<br>";
    } catch (PDOException $e) {
        echo "Error inserting user: " . $e->getMessage() . "<br>";
    }
}

echo "Sample users insertion completed.";
?>