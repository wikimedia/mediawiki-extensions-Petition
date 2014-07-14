-- Adds tables for petition
CREATE TABLE /*_*/petition_data (
	pt_id int unsigned PRIMARY KEY auto_increment,
	pt_petitionname varchar(255),
	pt_source varchar(255),
	pt_name varchar(255),
	pt_email varchar(255),
	pt_country varchar(2),
	pt_message blob,
	pt_share boolean,
	pt_timestamp varbinary(14)
) /*$wgDBTableOptions*/;

CREATE INDEX /*i*/pt_petitionname ON /*_*/petition_data (pt_petitionname);