CREATE DATABASE IF NOT EXISTS wallet_tester;

USE wallet_tester;

CREATE TABLE IF NOT EXISTS user(
    ID INT NOT NULL PRIMARY KEY,
    FirstName VARCHAR(50) NOT NULL ,
    LastName VARCHAR(50) NOT NULL,
    Email VARCHAR(100) NOT NULL UNIQUE,
    Date_of_birth DATE NOT NULL ,
    Nationality VARCHAR(100) NOT NULL,
    Address VARCHAR(100) NOT NULL,
    Phone INT NOT NULL ,
    Type_of_Account VARCHAR(50) NULL,
    Income_Source VARCHAR(50) NULL
);