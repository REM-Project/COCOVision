/*必須*/
CREATE DATABASE cocovision;
use cocovision;

CREATE TABLE room_info(
id int AUTO_INCREMENT PRIMARY KEY NOT NULL
,room_name varchar(256) UNIQUE NOT NULL
,table_name varchar(256) UNIQUE NOT NULL
,room_capacity int
,device_ip_address varchar(16)
,num_camera int default 0
);

CREATE TABLE values_template(
id int AUTO_INCREMENT PRIMARY KEY NOT NULL
,rec_time datetime
,co2 float
,temp float
,humi float
,cong float
);

CREATE USER worker@'%' IDENTIFIED BY 'th1117';
GRANT create,delete ON cocovision.* TO worker@'%';

CREATE USER recorder@'%' IDENTIFIED BY 'th1117';
GRANT INSERT ON cocovision.* TO recorder@'%';
GRANT SELECT,UPDATE ON cocovision.room_info TO recorder@'%';

CREATE USER webuser@'%' IDENTIFIED BY 'th1117';
GRANT SELECT ON cocovision.* TO webuser@'%';





/*初期化（３部屋生成）*/
create table system_values like values_template;
create table hardware_values like values_template;
create table software_values like values_template;

insert into room_info(room_name,table_name,room_capacity) values
('システム実習室','system_values',20)
,('ハードウェア実習室','hardware_values',20)
,('ソフトウェア実習室','software_values',20)
;






