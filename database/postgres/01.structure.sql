CREATE EXTENSION hstore;

DROP TABLE IF EXISTS tb_global_ids;
CREATE TABLE tb_global_ids (
  global_id            bigserial PRIMARY KEY,
  global_hash          varchar(256) NOT NULL UNIQUE,
  global_time          timestamp DEFAULT CURRENT_TIMESTAMP
);
GRANT SELECT, INSERT ( global_hash ) ON tb_global_ids TO :var_user;
GRANT USAGE, SELECT ON SEQUENCE tb_global_ids_global_id_seq TO :var_user;

DROP VIEW IF EXISTS v_global_ids;
DROP TABLE IF EXISTS tb_global_id_ipaddress;
CREATE TABLE tb_global_id_ipaddress (
  global_id_ipaddress_id            bigserial PRIMARY KEY,
  global_id_ipaddress               varchar(39) NOT NULL,
  global_id_ipaddress_user_agent    varchar(256) NOT NULL,
  global_id_ipaddress_time          timestamp DEFAULT CURRENT_TIMESTAMP,
  global_id_ipaddress_lastseen      timestamp DEFAULT CURRENT_TIMESTAMP,
  global_id_ipaddress_global_id     bigint NOT NULL REFERENCES tb_global_ids ( global_id ) ON DELETE CASCADE
);
GRANT SELECT, INSERT ( global_id_ipaddress, global_id_ipaddress_global_id, global_id_ipaddress_user_agent ), UPDATE ( global_id_ipaddress_lastseen ) ON tb_global_id_ipaddress TO :var_user;
GRANT USAGE, SELECT ON SEQUENCE tb_global_id_ipaddress_global_id_ipaddress_id_seq TO :var_user;

DROP TABLE IF EXISTS tb_blocks;
CREATE TABLE tb_blocks (
  block_ipaddress     varchar(39) NOT NULL,
  block_time          timestamp NOT NULL
);
GRANT SELECT, INSERT ON tb_blocks TO :var_user;

DROP TABLE IF EXISTS tb_network_zone;
CREATE TABLE tb_network_zone (
  nz_id			smallserial NOT NULL PRIMARY KEY,
  nz_network     	cidr NOT NULL UNIQUE,
  nz_internal		boolean DEFAULT false,
  nz_trusted 		boolean DEFAULT false,
  nz_time        	timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
);
GRANT SELECT, INSERT ( nz_network, nz_internal, nz_trusted ) ON tb_network_zone TO :var_user;
GRANT USAGE, SELECT ON SEQUENCE tb_network_zone_nz_id_seq TO :var_user;

DROP TABLE IF EXISTS tb_config;
CREATE TABLE tb_config (
  config_name     varchar(64) NOT NULL,
  config_value    varchar(64) NOT NULL,
  config_editable boolean DEFAULT true,
  CONSTRAINT "U1" UNIQUE ("config_name")
);
GRANT SELECT, INSERT, UPDATE, DELETE ON tb_config TO :var_user;

DROP TABLE IF EXISTS tb_languages;
CREATE TABLE tb_languages (
  language_id                   smallserial PRIMARY KEY,
  language_codeset              smallint NOT NULL UNIQUE,
  language_code                 char(5) NOT NULL UNIQUE,
  language_wui                  boolean NOT NULL DEFAULT false,
  language_name                 hstore
);
GRANT SELECT, INSERT, UPDATE, DELETE ON tb_languages TO :var_user;

DROP TABLE IF EXISTS tb_sentences_page;
DROP TABLE IF EXISTS tb_sentences;
CREATE TABLE tb_sentences (
  sentence_id                   bigserial PRIMARY KEY,
  sentence_alias                varchar(128) UNIQUE,
  sentence_value                hstore
);
GRANT SELECT, INSERT, UPDATE, DELETE ON tb_sentences TO :var_user;
GRANT USAGE, SELECT ON SEQUENCE tb_sentences_sentence_id_seq TO :var_user;

DROP VIEW IF EXISTS v_sentences_page;
DROP TABLE IF EXISTS tb_sentences_page;
CREATE TABLE tb_sentences_page (
  sentence_page_alias               varchar(128),
  sentence_page_sentence_id         bigint REFERENCES tb_sentences (sentence_id) ON DELETE CASCADE,
  CONSTRAINT "tb_sentences_page_U1" UNIQUE ("sentence_page_alias", "sentence_page_sentence_id")
);
GRANT SELECT, INSERT, UPDATE, DELETE ON tb_sentences_page TO :var_user;

DROP TABLE IF EXISTS tb_permissions;
CREATE TABLE tb_permissions (
  permission_id					serial PRIMARY KEY,
  permission_alias 			  	varchar(64) UNIQUE,
  permission_sentence_id 			bigint REFERENCES tb_sentences (sentence_id) ON DELETE RESTRICT
);
GRANT SELECT ON tb_permissions TO :var_user;

DROP TABLE IF EXISTS tb_menu;
CREATE TABLE tb_menu (
  menu_id						    serial PRIMARY KEY,
  menu_alias 					  varchar(64) UNIQUE,
  menu_sentence_id 			bigint REFERENCES tb_sentences (sentence_id) ON DELETE RESTRICT,
  menu_parent           int DEFAULT NULL REFERENCES tb_menu (menu_id) ON DELETE CASCADE,
  menu_permission_id    int DEFAULT NULL REFERENCES tb_permissions (permission_id) ON DELETE RESTRICT,
  menu_wui              boolean NOT NULL DEFAULT true,
  menu_order            smallint NOT NULL DEFAULT 0,
  menu_function 	      varchar(64) DEFAULT NULL,
  menu_info             hstore DEFAULT NULL,
  menu_prelude 	        bigint DEFAULT NULL REFERENCES tb_sentences (sentence_id) ON DELETE RESTRICT
);
GRANT SELECT, UPDATE ( menu_prelude, menu_sentence_id, menu_alias, menu_parent, menu_permission_id, menu_wui, menu_order, menu_function, menu_info ), INSERT ( menu_prelude, menu_sentence_id, menu_alias, menu_parent, menu_permission_id, menu_wui, menu_order, menu_function, menu_info ) ON tb_menu TO :var_user;

DROP TABLE IF EXISTS tb_panel;
CREATE TABLE tb_panel (
  panel_id			serial PRIMARY KEY,
  panel_alias 			varchar(64) UNIQUE,
  panel_sentence_id 		bigint REFERENCES tb_sentences (sentence_id) ON DELETE RESTRICT,
  panel_permission_id   	int DEFAULT NULL REFERENCES tb_permissions (permission_id) ON DELETE RESTRICT,
  panel_floor  			smallint DEFAULT 1,
  panel_icon            	varchar(64) DEFAULT NULL,
  panel_order           	smallint NOT NULL DEFAULT 0
);
GRANT SELECT ON tb_panel TO :var_user;

DROP TABLE IF EXISTS tb_groups;
CREATE TABLE tb_groups (
  group_id              serial PRIMARY KEY,
  group_alias           varchar(64) UNIQUE,
  group_description     bigint REFERENCES tb_sentences(sentence_id) ON DELETE RESTRICT,
  group_power           smallint DEFAULT 0 NOT NULL
);
GRANT SELECT, INSERT, UPDATE, DELETE ON tb_groups TO :var_user;

DROP TABLE IF EXISTS tb_relation_group_permission;
CREATE TABLE tb_relation_group_permission (
  relation_group_permission_group               smallint REFERENCES tb_groups(group_id) On DELETE CASCADE,
  relation_group_permission_permission          int REFERENCES tb_permissions(permission_id) On DELETE CASCADE,
  relation_group_permission_allowed             boolean NOT NULL DEFAULT false,
  relation_group_permission_blocked             boolean NOT NULL DEFAULT false,
  relation_group_permission_accept_internal	boolean DEFAULT true,
  relation_group_permission_accept_trusted 	boolean DEFAULT true,
  relation_group_permission_accept_external	boolean DEFAULT true,
  PRIMARY KEY ( relation_group_permission_group, relation_group_permission_permission ),
  CHECK ( relation_group_permission_allowed <> relation_group_permission_blocked )
);
GRANT SELECT, INSERT, UPDATE, DELETE ON tb_relation_group_permission TO :var_user;

DROP TABLE IF EXISTS tb_user_passwords;
DROP TABLE IF EXISTS tb_user_auth;
DROP TABLE IF EXISTS tb_relation_user_group;
DROP TABLE IF EXISTS tb_relation_user_permission;
DROP TABLE IF EXISTS tb_users;
CREATE TABLE tb_users (
  user_id                     bigserial PRIMARY KEY,
  user_nickname               varchar(16) NOT NULL UNIQUE,
  user_nickname_hashed        varchar(256) NOT NULL UNIQUE,
  user_firstname              varchar(128) DEFAULT NULL,
  user_fullname               text DEFAULT NULL,
  user_lastname               varchar(128) DEFAULT NULL,
  user_primaryemail           varchar(256) NOT NULL UNIQUE,
  user_primaryemail_hashed    varchar(256) NOT NULL UNIQUE,
  user_hash                   varchar(64) NOT NULL UNIQUE,
  user_attributes             hstore DEFAULT '"items_per_page"=>"50"',
  user_language               smallint REFERENCES tb_languages ( language_id ) ON DELETE RESTRICT,
  CONSTRAINT tb_users_nickname CHECK (length(user_nickname) > 2 AND length(user_nickname) < 17 ),
  CONSTRAINT tb_users_firstname CHECK (length(user_firstname) > 2),
  CONSTRAINT tb_users_lastname CHECK (length(user_lastname) > 2),
  CONSTRAINT tb_users_primaryemail CHECK (length(user_primaryemail) > 8 AND user_primaryemail LIKE '%@%')
);
GRANT SELECT, INSERT, UPDATE, DELETE ON tb_users TO :var_user;
GRANT USAGE, SELECT ON SEQUENCE tb_users_user_id_seq TO :var_user;

DROP TABLE IF EXISTS tb_user_balance;
CREATE TABLE tb_user_balance (
  ub_user_id		bigint REFERENCES tb_users ( user_id ) ON DELETE CASCADE NOT NULL,
  ub_ammount 		money NOT NULL,
  ub_time 		timestamp DEFAULT CURRENT_TIMESTAMP,
  ub_description 	bigint REFERENCES tb_sentences ( sentence_id ) ON DELETE RESTRICT NOT NULL,
  ub_details 		json
);
GRANT SELECT, INSERT ( ub_ammount, ub_description ) ON tb_user_balance TO :var_user;

DROP TABLE IF EXISTS tb_payment_methods;
CREATE TABLE tb_payment_methods (
  payment_method_id   		smallserial PRIMARY KEY,
  payment_method_alias          varchar(64) NOT NULL UNIQUE,
  payment_method_time           timestamp DEFAULT CURRENT_TIMESTAMP
);
GRANT SELECT, INSERT, UPDATE, DELETE ON tb_payment_methods TO :var_user;
GRANT USAGE, SELECT ON SEQUENCE tb_payment_methods_payment_method_id_seq TO :var_user;

DROP TABLE IF EXISTS tb_payment_status;
CREATE TABLE tb_payment_status (
  payment_status_id   		smallserial PRIMARY KEY,
  payment_status_alias          varchar(64) NOT NULL UNIQUE,
  payment_status_sentence       bigint NOT NULL REFERENCES tb_sentences ( sentence_id ) ON DELETE CASCADE,
  payment_status_time           timestamp DEFAULT CURRENT_TIMESTAMP
);
GRANT SELECT, INSERT, UPDATE, DELETE ON tb_payment_status TO :var_user;
GRANT USAGE, SELECT ON SEQUENCE tb_payment_status_payment_status_id_seq TO :var_user;

DROP TABLE IF EXISTS tb_user_payments;
CREATE TABLE tb_user_payments (
  user_payment_id		bigserial PRIMARY KEY,
  user_payment_method		smallint NOT NULL REFERENCES tb_payment_methods ( payment_method_id ) ON DELETE RESTRICT,
  user_payment_unique		text,
  user_payment_user_id		bigint DEFAULT NULL REFERENCES tb_users ( user_id ) ON DELETE CASCADE,
  user_payment_time		timestamp DEFAULT CURRENT_TIMESTAMP
);
GRANT SELECT, INSERT, UPDATE, DELETE ON tb_user_payments TO :var_user;
GRANT USAGE, SELECT ON SEQUENCE tb_user_payments_user_payment_id_seq TO :var_user;


DROP TABLE IF EXISTS tb_user_passwords;
CREATE TABLE tb_user_passwords (
  user_password_id              bigserial PRIMARY KEY,
  user_password_user_id         bigint REFERENCES tb_users(user_id) On DELETE CASCADE,
  user_password_hash            varchar(256) NOT NULL,
  user_password_time            timestamp DEFAULT CURRENT_TIMESTAMP
);
GRANT SELECT, INSERT ON tb_user_passwords TO :var_user;
GRANT USAGE, SELECT ON SEQUENCE tb_user_passwords_user_password_id_seq TO :var_user;

DROP TABLE IF EXISTS tb_relation_user_permission;
CREATE TABLE tb_relation_user_permission (
  relation_user_permission_user                bigint REFERENCES tb_users(user_id) On DELETE CASCADE,
  relation_user_permission_permission          int REFERENCES tb_permissions(permission_id) On DELETE CASCADE,
  relation_user_permission_allowed             boolean NOT NULL DEFAULT false,
  relation_user_permission_blocked             boolean NOT NULL DEFAULT false,
  relation_user_permission_accept_internal	boolean DEFAULT true,
  relation_user_permission_accept_trusted 	boolean DEFAULT true,
  relation_user_permission_accept_external	boolean DEFAULT true,
  PRIMARY KEY ( relation_user_permission_user, relation_user_permission_permission ),
  CHECK ( relation_user_permission_allowed <> relation_user_permission_blocked )
);
GRANT SELECT, INSERT, UPDATE, DELETE ON tb_relation_user_permission TO :var_user;

DROP TABLE IF EXISTS tb_relation_user_group;
CREATE TABLE tb_relation_user_group (
  relation_user_group_user                      bigint REFERENCES tb_users(user_id) On DELETE CASCADE,
  relation_user_group_group                     smallint REFERENCES tb_groups(group_id) On DELETE CASCADE,
  PRIMARY KEY ( relation_user_group_user, relation_user_group_group )
);
GRANT SELECT, INSERT, UPDATE, DELETE ON tb_relation_user_group TO :var_user;

DROP TABLE IF EXISTS tb_user_auth;
CREATE TABLE tb_user_auth (
  user_auth_id              bigserial PRIMARY KEY,
  user_auth_user_id         bigint REFERENCES tb_users(user_id) On DELETE CASCADE,
  user_auth_hash            varchar(256) NOT NULL UNIQUE,
  user_auth_time            timestamp DEFAULT CURRENT_TIMESTAMP,
  user_auth_last_used       timestamp DEFAULT CURRENT_TIMESTAMP,
  user_auth_global_id       bigint NOT NULL REFERENCES tb_global_ids ( global_id ) ON DELETE RESTRICT,
  user_auth_active          boolean NOT NULL DEFAULT true
);
GRANT SELECT, INSERT ( user_auth_user_id, user_auth_hash, user_auth_global_id ), UPDATE ( user_auth_last_used, user_auth_active ) ON tb_user_auth TO :var_user;
GRANT USAGE, SELECT ON SEQUENCE tb_user_auth_user_auth_id_seq TO :var_user;

DROP TABLE IF EXISTS tb_user_states;
CREATE TABLE tb_user_states (
  user_state_id           smallserial PRIMARY KEY,
  user_state_alias        varchar(64) NOT NULL UNIQUE,
  user_state_description  bigint REFERENCES tb_sentences (sentence_id) ON DELETE RESTRICT
);
GRANT SELECT, INSERT, UPDATE, DELETE ON tb_user_states TO :var_user;

DROP TABLE IF EXISTS tb_relation_user_state;
CREATE TABLE tb_relation_user_state (
  relation_user_state_user                bigint REFERENCES tb_users(user_id) On DELETE CASCADE,
  relation_user_state_state               int REFERENCES tb_user_states(user_state_id) On DELETE CASCADE,
  relation_user_state_time                timestamp DEFAULT CURRENT_TIMESTAMP,
  relation_user_state_hash                varchar(64) DEFAULT NULL UNIQUE,
  PRIMARY KEY ( relation_user_state_user, relation_user_state_state, relation_user_state_time )
);
GRANT SELECT, INSERT, UPDATE, DELETE ON tb_relation_user_state TO :var_user;

DROP TABLE IF EXISTS tb_tags;
CREATE TABLE tb_tags (
  tag_id          bigserial PRIMARY KEY,
  tag_alias       varchar(256) NOT NULL UNIQUE,
  tag_aka         text[] NOT NULL,
  tag_sentence_id bigint REFERENCES tb_sentences ( sentence_id ) ON DELETE CASCADE
);  
GRANT SELECT, INSERT ( tag_alias, tag_aka, tag_sentence_id), UPDATE(tag_aka)  ON tb_tags TO :var_user;
GRANT USAGE, SELECT ON SEQUENCE tb_tags_tag_id_seq TO :var_user;

DROP TABLE IF EXISTS tb_media;
CREATE TABLE tb_media (
  media_id                bigserial PRIMARY KEY,
  media_alias             varchar(64) NOT NULL UNIQUE,
  media_info              json,
  media_cover             json,
  media_small             json,
  media_medium            json,
  media_time              timestamp DEFAULT CURRENT_TIMESTAMP,
  media_tags              int[]
);
GRANT SELECT, INSERT ( media_alias, media_info, media_cover, media_medium, media_small, media_tags ), UPDATE ( media_info, media_cover, media_small, media_medium, media_tags ), DELETE ON tb_media TO :var_user;
GRANT USAGE, SELECT ON SEQUENCE tb_media_media_id_seq TO :var_user;

DROP TABLE IF EXISTS tb_relation_media_reviewed;
CREATE TABLE tb_relation_media_reviewed (
  rmv_media_id      bigint REFERENCES tb_media ( media_id ) ON DELETE CASCADE,
  rmv_user_id       bigint DEFAULT NULL REFERENCES tb_users ( user_id ) ON DELETE CASCADE,
  rmv_time          timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY ( rmv_media_id )
);
GRANT SELECT, INSERT ( rmv_media_id, rmv_user_id ), DELETE ON tb_relation_media_reviewed TO :var_user;

DROP TABLE IF EXISTS tb_relation_media_tag;
CREATE TABLE tb_relation_media_tag (
  rmt_media_id      bigint REFERENCES tb_media ( media_id ) ON DELETE CASCADE,
  rmt_tag_id        bigint REFERENCES tb_tags ( tag_id ) ON DELETE CASCADE,
  rmt_user_id       bigint DEFAULT NULL REFERENCES tb_users ( user_id ) ON DELETE CASCADE,
  rmt_global_id     bigint DEFAULT NULL REFERENCES tb_global_ids ( global_id ) ON DELETE CASCADE,
  rmt_time          timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY ( rmt_media_id, rmt_tag_id )
);
GRANT SELECT, INSERT ( rmt_media_id, rmt_tag_id, rmt_user_id, rmt_global_id ), DELETE ON tb_relation_media_tag TO :var_user;

DROP TABLE IF EXISTS tb_relation_media_rating;
CREATE TABLE tb_relation_media_rating (
  rmr_media_id      bigint REFERENCES tb_media ( media_id ) ON DELETE CASCADE,
  rmr_user_id       bigint DEFAULT NULL REFERENCES tb_users ( user_id ) ON DELETE CASCADE,
  rmr_rating        numeric(2) DEFAULT 0 NOT NULL,
  rmr_time          timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY ( rmr_media_id, rmr_user_id )
);
GRANT SELECT, INSERT ( rmr_media_id, rmr_user_id, rmr_rating), UPDATE ( rmr_rating, rmr_time ) ON tb_relation_media_rating TO :var_user;

DROP TABLE IF EXISTS tb_relation_post_media;
DROP TABLE IF EXISTS tb_posts;
CREATE TABLE tb_posts (
  post_id                 bigserial PRIMARY KEY,
  post_alias              varchar(2048) DEFAULT NULL,
  post_unique             varchar(64) DEFAULT NULL,
  post_user_id            bigint DEFAULT NULL REFERENCES tb_users ( user_id ) ON DELETE CASCADE,
  post_global_id          bigint DEFAULT NULL REFERENCES tb_global_ids ( global_id ) ON DELETE CASCADE,
  post_title              varchar(256),
  post_description        text,
  post_hidden		  boolean DEFAULT false,
  post_public		  boolean DEFAULT true,
  post_published	  timestamp DEFAULT NULL,
  post_time               timestamp DEFAULT CURRENT_TIMESTAMP
);
GRANT SELECT, INSERT ( post_hidden, post_unique, post_alias, post_user_id, post_global_id, post_title, post_description ), UPDATE ( post_public, post_published, post_hidden, post_user_id, post_global_id, post_title, post_description, post_alias, post_time ), DELETE ON tb_posts TO :var_user;
GRANT USAGE, SELECT ON SEQUENCE tb_posts_post_id_seq TO :var_user;

DROP TABLE IF EXISTS tb_relation_post_media;
CREATE TABLE tb_relation_post_media (
  rpm_media_id      bigint REFERENCES tb_media ( media_id ) ON DELETE CASCADE,
  rpm_post_id       bigint REFERENCES tb_posts ( post_id ) ON DELETE CASCADE,
  PRIMARY KEY ( rpm_media_id, rpm_post_id )
);
GRANT SELECT, INSERT ( rpm_media_id, rpm_post_id ), DELETE ON tb_relation_post_media TO :var_user;

DROP TABLE IF EXISTS tb_emails;
CREATE TABLE tb_emails (
    email_id            bigserial PRIMARY KEY,
    email_subject       varchar(64) NOT NULL,
    email_from          varchar(256) NOT NULL,
    email_reply_to      varchar(256) DEFAULT NULL,
    email_html          boolean DEFAULT true,
    email_text          text,
    email_language      smallint DEFAULT NULL REFERENCES tb_languages ( language_id ) ON DELETE RESTRICT,
    email_signature     boolean DEFAULT false,
    email_time          timestamp DEFAULT CURRENT_TIMESTAMP,
    email_sent_time     timestamp DEFAULT NULL
);
GRANT SELECT, INSERT ( email_subject, email_from, email_html, email_text, email_reply_to, email_signature, email_language ), UPDATE ( email_sent_time ) ON tb_emails TO :var_user;
GRANT USAGE, SELECT ON SEQUENCE tb_emails_email_id_seq TO :var_user;

DROP TABLE IF EXISTS tb_email_to;
CREATE TABLE tb_email_to (
    email_to_id         bigserial PRIMARY KEY,
    email_to_email_id   bigint REFERENCES tb_emails ( email_id ) ON DELETE CASCADE,
    email_to_name       varchar(128) DEFAULT NULL,
    email_to_email      varchar(128) NOT NULL,
    CONSTRAINT "tb_email_to_U1" UNIQUE ("email_to_email_id", "email_to_email")
);
GRANT SELECT, INSERT ( email_to_email_id, email_to_name, email_to_email ) ON tb_email_to TO :var_user; 
GRANT USAGE, SELECT ON SEQUENCE tb_email_to_email_to_id_seq TO :var_user;

DROP TABLE IF EXISTS tb_email_bcc;
CREATE TABLE tb_email_bcc (
    email_bcc_id         bigserial PRIMARY KEY,
    email_bcc_email_id   bigint REFERENCES tb_emails ( email_id ) ON DELETE CASCADE,
    email_bcc_name       varchar(128) DEFAULT NULL,
    email_bcc_email      varchar(128) NOT NULL,
    CONSTRAINT "tb_email_bcc_U1" UNIQUE ("email_bcc_email_id", "email_bcc_email")
);
GRANT SELECT, INSERT ( email_bcc_email_id, email_bcc_name, email_bcc_email ) ON tb_email_bcc TO :var_user;
GRANT USAGE, SELECT ON SEQUENCE tb_email_bcc_email_bcc_id_seq TO :var_user;

DROP TABLE IF EXISTS tb_email_attachments;
CREATE TABLE tb_email_attachments (
    email_attachment_id         bigserial PRIMARY KEY,
    email_attachment_email_id   bigint REFERENCES tb_emails ( email_id ) ON DELETE CASCADE,
    email_attachment_file_id    bigint NOT NULL,
    CONSTRAINT "tb_email_attachments_U1" UNIQUE ("email_attachment_email_id", "email_attachment_file_id")
);
GRANT SELECT, INSERT ( email_attachment_email_id, email_attachment_file_id) ON tb_email_attachments TO :var_user;
GRANT USAGE, SELECT ON SEQUENCE tb_email_attachments_email_attachment_id_seq TO :var_user;

DROP TABLE IF EXISTS tb_ldap;
CREATE TABLE tb_ldap (
    ldap_id         	serial PRIMARY KEY,
    ldap_domain     	varchar(128) UNIQUE,
    ldap_server     	text[] DEFAULT NULL,
    ldap_aka        	text[],
    ldap_smbpasswd     	text[],
    ldap_auth_mask      text[] DEFAULT NULL,
    ldap_search		text,
    ldap_order		smallint DEFAULT 100,
    ldap_admin_user 	varchar(32) DEFAULT NULL,
    ldap_admin_password varchar(128) DEFAULT NULL,
    ldap_trusted	boolean DEFAULT true,
    ldap_time       	timestamp DEFAULT CURRENT_TIMESTAMP
);
GRANT SELECT, INSERT ( ldap_smbpasswd, ldap_trusted, ldap_search, ldap_domain, ldap_server, ldap_auth_mask, ldap_order, ldap_aka, ldap_admin_user, ldap_admin_password ), UPDATE (ldap_server, ldap_auth_mask, ldap_aka, ldap_order, ldap_admin_user, ldap_admin_password, ldap_trusted, ldap_search, ldap_smbpasswd )  ON tb_ldap TO :var_user;
GRANT USAGE, SELECT ON SEQUENCE tb_ldap_ldap_id_seq TO :var_user;

DROP TABLE IF EXISTS tb_relation_ldap_user;
CREATE TABLE tb_relation_ldap_user (
    trlu_ldap_id		int REFERENCES tb_ldap ( ldap_id ) ON DELETE CASCADE,
    trlu_user_id		bigint REFERENCES tb_users ( user_id ) ON DELETE CASCADE DEFAULT NULL,
    trlu_alias			varchar(128) NOT NULL,
    trlu_password		varchar(256) NOT NULL,
    trlu_email 			text[],
    trlu_token			varchar(384) UNIQUE,
    trlu_data			json,
    trlu_time			timestamp DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT "tb_tb_relation_ldap_user_U1" UNIQUE ( "trlu_ldap_id", "trlu_user_id" ),
    CONSTRAINT "tb_tb_relation_ldap_user_U2" UNIQUE ( "trlu_ldap_id", "trlu_alias" )
);
GRANT SELECT, INSERT ( trlu_ldap_id, trlu_data, trlu_user_id, trlu_alias, trlu_password, trlu_email, trlu_token ), UPDATE ( trlu_password, trlu_email, trlu_token, trlu_user_id, trlu_data ) ON tb_relation_ldap_user TO :var_user;

DROP TABLE IF EXISTS tb_task_status;
CREATE TABLE tb_task_status (
    task_status_id             smallserial PRIMARY KEY,
    task_status_alias          varchar(64) NOT NULL UNIQUE,
    task_status_sentence       bigint REFERENCES tb_sentences ( sentence_id ) ON DELETE CASCADE
);
GRANT USAGE, SELECT ON SEQUENCE tb_task_status_task_status_id_seq TO :var_user;
GRANT SELECT, INSERT ( task_status_alias,task_status_sentence ), UPDATE ( task_status_alias,task_status_sentence ) ON tb_task_status TO :var_user;

DROP TABLE IF EXISTS tb_relation_task_status;
DROP TABLE IF EXISTS tb_tasks;
CREATE TABLE tb_tasks (
    task_id                     bigserial PRIMARY KEY,
    task_global_id              bigint DEFAULT NULL REFERENCES tb_global_ids (global_id) ON DELETE CASCADE,
    task_user_id                bigint DEFAULT NULL REFERENCES tb_users (user_id) ON DELETE CASCADE,
    task_time                   timestamp DEFAULT CURRENT_TIMESTAMP,
    task_schedule_time          timestamp DEFAULT CURRENT_TIMESTAMP,
    task_done                   boolean DEFAULT false,
    task_hidden                 boolean DEFAULT false,
    task_progress             	smallint DEFAULT 0,
    task_parameters             json,
    task_result                 json
);
GRANT SELECT, INSERT ( task_global_id, task_user_id, task_parameters, task_schedule_time ), UPDATE ( task_progress, task_done, task_hidden, task_result, task_schedule_time ) ON tb_tasks TO :var_user;
GRANT USAGE, SELECT ON SEQUENCE tb_tasks_task_id_seq TO :var_user;

DROP TABLE IF EXISTS tb_relation_task_status;
CREATE TABLE tb_relation_task_status (
  trts_task_id                  bigint REFERENCES tb_tasks ( task_id ) On DELETE CASCADE,
  trts_task_status_id           bigint REFERENCES tb_task_status ( task_status_id ) On DELETE CASCADE,
  trts_time                     timestamp DEFAULT CURRENT_TIMESTAMP
);
GRANT SELECT, INSERT ( trts_task_id, trts_task_status_id ) ON tb_relation_task_status TO :var_user;

--# VIEWS
DROP VIEW IF EXISTS v_sentences_page;
CREATE VIEW v_sentences_page AS (
  SELECT  tsp.sentence_page_alias AS page_alias,
          ts.sentence_alias AS sentence_alias,
          ts.sentence_value AS sentence_value
  FROM tb_sentences_page tsp
  JOIN tb_sentences ts ON tsp.sentence_page_sentence_id=ts.sentence_id
);
GRANT SELECT ON v_sentences_page TO :var_user;

DROP VIEW IF EXISTS v_global_ids;
CREATE VIEW v_global_ids AS (
  SELECT  global_id,
          global_hash,
          (SELECT json_build_object('ipaddress_id',tgii.global_id_ipaddress_id,'ipaddress',tgii.global_id_ipaddress, 'useragent',tgii.global_id_ipaddress_user_agent,'lastseen',tgii.global_id_ipaddress_lastseen)  FROM tb_global_id_ipaddress tgii WHERE tgii.global_id_ipaddress_global_id=tgi.global_id ORDER BY global_id_ipaddress_lastseen DESC LIMIT 1) AS global_info
  FROM tb_global_ids tgi
);
GRANT SELECT ON v_global_ids TO :var_user;

DROP VIEW IF EXISTS v_permissions;
CREATE VIEW v_permissions AS (
  SELECT  permission_alias,
          sentence_value
  FROM tb_permissions tp
  JOIN tb_sentences ts ON tp.permission_sentence_id=ts.sentence_id
);
GRANT SELECT ON v_permissions TO :var_user;

DROP VIEW IF EXISTS v_user_passwords;
CREATE VIEW v_user_passwords AS (
  SELECT  tu.user_id AS user_id,
          tu.user_hash AS uid,
          tu.user_nickname_hashed as user_nickname,
          tu.user_primaryemail_hashed as user_primaryemail,
          (SELECT user_password_hash FROM tb_user_passwords tup WHERE tup.user_password_user_id=tu.user_id ORDER BY user_password_time DESC LIMIT 1) AS user_password
  FROM tb_users tu
);
GRANT SELECT ON v_user_passwords TO :var_user;

DROP VIEW IF EXISTS v_user_permissions_v1;
CREATE VIEW v_user_permissions_v1 AS (
  WITH group_permissions_allowed AS (
    (SELECT tg.group_id AS group_id, tg.group_power AS group_power, trgp.relation_group_permission_permission AS permission_id, tp.permission_alias AS permission_alias FROM tb_relation_group_permission trgp JOIN tb_groups tg ON trgp.relation_group_permission_group=tg.group_id JOIN tb_permissions tp ON tp.permission_id=trgp.relation_group_permission_permission WHERE trgp.relation_group_permission_allowed=true) 
  ), group_permissions_blocked AS (
    (SELECT tg.group_id AS group_id, tg.group_power AS group_power, trgp.relation_group_permission_permission AS permission_id FROM tb_relation_group_permission trgp JOIN tb_groups tg ON trgp.relation_group_permission_group=tg.group_id WHERE trgp.relation_group_permission_blocked=true) 
  ), group_permissions AS (
    SELECT  gpa.group_id AS group_id,
            (CASE(gpb.group_power IS NULL OR gpa.group_power>gpb.group_power) WHEN true THEN gpa.permission_id ELSE null END) as permission_id
    FROM group_permissions_allowed gpa
    LEFT JOIN group_permissions_blocked gpb ON (gpb.group_id=gpb.group_id AND gpb.permission_id=gpa.permission_id)
  ), user_permissions_allowed AS (
    (SELECT trup.relation_user_permission_user AS user_id, trup.relation_user_permission_permission AS permission_id, tp.permission_alias AS permission_alias FROM tb_relation_user_permission trup JOIN tb_permissions tp ON tp.permission_id=trup.relation_user_permission_permission WHERE trup.relation_user_permission_allowed=true) 
  ), user_permissions_blocked AS (
    (SELECT trup.relation_user_permission_user AS user_id, trup.relation_user_permission_permission AS permission_id, tp.permission_alias AS permission_alias FROM tb_relation_user_permission trup JOIN tb_permissions tp ON tp.permission_id=trup.relation_user_permission_permission WHERE trup.relation_user_permission_blocked=true) 
  ), user_permissions AS (
    SELECT  tu.user_id AS user_id,
            array_remove(array_cat((SELECT array_agg(DISTINCT(CASE upb.permission_id IS NULL WHEN true THEN gp.permission_id ELSE null END)) FROM tb_groups tg JOIN tb_relation_user_group trug ON trug.relation_user_group_group=tg.group_id JOIN group_permissions gp ON gp.group_id=tg.group_id LEFT JOIN user_permissions_blocked upb ON upb.permission_id=gp.permission_id AND upb.user_id=tu.user_id WHERE trug.relation_user_group_user=tu.user_id AND gp.permission_id IS NOT NULL),(SELECT array_agg(upa.permission_id) FROM user_permissions_allowed upa WHERE upa.user_id=tu.user_id)),null) AS user_permissions
    FROM tb_users tu
  )
  SELECT  *,
     (SELECT array_agg(tg.group_alias) FROM tb_groups tg JOIN tb_relation_user_group trug ON trug.relation_user_group_group=tg.group_id WHERE trug.relation_user_group_user=tu.user_id) AS user_groups,
     array_remove(array_cat((SELECT array_agg(DISTINCT(CASE upb.permission_id IS NULL WHEN true THEN gp.permission_id ELSE null END)) FROM tb_groups tg JOIN tb_relation_user_group trug ON trug.relation_user_group_group=tg.group_id JOIN group_permissions gp ON gp.group_id=tg.group_id LEFT JOIN user_permissions_blocked upb ON upb.permission_id=gp.permission_id AND upb.user_id=tu.user_id WHERE trug.relation_user_group_user=tu.user_id AND gp.permission_id IS NOT NULL),(SELECT array_agg(upa.permission_id) FROM user_permissions_allowed upa WHERE upa.user_id=tu.user_id)),null) AS user_permissions
  FROM tb_users tu
);
GRANT SELECT ON v_user_permissions_v1 TO :var_user;

DROP VIEW IF EXISTS v_groups;
CREATE VIEW v_groups AS (
  WITH menu_group_permissions AS (
    SELECT trgp.relation_group_permission_group AS group_id, tg.group_power AS group_power, tm2.menu_permission_id as permission_id, tp.permission_alias as permission_alias, trgp.relation_group_permission_accept_internal, trgp.relation_group_permission_accept_trusted, trgp.relation_group_permission_accept_external FROM tb_menu tm JOIN tb_menu tm2 ON tm.menu_parent=tm2.menu_id JOIN tb_permissions tp ON tp.permission_id=tm2.menu_permission_id JOIN tb_relation_group_permission trgp ON trgp.relation_group_permission_permission=tm.menu_permission_id JOIN tb_groups tg ON tg.group_id=trgp.relation_group_permission_group WHERE tm.menu_parent IS NOT NULL AND tm2.menu_permission_id IS NOT NULL AND trgp.relation_group_permission_allowed=true
  ), group_permissions_allowed AS (
    (SELECT tg.group_id AS group_id, tg.group_power AS group_power, trgp.relation_group_permission_permission AS permission_id, tp.permission_alias AS permission_alias, trgp.relation_group_permission_accept_internal, trgp.relation_group_permission_accept_trusted, trgp.relation_group_permission_accept_external FROM tb_relation_group_permission trgp JOIN tb_groups tg ON trgp.relation_group_permission_group=tg.group_id JOIN tb_permissions tp ON tp.permission_id=trgp.relation_group_permission_permission WHERE trgp.relation_group_permission_allowed=true)
  ), group_permissions_blocked AS (
    (SELECT tg.group_id AS group_id, tg.group_power AS group_power, trgp.relation_group_permission_permission AS permission_id FROM tb_relation_group_permission trgp JOIN tb_groups tg ON trgp.relation_group_permission_group=tg.group_id WHERE trgp.relation_group_permission_blocked=true)
  ), group_permissions AS (
     SELECT  gpa.group_id AS group_id,
            (CASE(gpb.group_power IS NULL OR gpa.group_power>gpb.group_power) WHEN true THEN gpa.permission_id ELSE null END) as permission_id,
            gpa.relation_group_permission_accept_internal AS permission_accept_internal,
            gpa.relation_group_permission_accept_trusted AS permission_accept_trusted,
            gpa.relation_group_permission_accept_external AS permission_accept_external
    FROM group_permissions_allowed gpa
    LEFT JOIN group_permissions_blocked gpb ON ((gpb.group_id=gpa.group_id AND gpb.permission_id=gpa.permission_id))
    JOIN tb_permissions tp ON gpa.permission_id=tp.permission_id
    UNION
    SELECT  mpg.group_id AS group_id,
            (CASE(gpb.group_power IS NULL OR mpg.group_power>gpb.group_power) WHEN true THEN mpg.permission_id ELSE null END) as permission_id,
            mpg.relation_group_permission_accept_internal AS permission_accept_internal,
            mpg.relation_group_permission_accept_trusted AS permission_accept_trusted,
            mpg.relation_group_permission_accept_external AS permission_accept_external
    FROM menu_group_permissions mpg
    LEFT JOIN group_permissions_blocked gpb ON ((gpb.group_id=mpg.group_id AND gpb.permission_id=mpg.permission_id))
    JOIN tb_permissions tp ON mpg.permission_id=tp.permission_id 
  )
  SELECT  tg.group_id,
          tg.group_alias,
          tg.group_power,
          (SELECT array_to_json(array_agg(tp.permission_alias)) FROM group_permissions gp JOIN tb_permissions tp ON tp.permission_id=gp.permission_id WHERE gp.group_id=tg.group_id AND gp.permission_accept_internal = true) AS group_permissions_internal,
          (SELECT array_to_json(array_agg(tp.permission_alias)) FROM group_permissions gp JOIN tb_permissions tp ON tp.permission_id=gp.permission_id WHERE gp.group_id=tg.group_id AND gp.permission_accept_trusted = true) AS group_permissions_trusted,
          (SELECT array_to_json(array_agg(tp.permission_alias)) FROM group_permissions gp JOIN tb_permissions tp ON tp.permission_id=gp.permission_id WHERE gp.group_id=tg.group_id AND gp.permission_accept_external = true) AS group_permissions_external,
          ts.sentence_value AS group_description
  FROM tb_groups tg
  JOIN tb_sentences ts ON tg.group_description=ts.sentence_id
);
GRANT SELECT ON v_groups TO :var_user;

DROP VIEW IF EXISTS v_users;
CREATE VIEW v_users AS (
  WITH menu_group_permissions AS (
    SELECT trgp.relation_group_permission_group AS group_id, tg.group_power AS group_power, tm2.menu_permission_id as permission_id, tp.permission_alias as permission_alias, trgp.relation_group_permission_accept_internal, trgp.relation_group_permission_accept_trusted, trgp.relation_group_permission_accept_external FROM tb_menu tm JOIN tb_menu tm2 ON tm.menu_parent=tm2.menu_id JOIN tb_permissions tp ON tp.permission_id=tm2.menu_permission_id JOIN tb_relation_group_permission trgp ON trgp.relation_group_permission_permission=tm.menu_permission_id JOIN tb_groups tg ON tg.group_id=trgp.relation_group_permission_group WHERE tm.menu_parent IS NOT NULL AND tm2.menu_permission_id IS NOT NULL AND trgp.relation_group_permission_allowed=true
  ), group_permissions_allowed AS (
    (SELECT tg.group_id AS group_id, tg.group_power AS group_power, trgp.relation_group_permission_permission AS permission_id, tp.permission_alias AS permission_alias, trgp.relation_group_permission_accept_internal, trgp.relation_group_permission_accept_trusted, trgp.relation_group_permission_accept_external FROM tb_relation_group_permission trgp JOIN tb_groups tg ON trgp.relation_group_permission_group=tg.group_id JOIN tb_permissions tp ON tp.permission_id=trgp.relation_group_permission_permission WHERE trgp.relation_group_permission_allowed=true)
  ), group_permissions_blocked AS (
    (SELECT tg.group_id AS group_id, tg.group_power AS group_power, trgp.relation_group_permission_permission AS permission_id FROM tb_relation_group_permission trgp JOIN tb_groups tg ON trgp.relation_group_permission_group=tg.group_id WHERE trgp.relation_group_permission_blocked=true)
  ), group_permissions AS (
    SELECT  gpa.group_id AS group_id,
            (CASE(gpb.group_power IS NULL OR gpa.group_power>gpb.group_power) WHEN true THEN gpa.permission_id ELSE null END) as permission_id,
            gpa.relation_group_permission_accept_internal AS permission_accept_internal,
            gpa.relation_group_permission_accept_trusted AS permission_accept_trusted,
            gpa.relation_group_permission_accept_external AS permission_accept_external
    FROM group_permissions_allowed gpa
    LEFT JOIN group_permissions_blocked gpb ON (gpb.group_id=gpa.group_id AND gpb.permission_id=gpa.permission_id)
    UNION
    SELECT  mpg.group_id AS group_id,
            (CASE(gpb.group_power IS NULL OR mpg.group_power>gpb.group_power) WHEN true THEN mpg.permission_id ELSE null END) as permission_id,
            mpg.relation_group_permission_accept_internal AS permission_accept_internal,
            mpg.relation_group_permission_accept_trusted AS permission_accept_trusted,
            mpg.relation_group_permission_accept_external AS permission_accept_external
    FROM menu_group_permissions mpg
    LEFT JOIN group_permissions_blocked gpb ON ((gpb.group_id=mpg.group_id AND gpb.permission_id=mpg.permission_id))
    JOIN tb_permissions tp ON mpg.permission_id=tp.permission_id 
  ), menu_user_permissions AS (
    SELECT trup.relation_user_permission_user AS user_id, tm2.menu_permission_id as permission_id, tp.permission_alias as permission_alias, trup.relation_user_permission_accept_internal AS permission_accept_internal, trup.relation_user_permission_accept_trusted AS permission_accept_trusted, trup.relation_user_permission_accept_external AS permission_accept_external FROM tb_menu tm JOIN tb_menu tm2 ON tm.menu_parent=tm2.menu_id JOIN tb_permissions tp ON tp.permission_id=tm2.menu_permission_id JOIN tb_relation_user_permission trup ON trup.relation_user_permission_permission=tm.menu_permission_id WHERE tm.menu_parent IS NOT NULL AND tm2.menu_permission_id IS NOT NULL AND trup.relation_user_permission_allowed=true
  ), user_permissions_allowed AS (
    (SELECT trup.relation_user_permission_user AS user_id, trup.relation_user_permission_permission AS permission_id, tp.permission_alias AS permission_alias, trup.relation_user_permission_accept_internal AS permission_accept_internal, trup.relation_user_permission_accept_trusted AS permission_accept_trusted, trup.relation_user_permission_accept_external AS permission_accept_external FROM tb_relation_user_permission trup JOIN tb_permissions tp ON tp.permission_id=trup.relation_user_permission_permission WHERE trup.relation_user_permission_allowed=true)
  ), user_permissions_blocked AS (
    (SELECT trup.relation_user_permission_user AS user_id, trup.relation_user_permission_permission AS permission_id, tp.permission_alias AS permission_alias FROM tb_relation_user_permission trup JOIN tb_permissions tp ON tp.permission_id=trup.relation_user_permission_permission WHERE trup.relation_user_permission_blocked=true)
  ), user_permissions AS (
    SELECT  tu.user_id AS user_id,
            gp.permission_id AS permission_id,
            tp.permission_alias AS permission_alias,
            gp.permission_accept_internal as internal,
            gp.permission_accept_trusted as trusted,
            gp.permission_accept_external as external
    FROM tb_users tu
    JOIN tb_relation_user_group trug ON trug.relation_user_group_user=user_id
    JOIN group_permissions gp ON trug.relation_user_group_group=gp.group_id
    JOIN tb_permissions tp ON gp.permission_id=tp.permission_id
    WHERE gp.permission_id NOT IN (SELECT upb.permission_id FROM user_permissions_blocked upb WHERE upb.user_id=tu.user_id )
    UNION
    SELECT upa.user_id,
           upa.permission_id AS permission_id,
           tp.permission_alias AS permission_alias,
           upa.permission_accept_internal as internal,
           upa.permission_accept_trusted as trusted,
           upa.permission_accept_external as external
    FROM user_permissions_allowed upa
    JOIN tb_permissions tp ON upa.permission_id=tp.permission_id
    UNION
    SELECT mup.user_id,
           mup.permission_id AS permission_id,
           tp.permission_alias AS permission_alias,
           mup.permission_accept_internal as internal,
           mup.permission_accept_trusted as trusted,
           mup.permission_accept_external as external
    FROM menu_user_permissions mup
    JOIN tb_permissions tp ON mup.permission_id=tp.permission_id
  )
  SELECT  user_id,
          user_nickname,
          user_firstname,
          user_fullname,
          user_lastname,
          user_primaryemail,
          user_hash,
          user_attributes,
          json_build_object('codeset',tl.language_codeset,'code',tl.language_code) AS user_language,
          (SELECT array_agg(tg.group_alias) FROM tb_groups tg JOIN tb_relation_user_group trug ON trug.relation_user_group_group=tg.group_id WHERE trug.relation_user_group_user=tu.user_id) AS user_groups,
          (SELECT json_build_object('state',tus.user_state_alias, 'hash',trus.relation_user_state_hash) FROM tb_relation_user_state trus JOIN tb_user_states tus ON tus.user_state_id=trus.relation_user_state_state ORDER BY relation_user_state_time DESC LIMIT 1) AS user_state,
          (SELECT array_to_json(array_agg(up.permission_alias)) FROM user_permissions up WHERE up.user_id=tu.user_id AND up.internal = true ) AS user_permissions_internal,
          (SELECT array_to_json(array_agg(up.permission_alias)) FROM user_permissions up WHERE up.user_id=tu.user_id AND up.external = true ) AS user_permissions_external,
          (SELECT array_to_json(array_agg(up.permission_alias)) FROM user_permissions up WHERE up.user_id=tu.user_id AND up.trusted = true ) AS user_permissions_trusted,
          (SELECT array_agg(tua.user_auth_hash) FROM tb_user_auth tua WHERE tua.user_auth_active = true AND tua.user_auth_user_id=tu.user_id AND (NOW() - INTERVAL '1 month') < tua.user_auth_last_used) AS user_auth
  FROM tb_users tu
  JOIN tb_languages tl ON tu.user_language=tl.language_id
);
GRANT SELECT ON v_users TO :var_user;

DROP VIEW IF EXISTS v_menu;
CREATE VIEW v_menu AS (
  SELECT  tm.menu_id,
          tm.menu_alias,
          ts.sentence_value AS menu_sentence,
          tm.menu_wui,
          tm.menu_info,
          tm.menu_function,
          CASE tm.menu_permission_id IS NOT NULL WHEN true THEN array_append((SELECT array_agg(tp.permission_alias) FROM tb_menu tm2 JOIN tb_permissions tp ON tm2.menu_permission_id=tp.permission_id WHERE tm2.menu_parent=tm.menu_id),tp.permission_alias ) ELSE (SELECT array_agg(tp.permission_alias) FROM tb_menu tm2 JOIN tb_permissions tp ON tm2.menu_permission_id=tp.permission_id WHERE tm2.menu_parent=tm.menu_id) END as menu_permissions,
          CASE tm.menu_parent IS NOT NULL WHEN true THEN (SELECT menu_alias FROM tb_menu tm2 WHERE tm2.menu_id=tm.menu_parent) ELSE null END AS menu_parent
  FROM tb_menu tm
  LEFT JOIN tb_permissions tp ON tm.menu_permission_id=tp.permission_id
  JOIN tb_sentences ts ON tm.menu_sentence_id=ts.sentence_id
  ORDER BY tm.menu_parent DESC, tm.menu_order ASC, tm.menu_alias
);
GRANT SELECT ON v_menu TO :var_user;

DROP VIEW IF EXISTS v_panel;
CREATE VIEW v_panel AS (
  WITH languages AS (
    SELECT json_build_object('pic',l1.language_code, 'alias', l1.language_codeset, 'name', l1.language_name->CONCAT(language_codeset))::text AS language_name
    FROM tb_languages l1 
    ORDER BY l1.language_name->CONCAT(language_codeset) ASC
  )
  SELECT  tpa.panel_alias,
          tpa.panel_floor,
          (SELECT sentence_value::json FROM tb_sentences ts WHERE ts.sentence_id=tpa.panel_sentence_id) AS panel_sentence,
          tpa.panel_icon,
          tp.permission_alias AS panel_permissions
  FROM tb_panel tpa
  JOIN tb_permissions tp ON tpa.panel_permission_id=tp.permission_id
  ORDER BY tpa.panel_order ASC
);
GRANT SELECT ON v_panel TO :var_user;

DROP VIEW IF EXISTS v_emails;
CREATE VIEW v_emails AS (
  SELECT  *,
          (CASE te.email_language WHEN null THEN null ELSE (SELECT language_codeset FROM tb_languages tl WHERE tl.language_id=te.email_language) END) as language,
          (SELECT array_agg(CASE tet.email_to_name = '' WHEN true THEN tet.email_to_email ELSE tet.email_to_name||' <'||tet.email_to_email||'>' END) FROM tb_email_to tet WHERE tet.email_to_email_id=te.email_id) AS email_to,
          (SELECT array_agg(CASE teb.email_bcc_name = '' WHEN true THEN teb.email_bcc_email ELSE teb.email_bcc_name||' <'||teb.email_bcc_email||'>' END) FROM tb_email_bcc teb WHERE teb.email_bcc_email_id=te.email_id) AS email_bcc
  FROM tb_emails te
  WHERE te.email_sent_time IS NULL ORDER BY te.email_id ASC
);
GRANT SELECT ON v_emails TO :var_user;

DROP VIEW IF EXISTS v_posts;
CREATE VIEW v_posts AS (
  SELECT  *,
          (SELECT array_to_json(array_agg(json_build_object('id',tm.media_id,'cover',tm.media_cover,'small',tm.media_small,'medium',tm.media_medium,'original',tm.media_info,'tags',(SELECT array_agg(to_json(ts.sentence_value)) FROM tb_relation_media_tag trmt JOIN tb_tags tt ON tt.tag_id=trmt.rmt_tag_id JOIN tb_sentences ts ON tt.tag_sentence_id=ts.sentence_id WHERE trmt.rmt_media_id=trpm.rpm_media_id)))) FROM tb_relation_post_media trpm JOIN tb_media tm ON trpm.rpm_media_id=tm.media_id WHERE trpm.rpm_post_id=tp.post_id) as media_files
  FROM tb_posts tp
);
GRANT SELECT ON v_posts TO :var_user;

DROP VIEW IF EXISTS v_tasks;
CREATE VIEW v_tasks AS (
  WITH A AS (
    SELECT * FROM tb_tasks tt
  ), B AS (
    SELECT task_status_id,task_status_alias,sentence_value FROM tb_task_status tts JOIN tb_sentences ts ON tts.task_status_sentence=ts.sentence_id
  )
  SELECT *,
         (SELECT json_build_object('alias',task_status_alias,'sentence',sentence_value) FROM tb_relation_task_status trts JOIN B b ON b.task_status_id=trts.trts_task_status_id WHERE trts.trts_task_id=tt.task_id ORDER BY trts_time DESC LIMIT 1) as task_status
  FROM A tt
);
GRANT SELECT ON v_tasks TO :var_user;

--# STORED PROCEDURES
CREATE OR REPLACE FUNCTION insert_sentences(p_param1 tb_sentences.sentence_alias%TYPE, p_param2 tb_sentences.sentence_value%TYPE) RETURNS tb_sentences.sentence_id%TYPE AS $$
 DECLARE
  v_id tb_sentences.sentence_id%TYPE;
  BEGIN

  SELECT sentence_id
    INTO v_id 
    FROM tb_sentences ts 
    WHERE ts.sentence_alias=p_param1;
  
    IF v_id IS NULL THEN
      INSERT INTO tb_sentences ( sentence_alias, sentence_value ) VALUES ( p_param1, p_param2 )
        RETURNING sentence_id INTO v_id;
    END IF;
    RETURN v_id;
    
  END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION insert_sentences_page(p_param1 tb_sentences.sentence_alias%TYPE, p_param2 tb_sentences_page.sentence_page_alias%TYPE, p_param3 tb_sentences.sentence_value%TYPE) RETURNS tb_sentences_page.sentence_page_alias%TYPE AS $$
 DECLARE
  v_id tb_sentences_page.sentence_page_alias%TYPE;
  BEGIN

  SELECT sentence_page_alias
    INTO v_id
    FROM tb_sentences_page 
    WHERE sentence_page_alias = p_param2 AND sentence_page_sentence_id=(SELECT sentence_id FROM tb_sentences WHERE sentence_alias=p_param1);

    IF v_id IS NULL THEN
      WITH A AS (
        SELECT p_param2 AS tmp_page_alias
      ), B AS (
        SELECT p_param1 AS tmp_sentence_alias,
        p_param3 AS sentence_content
        FROM A
      ), C AS (
        SELECT sentence_id FROM tb_sentences ts WHERE ts.sentence_alias=(SELECT tmp_sentence_alias FROM B)
      ), D AS (
        INSERT INTO tb_sentences ( sentence_alias, sentence_value ) SELECT tmp_sentence_alias AS alias, sentence_content FROM B WHERE NOT EXISTS (SELECT * FROM C)
        RETURNING sentence_id
      ), E AS (
        SELECT sentence_id FROM D
        UNION  ALL
        SELECT sentence_id FROM C
      )
      INSERT INTO tb_sentences_page ( sentence_page_alias, sentence_page_sentence_id )
        SELECT tmp_page_alias, sentence_id FROM A,E
        RETURNING sentence_page_alias INTO v_id;
    END IF;
    
    RETURN v_id;
    
  END;
$$ LANGUAGE plpgsql;

DROP FUNCTION IF EXISTS insert_group( p_alias tb_groups.group_alias%TYPE, p_sentence tb_sentences.sentence_value%TYPE, p_group_power tb_groups.group_power%TYPE );
CREATE OR REPLACE FUNCTION insert_group( p_alias tb_groups.group_alias%TYPE, p_sentence tb_sentences.sentence_value%TYPE, p_group_power tb_groups.group_power%TYPE ) RETURNS tb_groups.group_id%TYPE AS $$
DECLARE
  r_id tb_groups.group_id%TYPE;
  desc_id tb_sentences.sentence_id%TYPE;
BEGIN
  INSERT INTO tb_sentences ( sentence_alias, sentence_value ) VALUES ( CONCAT('GROUP_',p_alias), p_sentence )
    RETURNING sentence_id INTO desc_id;
  INSERT INTO tb_groups ( group_alias, group_description, group_power ) VALUES ( p_alias, desc_id, p_group_power )
    RETURNING group_id INTO r_id;
  RETURN r_id;
END;
$$ language plpgsql;

DROP FUNCTION IF EXISTS add_permission_to_group( p_group text, p_permission text, p_allow boolean, p_internal boolean, p_trusted boolean, p_external boolean );
CREATE OR REPLACE FUNCTION add_permission_to_group( p_group text, p_permission text, p_allow boolean, p_internal boolean, p_trusted boolean, p_external boolean ) RETURNS boolean AS $$
BEGIN
  INSERT INTO tb_relation_group_permission ( relation_group_permission_group, relation_group_permission_permission, relation_group_permission_allowed, relation_group_permission_accept_internal, relation_group_permission_accept_trusted, relation_group_permission_accept_external )
    VALUES ( (SELECT group_id FROM tb_groups WHERE group_alias=p_group), (SELECT permission_id FROM tb_permissions WHERE permission_alias=p_permission), p_allow, p_internal, p_trusted, p_external );
  RETURN true;
END;
$$ language plpgsql;

DROP FUNCTION IF EXISTS add_permission_to_group( p_group text, p_permission text[], p_allow boolean, p_internal boolean, p_trusted boolean, p_external boolean );
CREATE OR REPLACE FUNCTION add_permission_to_group( p_group text, p_permission text[], p_allow boolean, p_internal boolean, p_trusted boolean, p_external boolean ) RETURNS boolean AS $$
BEGIN
  INSERT INTO tb_relation_group_permission ( relation_group_permission_group, relation_group_permission_permission, relation_group_permission_allowed, relation_group_permission_accept_internal, relation_group_permission_accept_trusted, relation_group_permission_accept_external  )
    SELECT group_id, permission_id, p_allow, p_internal, p_trusted, p_external FROM tb_permissions,tb_groups WHERE group_alias=p_group AND permission_alias=ANY(p_permission);
  RETURN true;
END;
$$ language plpgsql;

DROP FUNCTION IF EXISTS add_permission_to_group( p_group text[], p_permission text[], p_allow boolean, p_internal boolean, p_trusted boolean, p_external boolean );
CREATE OR REPLACE FUNCTION add_permission_to_group( p_group text[], p_permission text[], p_allow boolean, p_internal boolean, p_trusted boolean, p_external boolean ) RETURNS boolean AS $$
BEGIN
  INSERT INTO tb_relation_group_permission ( relation_group_permission_group, relation_group_permission_permission, relation_group_permission_allowed, relation_group_permission_accept_internal, relation_group_permission_accept_trusted, relation_group_permission_accept_external  )
    SELECT group_id, permission_id, p_allow, p_internal, p_trusted, p_external FROM tb_permissions,tb_groups WHERE group_alias=ANY(p_group) AND permission_alias=ANY(p_permission);
  RETURN true;
END;
$$ language plpgsql;

DROP FUNCTION IF EXISTS add_permission_to_group( p_group text[], p_permission text, p_allow boolean, p_internal boolean, p_trusted boolean, p_external boolean );
CREATE OR REPLACE FUNCTION add_permission_to_group( p_group text[], p_permission text, p_allow boolean, p_internal boolean, p_trusted boolean, p_external boolean ) RETURNS boolean AS $$
BEGIN
  INSERT INTO tb_relation_group_permission ( relation_group_permission_group, relation_group_permission_permission, relation_group_permission_allowed, relation_group_permission_accept_internal, relation_group_permission_accept_trusted, relation_group_permission_accept_external  )
    SELECT group_id, permission_id, p_allow, p_internal, p_trusted, p_external FROM tb_permissions,tb_groups WHERE group_alias=ANY(p_group) AND permission_alias=p_permission;
  RETURN true;
END;
$$ language plpgsql;


DROP FUNCTION IF EXISTS insert_permission( p_alias tb_permissions.permission_alias%TYPE, p_sentence tb_sentences.sentence_value%TYPE );
CREATE OR REPLACE FUNCTION insert_permission( p_alias tb_permissions.permission_alias%TYPE, p_sentence tb_sentences.sentence_value%TYPE ) RETURNS tb_permissions.permission_id%TYPE AS $$
DECLARE
  r_id tb_permissions.permission_id%TYPE;
  desc_id tb_sentences.sentence_id%TYPE;
BEGIN
  INSERT INTO tb_sentences ( sentence_alias, sentence_value ) VALUES ( CONCAT('PERMISSION_',p_alias), p_sentence )
    RETURNING sentence_id INTO desc_id;
  INSERT INTO tb_permissions ( permission_alias, permission_sentence_id ) VALUES ( p_alias, desc_id )
    RETURNING permission_id INTO r_id;
  RETURN r_id;
END;
$$ language plpgsql;

CREATE OR REPLACE FUNCTION insert_block( p_param1 tb_blocks.block_ipaddress%TYPE ) RETURNS void AS $$
BEGIN
   INSERT INTO tb_blocks ( block_ipaddress, block_time ) VALUES
    ( p_param1, (SELECT CURRENT_TIMESTAMP + random()*10*(INTERVAL '1 hour')));
END;
$$ language plpgsql;

--DROP FUNCTION IF EXISTS insert_menu( p_alias tb_permissions.permission_alias%TYPE, p_sentence tb_sentences.sentence_value%TYPE, m_alias tb_menu.menu_alias%TYPE, m_sentence tb_sentences.sentence_value%TYPE );
--CREATE OR REPLACE FUNCTION insert_menu( p_alias tb_permissions.permission_alias%TYPE, p_sentence tb_sentences.sentence_value%TYPE, m_alias tb_menu.menu_alias%TYPE, m_sentence tb_sentences.sentence_value%TYPE ) RETURNS tb_menu.menu_id%TYPE AS $$
--DECLARE
--  m_id tb_menu.menu_id%TYPE;
--  r_id tb_permissions.permission_id%TYPE;
--  desc_id tb_sentences.sentence_id%TYPE;
--BEGIN
--  r_id SELECT insert_permission( p_alias, p_sentence );
--  IF r_id IS NULL THEN return null;
--  END IF
--END;
--$$ language plpgsql;

DROP FUNCTION IF EXISTS insert_tag(p_param1 tb_tags.tag_alias%TYPE, p_param2 tb_tags.tag_aka%TYPE, p_param3 tb_sentences.sentence_value%TYPE);
CREATE OR REPLACE FUNCTION insert_tag(p_param1 tb_tags.tag_alias%TYPE, p_param2 tb_tags.tag_aka%TYPE, p_param3 tb_sentences.sentence_value%TYPE) RETURNS tb_tags.tag_id%TYPE AS $$
 DECLARE
  v_id tb_tags.tag_id%TYPE;
  s_id tb_sentences.sentence_id%TYPE;
  BEGIN
    SELECT sentence_id INTO s_id FROM tb_sentences WHERE sentence_alias=CONCAT('TAG_',p_param1);
    IF s_id IS NULL THEN
      INSERT INTO tb_sentences ( sentence_alias, sentence_value )
        SELECT CONCAT('TAG_',p_param1) AS alias, p_param3 
        RETURNING sentence_id INTO s_id;
    END IF;
    SELECT tag_id INTO v_id FROM tb_tags WHERE tag_alias=p_param1;
    IF v_id IS NULL THEN
      INSERT INTO tb_tags ( tag_alias, tag_aka, tag_sentence_id ) 
        SELECT p_param1, p_param2, s_id RETURNING tag_id INTO v_id;
    ELSE
      UPDATE tb_tags SET tag_aka=(SELECT tag_aka || p_param2 FROM tb_tags WHERE tag_id=v_id) WHERE tag_id=v_id;
    END IF;
    RETURN v_id;
  END;
$$ LANGUAGE plpgsql;

DROP FUNCTION IF EXISTS insert_task_status(p_param1 tb_task_status.task_status_alias%TYPE, p_param2 tb_sentences.sentence_value%TYPE);
CREATE OR REPLACE FUNCTION insert_task_status(p_param1 tb_task_status.task_status_alias%TYPE, p_param2 tb_sentences.sentence_value%TYPE) RETURNS tb_task_status.task_status_id%TYPE AS $$
 DECLARE
  v_id tb_task_status.task_status_id%TYPE;
  BEGIN
    WITH A AS (
      SELECT  p_param1 AS alias,
              p_param2 AS status_sentence
    ), B AS (
      INSERT INTO tb_sentences ( sentence_alias, sentence_value )
        SELECT 'TASK_STATUS_'||alias, status_sentence FROM A
        RETURNING sentence_id
    )
    INSERT INTO tb_task_status ( task_status_alias, task_status_sentence )
      SELECT alias, sentence_id FROM A,B RETURNING task_status_id INTO v_id;
    RETURN v_id;
  END;
$$ LANGUAGE plpgsql;

DROP FUNCTION IF EXISTS insert_task(p_param1 tb_tasks.task_parameters%TYPE, p_param2 text, p_param3 bigint );
CREATE OR REPLACE FUNCTION insert_task(p_param1 tb_tasks.task_parameters%TYPE, p_param2 text, p_param3 bigint ) RETURNS tb_tasks.task_id%TYPE AS $$
 DECLARE
  v_id tb_tasks.task_id%TYPE;
  v_alias text;
  BEGIN
    EXECUTE 'INSERT INTO tb_tasks ( task_parameters, '|| p_param2 ||' ) VALUES ( '''|| p_param1 ||''', '||p_param3||' ) RETURNING task_id' INTO v_id;
    INSERT INTO tb_relation_task_status ( trts_task_id, trts_task_status_id ) VALUES ( v_id, (SELECT task_status_id FROM tb_task_status WHERE task_status_alias='QUEUED') );
    RETURN v_id;
  END;
$$ LANGUAGE plpgsql;

DROP FUNCTION IF EXISTS insert_payment_status(p_param1 tb_payment_status.payment_status_alias%TYPE, p_param2 tb_sentences.sentence_value%TYPE);
CREATE OR REPLACE FUNCTION insert_payment_status(p_param1 tb_payment_status.payment_status_alias%TYPE, p_param2 tb_sentences.sentence_value%TYPE) RETURNS tb_payment_status.payment_status_id%TYPE AS $$
 DECLARE
  v_id tb_payment_status.payment_status_id%TYPE;
  BEGIN
    WITH A AS (
      SELECT  p_param1 AS alias,
              p_param2 AS status_sentence
    ), B AS (
      INSERT INTO tb_sentences ( sentence_alias, sentence_value )
        SELECT CONCAT('TASK_STATUS_',alias) AS alias, status_sentence FROM A
        RETURNING sentence_id
    )
    INSERT INTO tb_payment_status ( payment_status_alias, payment_status_sentence )
      SELECT alias, sentence_id FROM A,B RETURNING payment_status_id INTO v_id;
    RETURN v_id;
  END;
$$ LANGUAGE plpgsql;

