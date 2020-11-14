create table condo_association (
                                   id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                                   name text NOT NULL
);

create table building (
                          id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                          associationid INT,
                          datebuilt TIMESTAMP NOT NULL,
                          address TEXT NOT NULL,
                          FOREIGN KEY (associationid) REFERENCES condo_association(id)
);

create table condo_unit (
                            id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                            ownerid INT,
                            floor int NOT NULL,
                            door int NOT NULL,
                            FOREIGN KEY (ownerid) REFERENCES users(id)
);


create table user_roles (
                            userid INT,
                            associationid INT,
                            roleid INT,
                            FOREIGN KEY (userid) REFERENCES users(id),
                            FOREIGN KEY (associationid) REFERENCES condo_association(id),
                            FOREIGN KEY (roleid) REFERENCES roles(id),
                            PRIMARY KEY (userid, associationid)
);
