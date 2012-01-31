BEGIN;

CREATE TABLE api_keys (
  key varchar(32) PRIMARY KEY,
  username varchar(255) not null,
  active boolean default 'true',
	created_at timestamptz default now()
);


CREATE TABLE api_sessions (
  session_id varchar(32) PRIMARY KEY,
	created_at timestamptz default now()  
);

ALTER TABLE api_keys OWNER TO rig;
ALTER TABLE api_sessions OWNER TO rig;

GRANT SELECT, INSERT on api_keys to rig;
GRANT SELECT, INSERT on api_sessions to rig;

INSERT INTO api_keys(key,username) VALUES ('f3c14382068e9b476077ce6f885c77f2', 'RiG iPhone');
INSERT INTO api_keys(key,username) VALUES ('a7440ae35c0d838dfb147f7c1a976c8a', 'RiG iPad');
