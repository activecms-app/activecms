
delete from Users;
delete from Users_Roles;
delete from Roles;

-- Roles
insert into Roles value
('1', 'administrator', ''),
('2', 'editor', ''),
('3', 'writer', '');

-- Users
insert into Users(Id, Username, Email, Pass, FirstName, LastName, Notify, Observation, RecoverCode, UserStatus) values
('1', 'admin', '', 'activecms', 'Administrator', 'General', 'never', NULL, NULL, 'active');

-- UserRoles
insert into Users_Roles values 
('1', '1');

delete from Webs;
delete from ObjectsVersion;
delete from Objects;
delete from Types;

-- Types
insert into Types (Id, Name, Description, Class, Template, TypeStatus) values
('1', 'Inicio', NULL, 'folder', 'inicio', 'active');

-- Objects
insert into Objects (Id, Version, Types_Id, Name, DisplayBegin, DisplayEnd, Template, Published, LastChange, LastUser_Id, Deleted) values
('1', '1', '1', 'home', NULL, NULL, NULL, 'yes', now(), '1', 'no');

-- Objects Version
insert into ObjectsVersion (Objects_Id, Version, Title, Description) values
('1', '1', 'Home', 'Home');

-- Webs
insert into Webs (Id, Objects_Id, Name, Host, Url, Themes_Id, Published) values
('1', '1', 'Web', 'host', 'url', '1', 'yes');

