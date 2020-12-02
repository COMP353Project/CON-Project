create table group_role_def (
                                id int not null PRIMARY KEY AUTO_INCREMENT,
                                name text not null,
                                description text
);

insert into group_role_def
    (name, description)
    values
    ('member', 'Member of group can see the groups, access posts, email its members'),
    ('admin', 'Admin of group can add others to groups, delete other from group, delete group');

create table con_group (
    id int not null PRIMARY KEY AUTO_INCREMENT,
    name text not null,
    description text,
    isactive bool not null default TRUE,
    createdon timestamp not null default now()
);

create table group_membership (
    userid int,
    groupid int,
    roleid int,
    joined timestamp not null default now(),
    PRIMARY KEY (userid, groupid),
    FOREIGN KEY (userid) references users(id),
    FOREIGN KEY (groupid) references con_group(id),
    FOREIGN KEY (roleid) references group_role_def(id)
);

