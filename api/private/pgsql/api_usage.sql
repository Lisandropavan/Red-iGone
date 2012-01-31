BEGIN;

CREATE TABLE api_uploads (
  device_id varchar(255) PRIMARY KEY,
  counter integer not null,
  last_ip varchar(15) not null,
  last_accessed timestamptz default now()
);

CREATE TABLE api_thumbs (
  device_id varchar(255) PRIMARY KEY,
  counter integer not null,
  last_ip varchar(15) not null,
  last_accessed timestamptz default now()  
);

CREATE TABLE api_usage (
  device_id varchar(255) PRIMARY KEY,
  device_name varchar(255) default null,
  device_systemName varchar(255) default null,
  device_systemVersion varchar(255) default null,
  device_model varchar(255) default null,
  device_localizedModel varchar(255) default null,
  app_name varchar(255) default null,
  app_version varchar(30) default null,  
  counter integer not null,
  last_ip varchar(15) not null,
  last_accessed timestamptz default now()
);

ALTER TABLE api_uploads OWNER TO rig;
ALTER TABLE api_thumbs OWNER TO rig;
ALTER TABLE api_usage OWNER TO rig;

GRANT SELECT, INSERT on api_uploads to rig;
GRANT SELECT, INSERT on api_thumbs to rig;
GRANT SELECT, INSERT on api_usage to rig;