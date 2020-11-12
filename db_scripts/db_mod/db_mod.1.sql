create table users(
                      id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                      email TEXT NOT NULL,
                      firstname TEXT,
                      lastname TEXT,
                      password TEXT NOT NULL,
                      isactive BOOL NOT NULL DEFAULT TRUE,
                      createdon TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);