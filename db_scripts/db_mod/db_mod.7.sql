create table Posts (
    post_id int PRIMARY KEY NOT NULL AUTO_INCREMENT,
    user_id int NOT NULL,
    group_id int,
    contents text NOT NULL,
    is_commentable bool NOT NULL DEFAULT TRUE,
    tstamp timestamp NOT NULL DEFAULT NOW(),
    FOREIGN KEY (user_id) references users(id),
    FOREIGN KEY (group_id) references con_group(id)
);

create table Comments (
                          comment_id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
                          post_id INT NOT NULL,
                          user_id INT NOT NULL,
                          message text NOT NULL,
                          tstamp TIMESTAMP NOT NULL DEFAULT NOW(),
                          FOREIGN KEY (post_id) references Posts(post_id),
                          FOREIGN KEY (user_id) references users(id)
);

