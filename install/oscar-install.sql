--
-- PostgreSQL database dump
--

-- Dumped from database version 9.5.13
-- Dumped by pg_dump version 10.6 (Ubuntu 10.6-0ubuntu0.18.10.1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET client_min_messages = warning;
SET row_security = off;

ALTER TABLE ONLY public.workpackageperson DROP CONSTRAINT fk_e9b876779485a167;
ALTER TABLE ONLY public.workpackageperson DROP CONSTRAINT fk_e9b8767765ff1aec;
ALTER TABLE ONLY public.workpackageperson DROP CONSTRAINT fk_e9b8767763d8c20e;
ALTER TABLE ONLY public.workpackageperson DROP CONSTRAINT fk_e9b876773174800f;
ALTER TABLE ONLY public.workpackageperson DROP CONSTRAINT fk_e9b87677217bbb47;
ALTER TABLE ONLY public.projectpartner DROP CONSTRAINT fk_dd65739b65ff1aec;
ALTER TABLE ONLY public.projectpartner DROP CONSTRAINT fk_dd65739b63d8c20e;
ALTER TABLE ONLY public.projectpartner DROP CONSTRAINT fk_dd65739b32c8a3de;
ALTER TABLE ONLY public.projectpartner DROP CONSTRAINT fk_dd65739b3174800f;
ALTER TABLE ONLY public.projectpartner DROP CONSTRAINT fk_dd65739b1c4132c1;
ALTER TABLE ONLY public.projectpartner DROP CONSTRAINT fk_dd65739b166d1f9c;
ALTER TABLE ONLY public.organization DROP CONSTRAINT fk_d9dfb884e5915d19;
ALTER TABLE ONLY public.organization DROP CONSTRAINT fk_d9dfb88465ff1aec;
ALTER TABLE ONLY public.organization DROP CONSTRAINT fk_d9dfb88463d8c20e;
ALTER TABLE ONLY public.organization DROP CONSTRAINT fk_d9dfb8843174800f;
ALTER TABLE ONLY public.activityrequest DROP CONSTRAINT fk_d7aa8f1e9e6b1585;
ALTER TABLE ONLY public.activityrequest DROP CONSTRAINT fk_d7aa8f1e65ff1aec;
ALTER TABLE ONLY public.activityrequest DROP CONSTRAINT fk_d7aa8f1e63d8c20e;
ALTER TABLE ONLY public.activityrequest DROP CONSTRAINT fk_d7aa8f1e3174800f;
ALTER TABLE ONLY public.validationperiod_prj DROP CONSTRAINT fk_d7488e1525e297e4;
ALTER TABLE ONLY public.validationperiod_prj DROP CONSTRAINT fk_d7488e15217bbb47;
ALTER TABLE ONLY public.role_privilege DROP CONSTRAINT fk_d6d4495bd60322ac;
ALTER TABLE ONLY public.role_privilege DROP CONSTRAINT fk_d6d4495b32fb8aea;
ALTER TABLE ONLY public.activityrequestfollow DROP CONSTRAINT fk_cfe2df3ae8fa3e0f;
ALTER TABLE ONLY public.activityrequestfollow DROP CONSTRAINT fk_cfe2df3a65ff1aec;
ALTER TABLE ONLY public.activityrequestfollow DROP CONSTRAINT fk_cfe2df3a63d8c20e;
ALTER TABLE ONLY public.activityrequestfollow DROP CONSTRAINT fk_cfe2df3a3174800f;
ALTER TABLE ONLY public.workpackage DROP CONSTRAINT fk_c583f07f81c06096;
ALTER TABLE ONLY public.workpackage DROP CONSTRAINT fk_c583f07f65ff1aec;
ALTER TABLE ONLY public.workpackage DROP CONSTRAINT fk_c583f07f63d8c20e;
ALTER TABLE ONLY public.workpackage DROP CONSTRAINT fk_c583f07f3174800f;
ALTER TABLE ONLY public.administrativedocument DROP CONSTRAINT fk_c311ba72217bbb47;
ALTER TABLE ONLY public.activitytype DROP CONSTRAINT fk_b8fa49765ff1aec;
ALTER TABLE ONLY public.activitytype DROP CONSTRAINT fk_b8fa49763d8c20e;
ALTER TABLE ONLY public.activitytype DROP CONSTRAINT fk_b8fa4973174800f;
ALTER TABLE ONLY public.validationperiod DROP CONSTRAINT fk_b700890a3c21f464;
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
ALTER TABLE ONLY public.timesheetsby DROP CONSTRAINT fk_8ffc688a241061bf;
ALTER TABLE ONLY public.timesheetsby DROP CONSTRAINT fk_8ffc688a217bbb47;
ALTER TABLE ONLY public.privilege DROP CONSTRAINT fk_87209a87bcf5e72d;
ALTER TABLE ONLY public.privilege DROP CONSTRAINT fk_87209a8779066886;
ALTER TABLE ONLY public.activitypayment DROP CONSTRAINT fk_8115848c81c06096;
ALTER TABLE ONLY public.activitypayment DROP CONSTRAINT fk_8115848c65ff1aec;
ALTER TABLE ONLY public.activitypayment DROP CONSTRAINT fk_8115848c63d8c20e;
ALTER TABLE ONLY public.activitypayment DROP CONSTRAINT fk_8115848c38248176;
ALTER TABLE ONLY public.activitypayment DROP CONSTRAINT fk_8115848c3174800f;
ALTER TABLE ONLY public.referent DROP CONSTRAINT fk_7ecce3a35e47e35;
ALTER TABLE ONLY public.referent DROP CONSTRAINT fk_7ecce3a217bbb47;
ALTER TABLE ONLY public.organizationtype DROP CONSTRAINT fk_7c35c57379066886;
ALTER TABLE ONLY public.organizationtype DROP CONSTRAINT fk_7c35c57365ff1aec;
ALTER TABLE ONLY public.organizationtype DROP CONSTRAINT fk_7c35c57363d8c20e;
ALTER TABLE ONLY public.organizationtype DROP CONSTRAINT fk_7c35c5733174800f;
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
ALTER TABLE ONLY public.activity DROP CONSTRAINT fk_55026b0c65ff1aec;
ALTER TABLE ONLY public.activity DROP CONSTRAINT fk_55026b0c63d8c20e;
ALTER TABLE ONLY public.activity DROP CONSTRAINT fk_55026b0c4d79775f;
ALTER TABLE ONLY public.activity DROP CONSTRAINT fk_55026b0c38248176;
ALTER TABLE ONLY public.activity DROP CONSTRAINT fk_55026b0c3174800f;
ALTER TABLE ONLY public.activity DROP CONSTRAINT fk_55026b0c166d1f9c;
ALTER TABLE ONLY public.contractdocument DROP CONSTRAINT fk_4a390fe85c0c89f3;
ALTER TABLE ONLY public.contractdocument DROP CONSTRAINT fk_4a390fe83bebd1bd;
ALTER TABLE ONLY public.contractdocument DROP CONSTRAINT fk_4a390fe8217bbb47;
ALTER TABLE ONLY public.validationperiod_adm DROP CONSTRAINT fk_4850672625e297e4;
ALTER TABLE ONLY public.validationperiod_adm DROP CONSTRAINT fk_48506726217bbb47;
ALTER TABLE ONLY public.timesheet DROP CONSTRAINT fk_34944573dbd8a2b7;
ALTER TABLE ONLY public.timesheet DROP CONSTRAINT fk_34944573a7131547;
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
ALTER TABLE ONLY public.validationperiod_sci DROP CONSTRAINT fk_1fde42e625e297e4;
ALTER TABLE ONLY public.validationperiod_sci DROP CONSTRAINT fk_1fde42e6217bbb47;
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
DROP INDEX public.idx_dd65739b65ff1aec;
DROP INDEX public.idx_dd65739b63d8c20e;
DROP INDEX public.idx_dd65739b32c8a3de;
DROP INDEX public.idx_dd65739b3174800f;
DROP INDEX public.idx_dd65739b1c4132c1;
DROP INDEX public.idx_dd65739b166d1f9c;
DROP INDEX public.idx_d9dfb884e5915d19;
DROP INDEX public.idx_d9dfb88465ff1aec;
DROP INDEX public.idx_d9dfb88463d8c20e;
DROP INDEX public.idx_d9dfb8843174800f;
DROP INDEX public.idx_d7aa8f1e9e6b1585;
DROP INDEX public.idx_d7aa8f1e65ff1aec;
DROP INDEX public.idx_d7aa8f1e63d8c20e;
DROP INDEX public.idx_d7aa8f1e3174800f;
DROP INDEX public.idx_d7488e1525e297e4;
DROP INDEX public.idx_d7488e15217bbb47;
DROP INDEX public.idx_d6d4495bd60322ac;
DROP INDEX public.idx_d6d4495b32fb8aea;
DROP INDEX public.idx_cfe2df3ae8fa3e0f;
DROP INDEX public.idx_cfe2df3a65ff1aec;
DROP INDEX public.idx_cfe2df3a63d8c20e;
DROP INDEX public.idx_cfe2df3a3174800f;
DROP INDEX public.idx_c583f07f81c06096;
DROP INDEX public.idx_c583f07f65ff1aec;
DROP INDEX public.idx_c583f07f63d8c20e;
DROP INDEX public.idx_c583f07f3174800f;
DROP INDEX public.idx_c311ba72217bbb47;
DROP INDEX public.idx_b8fa49765ff1aec;
DROP INDEX public.idx_b8fa49763d8c20e;
DROP INDEX public.idx_b8fa4973174800f;
DROP INDEX public.idx_b700890a3c21f464;
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
DROP INDEX public.idx_8ffc688a241061bf;
DROP INDEX public.idx_8ffc688a217bbb47;
DROP INDEX public.idx_87209a87bcf5e72d;
DROP INDEX public.idx_87209a8779066886;
DROP INDEX public.idx_8115848c81c06096;
DROP INDEX public.idx_8115848c65ff1aec;
DROP INDEX public.idx_8115848c63d8c20e;
DROP INDEX public.idx_8115848c38248176;
DROP INDEX public.idx_8115848c3174800f;
DROP INDEX public.idx_7ecce3a35e47e35;
DROP INDEX public.idx_7ecce3a217bbb47;
DROP INDEX public.idx_7c35c57379066886;
DROP INDEX public.idx_7c35c57365ff1aec;
DROP INDEX public.idx_7c35c57363d8c20e;
DROP INDEX public.idx_7c35c5733174800f;
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
DROP INDEX public.idx_55026b0c65ff1aec;
DROP INDEX public.idx_55026b0c63d8c20e;
DROP INDEX public.idx_55026b0c4d79775f;
DROP INDEX public.idx_55026b0c38248176;
DROP INDEX public.idx_55026b0c3174800f;
DROP INDEX public.idx_55026b0c166d1f9c;
DROP INDEX public.idx_4a390fe85c0c89f3;
DROP INDEX public.idx_4a390fe83bebd1bd;
DROP INDEX public.idx_4a390fe8217bbb47;
DROP INDEX public.idx_4850672625e297e4;
DROP INDEX public.idx_48506726217bbb47;
DROP INDEX public.idx_34944573dbd8a2b7;
DROP INDEX public.idx_34944573a7131547;
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
DROP INDEX public.idx_1fde42e625e297e4;
DROP INDEX public.idx_1fde42e6217bbb47;
ALTER TABLE ONLY public.workpackageperson DROP CONSTRAINT workpackageperson_pkey;
ALTER TABLE ONLY public.workpackage DROP CONSTRAINT workpackage_pkey;
ALTER TABLE ONLY public.validationperiod_sci DROP CONSTRAINT validationperiod_sci_pkey;
ALTER TABLE ONLY public.validationperiod_prj DROP CONSTRAINT validationperiod_prj_pkey;
ALTER TABLE ONLY public.validationperiod DROP CONSTRAINT validationperiod_pkey;
ALTER TABLE ONLY public.validationperiod_adm DROP CONSTRAINT validationperiod_adm_pkey;
ALTER TABLE ONLY public.useraccessdefinition DROP CONSTRAINT useraccessdefinition_pkey;
ALTER TABLE ONLY public.user_role DROP CONSTRAINT user_role_pkey;
ALTER TABLE ONLY public.typedocument DROP CONSTRAINT typedocument_pkey;
ALTER TABLE ONLY public.tva DROP CONSTRAINT tva_pkey;
ALTER TABLE ONLY public.timesheetsby DROP CONSTRAINT timesheetsby_pkey;
ALTER TABLE ONLY public.timesheet DROP CONSTRAINT timesheet_pkey;
ALTER TABLE ONLY public.role_privilege DROP CONSTRAINT role_privilege_pkey;
ALTER TABLE ONLY public.referent DROP CONSTRAINT referent_pkey;
ALTER TABLE ONLY public.projectpartner DROP CONSTRAINT projectpartner_pkey;
ALTER TABLE ONLY public.projectmember DROP CONSTRAINT projectmember_pkey;
ALTER TABLE ONLY public.project DROP CONSTRAINT project_pkey;
ALTER TABLE ONLY public.project_discipline DROP CONSTRAINT project_discipline_pkey;
ALTER TABLE ONLY public.privilege DROP CONSTRAINT privilege_pkey;
ALTER TABLE ONLY public.person DROP CONSTRAINT person_pkey;
ALTER TABLE ONLY public.organizationtype DROP CONSTRAINT organizationtype_pkey;
ALTER TABLE ONLY public.organizationrole DROP CONSTRAINT organizationrole_pkey;
ALTER TABLE ONLY public.organizationperson DROP CONSTRAINT organizationperson_pkey;
ALTER TABLE ONLY public.organization_role DROP CONSTRAINT organization_role_pkey;
ALTER TABLE ONLY public.organization DROP CONSTRAINT organization_pkey;
ALTER TABLE ONLY public.notificationperson DROP CONSTRAINT notificationperson_pkey;
ALTER TABLE ONLY public.notification DROP CONSTRAINT notification_pkey;
ALTER TABLE ONLY public.logactivity DROP CONSTRAINT logactivity_pkey;
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
ALTER TABLE ONLY public.activityrequestfollow DROP CONSTRAINT activityrequestfollow_pkey;
ALTER TABLE ONLY public.activityrequest DROP CONSTRAINT activityrequest_pkey;
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
DROP TABLE public.validationperiod_sci;
DROP TABLE public.validationperiod_prj;
DROP SEQUENCE public.validationperiod_id_seq;
DROP TABLE public.validationperiod_adm;
DROP TABLE public.validationperiod;
DROP SEQUENCE public.useraccessdefinition_id_seq;
DROP TABLE public.useraccessdefinition;
DROP SEQUENCE public.user_role_id_seq;
DROP TABLE public.user_role;
DROP SEQUENCE public.typedocument_id_seq;
DROP TABLE public.typedocument;
DROP SEQUENCE public.tva_id_seq;
DROP TABLE public.tva;
DROP TABLE public.timesheetsby;
DROP SEQUENCE public.timesheet_id_seq;
DROP TABLE public.timesheet;
DROP TABLE public.role_privilege;
DROP SEQUENCE public.role_id_seq;
DROP SEQUENCE public.referent_id_seq;
DROP TABLE public.referent;
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
DROP SEQUENCE public.organizationtype_id_seq;
DROP TABLE public.organizationtype;
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
DROP SEQUENCE public.activityrequestfollow_id_seq;
DROP TABLE public.activityrequestfollow;
DROP SEQUENCE public.activityrequest_id_seq;
DROP TABLE public.activityrequest;
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


--
-- Name: ProjectRemoveClone(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public."ProjectRemoveClone"() RETURNS void
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

CREATE FUNCTION public.activity_num_auto(activity_id integer) RETURNS text
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
    SELECT MAX(oscarNum) INTO last_num FROM activity WHERE oscarnum LIKE year || (separator ||'%');
    
    IF last_num IS NULL THEN
        counter_val := 0;
    ELSE
        counter_val := substring(last_num FROM (5 + char_length(separator)) FOR 5)::int;
    END IF;

    counter_val := counter_val + 1;

    num := CONCAT(year, separator, to_char(counter_val, 'fm00000'));

    UPDATE activity SET oscarNum = num WHERE id = activity_id;

    RETURN num;
END;
$$;


--
-- Name: oscar_activity_numauto(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.oscar_activity_numauto() RETURNS trigger
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

CREATE FUNCTION public.test() RETURNS integer
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
-- Name: activity; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.activity (
    id integer NOT NULL,
    project_id integer,
    type_id integer,
    centaureid character varying(128) DEFAULT NULL::character varying,
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
    oscarnum character varying(20) DEFAULT NULL::character varying,
    timesheetformat character varying(255) DEFAULT 'none'::character varying NOT NULL,
    numbers text,
    financialimpact character varying(32) DEFAULT 'Recette'::character varying NOT NULL,
    fraisdegestion double precision,
    notefinanciere text,
    assiettesubventionnable double precision
);


--
-- Name: COLUMN activity.numbers; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.activity.numbers IS '(DC2Type:object)';


--
-- Name: activity_discipline; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.activity_discipline (
    activity_id integer NOT NULL,
    discipline_id integer NOT NULL
);


--
-- Name: activity_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.activity_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: activitydate; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.activitydate (
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
    deletedby_id integer,
    finished integer,
    datefinish date,
    finishedby character varying(255) DEFAULT NULL::character varying
);


--
-- Name: activitydate_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.activitydate_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: activityorganization; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.activityorganization (
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

CREATE SEQUENCE public.activityorganization_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: activitypayment; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.activitypayment (
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

CREATE SEQUENCE public.activitypayment_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: activityperson; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.activityperson (
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

CREATE SEQUENCE public.activityperson_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: activityrequest; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.activityrequest (
    id integer NOT NULL,
    label character varying(255) DEFAULT NULL::character varying,
    description text,
    amount double precision,
    datestart date,
    dateend date,
    files text,
    status integer,
    datecreated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    dateupdated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    datedeleted timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    createdby_id integer,
    updatedby_id integer,
    deletedby_id integer,
    organisation_id integer
);


--
-- Name: COLUMN activityrequest.files; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.activityrequest.files IS '(DC2Type:array)';


--
-- Name: activityrequest_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.activityrequest_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: activityrequestfollow; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.activityrequestfollow (
    id integer NOT NULL,
    description text,
    status integer,
    datecreated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    dateupdated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    datedeleted timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    activityrequest_id integer,
    createdby_id integer,
    updatedby_id integer,
    deletedby_id integer
);


--
-- Name: activityrequestfollow_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.activityrequestfollow_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: activitytype; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.activitytype (
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

CREATE SEQUENCE public.activitytype_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: administrativedocument; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.administrativedocument (
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

CREATE SEQUENCE public.administrativedocument_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: authentification; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.authentification (
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

COMMENT ON COLUMN public.authentification.settings IS '(DC2Type:array)';


--
-- Name: authentification_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.authentification_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: authentification_role; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.authentification_role (
    authentification_id integer NOT NULL,
    role_id integer NOT NULL
);


--
-- Name: categorie_privilege; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.categorie_privilege (
    id integer NOT NULL,
    code character varying(150) NOT NULL,
    libelle character varying(200) NOT NULL,
    ordre integer
);


--
-- Name: categorie_privilege_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.categorie_privilege_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: contractdocument; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.contractdocument (
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

CREATE SEQUENCE public.contractdocument_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: contracttype; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.contracttype (
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

CREATE SEQUENCE public.contracttype_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: currency; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.currency (
    id integer NOT NULL,
    status integer,
    datecreated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    dateupdated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    datedeleted timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    createdby_id integer,
    updatedby_id integer,
    deletedby_id integer,
    label character varying(20) DEFAULT NULL::character varying NOT NULL,
    symbol character varying(4) DEFAULT NULL::character varying NOT NULL,
    rate double precision NOT NULL
);


--
-- Name: currency_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.currency_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: datetype; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.datetype (
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
    recursivity character varying(255) DEFAULT NULL::character varying,
    finishable boolean DEFAULT false NOT NULL
);


--
-- Name: datetype_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.datetype_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: discipline; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.discipline (
    id integer NOT NULL,
    label character varying(128) NOT NULL,
    centaureid character varying(10) DEFAULT NULL::character varying
);


--
-- Name: discipline_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.discipline_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: grantsource_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.grantsource_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: logactivity; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.logactivity (
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

COMMENT ON COLUMN public.logactivity.datas IS '(DC2Type:object)';


--
-- Name: logactivity_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.logactivity_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: notification; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.notification (
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

COMMENT ON COLUMN public.notification.datas IS '(DC2Type:object)';


--
-- Name: notification_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.notification_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: notificationperson; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.notificationperson (
    id integer NOT NULL,
    notification_id integer,
    person_id integer,
    read timestamp(0) without time zone DEFAULT NULL::timestamp without time zone
);


--
-- Name: notificationperson_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.notificationperson_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: organization; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.organization (
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
    connectors text,
    typeobj_id integer
);


--
-- Name: COLUMN organization.connectors; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.organization.connectors IS '(DC2Type:object)';


--
-- Name: organization_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.organization_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: organization_role; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.organization_role (
    id integer NOT NULL,
    role_id character varying(255) NOT NULL,
    description character varying(255) DEFAULT NULL::character varying,
    principal boolean DEFAULT false NOT NULL
);


--
-- Name: organization_role_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.organization_role_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: organizationperson; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.organizationperson (
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
    roleobj_id integer,
    origin character varying(255) DEFAULT NULL::character varying
);


--
-- Name: organizationperson_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.organizationperson_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: organizationrole; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.organizationrole (
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

CREATE SEQUENCE public.organizationrole_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: organizationtype; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.organizationtype (
    id integer NOT NULL,
    root_id integer,
    label character varying(255) DEFAULT NULL::character varying,
    description character varying(255) DEFAULT NULL::character varying,
    status integer,
    datecreated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    dateupdated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    datedeleted timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    createdby_id integer,
    updatedby_id integer,
    deletedby_id integer
);


--
-- Name: organizationtype_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.organizationtype_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: person; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.person (
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
    ldapmemberof text,
    customsettings text,
    foo character varying(255) DEFAULT NULL::character varying,
    schedulekey character varying(255) DEFAULT NULL::character varying
);


--
-- Name: COLUMN person.centaureid; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.person.centaureid IS '(DC2Type:simple_array)';


--
-- Name: COLUMN person.connectors; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.person.connectors IS '(DC2Type:object)';


--
-- Name: COLUMN person.ldapmemberof; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.person.ldapmemberof IS '(DC2Type:array)';


--
-- Name: person_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.person_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: privilege; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.privilege (
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

CREATE SEQUENCE public.privilege_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: project; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.project (
    id integer NOT NULL,
    centaureid character varying(10) DEFAULT NULL::character varying,
    code character varying(48) DEFAULT NULL::character varying,
    eotp character varying(64) DEFAULT NULL::character varying,
    composanteprincipal character varying(32) DEFAULT NULL::character varying,
    acronym character varying(255),
    label character varying(255) NOT NULL,
    description text,
    datecreated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    dateupdated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    datevalidated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone
);


--
-- Name: project_discipline; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.project_discipline (
    project_id integer NOT NULL,
    discipline_id integer NOT NULL
);


--
-- Name: project_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.project_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: projectgrant_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.projectgrant_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: projectmember; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.projectmember (
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

CREATE SEQUENCE public.projectmember_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: projectpartner; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.projectpartner (
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

CREATE SEQUENCE public.projectpartner_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: referent; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.referent (
    id integer NOT NULL,
    referent_id integer,
    person_id integer,
    datestart timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    dateend timestamp(0) without time zone DEFAULT NULL::timestamp without time zone
);


--
-- Name: referent_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.referent_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: role_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.role_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: role_privilege; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.role_privilege (
    privilege_id integer NOT NULL,
    role_id integer NOT NULL
);


--
-- Name: timesheet; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.timesheet (
    id integer NOT NULL,
    workpackage_id integer,
    person_id integer,
    datefrom timestamp(0) without time zone NOT NULL,
    dateto timestamp(0) without time zone NOT NULL,
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
    sendby character varying(255) DEFAULT NULL::character varying,
    icsuid text,
    icsfileuid text,
    icsfilename text,
    icsfiledateadded timestamp(0) without time zone,
    datesync timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    syncid character varying(255) DEFAULT NULL::character varying,
    validationperiod_id integer
);


--
-- Name: timesheet_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.timesheet_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: timesheetsby; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.timesheetsby (
    person_id integer NOT NULL,
    usurpation_person_id integer NOT NULL
);


--
-- Name: tva; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.tva (
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

CREATE SEQUENCE public.tva_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: typedocument; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.typedocument (
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

CREATE SEQUENCE public.typedocument_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: user_role; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.user_role (
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

CREATE SEQUENCE public.user_role_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: useraccessdefinition; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.useraccessdefinition (
    id integer NOT NULL,
    context character varying(200) NOT NULL,
    label character varying(200) NOT NULL,
    description character varying(200) DEFAULT NULL::character varying,
    key character varying(200) NOT NULL
);


--
-- Name: useraccessdefinition_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.useraccessdefinition_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: validationperiod; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.validationperiod (
    id integer NOT NULL,
    declarer_id integer,
    object character varying(255) NOT NULL,
    objectgroup character varying(255) NOT NULL,
    object_id character varying(255) NOT NULL,
    month integer NOT NULL,
    year integer NOT NULL,
    datesend date,
    log text,
    validationactivityat date,
    validationactivityby character varying(255) DEFAULT NULL::character varying,
    validationactivitybyid integer,
    validationactivitymessage text,
    validationsciat date,
    validationsciby character varying(255) DEFAULT NULL::character varying,
    validationscibyid integer,
    validationscimessage text,
    validationadmat date,
    validationadmby character varying(255) DEFAULT NULL::character varying,
    validationadmbyid integer,
    validationadmmessage text,
    rejectactivityat date,
    rejectactivityby character varying(255) DEFAULT NULL::character varying,
    rejectactivitybyid integer,
    rejectactivitymessage text,
    rejectsciat date,
    rejectsciby character varying(255) DEFAULT NULL::character varying,
    rejectscibyid integer,
    rejectscimessage text,
    rejectadmat date,
    rejectadmby character varying(255) DEFAULT NULL::character varying,
    rejectadmbyid integer,
    rejectadmmessage text,
    schedule text,
    status character varying(255) NOT NULL
);


--
-- Name: validationperiod_adm; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.validationperiod_adm (
    validationperiod_id integer NOT NULL,
    person_id integer NOT NULL
);


--
-- Name: validationperiod_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.validationperiod_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: validationperiod_prj; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.validationperiod_prj (
    validationperiod_id integer NOT NULL,
    person_id integer NOT NULL
);


--
-- Name: validationperiod_sci; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.validationperiod_sci (
    validationperiod_id integer NOT NULL,
    person_id integer NOT NULL
);


--
-- Name: workpackage; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.workpackage (
    id integer NOT NULL,
    activity_id integer,
    status integer,
    datecreated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    dateupdated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    datedeleted timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    createdby_id integer,
    updatedby_id integer,
    deletedby_id integer,
    code character varying(255) DEFAULT NULL::character varying NOT NULL,
    label character varying(255) NOT NULL,
    description text,
    datestart date,
    dateend date
);


--
-- Name: workpackage_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.workpackage_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: workpackageperson; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.workpackageperson (
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

CREATE SEQUENCE public.workpackageperson_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Data for Name: activity; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.activity (id, project_id, type_id, centaureid, centaurenumconvention, codeeotp, label, description, hassheet, duration, justifyworkingtime, justifycost, amount, datestart, dateend, datesigned, dateopened, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id, activitytype_id, currency_id, tva_id, oscarid, oscarnum, timesheetformat, numbers, financialimpact, fraisdegestion, notefinanciere, assiettesubventionnable) FROM stdin;
\.


--
-- Data for Name: activity_discipline; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.activity_discipline (activity_id, discipline_id) FROM stdin;
\.


--
-- Data for Name: activitydate; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.activitydate (id, type_id, activity_id, datestart, comment, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id, finished, datefinish, finishedby) FROM stdin;
\.


--
-- Data for Name: activityorganization; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.activityorganization (id, organization_id, activity_id, main, role, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id, datestart, dateend, roleobj_id) FROM stdin;
\.


--
-- Data for Name: activitypayment; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.activitypayment (id, activity_id, currency_id, datepayment, comment, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id, amount, rate, codetransaction, datepredicted) FROM stdin;
\.


--
-- Data for Name: activityperson; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.activityperson (id, person_id, activity_id, main, role, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id, datestart, dateend, roleobj_id) FROM stdin;
\.


--
-- Data for Name: activityrequest; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.activityrequest (id, label, description, amount, datestart, dateend, files, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id, organisation_id) FROM stdin;
\.


--
-- Data for Name: activityrequestfollow; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.activityrequestfollow (id, description, status, datecreated, dateupdated, datedeleted, activityrequest_id, createdby_id, updatedby_id, deletedby_id) FROM stdin;
\.


--
-- Data for Name: activitytype; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.activitytype (id, lft, rgt, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id, label, description, nature, centaureid) FROM stdin;
411	2	3	1	2016-03-14 12:21:16	\N	\N	\N	\N	\N	Accords cadre		0	\N
441	11	12	1	2016-03-14 13:07:09	\N	\N	\N	\N	\N	Cession de droit d'auteur		0	\N
439	9	10	1	2016-03-14 13:06:25	\N	\N	\N	\N	\N	Accord de copropriété avec exploitation		0	\N
450	13	14	1	2016-03-14 13:13:08	\N	\N	\N	\N	\N	Cession de brevet		0	\N
437	5	6	1	2016-03-14 13:05:25	\N	\N	\N	\N	\N	Accord de confidentialité		0	\N
482	95	96	1	2018-02-05 15:48:01	\N	\N	\N	\N	\N	ERANET - JPI		0	\N
451	15	16	1	2016-03-14 13:13:33	\N	\N	\N	\N	\N	Cession de logiciel		0	\N
438	7	8	1	2016-03-14 13:05:59	\N	\N	\N	\N	\N	Accord de copropriété		0	\N
474	17	18	1	2016-03-14 13:24:11	\N	\N	\N	\N	\N	Contrat de licence (brevet)		0	\N
452	19	20	1	2016-03-14 13:14:02	\N	\N	\N	\N	\N	Cession de quotes parts de brevet		0	\N
453	21	22	1	2016-03-14 13:14:17	\N	\N	\N	\N	\N	Contrat de transfert de Savoir-Faire		0	\N
455	23	24	1	2016-03-14 13:15:36	\N	\N	\N	\N	\N	Contrat d'édition		0	\N
458	25	26	1	2016-03-14 13:17:50	\N	\N	\N	\N	\N	Concours scientifique		0	\N
459	27	28	1	2016-03-14 13:18:13	\N	\N	\N	\N	\N	Convention de mise en délégation		0	\N
473	29	30	1	2016-03-14 13:23:55	\N	\N	\N	\N	\N	Contrat de licence (logiciel)		0	\N
476	91	92	1	2016-03-14 13:25:18	\N	\N	\N	\N	\N	LIFE+		0	\N
462	69	70	1	2016-03-14 13:19:32	\N	\N	\N	\N	\N	FEAMP		0	\N
466	77	78	1	2016-03-14 13:21:19	\N	\N	\N	\N	\N	FP7 - Marie curie		0	\N
470	85	86	1	2016-03-14 13:22:23	\N	\N	\N	\N	\N	H2020		0	\N
418	60	61	1	2016-03-14 12:28:29	\N	\N	\N	\N	\N	BQR		0	\N
417	56	59	1	2016-03-14 12:27:57	\N	\N	\N	\N	\N	ANR                                                                                                                                                                                                                                                            		0	\N
446	57	58	1	2016-03-14 13:10:20	\N	\N	\N	\N	\N	Convention de subvention (ANR)		0	\N
412	4	33	1	2016-03-14 12:21:48	\N	\N	\N	\N	\N	Valorisation		0	\N
414	42	43	1	2016-03-14 12:24:20	\N	\N	\N	\N	\N	Achat en commun                                                                                                                                                                                                                                                		0	\N
465	75	76	1	2016-03-14 13:20:37	\N	\N	\N	\N	\N	FP6 - tous programmes		0	\N
477	93	94	1	2016-03-14 13:28:10	\N	\N	\N	\N	\N	Interreg III		0	\N
463	71	72	1	2016-03-14 13:19:43	\N	\N	\N	\N	\N	FEDER - 2014 / 2020		0	\N
467	79	80	1	2016-03-14 13:21:30	\N	\N	\N	\N	\N	FP7 - Capacité		0	\N
471	87	88	1	2016-03-14 13:23:06	\N	\N	\N	\N	\N	Interreg IVA		0	\N
447	63	64	1	2016-03-14 13:11:02	\N	\N	\N	\N	\N	Autres financements UE		0	\N
444	47	48	1	2016-03-14 13:09:12	\N	\N	\N	\N	\N	Post-doc subvention autre que Région		0	\N
415	44	51	1	2016-03-14 12:26:44	\N	\N	\N	\N	\N	Allocations de recherche		0	\N
416	52	55	1	2016-03-14 12:26:59	\N	\N	\N	\N	\N	Aides OSEO		0	\N
445	49	50	1	2016-03-14 13:09:51	\N	\N	\N	\N	\N	Post-doc subvention Région		0	\N
442	53	54	1	2016-03-14 13:07:49	\N	\N	\N	\N	\N	Aides BPI		0	\N
468	81	82	1	2016-03-14 13:21:42	\N	\N	\N	\N	\N	FP7 - Coopération		0	\N
472	89	90	1	2016-03-14 13:23:17	\N	\N	\N	\N	\N	Interreg V		0	\N
475	31	32	1	2016-03-14 13:24:30	\N	\N	\N	\N	\N	Contrat de licence		0	\N
443	45	46	1	2016-03-14 13:08:30	\N	\N	\N	\N	\N	Thèse subvention autre que Région		0	\N
413	34	41	1	2016-03-14 12:24:04	\N	\N	\N	\N	\N	Recherche partenariale		0	\N
457	39	40	1	2016-03-14 13:17:09	\N	\N	\N	\N	\N	Contrats de transfert de matériel		0	\N
456	37	38	1	2016-03-14 13:16:30	\N	\N	\N	\N	\N	Contrats de mise à disposition		0	\N
440	35	36	1	2016-03-14 13:06:49	\N	\N	\N	\N	\N	Accord de consortium		0	\N
461	67	68	1	2016-03-14 13:19:03	\N	\N	\N	\N	\N	EUREKA		0	\N
464	73	74	1	2016-03-14 13:19:55	\N	\N	\N	\N	\N	FEDER - 2007 / 2013		0	\N
469	83	84	1	2016-03-14 13:21:55	\N	\N	\N	\N	\N	FP7 - Idées		0	\N
483	97	98	1	2018-02-05 15:48:53	\N	\N	\N	\N	\N	FEADER		0	\N
460	65	66	1	2016-03-14 13:18:43	\N	\N	\N	\N	\N	COST		0	\N
484	99	100	1	2018-02-05 15:50:18	\N	\N	\N	\N	\N	Direction Générale Europe		0	\N
419	62	103	1	2016-03-14 12:33:48	\N	\N	\N	\N	\N	Union Européenne		0	\N
1	1	162	\N	\N	\N	\N	\N	\N	\N	ROOT	\N	\N	\N
454	113	114	1	2016-03-14 13:15:14	\N	\N	\N	\N	\N	Contrat Accompagnement Cifre		0	\N
485	123	124	1	2018-04-06 10:05:37	\N	\N	\N	\N	\N	Accord de consortium EUROPE		0	\N
481	160	161	1	2018-01-24 10:53:22	\N	\N	\N	\N	\N	PIA		0	\N
425	122	127	1	2016-03-14 12:42:22	\N	\N	\N	\N	\N	Contrats européens		0	\N
449	111	112	1	2016-03-14 13:12:36	\N	\N	\N	\N	\N	Thèse subvention Région		0	\N
420	104	107	1	2016-03-14 12:36:17	\N	\N	\N	\N	\N	 Appels à projets pôles (FUI)		0	\N
448	105	106	1	2016-03-14 13:11:38	\N	\N	\N	\N	\N	Convention de subvention (FUI)		0	\N
421	108	109	1	2016-03-14 12:36:46	\N	\N	\N	\N	\N	International hors UE		0	\N
422	110	115	1	2016-03-14 12:38:18	\N	\N	\N	\N	\N	Thèse		0	\N
423	116	117	1	2016-03-14 12:39:54	\N	\N	\N	\N	\N	Collaboration recherche		0	\N
424	118	121	1	2016-03-14 12:41:35	\N	\N	\N	\N	\N	Relations internationales		0	\N
410	150	151	1	2017-10-10 15:35:16	\N	\N	\N	\N	\N	Mandat		0	\N
478	154	155	1	2017-10-24 09:58:46	\N	\N	\N	\N	\N	GIP/GIS		0	\N
479	156	157	1	2017-11-30 11:10:19	\N	\N	\N	\N	\N	 CPER		0	\N
480	158	159	1	2017-11-30 11:10:41	\N	\N	\N	\N	\N	CPIER		0	\N
427	130	131	1	2016-03-14 12:49:38	\N	\N	\N	\N	\N	Location		0	\N
428	132	133	1	2016-03-14 12:50:13	\N	\N	\N	\N	\N	Maintenance		0	\N
429	134	135	1	2016-03-14 12:51:24	\N	\N	\N	\N	\N	Mise à disposition de matériel		0	\N
431	138	139	1	2016-03-14 12:56:10	\N	\N	\N	\N	\N	Transfert de financement		0	\N
409	119	120	1	2016-10-20 10:56:40	\N	\N	\N	\N	\N	LIA (Laboratoire International Associé)		0	\N
436	148	149	1	2016-03-14 13:04:13	\N	\N	\N	\N	\N	Vente de matériel		0	\N
433	142	143	1	2016-03-14 12:59:33	\N	\N	\N	\N	\N	Conseils régionaux		0	\N
432	140	141	1	2016-03-14 12:57:56	\N	\N	\N	\N	\N	Autres collectivités territoriales		0	\N
434	144	145	1	2016-03-14 13:00:17	\N	\N	\N	\N	\N	Subventions		0	\N
435	146	147	1	2016-03-14 13:03:05	\N	\N	\N	\N	\N	Colloques		0	\N
430	136	137	1	2016-03-14 12:53:44	\N	\N	\N	\N	\N	Prestations		0	\N
426	128	129	1	2016-03-14 12:43:24	\N	\N	\N	\N	\N	Formation		0	\N
486	125	126	1	2018-04-06 10:06:01	\N	\N	\N	\N	\N	Avenant Contrat EUROPE		0	\N
487	101	102	1	2019-01-30 10:21:19	\N	\N	\N	\N	\N	ERASMUS+		0	\N
\.


--
-- Data for Name: administrativedocument; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.administrativedocument (id, person_id, dateupdoad, path, information, filetypemime, filesize, filename, version, status) FROM stdin;
\.


--
-- Data for Name: categorie_privilege; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.categorie_privilege (id, code, libelle, ordre) FROM stdin;
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
-- Data for Name: contractdocument; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.contractdocument (id, grant_id, person_id, dateupdoad, path, information, centaureid, filetypemime, filesize, filename, version, typedocument_id, status, datedeposit, datesend) FROM stdin;
\.


--
-- Data for Name: contracttype; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.contracttype (id, code, label, description, lft, rgt) FROM stdin;
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
-- Data for Name: currency; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.currency (id, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id, label, symbol, rate) FROM stdin;
1	1	2015-11-03 14:48:10	\N	\N	\N	\N	\N	Euro	€	1
4	1	2015-11-03 14:58:31	\N	\N	\N	\N	\N	Yens	¥	132.65100000000001
3	1	2015-11-03 14:57:20	\N	\N	\N	\N	\N	Livre	£	0.713300000000000045
2	1	2015-11-03 14:56:38	\N	\N	\N	\N	\N	Dollars	$	1.09600000000000009
\.


--
-- Data for Name: datetype; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.datetype (id, label, description, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id, facet, recursivity, finishable) FROM stdin;
1	Début du contrat		1	2016-01-27 14:20:48	\N	\N	\N	\N	\N	\N	\N	f
3	Début d'éligibilité des dépenses		1	2016-01-27 14:26:21	\N	\N	\N	\N	\N	\N	\N	f
4	Fin d'éligibilité des dépenses		1	2016-01-27 14:31:13	\N	\N	\N	\N	\N	\N	\N	f
5	Début d'éligibilité des dépenses de fonctionnement		1	2016-01-27 14:48:23	\N	\N	\N	\N	\N	\N	\N	f
6	Fin d'éligibilité des dépenses de fonctionnement		1	2016-01-27 14:48:46	\N	\N	\N	\N	\N	\N	\N	f
7	Dépôt de dossier		1	2016-01-27 14:49:01	\N	\N	\N	\N	\N	\N	\N	f
8	Signature		1	2016-01-27 14:49:14	\N	\N	\N	\N	\N	\N	\N	f
9	Première dépense	Déclenche la demande de l'avance (certificat de commencement du projet)	1	2016-01-27 14:49:42	\N	\N	\N	\N	\N	\N	\N	f
10	Démo		1	2016-02-03 18:11:45	\N	\N	\N	\N	\N	\N	\N	f
12	Rapport de thèse		1	2016-02-08 12:54:00	\N	\N	\N	\N	\N	Scientifique	\N	f
11	Publication d'article		1	2016-02-04 09:34:18	\N	\N	\N	\N	\N	Scientifique	\N	f
15	Rapport d'étude		1	2016-02-08 13:23:55	\N	\N	\N	\N	\N	Scientifique	\N	f
16	Prototype		1	2016-02-08 13:26:10	\N	\N	\N	\N	\N	Scientifique	\N	f
17	Logiciel		1	2016-02-08 13:29:37	\N	\N	\N	\N	\N	Scientifique	\N	f
18	Rapport de recherche		1	2016-02-08 13:30:10	\N	\N	\N	\N	\N	Scientifique	\N	f
19	Rapport final		1	2016-02-08 13:30:42	\N	\N	\N	\N	\N	Scientifique	\N	f
20	Rapport scientifique intermédiaire		1	2016-02-08 13:31:20	\N	\N	\N	\N	\N	Scientifique	\N	f
21	Soutenance de thèse		1	2016-02-08 13:31:40	\N	\N	\N	\N	\N	Scientifique	\N	f
52	Date de fin d'éligibilité des dépenses d'investissement		1	2016-04-07 12:58:56	\N	\N	\N	\N	\N	Financier	\N	f
54	Fin de période de rapport/reporting		1	2016-08-26 13:54:00	\N	\N	\N	\N	\N	Général	\N	f
55	Soumission du projet		1	2018-02-08 18:09:50	\N	\N	\N	\N	\N	Administratif		f
53	Rapport financier		1	2016-08-26 13:53:40	\N	\N	\N	\N	\N	Financier		t
\.


--
-- Data for Name: discipline; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.discipline (id, label, centaureid) FROM stdin;
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
-- Data for Name: notification; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.notification (id, dateeffective, datereal, datecreated, message, object, objectid, hash, context, serie, level, datas) FROM stdin;
\.


--
-- Data for Name: notificationperson; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.notificationperson (id, notification_id, person_id, read) FROM stdin;
\.


--
-- Data for Name: organization; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.organization (id, centaureid, shortname, fullname, code, email, url, description, street1, street2, street3, city, zipcode, phone, dateupdated, datecreated, dateend, datestart, status, datedeleted, createdby_id, updatedby_id, deletedby_id, ldapsupanncodeentite, country, sifacid, codepays, siret, bp, type, sifacgroup, sifacgroupid, numtvaca, connectors, typeobj_id) FROM stdin;
\.


--
-- Data for Name: organization_role; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.organization_role (id, role_id, description, principal) FROM stdin;
\.


--
-- Data for Name: organizationperson; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.organizationperson (id, person_id, organization_id, main, role, datestart, dateend, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id, roleobj_id, origin) FROM stdin;
\.


--
-- Data for Name: organizationrole; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.organizationrole (id, label, description, principal, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id) FROM stdin;
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
14	Tiers		f	1	2018-02-08 18:08:03	\N	\N	\N	\N	\N
15	Composante Responsable		f	1	2019-01-30 08:32:53	\N	\N	\N	\N	\N
\.


--
-- Data for Name: organizationtype; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.organizationtype (id, root_id, label, description, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id) FROM stdin;
1	\N	Association	\N	1	\N	\N	\N	\N	\N	\N
2	\N	Collectivité territoriale	\N	1	\N	\N	\N	\N	\N	\N
3	\N	Composante	\N	1	\N	\N	\N	\N	\N	\N
4	\N	Groupement d'intérêt économique	\N	1	\N	\N	\N	\N	\N	\N
5	\N	Inconnue	\N	1	\N	\N	\N	\N	\N	\N
6	\N	Institution	\N	1	\N	\N	\N	\N	\N	\N
7	\N	Laboratoire	\N	1	\N	\N	\N	\N	\N	\N
8	\N	Plateau technique	\N	1	\N	\N	\N	\N	\N	\N
9	\N	Société	\N	1	\N	\N	\N	\N	\N	\N
10	\N	Établissement publique	\N	1	\N	\N	\N	\N	\N	\N
\.


--
-- Data for Name: person; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.person (id, firstname, lastname, codeharpege, centaureid, codeldap, email, ldapstatus, ldapsitelocation, ldapaffectation, ldapdisabled, ldapfininscription, ladaplogin, phone, datesyncldap, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id, emailprive, harpegeinm, connectors, ldapmemberof, customsettings, foo, schedulekey) FROM stdin;
\.


--
-- Data for Name: privilege; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.privilege (id, categorie_id, code, libelle, ordre, root_id, spot) FROM stdin;
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
70	6	CONNECTOR_ACCESS	Peut exécuter la synchronisation des données	\N	\N	7
69	2	TIMESHEET_USURPATION	Peut remplir les feuilles de temps des déclarants d'une activité	\N	\N	7
72	2	NOTIFICATIONS_SHOW	Peut voir les notifications planifiées dans la fiche activité	\N	\N	7
74	6	NOTIFICATION_PERSON	Peut notifier manuellement un personne	\N	\N	7
75	2	PERSON_ACCESS	Voir les personnes qui ont la vision sur l'activité	\N	\N	7
76	3	VIEW_TIMESHEET	Peut voir les feuilles de temps de n'importe quelle personne	\N	\N	7
78	2	TIMESHEET_VIEW	Voir les feuilles de temps	\N	\N	7
79	6	ACTIVITYTYPE_MANAGE	Configurer les types d'activités disponibles	\N	\N	7
80	6	MILESTONETYPE_MANAGE	Configurer les types de jalons disponibles	\N	\N	7
81	6	ORGANIZATIONTYPE_MANAGE	Configurer les types d'organisation disponibles	\N	\N	7
82	6	SEARCH_BUILD	Peut lancer la reconstruction de l'index de recherche des activités	\N	\N	7
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
29	2	STATUS_OFF	Peut modifier le statut vers "Désactivé"	\N	\N	4
36	3	SHOW	Voir la fiche d'une personne	\N	\N	4
37	3	EDIT	Modifier la fiche d'une personne	\N	36	4
41	4	SHOW	Voir la fiche d'une organisation	\N	\N	4
42	4	EDIT	Modifier la fiche d'une organisation	\N	41	4
14	2	PERSON_MANAGE	Gestion des membres d'une activité	\N	30	7
51	6	MENU_ADMIN	Accès au menu d'administration	\N	\N	4
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
66	7	ROLEORGA_EDITION	Configurer les rôles des organisations	\N	65	4
73	2	NOTIFICATIONS_GENERATE	Peut regénérer manuellement les notifications d'une activité	\N	72	7
67	2	TIMESHEET_VALIDATE_SCI	Validation scientifique des feuilles de temps	\N	78	7
68	2	TIMESHEET_VALIDATE_ADM	Validation administrative des feuilles de temps	\N	78	7
77	2	MILESTONE_PROGRESSION	Peut gérer l'état d'avancement des jalons	\N	22	7
83	6	DISCIPLINE_MANAGE	Configurer les disciplines disponibles pour les activités	\N	\N	7
38	3	SYNC_LDAP	Synchroniser les données depuis les connecteurs	\N	36	4
43	4	SYNC_LDAP	Synchroniser les données avec les connecteurs	\N	41	4
88	6	VALIDATION_MANAGE	Peut gérer et modifier l'état des déclarations envoyées	\N	\N	7
84	3	MANAGE_SCHEDULE	Peut  modifier et valider la répartition horaire d'une personne	\N	36	7
85	3	SHOW_SCHEDULE	Peut  voir la répartition horaire d'une personne	\N	36	7
86	4	DELETE	Autorise la suppression définitive d'une organisation	\N	40	4
87	2	TIMESHEET_VALIDATE_ACTIVITY	Validation niveau activité des feuilles de temps	\N	78	7
89	2	REQUEST	Faire une demande d'activité	\N	\N	4
90	6	TVA_MANAGE	Configurer les TVAs disponibles	\N	\N	7
96	2	REQUEST_MANAGE	Traiter les demandes d'activité	\N	\N	4
97	2	REQUEST_ADMIN	Administrer toutes les demandes d'activité	\N	\N	4
101	3	FEED_TIMESHEET	Peut compléter les feuilles de temps de n'importe quel déclarant	\N	\N	7
\.


--
-- Data for Name: project; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.project (id, centaureid, code, eotp, composanteprincipal, acronym, label, description, datecreated, dateupdated, datevalidated) FROM stdin;
\.


--
-- Data for Name: project_discipline; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.project_discipline (project_id, discipline_id) FROM stdin;
\.


--
-- Data for Name: projectmember; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.projectmember (id, project_id, person_id, role, datestart, dateend, main, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id, roleobj_id) FROM stdin;
\.


--
-- Data for Name: projectpartner; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.projectpartner (id, project_id, organization_id, datestart, dateend, main, role, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id, roleobj_id) FROM stdin;
\.


--
-- Data for Name: referent; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.referent (id, referent_id, person_id, datestart, dateend) FROM stdin;
\.


--
-- Data for Name: role_privilege; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.role_privilege (privilege_id, role_id) FROM stdin;
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
1	11
32	11
35	11
18	11
30	11
56	8
59	1
51	9
59	9
58	10
58	15
61	7
61	10
61	15
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
2	1
2	7
35	22
13	7
13	1
16	1
16	7
15	1
15	7
19	1
19	7
17	1
17	7
33	22
18	1
18	7
20	1
20	7
22	1
22	7
23	1
23	7
3	22
24	1
24	7
25	1
25	7
26	1
26	7
36	22
27	7
27	1
28	1
53	22
41	22
58	22
70	1
69	1
68	22
75	1
72	1
76	7
76	9
29	1
30	1
30	7
31	1
31	7
3	1
3	7
32	7
32	1
33	7
33	1
34	7
34	1
35	7
35	1
36	7
36	1
37	7
37	1
38	1
41	7
41	1
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
3	11
54	11
56	11
56	15
58	1
60	1
58	9
61	1
58	7
58	8
58	11
59	7
61	8
61	11
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
68	21
73	1
76	1
76	8
76	15
74	1
79	1
80	1
81	1
82	1
78	1
67	23
67	24
78	22
78	10
78	21
78	24
78	23
78	7
78	20
78	8
78	9
78	15
87	10
89	22
89	21
83	1
62	1
89	1
96	1
89	6
96	20
96	7
97	1
85	1
84	1
88	1
101	1
77	1
90	1
42	7
86	1
\.


--
-- Data for Name: timesheet; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.timesheet (id, workpackage_id, person_id, datefrom, dateto, comment, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id, activity_id, label, sendby, icsuid, icsfileuid, icsfilename, icsfiledateadded, datesync, syncid, validationperiod_id) FROM stdin;
\.


--
-- Data for Name: timesheetsby; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.timesheetsby (person_id, usurpation_person_id) FROM stdin;
\.


--
-- Data for Name: tva; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.tva (id, label, rate, active, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id) FROM stdin;
1	Exonéré	0	t	1	\N	\N	\N	\N	\N	\N
2	Taux réduit (5,5%)	5.5	t	1	\N	\N	\N	\N	\N	\N
3	Taux normal (19,6%)	19.6000000000000014	t	1	\N	\N	\N	\N	\N	\N
4	Taux DOM-TOM	8.5	t	1	\N	\N	\N	\N	\N	\N
5	Taux réduit 7%	7	t	1	\N	\N	\N	\N	\N	\N
6	Taux normal 20%	20	t	1	\N	\N	\N	\N	\N	\N
7	Taux réduit 10%	10	f	1	\N	\N	\N	\N	\N	\N
\.


--
-- Data for Name: typedocument; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.typedocument (id, label, description, codecentaure, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id) FROM stdin;
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
-- Data for Name: user_role; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.user_role (id, parent_id, role_id, is_default, ldap_filter, spot, description, principal) FROM stdin;
1	\N	Administrateur	f	\N	4	\N	f
8	\N	Responsable RH	f	\N	6	\N	f
9	\N	Responsable financier	f	(memberOf=cn=projet_oscar_agence_comptable,ou=groups,dc=unicaen,dc=fr)	6	\N	f
11	\N	Ingénieur	f	\N	1	\N	f
15	\N	Responsable juridique	f	\N	6	\N	f
20	\N	Chargé de mission Europe	f	\N	3		t
10	\N	Responsable scientifique	f	\N	3		t
22	\N	Directeur de composante	f	\N	2	Contient les directeurs de composantes, directeurs de composantes adjoint, les administrateurs provisoires 	t
21	\N	Directeur de laboratoire	f	\N	2	Contient la liste des directeurs de laboratoires et assimilés (directeurs adjoints, directeurs temporaire, etc.)	t
24	\N	Gestionnaire recherche de laboratoire	f	\N	2		t
23	\N	Responsable administratif et gestionnaire de composante	f	\N	3	Les responsables administratifs et gestionnaires de composantes 	t
7	\N	Chargé de valorisation	f	(memberOf=cn=structure_dir-recherche-innov,ou=groups,dc=unicaen,dc=fr)	7		t
6	\N	user	t	\N	4	Rôle par défaut	f
\.


--
-- Data for Name: useraccessdefinition; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.useraccessdefinition (id, context, label, description, key) FROM stdin;
\.


--
-- Data for Name: validationperiod; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.validationperiod (id, declarer_id, object, objectgroup, object_id, month, year, datesend, log, validationactivityat, validationactivityby, validationactivitybyid, validationactivitymessage, validationsciat, validationsciby, validationscibyid, validationscimessage, validationadmat, validationadmby, validationadmbyid, validationadmmessage, rejectactivityat, rejectactivityby, rejectactivitybyid, rejectactivitymessage, rejectsciat, rejectsciby, rejectscibyid, rejectscimessage, rejectadmat, rejectadmby, rejectadmbyid, rejectadmmessage, schedule, status) FROM stdin;
\.


--
-- Data for Name: validationperiod_adm; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.validationperiod_adm (validationperiod_id, person_id) FROM stdin;
\.


--
-- Data for Name: validationperiod_prj; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.validationperiod_prj (validationperiod_id, person_id) FROM stdin;
\.


--
-- Data for Name: validationperiod_sci; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.validationperiod_sci (validationperiod_id, person_id) FROM stdin;
\.


--
-- Data for Name: workpackage; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.workpackage (id, activity_id, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id, code, label, description, datestart, dateend) FROM stdin;
\.


--
-- Data for Name: workpackageperson; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.workpackageperson (id, person_id, duration, status, datecreated, dateupdated, datedeleted, workpackage_id, createdby_id, updatedby_id, deletedby_id) FROM stdin;
\.


--
-- Name: activity_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.activity_id_seq', 1, false);


--
-- Name: activitydate_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.activitydate_id_seq', 1, false);


--
-- Name: activityorganization_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.activityorganization_id_seq', 1, false);


--
-- Name: activitypayment_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.activitypayment_id_seq', 1, false);


--
-- Name: activityperson_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.activityperson_id_seq', 1, false);


--
-- Name: activityrequest_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.activityrequest_id_seq', 79, true);


--
-- Name: activityrequestfollow_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.activityrequestfollow_id_seq', 54, true);


--
-- Name: activitytype_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.activitytype_id_seq', 488, false);


--
-- Name: administrativedocument_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.administrativedocument_id_seq', 1, false);


--
-- Name: authentification_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.authentification_id_seq', 1, true);


--
-- Name: categorie_privilege_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.categorie_privilege_id_seq', 1, false);


--
-- Name: contractdocument_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.contractdocument_id_seq', 1, false);


--
-- Name: contracttype_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.contracttype_id_seq', 231, true);


--
-- Name: currency_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.currency_id_seq', 5, false);


--
-- Name: datetype_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.datetype_id_seq', 57, false);


--
-- Name: discipline_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.discipline_id_seq', 120, false);


--
-- Name: grantsource_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.grantsource_id_seq', 33, true);


--
-- Name: logactivity_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.logactivity_id_seq', 13, true);


--
-- Name: notification_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.notification_id_seq', 1, false);


--
-- Name: notificationperson_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.notificationperson_id_seq', 1, false);


--
-- Name: organization_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.organization_id_seq', 1, false);


--
-- Name: organization_role_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.organization_role_id_seq', 1, false);


--
-- Name: organizationperson_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.organizationperson_id_seq', 1, false);


--
-- Name: organizationrole_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.organizationrole_id_seq', 16, false);


--
-- Name: organizationtype_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.organizationtype_id_seq', 10, true);


--
-- Name: person_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.person_id_seq', 1, false);


--
-- Name: privilege_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.privilege_id_seq', 102, false);


--
-- Name: project_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.project_id_seq', 1, false);


--
-- Name: projectgrant_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.projectgrant_id_seq', 8654, true);


--
-- Name: projectmember_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.projectmember_id_seq', 10723, true);


--
-- Name: projectpartner_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.projectpartner_id_seq', 60614, true);


--
-- Name: referent_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.referent_id_seq', 1, true);


--
-- Name: role_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.role_id_seq', 1, false);


--
-- Name: timesheet_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.timesheet_id_seq', 7490, true);


--
-- Name: tva_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.tva_id_seq', 1, false);


--
-- Name: typedocument_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.typedocument_id_seq', 41, true);


--
-- Name: user_role_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.user_role_id_seq', 28, true);


--
-- Name: useraccessdefinition_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.useraccessdefinition_id_seq', 1, false);


--
-- Name: validationperiod_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.validationperiod_id_seq', 7, true);


--
-- Name: workpackage_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.workpackage_id_seq', 53, true);


--
-- Name: workpackageperson_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.workpackageperson_id_seq', 83, true);


--
-- Name: activity_discipline activity_discipline_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activity_discipline
    ADD CONSTRAINT activity_discipline_pkey PRIMARY KEY (activity_id, discipline_id);


--
-- Name: activity activity_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activity
    ADD CONSTRAINT activity_pkey PRIMARY KEY (id);


--
-- Name: activitydate activitydate_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activitydate
    ADD CONSTRAINT activitydate_pkey PRIMARY KEY (id);


--
-- Name: activityorganization activityorganization_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activityorganization
    ADD CONSTRAINT activityorganization_pkey PRIMARY KEY (id);


--
-- Name: activitypayment activitypayment_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activitypayment
    ADD CONSTRAINT activitypayment_pkey PRIMARY KEY (id);


--
-- Name: activityperson activityperson_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activityperson
    ADD CONSTRAINT activityperson_pkey PRIMARY KEY (id);


--
-- Name: activityrequest activityrequest_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activityrequest
    ADD CONSTRAINT activityrequest_pkey PRIMARY KEY (id);


--
-- Name: activityrequestfollow activityrequestfollow_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activityrequestfollow
    ADD CONSTRAINT activityrequestfollow_pkey PRIMARY KEY (id);


--
-- Name: activitytype activitytype_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activitytype
    ADD CONSTRAINT activitytype_pkey PRIMARY KEY (id);


--
-- Name: administrativedocument administrativedocument_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.administrativedocument
    ADD CONSTRAINT administrativedocument_pkey PRIMARY KEY (id);


--
-- Name: authentification authentification_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.authentification
    ADD CONSTRAINT authentification_pkey PRIMARY KEY (id);


--
-- Name: authentification_role authentification_role_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.authentification_role
    ADD CONSTRAINT authentification_role_pkey PRIMARY KEY (authentification_id, role_id);


--
-- Name: categorie_privilege categorie_privilege_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.categorie_privilege
    ADD CONSTRAINT categorie_privilege_pkey PRIMARY KEY (id);


--
-- Name: contractdocument contractdocument_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.contractdocument
    ADD CONSTRAINT contractdocument_pkey PRIMARY KEY (id);


--
-- Name: contracttype contracttype_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.contracttype
    ADD CONSTRAINT contracttype_pkey PRIMARY KEY (id);


--
-- Name: currency currency_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.currency
    ADD CONSTRAINT currency_pkey PRIMARY KEY (id);


--
-- Name: datetype datetype_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.datetype
    ADD CONSTRAINT datetype_pkey PRIMARY KEY (id);


--
-- Name: discipline discipline_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.discipline
    ADD CONSTRAINT discipline_pkey PRIMARY KEY (id);


--
-- Name: logactivity logactivity_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.logactivity
    ADD CONSTRAINT logactivity_pkey PRIMARY KEY (id);


--
-- Name: notification notification_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notification
    ADD CONSTRAINT notification_pkey PRIMARY KEY (id);


--
-- Name: notificationperson notificationperson_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notificationperson
    ADD CONSTRAINT notificationperson_pkey PRIMARY KEY (id);


--
-- Name: organization organization_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.organization
    ADD CONSTRAINT organization_pkey PRIMARY KEY (id);


--
-- Name: organization_role organization_role_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.organization_role
    ADD CONSTRAINT organization_role_pkey PRIMARY KEY (id);


--
-- Name: organizationperson organizationperson_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.organizationperson
    ADD CONSTRAINT organizationperson_pkey PRIMARY KEY (id);


--
-- Name: organizationrole organizationrole_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.organizationrole
    ADD CONSTRAINT organizationrole_pkey PRIMARY KEY (id);


--
-- Name: organizationtype organizationtype_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.organizationtype
    ADD CONSTRAINT organizationtype_pkey PRIMARY KEY (id);


--
-- Name: person person_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.person
    ADD CONSTRAINT person_pkey PRIMARY KEY (id);


--
-- Name: privilege privilege_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.privilege
    ADD CONSTRAINT privilege_pkey PRIMARY KEY (id);


--
-- Name: project_discipline project_discipline_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.project_discipline
    ADD CONSTRAINT project_discipline_pkey PRIMARY KEY (project_id, discipline_id);


--
-- Name: project project_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.project
    ADD CONSTRAINT project_pkey PRIMARY KEY (id);


--
-- Name: projectmember projectmember_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.projectmember
    ADD CONSTRAINT projectmember_pkey PRIMARY KEY (id);


--
-- Name: projectpartner projectpartner_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.projectpartner
    ADD CONSTRAINT projectpartner_pkey PRIMARY KEY (id);


--
-- Name: referent referent_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.referent
    ADD CONSTRAINT referent_pkey PRIMARY KEY (id);


--
-- Name: role_privilege role_privilege_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.role_privilege
    ADD CONSTRAINT role_privilege_pkey PRIMARY KEY (privilege_id, role_id);


--
-- Name: timesheet timesheet_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.timesheet
    ADD CONSTRAINT timesheet_pkey PRIMARY KEY (id);


--
-- Name: timesheetsby timesheetsby_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.timesheetsby
    ADD CONSTRAINT timesheetsby_pkey PRIMARY KEY (person_id, usurpation_person_id);


--
-- Name: tva tva_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tva
    ADD CONSTRAINT tva_pkey PRIMARY KEY (id);


--
-- Name: typedocument typedocument_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.typedocument
    ADD CONSTRAINT typedocument_pkey PRIMARY KEY (id);


--
-- Name: user_role user_role_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_role
    ADD CONSTRAINT user_role_pkey PRIMARY KEY (id);


--
-- Name: useraccessdefinition useraccessdefinition_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.useraccessdefinition
    ADD CONSTRAINT useraccessdefinition_pkey PRIMARY KEY (id);


--
-- Name: validationperiod_adm validationperiod_adm_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.validationperiod_adm
    ADD CONSTRAINT validationperiod_adm_pkey PRIMARY KEY (validationperiod_id, person_id);


--
-- Name: validationperiod validationperiod_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.validationperiod
    ADD CONSTRAINT validationperiod_pkey PRIMARY KEY (id);


--
-- Name: validationperiod_prj validationperiod_prj_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.validationperiod_prj
    ADD CONSTRAINT validationperiod_prj_pkey PRIMARY KEY (validationperiod_id, person_id);


--
-- Name: validationperiod_sci validationperiod_sci_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.validationperiod_sci
    ADD CONSTRAINT validationperiod_sci_pkey PRIMARY KEY (validationperiod_id, person_id);


--
-- Name: workpackage workpackage_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workpackage
    ADD CONSTRAINT workpackage_pkey PRIMARY KEY (id);


--
-- Name: workpackageperson workpackageperson_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workpackageperson
    ADD CONSTRAINT workpackageperson_pkey PRIMARY KEY (id);


--
-- Name: idx_1fde42e6217bbb47; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_1fde42e6217bbb47 ON public.validationperiod_sci USING btree (person_id);


--
-- Name: idx_1fde42e625e297e4; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_1fde42e625e297e4 ON public.validationperiod_sci USING btree (validationperiod_id);


--
-- Name: idx_205cd03781c06096; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_205cd03781c06096 ON public.activity_discipline USING btree (activity_id);


--
-- Name: idx_205cd037a5522701; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_205cd037a5522701 ON public.activity_discipline USING btree (discipline_id);


--
-- Name: idx_22ba6515217bbb47; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_22ba6515217bbb47 ON public.notificationperson USING btree (person_id);


--
-- Name: idx_22ba6515ef1a9d84; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_22ba6515ef1a9d84 ON public.notificationperson USING btree (notification_id);


--
-- Name: idx_29fdc4ce3174800f; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_29fdc4ce3174800f ON public.datetype USING btree (createdby_id);


--
-- Name: idx_29fdc4ce63d8c20e; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_29fdc4ce63d8c20e ON public.datetype USING btree (deletedby_id);


--
-- Name: idx_29fdc4ce65ff1aec; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_29fdc4ce65ff1aec ON public.datetype USING btree (updatedby_id);


--
-- Name: idx_2dcfc4c43174800f; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_2dcfc4c43174800f ON public.activitydate USING btree (createdby_id);


--
-- Name: idx_2dcfc4c463d8c20e; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_2dcfc4c463d8c20e ON public.activitydate USING btree (deletedby_id);


--
-- Name: idx_2dcfc4c465ff1aec; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_2dcfc4c465ff1aec ON public.activitydate USING btree (updatedby_id);


--
-- Name: idx_2dcfc4c481c06096; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_2dcfc4c481c06096 ON public.activitydate USING btree (activity_id);


--
-- Name: idx_2dcfc4c4c54c8c93; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_2dcfc4c4c54c8c93 ON public.activitydate USING btree (type_id);


--
-- Name: idx_2de8c6a3727aca70; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_2de8c6a3727aca70 ON public.user_role USING btree (parent_id);


--
-- Name: idx_3370d4403174800f; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_3370d4403174800f ON public.person USING btree (createdby_id);


--
-- Name: idx_3370d44063d8c20e; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_3370d44063d8c20e ON public.person USING btree (deletedby_id);


--
-- Name: idx_3370d44065ff1aec; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_3370d44065ff1aec ON public.person USING btree (updatedby_id);


--
-- Name: idx_34944573217bbb47; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_34944573217bbb47 ON public.timesheet USING btree (person_id);


--
-- Name: idx_349445733174800f; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_349445733174800f ON public.timesheet USING btree (createdby_id);


--
-- Name: idx_3494457363d8c20e; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_3494457363d8c20e ON public.timesheet USING btree (deletedby_id);


--
-- Name: idx_3494457365ff1aec; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_3494457365ff1aec ON public.timesheet USING btree (updatedby_id);


--
-- Name: idx_3494457381c06096; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_3494457381c06096 ON public.timesheet USING btree (activity_id);


--
-- Name: idx_34944573a7131547; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_34944573a7131547 ON public.timesheet USING btree (validationperiod_id);


--
-- Name: idx_34944573dbd8a2b7; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_34944573dbd8a2b7 ON public.timesheet USING btree (workpackage_id);


--
-- Name: idx_48506726217bbb47; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_48506726217bbb47 ON public.validationperiod_adm USING btree (person_id);


--
-- Name: idx_4850672625e297e4; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_4850672625e297e4 ON public.validationperiod_adm USING btree (validationperiod_id);


--
-- Name: idx_4a390fe8217bbb47; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_4a390fe8217bbb47 ON public.contractdocument USING btree (person_id);


--
-- Name: idx_4a390fe83bebd1bd; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_4a390fe83bebd1bd ON public.contractdocument USING btree (typedocument_id);


--
-- Name: idx_4a390fe85c0c89f3; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_4a390fe85c0c89f3 ON public.contractdocument USING btree (grant_id);


--
-- Name: idx_55026b0c166d1f9c; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_55026b0c166d1f9c ON public.activity USING btree (project_id);


--
-- Name: idx_55026b0c3174800f; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_55026b0c3174800f ON public.activity USING btree (createdby_id);


--
-- Name: idx_55026b0c38248176; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_55026b0c38248176 ON public.activity USING btree (currency_id);


--
-- Name: idx_55026b0c4d79775f; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_55026b0c4d79775f ON public.activity USING btree (tva_id);


--
-- Name: idx_55026b0c63d8c20e; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_55026b0c63d8c20e ON public.activity USING btree (deletedby_id);


--
-- Name: idx_55026b0c65ff1aec; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_55026b0c65ff1aec ON public.activity USING btree (updatedby_id);


--
-- Name: idx_55026b0ca1b4b28c; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_55026b0ca1b4b28c ON public.activity USING btree (activitytype_id);


--
-- Name: idx_55026b0cc54c8c93; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_55026b0cc54c8c93 ON public.activity USING btree (type_id);


--
-- Name: idx_5d5b51b9166d1f9c; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_5d5b51b9166d1f9c ON public.projectmember USING btree (project_id);


--
-- Name: idx_5d5b51b91c4132c1; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_5d5b51b91c4132c1 ON public.projectmember USING btree (roleobj_id);


--
-- Name: idx_5d5b51b9217bbb47; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_5d5b51b9217bbb47 ON public.projectmember USING btree (person_id);


--
-- Name: idx_5d5b51b93174800f; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_5d5b51b93174800f ON public.projectmember USING btree (createdby_id);


--
-- Name: idx_5d5b51b963d8c20e; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_5d5b51b963d8c20e ON public.projectmember USING btree (deletedby_id);


--
-- Name: idx_5d5b51b965ff1aec; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_5d5b51b965ff1aec ON public.projectmember USING btree (updatedby_id);


--
-- Name: idx_5dbdaf56d28043b; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_5dbdaf56d28043b ON public.authentification_role USING btree (authentification_id);


--
-- Name: idx_5dbdaf5d60322ac; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_5dbdaf5d60322ac ON public.authentification_role USING btree (role_id);


--
-- Name: idx_6547bd503174800f; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_6547bd503174800f ON public.typedocument USING btree (createdby_id);


--
-- Name: idx_6547bd5063d8c20e; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_6547bd5063d8c20e ON public.typedocument USING btree (deletedby_id);


--
-- Name: idx_6547bd5065ff1aec; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_6547bd5065ff1aec ON public.typedocument USING btree (updatedby_id);


--
-- Name: idx_6a2e76b71c4132c1; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_6a2e76b71c4132c1 ON public.activityperson USING btree (roleobj_id);


--
-- Name: idx_6a2e76b7217bbb47; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_6a2e76b7217bbb47 ON public.activityperson USING btree (person_id);


--
-- Name: idx_6a2e76b73174800f; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_6a2e76b73174800f ON public.activityperson USING btree (createdby_id);


--
-- Name: idx_6a2e76b763d8c20e; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_6a2e76b763d8c20e ON public.activityperson USING btree (deletedby_id);


--
-- Name: idx_6a2e76b765ff1aec; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_6a2e76b765ff1aec ON public.activityperson USING btree (updatedby_id);


--
-- Name: idx_6a2e76b781c06096; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_6a2e76b781c06096 ON public.activityperson USING btree (activity_id);


--
-- Name: idx_6a89662b1c4132c1; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_6a89662b1c4132c1 ON public.organizationperson USING btree (roleobj_id);


--
-- Name: idx_6a89662b217bbb47; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_6a89662b217bbb47 ON public.organizationperson USING btree (person_id);


--
-- Name: idx_6a89662b3174800f; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_6a89662b3174800f ON public.organizationperson USING btree (createdby_id);


--
-- Name: idx_6a89662b32c8a3de; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_6a89662b32c8a3de ON public.organizationperson USING btree (organization_id);


--
-- Name: idx_6a89662b63d8c20e; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_6a89662b63d8c20e ON public.organizationperson USING btree (deletedby_id);


--
-- Name: idx_6a89662b65ff1aec; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_6a89662b65ff1aec ON public.organizationperson USING btree (updatedby_id);


--
-- Name: idx_6d18950d166d1f9c; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_6d18950d166d1f9c ON public.project_discipline USING btree (project_id);


--
-- Name: idx_6d18950da5522701; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_6d18950da5522701 ON public.project_discipline USING btree (discipline_id);


--
-- Name: idx_79ced4aa3174800f; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_79ced4aa3174800f ON public.tva USING btree (createdby_id);


--
-- Name: idx_79ced4aa63d8c20e; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_79ced4aa63d8c20e ON public.tva USING btree (deletedby_id);


--
-- Name: idx_79ced4aa65ff1aec; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_79ced4aa65ff1aec ON public.tva USING btree (updatedby_id);


--
-- Name: idx_7c35c5733174800f; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_7c35c5733174800f ON public.organizationtype USING btree (createdby_id);


--
-- Name: idx_7c35c57363d8c20e; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_7c35c57363d8c20e ON public.organizationtype USING btree (deletedby_id);


--
-- Name: idx_7c35c57365ff1aec; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_7c35c57365ff1aec ON public.organizationtype USING btree (updatedby_id);


--
-- Name: idx_7c35c57379066886; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_7c35c57379066886 ON public.organizationtype USING btree (root_id);


--
-- Name: idx_7ecce3a217bbb47; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_7ecce3a217bbb47 ON public.referent USING btree (person_id);


--
-- Name: idx_7ecce3a35e47e35; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_7ecce3a35e47e35 ON public.referent USING btree (referent_id);


--
-- Name: idx_8115848c3174800f; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_8115848c3174800f ON public.activitypayment USING btree (createdby_id);


--
-- Name: idx_8115848c38248176; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_8115848c38248176 ON public.activitypayment USING btree (currency_id);


--
-- Name: idx_8115848c63d8c20e; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_8115848c63d8c20e ON public.activitypayment USING btree (deletedby_id);


--
-- Name: idx_8115848c65ff1aec; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_8115848c65ff1aec ON public.activitypayment USING btree (updatedby_id);


--
-- Name: idx_8115848c81c06096; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_8115848c81c06096 ON public.activitypayment USING btree (activity_id);


--
-- Name: idx_87209a8779066886; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_87209a8779066886 ON public.privilege USING btree (root_id);


--
-- Name: idx_87209a87bcf5e72d; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_87209a87bcf5e72d ON public.privilege USING btree (categorie_id);


--
-- Name: idx_8ffc688a217bbb47; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_8ffc688a217bbb47 ON public.timesheetsby USING btree (person_id);


--
-- Name: idx_8ffc688a241061bf; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_8ffc688a241061bf ON public.timesheetsby USING btree (usurpation_person_id);


--
-- Name: idx_9020ea693174800f; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_9020ea693174800f ON public.currency USING btree (createdby_id);


--
-- Name: idx_9020ea6963d8c20e; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_9020ea6963d8c20e ON public.currency USING btree (deletedby_id);


--
-- Name: idx_9020ea6965ff1aec; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_9020ea6965ff1aec ON public.currency USING btree (updatedby_id);


--
-- Name: idx_9310307d1c4132c1; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_9310307d1c4132c1 ON public.activityorganization USING btree (roleobj_id);


--
-- Name: idx_9310307d3174800f; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_9310307d3174800f ON public.activityorganization USING btree (createdby_id);


--
-- Name: idx_9310307d32c8a3de; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_9310307d32c8a3de ON public.activityorganization USING btree (organization_id);


--
-- Name: idx_9310307d63d8c20e; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_9310307d63d8c20e ON public.activityorganization USING btree (deletedby_id);


--
-- Name: idx_9310307d65ff1aec; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_9310307d65ff1aec ON public.activityorganization USING btree (updatedby_id);


--
-- Name: idx_9310307d81c06096; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_9310307d81c06096 ON public.activityorganization USING btree (activity_id);


--
-- Name: idx_a78218303174800f; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_a78218303174800f ON public.organizationrole USING btree (createdby_id);


--
-- Name: idx_a782183063d8c20e; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_a782183063d8c20e ON public.organizationrole USING btree (deletedby_id);


--
-- Name: idx_a782183065ff1aec; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_a782183065ff1aec ON public.organizationrole USING btree (updatedby_id);


--
-- Name: idx_b700890a3c21f464; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_b700890a3c21f464 ON public.validationperiod USING btree (declarer_id);


--
-- Name: idx_b8fa4973174800f; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_b8fa4973174800f ON public.activitytype USING btree (createdby_id);


--
-- Name: idx_b8fa49763d8c20e; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_b8fa49763d8c20e ON public.activitytype USING btree (deletedby_id);


--
-- Name: idx_b8fa49765ff1aec; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_b8fa49765ff1aec ON public.activitytype USING btree (updatedby_id);


--
-- Name: idx_c311ba72217bbb47; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_c311ba72217bbb47 ON public.administrativedocument USING btree (person_id);


--
-- Name: idx_c583f07f3174800f; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_c583f07f3174800f ON public.workpackage USING btree (createdby_id);


--
-- Name: idx_c583f07f63d8c20e; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_c583f07f63d8c20e ON public.workpackage USING btree (deletedby_id);


--
-- Name: idx_c583f07f65ff1aec; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_c583f07f65ff1aec ON public.workpackage USING btree (updatedby_id);


--
-- Name: idx_c583f07f81c06096; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_c583f07f81c06096 ON public.workpackage USING btree (activity_id);


--
-- Name: idx_cfe2df3a3174800f; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_cfe2df3a3174800f ON public.activityrequestfollow USING btree (createdby_id);


--
-- Name: idx_cfe2df3a63d8c20e; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_cfe2df3a63d8c20e ON public.activityrequestfollow USING btree (deletedby_id);


--
-- Name: idx_cfe2df3a65ff1aec; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_cfe2df3a65ff1aec ON public.activityrequestfollow USING btree (updatedby_id);


--
-- Name: idx_cfe2df3ae8fa3e0f; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_cfe2df3ae8fa3e0f ON public.activityrequestfollow USING btree (activityrequest_id);


--
-- Name: idx_d6d4495b32fb8aea; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_d6d4495b32fb8aea ON public.role_privilege USING btree (privilege_id);


--
-- Name: idx_d6d4495bd60322ac; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_d6d4495bd60322ac ON public.role_privilege USING btree (role_id);


--
-- Name: idx_d7488e15217bbb47; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_d7488e15217bbb47 ON public.validationperiod_prj USING btree (person_id);


--
-- Name: idx_d7488e1525e297e4; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_d7488e1525e297e4 ON public.validationperiod_prj USING btree (validationperiod_id);


--
-- Name: idx_d7aa8f1e3174800f; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_d7aa8f1e3174800f ON public.activityrequest USING btree (createdby_id);


--
-- Name: idx_d7aa8f1e63d8c20e; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_d7aa8f1e63d8c20e ON public.activityrequest USING btree (deletedby_id);


--
-- Name: idx_d7aa8f1e65ff1aec; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_d7aa8f1e65ff1aec ON public.activityrequest USING btree (updatedby_id);


--
-- Name: idx_d7aa8f1e9e6b1585; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_d7aa8f1e9e6b1585 ON public.activityrequest USING btree (organisation_id);


--
-- Name: idx_d9dfb8843174800f; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_d9dfb8843174800f ON public.organization USING btree (createdby_id);


--
-- Name: idx_d9dfb88463d8c20e; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_d9dfb88463d8c20e ON public.organization USING btree (deletedby_id);


--
-- Name: idx_d9dfb88465ff1aec; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_d9dfb88465ff1aec ON public.organization USING btree (updatedby_id);


--
-- Name: idx_d9dfb884e5915d19; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_d9dfb884e5915d19 ON public.organization USING btree (typeobj_id);


--
-- Name: idx_dd65739b166d1f9c; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_dd65739b166d1f9c ON public.projectpartner USING btree (project_id);


--
-- Name: idx_dd65739b1c4132c1; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_dd65739b1c4132c1 ON public.projectpartner USING btree (roleobj_id);


--
-- Name: idx_dd65739b3174800f; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_dd65739b3174800f ON public.projectpartner USING btree (createdby_id);


--
-- Name: idx_dd65739b32c8a3de; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_dd65739b32c8a3de ON public.projectpartner USING btree (organization_id);


--
-- Name: idx_dd65739b63d8c20e; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_dd65739b63d8c20e ON public.projectpartner USING btree (deletedby_id);


--
-- Name: idx_dd65739b65ff1aec; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_dd65739b65ff1aec ON public.projectpartner USING btree (updatedby_id);


--
-- Name: idx_e9b87677217bbb47; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_e9b87677217bbb47 ON public.workpackageperson USING btree (person_id);


--
-- Name: idx_e9b876773174800f; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_e9b876773174800f ON public.workpackageperson USING btree (createdby_id);


--
-- Name: idx_e9b8767763d8c20e; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_e9b8767763d8c20e ON public.workpackageperson USING btree (deletedby_id);


--
-- Name: idx_e9b8767765ff1aec; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_e9b8767765ff1aec ON public.workpackageperson USING btree (updatedby_id);


--
-- Name: idx_e9b876779485a167; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_e9b876779485a167 ON public.workpackageperson USING btree (workpackage_id);


--
-- Name: uniq_2de8c6a31596728e; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX uniq_2de8c6a31596728e ON public.user_role USING btree (ldap_filter);


--
-- Name: uniq_2de8c6a3d60322ac; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX uniq_2de8c6a3d60322ac ON public.user_role USING btree (role_id);


--
-- Name: uniq_598638fb8a90aba9; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX uniq_598638fb8a90aba9 ON public.useraccessdefinition USING btree (key);


--
-- Name: uniq_6e60b4f7d60322ac; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX uniq_6e60b4f7d60322ac ON public.organization_role USING btree (role_id);


--
-- Name: uniq_9de7cd62e7927c74; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX uniq_9de7cd62e7927c74 ON public.authentification USING btree (email);


--
-- Name: uniq_9de7cd62f85e0677; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX uniq_9de7cd62f85e0677 ON public.authentification USING btree (username);


--
-- Name: uniq_a7821830ea750e8; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX uniq_a7821830ea750e8 ON public.organizationrole USING btree (label);


--
-- Name: activity activity_numauto; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER activity_numauto AFTER INSERT ON public.activity FOR EACH ROW EXECUTE PROCEDURE public.oscar_activity_numauto();


--
-- Name: validationperiod_sci fk_1fde42e6217bbb47; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.validationperiod_sci
    ADD CONSTRAINT fk_1fde42e6217bbb47 FOREIGN KEY (person_id) REFERENCES public.person(id) ON DELETE CASCADE;


--
-- Name: validationperiod_sci fk_1fde42e625e297e4; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.validationperiod_sci
    ADD CONSTRAINT fk_1fde42e625e297e4 FOREIGN KEY (validationperiod_id) REFERENCES public.validationperiod(id) ON DELETE CASCADE;


--
-- Name: activity_discipline fk_205cd03781c06096; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activity_discipline
    ADD CONSTRAINT fk_205cd03781c06096 FOREIGN KEY (activity_id) REFERENCES public.activity(id) ON DELETE CASCADE;


--
-- Name: activity_discipline fk_205cd037a5522701; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activity_discipline
    ADD CONSTRAINT fk_205cd037a5522701 FOREIGN KEY (discipline_id) REFERENCES public.discipline(id) ON DELETE CASCADE;


--
-- Name: notificationperson fk_22ba6515217bbb47; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notificationperson
    ADD CONSTRAINT fk_22ba6515217bbb47 FOREIGN KEY (person_id) REFERENCES public.person(id);


--
-- Name: notificationperson fk_22ba6515ef1a9d84; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notificationperson
    ADD CONSTRAINT fk_22ba6515ef1a9d84 FOREIGN KEY (notification_id) REFERENCES public.notification(id) ON DELETE CASCADE;


--
-- Name: datetype fk_29fdc4ce3174800f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.datetype
    ADD CONSTRAINT fk_29fdc4ce3174800f FOREIGN KEY (createdby_id) REFERENCES public.person(id);


--
-- Name: datetype fk_29fdc4ce63d8c20e; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.datetype
    ADD CONSTRAINT fk_29fdc4ce63d8c20e FOREIGN KEY (deletedby_id) REFERENCES public.person(id);


--
-- Name: datetype fk_29fdc4ce65ff1aec; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.datetype
    ADD CONSTRAINT fk_29fdc4ce65ff1aec FOREIGN KEY (updatedby_id) REFERENCES public.person(id);


--
-- Name: activitydate fk_2dcfc4c43174800f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activitydate
    ADD CONSTRAINT fk_2dcfc4c43174800f FOREIGN KEY (createdby_id) REFERENCES public.person(id);


--
-- Name: activitydate fk_2dcfc4c463d8c20e; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activitydate
    ADD CONSTRAINT fk_2dcfc4c463d8c20e FOREIGN KEY (deletedby_id) REFERENCES public.person(id);


--
-- Name: activitydate fk_2dcfc4c465ff1aec; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activitydate
    ADD CONSTRAINT fk_2dcfc4c465ff1aec FOREIGN KEY (updatedby_id) REFERENCES public.person(id);


--
-- Name: activitydate fk_2dcfc4c481c06096; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activitydate
    ADD CONSTRAINT fk_2dcfc4c481c06096 FOREIGN KEY (activity_id) REFERENCES public.activity(id);


--
-- Name: activitydate fk_2dcfc4c4c54c8c93; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activitydate
    ADD CONSTRAINT fk_2dcfc4c4c54c8c93 FOREIGN KEY (type_id) REFERENCES public.datetype(id);


--
-- Name: user_role fk_2de8c6a3727aca70; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_role
    ADD CONSTRAINT fk_2de8c6a3727aca70 FOREIGN KEY (parent_id) REFERENCES public.user_role(id);


--
-- Name: person fk_3370d4403174800f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.person
    ADD CONSTRAINT fk_3370d4403174800f FOREIGN KEY (createdby_id) REFERENCES public.person(id);


--
-- Name: person fk_3370d44063d8c20e; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.person
    ADD CONSTRAINT fk_3370d44063d8c20e FOREIGN KEY (deletedby_id) REFERENCES public.person(id);


--
-- Name: person fk_3370d44065ff1aec; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.person
    ADD CONSTRAINT fk_3370d44065ff1aec FOREIGN KEY (updatedby_id) REFERENCES public.person(id);


--
-- Name: timesheet fk_34944573217bbb47; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.timesheet
    ADD CONSTRAINT fk_34944573217bbb47 FOREIGN KEY (person_id) REFERENCES public.person(id);


--
-- Name: timesheet fk_349445733174800f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.timesheet
    ADD CONSTRAINT fk_349445733174800f FOREIGN KEY (createdby_id) REFERENCES public.person(id);


--
-- Name: timesheet fk_3494457363d8c20e; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.timesheet
    ADD CONSTRAINT fk_3494457363d8c20e FOREIGN KEY (deletedby_id) REFERENCES public.person(id);


--
-- Name: timesheet fk_3494457365ff1aec; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.timesheet
    ADD CONSTRAINT fk_3494457365ff1aec FOREIGN KEY (updatedby_id) REFERENCES public.person(id);


--
-- Name: timesheet fk_3494457381c06096; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.timesheet
    ADD CONSTRAINT fk_3494457381c06096 FOREIGN KEY (activity_id) REFERENCES public.activity(id);


--
-- Name: timesheet fk_34944573a7131547; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.timesheet
    ADD CONSTRAINT fk_34944573a7131547 FOREIGN KEY (validationperiod_id) REFERENCES public.validationperiod(id) ON DELETE SET NULL;


--
-- Name: timesheet fk_34944573dbd8a2b7; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.timesheet
    ADD CONSTRAINT fk_34944573dbd8a2b7 FOREIGN KEY (workpackage_id) REFERENCES public.workpackage(id);


--
-- Name: validationperiod_adm fk_48506726217bbb47; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.validationperiod_adm
    ADD CONSTRAINT fk_48506726217bbb47 FOREIGN KEY (person_id) REFERENCES public.person(id) ON DELETE CASCADE;


--
-- Name: validationperiod_adm fk_4850672625e297e4; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.validationperiod_adm
    ADD CONSTRAINT fk_4850672625e297e4 FOREIGN KEY (validationperiod_id) REFERENCES public.validationperiod(id) ON DELETE CASCADE;


--
-- Name: contractdocument fk_4a390fe8217bbb47; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.contractdocument
    ADD CONSTRAINT fk_4a390fe8217bbb47 FOREIGN KEY (person_id) REFERENCES public.person(id);


--
-- Name: contractdocument fk_4a390fe83bebd1bd; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.contractdocument
    ADD CONSTRAINT fk_4a390fe83bebd1bd FOREIGN KEY (typedocument_id) REFERENCES public.typedocument(id);


--
-- Name: contractdocument fk_4a390fe85c0c89f3; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.contractdocument
    ADD CONSTRAINT fk_4a390fe85c0c89f3 FOREIGN KEY (grant_id) REFERENCES public.activity(id);


--
-- Name: activity fk_55026b0c166d1f9c; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activity
    ADD CONSTRAINT fk_55026b0c166d1f9c FOREIGN KEY (project_id) REFERENCES public.project(id);


--
-- Name: activity fk_55026b0c3174800f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activity
    ADD CONSTRAINT fk_55026b0c3174800f FOREIGN KEY (createdby_id) REFERENCES public.person(id);


--
-- Name: activity fk_55026b0c38248176; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activity
    ADD CONSTRAINT fk_55026b0c38248176 FOREIGN KEY (currency_id) REFERENCES public.currency(id);


--
-- Name: activity fk_55026b0c4d79775f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activity
    ADD CONSTRAINT fk_55026b0c4d79775f FOREIGN KEY (tva_id) REFERENCES public.tva(id);


--
-- Name: activity fk_55026b0c63d8c20e; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activity
    ADD CONSTRAINT fk_55026b0c63d8c20e FOREIGN KEY (deletedby_id) REFERENCES public.person(id);


--
-- Name: activity fk_55026b0c65ff1aec; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activity
    ADD CONSTRAINT fk_55026b0c65ff1aec FOREIGN KEY (updatedby_id) REFERENCES public.person(id);


--
-- Name: activity fk_55026b0ca1b4b28c; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activity
    ADD CONSTRAINT fk_55026b0ca1b4b28c FOREIGN KEY (activitytype_id) REFERENCES public.activitytype(id);


--
-- Name: activity fk_55026b0cc54c8c93; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activity
    ADD CONSTRAINT fk_55026b0cc54c8c93 FOREIGN KEY (type_id) REFERENCES public.contracttype(id);


--
-- Name: projectmember fk_5d5b51b9166d1f9c; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.projectmember
    ADD CONSTRAINT fk_5d5b51b9166d1f9c FOREIGN KEY (project_id) REFERENCES public.project(id);


--
-- Name: projectmember fk_5d5b51b91c4132c1; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.projectmember
    ADD CONSTRAINT fk_5d5b51b91c4132c1 FOREIGN KEY (roleobj_id) REFERENCES public.user_role(id);


--
-- Name: projectmember fk_5d5b51b9217bbb47; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.projectmember
    ADD CONSTRAINT fk_5d5b51b9217bbb47 FOREIGN KEY (person_id) REFERENCES public.person(id);


--
-- Name: projectmember fk_5d5b51b93174800f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.projectmember
    ADD CONSTRAINT fk_5d5b51b93174800f FOREIGN KEY (createdby_id) REFERENCES public.person(id);


--
-- Name: projectmember fk_5d5b51b963d8c20e; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.projectmember
    ADD CONSTRAINT fk_5d5b51b963d8c20e FOREIGN KEY (deletedby_id) REFERENCES public.person(id);


--
-- Name: projectmember fk_5d5b51b965ff1aec; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.projectmember
    ADD CONSTRAINT fk_5d5b51b965ff1aec FOREIGN KEY (updatedby_id) REFERENCES public.person(id);


--
-- Name: authentification_role fk_5dbdaf56d28043b; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.authentification_role
    ADD CONSTRAINT fk_5dbdaf56d28043b FOREIGN KEY (authentification_id) REFERENCES public.authentification(id) ON DELETE CASCADE;


--
-- Name: authentification_role fk_5dbdaf5d60322ac; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.authentification_role
    ADD CONSTRAINT fk_5dbdaf5d60322ac FOREIGN KEY (role_id) REFERENCES public.user_role(id) ON DELETE CASCADE;


--
-- Name: typedocument fk_6547bd503174800f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.typedocument
    ADD CONSTRAINT fk_6547bd503174800f FOREIGN KEY (createdby_id) REFERENCES public.person(id);


--
-- Name: typedocument fk_6547bd5063d8c20e; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.typedocument
    ADD CONSTRAINT fk_6547bd5063d8c20e FOREIGN KEY (deletedby_id) REFERENCES public.person(id);


--
-- Name: typedocument fk_6547bd5065ff1aec; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.typedocument
    ADD CONSTRAINT fk_6547bd5065ff1aec FOREIGN KEY (updatedby_id) REFERENCES public.person(id);


--
-- Name: activityperson fk_6a2e76b71c4132c1; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activityperson
    ADD CONSTRAINT fk_6a2e76b71c4132c1 FOREIGN KEY (roleobj_id) REFERENCES public.user_role(id);


--
-- Name: activityperson fk_6a2e76b7217bbb47; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activityperson
    ADD CONSTRAINT fk_6a2e76b7217bbb47 FOREIGN KEY (person_id) REFERENCES public.person(id);


--
-- Name: activityperson fk_6a2e76b73174800f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activityperson
    ADD CONSTRAINT fk_6a2e76b73174800f FOREIGN KEY (createdby_id) REFERENCES public.person(id);


--
-- Name: activityperson fk_6a2e76b763d8c20e; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activityperson
    ADD CONSTRAINT fk_6a2e76b763d8c20e FOREIGN KEY (deletedby_id) REFERENCES public.person(id);


--
-- Name: activityperson fk_6a2e76b765ff1aec; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activityperson
    ADD CONSTRAINT fk_6a2e76b765ff1aec FOREIGN KEY (updatedby_id) REFERENCES public.person(id);


--
-- Name: activityperson fk_6a2e76b781c06096; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activityperson
    ADD CONSTRAINT fk_6a2e76b781c06096 FOREIGN KEY (activity_id) REFERENCES public.activity(id);


--
-- Name: organizationperson fk_6a89662b1c4132c1; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.organizationperson
    ADD CONSTRAINT fk_6a89662b1c4132c1 FOREIGN KEY (roleobj_id) REFERENCES public.user_role(id);


--
-- Name: organizationperson fk_6a89662b217bbb47; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.organizationperson
    ADD CONSTRAINT fk_6a89662b217bbb47 FOREIGN KEY (person_id) REFERENCES public.person(id);


--
-- Name: organizationperson fk_6a89662b3174800f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.organizationperson
    ADD CONSTRAINT fk_6a89662b3174800f FOREIGN KEY (createdby_id) REFERENCES public.person(id);


--
-- Name: organizationperson fk_6a89662b32c8a3de; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.organizationperson
    ADD CONSTRAINT fk_6a89662b32c8a3de FOREIGN KEY (organization_id) REFERENCES public.organization(id);


--
-- Name: organizationperson fk_6a89662b63d8c20e; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.organizationperson
    ADD CONSTRAINT fk_6a89662b63d8c20e FOREIGN KEY (deletedby_id) REFERENCES public.person(id);


--
-- Name: organizationperson fk_6a89662b65ff1aec; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.organizationperson
    ADD CONSTRAINT fk_6a89662b65ff1aec FOREIGN KEY (updatedby_id) REFERENCES public.person(id);


--
-- Name: project_discipline fk_6d18950d166d1f9c; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.project_discipline
    ADD CONSTRAINT fk_6d18950d166d1f9c FOREIGN KEY (project_id) REFERENCES public.project(id) ON DELETE CASCADE;


--
-- Name: project_discipline fk_6d18950da5522701; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.project_discipline
    ADD CONSTRAINT fk_6d18950da5522701 FOREIGN KEY (discipline_id) REFERENCES public.discipline(id) ON DELETE CASCADE;


--
-- Name: tva fk_79ced4aa3174800f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tva
    ADD CONSTRAINT fk_79ced4aa3174800f FOREIGN KEY (createdby_id) REFERENCES public.person(id);


--
-- Name: tva fk_79ced4aa63d8c20e; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tva
    ADD CONSTRAINT fk_79ced4aa63d8c20e FOREIGN KEY (deletedby_id) REFERENCES public.person(id);


--
-- Name: tva fk_79ced4aa65ff1aec; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tva
    ADD CONSTRAINT fk_79ced4aa65ff1aec FOREIGN KEY (updatedby_id) REFERENCES public.person(id);


--
-- Name: organizationtype fk_7c35c5733174800f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.organizationtype
    ADD CONSTRAINT fk_7c35c5733174800f FOREIGN KEY (createdby_id) REFERENCES public.person(id);


--
-- Name: organizationtype fk_7c35c57363d8c20e; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.organizationtype
    ADD CONSTRAINT fk_7c35c57363d8c20e FOREIGN KEY (deletedby_id) REFERENCES public.person(id);


--
-- Name: organizationtype fk_7c35c57365ff1aec; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.organizationtype
    ADD CONSTRAINT fk_7c35c57365ff1aec FOREIGN KEY (updatedby_id) REFERENCES public.person(id);


--
-- Name: organizationtype fk_7c35c57379066886; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.organizationtype
    ADD CONSTRAINT fk_7c35c57379066886 FOREIGN KEY (root_id) REFERENCES public.organizationtype(id);


--
-- Name: referent fk_7ecce3a217bbb47; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.referent
    ADD CONSTRAINT fk_7ecce3a217bbb47 FOREIGN KEY (person_id) REFERENCES public.person(id);


--
-- Name: referent fk_7ecce3a35e47e35; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.referent
    ADD CONSTRAINT fk_7ecce3a35e47e35 FOREIGN KEY (referent_id) REFERENCES public.person(id);


--
-- Name: activitypayment fk_8115848c3174800f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activitypayment
    ADD CONSTRAINT fk_8115848c3174800f FOREIGN KEY (createdby_id) REFERENCES public.person(id);


--
-- Name: activitypayment fk_8115848c38248176; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activitypayment
    ADD CONSTRAINT fk_8115848c38248176 FOREIGN KEY (currency_id) REFERENCES public.currency(id);


--
-- Name: activitypayment fk_8115848c63d8c20e; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activitypayment
    ADD CONSTRAINT fk_8115848c63d8c20e FOREIGN KEY (deletedby_id) REFERENCES public.person(id);


--
-- Name: activitypayment fk_8115848c65ff1aec; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activitypayment
    ADD CONSTRAINT fk_8115848c65ff1aec FOREIGN KEY (updatedby_id) REFERENCES public.person(id);


--
-- Name: activitypayment fk_8115848c81c06096; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activitypayment
    ADD CONSTRAINT fk_8115848c81c06096 FOREIGN KEY (activity_id) REFERENCES public.activity(id);


--
-- Name: privilege fk_87209a8779066886; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.privilege
    ADD CONSTRAINT fk_87209a8779066886 FOREIGN KEY (root_id) REFERENCES public.privilege(id);


--
-- Name: privilege fk_87209a87bcf5e72d; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.privilege
    ADD CONSTRAINT fk_87209a87bcf5e72d FOREIGN KEY (categorie_id) REFERENCES public.categorie_privilege(id);


--
-- Name: timesheetsby fk_8ffc688a217bbb47; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.timesheetsby
    ADD CONSTRAINT fk_8ffc688a217bbb47 FOREIGN KEY (person_id) REFERENCES public.person(id);


--
-- Name: timesheetsby fk_8ffc688a241061bf; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.timesheetsby
    ADD CONSTRAINT fk_8ffc688a241061bf FOREIGN KEY (usurpation_person_id) REFERENCES public.person(id);


--
-- Name: currency fk_9020ea693174800f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.currency
    ADD CONSTRAINT fk_9020ea693174800f FOREIGN KEY (createdby_id) REFERENCES public.person(id);


--
-- Name: currency fk_9020ea6963d8c20e; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.currency
    ADD CONSTRAINT fk_9020ea6963d8c20e FOREIGN KEY (deletedby_id) REFERENCES public.person(id);


--
-- Name: currency fk_9020ea6965ff1aec; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.currency
    ADD CONSTRAINT fk_9020ea6965ff1aec FOREIGN KEY (updatedby_id) REFERENCES public.person(id);


--
-- Name: activityorganization fk_9310307d1c4132c1; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activityorganization
    ADD CONSTRAINT fk_9310307d1c4132c1 FOREIGN KEY (roleobj_id) REFERENCES public.organizationrole(id);


--
-- Name: activityorganization fk_9310307d3174800f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activityorganization
    ADD CONSTRAINT fk_9310307d3174800f FOREIGN KEY (createdby_id) REFERENCES public.person(id);


--
-- Name: activityorganization fk_9310307d32c8a3de; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activityorganization
    ADD CONSTRAINT fk_9310307d32c8a3de FOREIGN KEY (organization_id) REFERENCES public.organization(id);


--
-- Name: activityorganization fk_9310307d63d8c20e; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activityorganization
    ADD CONSTRAINT fk_9310307d63d8c20e FOREIGN KEY (deletedby_id) REFERENCES public.person(id);


--
-- Name: activityorganization fk_9310307d65ff1aec; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activityorganization
    ADD CONSTRAINT fk_9310307d65ff1aec FOREIGN KEY (updatedby_id) REFERENCES public.person(id);


--
-- Name: activityorganization fk_9310307d81c06096; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activityorganization
    ADD CONSTRAINT fk_9310307d81c06096 FOREIGN KEY (activity_id) REFERENCES public.activity(id);


--
-- Name: organizationrole fk_a78218303174800f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.organizationrole
    ADD CONSTRAINT fk_a78218303174800f FOREIGN KEY (createdby_id) REFERENCES public.person(id);


--
-- Name: organizationrole fk_a782183063d8c20e; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.organizationrole
    ADD CONSTRAINT fk_a782183063d8c20e FOREIGN KEY (deletedby_id) REFERENCES public.person(id);


--
-- Name: organizationrole fk_a782183065ff1aec; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.organizationrole
    ADD CONSTRAINT fk_a782183065ff1aec FOREIGN KEY (updatedby_id) REFERENCES public.person(id);


--
-- Name: validationperiod fk_b700890a3c21f464; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.validationperiod
    ADD CONSTRAINT fk_b700890a3c21f464 FOREIGN KEY (declarer_id) REFERENCES public.person(id);


--
-- Name: activitytype fk_b8fa4973174800f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activitytype
    ADD CONSTRAINT fk_b8fa4973174800f FOREIGN KEY (createdby_id) REFERENCES public.person(id);


--
-- Name: activitytype fk_b8fa49763d8c20e; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activitytype
    ADD CONSTRAINT fk_b8fa49763d8c20e FOREIGN KEY (deletedby_id) REFERENCES public.person(id);


--
-- Name: activitytype fk_b8fa49765ff1aec; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activitytype
    ADD CONSTRAINT fk_b8fa49765ff1aec FOREIGN KEY (updatedby_id) REFERENCES public.person(id);


--
-- Name: administrativedocument fk_c311ba72217bbb47; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.administrativedocument
    ADD CONSTRAINT fk_c311ba72217bbb47 FOREIGN KEY (person_id) REFERENCES public.person(id);


--
-- Name: workpackage fk_c583f07f3174800f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workpackage
    ADD CONSTRAINT fk_c583f07f3174800f FOREIGN KEY (createdby_id) REFERENCES public.person(id);


--
-- Name: workpackage fk_c583f07f63d8c20e; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workpackage
    ADD CONSTRAINT fk_c583f07f63d8c20e FOREIGN KEY (deletedby_id) REFERENCES public.person(id);


--
-- Name: workpackage fk_c583f07f65ff1aec; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workpackage
    ADD CONSTRAINT fk_c583f07f65ff1aec FOREIGN KEY (updatedby_id) REFERENCES public.person(id);


--
-- Name: workpackage fk_c583f07f81c06096; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workpackage
    ADD CONSTRAINT fk_c583f07f81c06096 FOREIGN KEY (activity_id) REFERENCES public.activity(id);


--
-- Name: activityrequestfollow fk_cfe2df3a3174800f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activityrequestfollow
    ADD CONSTRAINT fk_cfe2df3a3174800f FOREIGN KEY (createdby_id) REFERENCES public.person(id);


--
-- Name: activityrequestfollow fk_cfe2df3a63d8c20e; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activityrequestfollow
    ADD CONSTRAINT fk_cfe2df3a63d8c20e FOREIGN KEY (deletedby_id) REFERENCES public.person(id);


--
-- Name: activityrequestfollow fk_cfe2df3a65ff1aec; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activityrequestfollow
    ADD CONSTRAINT fk_cfe2df3a65ff1aec FOREIGN KEY (updatedby_id) REFERENCES public.person(id);


--
-- Name: activityrequestfollow fk_cfe2df3ae8fa3e0f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activityrequestfollow
    ADD CONSTRAINT fk_cfe2df3ae8fa3e0f FOREIGN KEY (activityrequest_id) REFERENCES public.activityrequest(id) ON DELETE CASCADE;


--
-- Name: role_privilege fk_d6d4495b32fb8aea; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.role_privilege
    ADD CONSTRAINT fk_d6d4495b32fb8aea FOREIGN KEY (privilege_id) REFERENCES public.privilege(id);


--
-- Name: role_privilege fk_d6d4495bd60322ac; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.role_privilege
    ADD CONSTRAINT fk_d6d4495bd60322ac FOREIGN KEY (role_id) REFERENCES public.user_role(id);


--
-- Name: validationperiod_prj fk_d7488e15217bbb47; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.validationperiod_prj
    ADD CONSTRAINT fk_d7488e15217bbb47 FOREIGN KEY (person_id) REFERENCES public.person(id) ON DELETE CASCADE;


--
-- Name: validationperiod_prj fk_d7488e1525e297e4; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.validationperiod_prj
    ADD CONSTRAINT fk_d7488e1525e297e4 FOREIGN KEY (validationperiod_id) REFERENCES public.validationperiod(id) ON DELETE CASCADE;


--
-- Name: activityrequest fk_d7aa8f1e3174800f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activityrequest
    ADD CONSTRAINT fk_d7aa8f1e3174800f FOREIGN KEY (createdby_id) REFERENCES public.person(id);


--
-- Name: activityrequest fk_d7aa8f1e63d8c20e; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activityrequest
    ADD CONSTRAINT fk_d7aa8f1e63d8c20e FOREIGN KEY (deletedby_id) REFERENCES public.person(id);


--
-- Name: activityrequest fk_d7aa8f1e65ff1aec; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activityrequest
    ADD CONSTRAINT fk_d7aa8f1e65ff1aec FOREIGN KEY (updatedby_id) REFERENCES public.person(id);


--
-- Name: activityrequest fk_d7aa8f1e9e6b1585; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activityrequest
    ADD CONSTRAINT fk_d7aa8f1e9e6b1585 FOREIGN KEY (organisation_id) REFERENCES public.organization(id);


--
-- Name: organization fk_d9dfb8843174800f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.organization
    ADD CONSTRAINT fk_d9dfb8843174800f FOREIGN KEY (createdby_id) REFERENCES public.person(id);


--
-- Name: organization fk_d9dfb88463d8c20e; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.organization
    ADD CONSTRAINT fk_d9dfb88463d8c20e FOREIGN KEY (deletedby_id) REFERENCES public.person(id);


--
-- Name: organization fk_d9dfb88465ff1aec; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.organization
    ADD CONSTRAINT fk_d9dfb88465ff1aec FOREIGN KEY (updatedby_id) REFERENCES public.person(id);


--
-- Name: organization fk_d9dfb884e5915d19; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.organization
    ADD CONSTRAINT fk_d9dfb884e5915d19 FOREIGN KEY (typeobj_id) REFERENCES public.organizationtype(id);


--
-- Name: projectpartner fk_dd65739b166d1f9c; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.projectpartner
    ADD CONSTRAINT fk_dd65739b166d1f9c FOREIGN KEY (project_id) REFERENCES public.project(id);


--
-- Name: projectpartner fk_dd65739b1c4132c1; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.projectpartner
    ADD CONSTRAINT fk_dd65739b1c4132c1 FOREIGN KEY (roleobj_id) REFERENCES public.organizationrole(id);


--
-- Name: projectpartner fk_dd65739b3174800f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.projectpartner
    ADD CONSTRAINT fk_dd65739b3174800f FOREIGN KEY (createdby_id) REFERENCES public.person(id);


--
-- Name: projectpartner fk_dd65739b32c8a3de; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.projectpartner
    ADD CONSTRAINT fk_dd65739b32c8a3de FOREIGN KEY (organization_id) REFERENCES public.organization(id);


--
-- Name: projectpartner fk_dd65739b63d8c20e; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.projectpartner
    ADD CONSTRAINT fk_dd65739b63d8c20e FOREIGN KEY (deletedby_id) REFERENCES public.person(id);


--
-- Name: projectpartner fk_dd65739b65ff1aec; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.projectpartner
    ADD CONSTRAINT fk_dd65739b65ff1aec FOREIGN KEY (updatedby_id) REFERENCES public.person(id);


--
-- Name: workpackageperson fk_e9b87677217bbb47; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workpackageperson
    ADD CONSTRAINT fk_e9b87677217bbb47 FOREIGN KEY (person_id) REFERENCES public.person(id);


--
-- Name: workpackageperson fk_e9b876773174800f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workpackageperson
    ADD CONSTRAINT fk_e9b876773174800f FOREIGN KEY (createdby_id) REFERENCES public.person(id);


--
-- Name: workpackageperson fk_e9b8767763d8c20e; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workpackageperson
    ADD CONSTRAINT fk_e9b8767763d8c20e FOREIGN KEY (deletedby_id) REFERENCES public.person(id);


--
-- Name: workpackageperson fk_e9b8767765ff1aec; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workpackageperson
    ADD CONSTRAINT fk_e9b8767765ff1aec FOREIGN KEY (updatedby_id) REFERENCES public.person(id);


--
-- Name: workpackageperson fk_e9b876779485a167; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workpackageperson
    ADD CONSTRAINT fk_e9b876779485a167 FOREIGN KEY (workpackage_id) REFERENCES public.workpackage(id);


--
-- Name: SCHEMA public; Type: ACL; Schema: -; Owner: -
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- PostgreSQL database dump complete
--

