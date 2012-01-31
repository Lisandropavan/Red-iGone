--CREATE DATABASE rig OWNER rig;

BEGIN;
CREATE TABLE image_queue (
	id serial,
	image varchar(32) not null,
	th integer not null,
	selections varchar(2048) default null,
	status varchar(10) default 'new',
	owner varchar(255) default null,
	created_at timestamptz default now(),
	processed_at timestamptz default null,
	completed_at timestamptz default null,
	resize varchar(11) not null,
  device_id varchar(255) not null,
  ip varchar(15) not null,
  width varchar(5) default null,
  height varchar(5) default null,
  bytes varchar(10) default null
);

ALTER TABLE image_queue OWNER TO rig;

GRANT SELECT, INSERT on image_queue to rig;

--UPDATES ONLY BELOW -- DO NOT RUN ON NEW SERVER DEPLOYMENT

--update adding adaptive resize maximum dimensions column
--ALTER TABLE image_queue ADD COLUMN resize varchar(11) default null;

--update adding device id and ip address
--ALTER TABLE image_queue ADD COLUMN device_id varchar(255) not null;
--ALTER TABLE image_queue ADD COLUMN ip varchar(15) not null;

--update adding device id and ip address
--ALTER TABLE image_queue ADD COLUMN width varchar(5) default null;
--ALTER TABLE image_queue ADD COLUMN height varchar(5) default null;
--ALTER TABLE image_queue ADD COLUMN bytes varchar(10) default null;
