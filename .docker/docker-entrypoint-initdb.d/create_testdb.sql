CREATE DATABASE IF NOT EXISTS cake_test
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

CREATE USER IF NOT EXISTS 'cake_test'@'%'
IDENTIFIED BY 'cake_test';

GRANT ALL PRIVILEGES
ON cake_test.*
TO 'cake_test'@'%';
