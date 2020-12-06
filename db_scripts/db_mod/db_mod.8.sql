create table emails (
                        emailid int PRIMARY KEY AUTO_INCREMENT,
                        userid int NOT NULL,
                        subject varchar(255),
                        content text,
                        senton timestamp not NULL DEFAULT NOW(),
                        FOREIGN KEY (userid) references users(id)
);

create table email_recipients(
                                 emailid int NOT NULL,
                                 userid int NOT NULL,
                                 opened bool NOT NULL default FALSE,
                                 FOREIGN KEY (emailid) references emails(emailid),
                                 FOREIGN KEY (userid) references users(id)
);