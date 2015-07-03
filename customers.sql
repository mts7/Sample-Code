# author: Mike Rodarte
# Sample table declaration for a basic customers table in MySQL
CREATE TABLE customers(
  id INT(6) PRIMARY KEY NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  number INT(8) NOT NULL DEFAULT 0
  added DATETIME NOT NULL,
  modified DATETIME DEFAULT NULL
);

