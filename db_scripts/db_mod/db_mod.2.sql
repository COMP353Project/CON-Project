create table roles (
                       id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                       name TEXT NOT NULL,
                       description TEXT
);


insert into roles
(name, description)
values
('superuser', 'SUPER USER CAN DO ANYTHING'),
('assadmin', 'Condo association admin can only make changes specific to their condo association'),
('standard', 'Standard user can only affect their own profile');