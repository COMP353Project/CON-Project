  /*****************************************************************************
  ||   Filename:    db_code.sql
  ||   Description: Create database for COMP 353 final project
  ||
  ||   Ver  Date           Author                 Modification
  ||   1.0  29-nov-2020    Anthony                First version
  ||
  ||   Usage : Store data for condo website
  ||
  ||   For Information concerning this software contact: Anthony Chraim
  ||
  *****************************************************************************/
DROP TABLE GroupRole;
CREATE TABLE GroupRole(
        group_role_code varchar(3) NOT NULL,
        enabled  BOOLEAN NOT NULL,
        description TINYTEXT,
        PRIMARY KEY (group_role_code));

DROP TABLE UserRole;
CREATE TABLE UserRole(
        user_role_code varchar(3) NOT NULL,
        enabled  BOOLEAN NOT NULL,
        description TINYTEXT,
        PRIMARY KEY (user_role_code));

DROP TABLE AssociationRole;
CREATE TABLE AssociationRole(
        association_role_code varchar(3) NOT NULL,
        enabled  BOOLEAN NOT NULL,
        description TINYTEXT,
        PRIMARY KEY (association_role_code));

DROP TABLE Users1;
CREATE TABLE Users1(
        user_id int NOT NULL AUTO_INCREMENT,
        association_role_code varchar(3) NOT NULL,
        user_role_code varchar(3) NOT NULL,
        first_name varchar(255) NOT NULL,
        last_name varchar(255) NOT NULL,
        address varchar(255) NOT NULL,
        email varchar(320) NOT NULL,
        password varchar(500) NOT NULL,
        creation_date varchar(500),
        standing varchar(50),
        status varchar(20),
        isactive boolean,
        description tinytext,
        PRIMARY KEY (user_id),
        FOREIGN KEY (user_role_code) REFERENCES UserRole(user_role_code),
        FOREIGN KEY (association_role_code) REFERENCES AssociationRole(association_role_code));

DROP TABLE ConGroup;
CREATE TABLE ConGroup(
        con_group_id int NOT NULL,
        user_id int NOT NULL,
        group_role_code varchar(3),
        group_name varchar(255),
        PRIMARY KEY (con_group_id, user_id),
        FOREIGN KEY (user_id) REFERENCES Users1(user_id),
        FOREIGN KEY (group_role_code) REFERENCES GroupRole(group_role_code));

DROP TABLE Messages;
CREATE TABLE Messages(
        message_id int NOT NULL AUTO_INCREMENT,
        con_group_id int NOT NULL,
        user_id int NOT NULL,
        content TINYTEXT,
        tstamp timestamp,
        PRIMARY KEY (message_id),
        FOREIGN KEY (user_id) REFERENCES Users1(user_id),
        FOREIGN KEY (con_group_id) REFERENCES ConGroup(con_group_id));

DROP TABLE PostsPermissions;
CREATE TABLE PostsPermissions(
        posts_permissions_code varchar(3) NOT NULL,
        enabled  BOOLEAN NOT NULL,
        description TINYTEXT,
        PRIMARY KEY (posts_permissions_code));

DROP TABLE PostsAccessabilities;
CREATE TABLE PostsAccessabilities(
        posts_accessabilities_code varchar(3) NOT NULL,
        enabled  BOOLEAN NOT NULL,
        description TINYTEXT,
        PRIMARY KEY (posts_accessabilities_code));

DROP TABLE Posts;
CREATE TABLE Posts(
        post_id int NOT NULL,
        user_id int NOT NULL,
        group_id int NOT NULL,
        posts_accessabilities_code varchar(3),
        posts_permissions_code varchar(3),
        contents TEXT,
        is_commentable BOOLEAN,
        tstamp timestamp,
        PRIMARY KEY (post_id),
        FOREIGN KEY (user_id) REFERENCES Users1(user_id),
        FOREIGN KEY (group_id) REFERENCES con_group(id),
        FOREIGN KEY (posts_accessabilities_code) REFERENCES PostsAccessabilities(posts_accessabilities_code),
        FOREIGN KEY (posts_permissions_code) REFERENCES PostsPermissions(posts_permissions_code));

DROP TABLE Images;
CREATE TABLE Images(
        images_id int NOT NULL AUTO_INCREMENT,
        user_id int NOT NULL,
        post_id int NOT NULL,
        image BLOB NOT NULL,
        caption TINYTEXT,
        PRIMARY KEY (images_id),
        FOREIGN KEY (user_id) REFERENCES Users1(user_id),
        FOREIGN KEY (post_id) REFERENCES Posts(post_id));

DROP TABLE Comments;
CREATE TABLE Comments(
        comment_id int NOT NULL AUTO_INCREMENT,
        post_id int NOT NULL,
        user_id int NOT NULL,
        message TINYTEXT,
        tstamp TIMESTAMP,
        PRIMARY KEY (comment_id),
        FOREIGN KEY (user_id) REFERENCES Users1(user_id),
        FOREIGN KEY (post_id) REFERENCES Posts(post_id));

DROP TABLE Poll;
CREATE TABLE Poll(
        poll_id int NOT NULL AUTO_INCREMENT,
        user_id int NOT NULL,
        poll_title varchar(255),
        poll_description TINYTEXT,
        tstamp TIMESTAMP,
        PRIMARY KEY (poll_id, user_id),
        FOREIGN KEY (user_id) REFERENCES Users1(user_id));

DROP TABLE Options;
CREATE TABLE Options(
        option_id int NOT NULL AUTO_INCREMENT,
        poll_id int NOT NULL,
        poll_title varchar(255),
        poll_description TINYTEXT,
        num_votes int,
        PRIMARY KEY (option_id),
        FOREIGN KEY (poll_id) REFERENCES Poll(poll_id));

DROP TABLE Votes;
CREATE TABLE Votes(
        option_id int NOT NULL,
        user_id int NOT NULL,
        PRIMARY KEY (option_id, user_id),
        FOREIGN KEY (option_id) REFERENCES Options(option_id),
        FOREIGN KEY (user_id) REFERENCES Users1(user_id));

DROP TABLE Parkings;
CREATE TABLE Parkings(
        parking_id INT NOT NULL,
        condo_unit_id INT NOT NULL,
        typle varchar(20),
        PRIMARY KEY (parking_id),
        FOREIGN KEY (condo_unit_id) REFERENCES condo_unit(id));

DROP TALBE Storages;
CREATE TABLE Storages(
        storage_id INT NOT NULL,
        condo_unit_id INT NOT NULL,
        typle varchar(20),
        floor int,
        PRIMARY KEY (storage_id),
        FOREIGN KEY (condo_unit_id) REFERENCES condo_unit(id));

DROP TABLE Maintenance;
CREATE TABLE Maintenance(
        maintenance_id INT NOT NULL AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        contractor VARCHAR(255),
        PRIMARY KEY (maintenance_id));

DROP TABLE Transactions;
CREATE TABLE Transactions(
        transaction_id INT NOT NULL AUTO_INCREMENT,
        buyer_user_id INT NOT NULL,
        seller_user_id  INT NOT NULL,
        condo_unit_id INT NOT NULL,
        tstamp TIMESTAMP,
        price decimal(12,2),
        address TEXT,
        PRIMARY KEY (transaction_id),
        FOREIGN KEY (buyer_user_id) REFERENCES Users1(user_id),
        FOREIGN KEY (seller_user_id) REFERENCES Users1(user_id),
        FOREIGN KEY (condo_unit_id) REFERENCES condo_unit(id));