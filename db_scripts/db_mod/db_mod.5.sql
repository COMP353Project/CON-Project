alter table building add column name text not null;

alter table condo_unit add column buildingid INT;
alter table condo_unit add constraint foreign key (buildingid) references building(id);

alter table building modify buildingid INT NOT NULL;

alter table condo_association add column createdon timestamp not null default now();


alter table condo_unit modify ownerid int default NULL;