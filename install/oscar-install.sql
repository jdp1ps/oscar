--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET lock_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

SET search_path = public, pg_catalog;

ALTER TABLE ONLY public.workpackageperson DROP CONSTRAINT fk_e9b876779485a167;
ALTER TABLE ONLY public.workpackageperson DROP CONSTRAINT fk_e9b8767765ff1aec;
ALTER TABLE ONLY public.workpackageperson DROP CONSTRAINT fk_e9b8767763d8c20e;
ALTER TABLE ONLY public.workpackageperson DROP CONSTRAINT fk_e9b876773174800f;
ALTER TABLE ONLY public.workpackageperson DROP CONSTRAINT fk_e9b87677217bbb47;
ALTER TABLE ONLY public.project DROP CONSTRAINT fk_e00ee972a5522701;
ALTER TABLE ONLY public.projectpartner DROP CONSTRAINT fk_dd65739b65ff1aec;
ALTER TABLE ONLY public.projectpartner DROP CONSTRAINT fk_dd65739b63d8c20e;
ALTER TABLE ONLY public.projectpartner DROP CONSTRAINT fk_dd65739b32c8a3de;
ALTER TABLE ONLY public.projectpartner DROP CONSTRAINT fk_dd65739b3174800f;
ALTER TABLE ONLY public.projectpartner DROP CONSTRAINT fk_dd65739b1c4132c1;
ALTER TABLE ONLY public.projectpartner DROP CONSTRAINT fk_dd65739b166d1f9c;
ALTER TABLE ONLY public.organization DROP CONSTRAINT fk_d9dfb88465ff1aec;
ALTER TABLE ONLY public.organization DROP CONSTRAINT fk_d9dfb88463d8c20e;
ALTER TABLE ONLY public.organization DROP CONSTRAINT fk_d9dfb8843174800f;
ALTER TABLE ONLY public.role_privilege DROP CONSTRAINT fk_d6d4495bd60322ac;
ALTER TABLE ONLY public.role_privilege DROP CONSTRAINT fk_d6d4495b32fb8aea;
ALTER TABLE ONLY public.workpackage DROP CONSTRAINT fk_c583f07f81c06096;
ALTER TABLE ONLY public.workpackage DROP CONSTRAINT fk_c583f07f65ff1aec;
ALTER TABLE ONLY public.workpackage DROP CONSTRAINT fk_c583f07f63d8c20e;
ALTER TABLE ONLY public.workpackage DROP CONSTRAINT fk_c583f07f3174800f;
ALTER TABLE ONLY public.administrativedocument DROP CONSTRAINT fk_c311ba72217bbb47;
ALTER TABLE ONLY public.activitytype DROP CONSTRAINT fk_b8fa49765ff1aec;
ALTER TABLE ONLY public.activitytype DROP CONSTRAINT fk_b8fa49763d8c20e;
ALTER TABLE ONLY public.activitytype DROP CONSTRAINT fk_b8fa4973174800f;
ALTER TABLE ONLY public.organizationrole DROP CONSTRAINT fk_a782183065ff1aec;
ALTER TABLE ONLY public.organizationrole DROP CONSTRAINT fk_a782183063d8c20e;
ALTER TABLE ONLY public.organizationrole DROP CONSTRAINT fk_a78218303174800f;
ALTER TABLE ONLY public.activityorganization DROP CONSTRAINT fk_9310307d81c06096;
ALTER TABLE ONLY public.activityorganization DROP CONSTRAINT fk_9310307d65ff1aec;
ALTER TABLE ONLY public.activityorganization DROP CONSTRAINT fk_9310307d63d8c20e;
ALTER TABLE ONLY public.activityorganization DROP CONSTRAINT fk_9310307d32c8a3de;
ALTER TABLE ONLY public.activityorganization DROP CONSTRAINT fk_9310307d3174800f;
ALTER TABLE ONLY public.activityorganization DROP CONSTRAINT fk_9310307d1c4132c1;
ALTER TABLE ONLY public.currency DROP CONSTRAINT fk_9020ea6965ff1aec;
ALTER TABLE ONLY public.currency DROP CONSTRAINT fk_9020ea6963d8c20e;
ALTER TABLE ONLY public.currency DROP CONSTRAINT fk_9020ea693174800f;
ALTER TABLE ONLY public.privilege DROP CONSTRAINT fk_87209a87bcf5e72d;
ALTER TABLE ONLY public.privilege DROP CONSTRAINT fk_87209a8779066886;
ALTER TABLE ONLY public.activitypayment DROP CONSTRAINT fk_8115848c81c06096;
ALTER TABLE ONLY public.activitypayment DROP CONSTRAINT fk_8115848c65ff1aec;
ALTER TABLE ONLY public.activitypayment DROP CONSTRAINT fk_8115848c63d8c20e;
ALTER TABLE ONLY public.activitypayment DROP CONSTRAINT fk_8115848c38248176;
ALTER TABLE ONLY public.activitypayment DROP CONSTRAINT fk_8115848c3174800f;
ALTER TABLE ONLY public.tva DROP CONSTRAINT fk_79ced4aa65ff1aec;
ALTER TABLE ONLY public.tva DROP CONSTRAINT fk_79ced4aa63d8c20e;
ALTER TABLE ONLY public.tva DROP CONSTRAINT fk_79ced4aa3174800f;
ALTER TABLE ONLY public.project_discipline DROP CONSTRAINT fk_6d18950da5522701;
ALTER TABLE ONLY public.project_discipline DROP CONSTRAINT fk_6d18950d166d1f9c;
ALTER TABLE ONLY public.organizationperson DROP CONSTRAINT fk_6a89662b65ff1aec;
ALTER TABLE ONLY public.organizationperson DROP CONSTRAINT fk_6a89662b63d8c20e;
ALTER TABLE ONLY public.organizationperson DROP CONSTRAINT fk_6a89662b32c8a3de;
ALTER TABLE ONLY public.organizationperson DROP CONSTRAINT fk_6a89662b3174800f;
ALTER TABLE ONLY public.organizationperson DROP CONSTRAINT fk_6a89662b217bbb47;
ALTER TABLE ONLY public.organizationperson DROP CONSTRAINT fk_6a89662b1c4132c1;
ALTER TABLE ONLY public.activityperson DROP CONSTRAINT fk_6a2e76b781c06096;
ALTER TABLE ONLY public.activityperson DROP CONSTRAINT fk_6a2e76b765ff1aec;
ALTER TABLE ONLY public.activityperson DROP CONSTRAINT fk_6a2e76b763d8c20e;
ALTER TABLE ONLY public.activityperson DROP CONSTRAINT fk_6a2e76b73174800f;
ALTER TABLE ONLY public.activityperson DROP CONSTRAINT fk_6a2e76b7217bbb47;
ALTER TABLE ONLY public.activityperson DROP CONSTRAINT fk_6a2e76b71c4132c1;
ALTER TABLE ONLY public.typedocument DROP CONSTRAINT fk_6547bd5065ff1aec;
ALTER TABLE ONLY public.typedocument DROP CONSTRAINT fk_6547bd5063d8c20e;
ALTER TABLE ONLY public.typedocument DROP CONSTRAINT fk_6547bd503174800f;
ALTER TABLE ONLY public.authentification_role DROP CONSTRAINT fk_5dbdaf5d60322ac;
ALTER TABLE ONLY public.authentification_role DROP CONSTRAINT fk_5dbdaf56d28043b;
ALTER TABLE ONLY public.projectmember DROP CONSTRAINT fk_5d5b51b965ff1aec;
ALTER TABLE ONLY public.projectmember DROP CONSTRAINT fk_5d5b51b963d8c20e;
ALTER TABLE ONLY public.projectmember DROP CONSTRAINT fk_5d5b51b93174800f;
ALTER TABLE ONLY public.projectmember DROP CONSTRAINT fk_5d5b51b9217bbb47;
ALTER TABLE ONLY public.projectmember DROP CONSTRAINT fk_5d5b51b91c4132c1;
ALTER TABLE ONLY public.projectmember DROP CONSTRAINT fk_5d5b51b9166d1f9c;
ALTER TABLE ONLY public.activity DROP CONSTRAINT fk_55026b0cc54c8c93;
ALTER TABLE ONLY public.activity DROP CONSTRAINT fk_55026b0ca1b4b28c;
ALTER TABLE ONLY public.activity DROP CONSTRAINT fk_55026b0c953c1c61;
ALTER TABLE ONLY public.activity DROP CONSTRAINT fk_55026b0c65ff1aec;
ALTER TABLE ONLY public.activity DROP CONSTRAINT fk_55026b0c63d8c20e;
ALTER TABLE ONLY public.activity DROP CONSTRAINT fk_55026b0c4d79775f;
ALTER TABLE ONLY public.activity DROP CONSTRAINT fk_55026b0c38248176;
ALTER TABLE ONLY public.activity DROP CONSTRAINT fk_55026b0c3174800f;
ALTER TABLE ONLY public.activity DROP CONSTRAINT fk_55026b0c166d1f9c;
ALTER TABLE ONLY public.contractdocument DROP CONSTRAINT fk_4a390fe85c0c89f3;
ALTER TABLE ONLY public.contractdocument DROP CONSTRAINT fk_4a390fe83bebd1bd;
ALTER TABLE ONLY public.contractdocument DROP CONSTRAINT fk_4a390fe8217bbb47;
ALTER TABLE ONLY public.timesheet DROP CONSTRAINT fk_34944573dbd8a2b7;
ALTER TABLE ONLY public.timesheet DROP CONSTRAINT fk_3494457381c06096;
ALTER TABLE ONLY public.timesheet DROP CONSTRAINT fk_3494457365ff1aec;
ALTER TABLE ONLY public.timesheet DROP CONSTRAINT fk_3494457363d8c20e;
ALTER TABLE ONLY public.timesheet DROP CONSTRAINT fk_349445733174800f;
ALTER TABLE ONLY public.timesheet DROP CONSTRAINT fk_34944573217bbb47;
ALTER TABLE ONLY public.person DROP CONSTRAINT fk_3370d44065ff1aec;
ALTER TABLE ONLY public.person DROP CONSTRAINT fk_3370d44063d8c20e;
ALTER TABLE ONLY public.person DROP CONSTRAINT fk_3370d4403174800f;
ALTER TABLE ONLY public.user_role DROP CONSTRAINT fk_2de8c6a3727aca70;
ALTER TABLE ONLY public.activitydate DROP CONSTRAINT fk_2dcfc4c4c54c8c93;
ALTER TABLE ONLY public.activitydate DROP CONSTRAINT fk_2dcfc4c481c06096;
ALTER TABLE ONLY public.activitydate DROP CONSTRAINT fk_2dcfc4c465ff1aec;
ALTER TABLE ONLY public.activitydate DROP CONSTRAINT fk_2dcfc4c463d8c20e;
ALTER TABLE ONLY public.activitydate DROP CONSTRAINT fk_2dcfc4c43174800f;
ALTER TABLE ONLY public.datetype DROP CONSTRAINT fk_29fdc4ce65ff1aec;
ALTER TABLE ONLY public.datetype DROP CONSTRAINT fk_29fdc4ce63d8c20e;
ALTER TABLE ONLY public.datetype DROP CONSTRAINT fk_29fdc4ce3174800f;
ALTER TABLE ONLY public.notificationperson DROP CONSTRAINT fk_22ba6515ef1a9d84;
ALTER TABLE ONLY public.notificationperson DROP CONSTRAINT fk_22ba6515217bbb47;
ALTER TABLE ONLY public.activity_discipline DROP CONSTRAINT fk_205cd037a5522701;
ALTER TABLE ONLY public.activity_discipline DROP CONSTRAINT fk_205cd03781c06096;
DROP TRIGGER activity_numauto ON public.activity;
DROP INDEX public.uniq_a7821830ea750e8;
DROP INDEX public.uniq_9de7cd62f85e0677;
DROP INDEX public.uniq_9de7cd62e7927c74;
DROP INDEX public.uniq_6e60b4f7d60322ac;
DROP INDEX public.uniq_598638fb8a90aba9;
DROP INDEX public.uniq_2de8c6a3d60322ac;
DROP INDEX public.uniq_2de8c6a31596728e;
DROP INDEX public.idx_e9b876779485a167;
DROP INDEX public.idx_e9b8767765ff1aec;
DROP INDEX public.idx_e9b8767763d8c20e;
DROP INDEX public.idx_e9b876773174800f;
DROP INDEX public.idx_e9b87677217bbb47;
DROP INDEX public.idx_e00ee972a5522701;
DROP INDEX public.idx_dd65739b65ff1aec;
DROP INDEX public.idx_dd65739b63d8c20e;
DROP INDEX public.idx_dd65739b32c8a3de;
DROP INDEX public.idx_dd65739b3174800f;
DROP INDEX public.idx_dd65739b1c4132c1;
DROP INDEX public.idx_dd65739b166d1f9c;
DROP INDEX public.idx_d9dfb88465ff1aec;
DROP INDEX public.idx_d9dfb88463d8c20e;
DROP INDEX public.idx_d9dfb8843174800f;
DROP INDEX public.idx_d6d4495bd60322ac;
DROP INDEX public.idx_d6d4495b32fb8aea;
DROP INDEX public.idx_c583f07f81c06096;
DROP INDEX public.idx_c583f07f65ff1aec;
DROP INDEX public.idx_c583f07f63d8c20e;
DROP INDEX public.idx_c583f07f3174800f;
DROP INDEX public.idx_c311ba72217bbb47;
DROP INDEX public.idx_b8fa49765ff1aec;
DROP INDEX public.idx_b8fa49763d8c20e;
DROP INDEX public.idx_b8fa4973174800f;
DROP INDEX public.idx_a782183065ff1aec;
DROP INDEX public.idx_a782183063d8c20e;
DROP INDEX public.idx_a78218303174800f;
DROP INDEX public.idx_9310307d81c06096;
DROP INDEX public.idx_9310307d65ff1aec;
DROP INDEX public.idx_9310307d63d8c20e;
DROP INDEX public.idx_9310307d32c8a3de;
DROP INDEX public.idx_9310307d3174800f;
DROP INDEX public.idx_9310307d1c4132c1;
DROP INDEX public.idx_9020ea6965ff1aec;
DROP INDEX public.idx_9020ea6963d8c20e;
DROP INDEX public.idx_9020ea693174800f;
DROP INDEX public.idx_87209a87bcf5e72d;
DROP INDEX public.idx_87209a8779066886;
DROP INDEX public.idx_8115848c81c06096;
DROP INDEX public.idx_8115848c65ff1aec;
DROP INDEX public.idx_8115848c63d8c20e;
DROP INDEX public.idx_8115848c38248176;
DROP INDEX public.idx_8115848c3174800f;
DROP INDEX public.idx_79ced4aa65ff1aec;
DROP INDEX public.idx_79ced4aa63d8c20e;
DROP INDEX public.idx_79ced4aa3174800f;
DROP INDEX public.idx_6d18950da5522701;
DROP INDEX public.idx_6d18950d166d1f9c;
DROP INDEX public.idx_6a89662b65ff1aec;
DROP INDEX public.idx_6a89662b63d8c20e;
DROP INDEX public.idx_6a89662b32c8a3de;
DROP INDEX public.idx_6a89662b3174800f;
DROP INDEX public.idx_6a89662b217bbb47;
DROP INDEX public.idx_6a89662b1c4132c1;
DROP INDEX public.idx_6a2e76b781c06096;
DROP INDEX public.idx_6a2e76b765ff1aec;
DROP INDEX public.idx_6a2e76b763d8c20e;
DROP INDEX public.idx_6a2e76b73174800f;
DROP INDEX public.idx_6a2e76b7217bbb47;
DROP INDEX public.idx_6a2e76b71c4132c1;
DROP INDEX public.idx_6547bd5065ff1aec;
DROP INDEX public.idx_6547bd5063d8c20e;
DROP INDEX public.idx_6547bd503174800f;
DROP INDEX public.idx_5dbdaf5d60322ac;
DROP INDEX public.idx_5dbdaf56d28043b;
DROP INDEX public.idx_5d5b51b965ff1aec;
DROP INDEX public.idx_5d5b51b963d8c20e;
DROP INDEX public.idx_5d5b51b93174800f;
DROP INDEX public.idx_5d5b51b9217bbb47;
DROP INDEX public.idx_5d5b51b91c4132c1;
DROP INDEX public.idx_5d5b51b9166d1f9c;
DROP INDEX public.idx_55026b0cc54c8c93;
DROP INDEX public.idx_55026b0ca1b4b28c;
DROP INDEX public.idx_55026b0c953c1c61;
DROP INDEX public.idx_55026b0c65ff1aec;
DROP INDEX public.idx_55026b0c63d8c20e;
DROP INDEX public.idx_55026b0c4d79775f;
DROP INDEX public.idx_55026b0c38248176;
DROP INDEX public.idx_55026b0c3174800f;
DROP INDEX public.idx_55026b0c166d1f9c;
DROP INDEX public.idx_4a390fe85c0c89f3;
DROP INDEX public.idx_4a390fe83bebd1bd;
DROP INDEX public.idx_4a390fe8217bbb47;
DROP INDEX public.idx_34944573dbd8a2b7;
DROP INDEX public.idx_3494457381c06096;
DROP INDEX public.idx_3494457365ff1aec;
DROP INDEX public.idx_3494457363d8c20e;
DROP INDEX public.idx_349445733174800f;
DROP INDEX public.idx_34944573217bbb47;
DROP INDEX public.idx_3370d44065ff1aec;
DROP INDEX public.idx_3370d44063d8c20e;
DROP INDEX public.idx_3370d4403174800f;
DROP INDEX public.idx_2de8c6a3727aca70;
DROP INDEX public.idx_2dcfc4c4c54c8c93;
DROP INDEX public.idx_2dcfc4c481c06096;
DROP INDEX public.idx_2dcfc4c465ff1aec;
DROP INDEX public.idx_2dcfc4c463d8c20e;
DROP INDEX public.idx_2dcfc4c43174800f;
DROP INDEX public.idx_29fdc4ce65ff1aec;
DROP INDEX public.idx_29fdc4ce63d8c20e;
DROP INDEX public.idx_29fdc4ce3174800f;
DROP INDEX public.idx_22ba6515ef1a9d84;
DROP INDEX public.idx_22ba6515217bbb47;
DROP INDEX public.idx_205cd037a5522701;
DROP INDEX public.idx_205cd03781c06096;
ALTER TABLE ONLY public.workpackageperson DROP CONSTRAINT workpackageperson_pkey;
ALTER TABLE ONLY public.workpackage DROP CONSTRAINT workpackage_pkey;
ALTER TABLE ONLY public.useraccessdefinition DROP CONSTRAINT useraccessdefinition_pkey;
ALTER TABLE ONLY public.user_role DROP CONSTRAINT user_role_pkey;
ALTER TABLE ONLY public.typedocument DROP CONSTRAINT typedocument_pkey;
ALTER TABLE ONLY public.tva DROP CONSTRAINT tva_pkey;
ALTER TABLE ONLY public.timesheet DROP CONSTRAINT timesheet_pkey;
ALTER TABLE ONLY public.role_privilege DROP CONSTRAINT role_privilege_pkey;
ALTER TABLE ONLY public.projectpartner DROP CONSTRAINT projectpartner_pkey;
ALTER TABLE ONLY public.projectmember DROP CONSTRAINT projectmember_pkey;
ALTER TABLE ONLY public.project DROP CONSTRAINT project_pkey;
ALTER TABLE ONLY public.project_discipline DROP CONSTRAINT project_discipline_pkey;
ALTER TABLE ONLY public.privilege DROP CONSTRAINT privilege_pkey;
ALTER TABLE ONLY public.person DROP CONSTRAINT person_pkey;
ALTER TABLE ONLY public.organizationrole DROP CONSTRAINT organizationrole_pkey;
ALTER TABLE ONLY public.organizationperson DROP CONSTRAINT organizationperson_pkey;
ALTER TABLE ONLY public.organization_role DROP CONSTRAINT organization_role_pkey;
ALTER TABLE ONLY public.organization DROP CONSTRAINT organization_pkey;
ALTER TABLE ONLY public.notificationperson DROP CONSTRAINT notificationperson_pkey;
ALTER TABLE ONLY public.notification DROP CONSTRAINT notification_pkey;
ALTER TABLE ONLY public.logactivity DROP CONSTRAINT logactivity_pkey;
ALTER TABLE ONLY public.grantsource DROP CONSTRAINT grantsource_pkey;
ALTER TABLE ONLY public.discipline DROP CONSTRAINT discipline_pkey;
ALTER TABLE ONLY public.datetype DROP CONSTRAINT datetype_pkey;
ALTER TABLE ONLY public.currency DROP CONSTRAINT currency_pkey;
ALTER TABLE ONLY public.contracttype DROP CONSTRAINT contracttype_pkey;
ALTER TABLE ONLY public.contractdocument DROP CONSTRAINT contractdocument_pkey;
ALTER TABLE ONLY public.categorie_privilege DROP CONSTRAINT categorie_privilege_pkey;
ALTER TABLE ONLY public.authentification_role DROP CONSTRAINT authentification_role_pkey;
ALTER TABLE ONLY public.authentification DROP CONSTRAINT authentification_pkey;
ALTER TABLE ONLY public.administrativedocument DROP CONSTRAINT administrativedocument_pkey;
ALTER TABLE ONLY public.activitytype DROP CONSTRAINT activitytype_pkey;
ALTER TABLE ONLY public.activityperson DROP CONSTRAINT activityperson_pkey;
ALTER TABLE ONLY public.activitypayment DROP CONSTRAINT activitypayment_pkey;
ALTER TABLE ONLY public.activityorganization DROP CONSTRAINT activityorganization_pkey;
ALTER TABLE ONLY public.activitydate DROP CONSTRAINT activitydate_pkey;
ALTER TABLE ONLY public.activity DROP CONSTRAINT activity_pkey;
ALTER TABLE ONLY public.activity_discipline DROP CONSTRAINT activity_discipline_pkey;
DROP SEQUENCE public.workpackageperson_id_seq;
DROP TABLE public.workpackageperson;
DROP SEQUENCE public.workpackage_id_seq;
DROP TABLE public.workpackage;
DROP SEQUENCE public.useraccessdefinition_id_seq;
DROP TABLE public.useraccessdefinition;
DROP SEQUENCE public.user_role_id_seq;
DROP TABLE public.user_role;
DROP SEQUENCE public.typedocument_id_seq;
DROP TABLE public.typedocument;
DROP SEQUENCE public.tva_id_seq;
DROP TABLE public.tva;
DROP SEQUENCE public.timesheet_id_seq;
DROP TABLE public.timesheet;
DROP TABLE public.role_privilege;
DROP SEQUENCE public.role_id_seq;
DROP SEQUENCE public.projectpartner_id_seq;
DROP TABLE public.projectpartner;
DROP SEQUENCE public.projectmember_id_seq;
DROP TABLE public.projectmember;
DROP SEQUENCE public.projectgrant_id_seq;
DROP SEQUENCE public.project_id_seq;
DROP TABLE public.project_discipline;
DROP TABLE public.project;
DROP SEQUENCE public.privilege_id_seq;
DROP TABLE public.privilege;
DROP SEQUENCE public.person_id_seq;
DROP TABLE public.person;
DROP SEQUENCE public.organizationrole_id_seq;
DROP TABLE public.organizationrole;
DROP SEQUENCE public.organizationperson_id_seq;
DROP TABLE public.organizationperson;
DROP SEQUENCE public.organization_role_id_seq;
DROP TABLE public.organization_role;
DROP SEQUENCE public.organization_id_seq;
DROP TABLE public.organization;
DROP SEQUENCE public.notificationperson_id_seq;
DROP TABLE public.notificationperson;
DROP SEQUENCE public.notification_id_seq;
DROP TABLE public.notification;
DROP SEQUENCE public.logactivity_id_seq;
DROP TABLE public.logactivity;
DROP SEQUENCE public.grantsource_id_seq;
DROP TABLE public.grantsource;
DROP SEQUENCE public.discipline_id_seq;
DROP TABLE public.discipline;
DROP SEQUENCE public.datetype_id_seq;
DROP TABLE public.datetype;
DROP SEQUENCE public.currency_id_seq;
DROP TABLE public.currency;
DROP SEQUENCE public.contracttype_id_seq;
DROP TABLE public.contracttype;
DROP SEQUENCE public.contractdocument_id_seq;
DROP TABLE public.contractdocument;
DROP SEQUENCE public.categorie_privilege_id_seq;
DROP TABLE public.categorie_privilege;
DROP TABLE public.authentification_role;
DROP SEQUENCE public.authentification_id_seq;
DROP TABLE public.authentification;
DROP SEQUENCE public.administrativedocument_id_seq;
DROP TABLE public.administrativedocument;
DROP SEQUENCE public.activitytype_id_seq;
DROP TABLE public.activitytype;
DROP SEQUENCE public.activityperson_id_seq;
DROP TABLE public.activityperson;
DROP SEQUENCE public.activitypayment_id_seq;
DROP TABLE public.activitypayment;
DROP SEQUENCE public.activityorganization_id_seq;
DROP TABLE public.activityorganization;
DROP SEQUENCE public.activitydate_id_seq;
DROP TABLE public.activitydate;
DROP SEQUENCE public.activity_id_seq;
DROP TABLE public.activity_discipline;
DROP TABLE public.activity;
DROP FUNCTION public.test();
DROP FUNCTION public.oscar_activity_numauto();
DROP FUNCTION public.activity_num_auto(activity_id integer);
DROP FUNCTION public."ProjectRemoveClone"();
DROP EXTENSION plpgsql;
DROP SCHEMA public;
--
-- Name: public; Type: SCHEMA; Schema: -; Owner: -
--

CREATE SCHEMA public;


--
-- Name: SCHEMA public; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON SCHEMA public IS 'standard public schema';


--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


SET search_path = public, pg_catalog;

--
-- Name: ProjectRemoveClone(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION "ProjectRemoveClone"() RETURNS void
    LANGUAGE plpgsql
    AS $$DECLARE
  -- TOTO
BEGIN
	RAISE NOTICE 'Appel de ProjectRemoveClone()';

	-- On récupère les projet en double
	SELECT eotp 
	FROM project 
	GROUP BY eotp 
	HAVING count(*) > 1;

	RETURN;
END
$$;


--
-- Name: activity_num_auto(integer); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION activity_num_auto(activity_id integer) RETURNS text
    LANGUAGE plpgsql
    AS $$
DECLARE
	activity_record activity;
	year int;
	last_num text;
	num text;
	separator text := 'DRI';
	counter_val int;
BEGIN
	------------------------------------------------------------------------------------
	-- On récupère l'activité qui va bien
	SELECT * INTO activity_record FROM activity WHERE id = activity_id;

	-- Err : Pas d'activité
	IF activity_record IS NULL THEN
		RAISE EXCEPTION 'Activité % non trouve', activity_id;
	END IF;

	-- Err : Activité déjà numérotée
	IF activity_record.oscarnum IS NOT NULL THEN
		RAISE EXCEPTION 'Cette activité (%) est déjà numérotée', activity_id;
	END IF;
	-------------------------------------------------------------------------------------

	-------------------------------------------------------------------------------------
	-- Récupération du plus grand numéro précédent : 

	-- On récupère l'année de l'activité (Si elle est null, on utilise l'année courante)
	year := EXTRACT(YEAR FROM activity_record.dateSigned);
	IF year IS NULL THEN
		year = EXTRACT(YEAR FROM activity_record.dateCreated);
	END IF;
	IF year IS NULL THEN
		year = EXTRACT(YEAR FROM CURRENT_TIMESTAMP);
	END IF;

	-- On récupère le dernier numéro pour cette année
	SELECT MAX(oscarNum) INTO last_num FROM activity WHERE oscarnum LIKE year || 'DRI%';
	IF last_num IS NULL THEN
		counter_val := 0;
	ELSE
		counter_val := substring(last_num FROM 8 FOR 5)::int;
	END IF;

	counter_val := counter_val + 1;
	
	
	num := CONCAT(year, 'DRI', to_char(counter_val, 'fm00000'));

	UPDATE activity SET oscarNum = num WHERE id = activity_id;
	
	RETURN num;
END;
$$;


--
-- Name: oscar_activity_numauto(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION oscar_activity_numauto() RETURNS trigger
    LANGUAGE plpgsql
    AS $$DECLARE
	-- Résultat de la numérotation
	result text;
BEGIN
	IF (TG_OP = 'INSERT') THEN
		SELECT * INTO result FROM activity_num_auto(NEW.id);
		RETURN NEW;
	END IF;
	-- Autre, osef
	RETURN NULL;
END$$;


--
-- Name: test(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION test() RETURNS integer
    LANGUAGE plpgsql
    AS $$
DECLARE
	-- stuff
	eotps RECORD; -- EOTP des projets en double
	r project%rowtype;
BEGIN
	-- Liste des EOTP des projets en double
	
	RAISE NOTICE 'Execution de test()';
	SELECT eotp INTO eotps FROM PROJECT GROUP BY eotp HAVING COUNT(eotp) > 1;

	FOR r IN SELECT * FROM project
	LOOP
		RAISE NOTICE 'r.id';
	END LOOP;
	
	RETURN 1;
END;
$$;


SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: activity; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE activity (
    id integer NOT NULL,
    source_id integer,
    project_id integer,
    type_id integer,
    centaureid character varying(10) DEFAULT NULL::character varying,
    centaurenumconvention character varying(64) DEFAULT NULL::character varying,
    codeeotp character varying(64) DEFAULT NULL::character varying,
    label character varying(255) DEFAULT NULL::character varying,
    description text,
    hassheet boolean,
    duration integer,
    justifyworkingtime integer,
    justifycost double precision,
    amount double precision,
    datestart date,
    dateend date,
    datesigned date,
    dateopened date,
    status integer,
    datecreated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    dateupdated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    datedeleted timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    createdby_id integer,
    updatedby_id integer,
    deletedby_id integer,
    activitytype_id integer,
    currency_id integer,
    tva_id integer,
    oscarid character varying(255) DEFAULT NULL::character varying,
    oscarnum character varying(12) DEFAULT NULL::character varying,
    timesheetformat character varying(255) DEFAULT 'none'::character varying NOT NULL,
    numbers text,
    financialimpact character varying(32) DEFAULT 'Recette'::character varying NOT NULL
);


--
-- Name: COLUMN activity.numbers; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN activity.numbers IS '(DC2Type:object)';


--
-- Name: activity_discipline; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE activity_discipline (
    activity_id integer NOT NULL,
    discipline_id integer NOT NULL
);


--
-- Name: activity_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE activity_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: activitydate; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE activitydate (
    id integer NOT NULL,
    type_id integer,
    activity_id integer,
    datestart date NOT NULL,
    comment text,
    status integer,
    datecreated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    dateupdated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    datedeleted timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    createdby_id integer,
    updatedby_id integer,
    deletedby_id integer
);


--
-- Name: activitydate_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE activitydate_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: activityorganization; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE activityorganization (
    id integer NOT NULL,
    organization_id integer,
    activity_id integer,
    main boolean,
    role character varying(255) DEFAULT NULL::character varying,
    status integer,
    datecreated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    dateupdated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    datedeleted timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    createdby_id integer,
    updatedby_id integer,
    deletedby_id integer,
    datestart timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    dateend timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    roleobj_id integer
);


--
-- Name: activityorganization_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE activityorganization_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: activitypayment; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE activitypayment (
    id integer NOT NULL,
    activity_id integer,
    currency_id integer,
    datepayment date,
    comment text,
    status integer,
    datecreated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    dateupdated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    datedeleted timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    createdby_id integer,
    updatedby_id integer,
    deletedby_id integer,
    amount double precision NOT NULL,
    rate double precision,
    codetransaction character varying(255) DEFAULT NULL::character varying,
    datepredicted date
);


--
-- Name: activitypayment_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE activitypayment_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: activityperson; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE activityperson (
    id integer NOT NULL,
    person_id integer,
    activity_id integer,
    main boolean,
    role character varying(255) DEFAULT NULL::character varying,
    status integer,
    datecreated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    dateupdated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    datedeleted timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    createdby_id integer,
    updatedby_id integer,
    deletedby_id integer,
    datestart timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    dateend timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    roleobj_id integer
);


--
-- Name: activityperson_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE activityperson_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: activitytype; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE activitytype (
    id integer NOT NULL,
    lft integer NOT NULL,
    rgt integer NOT NULL,
    status integer,
    datecreated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    dateupdated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    datedeleted timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    createdby_id integer,
    updatedby_id integer,
    deletedby_id integer,
    label character varying(255) DEFAULT NULL::character varying,
    description character varying(255) DEFAULT NULL::character varying,
    nature character varying(255) DEFAULT NULL::character varying,
    centaureid character varying(255) DEFAULT NULL::character varying
);


--
-- Name: activitytype_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE activitytype_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: administrativedocument; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE administrativedocument (
    id integer NOT NULL,
    person_id integer,
    dateupdoad date,
    path character varying(255) NOT NULL,
    information text,
    filetypemime character varying(255) DEFAULT NULL::character varying,
    filesize integer,
    filename character varying(255) DEFAULT NULL::character varying,
    version integer,
    status integer DEFAULT 1 NOT NULL
);


--
-- Name: administrativedocument_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE administrativedocument_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: authentification; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE authentification (
    id integer NOT NULL,
    username character varying(255) DEFAULT NULL::character varying,
    email character varying(255) NOT NULL,
    display_name character varying(50) NOT NULL,
    password character varying(128) NOT NULL,
    state smallint NOT NULL,
    datelogin timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    settings text,
    secret character varying(255) DEFAULT NULL::character varying
);


--
-- Name: COLUMN authentification.settings; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN authentification.settings IS '(DC2Type:array)';


--
-- Name: authentification_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE authentification_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: authentification_role; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE authentification_role (
    authentification_id integer NOT NULL,
    role_id integer NOT NULL
);


--
-- Name: categorie_privilege; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE categorie_privilege (
    id integer NOT NULL,
    code character varying(150) NOT NULL,
    libelle character varying(200) NOT NULL,
    ordre integer
);


--
-- Name: categorie_privilege_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE categorie_privilege_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: contractdocument; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE contractdocument (
    id integer NOT NULL,
    grant_id integer,
    person_id integer,
    dateupdoad date,
    path character varying(255) NOT NULL,
    information text,
    centaureid character varying(255) DEFAULT NULL::character varying,
    filetypemime character varying(255) DEFAULT NULL::character varying,
    filesize integer,
    filename character varying(255) DEFAULT NULL::character varying,
    version integer,
    typedocument_id integer,
    status integer DEFAULT 1 NOT NULL,
    datedeposit date,
    datesend date
);


--
-- Name: contractdocument_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE contractdocument_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: contracttype; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE contracttype (
    id integer NOT NULL,
    code character varying(255) NOT NULL,
    label character varying(255) NOT NULL,
    description character varying(255) NOT NULL,
    lft integer NOT NULL,
    rgt integer NOT NULL
);


--
-- Name: contracttype_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE contracttype_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: currency; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE currency (
    id integer NOT NULL,
    status integer,
    datecreated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    dateupdated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    datedeleted timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    createdby_id integer,
    updatedby_id integer,
    deletedby_id integer,
    label character varying(20) DEFAULT NULL::character varying NOT NULL,
    symbol character varying(1) DEFAULT NULL::character varying NOT NULL,
    rate double precision NOT NULL
);


--
-- Name: currency_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE currency_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: datetype; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE datetype (
    id integer NOT NULL,
    label character varying(255) DEFAULT NULL::character varying,
    description character varying(255) DEFAULT NULL::character varying,
    status integer,
    datecreated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    dateupdated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    datedeleted timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    createdby_id integer,
    updatedby_id integer,
    deletedby_id integer,
    facet character varying(255) DEFAULT NULL::character varying,
    recursivity character varying(255) DEFAULT NULL::character varying
);


--
-- Name: datetype_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE datetype_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: discipline; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE discipline (
    id integer NOT NULL,
    label character varying(128) NOT NULL,
    centaureid character varying(10) DEFAULT NULL::character varying
);


--
-- Name: discipline_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE discipline_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: grantsource; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE grantsource (
    id integer NOT NULL,
    description character varying(255) DEFAULT NULL::character varying,
    logo character varying(255) DEFAULT NULL::character varying,
    informations character varying(255) DEFAULT NULL::character varying,
    centaureid character varying(10) DEFAULT NULL::character varying,
    label character varying(255) DEFAULT NULL::character varying
);


--
-- Name: grantsource_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE grantsource_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: logactivity; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE logactivity (
    id integer NOT NULL,
    datecreated timestamp(0) without time zone NOT NULL,
    message text NOT NULL,
    context character varying(255) NOT NULL,
    contextid character varying(255) DEFAULT NULL::character varying,
    userid integer,
    level integer NOT NULL,
    type character varying(255) NOT NULL,
    ip character varying(255) DEFAULT NULL::character varying,
    datas text
);


--
-- Name: COLUMN logactivity.datas; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN logactivity.datas IS '(DC2Type:object)';


--
-- Name: logactivity_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE logactivity_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: notification; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE notification (
    id integer NOT NULL,
    dateeffective date NOT NULL,
    datereal date NOT NULL,
    datecreated timestamp(0) with time zone NOT NULL,
    message text NOT NULL,
    object character varying(255) DEFAULT NULL::character varying,
    objectid integer,
    hash character varying(255) NOT NULL,
    context character varying(255) NOT NULL,
    serie character varying(255) DEFAULT NULL::character varying,
    level integer NOT NULL,
    datas text
);


--
-- Name: COLUMN notification.datas; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN notification.datas IS '(DC2Type:object)';


--
-- Name: notification_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE notification_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: notificationperson; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE notificationperson (
    id integer NOT NULL,
    notification_id integer,
    person_id integer,
    read timestamp(0) without time zone DEFAULT NULL::timestamp without time zone
);


--
-- Name: notificationperson_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE notificationperson_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: organization; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE organization (
    id integer NOT NULL,
    centaureid character varying(10) DEFAULT NULL::character varying,
    shortname character varying(128) DEFAULT NULL::character varying,
    fullname character varying(255) DEFAULT NULL::character varying,
    code character varying(255) DEFAULT NULL::character varying,
    email character varying(255) DEFAULT NULL::character varying,
    url character varying(255) DEFAULT NULL::character varying,
    description character varying(255) DEFAULT NULL::character varying,
    street1 character varying(255) DEFAULT NULL::character varying,
    street2 character varying(255) DEFAULT NULL::character varying,
    street3 character varying(255) DEFAULT NULL::character varying,
    city character varying(255) DEFAULT NULL::character varying,
    zipcode character varying(255) DEFAULT NULL::character varying,
    phone character varying(255) DEFAULT NULL::character varying,
    dateupdated timestamp(0) without time zone,
    datecreated timestamp(0) without time zone,
    dateend timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    datestart timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    status integer,
    datedeleted timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    createdby_id integer,
    updatedby_id integer,
    deletedby_id integer,
    ldapsupanncodeentite character varying(255) DEFAULT NULL::character varying,
    country character varying(255) DEFAULT NULL::character varying,
    sifacid character varying(255) DEFAULT NULL::character varying,
    codepays character varying(2) DEFAULT NULL::character varying,
    siret character varying(255) DEFAULT NULL::character varying,
    bp character varying(255) DEFAULT NULL::character varying,
    type character varying(255) DEFAULT NULL::character varying,
    sifacgroup character varying(255) DEFAULT NULL::character varying,
    sifacgroupid character varying(255) DEFAULT NULL::character varying,
    numtvaca character varying(255) DEFAULT NULL::character varying,
    connectors text
);


--
-- Name: COLUMN organization.connectors; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN organization.connectors IS '(DC2Type:object)';


--
-- Name: organization_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE organization_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: organization_role; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE organization_role (
    id integer NOT NULL,
    role_id character varying(255) NOT NULL,
    description character varying(255) DEFAULT NULL::character varying,
    principal boolean DEFAULT false NOT NULL
);


--
-- Name: organization_role_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE organization_role_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: organizationperson; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE organizationperson (
    id integer NOT NULL,
    person_id integer,
    organization_id integer,
    main boolean,
    role character varying(255) DEFAULT NULL::character varying,
    datestart timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    dateend timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    status integer,
    datecreated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    dateupdated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    datedeleted timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    createdby_id integer,
    updatedby_id integer,
    deletedby_id integer,
    roleobj_id integer
);


--
-- Name: organizationperson_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE organizationperson_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: organizationrole; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE organizationrole (
    id integer NOT NULL,
    label character varying(255) NOT NULL,
    description character varying(255) DEFAULT NULL::character varying,
    principal boolean DEFAULT false NOT NULL,
    status integer,
    datecreated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    dateupdated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    datedeleted timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    createdby_id integer,
    updatedby_id integer,
    deletedby_id integer
);


--
-- Name: organizationrole_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE organizationrole_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: person; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE person (
    id integer NOT NULL,
    firstname character varying(255) DEFAULT NULL::character varying,
    lastname character varying(255) DEFAULT NULL::character varying,
    codeharpege character varying(255) DEFAULT NULL::character varying,
    centaureid text,
    codeldap character varying(255) DEFAULT NULL::character varying,
    email character varying(255) DEFAULT NULL::character varying,
    ldapstatus character varying(255) DEFAULT NULL::character varying,
    ldapsitelocation character varying(255) DEFAULT NULL::character varying,
    ldapaffectation character varying(255) DEFAULT NULL::character varying,
    ldapdisabled boolean,
    ldapfininscription character varying(255) DEFAULT NULL::character varying,
    ladaplogin character varying(255) DEFAULT NULL::character varying,
    phone character varying(255) DEFAULT NULL::character varying,
    datesyncldap timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    status integer,
    datecreated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    dateupdated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    datedeleted timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    createdby_id integer,
    updatedby_id integer,
    deletedby_id integer,
    emailprive character varying(255) DEFAULT NULL::character varying,
    harpegeinm character varying(255) DEFAULT NULL::character varying,
    connectors text,
    ldapmemberof text
);


--
-- Name: COLUMN person.centaureid; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN person.centaureid IS '(DC2Type:simple_array)';


--
-- Name: COLUMN person.connectors; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN person.connectors IS '(DC2Type:object)';


--
-- Name: COLUMN person.ldapmemberof; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN person.ldapmemberof IS '(DC2Type:array)';


--
-- Name: person_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE person_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: privilege; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE privilege (
    id integer NOT NULL,
    categorie_id integer,
    code character varying(150) NOT NULL,
    libelle character varying(200) NOT NULL,
    ordre integer,
    root_id integer,
    spot integer DEFAULT 7
);


--
-- Name: privilege_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE privilege_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: project; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE project (
    id integer NOT NULL,
    discipline_id integer,
    centaureid character varying(10) DEFAULT NULL::character varying,
    code character varying(48) DEFAULT NULL::character varying,
    eotp character varying(64) DEFAULT NULL::character varying,
    composanteprincipal character varying(32) DEFAULT NULL::character varying,
    acronym character varying(255),
    label character varying(255) NOT NULL,
    description character varying(255) DEFAULT NULL::character varying,
    datecreated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    dateupdated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    datevalidated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone
);


--
-- Name: project_discipline; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE project_discipline (
    project_id integer NOT NULL,
    discipline_id integer NOT NULL
);


--
-- Name: project_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE project_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: projectgrant_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE projectgrant_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: projectmember; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE projectmember (
    id integer NOT NULL,
    project_id integer,
    person_id integer,
    role character varying(255),
    datestart timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    dateend timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    main boolean,
    status integer,
    datecreated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    dateupdated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    datedeleted timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    createdby_id integer,
    updatedby_id integer,
    deletedby_id integer,
    roleobj_id integer
);


--
-- Name: projectmember_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE projectmember_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: projectpartner; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE projectpartner (
    id integer NOT NULL,
    project_id integer,
    organization_id integer,
    datestart timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    dateend timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    main boolean,
    role character varying(255) DEFAULT NULL::character varying,
    status integer,
    datecreated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    dateupdated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    datedeleted timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    createdby_id integer,
    updatedby_id integer,
    deletedby_id integer,
    roleobj_id integer
);


--
-- Name: projectpartner_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE projectpartner_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: role_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE role_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: role_privilege; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE role_privilege (
    privilege_id integer NOT NULL,
    role_id integer NOT NULL
);


--
-- Name: timesheet; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE timesheet (
    id integer NOT NULL,
    workpackage_id integer,
    person_id integer,
    datefrom timestamp(0) with time zone NOT NULL,
    dateto timestamp(0) with time zone NOT NULL,
    comment text,
    status integer,
    datecreated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    dateupdated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    datedeleted timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    createdby_id integer,
    updatedby_id integer,
    deletedby_id integer,
    activity_id integer,
    label text,
    icsuid text,
    icsfileuid text,
    icsfilename text,
    icsfiledateadded timestamp(0) with time zone DEFAULT NULL::timestamp with time zone,
    validatedsciby character varying(255) DEFAULT NULL::character varying,
    validatedscibyid integer,
    validatedsciat timestamp(0) with time zone DEFAULT NULL::timestamp with time zone,
    validatedadminby character varying(255) DEFAULT NULL::character varying,
    validatedadminbyid integer,
    validatedadminat timestamp(0) with time zone DEFAULT NULL::timestamp with time zone,
    rejectedsciby character varying(255) DEFAULT NULL::character varying,
    rejectedscibyid integer,
    rejectedsciat timestamp(0) with time zone DEFAULT NULL::timestamp with time zone,
    rejectedscicomment text,
    rejectedadminby character varying(255) DEFAULT NULL::character varying,
    rejectedadminbyid integer,
    rejectedadminat timestamp(0) with time zone DEFAULT NULL::timestamp with time zone,
    rejectedadmincomment text,
    sendby character varying(255) DEFAULT NULL::character varying
);


--
-- Name: timesheet_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE timesheet_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: tva; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE tva (
    id integer NOT NULL,
    label character varying(20) NOT NULL,
    rate double precision NOT NULL,
    active boolean NOT NULL,
    status integer,
    datecreated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    dateupdated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    datedeleted timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    createdby_id integer,
    updatedby_id integer,
    deletedby_id integer
);


--
-- Name: tva_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE tva_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: typedocument; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE typedocument (
    id integer NOT NULL,
    label character varying(255) NOT NULL,
    description character varying(255) DEFAULT NULL::character varying,
    codecentaure character varying(255) DEFAULT NULL::character varying,
    status integer,
    datecreated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    dateupdated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    datedeleted timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    createdby_id integer,
    updatedby_id integer,
    deletedby_id integer
);


--
-- Name: typedocument_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE typedocument_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: user_role; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE user_role (
    id integer NOT NULL,
    parent_id integer,
    role_id character varying(255) NOT NULL,
    is_default boolean NOT NULL,
    ldap_filter character varying(255) DEFAULT NULL::character varying,
    spot integer DEFAULT 7,
    description character varying(255) DEFAULT NULL::character varying,
    principal boolean DEFAULT false NOT NULL
);


--
-- Name: user_role_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE user_role_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: useraccessdefinition; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE useraccessdefinition (
    id integer NOT NULL,
    context character varying(200) NOT NULL,
    label character varying(200) NOT NULL,
    description character varying(200) DEFAULT NULL::character varying,
    key character varying(200) NOT NULL
);


--
-- Name: useraccessdefinition_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE useraccessdefinition_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: workpackage; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE workpackage (
    id integer NOT NULL,
    activity_id integer,
    status integer,
    datecreated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    dateupdated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    datedeleted timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    createdby_id integer,
    updatedby_id integer,
    deletedby_id integer,
    code character varying(255) DEFAULT NULL::character varying,
    label character varying(255) NOT NULL,
    description text,
    datestart date,
    dateend date
);


--
-- Name: workpackage_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE workpackage_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: workpackageperson; Type: TABLE; Schema: public; Owner: -; Tablespace: 
--

CREATE TABLE workpackageperson (
    id integer NOT NULL,
    person_id integer,
    duration integer NOT NULL,
    status integer,
    datecreated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    dateupdated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    datedeleted timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    workpackage_id integer,
    createdby_id integer,
    updatedby_id integer,
    deletedby_id integer
);


--
-- Name: workpackageperson_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE workpackageperson_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Data for Name: activity; Type: TABLE DATA; Schema: public; Owner: -
--

COPY activity (id, source_id, project_id, type_id, centaureid, centaurenumconvention, codeeotp, label, description, hassheet, duration, justifyworkingtime, justifycost, amount, datestart, dateend, datesigned, dateopened, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id, activitytype_id, currency_id, tva_id, oscarid, oscarnum, timesheetformat, numbers, financialimpact) FROM stdin;
\.


--
-- Data for Name: activity_discipline; Type: TABLE DATA; Schema: public; Owner: -
--

COPY activity_discipline (activity_id, discipline_id) FROM stdin;
\.


--
-- Name: activity_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('activity_id_seq', 9839, true);


--
-- Data for Name: activitydate; Type: TABLE DATA; Schema: public; Owner: -
--

COPY activitydate (id, type_id, activity_id, datestart, comment, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id) FROM stdin;
\.


--
-- Name: activitydate_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('activitydate_id_seq', 1114, true);


--
-- Data for Name: activityorganization; Type: TABLE DATA; Schema: public; Owner: -
--

COPY activityorganization (id, organization_id, activity_id, main, role, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id, datestart, dateend, roleobj_id) FROM stdin;
\.


--
-- Name: activityorganization_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('activityorganization_id_seq', 83929, true);


--
-- Data for Name: activitypayment; Type: TABLE DATA; Schema: public; Owner: -
--

COPY activitypayment (id, activity_id, currency_id, datepayment, comment, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id, amount, rate, codetransaction, datepredicted) FROM stdin;
\.


--
-- Name: activitypayment_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('activitypayment_id_seq', 2466, true);


--
-- Data for Name: activityperson; Type: TABLE DATA; Schema: public; Owner: -
--

COPY activityperson (id, person_id, activity_id, main, role, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id, datestart, dateend, roleobj_id) FROM stdin;
\.


--
-- Name: activityperson_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('activityperson_id_seq', 20984, true);


--
-- Data for Name: activitytype; Type: TABLE DATA; Schema: public; Owner: -
--

COPY activitytype (id, lft, rgt, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id, label, description, nature, centaureid) FROM stdin;
1	1	4	\N	\N	\N	\N	\N	\N	\N	ROOT	\N	\N	\N
411	2	3	1	2017-04-24 12:31:55	\N	\N	\N	\N	\N	Type non-définit		0	\N
\.


--
-- Name: activitytype_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('activitytype_id_seq', 411, true);


--
-- Data for Name: administrativedocument; Type: TABLE DATA; Schema: public; Owner: -
--

COPY administrativedocument (id, person_id, dateupdoad, path, information, filetypemime, filesize, filename, version, status) FROM stdin;
\.


--
-- Name: administrativedocument_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('administrativedocument_id_seq', 14, true);


--
-- Data for Name: authentification; Type: TABLE DATA; Schema: public; Owner: -
--

COPY authentification (id, username, email, display_name, password, state, datelogin, settings, secret) FROM stdin;
\.


--
-- Name: authentification_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('authentification_id_seq', 120, true);


--
-- Data for Name: authentification_role; Type: TABLE DATA; Schema: public; Owner: -
--

COPY authentification_role (authentification_id, role_id) FROM stdin;
\.


--
-- Data for Name: categorie_privilege; Type: TABLE DATA; Schema: public; Owner: -
--

COPY categorie_privilege (id, code, libelle, ordre) FROM stdin;
2	ACTIVITY	Activité de recherche	\N
3	PERSON	Personne	\N
4	ORGANIZATION	Organisation	\N
5	DOCUMENT	Document	\N
6	MAINTENANCE	Maintenance	\N
7	droit	Gestion des droits	\N
1	PROJECT	Projet	\N
8	ADMINISTRATIVE	Informations administratives	\N
9	DEPENSE	Accès aux dépenses	\N
\.


--
-- Name: categorie_privilege_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('categorie_privilege_id_seq', 1, false);


--
-- Data for Name: contractdocument; Type: TABLE DATA; Schema: public; Owner: -
--

COPY contractdocument (id, grant_id, person_id, dateupdoad, path, information, centaureid, filetypemime, filesize, filename, version, typedocument_id, status, datedeposit, datesend) FROM stdin;
\.


--
-- Name: contractdocument_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('contractdocument_id_seq', 55696, true);


--
-- Data for Name: contracttype; Type: TABLE DATA; Schema: public; Owner: -
--

COPY contracttype (id, code, label, description, lft, rgt) FROM stdin;
1	ROOT			1	462
2	ADMINISTR.	Label ADMINISTR.	Description pour la catégorie ADMINISTR.	2	23
3	HEBERGEM.	Label HEBERGEM.	Description pour la sous-catégorie HEBERGEM.	3	8
4	HEB.MAT.	Hébergement de matériel (serveurs...)	Description pour la rubrique Hébergement de matériel (serveurs...)	4	5
5	HEB.PERSON	Hébergement personnel	Description pour la rubrique Hébergement personnel	6	7
6	LOCATION	Label LOCATION	Description pour la sous-catégorie LOCATION	9	14
7	LOCAUX NUS	Locaux nus	Description pour la rubrique Locaux nus	10	11
8	MAINT	Label MAINT	Description pour la sous-catégorie MAINT	15	22
9	MAINT.LOGI	Maintenance logiciel	Description pour la rubrique Maintenance logiciel	16	17
10	COMMUNICAT	Label COMMUNICAT	Description pour la catégorie COMMUNICAT	24	29
11	COMM	Label COMM	Description pour la sous-catégorie COMM	25	28
12	SUB_COMM	actions de communication subventionnées	Description pour la rubrique actions de communication subventionnées	26	27
13	FORMATION	Label FORMATION	Description pour la catégorie FORMATION	30	113
14	ACADRE_FIC	Label ACADRE_FIC	Description pour la sous-catégorie ACADRE_FIC	31	42
15	AC_FOR_INT	accord cadre formation partenaire international	Description pour la rubrique accord cadre formation partenaire international	32	33
16	AC_FOR_PRI	accord cadre formation partenaire priv¿	Description pour la rubrique accord cadre formation partenaire priv¿	34	35
17	AC_FOR_PUB	accord cadre formation partenaire public	Description pour la rubrique accord cadre formation partenaire public	36	37
18	AC_FOR_QUA	accord formation quadriennal etat	Description pour la rubrique accord formation quadriennal etat	38	39
19	AC_FOR_AUT	autre accord cadre formation	Description pour la rubrique autre accord cadre formation	40	41
20	UE_FIC	Label UE_FIC	Description pour la sous-catégorie UE_FIC	43	50
21	EF_FOR-UE	autres programmes européens de formation	Description pour la rubrique autres programmes européens de formation	44	45
22	REL_INTER	Label REL_INTER	Description pour la sous-catégorie REL_INTER	51	54
23	COLLAB_INT	collaboration internationale	Description pour la rubrique collaboration internationale	52	53
24	CEE	Label CEE	Description pour la sous-catégorie CEE	55	62
25	COM	COMENIUS	Description pour la rubrique COMENIUS	56	57
26	CONV.STAGE	Convention de stage	Description pour la rubrique Convention de stage	63	64
27	ACC.CADRE	Label ACC.CADRE	Description pour la sous-catégorie ACC.CADRE	65	66
28	FORM.EXT.	Formation externe (vente formation)	Description pour la rubrique Formation externe (vente formation)	67	68
29	FORM.INT.	Formation interne (personnel établissement)	Description pour la rubrique Formation interne (personnel établissement)	69	70
30	EF_FOR-FSE	fse formation	Description pour la rubrique fse formation	46	47
31	HEURES ENS	Heures d'enseignement	Description pour la rubrique Heures d'enseignement	71	72
32	LEONARDO	Léonardo	Description pour la rubrique Léonardo	58	59
33	ERASMUS	programme erasmus	Description pour la rubrique programme erasmus	48	49
34	SUBV	Label SUBV	Description pour la sous-catégorie SUBV	73	92
35	SUBV FONCT	Subvention de fonctionnement	Description pour la rubrique Subvention de fonctionnement	74	75
36	CR_FIC	Label CR_FIC	Description pour la sous-catégorie CR_FIC	93	100
37	FT_FOR_CR	subvention de fonctionnement formation	Description pour la rubrique subvention de fonctionnement formation	94	95
38	AU_COL_FIC	Label AU_COL_FIC	Description pour la sous-catégorie AU_COL_FIC	101	108
39	FT_FOR_CT	subvention de fonctionnement formation	Description pour la rubrique subvention de fonctionnement formation	102	103
40	SUBV EQUIP	Subvention d'équipement	Description pour la rubrique Subvention d'équipement	76	77
41	SUBV EQFCT	Subvention d'équipement et de fonctionnement	Description pour la rubrique Subvention d'équipement et de fonctionnement	78	79
42	EQ_FOR_CR	subvention d''équipement formation	Description pour la rubrique subvention d''équipement formation	96	97
43	EQ_FOR_CT	subvention d''équipement formation	Description pour la rubrique subvention d''équipement formation	104	105
44	EF_FOR_CR	subvention équipement et fonctionnement formation	Description pour la rubrique subvention équipement et fonctionnement formation	98	99
45	EF_FOR_CT	subvention équipement et fonctionnement formation	Description pour la rubrique subvention équipement et fonctionnement formation	106	107
46	COLL_FIC	Label COLL_FIC	Description pour la sous-catégorie COLL_FIC	109	112
47	SUB_COLL_F	subventions colloques formation	Description pour la rubrique subventions colloques formation	110	111
48	TEMPUS	Tempus	Description pour la rubrique Tempus	60	61
49	IMMO/STRAT	Label IMMO/STRAT	Description pour la catégorie IMMO/STRAT	114	115
50	SUBV-FONCT	Subvention de fonctionnement	Description pour la rubrique Subvention de fonctionnement	80	81
51	SUBV-EQUIP	Subvention d'équipement	Description pour la rubrique Subvention d'équipement	82	83
52	SUBV-EQFCT	Subvention d'équipement et de fonctionnement	Description pour la rubrique Subvention d'équipement et de fonctionnement	84	85
53	RECHERCHE	Label RECHERCHE	Description pour la catégorie RECHERCHE	116	453
54	THESE	Label THESE	Description pour la sous-catégorie THESE	117	126
55	ACC.INDUST	Accompagnement industriel	Description pour la rubrique Accompagnement industriel	118	119
56	ACADRE_RV	Label ACADRE_RV	Description pour la sous-catégorie ACADRE_RV	127	156
57	AC_RE_INT	accord cadre recherche partenaire international	Description pour la rubrique accord cadre recherche partenaire international	128	129
58	AC_RE_PRI	accord cadre recherche partenaire privé	Description pour la rubrique accord cadre recherche partenaire privé	130	131
59	AC_RE_PUB	accord cadre recherche partenaire public	Description pour la rubrique accord cadre recherche partenaire public	132	133
60	VALO	Label VALO	Description pour la sous-catégorie VALO	157	214
61	ACC.CONF.	Accord de confidentialité	Description pour la rubrique Accord de confidentialité	158	159
62	RECHPART	Label RECHPART	Description pour la sous-catégorie RECHPART	215	250
63	RP_NDA	accord de confidentialité	Description pour la rubrique accord de confidentialité	216	217
64	COPRO	accord de copropriété intellectuelle	Description pour la rubrique accord de copropriété intellectuelle	160	161
65	COPRO_EXPL	accord de copropriété intellectuelle et exploit.	Description pour la rubrique accord de copropriété intellectuelle et exploit.	162	163
66	RP_AC	ACCORDS DE CONSORTIUM	Description pour la rubrique ACCORDS DE CONSORTIUM	218	219
67	ACHAT COM.	Label ACHAT COM.	Description pour la sous-catégorie ACHAT COM.	251	254
68	ACHAT COMM	Achat en commun	Description pour la rubrique Achat en commun	252	253
69	ACQ_AUTEUR	acquisition de droit d''auteur par l''ufc	Description pour la rubrique acquisition de droit d''auteur par l''ufc	164	165
70	ALLOC	Label ALLOC	Description pour la sous-catégorie ALLOC	255	274
71	AL_D_ADEME	ademe	Description pour la rubrique ademe	256	257
72	OSEO	Label OSEO	Description pour la sous-catégorie OSEO	275	282
73	AREMB_OSEO	aides au transfert oseo avances remboursables	Description pour la rubrique aides au transfert oseo avances remboursables	276	277
74	SUB_OSEO	aides au transfert oseo subvention	Description pour la rubrique aides au transfert oseo subvention	278	279
75	AJ_OSEO	aides jeunes innovation oseo	Description pour la rubrique aides jeunes innovation oseo	280	281
76	AL_D_AUT	alloc. doct. autres	Description pour la rubrique alloc. doct. autres	258	259
77	AL_D_CG	alloc. doct. conseils généraux	Description pour la rubrique alloc. doct. conseils généraux	260	261
78	AL_D_MEN	alloc. doct. ministère education nationale	Description pour la rubrique alloc. doct. ministère education nationale	262	263
79	AL_PD_AUT	alloc. post-doct. autres	Description pour la rubrique alloc. post-doct. autres	264	265
80	AL_PD_CG	alloc. post-doct. conseils généraux	Description pour la rubrique alloc. post-doct. conseils généraux	266	267
81	AL_PD_MEN	alloc. post-doct. ministère education nationale	Description pour la rubrique alloc. post-doct. ministère education nationale	268	269
82	AL_PD_REG	alloc. post-doct. région	Description pour la rubrique alloc. post-doct. région	270	271
83	AL_PD_VGC	alloc. post-doct. ville et groupement de communes	Description pour la rubrique alloc. post-doct. ville et groupement de communes	272	273
84	ANR	Label ANR	Description pour la sous-catégorie ANR	283	322
85	ANR_GE	anr - AAP - Générique	Description pour la rubrique anr - AAP - Générique	284	285
86	ANR_PRCI	anr - AAP - PRCI	Description pour la rubrique anr - AAP - PRCI	286	287
87	ANR_PREDIT	ANR ASTRID	Description pour la rubrique ANR ASTRID	288	289
88	BQR	Label BQR	Description pour la sous-catégorie BQR	323	332
89	ANR_ASTRID	anr ASTRID	Description pour la rubrique anr ASTRID	324	325
90	ANR_NTHEM	anr autres non-thématiques	Description pour la rubrique anr autres non-thématiques	290	291
91	ANR_BS	anr biologie-santé	Description pour la rubrique anr biologie-santé	292	293
92	ANR_BL	anr blanc	Description pour la rubrique anr blanc	294	295
93	ANR_CARNF	ANR CARNOT FRAUNHOFFER	Description pour la rubrique ANR CARNOT FRAUNHOFFER	296	297
94	ANR_EX	anr chaires d''excellence	Description pour la rubrique anr chaires d''excellence	298	299
95	ANR_EDD	anr ecosystèmes et développement durable	Description pour la rubrique anr ecosystèmes et développement durable	300	301
96	ANR_EDE	anr énergie durable et environnement	Description pour la rubrique anr énergie durable et environnement	302	303
97	ANR_ERB	anr environnement et ressources biologiques	Description pour la rubrique anr environnement et ressources biologiques	304	305
98	ANR_IPS	anr ingénierie, procédés et sécurité	Description pour la rubrique anr ingénierie, procédés et sécurité	306	307
99	ANR_JC	anr jeunes chercheurs	Description pour la rubrique anr jeunes chercheurs	308	309
100	ANR_PART	anr partenariats et compétitivité	Description pour la rubrique anr partenariats et compétitivité	310	311
101	ANR_TR	anr programmes transversaux	Description pour la rubrique anr programmes transversaux	312	313
102	ANR_REE	anr recherches exploratoires et émergentes	Description pour la rubrique anr recherches exploratoires et émergentes	314	315
103	ANR_STI	anr sciences et technologies de l''information	Description pour la rubrique anr sciences et technologies de l''information	316	317
104	ANR_SHS	anr sciences humaines et sociales	Description pour la rubrique anr sciences humaines et sociales	318	319
105	ANR_INNOV	anr sociétés innovantes, nouvelle économie	Description pour la rubrique anr sociétés innovantes, nouvelle économie	320	321
106	AC_RE_AUT	autre accord cadre recherche	Description pour la rubrique autre accord cadre recherche	134	135
107	TT_AUT	autre accord de transfert de technologie	Description pour la rubrique autre accord de transfert de technologie	166	167
108	AC_LCO_AUT	autre laboratoire commun	Description pour la rubrique autre laboratoire commun	136	137
109	UE_RV	Label UE_RV	Description pour la sous-catégorie UE_RV	333	374
110	AUTRES-UE	Autres financements UE	Description pour la rubrique Autres financements UE	334	335
111	COMPET	Label COMPET	Description pour la sous-catégorie COMPET	375	386
112	POLE_AUT	autres pôles	Description pour la rubrique autres pôles	376	377
113	RD_AUT_UE	autres programmes européens de r et d	Description pour la rubrique autres programmes européens de r et d	336	337
114	INTER	Label INTER	Description pour la sous-catégorie INTER	387	392
115	EF_RD_HUE	autres programmes internationaux r et d hors ue	Description pour la rubrique autres programmes internationaux r et d hors ue	388	389
116	BDI	Bourse Docteur Ingénieur	Description pour la rubrique Bourse Docteur Ingénieur	120	121
117	BOURSE REG	Bourse Région	Description pour la rubrique Bourse Région	122	123
118	BQR_AUTRES	bqr autres	Description pour la rubrique bqr autres	326	327
119	BQR_ENSMM	bqr ensmm	Description pour la rubrique bqr ensmm	328	329
120	BQR_UFC	bqr ufc	Description pour la rubrique bqr ufc	330	331
121	CES_BR	cession de brevet	Description pour la rubrique cession de brevet	168	169
122	CES_AUTEUR	cession de droit d''auteur de l''ufc	Description pour la rubrique cession de droit d''auteur de l''ufc	170	171
123	CES_LOG	cession de logiciel	Description pour la rubrique cession de logiciel	172	173
124	CES_LOG_BR	cession de logiciel et brevet	Description pour la rubrique cession de logiciel et brevet	174	175
125	CES_QP	cession de quote part	Description pour la rubrique cession de quote part	176	177
126	CES_SF	cession de savoir-faire	Description pour la rubrique cession de savoir-faire	178	179
127	CES_SF_BR	cession de savoir-faire et brevet	Description pour la rubrique cession de savoir-faire et brevet	180	181
128	CES_LOG_SF	cession logiciel et savoir faire	Description pour la rubrique cession logiciel et savoir faire	182	183
129	COLL.RECH	Label COLL.RECH	Description pour la sous-catégorie COLL.RECH	393	408
130	RP_PUB_SIF	collab recherc publique sans incidence financière	Description pour la rubrique collab recherc publique sans incidence financière	394	395
131	RP_PRI_SIF	collab recherche privée sans incidence financière	Description pour la rubrique collab recherche privée sans incidence financière	396	397
132	COLL VALO	collaboration de recherche avec valo prénégociée	Description pour la rubrique collaboration de recherche avec valo prénégociée	184	185
133	RP_PUB_GO	collaboration recheche publique grands organismes	Description pour la rubrique collaboration recheche publique grands organismes	398	399
134	RP_PRI_GGR	Collaboration recherche  industriel grands groupes	Description pour la rubrique Collaboration recherche  industriel grands groupes	400	401
135	RP_PRI_AUT	Collaboration recherche industriel  autres	Description pour la rubrique Collaboration recherche industriel  autres	402	403
136	RP_PRI_PME	Collaboration recherche industriel pme	Description pour la rubrique Collaboration recherche industriel pme	404	405
137	RP_PUB_AUT	collaboration recherche publique autres	Description pour la rubrique collaboration recherche publique autres	406	407
138	CIFRE	Contrat Accompagnement Cifre	Description pour la rubrique Contrat Accompagnement Cifre	124	125
139	BREVET	Contrat de copropriété de Brevet	Description pour la rubrique Contrat de copropriété de Brevet	186	187
140	RP_MAD_LOC	contrat de mise à disposition de locaux	Description pour la rubrique contrat de mise à disposition de locaux	220	221
141	RP_MAD_MAT	contrat de mise à disposition de matériel	Description pour la rubrique contrat de mise à disposition de matériel	222	223
142	REP_ROYALT	contrat de répartition de redevances	Description pour la rubrique contrat de répartition de redevances	188	189
143	RP_MTA	contrat de transfert de matériel	Description pour la rubrique contrat de transfert de matériel	224	225
144	EDITION	contrat d''édition	Description pour la rubrique contrat d''édition	190	191
145	AC_RE_QUA	contrat quadriennal volet recherche	Description pour la rubrique contrat quadriennal volet recherche	138	139
146	25_2	convention de concours scientifique	Description pour la rubrique convention de concours scientifique	192	193
147	25_1	convention de mise en délégation	Description pour la rubrique convention de mise en délégation	194	195
148	RP_HEBERG	convention d''hébergement	Description pour la rubrique convention d''hébergement	226	227
149	COST	Cost	Description pour la rubrique Cost	338	339
150	EUREKA	Eureka	Description pour la rubrique Eureka	340	341
151	FEAMP	feamp	Description pour la rubrique feamp	342	343
152	FEDER 2014	FEDER - 2014 / 2020	Description pour la rubrique FEDER - 2014 / 2020	344	345
153	FEDER	financements feder	Description pour la rubrique financements feder	346	347
154	FP6	fp6 tous programmes	Description pour la rubrique fp6 tous programmes	348	349
155	FP7_CURIE	fp7 bourses marie curie	Description pour la rubrique fp7 bourses marie curie	350	351
156	FP7_CAPA	fp7 capacité	Description pour la rubrique fp7 capacité	352	353
157	FP7_COOP	fp7 coopération	Description pour la rubrique fp7 coopération	354	355
158	FP7_ERC	fp7 idées	Description pour la rubrique fp7 idées	356	357
159	FSE_RECH	fse recherche	Description pour la rubrique fse recherche	358	359
160	GRANT	GRANT	Description pour la rubrique GRANT	228	229
161	AC_GDR	groupement de recherche	Description pour la rubrique groupement de recherche	140	141
162	AC_GDRE	groupement de recherche européen	Description pour la rubrique groupement de recherche européen	142	143
163	AC_GDRI	groupement de recherche international hors ue	Description pour la rubrique groupement de recherche international hors ue	144	145
164	H2020	H2020	Description pour la rubrique H2020	360	361
165	INTER_IV_A	interreg iv a	Description pour la rubrique interreg iv a	362	363
166	INTER_IV_B	interreg iv b	Description pour la rubrique interreg iv b	364	365
167	INTER_IV_C	interreg iv c	Description pour la rubrique interreg iv c	366	367
168	INTERREG V	interreg V	Description pour la rubrique interreg V	368	369
169	AC_LCO_PRI	laboratoire commun partenaire privé	Description pour la rubrique laboratoire commun partenaire privé	146	147
170	AC_LCO_PUB	laboratoire commun partenaire public	Description pour la rubrique laboratoire commun partenaire public	148	149
171	AC_LEA	laboratoire européen associé	Description pour la rubrique laboratoire européen associé	150	151
172	AC_LIA	laboratoire international associé	Description pour la rubrique laboratoire international associé	152	153
173	LICENCE	Licence	Description pour la rubrique Licence	196	197
174	LIC_BR	licence de brevet	Description pour la rubrique licence de brevet	198	199
175	LIC_LOG	licence de logiciel	Description pour la rubrique licence de logiciel	200	201
176	LIC_LOG_BR	licence de logiciel et brevet	Description pour la rubrique licence de logiciel et brevet	202	203
177	LIC_SF	licence de savoir-faire	Description pour la rubrique licence de savoir-faire	204	205
178	LIC_SF_BR	licence de savoir-faire et brevet	Description pour la rubrique licence de savoir-faire et brevet	206	207
179	LIC_LOG_SF	licence logiciel et savoir faire	Description pour la rubrique licence logiciel et savoir faire	208	209
180	LIFE +	Life +	Description pour la rubrique Life +	370	371
181	MATERIEL	Label MATERIEL	Description pour la sous-catégorie MATERIEL	409	412
182	DISP.MATER	Mise à disposition de matériel	Description pour la rubrique Mise à disposition de matériel	410	411
183	N R	NON RENSEIGNE	Description pour la rubrique NON RENSEIGNE	230	231
184	TT_OPT	option sur un transfert technologique	Description pour la rubrique option sur un transfert technologique	210	211
185	EU	Partenariat europeen hors ERC	Description pour la rubrique Partenariat europeen hors ERC	232	233
186	Nat	Partenariat national	Description pour la rubrique Partenariat national	234	235
187	25_3	participation au capital social de l'entreprise	Description pour la rubrique participation au capital social de l'entreprise	212	213
188	POLE_MIC	pôle Filière Equine	Description pour la rubrique pôle Filière Equine	378	379
189	POLE_VITA	pôle Mer Bretagne	Description pour la rubrique pôle Mer Bretagne	380	381
190	POLE_PLAST	pôle TES	Description pour la rubrique pôle TES	382	383
191	POLE_VF	pôle véhicule du futur	Description pour la rubrique pôle véhicule du futur	384	385
192	PREST	Label PREST	Description pour la sous-catégorie PREST	413	424
193	PREST_AUT	prestations autres	Description pour la rubrique prestations autres	414	415
194	PRES_GGR	prestations grands groupes	Description pour la rubrique prestations grands groupes	416	417
195	PREST_GO	prestations grands org. (cea, cnes...)	Description pour la rubrique prestations grands org. (cea, cnes...)	418	419
196	PREST_PME	prestations pme	Description pour la rubrique prestations pme	420	421
197	RP_EXP_EQU	rech. part. consultance/expertise équipe	Description pour la rubrique rech. part. consultance/expertise équipe	236	237
198	RP_EXP_IND	rech. part. consultance/expertise individuelle	Description pour la rubrique rech. part. consultance/expertise individuelle	238	239
199	RP_THESE	rech. part. encadrement de thèse	Description pour la rubrique rech. part. encadrement de thèse	240	241
200	EF_RP_HUE	recherche partenariale internationale	Description pour la rubrique recherche partenariale internationale	390	391
201	TRANSF.FI	Label TRANSF.FI	Description pour la sous-catégorie TRANSF.FI	425	428
202	REVERS.SUB	Reversement subvention	Description pour la rubrique Reversement subvention	426	427
203	RTR	RTRA / RTRS	Description pour la rubrique RTRA / RTRS	242	243
204	SUBV.FONCT	Subvention de fonctionnement	Description pour la rubrique Subvention de fonctionnement	86	87
205	AU_COL_RV	Label AU_COL_RV	Description pour la sous-catégorie AU_COL_RV	429	436
206	FT_RECH_CT	subvention de fonctionnement recherche	Description pour la rubrique subvention de fonctionnement recherche	430	431
207	CR_RV	Label CR_RV	Description pour la sous-catégorie CR_RV	437	444
208	FT_RECH_CR	subvention de fonctionnement recherche	Description pour la rubrique subvention de fonctionnement recherche	438	439
209	SUB_RE_PUB	subvention de recherche publique	Description pour la rubrique subvention de recherche publique	244	245
210	SUBV.EQFCT	Subvention d'équipement et de fonctionnement	Description pour la rubrique Subvention d'équipement et de fonctionnement	88	89
211	EQ_RECH_CT	subvention d''équipement recherche	Description pour la rubrique subvention d''équipement recherche	432	433
212	EQ_RECH_CR	subvention d''équipement recherche	Description pour la rubrique subvention d''équipement recherche	440	441
213	SUB_RE_PRI	subvention d''étude publique	Description pour la rubrique subvention d''étude publique	246	247
214	SUBV.EQUIP	Subvention équipement	Description pour la rubrique Subvention équipement	90	91
215	EF_RECH_CR	subvention équipement et fonctionnement recherche	Description pour la rubrique subvention équipement et fonctionnement recherche	442	443
216	EF_RECH_CT	subvention équipement et fonctionnement recherche	Description pour la rubrique subvention équipement et fonctionnement recherche	434	435
217	COLL_RV	Label COLL_RV	Description pour la sous-catégorie COLL_RV	445	448
218	SUB_COLL_R	subventions colloques recherche	Description pour la rubrique subventions colloques recherche	446	447
219	INTER_III	tous programmes interreg iii	Description pour la rubrique tous programmes interreg iii	372	373
220	AC_UMI	unité mixte internationale	Description pour la rubrique unité mixte internationale	154	155
221	VENTE MAT.	Label VENTE MAT.	Description pour la sous-catégorie VENTE MAT.	449	452
222	VENTE MAT	Vente de matériel	Description pour la rubrique Vente de matériel	450	451
223	CONV_REVER	convention de reversement	Description pour la rubrique convention de reversement	248	249
224	PREST_CT	prestations collectivités territoriales	Description pour la rubrique prestations collectivités territoriales	422	423
225	RECH_FORM	Label RECH_FORM	Description pour la catégorie RECH_FORM	454	459
226	ACADRE_RF	Label ACADRE_RF	Description pour la sous-catégorie ACADRE_RF	455	458
227	AC_FOR_RE	accord cadre formation et recherche	Description pour la rubrique accord cadre formation et recherche	456	457
228	0000000000	Label 0000000000	Description pour la catégorie 0000000000	460	461
229	LOCAUX AME	Locaux aménagés	Description pour la rubrique Locaux aménagés	12	13
230	MAINT.LOC.	Maintenance des locaux	Description pour la rubrique Maintenance des locaux	18	19
231	MAINT.MAT	Maintenance matériel	Description pour la rubrique Maintenance matériel	20	21
\.


--
-- Name: contracttype_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('contracttype_id_seq', 231, true);


--
-- Data for Name: currency; Type: TABLE DATA; Schema: public; Owner: -
--

COPY currency (id, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id, label, symbol, rate) FROM stdin;
1	1	2015-11-03 14:48:10	\N	\N	\N	\N	\N	Euro	€	1
4	1	2015-11-03 14:58:31	\N	\N	\N	\N	\N	Yens	¥	132.65100000000001
3	1	2015-11-03 14:57:20	\N	\N	\N	\N	\N	Livre	£	0.713300000000000045
2	1	2015-11-03 14:56:38	\N	\N	\N	\N	\N	Dollars	$	1.09600000000000009
\.


--
-- Name: currency_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('currency_id_seq', 1, false);


--
-- Data for Name: datetype; Type: TABLE DATA; Schema: public; Owner: -
--

COPY datetype (id, label, description, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id, facet, recursivity) FROM stdin;
1	Début du contrat		1	2016-01-27 14:20:48	\N	\N	\N	\N	\N	\N	\N
3	Début d'éligibilité des dépenses		1	2016-01-27 14:26:21	\N	\N	\N	\N	\N	\N	\N
4	Fin d'éligibilité des dépenses		1	2016-01-27 14:31:13	\N	\N	\N	\N	\N	\N	\N
5	Début d'éligibilité des dépenses de fonctionnement		1	2016-01-27 14:48:23	\N	\N	\N	\N	\N	\N	\N
6	Fin d'éligibilité des dépenses de fonctionnement		1	2016-01-27 14:48:46	\N	\N	\N	\N	\N	\N	\N
7	Dépôt de dossier		1	2016-01-27 14:49:01	\N	\N	\N	\N	\N	\N	\N
8	Signature		1	2016-01-27 14:49:14	\N	\N	\N	\N	\N	\N	\N
9	Première dépense	Déclenche la demande de l'avance (certificat de commencement du projet)	1	2016-01-27 14:49:42	\N	\N	\N	\N	\N	\N	\N
10	Démo		1	2016-02-03 18:11:45	\N	\N	\N	\N	\N	\N	\N
12	Rapport de thèse		1	2016-02-08 12:54:00	\N	\N	\N	\N	\N	Scientifique	\N
11	Publication d'article		1	2016-02-04 09:34:18	\N	\N	\N	\N	\N	Scientifique	\N
15	Rapport d'étude		1	2016-02-08 13:23:55	\N	\N	\N	\N	\N	Scientifique	\N
16	Prototype		1	2016-02-08 13:26:10	\N	\N	\N	\N	\N	Scientifique	\N
17	Logiciel		1	2016-02-08 13:29:37	\N	\N	\N	\N	\N	Scientifique	\N
18	Rapport de recherche		1	2016-02-08 13:30:10	\N	\N	\N	\N	\N	Scientifique	\N
19	Rapport final		1	2016-02-08 13:30:42	\N	\N	\N	\N	\N	Scientifique	\N
20	Rapport scientifique intermédiaire		1	2016-02-08 13:31:20	\N	\N	\N	\N	\N	Scientifique	\N
21	Soutenance de thèse		1	2016-02-08 13:31:40	\N	\N	\N	\N	\N	Scientifique	\N
52	Date de fin d'éligibilité des dépenses d'investissement		1	2016-04-07 12:58:56	\N	\N	\N	\N	\N	Financier	\N
53	Rapport financier		1	2016-08-26 13:53:40	\N	\N	\N	\N	\N	Financier	\N
54	Fin de période de rapport/reporting		1	2016-08-26 13:54:00	\N	\N	\N	\N	\N	Général	\N
\.


--
-- Name: datetype_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('datetype_id_seq', 54, true);


--
-- Data for Name: discipline; Type: TABLE DATA; Schema: public; Owner: -
--

COPY discipline (id, label, centaureid) FROM stdin;
91	CONSTITUANTS ELEMENTAIRES, PHYSIQUE THEORIQUE	GMC2
92	PHYSIQUE ATOMIQUE ET MOLECULAIRE, OPTIQUE, LASERS, ELECTROMAGNETISME	GMC3
93	MATIERE CONDENSEE, MATERIAUX, COMPOSANTS	GMC4
94	AUTOMATIQUE, PRODUCTIQUE	GMC5
95	BIOPHYSIQUE	GMC6
96	ASTRONOMIE	GMC7
97	SCIENCES DE LA TERRE	GMC8
98	CHIMIE	GMC9
99	BIOLOGIE MOLECULAIRE ET CELLULAIRE	GMD1
100	GENOME	GMD2
101	DEVELOPPEMENT, IMMUNOLOGIE, METABOLISME	GMD3
102	BIOLOGIE COGNITIQUE	GMD4
103	MEDECINE, CLINIQUE, GBM	GMD5
104	PHARMACIE	GMD6
105	LETTRES	GMD7
106	SCIENCES HUMAINES	GMD8
107	SCIENCES DE L'ESPACE ET DU TEMPS	GMD9
108	SCIENCES DE L'EDUCATION	GME1
109	ECONOMIE ET GESTION	GME2
110	DROIT ET SCIENCES POLITIQUES	GME3
111	SOCIOLOGIE	GME4
112	MÉCANIQUE	GME5
113	ENERGETIQUE ET PROCEDES	GME6
114	INFORMATIQUE	GME7
115	ELECTRONIQUE, OPTRONIQUE	GME8
116	ELECTROTECHNIQUE ET IMAGE	GME9
117	BIOELECTRONIQUE, BIOPUCES	GMF1
118	SCIENCES DE LA VIE	GMF2
119	MATHEMATIQUE	GMC1
\.


--
-- Name: discipline_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('discipline_id_seq', 122, true);


--
-- Data for Name: grantsource; Type: TABLE DATA; Schema: public; Owner: -
--

COPY grantsource (id, description, logo, informations, centaureid, label) FROM stdin;
1		\N	\N		
2	ANR	\N	\N	ANR_SP	ANR_SP
3	UCBN PCRD (Plan de Calcul)	\N	\N	UCBN PCRD	UCBN PCRD
4	coût complet (Plan de calcul)	\N	\N	UCBN_1	UCBN_1
5	Coût marginal (Plan de Calcul)	\N	\N	UCBN_2	UCBN_2
6	NON RENSEIGNE	\N	\N	0000000000	0000000000
7	Accords Cadre	\N	\N	ACADRE	ACADRE
8	BQR	\N	\N	BQR	BQR
9	Pôles de compétitivités (FUI)	\N	\N	COMPET	COMPET
10	CPER	\N	\N	CPER	CPER
11	Subvention Conseil Régional	\N	\N	CR	CR
12	International hors Europe	\N	\N	INTER	INTER
13	NON APPLICABLE	\N	\N	NA	NA
14	Non défini	\N	\N	ND	ND
15	Aide à l'innovation OSEO	\N	\N	OSEO	OSEO
16	Contrat de prestations	\N	\N	PREST	PREST
17	contrat de collaboration de recherche lucratif	\N	\N	RALNT	RALNT
18	contrat de collaboration de recherche non lucratif	\N	\N	RANLNT	RANLNT
19	Convention de Subvention	\N	\N	RANLT	RANLT
20	Union Européenne	\N	\N	UE	UE
21	Accord de confidentialité	\N	\N	CONFIDENC	CONFIDENC
22	Contrat de mise à disposition	\N	\N	MAD	MAD
23	Accord de copropriété	\N	\N	COPRO	COPRO
24	Contrat de mandat	\N	\N	mandat	mandat
25	Contrat de licence	\N	\N	LICENC	LICENC
26	Contrat de cession	\N	\N	CESSION	CESSION
\.


--
-- Name: grantsource_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('grantsource_id_seq', 33, true);


--
-- Data for Name: logactivity; Type: TABLE DATA; Schema: public; Owner: -
--

COPY logactivity (id, datecreated, message, context, contextid, userid, level, type, ip, datas) FROM stdin;
\.


--
-- Name: logactivity_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('logactivity_id_seq', 29164, true);


--
-- Data for Name: notification; Type: TABLE DATA; Schema: public; Owner: -
--

COPY notification (id, dateeffective, datereal, datecreated, message, object, objectid, hash, context, serie, level, datas) FROM stdin;
\.


--
-- Name: notification_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('notification_id_seq', 1, false);


--
-- Data for Name: notificationperson; Type: TABLE DATA; Schema: public; Owner: -
--

COPY notificationperson (id, notification_id, person_id, read) FROM stdin;
\.


--
-- Name: notificationperson_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('notificationperson_id_seq', 1, false);


--
-- Data for Name: organization; Type: TABLE DATA; Schema: public; Owner: -
--

COPY organization (id, centaureid, shortname, fullname, code, email, url, description, street1, street2, street3, city, zipcode, phone, dateupdated, datecreated, dateend, datestart, status, datedeleted, createdby_id, updatedby_id, deletedby_id, ldapsupanncodeentite, country, sifacid, codepays, siret, bp, type, sifacgroup, sifacgroupid, numtvaca, connectors) FROM stdin;
\.


--
-- Name: organization_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('organization_id_seq', 12932, true);


--
-- Data for Name: organization_role; Type: TABLE DATA; Schema: public; Owner: -
--

COPY organization_role (id, role_id, description, principal) FROM stdin;
\.


--
-- Name: organization_role_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('organization_role_id_seq', 1, false);


--
-- Data for Name: organizationperson; Type: TABLE DATA; Schema: public; Owner: -
--

COPY organizationperson (id, person_id, organization_id, main, role, datestart, dateend, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id, roleobj_id) FROM stdin;
\.


--
-- Name: organizationperson_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('organizationperson_id_seq', 181, true);


--
-- Data for Name: organizationrole; Type: TABLE DATA; Schema: public; Owner: -
--

COPY organizationrole (id, label, description, principal, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id) FROM stdin;
5	Co-financeur	\N	f	\N	\N	\N	\N	\N	\N	\N
6	Coordinateur	\N	f	\N	\N	\N	\N	\N	\N	\N
8	Client	\N	f	\N	\N	\N	\N	\N	\N	\N
2	Composante de gestion	\N	t	\N	\N	\N	\N	\N	\N	\N
1	Laboratoire	\N	t	\N	\N	\N	\N	\N	\N	\N
3	Financeur	\N	t	\N	\N	\N	\N	\N	\N	\N
4	Composante responsable	\N	t	\N	\N	\N	\N	\N	\N	\N
9	Co-contractant	\N	f	\N	\N	\N	\N	\N	\N	\N
10	Tutelle de gestion	\N	t	\N	\N	\N	\N	\N	\N	\N
12	Conseiller	\N	f	\N	\N	\N	\N	\N	\N	\N
13	Partenaire	\N	f	\N	\N	\N	\N	\N	\N	\N
7	Scientifique		f	\N	\N	\N	\N	\N	\N	\N
\.


--
-- Name: organizationrole_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('organizationrole_id_seq', 1, false);


--
-- Data for Name: person; Type: TABLE DATA; Schema: public; Owner: -
--

COPY person (id, firstname, lastname, codeharpege, centaureid, codeldap, email, ldapstatus, ldapsitelocation, ldapaffectation, ldapdisabled, ldapfininscription, ladaplogin, phone, datesyncldap, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id, emailprive, harpegeinm, connectors, ldapmemberof) FROM stdin;
\.


--
-- Name: person_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('person_id_seq', 11691, true);


--
-- Data for Name: privilege; Type: TABLE DATA; Schema: public; Owner: -
--

COPY privilege (id, categorie_id, code, libelle, ordre, root_id, spot) FROM stdin;
6	7	privilege-visualisation	Privilèges - Visualisation	\N	\N	7
18	2	SHOW	Afficher la fiche d'une activité	\N	\N	7
20	2	PAYMENT_SHOW	Voir les versements et le budget	\N	\N	7
22	2	MILESTONE_SHOW	Peut voir les jalons	\N	\N	7
24	2	DOCUMENT_SHOW	Peut voir les documents	\N	\N	7
30	2	PERSON_SHOW	Peut voir les membres d'une activité	\N	\N	7
31	2	ORGANIZATION_SHOW	Peut voir les partenaires d'un projet	\N	\N	7
3	1	SHOW	Voir les détails d'un projet	\N	\N	7
32	1	PERSON_SHOW	Voir les membres d'un projet	\N	\N	7
33	1	ORGANIZATION_SHOW	Voir les partenaires d'un projet	\N	\N	7
34	1	DOCUMENT_SHOW	Voir les documents d'un projet	\N	\N	7
35	1	ACTIVITY_SHOW	Voir les activités d'un projet	\N	\N	7
54	2	WORKPACKAGE_SHOW	Voir les lots de travail d'une activité	\N	\N	7
56	2	WORKPACKAGE_COMMIT	Déclarer des heures pour un lot de travail	\N	\N	7
62	9	SHOW	Voir les dépenses	\N	\N	7
67	3	VIEW_TIMESHEET	Peut voir les feuilles de temps de n'importe quelle personne	\N	\N	7
68	2	TIMESHEET_VIEW	Voir les feuilles de temps	\N	\N	7
71	2	TIMESHEET_USURPATION	Peut remplir les feuilles de temps des déclarants d'une activité	\N	\N	7
72	2	NOTIFICATIONS_SHOW	Peut voir les notifications planifiées dans la fiche activité	\N	\N	7
74	2	PERSON_ACCESS	Voir les personnes qui ont la vision sur l'activité	\N	\N	7
75	6	CONNECTOR_ACCESS	Peut exécuter la synchronisation des données	\N	\N	7
76	6	NOTIFICATION_PERSON	Peut notifier manuellement un personne	\N	\N	7
4	7	role-visualisation	Visualisation des rôles	\N	\N	4
5	7	role-edition	Édition des rôles	\N	4	4
7	7	privilege-edition	Privilèges - Édition	\N	6	4
8	1	CREATE	Création d'un nouveau projet	\N	\N	4
9	1	EDIT	Modifier un projet	\N	3	4
10	1	ACTIVITY-ADD	Ajouter une activité dans le projet	\N	\N	4
11	1	PERSON_MANAGE	Gérer les membres d'un projet	\N	32	7
12	1	ORGANIZATION_MANAGE	Gérer les partenaires d'un projet	\N	33	7
1	1	DASHBOARD	Tableau de bord	\N	\N	4
2	1	INDEX	Lister et recherche dans les projets	\N	\N	6
13	2	EXPORT	Exporter les données des activités	\N	17	4
16	2	PAYMENT_MANAGE	Gestion des versements d'une activités	\N	20	7
15	2	ORGANIZATION_MANAGE	Gestion des partenaires d'une activité	\N	31	7
19	2	EDIT	Modifier les informations générales d'une activité	\N	18	7
17	2	INDEX	Afficher / rechercher dans les activités	\N	\N	4
23	2	MILESTONE_MANAGE	Peut gérer les jalons	\N	22	7
25	2	DOCUMENT_MANAGE	Peut gérer les documents (Ajouter)	\N	24	7
26	2	DUPLICATE	Peut dupliquer l'activité	\N	\N	3
27	2	CHANGE_PROJECT	Peut modifier le projet d'une activité	\N	\N	4
28	2	DELETE	Peut supprimer définitivement une activité	\N	\N	4
29	2	STATUS_OFF	Peut modifier le status vers "Désactivé"	\N	\N	4
36	3	SHOW	Voir la fiche d'une personne	\N	\N	4
37	3	EDIT	Modifier la fiche d'une personne	\N	36	4
38	3	SYNC_LDAP	Synchroniser les données avec LDAP	\N	36	4
41	4	SHOW	Voir la fiche d'une organisation	\N	\N	4
42	4	EDIT	Modifier la fiche d'une organisation	\N	41	4
43	4	SYNC_LDAP	Synchroniser les données avec LDAP	\N	41	4
14	2	PERSON_MANAGE	Gestion des membres d'une activité	\N	30	7
51	6	MENU_ADMIN	Accès aux menu d'administration	\N	\N	4
40	4	INDEX	Voir la liste des organisations	\N	\N	4
39	3	INDEX	Voir la liste des personnes	\N	\N	4
53	3	PROJECTS	Voir les projets d'une personnes	\N	36	7
52	3	INFOS_RH	Voir les données administratives	\N	36	4
55	2	WORKPACKAGE_MANAGE	Gérer les lots de travail d'une activité	\N	54	7
58	8	DOCUMENT_INDEX	Voir les documents adminstratifs	\N	\N	4
60	8	DOCUMENT_DELETE	Supprimer un document	\N	58	4
61	8	DOCUMENT_DOWNLOAD	Télécharger un document	\N	58	4
59	8	DOCUMENT_NEW	Téléverser un nouveau document	\N	58	4
63	7	USER_VISUALISATION	Voir les authentifications utilisateur	\N	\N	4
64	7	USER_EDITION	Gérer les authentifications des utilisateurs	\N	\N	4
65	7	ROLEORGA_VISUALISATION	Voir les rôles des organisations	\N	\N	4
66	7	ROLEORGA_EDITION	Gérer les rôles des organisations	\N	65	4
69	2	TIMESHEET_VALIDATE_SCI	Validation scientifique des feuilles de temps	\N	68	7
70	2	TIMESHEET_VALIDATE_ADM	Validation administrative des feuilles de temps	\N	68	7
73	2	NOTIFICATIONS_GENERATE	Peut regénérer manuellement les notifications d'une activité	\N	72	7
\.


--
-- Name: privilege_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('privilege_id_seq', 77, false);


--
-- Data for Name: project; Type: TABLE DATA; Schema: public; Owner: -
--

COPY project (id, discipline_id, centaureid, code, eotp, composanteprincipal, acronym, label, description, datecreated, dateupdated, datevalidated) FROM stdin;
\.


--
-- Data for Name: project_discipline; Type: TABLE DATA; Schema: public; Owner: -
--

COPY project_discipline (project_id, discipline_id) FROM stdin;
\.


--
-- Name: project_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('project_id_seq', 8634, true);


--
-- Name: projectgrant_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('projectgrant_id_seq', 8654, true);


--
-- Data for Name: projectmember; Type: TABLE DATA; Schema: public; Owner: -
--

COPY projectmember (id, project_id, person_id, role, datestart, dateend, main, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id, roleobj_id) FROM stdin;
\.


--
-- Name: projectmember_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('projectmember_id_seq', 10714, true);


--
-- Data for Name: projectpartner; Type: TABLE DATA; Schema: public; Owner: -
--

COPY projectpartner (id, project_id, organization_id, datestart, dateend, main, role, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id, roleobj_id) FROM stdin;
\.


--
-- Name: projectpartner_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('projectpartner_id_seq', 59703, true);


--
-- Name: role_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('role_id_seq', 1, false);


--
-- Data for Name: role_privilege; Type: TABLE DATA; Schema: public; Owner: -
--

COPY role_privilege (privilege_id, role_id) FROM stdin;
3	6
33	6
3	10
33	10
35	10
20	10
24	10
31	10
25	10
54	1
53	9
2	9
32	9
34	9
13	9
18	9
22	9
30	9
36	9
40	9
1	15
3	15
33	15
35	15
17	15
20	15
24	15
31	15
36	15
41	15
51	15
55	7
6	16
2	16
32	16
34	16
13	16
18	16
22	16
30	16
54	16
36	16
41	16
51	16
1	11
32	11
3	13
3	12
32	13
35	11
18	11
18	12
30	11
54	12
54	13
56	12
56	13
56	8
59	1
58	16
51	9
59	9
58	10
58	15
58	13
61	7
1	17
32	17
18	17
54	17
58	17
3	18
34	18
18	18
22	18
30	18
54	18
61	18
61	10
61	15
61	13
58	6
16	9
54	9
63	1
65	1
53	7
20	23
13	23
22	23
30	23
54	23
53	23
41	23
61	23
58	23
1	23
32	23
34	23
13	24
18	24
22	24
30	24
54	24
36	24
53	24
52	24
40	24
61	24
61	21
40	21
52	21
39	21
54	21
30	21
22	21
18	21
13	21
34	24
32	24
35	21
33	21
3	21
1	24
4	1
5	1
6	1
7	1
8	7
8	1
9	1
9	7
17	22
10	1
10	7
20	22
11	1
11	7
24	22
12	1
12	7
31	22
1	1
1	7
1	6
1	14
2	1
2	7
35	22
13	7
13	1
16	1
16	14
16	7
15	1
15	7
15	14
19	1
19	7
19	14
17	1
17	7
33	22
18	1
18	7
18	14
20	1
20	7
20	14
22	1
22	7
22	14
23	1
23	7
3	22
24	1
24	7
24	14
25	1
25	7
25	14
26	1
26	7
36	22
27	7
27	1
28	1
53	22
41	22
58	22
29	1
30	1
30	7
30	14
31	1
31	7
31	14
3	1
3	7
3	14
32	14
32	7
32	1
33	14
33	7
33	1
34	14
34	7
34	1
35	14
35	7
35	1
36	7
36	1
37	7
37	1
38	1
41	7
41	1
42	7
42	1
43	7
43	1
14	1
14	7
51	7
51	1
40	7
40	1
39	7
39	1
53	1
61	9
32	6
1	10
32	10
34	10
18	10
22	10
30	10
54	10
55	1
56	1
56	10
1	9
3	9
33	9
35	9
17	9
20	9
24	9
31	9
39	9
41	9
2	15
32	15
34	15
13	15
18	15
22	15
30	15
54	15
39	15
40	15
54	7
56	7
4	16
1	16
3	16
33	16
35	16
17	16
20	16
24	16
31	16
56	16
39	16
40	16
3	11
1	13
1	12
32	12
35	12
35	13
18	13
30	13
30	12
54	11
54	14
56	11
56	14
56	15
58	1
60	1
58	9
61	1
58	7
58	8
58	14
58	11
58	12
59	7
3	17
35	17
30	17
56	17
1	18
32	18
35	18
20	18
24	18
31	18
56	18
58	18
61	16
61	8
61	14
61	11
61	17
23	9
64	1
66	1
18	23
17	23
24	23
31	23
39	23
40	23
3	23
33	23
35	23
17	24
20	24
24	24
31	24
36	23
39	24
52	23
41	24
58	24
58	21
41	21
53	21
36	21
31	21
24	21
20	21
17	21
35	24
33	24
3	24
34	21
32	21
1	21
13	22
18	22
22	22
30	22
54	22
34	22
32	22
1	22
39	22
52	22
40	22
61	22
\.


--
-- Data for Name: timesheet; Type: TABLE DATA; Schema: public; Owner: -
--

COPY timesheet (id, workpackage_id, person_id, datefrom, dateto, comment, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id, activity_id, label, icsuid, icsfileuid, icsfilename, icsfiledateadded, validatedsciby, validatedscibyid, validatedsciat, validatedadminby, validatedadminbyid, validatedadminat, rejectedsciby, rejectedscibyid, rejectedsciat, rejectedscicomment, rejectedadminby, rejectedadminbyid, rejectedadminat, rejectedadmincomment, sendby) FROM stdin;
\.


--
-- Name: timesheet_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('timesheet_id_seq', 11, true);


--
-- Data for Name: tva; Type: TABLE DATA; Schema: public; Owner: -
--

COPY tva (id, label, rate, active, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id) FROM stdin;
1	Exonéré	0	t	1	\N	\N	\N	\N	\N	\N
2	Taux réduit (5,5%)	5.5	t	1	\N	\N	\N	\N	\N	\N
3	Taux normal (19,6%)	19.6000000000000014	t	1	\N	\N	\N	\N	\N	\N
4	Taux DOM-TOM	8.5	t	1	\N	\N	\N	\N	\N	\N
5	Taux réduit 7%	7	t	1	\N	\N	\N	\N	\N	\N
6	Taux normal 20%	20	t	1	\N	\N	\N	\N	\N	\N
7	Taux réduit 10%	10	t	1	\N	\N	\N	\N	\N	\N
\.


--
-- Name: tva_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('tva_id_seq', 1, false);


--
-- Data for Name: typedocument; Type: TABLE DATA; Schema: public; Owner: -
--

COPY typedocument (id, label, description, codecentaure, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id) FROM stdin;
1	Bordereau d'envoi	Importé depuis centaure	BORD	1	2015-12-03 14:36:30	\N	\N	\N	\N	\N
2	Fiche d'analyse	Importé depuis centaure	ANA	1	2015-12-03 14:36:30	\N	\N	\N	\N	\N
3	Document de travail	Importé depuis centaure	DOC	1	2015-12-03 14:36:30	\N	\N	\N	\N	\N
4	Annexe	Importé depuis centaure	ANN	1	2015-12-03 14:36:30	\N	\N	\N	\N	\N
5	Draft	Importé depuis centaure	DRAFT	1	2015-12-03 14:36:30	\N	\N	\N	\N	\N
6	Contrat Version Définitive Signée	Importé depuis centaure	VDEF	1	2015-12-03 14:36:30	\N	\N	\N	\N	\N
7	Annexe budgétaire lors de l'ouverture du contrat	Importé depuis centaure	ANN_BUDGET	1	2015-12-03 14:36:30	\N	\N	\N	\N	\N
8	Pièces attachées aux emails	Importé depuis centaure	PJ_MAIL	1	2015-12-03 14:36:30	\N	\N	\N	\N	\N
9	Version de contrat	Type générique	VERSIONX	1	2015-12-03 14:59:55	\N	\N	\N	\N	\N
10	Fiche mouvement Contractuel	\N	\N	1	2016-05-18 11:06:54	\N	\N	\N	\N	\N
11	Email ou courrier	\N	\N	1	2016-12-15 15:20:00	\N	\N	\N	\N	\N
\.


--
-- Name: typedocument_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('typedocument_id_seq', 41, true);


--
-- Data for Name: user_role; Type: TABLE DATA; Schema: public; Owner: -
--

COPY user_role (id, parent_id, role_id, is_default, ldap_filter, spot, description, principal) FROM stdin;
16	\N	Superviseur	f	\N	4	\N	f
5	\N	valo	f	\N	0	\N	f
6	\N	Utilisateur	t	(memberOf=cn=harpege,ou=groups,dc=unicaen,dc=fr)	4	\N	f
1	\N	Administrateur	f	\N	4	\N	f
8	\N	Responsable RH	f	\N	6	\N	f
9	\N	Responsable financier	f	(memberOf=cn=projet_oscar_agence_comptable,ou=groups,dc=unicaen,dc=fr)	6	\N	f
11	\N	Ingénieur	f	\N	1	\N	f
12	\N	Doctorant	f	\N	3	\N	f
13	\N	Post-doc	f	\N	3	\N	f
14	\N	Responsable	f	\N	3	\N	f
15	\N	Responsable juridique	f	\N	6	\N	f
17	\N	Chercheur	f	\N	3	\N	f
18	\N	Co-responsable	f	\N	3	\N	f
19	\N	Gestionnaire	f	\N	2	\N	f
2	\N	beta_testeur	f	\N	0	\N	f
20	\N	Chargé de mission Europe	f	\N	3		t
10	\N	Responsable scientifique	f	\N	3		t
22	\N	Directeur de composante	f	\N	2	Contient les directeurs de composantes, directeurs de composantes adjoint, les administrateurs provisoires 	t
21	\N	Directeur de laboratoire	f	\N	2	Contient la liste des directeurs de laboratoires et assimilés (directeurs adjoints, directeurs temporaire, etc.)	t
24	\N	Gestionnaire recherche de laboratoire	f	\N	2		t
23	\N	Responsable administratif et gestionnaire de composante	f	\N	3	Les responsables administratifs et gestionnaires de composantes 	t
7	\N	Chargé de valorisation	f	(memberOf=cn=structure_dir-recherche-innov,ou=groups,dc=unicaen,dc=fr)	7		t
\.


--
-- Name: user_role_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('user_role_id_seq', 24, true);


--
-- Data for Name: useraccessdefinition; Type: TABLE DATA; Schema: public; Owner: -
--

COPY useraccessdefinition (id, context, label, description, key) FROM stdin;
\.


--
-- Name: useraccessdefinition_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('useraccessdefinition_id_seq', 1, false);


--
-- Data for Name: workpackage; Type: TABLE DATA; Schema: public; Owner: -
--

COPY workpackage (id, activity_id, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id, code, label, description, datestart, dateend) FROM stdin;
\.


--
-- Name: workpackage_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('workpackage_id_seq', 22, true);


--
-- Data for Name: workpackageperson; Type: TABLE DATA; Schema: public; Owner: -
--

COPY workpackageperson (id, person_id, duration, status, datecreated, dateupdated, datedeleted, workpackage_id, createdby_id, updatedby_id, deletedby_id) FROM stdin;
\.


--
-- Name: workpackageperson_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('workpackageperson_id_seq', 1, true);


--
-- Name: activity_discipline_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY activity_discipline
    ADD CONSTRAINT activity_discipline_pkey PRIMARY KEY (activity_id, discipline_id);


--
-- Name: activity_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY activity
    ADD CONSTRAINT activity_pkey PRIMARY KEY (id);


--
-- Name: activitydate_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY activitydate
    ADD CONSTRAINT activitydate_pkey PRIMARY KEY (id);


--
-- Name: activityorganization_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY activityorganization
    ADD CONSTRAINT activityorganization_pkey PRIMARY KEY (id);


--
-- Name: activitypayment_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY activitypayment
    ADD CONSTRAINT activitypayment_pkey PRIMARY KEY (id);


--
-- Name: activityperson_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY activityperson
    ADD CONSTRAINT activityperson_pkey PRIMARY KEY (id);


--
-- Name: activitytype_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY activitytype
    ADD CONSTRAINT activitytype_pkey PRIMARY KEY (id);


--
-- Name: administrativedocument_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY administrativedocument
    ADD CONSTRAINT administrativedocument_pkey PRIMARY KEY (id);


--
-- Name: authentification_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY authentification
    ADD CONSTRAINT authentification_pkey PRIMARY KEY (id);


--
-- Name: authentification_role_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY authentification_role
    ADD CONSTRAINT authentification_role_pkey PRIMARY KEY (authentification_id, role_id);


--
-- Name: categorie_privilege_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY categorie_privilege
    ADD CONSTRAINT categorie_privilege_pkey PRIMARY KEY (id);


--
-- Name: contractdocument_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY contractdocument
    ADD CONSTRAINT contractdocument_pkey PRIMARY KEY (id);


--
-- Name: contracttype_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY contracttype
    ADD CONSTRAINT contracttype_pkey PRIMARY KEY (id);


--
-- Name: currency_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY currency
    ADD CONSTRAINT currency_pkey PRIMARY KEY (id);


--
-- Name: datetype_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY datetype
    ADD CONSTRAINT datetype_pkey PRIMARY KEY (id);


--
-- Name: discipline_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY discipline
    ADD CONSTRAINT discipline_pkey PRIMARY KEY (id);


--
-- Name: grantsource_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY grantsource
    ADD CONSTRAINT grantsource_pkey PRIMARY KEY (id);


--
-- Name: logactivity_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY logactivity
    ADD CONSTRAINT logactivity_pkey PRIMARY KEY (id);


--
-- Name: notification_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY notification
    ADD CONSTRAINT notification_pkey PRIMARY KEY (id);


--
-- Name: notificationperson_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY notificationperson
    ADD CONSTRAINT notificationperson_pkey PRIMARY KEY (id);


--
-- Name: organization_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY organization
    ADD CONSTRAINT organization_pkey PRIMARY KEY (id);


--
-- Name: organization_role_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY organization_role
    ADD CONSTRAINT organization_role_pkey PRIMARY KEY (id);


--
-- Name: organizationperson_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY organizationperson
    ADD CONSTRAINT organizationperson_pkey PRIMARY KEY (id);


--
-- Name: organizationrole_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY organizationrole
    ADD CONSTRAINT organizationrole_pkey PRIMARY KEY (id);


--
-- Name: person_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY person
    ADD CONSTRAINT person_pkey PRIMARY KEY (id);


--
-- Name: privilege_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY privilege
    ADD CONSTRAINT privilege_pkey PRIMARY KEY (id);


--
-- Name: project_discipline_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY project_discipline
    ADD CONSTRAINT project_discipline_pkey PRIMARY KEY (project_id, discipline_id);


--
-- Name: project_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY project
    ADD CONSTRAINT project_pkey PRIMARY KEY (id);


--
-- Name: projectmember_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY projectmember
    ADD CONSTRAINT projectmember_pkey PRIMARY KEY (id);


--
-- Name: projectpartner_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY projectpartner
    ADD CONSTRAINT projectpartner_pkey PRIMARY KEY (id);


--
-- Name: role_privilege_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY role_privilege
    ADD CONSTRAINT role_privilege_pkey PRIMARY KEY (privilege_id, role_id);


--
-- Name: timesheet_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY timesheet
    ADD CONSTRAINT timesheet_pkey PRIMARY KEY (id);


--
-- Name: tva_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY tva
    ADD CONSTRAINT tva_pkey PRIMARY KEY (id);


--
-- Name: typedocument_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY typedocument
    ADD CONSTRAINT typedocument_pkey PRIMARY KEY (id);


--
-- Name: user_role_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY user_role
    ADD CONSTRAINT user_role_pkey PRIMARY KEY (id);


--
-- Name: useraccessdefinition_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY useraccessdefinition
    ADD CONSTRAINT useraccessdefinition_pkey PRIMARY KEY (id);


--
-- Name: workpackage_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY workpackage
    ADD CONSTRAINT workpackage_pkey PRIMARY KEY (id);


--
-- Name: workpackageperson_pkey; Type: CONSTRAINT; Schema: public; Owner: -; Tablespace: 
--

ALTER TABLE ONLY workpackageperson
    ADD CONSTRAINT workpackageperson_pkey PRIMARY KEY (id);


--
-- Name: idx_205cd03781c06096; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_205cd03781c06096 ON activity_discipline USING btree (activity_id);


--
-- Name: idx_205cd037a5522701; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_205cd037a5522701 ON activity_discipline USING btree (discipline_id);


--
-- Name: idx_22ba6515217bbb47; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_22ba6515217bbb47 ON notificationperson USING btree (person_id);


--
-- Name: idx_22ba6515ef1a9d84; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_22ba6515ef1a9d84 ON notificationperson USING btree (notification_id);


--
-- Name: idx_29fdc4ce3174800f; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_29fdc4ce3174800f ON datetype USING btree (createdby_id);


--
-- Name: idx_29fdc4ce63d8c20e; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_29fdc4ce63d8c20e ON datetype USING btree (deletedby_id);


--
-- Name: idx_29fdc4ce65ff1aec; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_29fdc4ce65ff1aec ON datetype USING btree (updatedby_id);


--
-- Name: idx_2dcfc4c43174800f; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_2dcfc4c43174800f ON activitydate USING btree (createdby_id);


--
-- Name: idx_2dcfc4c463d8c20e; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_2dcfc4c463d8c20e ON activitydate USING btree (deletedby_id);


--
-- Name: idx_2dcfc4c465ff1aec; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_2dcfc4c465ff1aec ON activitydate USING btree (updatedby_id);


--
-- Name: idx_2dcfc4c481c06096; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_2dcfc4c481c06096 ON activitydate USING btree (activity_id);


--
-- Name: idx_2dcfc4c4c54c8c93; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_2dcfc4c4c54c8c93 ON activitydate USING btree (type_id);


--
-- Name: idx_2de8c6a3727aca70; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_2de8c6a3727aca70 ON user_role USING btree (parent_id);


--
-- Name: idx_3370d4403174800f; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_3370d4403174800f ON person USING btree (createdby_id);


--
-- Name: idx_3370d44063d8c20e; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_3370d44063d8c20e ON person USING btree (deletedby_id);


--
-- Name: idx_3370d44065ff1aec; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_3370d44065ff1aec ON person USING btree (updatedby_id);


--
-- Name: idx_34944573217bbb47; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_34944573217bbb47 ON timesheet USING btree (person_id);


--
-- Name: idx_349445733174800f; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_349445733174800f ON timesheet USING btree (createdby_id);


--
-- Name: idx_3494457363d8c20e; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_3494457363d8c20e ON timesheet USING btree (deletedby_id);


--
-- Name: idx_3494457365ff1aec; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_3494457365ff1aec ON timesheet USING btree (updatedby_id);


--
-- Name: idx_3494457381c06096; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_3494457381c06096 ON timesheet USING btree (activity_id);


--
-- Name: idx_34944573dbd8a2b7; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_34944573dbd8a2b7 ON timesheet USING btree (workpackage_id);


--
-- Name: idx_4a390fe8217bbb47; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_4a390fe8217bbb47 ON contractdocument USING btree (person_id);


--
-- Name: idx_4a390fe83bebd1bd; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_4a390fe83bebd1bd ON contractdocument USING btree (typedocument_id);


--
-- Name: idx_4a390fe85c0c89f3; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_4a390fe85c0c89f3 ON contractdocument USING btree (grant_id);


--
-- Name: idx_55026b0c166d1f9c; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_55026b0c166d1f9c ON activity USING btree (project_id);


--
-- Name: idx_55026b0c3174800f; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_55026b0c3174800f ON activity USING btree (createdby_id);


--
-- Name: idx_55026b0c38248176; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_55026b0c38248176 ON activity USING btree (currency_id);


--
-- Name: idx_55026b0c4d79775f; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_55026b0c4d79775f ON activity USING btree (tva_id);


--
-- Name: idx_55026b0c63d8c20e; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_55026b0c63d8c20e ON activity USING btree (deletedby_id);


--
-- Name: idx_55026b0c65ff1aec; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_55026b0c65ff1aec ON activity USING btree (updatedby_id);


--
-- Name: idx_55026b0c953c1c61; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_55026b0c953c1c61 ON activity USING btree (source_id);


--
-- Name: idx_55026b0ca1b4b28c; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_55026b0ca1b4b28c ON activity USING btree (activitytype_id);


--
-- Name: idx_55026b0cc54c8c93; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_55026b0cc54c8c93 ON activity USING btree (type_id);


--
-- Name: idx_5d5b51b9166d1f9c; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_5d5b51b9166d1f9c ON projectmember USING btree (project_id);


--
-- Name: idx_5d5b51b91c4132c1; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_5d5b51b91c4132c1 ON projectmember USING btree (roleobj_id);


--
-- Name: idx_5d5b51b9217bbb47; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_5d5b51b9217bbb47 ON projectmember USING btree (person_id);


--
-- Name: idx_5d5b51b93174800f; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_5d5b51b93174800f ON projectmember USING btree (createdby_id);


--
-- Name: idx_5d5b51b963d8c20e; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_5d5b51b963d8c20e ON projectmember USING btree (deletedby_id);


--
-- Name: idx_5d5b51b965ff1aec; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_5d5b51b965ff1aec ON projectmember USING btree (updatedby_id);


--
-- Name: idx_5dbdaf56d28043b; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_5dbdaf56d28043b ON authentification_role USING btree (authentification_id);


--
-- Name: idx_5dbdaf5d60322ac; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_5dbdaf5d60322ac ON authentification_role USING btree (role_id);


--
-- Name: idx_6547bd503174800f; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_6547bd503174800f ON typedocument USING btree (createdby_id);


--
-- Name: idx_6547bd5063d8c20e; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_6547bd5063d8c20e ON typedocument USING btree (deletedby_id);


--
-- Name: idx_6547bd5065ff1aec; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_6547bd5065ff1aec ON typedocument USING btree (updatedby_id);


--
-- Name: idx_6a2e76b71c4132c1; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_6a2e76b71c4132c1 ON activityperson USING btree (roleobj_id);


--
-- Name: idx_6a2e76b7217bbb47; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_6a2e76b7217bbb47 ON activityperson USING btree (person_id);


--
-- Name: idx_6a2e76b73174800f; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_6a2e76b73174800f ON activityperson USING btree (createdby_id);


--
-- Name: idx_6a2e76b763d8c20e; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_6a2e76b763d8c20e ON activityperson USING btree (deletedby_id);


--
-- Name: idx_6a2e76b765ff1aec; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_6a2e76b765ff1aec ON activityperson USING btree (updatedby_id);


--
-- Name: idx_6a2e76b781c06096; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_6a2e76b781c06096 ON activityperson USING btree (activity_id);


--
-- Name: idx_6a89662b1c4132c1; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_6a89662b1c4132c1 ON organizationperson USING btree (roleobj_id);


--
-- Name: idx_6a89662b217bbb47; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_6a89662b217bbb47 ON organizationperson USING btree (person_id);


--
-- Name: idx_6a89662b3174800f; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_6a89662b3174800f ON organizationperson USING btree (createdby_id);


--
-- Name: idx_6a89662b32c8a3de; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_6a89662b32c8a3de ON organizationperson USING btree (organization_id);


--
-- Name: idx_6a89662b63d8c20e; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_6a89662b63d8c20e ON organizationperson USING btree (deletedby_id);


--
-- Name: idx_6a89662b65ff1aec; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_6a89662b65ff1aec ON organizationperson USING btree (updatedby_id);


--
-- Name: idx_6d18950d166d1f9c; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_6d18950d166d1f9c ON project_discipline USING btree (project_id);


--
-- Name: idx_6d18950da5522701; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_6d18950da5522701 ON project_discipline USING btree (discipline_id);


--
-- Name: idx_79ced4aa3174800f; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_79ced4aa3174800f ON tva USING btree (createdby_id);


--
-- Name: idx_79ced4aa63d8c20e; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_79ced4aa63d8c20e ON tva USING btree (deletedby_id);


--
-- Name: idx_79ced4aa65ff1aec; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_79ced4aa65ff1aec ON tva USING btree (updatedby_id);


--
-- Name: idx_8115848c3174800f; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_8115848c3174800f ON activitypayment USING btree (createdby_id);


--
-- Name: idx_8115848c38248176; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_8115848c38248176 ON activitypayment USING btree (currency_id);


--
-- Name: idx_8115848c63d8c20e; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_8115848c63d8c20e ON activitypayment USING btree (deletedby_id);


--
-- Name: idx_8115848c65ff1aec; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_8115848c65ff1aec ON activitypayment USING btree (updatedby_id);


--
-- Name: idx_8115848c81c06096; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_8115848c81c06096 ON activitypayment USING btree (activity_id);


--
-- Name: idx_87209a8779066886; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_87209a8779066886 ON privilege USING btree (root_id);


--
-- Name: idx_87209a87bcf5e72d; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_87209a87bcf5e72d ON privilege USING btree (categorie_id);


--
-- Name: idx_9020ea693174800f; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_9020ea693174800f ON currency USING btree (createdby_id);


--
-- Name: idx_9020ea6963d8c20e; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_9020ea6963d8c20e ON currency USING btree (deletedby_id);


--
-- Name: idx_9020ea6965ff1aec; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_9020ea6965ff1aec ON currency USING btree (updatedby_id);


--
-- Name: idx_9310307d1c4132c1; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_9310307d1c4132c1 ON activityorganization USING btree (roleobj_id);


--
-- Name: idx_9310307d3174800f; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_9310307d3174800f ON activityorganization USING btree (createdby_id);


--
-- Name: idx_9310307d32c8a3de; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_9310307d32c8a3de ON activityorganization USING btree (organization_id);


--
-- Name: idx_9310307d63d8c20e; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_9310307d63d8c20e ON activityorganization USING btree (deletedby_id);


--
-- Name: idx_9310307d65ff1aec; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_9310307d65ff1aec ON activityorganization USING btree (updatedby_id);


--
-- Name: idx_9310307d81c06096; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_9310307d81c06096 ON activityorganization USING btree (activity_id);


--
-- Name: idx_a78218303174800f; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_a78218303174800f ON organizationrole USING btree (createdby_id);


--
-- Name: idx_a782183063d8c20e; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_a782183063d8c20e ON organizationrole USING btree (deletedby_id);


--
-- Name: idx_a782183065ff1aec; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_a782183065ff1aec ON organizationrole USING btree (updatedby_id);


--
-- Name: idx_b8fa4973174800f; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_b8fa4973174800f ON activitytype USING btree (createdby_id);


--
-- Name: idx_b8fa49763d8c20e; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_b8fa49763d8c20e ON activitytype USING btree (deletedby_id);


--
-- Name: idx_b8fa49765ff1aec; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_b8fa49765ff1aec ON activitytype USING btree (updatedby_id);


--
-- Name: idx_c311ba72217bbb47; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_c311ba72217bbb47 ON administrativedocument USING btree (person_id);


--
-- Name: idx_c583f07f3174800f; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_c583f07f3174800f ON workpackage USING btree (createdby_id);


--
-- Name: idx_c583f07f63d8c20e; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_c583f07f63d8c20e ON workpackage USING btree (deletedby_id);


--
-- Name: idx_c583f07f65ff1aec; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_c583f07f65ff1aec ON workpackage USING btree (updatedby_id);


--
-- Name: idx_c583f07f81c06096; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_c583f07f81c06096 ON workpackage USING btree (activity_id);


--
-- Name: idx_d6d4495b32fb8aea; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_d6d4495b32fb8aea ON role_privilege USING btree (privilege_id);


--
-- Name: idx_d6d4495bd60322ac; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_d6d4495bd60322ac ON role_privilege USING btree (role_id);


--
-- Name: idx_d9dfb8843174800f; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_d9dfb8843174800f ON organization USING btree (createdby_id);


--
-- Name: idx_d9dfb88463d8c20e; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_d9dfb88463d8c20e ON organization USING btree (deletedby_id);


--
-- Name: idx_d9dfb88465ff1aec; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_d9dfb88465ff1aec ON organization USING btree (updatedby_id);


--
-- Name: idx_dd65739b166d1f9c; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_dd65739b166d1f9c ON projectpartner USING btree (project_id);


--
-- Name: idx_dd65739b1c4132c1; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_dd65739b1c4132c1 ON projectpartner USING btree (roleobj_id);


--
-- Name: idx_dd65739b3174800f; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_dd65739b3174800f ON projectpartner USING btree (createdby_id);


--
-- Name: idx_dd65739b32c8a3de; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_dd65739b32c8a3de ON projectpartner USING btree (organization_id);


--
-- Name: idx_dd65739b63d8c20e; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_dd65739b63d8c20e ON projectpartner USING btree (deletedby_id);


--
-- Name: idx_dd65739b65ff1aec; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_dd65739b65ff1aec ON projectpartner USING btree (updatedby_id);


--
-- Name: idx_e00ee972a5522701; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_e00ee972a5522701 ON project USING btree (discipline_id);


--
-- Name: idx_e9b87677217bbb47; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_e9b87677217bbb47 ON workpackageperson USING btree (person_id);


--
-- Name: idx_e9b876773174800f; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_e9b876773174800f ON workpackageperson USING btree (createdby_id);


--
-- Name: idx_e9b8767763d8c20e; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_e9b8767763d8c20e ON workpackageperson USING btree (deletedby_id);


--
-- Name: idx_e9b8767765ff1aec; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_e9b8767765ff1aec ON workpackageperson USING btree (updatedby_id);


--
-- Name: idx_e9b876779485a167; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE INDEX idx_e9b876779485a167 ON workpackageperson USING btree (workpackage_id);


--
-- Name: uniq_2de8c6a31596728e; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE UNIQUE INDEX uniq_2de8c6a31596728e ON user_role USING btree (ldap_filter);


--
-- Name: uniq_2de8c6a3d60322ac; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE UNIQUE INDEX uniq_2de8c6a3d60322ac ON user_role USING btree (role_id);


--
-- Name: uniq_598638fb8a90aba9; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE UNIQUE INDEX uniq_598638fb8a90aba9 ON useraccessdefinition USING btree (key);


--
-- Name: uniq_6e60b4f7d60322ac; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE UNIQUE INDEX uniq_6e60b4f7d60322ac ON organization_role USING btree (role_id);


--
-- Name: uniq_9de7cd62e7927c74; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE UNIQUE INDEX uniq_9de7cd62e7927c74 ON authentification USING btree (email);


--
-- Name: uniq_9de7cd62f85e0677; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE UNIQUE INDEX uniq_9de7cd62f85e0677 ON authentification USING btree (username);


--
-- Name: uniq_a7821830ea750e8; Type: INDEX; Schema: public; Owner: -; Tablespace: 
--

CREATE UNIQUE INDEX uniq_a7821830ea750e8 ON organizationrole USING btree (label);


--
-- Name: activity_numauto; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER activity_numauto AFTER INSERT ON activity FOR EACH ROW EXECUTE PROCEDURE oscar_activity_numauto();


--
-- Name: fk_205cd03781c06096; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY activity_discipline
    ADD CONSTRAINT fk_205cd03781c06096 FOREIGN KEY (activity_id) REFERENCES activity(id) ON DELETE CASCADE;


--
-- Name: fk_205cd037a5522701; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY activity_discipline
    ADD CONSTRAINT fk_205cd037a5522701 FOREIGN KEY (discipline_id) REFERENCES discipline(id) ON DELETE CASCADE;


--
-- Name: fk_22ba6515217bbb47; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY notificationperson
    ADD CONSTRAINT fk_22ba6515217bbb47 FOREIGN KEY (person_id) REFERENCES person(id);


--
-- Name: fk_22ba6515ef1a9d84; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY notificationperson
    ADD CONSTRAINT fk_22ba6515ef1a9d84 FOREIGN KEY (notification_id) REFERENCES notification(id) ON DELETE CASCADE;


--
-- Name: fk_29fdc4ce3174800f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY datetype
    ADD CONSTRAINT fk_29fdc4ce3174800f FOREIGN KEY (createdby_id) REFERENCES person(id);


--
-- Name: fk_29fdc4ce63d8c20e; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY datetype
    ADD CONSTRAINT fk_29fdc4ce63d8c20e FOREIGN KEY (deletedby_id) REFERENCES person(id);


--
-- Name: fk_29fdc4ce65ff1aec; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY datetype
    ADD CONSTRAINT fk_29fdc4ce65ff1aec FOREIGN KEY (updatedby_id) REFERENCES person(id);


--
-- Name: fk_2dcfc4c43174800f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY activitydate
    ADD CONSTRAINT fk_2dcfc4c43174800f FOREIGN KEY (createdby_id) REFERENCES person(id);


--
-- Name: fk_2dcfc4c463d8c20e; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY activitydate
    ADD CONSTRAINT fk_2dcfc4c463d8c20e FOREIGN KEY (deletedby_id) REFERENCES person(id);


--
-- Name: fk_2dcfc4c465ff1aec; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY activitydate
    ADD CONSTRAINT fk_2dcfc4c465ff1aec FOREIGN KEY (updatedby_id) REFERENCES person(id);


--
-- Name: fk_2dcfc4c481c06096; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY activitydate
    ADD CONSTRAINT fk_2dcfc4c481c06096 FOREIGN KEY (activity_id) REFERENCES activity(id);


--
-- Name: fk_2dcfc4c4c54c8c93; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY activitydate
    ADD CONSTRAINT fk_2dcfc4c4c54c8c93 FOREIGN KEY (type_id) REFERENCES datetype(id);


--
-- Name: fk_2de8c6a3727aca70; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY user_role
    ADD CONSTRAINT fk_2de8c6a3727aca70 FOREIGN KEY (parent_id) REFERENCES user_role(id);


--
-- Name: fk_3370d4403174800f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY person
    ADD CONSTRAINT fk_3370d4403174800f FOREIGN KEY (createdby_id) REFERENCES person(id);


--
-- Name: fk_3370d44063d8c20e; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY person
    ADD CONSTRAINT fk_3370d44063d8c20e FOREIGN KEY (deletedby_id) REFERENCES person(id);


--
-- Name: fk_3370d44065ff1aec; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY person
    ADD CONSTRAINT fk_3370d44065ff1aec FOREIGN KEY (updatedby_id) REFERENCES person(id);


--
-- Name: fk_34944573217bbb47; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY timesheet
    ADD CONSTRAINT fk_34944573217bbb47 FOREIGN KEY (person_id) REFERENCES person(id);


--
-- Name: fk_349445733174800f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY timesheet
    ADD CONSTRAINT fk_349445733174800f FOREIGN KEY (createdby_id) REFERENCES person(id);


--
-- Name: fk_3494457363d8c20e; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY timesheet
    ADD CONSTRAINT fk_3494457363d8c20e FOREIGN KEY (deletedby_id) REFERENCES person(id);


--
-- Name: fk_3494457365ff1aec; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY timesheet
    ADD CONSTRAINT fk_3494457365ff1aec FOREIGN KEY (updatedby_id) REFERENCES person(id);


--
-- Name: fk_3494457381c06096; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY timesheet
    ADD CONSTRAINT fk_3494457381c06096 FOREIGN KEY (activity_id) REFERENCES activity(id);


--
-- Name: fk_34944573dbd8a2b7; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY timesheet
    ADD CONSTRAINT fk_34944573dbd8a2b7 FOREIGN KEY (workpackage_id) REFERENCES workpackage(id);


--
-- Name: fk_4a390fe8217bbb47; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY contractdocument
    ADD CONSTRAINT fk_4a390fe8217bbb47 FOREIGN KEY (person_id) REFERENCES person(id);


--
-- Name: fk_4a390fe83bebd1bd; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY contractdocument
    ADD CONSTRAINT fk_4a390fe83bebd1bd FOREIGN KEY (typedocument_id) REFERENCES typedocument(id);


--
-- Name: fk_4a390fe85c0c89f3; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY contractdocument
    ADD CONSTRAINT fk_4a390fe85c0c89f3 FOREIGN KEY (grant_id) REFERENCES activity(id);


--
-- Name: fk_55026b0c166d1f9c; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY activity
    ADD CONSTRAINT fk_55026b0c166d1f9c FOREIGN KEY (project_id) REFERENCES project(id);


--
-- Name: fk_55026b0c3174800f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY activity
    ADD CONSTRAINT fk_55026b0c3174800f FOREIGN KEY (createdby_id) REFERENCES person(id);


--
-- Name: fk_55026b0c38248176; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY activity
    ADD CONSTRAINT fk_55026b0c38248176 FOREIGN KEY (currency_id) REFERENCES currency(id);


--
-- Name: fk_55026b0c4d79775f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY activity
    ADD CONSTRAINT fk_55026b0c4d79775f FOREIGN KEY (tva_id) REFERENCES tva(id);


--
-- Name: fk_55026b0c63d8c20e; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY activity
    ADD CONSTRAINT fk_55026b0c63d8c20e FOREIGN KEY (deletedby_id) REFERENCES person(id);


--
-- Name: fk_55026b0c65ff1aec; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY activity
    ADD CONSTRAINT fk_55026b0c65ff1aec FOREIGN KEY (updatedby_id) REFERENCES person(id);


--
-- Name: fk_55026b0c953c1c61; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY activity
    ADD CONSTRAINT fk_55026b0c953c1c61 FOREIGN KEY (source_id) REFERENCES grantsource(id);


--
-- Name: fk_55026b0ca1b4b28c; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY activity
    ADD CONSTRAINT fk_55026b0ca1b4b28c FOREIGN KEY (activitytype_id) REFERENCES activitytype(id);


--
-- Name: fk_55026b0cc54c8c93; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY activity
    ADD CONSTRAINT fk_55026b0cc54c8c93 FOREIGN KEY (type_id) REFERENCES contracttype(id);


--
-- Name: fk_5d5b51b9166d1f9c; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY projectmember
    ADD CONSTRAINT fk_5d5b51b9166d1f9c FOREIGN KEY (project_id) REFERENCES project(id);


--
-- Name: fk_5d5b51b91c4132c1; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY projectmember
    ADD CONSTRAINT fk_5d5b51b91c4132c1 FOREIGN KEY (roleobj_id) REFERENCES user_role(id);


--
-- Name: fk_5d5b51b9217bbb47; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY projectmember
    ADD CONSTRAINT fk_5d5b51b9217bbb47 FOREIGN KEY (person_id) REFERENCES person(id);


--
-- Name: fk_5d5b51b93174800f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY projectmember
    ADD CONSTRAINT fk_5d5b51b93174800f FOREIGN KEY (createdby_id) REFERENCES person(id);


--
-- Name: fk_5d5b51b963d8c20e; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY projectmember
    ADD CONSTRAINT fk_5d5b51b963d8c20e FOREIGN KEY (deletedby_id) REFERENCES person(id);


--
-- Name: fk_5d5b51b965ff1aec; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY projectmember
    ADD CONSTRAINT fk_5d5b51b965ff1aec FOREIGN KEY (updatedby_id) REFERENCES person(id);


--
-- Name: fk_5dbdaf56d28043b; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY authentification_role
    ADD CONSTRAINT fk_5dbdaf56d28043b FOREIGN KEY (authentification_id) REFERENCES authentification(id) ON DELETE CASCADE;


--
-- Name: fk_5dbdaf5d60322ac; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY authentification_role
    ADD CONSTRAINT fk_5dbdaf5d60322ac FOREIGN KEY (role_id) REFERENCES user_role(id) ON DELETE CASCADE;


--
-- Name: fk_6547bd503174800f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY typedocument
    ADD CONSTRAINT fk_6547bd503174800f FOREIGN KEY (createdby_id) REFERENCES person(id);


--
-- Name: fk_6547bd5063d8c20e; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY typedocument
    ADD CONSTRAINT fk_6547bd5063d8c20e FOREIGN KEY (deletedby_id) REFERENCES person(id);


--
-- Name: fk_6547bd5065ff1aec; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY typedocument
    ADD CONSTRAINT fk_6547bd5065ff1aec FOREIGN KEY (updatedby_id) REFERENCES person(id);


--
-- Name: fk_6a2e76b71c4132c1; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY activityperson
    ADD CONSTRAINT fk_6a2e76b71c4132c1 FOREIGN KEY (roleobj_id) REFERENCES user_role(id);


--
-- Name: fk_6a2e76b7217bbb47; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY activityperson
    ADD CONSTRAINT fk_6a2e76b7217bbb47 FOREIGN KEY (person_id) REFERENCES person(id);


--
-- Name: fk_6a2e76b73174800f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY activityperson
    ADD CONSTRAINT fk_6a2e76b73174800f FOREIGN KEY (createdby_id) REFERENCES person(id);


--
-- Name: fk_6a2e76b763d8c20e; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY activityperson
    ADD CONSTRAINT fk_6a2e76b763d8c20e FOREIGN KEY (deletedby_id) REFERENCES person(id);


--
-- Name: fk_6a2e76b765ff1aec; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY activityperson
    ADD CONSTRAINT fk_6a2e76b765ff1aec FOREIGN KEY (updatedby_id) REFERENCES person(id);


--
-- Name: fk_6a2e76b781c06096; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY activityperson
    ADD CONSTRAINT fk_6a2e76b781c06096 FOREIGN KEY (activity_id) REFERENCES activity(id);


--
-- Name: fk_6a89662b1c4132c1; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY organizationperson
    ADD CONSTRAINT fk_6a89662b1c4132c1 FOREIGN KEY (roleobj_id) REFERENCES user_role(id);


--
-- Name: fk_6a89662b217bbb47; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY organizationperson
    ADD CONSTRAINT fk_6a89662b217bbb47 FOREIGN KEY (person_id) REFERENCES person(id);


--
-- Name: fk_6a89662b3174800f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY organizationperson
    ADD CONSTRAINT fk_6a89662b3174800f FOREIGN KEY (createdby_id) REFERENCES person(id);


--
-- Name: fk_6a89662b32c8a3de; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY organizationperson
    ADD CONSTRAINT fk_6a89662b32c8a3de FOREIGN KEY (organization_id) REFERENCES organization(id);


--
-- Name: fk_6a89662b63d8c20e; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY organizationperson
    ADD CONSTRAINT fk_6a89662b63d8c20e FOREIGN KEY (deletedby_id) REFERENCES person(id);


--
-- Name: fk_6a89662b65ff1aec; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY organizationperson
    ADD CONSTRAINT fk_6a89662b65ff1aec FOREIGN KEY (updatedby_id) REFERENCES person(id);


--
-- Name: fk_6d18950d166d1f9c; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY project_discipline
    ADD CONSTRAINT fk_6d18950d166d1f9c FOREIGN KEY (project_id) REFERENCES project(id) ON DELETE CASCADE;


--
-- Name: fk_6d18950da5522701; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY project_discipline
    ADD CONSTRAINT fk_6d18950da5522701 FOREIGN KEY (discipline_id) REFERENCES discipline(id) ON DELETE CASCADE;


--
-- Name: fk_79ced4aa3174800f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY tva
    ADD CONSTRAINT fk_79ced4aa3174800f FOREIGN KEY (createdby_id) REFERENCES person(id);


--
-- Name: fk_79ced4aa63d8c20e; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY tva
    ADD CONSTRAINT fk_79ced4aa63d8c20e FOREIGN KEY (deletedby_id) REFERENCES person(id);


--
-- Name: fk_79ced4aa65ff1aec; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY tva
    ADD CONSTRAINT fk_79ced4aa65ff1aec FOREIGN KEY (updatedby_id) REFERENCES person(id);


--
-- Name: fk_8115848c3174800f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY activitypayment
    ADD CONSTRAINT fk_8115848c3174800f FOREIGN KEY (createdby_id) REFERENCES person(id);


--
-- Name: fk_8115848c38248176; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY activitypayment
    ADD CONSTRAINT fk_8115848c38248176 FOREIGN KEY (currency_id) REFERENCES currency(id);


--
-- Name: fk_8115848c63d8c20e; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY activitypayment
    ADD CONSTRAINT fk_8115848c63d8c20e FOREIGN KEY (deletedby_id) REFERENCES person(id);


--
-- Name: fk_8115848c65ff1aec; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY activitypayment
    ADD CONSTRAINT fk_8115848c65ff1aec FOREIGN KEY (updatedby_id) REFERENCES person(id);


--
-- Name: fk_8115848c81c06096; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY activitypayment
    ADD CONSTRAINT fk_8115848c81c06096 FOREIGN KEY (activity_id) REFERENCES activity(id);


--
-- Name: fk_87209a8779066886; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY privilege
    ADD CONSTRAINT fk_87209a8779066886 FOREIGN KEY (root_id) REFERENCES privilege(id);


--
-- Name: fk_87209a87bcf5e72d; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY privilege
    ADD CONSTRAINT fk_87209a87bcf5e72d FOREIGN KEY (categorie_id) REFERENCES categorie_privilege(id);


--
-- Name: fk_9020ea693174800f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY currency
    ADD CONSTRAINT fk_9020ea693174800f FOREIGN KEY (createdby_id) REFERENCES person(id);


--
-- Name: fk_9020ea6963d8c20e; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY currency
    ADD CONSTRAINT fk_9020ea6963d8c20e FOREIGN KEY (deletedby_id) REFERENCES person(id);


--
-- Name: fk_9020ea6965ff1aec; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY currency
    ADD CONSTRAINT fk_9020ea6965ff1aec FOREIGN KEY (updatedby_id) REFERENCES person(id);


--
-- Name: fk_9310307d1c4132c1; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY activityorganization
    ADD CONSTRAINT fk_9310307d1c4132c1 FOREIGN KEY (roleobj_id) REFERENCES organizationrole(id);


--
-- Name: fk_9310307d3174800f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY activityorganization
    ADD CONSTRAINT fk_9310307d3174800f FOREIGN KEY (createdby_id) REFERENCES person(id);


--
-- Name: fk_9310307d32c8a3de; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY activityorganization
    ADD CONSTRAINT fk_9310307d32c8a3de FOREIGN KEY (organization_id) REFERENCES organization(id);


--
-- Name: fk_9310307d63d8c20e; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY activityorganization
    ADD CONSTRAINT fk_9310307d63d8c20e FOREIGN KEY (deletedby_id) REFERENCES person(id);


--
-- Name: fk_9310307d65ff1aec; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY activityorganization
    ADD CONSTRAINT fk_9310307d65ff1aec FOREIGN KEY (updatedby_id) REFERENCES person(id);


--
-- Name: fk_9310307d81c06096; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY activityorganization
    ADD CONSTRAINT fk_9310307d81c06096 FOREIGN KEY (activity_id) REFERENCES activity(id);


--
-- Name: fk_a78218303174800f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY organizationrole
    ADD CONSTRAINT fk_a78218303174800f FOREIGN KEY (createdby_id) REFERENCES person(id);


--
-- Name: fk_a782183063d8c20e; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY organizationrole
    ADD CONSTRAINT fk_a782183063d8c20e FOREIGN KEY (deletedby_id) REFERENCES person(id);


--
-- Name: fk_a782183065ff1aec; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY organizationrole
    ADD CONSTRAINT fk_a782183065ff1aec FOREIGN KEY (updatedby_id) REFERENCES person(id);


--
-- Name: fk_b8fa4973174800f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY activitytype
    ADD CONSTRAINT fk_b8fa4973174800f FOREIGN KEY (createdby_id) REFERENCES person(id);


--
-- Name: fk_b8fa49763d8c20e; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY activitytype
    ADD CONSTRAINT fk_b8fa49763d8c20e FOREIGN KEY (deletedby_id) REFERENCES person(id);


--
-- Name: fk_b8fa49765ff1aec; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY activitytype
    ADD CONSTRAINT fk_b8fa49765ff1aec FOREIGN KEY (updatedby_id) REFERENCES person(id);


--
-- Name: fk_c311ba72217bbb47; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY administrativedocument
    ADD CONSTRAINT fk_c311ba72217bbb47 FOREIGN KEY (person_id) REFERENCES person(id);


--
-- Name: fk_c583f07f3174800f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY workpackage
    ADD CONSTRAINT fk_c583f07f3174800f FOREIGN KEY (createdby_id) REFERENCES person(id);


--
-- Name: fk_c583f07f63d8c20e; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY workpackage
    ADD CONSTRAINT fk_c583f07f63d8c20e FOREIGN KEY (deletedby_id) REFERENCES person(id);


--
-- Name: fk_c583f07f65ff1aec; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY workpackage
    ADD CONSTRAINT fk_c583f07f65ff1aec FOREIGN KEY (updatedby_id) REFERENCES person(id);


--
-- Name: fk_c583f07f81c06096; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY workpackage
    ADD CONSTRAINT fk_c583f07f81c06096 FOREIGN KEY (activity_id) REFERENCES activity(id);


--
-- Name: fk_d6d4495b32fb8aea; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY role_privilege
    ADD CONSTRAINT fk_d6d4495b32fb8aea FOREIGN KEY (privilege_id) REFERENCES privilege(id);


--
-- Name: fk_d6d4495bd60322ac; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY role_privilege
    ADD CONSTRAINT fk_d6d4495bd60322ac FOREIGN KEY (role_id) REFERENCES user_role(id);


--
-- Name: fk_d9dfb8843174800f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY organization
    ADD CONSTRAINT fk_d9dfb8843174800f FOREIGN KEY (createdby_id) REFERENCES person(id);


--
-- Name: fk_d9dfb88463d8c20e; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY organization
    ADD CONSTRAINT fk_d9dfb88463d8c20e FOREIGN KEY (deletedby_id) REFERENCES person(id);


--
-- Name: fk_d9dfb88465ff1aec; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY organization
    ADD CONSTRAINT fk_d9dfb88465ff1aec FOREIGN KEY (updatedby_id) REFERENCES person(id);


--
-- Name: fk_dd65739b166d1f9c; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY projectpartner
    ADD CONSTRAINT fk_dd65739b166d1f9c FOREIGN KEY (project_id) REFERENCES project(id);


--
-- Name: fk_dd65739b1c4132c1; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY projectpartner
    ADD CONSTRAINT fk_dd65739b1c4132c1 FOREIGN KEY (roleobj_id) REFERENCES organizationrole(id);


--
-- Name: fk_dd65739b3174800f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY projectpartner
    ADD CONSTRAINT fk_dd65739b3174800f FOREIGN KEY (createdby_id) REFERENCES person(id);


--
-- Name: fk_dd65739b32c8a3de; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY projectpartner
    ADD CONSTRAINT fk_dd65739b32c8a3de FOREIGN KEY (organization_id) REFERENCES organization(id);


--
-- Name: fk_dd65739b63d8c20e; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY projectpartner
    ADD CONSTRAINT fk_dd65739b63d8c20e FOREIGN KEY (deletedby_id) REFERENCES person(id);


--
-- Name: fk_dd65739b65ff1aec; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY projectpartner
    ADD CONSTRAINT fk_dd65739b65ff1aec FOREIGN KEY (updatedby_id) REFERENCES person(id);


--
-- Name: fk_e00ee972a5522701; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY project
    ADD CONSTRAINT fk_e00ee972a5522701 FOREIGN KEY (discipline_id) REFERENCES discipline(id);


--
-- Name: fk_e9b87677217bbb47; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY workpackageperson
    ADD CONSTRAINT fk_e9b87677217bbb47 FOREIGN KEY (person_id) REFERENCES person(id);


--
-- Name: fk_e9b876773174800f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY workpackageperson
    ADD CONSTRAINT fk_e9b876773174800f FOREIGN KEY (createdby_id) REFERENCES person(id);


--
-- Name: fk_e9b8767763d8c20e; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY workpackageperson
    ADD CONSTRAINT fk_e9b8767763d8c20e FOREIGN KEY (deletedby_id) REFERENCES person(id);


--
-- Name: fk_e9b8767765ff1aec; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY workpackageperson
    ADD CONSTRAINT fk_e9b8767765ff1aec FOREIGN KEY (updatedby_id) REFERENCES person(id);


--
-- Name: fk_e9b876779485a167; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY workpackageperson
    ADD CONSTRAINT fk_e9b876779485a167 FOREIGN KEY (workpackage_id) REFERENCES workpackage(id);


--
-- Name: public; Type: ACL; Schema: -; Owner: -
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- PostgreSQL database dump complete
--

