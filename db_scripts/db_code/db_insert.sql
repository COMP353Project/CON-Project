DELETE FROM GroupRole;
INSERT INTO GroupRole (group_role_code, enabled, description) VALUES ('MEM',true , 'member of the group');
INSERT INTO GroupRole (group_role_code, enabled, description) VALUES ('ADM',true , 'admin of the group');

DELETE FROM AssociationRole;
INSERT INTO AssociationRole (association_role_code, enabled, description) VALUES ('PRE',true , 'President of the association');
INSERT INTO AssociationRole (association_role_code, enabled, description) VALUES ('VIC',true , 'Vice-president of the association');
INSERT INTO AssociationRole (association_role_code, enabled, description) VALUES ('MEM',true , 'Member of the association');

DELETE FROM Users;
INSERT INTO Users (association_role_code, user_role_code, first_name, last_name, address, email, password, creation_date, standing, status, isactive, description) 
VALUES ('MEM','ADM' ,'Paul', 'Piggott', '123 street Montreal H1HH1H', 'flying-piggott@hotmail.com', '$2y$10$GbX614L8ykIlqODQDDqgBelJGp2UJ50TVjOx1fBO2wK8RQRPU.Na6', '2020-11-11 23:14:48', '', '',true , 'This is an active user that is a member');
INSERT INTO Users (association_role_code, user_role_code, first_name, last_name, address, email, password, creation_date, standing, status, isactive, description) 
VALUES ('PRE','SUP' ,'Super', 'User', '1234 street Montreal H2HH2H', 'superuser@dummy.com', '$2y$10$RW32GdotEVj0VfdO0CSyvOtEGJA4skyKfFAldQ017ZEkoBQTkIRMe', '2020-11-11 23:16:43', '', '',true , 'This is an active user that is a president');
INSERT INTO Users (association_role_code, user_role_code, first_name, last_name, address, email, password, creation_date, standing, status, isactive, description) 
VALUES ('MEM','ADM' ,'Andrei', 'Serban', '456 street Montreal H3HH3H', 'andrei@serban', '$2y$10$sHzMVXWMWPAGzWnbnPaW/OO6MfcAhpGh8AvvgtA1jbcntZSyncrWu', '2020-11-11 23:54:44', '', '',true , 'This is an active user that is a member');
INSERT INTO Users (association_role_code, user_role_code, first_name, last_name, address, email, password, creation_date, standing, status, isactive, description) 
VALUES ('MEM','STA' ,'Mona', 'Kia', '102 street Montreal H5HH5H', 'mona@kia', '$2y$10$5AcUaGNCtHNwN.EOfP9mouJkAZhYfwgl0R0HH7KQJGfXeFlmMoywm', '2020-11-27 21:20:54', '', '',true , 'This is an active user that is a member');
INSERT INTO Users (association_role_code, user_role_code, first_name, last_name, address, email, password, creation_date, standing, status, isactive, description) 
VALUES ('MEM','SUP' ,'Tony', 'Chraim', '789 street Montreal H4HH4H', 'tony@chraim', '$2y$10$76apYHg97YUt3bCgv0T06Ov8xmqw4FTbSQOkxz4dd9mkEzGSNVnPm', '2020-11-27 21:17:38', '', '',true , 'This is an active user that is a member');

DELETE FROM UserRole;
INSERT INTO UserRole (user_role_code, enabled, description) VALUES ('SUP',true , 'Super user can do anything');
INSERT INTO UserRole (user_role_code, enabled, description) VALUES ('ADM',true , 'Condo association admin can only make changes specific to their condo association');
INSERT INTO UserRole (user_role_code, enabled, description) VALUES ('STA',true , 'Standard user can only affect their own profile');

DELETE FROM ConGroup;
INSERT INTO ConGroup (con_group_id, user_id, group_role_code, group_name) VALUES (1, 1, 'ADM', 'FRIST GOUP');
INSERT INTO ConGroup (con_group_id, user_id, group_role_code, group_name) VALUES (1, 2, 'MEM', 'FRIST GOUP');
INSERT INTO ConGroup (con_group_id, user_id, group_role_code, group_name) VALUES (1, 3, 'MEM', 'FRIST GOUP');
INSERT INTO ConGroup (con_group_id, user_id, group_role_code, group_name) VALUES (2, 1, 'ADM', 'SECOND GOUP');
INSERT INTO ConGroup (con_group_id, user_id, group_role_code, group_name) VALUES (2, 2, 'MEM', 'SECOND GOUP');

DELETE FROM Messages;
INSERT INTO Messages (con_group_id, user_id, content, tstamp) VALUES (1, 1, 'Hey Guys!!1', '2020-11-11 23:14:48');
INSERT INTO Messages (con_group_id, user_id, content, tstamp) VALUES (1, 2, 'Hey Man!!2', '2020-11-11 23:14:49');

DELETE FROM PostsPermissions;
INSERT INTO PostsPermissions (posts_permissions_code, enabled, description) VALUES ('VON',true , 'Stands for View ONly. No one can comment on the post');
INSERT INTO PostsPermissions (posts_permissions_code, enabled, description) VALUES ('VAC',true , 'Stands for View And Comment. People will be able to view and comment on the post');
INSERT INTO PostsPermissions (posts_permissions_code, enabled, description) VALUES ('VAL',true , 'Stands for View And Link. People will be able to view and link the post');

DELETE FROM PostsAccessabilities;
INSERT INTO PostsAccessabilities (posts_accessabilities_code, enabled, description) VALUES ('PUB',true , 'Any one will be able to see the post');
INSERT INTO PostsAccessabilities (posts_accessabilities_code, enabled, description) VALUES ('CON',true , 'Only the contacts will be able to see the post');
INSERT INTO PostsAccessabilities (posts_accessabilities_code, enabled, description) VALUES ('PRI',true , 'Only the poster will be able to see the post');











