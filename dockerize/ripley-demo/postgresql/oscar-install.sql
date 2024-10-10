--
-- PostgreSQL database dump
--

-- Dumped from database version 13.16 (Debian 13.16-0+deb11u1)
-- Dumped by pg_dump version 13.16 (Debian 13.16-0+deb11u1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

ALTER TABLE ONLY public.unicaen_signature_recipient DROP CONSTRAINT fk_f47c5330ed61183a;
ALTER TABLE ONLY public.pcrutypecontract DROP CONSTRAINT fk_f40fcdc4a1b4b28c;
ALTER TABLE ONLY public.unicaen_signature_observer DROP CONSTRAINT fk_eac19423ed61183a;
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
ALTER TABLE ONLY public.unicaen_signature_notification DROP CONSTRAINT fk_dc74ea669f268069;
ALTER TABLE ONLY public.unicaen_signature_notification DROP CONSTRAINT fk_dc74ea6642e26054;
ALTER TABLE ONLY public.organization DROP CONSTRAINT fk_d9dfb884e5915d19;
ALTER TABLE ONLY public.organization DROP CONSTRAINT fk_d9dfb884727aca70;
ALTER TABLE ONLY public.organization DROP CONSTRAINT fk_d9dfb88465ff1aec;
ALTER TABLE ONLY public.organization DROP CONSTRAINT fk_d9dfb88463d8c20e;
ALTER TABLE ONLY public.organization DROP CONSTRAINT fk_d9dfb8843174800f;
ALTER TABLE ONLY public.tabsdocumentsroles DROP CONSTRAINT fk_d7f103acd60322ac;
ALTER TABLE ONLY public.tabsdocumentsroles DROP CONSTRAINT fk_d7f103ac1b50f2d9;
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
ALTER TABLE ONLY public.unicaen_signature_process_step DROP CONSTRAINT fk_cf70b0a5ed61183a;
ALTER TABLE ONLY public.unicaen_signature_process_step DROP CONSTRAINT fk_cf70b0a5c352c4;
ALTER TABLE ONLY public.unicaen_signature_process_step DROP CONSTRAINT fk_cf70b0a57ec2f574;
ALTER TABLE ONLY public.workpackage DROP CONSTRAINT fk_c583f07f81c06096;
ALTER TABLE ONLY public.workpackage DROP CONSTRAINT fk_c583f07f65ff1aec;
ALTER TABLE ONLY public.workpackage DROP CONSTRAINT fk_c583f07f63d8c20e;
ALTER TABLE ONLY public.workpackage DROP CONSTRAINT fk_c583f07f3174800f;
ALTER TABLE ONLY public.administrativedocument DROP CONSTRAINT fk_c311ba72d823e37a;
ALTER TABLE ONLY public.administrativedocument DROP CONSTRAINT fk_c311ba72217bbb47;
ALTER TABLE ONLY public.activitytype DROP CONSTRAINT fk_b8fa49765ff1aec;
ALTER TABLE ONLY public.activitytype DROP CONSTRAINT fk_b8fa49763d8c20e;
ALTER TABLE ONLY public.activitytype DROP CONSTRAINT fk_b8fa4973174800f;
ALTER TABLE ONLY public.validationperiod DROP CONSTRAINT fk_b700890a3c21f464;
ALTER TABLE ONLY public.person_activity_validator_prj DROP CONSTRAINT fk_ae64ea7d81c06096;
ALTER TABLE ONLY public.person_activity_validator_prj DROP CONSTRAINT fk_ae64ea7d217bbb47;
ALTER TABLE ONLY public.timesheetcommentperiod DROP CONSTRAINT fk_a8a6ec6e3c21f464;
ALTER TABLE ONLY public.organizationrole DROP CONSTRAINT fk_a782183065ff1aec;
ALTER TABLE ONLY public.organizationrole DROP CONSTRAINT fk_a782183063d8c20e;
ALTER TABLE ONLY public.organizationrole DROP CONSTRAINT fk_a78218303174800f;
ALTER TABLE ONLY public.unicaen_signature_signatureflowstep DROP CONSTRAINT fk_a575dc3eb4090c8a;
ALTER TABLE ONLY public.activitypcruinfos DROP CONSTRAINT fk_a3673210ae24e5c2;
ALTER TABLE ONLY public.activitypcruinfos DROP CONSTRAINT fk_a367321081c06096;
ALTER TABLE ONLY public.activitypcruinfos DROP CONSTRAINT fk_a36732106f04e0;
ALTER TABLE ONLY public.unicaen_signature_process DROP CONSTRAINT fk_994855d2b4090c8a;
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
ALTER TABLE ONLY public.recalldeclaration DROP CONSTRAINT fk_78e42a72217bbb47;
ALTER TABLE ONLY public.recallexception DROP CONSTRAINT fk_7358d996217bbb47;
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
ALTER TABLE ONLY public.person_activity_validator_sci DROP CONSTRAINT fk_66f2268e81c06096;
ALTER TABLE ONLY public.person_activity_validator_sci DROP CONSTRAINT fk_66f2268e217bbb47;
ALTER TABLE ONLY public.typedocument DROP CONSTRAINT fk_6547bd50b4090c8a;
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
ALTER TABLE ONLY public.role_datetype DROP CONSTRAINT fk_5a6aef97d8cb54f3;
ALTER TABLE ONLY public.role_datetype DROP CONSTRAINT fk_5a6aef97d60322ac;
ALTER TABLE ONLY public.estimatedspentline DROP CONSTRAINT fk_57175ded81c06096;
ALTER TABLE ONLY public.persons_documents DROP CONSTRAINT fk_5511ad90b9352966;
ALTER TABLE ONLY public.persons_documents DROP CONSTRAINT fk_5511ad90217bbb47;
ALTER TABLE ONLY public.activity DROP CONSTRAINT fk_55026b0cc54c8c93;
ALTER TABLE ONLY public.activity DROP CONSTRAINT fk_55026b0cb49d04;
ALTER TABLE ONLY public.activity DROP CONSTRAINT fk_55026b0ca1b4b28c;
ALTER TABLE ONLY public.activity DROP CONSTRAINT fk_55026b0c8c8fc2fe;
ALTER TABLE ONLY public.activity DROP CONSTRAINT fk_55026b0c65ff1aec;
ALTER TABLE ONLY public.activity DROP CONSTRAINT fk_55026b0c63d8c20e;
ALTER TABLE ONLY public.activity DROP CONSTRAINT fk_55026b0c4d79775f;
ALTER TABLE ONLY public.activity DROP CONSTRAINT fk_55026b0c38248176;
ALTER TABLE ONLY public.activity DROP CONSTRAINT fk_55026b0c3174800f;
ALTER TABLE ONLY public.activity DROP CONSTRAINT fk_55026b0c166d1f9c;
ALTER TABLE ONLY public.contractdocument DROP CONSTRAINT fk_4a390fe87ec2f574;
ALTER TABLE ONLY public.contractdocument DROP CONSTRAINT fk_4a390fe85c0c89f3;
ALTER TABLE ONLY public.contractdocument DROP CONSTRAINT fk_4a390fe83bebd1bd;
ALTER TABLE ONLY public.contractdocument DROP CONSTRAINT fk_4a390fe8217bbb47;
ALTER TABLE ONLY public.contractdocument DROP CONSTRAINT fk_4a390fe81b50f2d9;
ALTER TABLE ONLY public.validationperiod_adm DROP CONSTRAINT fk_4850672625e297e4;
ALTER TABLE ONLY public.validationperiod_adm DROP CONSTRAINT fk_48506726217bbb47;
ALTER TABLE ONLY public.spenttypegroup DROP CONSTRAINT fk_3f07201e727aca70;
ALTER TABLE ONLY public.spenttypegroup DROP CONSTRAINT fk_3f07201e65ff1aec;
ALTER TABLE ONLY public.spenttypegroup DROP CONSTRAINT fk_3f07201e63d8c20e;
ALTER TABLE ONLY public.spenttypegroup DROP CONSTRAINT fk_3f07201e3174800f;
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
ALTER TABLE ONLY public.person_activity_validator_adm DROP CONSTRAINT fk_317c034e81c06096;
ALTER TABLE ONLY public.person_activity_validator_adm DROP CONSTRAINT fk_317c034e217bbb47;
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
DROP INDEX public.uniq_cf70b0a5ed61183a;
DROP INDEX public.uniq_a7821830ea750e8;
DROP INDEX public.uniq_a367321081c06096;
DROP INDEX public.uniq_9de7cd62f85e0677;
DROP INDEX public.uniq_9de7cd62e7927c74;
DROP INDEX public.uniq_6e60b4f7d60322ac;
DROP INDEX public.uniq_598638fb8a90aba9;
DROP INDEX public.uniq_2de8c6a3d60322ac;
DROP INDEX public.uniq_2de8c6a31596728e;
DROP INDEX public.typecontractlabel_idx;
DROP INDEX public.sourcefinancementlabel_idx;
DROP INDEX public.polecompetivitelabel_idx;
DROP INDEX public.idx_f47c5330ed61183a;
DROP INDEX public.idx_f40fcdc4a1b4b28c;
DROP INDEX public.idx_eac19423ed61183a;
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
DROP INDEX public.idx_dc74ea669f268069;
DROP INDEX public.idx_dc74ea6642e26054;
DROP INDEX public.idx_d9dfb884e5915d19;
DROP INDEX public.idx_d9dfb884727aca70;
DROP INDEX public.idx_d9dfb88465ff1aec;
DROP INDEX public.idx_d9dfb88463d8c20e;
DROP INDEX public.idx_d9dfb8843174800f;
DROP INDEX public.idx_d7f103acd60322ac;
DROP INDEX public.idx_d7f103ac1b50f2d9;
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
DROP INDEX public.idx_cf70b0a5c352c4;
DROP INDEX public.idx_cf70b0a57ec2f574;
DROP INDEX public.idx_c583f07f81c06096;
DROP INDEX public.idx_c583f07f65ff1aec;
DROP INDEX public.idx_c583f07f63d8c20e;
DROP INDEX public.idx_c583f07f3174800f;
DROP INDEX public.idx_c311ba72d823e37a;
DROP INDEX public.idx_c311ba72217bbb47;
DROP INDEX public.idx_b8fa49765ff1aec;
DROP INDEX public.idx_b8fa49763d8c20e;
DROP INDEX public.idx_b8fa4973174800f;
DROP INDEX public.idx_b700890a3c21f464;
DROP INDEX public.idx_ae64ea7d81c06096;
DROP INDEX public.idx_ae64ea7d217bbb47;
DROP INDEX public.idx_a8a6ec6e3c21f464;
DROP INDEX public.idx_a782183065ff1aec;
DROP INDEX public.idx_a782183063d8c20e;
DROP INDEX public.idx_a78218303174800f;
DROP INDEX public.idx_a575dc3eb4090c8a;
DROP INDEX public.idx_a3673210ae24e5c2;
DROP INDEX public.idx_a36732106f04e0;
DROP INDEX public.idx_994855d2b4090c8a;
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
DROP INDEX public.idx_78e42a72217bbb47;
DROP INDEX public.idx_7358d996217bbb47;
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
DROP INDEX public.idx_66f2268e81c06096;
DROP INDEX public.idx_66f2268e217bbb47;
DROP INDEX public.idx_6547bd50b4090c8a;
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
DROP INDEX public.idx_5a6aef97d8cb54f3;
DROP INDEX public.idx_5a6aef97d60322ac;
DROP INDEX public.idx_57175ded81c06096;
DROP INDEX public.idx_5511ad90b9352966;
DROP INDEX public.idx_5511ad90217bbb47;
DROP INDEX public.idx_55026b0cc54c8c93;
DROP INDEX public.idx_55026b0cb49d04;
DROP INDEX public.idx_55026b0ca1b4b28c;
DROP INDEX public.idx_55026b0c8c8fc2fe;
DROP INDEX public.idx_55026b0c65ff1aec;
DROP INDEX public.idx_55026b0c63d8c20e;
DROP INDEX public.idx_55026b0c4d79775f;
DROP INDEX public.idx_55026b0c38248176;
DROP INDEX public.idx_55026b0c3174800f;
DROP INDEX public.idx_55026b0c166d1f9c;
DROP INDEX public.idx_4a390fe87ec2f574;
DROP INDEX public.idx_4a390fe85c0c89f3;
DROP INDEX public.idx_4a390fe83bebd1bd;
DROP INDEX public.idx_4a390fe8217bbb47;
DROP INDEX public.idx_4a390fe81b50f2d9;
DROP INDEX public.idx_4850672625e297e4;
DROP INDEX public.idx_48506726217bbb47;
DROP INDEX public.idx_3f07201e727aca70;
DROP INDEX public.idx_3f07201e65ff1aec;
DROP INDEX public.idx_3f07201e63d8c20e;
DROP INDEX public.idx_3f07201e3174800f;
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
DROP INDEX public.idx_317c034e81c06096;
DROP INDEX public.idx_317c034e217bbb47;
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
ALTER TABLE ONLY public.unicaen_signature_signatureflowstep DROP CONSTRAINT unicaen_signature_signatureflowstep_pkey;
ALTER TABLE ONLY public.unicaen_signature_signatureflow DROP CONSTRAINT unicaen_signature_signatureflow_pkey;
ALTER TABLE ONLY public.unicaen_signature_signature DROP CONSTRAINT unicaen_signature_signature_pkey;
ALTER TABLE ONLY public.unicaen_signature_recipient DROP CONSTRAINT unicaen_signature_recipient_pkey;
ALTER TABLE ONLY public.unicaen_signature_process_step DROP CONSTRAINT unicaen_signature_process_step_pkey;
ALTER TABLE ONLY public.unicaen_signature_process DROP CONSTRAINT unicaen_signature_process_pkey;
ALTER TABLE ONLY public.unicaen_signature_observer DROP CONSTRAINT unicaen_signature_observer_pkey;
ALTER TABLE ONLY public.unicaen_signature_notification DROP CONSTRAINT unicaen_signature_notification_pkey;
ALTER TABLE ONLY public.typedocument DROP CONSTRAINT typedocument_pkey;
ALTER TABLE ONLY public.tva DROP CONSTRAINT tva_pkey;
ALTER TABLE ONLY public.timesheetsby DROP CONSTRAINT timesheetsby_pkey;
ALTER TABLE ONLY public.timesheetcommentperiod DROP CONSTRAINT timesheetcommentperiod_pkey;
ALTER TABLE ONLY public.timesheet DROP CONSTRAINT timesheet_pkey;
ALTER TABLE ONLY public.tabsdocumentsroles DROP CONSTRAINT tabsdocumentsroles_pkey;
ALTER TABLE ONLY public.tabdocument DROP CONSTRAINT tabdocument_pkey;
ALTER TABLE ONLY public.spenttypegroup DROP CONSTRAINT spenttypegroup_pkey;
ALTER TABLE ONLY public.spentline DROP CONSTRAINT spentline_pkey;
ALTER TABLE ONLY public.role_privilege DROP CONSTRAINT role_privilege_pkey;
ALTER TABLE ONLY public.role_datetype DROP CONSTRAINT role_datetype_pkey;
ALTER TABLE ONLY public.referent DROP CONSTRAINT referent_pkey;
ALTER TABLE ONLY public.recallexception DROP CONSTRAINT recallexception_pkey;
ALTER TABLE ONLY public.recalldeclaration DROP CONSTRAINT recalldeclaration_pkey;
ALTER TABLE ONLY public.projectpartner DROP CONSTRAINT projectpartner_pkey;
ALTER TABLE ONLY public.projectmember DROP CONSTRAINT projectmember_pkey;
ALTER TABLE ONLY public.project DROP CONSTRAINT project_pkey;
ALTER TABLE ONLY public.project_discipline DROP CONSTRAINT project_discipline_pkey;
ALTER TABLE ONLY public.privilege DROP CONSTRAINT privilege_pkey;
ALTER TABLE ONLY public.persons_documents DROP CONSTRAINT persons_documents_pkey;
ALTER TABLE ONLY public.person DROP CONSTRAINT person_pkey;
ALTER TABLE ONLY public.person_activity_validator_sci DROP CONSTRAINT person_activity_validator_sci_pkey;
ALTER TABLE ONLY public.person_activity_validator_prj DROP CONSTRAINT person_activity_validator_prj_pkey;
ALTER TABLE ONLY public.person_activity_validator_adm DROP CONSTRAINT person_activity_validator_adm_pkey;
ALTER TABLE ONLY public.pcrutypecontract DROP CONSTRAINT pcrutypecontract_pkey;
ALTER TABLE ONLY public.pcrusourcefinancement DROP CONSTRAINT pcrusourcefinancement_pkey;
ALTER TABLE ONLY public.pcrupolecompetitivite DROP CONSTRAINT pcrupolecompetitivite_pkey;
ALTER TABLE ONLY public.organizationtype DROP CONSTRAINT organizationtype_pkey;
ALTER TABLE ONLY public.organizationrole DROP CONSTRAINT organizationrole_pkey;
ALTER TABLE ONLY public.organizationperson DROP CONSTRAINT organizationperson_pkey;
ALTER TABLE ONLY public.organization_role DROP CONSTRAINT organization_role_pkey;
ALTER TABLE ONLY public.organization DROP CONSTRAINT organization_pkey;
ALTER TABLE ONLY public.notificationperson DROP CONSTRAINT notificationperson_pkey;
ALTER TABLE ONLY public.notification DROP CONSTRAINT notification_pkey;
ALTER TABLE ONLY public.logactivity DROP CONSTRAINT logactivity_pkey;
ALTER TABLE ONLY public.estimatedspentline DROP CONSTRAINT estimatedspentline_pkey;
ALTER TABLE ONLY public.doctrine_migration_versions DROP CONSTRAINT doctrine_migration_versions_pkey;
ALTER TABLE ONLY public.discipline DROP CONSTRAINT discipline_pkey;
ALTER TABLE ONLY public.datetype DROP CONSTRAINT datetype_pkey;
ALTER TABLE ONLY public.currency DROP CONSTRAINT currency_pkey;
ALTER TABLE ONLY public.country3166 DROP CONSTRAINT country3166_pkey;
ALTER TABLE ONLY public.contracttype DROP CONSTRAINT contracttype_pkey;
ALTER TABLE ONLY public.contractdocument DROP CONSTRAINT contractdocument_pkey;
ALTER TABLE ONLY public.categorie_privilege DROP CONSTRAINT categorie_privilege_pkey;
ALTER TABLE ONLY public.authentification_role DROP CONSTRAINT authentification_role_pkey;
ALTER TABLE ONLY public.authentification DROP CONSTRAINT authentification_pkey;
ALTER TABLE ONLY public.administrativedocumentsection DROP CONSTRAINT administrativedocumentsection_pkey;
ALTER TABLE ONLY public.administrativedocument DROP CONSTRAINT administrativedocument_pkey;
ALTER TABLE ONLY public.activitytype DROP CONSTRAINT activitytype_pkey;
ALTER TABLE ONLY public.activityrequestfollow DROP CONSTRAINT activityrequestfollow_pkey;
ALTER TABLE ONLY public.activityrequest DROP CONSTRAINT activityrequest_pkey;
ALTER TABLE ONLY public.activityperson DROP CONSTRAINT activityperson_pkey;
ALTER TABLE ONLY public.activitypcruinfos DROP CONSTRAINT activitypcruinfos_pkey;
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
DROP SEQUENCE public.unicaen_signature_signatureflowstep_id_seq;
DROP TABLE public.unicaen_signature_signatureflowstep;
DROP SEQUENCE public.unicaen_signature_signatureflow_id_seq;
DROP TABLE public.unicaen_signature_signatureflow;
DROP SEQUENCE public.unicaen_signature_signature_id_seq;
DROP TABLE public.unicaen_signature_signature;
DROP SEQUENCE public.unicaen_signature_recipient_id_seq;
DROP TABLE public.unicaen_signature_recipient;
DROP SEQUENCE public.unicaen_signature_process_step_id_seq;
DROP TABLE public.unicaen_signature_process_step;
DROP SEQUENCE public.unicaen_signature_process_id_seq;
DROP TABLE public.unicaen_signature_process;
DROP SEQUENCE public.unicaen_signature_observer_id_seq;
DROP TABLE public.unicaen_signature_observer;
DROP SEQUENCE public.unicaen_signature_notification_id_seq;
DROP TABLE public.unicaen_signature_notification;
DROP SEQUENCE public.typedocument_id_seq;
DROP TABLE public.typedocument;
DROP SEQUENCE public.tva_id_seq;
DROP TABLE public.tva;
DROP TABLE public.timesheetsby;
DROP SEQUENCE public.timesheetcommentperiod_id_seq;
DROP TABLE public.timesheetcommentperiod;
DROP SEQUENCE public.timesheet_id_seq;
DROP TABLE public.timesheet;
DROP SEQUENCE public.tabsdocumentsroles_id_seq;
DROP TABLE public.tabsdocumentsroles;
DROP SEQUENCE public.tabdocument_id_seq;
DROP TABLE public.tabdocument;
DROP SEQUENCE public.spenttypegroup_id_seq;
DROP TABLE public.spenttypegroup;
DROP SEQUENCE public.spentline_id_seq;
DROP TABLE public.spentline;
DROP TABLE public.role_privilege;
DROP SEQUENCE public.role_id_seq;
DROP TABLE public.role_datetype;
DROP SEQUENCE public.referent_id_seq;
DROP TABLE public.referent;
DROP SEQUENCE public.recallexception_id_seq;
DROP TABLE public.recallexception;
DROP SEQUENCE public.recalldeclaration_id_seq;
DROP TABLE public.recalldeclaration;
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
DROP TABLE public.persons_documents;
DROP SEQUENCE public.person_id_seq;
DROP TABLE public.person_activity_validator_sci;
DROP TABLE public.person_activity_validator_prj;
DROP TABLE public.person_activity_validator_adm;
DROP TABLE public.person;
DROP SEQUENCE public.pcrutypecontract_id_seq;
DROP TABLE public.pcrutypecontract;
DROP SEQUENCE public.pcrusourcefinancement_id_seq;
DROP TABLE public.pcrusourcefinancement;
DROP SEQUENCE public.pcrupolecompetitivite_id_seq;
DROP TABLE public.pcrupolecompetitivite;
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
DROP SEQUENCE public.estimatedspentline_id_seq;
DROP TABLE public.estimatedspentline;
DROP TABLE public.doctrine_migration_versions;
DROP SEQUENCE public.discipline_id_seq;
DROP TABLE public.discipline;
DROP SEQUENCE public.datetype_id_seq;
DROP TABLE public.datetype;
DROP SEQUENCE public.currency_id_seq;
DROP TABLE public.currency;
DROP SEQUENCE public.country3166_id_seq;
DROP TABLE public.country3166;
DROP SEQUENCE public.contracttype_id_seq;
DROP TABLE public.contracttype;
DROP SEQUENCE public.contractdocument_id_seq;
DROP TABLE public.contractdocument;
DROP SEQUENCE public.categorie_privilege_id_seq;
DROP TABLE public.categorie_privilege;
DROP TABLE public.authentification_role;
DROP SEQUENCE public.authentification_id_seq;
DROP TABLE public.authentification;
DROP SEQUENCE public.administrativedocumentsection_id_seq;
DROP TABLE public.administrativedocumentsection;
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
DROP SEQUENCE public.activitypcruinfos_id_seq;
DROP TABLE public.activitypcruinfos;
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

SET default_table_access_method = heap;

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
                                 fraisdegestion character varying(255),
                                 notefinanciere text,
                                 assiettesubventionnable double precision,
                                 pcruvalidpolecompetitivite boolean DEFAULT false NOT NULL,
                                 fraisdegestionparthebergeur character varying(255) DEFAULT NULL::character varying,
                                 fraisdegestionpartunite character varying(255) DEFAULT NULL::character varying,
                                 totalspent double precision,
                                 datetotalspent timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                 pcrupolecompetitivite_id integer,
                                 pcrusourcefinancement_id integer
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
-- Name: activitypcruinfos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.activitypcruinfos (
                                          id integer NOT NULL,
                                          activity_id integer,
                                          objet text NOT NULL,
                                          codeunitelabintel character varying(10) NOT NULL,
                                          sigleunite character varying(20) DEFAULT NULL::character varying,
                                          numcontrattutellegestionnaire character varying(20) NOT NULL,
                                          equipe character varying(150) NOT NULL,
                                          acronyme text,
                                          contratsassocies text NOT NULL,
                                          responsablescientifique text NOT NULL,
                                          employeurresponsablescientifique text,
                                          coordinateurconsortium boolean NOT NULL,
                                          partenaires text,
                                          partenaireprincipal text NOT NULL,
                                          idpartenaireprincipal character varying(255) NOT NULL,
                                          lieuexecution character varying(50) NOT NULL,
                                          datedernieresignature date,
                                          duree character varying(255) DEFAULT NULL::character varying,
                                          datedebut date,
                                          datefin date,
                                          montantpercuunite character varying(255) DEFAULT NULL::character varying,
                                          couttotaletude character varying(255) DEFAULT NULL::character varying,
                                          montanttotal character varying(255) DEFAULT NULL::character varying,
                                          validepolecompetivite boolean NOT NULL,
                                          polecompetivite character varying(200) DEFAULT NULL::character varying,
                                          errorsremote character varying(255) DEFAULT NULL::character varying,
                                          status character varying(20) DEFAULT NULL::character varying,
                                          error text,
                                          warnings text,
                                          commentaires text,
                                          pia boolean NOT NULL,
                                          reference character varying(100) NOT NULL,
                                          accordcadre boolean NOT NULL,
                                          cifre character varying(100) DEFAULT NULL::character varying,
                                          chaireindustrielle character varying(8) NOT NULL,
                                          presencepartenaireindustriel boolean NOT NULL,
                                          documentid integer,
                                          typecontrat_id integer,
                                          sourcefinancement_id integer
);


--
-- Name: COLUMN activitypcruinfos.error; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.activitypcruinfos.error IS '(DC2Type:array)';


--
-- Name: COLUMN activitypcruinfos.warnings; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.activitypcruinfos.warnings IS '(DC2Type:array)';


--
-- Name: activitypcruinfos_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.activitypcruinfos_id_seq
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
                                               dateupdoad timestamp(0) without time zone,
                                               path character varying(255) NOT NULL,
                                               information text,
                                               filetypemime character varying(255) DEFAULT NULL::character varying,
                                               filesize integer,
                                               filename character varying(255) DEFAULT NULL::character varying,
                                               version integer,
                                               status integer DEFAULT 1 NOT NULL,
                                               section_id integer
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
-- Name: administrativedocumentsection; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.administrativedocumentsection (
                                                      id integer NOT NULL,
                                                      label character varying(255) NOT NULL,
                                                      description text
);


--
-- Name: administrativedocumentsection_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.administrativedocumentsection_id_seq
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

COMMENT ON COLUMN public.authentification.settings IS '(DC2Type:object)';


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
                                         dateupdoad timestamp(0) without time zone,
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
                                         datesend date,
                                         process_id integer,
                                         private boolean,
                                         signable boolean DEFAULT false NOT NULL,
                                         location character varying(255) DEFAULT 'local'::character varying NOT NULL,
                                         tabdocument_id integer
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
-- Name: country3166; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.country3166 (
                                    id integer NOT NULL,
                                    fr character varying(100) NOT NULL,
                                    en character varying(100) NOT NULL,
                                    alpha2 character varying(2) NOT NULL,
                                    alpha3 character varying(3) NOT NULL,
                                    "numeric" integer NOT NULL
);


--
-- Name: country3166_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.country3166_id_seq
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
-- Name: doctrine_migration_versions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.doctrine_migration_versions (
                                                    version character varying(191) NOT NULL,
                                                    executed_at timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                                    execution_time integer
);


--
-- Name: estimatedspentline; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.estimatedspentline (
                                           id integer NOT NULL,
                                           activity_id integer,
                                           year integer NOT NULL,
                                           amount double precision NOT NULL,
                                           account character varying(255) NOT NULL
);


--
-- Name: estimatedspentline_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.estimatedspentline_id_seq
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
                                     description text DEFAULT NULL::character varying,
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
                                     typeobj_id integer,
                                     parent_id integer,
                                     labintel character varying(255) DEFAULT NULL::character varying,
                                     rnsr character varying(255) DEFAULT NULL::character varying,
                                     duns character varying(255) DEFAULT NULL::character varying,
                                     tvaintra character varying(255) DEFAULT NULL::character varying
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
-- Name: pcrupolecompetitivite; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.pcrupolecompetitivite (
                                              id integer NOT NULL,
                                              label character varying(100) NOT NULL
);


--
-- Name: pcrupolecompetitivite_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.pcrupolecompetitivite_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: pcrusourcefinancement; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.pcrusourcefinancement (
                                              id integer NOT NULL,
                                              label character varying(100) NOT NULL
);


--
-- Name: pcrusourcefinancement_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.pcrusourcefinancement_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: pcrutypecontract; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.pcrutypecontract (
                                         id integer NOT NULL,
                                         label character varying(100) NOT NULL,
                                         activitytype_id integer
);


--
-- Name: pcrutypecontract_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.pcrutypecontract_id_seq
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
-- Name: person_activity_validator_adm; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.person_activity_validator_adm (
                                                      activity_id integer NOT NULL,
                                                      person_id integer NOT NULL
);


--
-- Name: person_activity_validator_prj; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.person_activity_validator_prj (
                                                      activity_id integer NOT NULL,
                                                      person_id integer NOT NULL
);


--
-- Name: person_activity_validator_sci; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.person_activity_validator_sci (
                                                      activity_id integer NOT NULL,
                                                      person_id integer NOT NULL
);


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
-- Name: persons_documents; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.persons_documents (
                                          contractdocument_id integer NOT NULL,
                                          person_id integer NOT NULL
);


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
-- Name: recalldeclaration; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.recalldeclaration (
                                          id integer NOT NULL,
                                          person_id integer,
                                          periodyear integer NOT NULL,
                                          periodmonth integer NOT NULL,
                                          context character varying(255) NOT NULL,
                                          startprocess timestamp(0) without time zone NOT NULL,
                                          lastsend timestamp(0) without time zone NOT NULL,
                                          history text,
                                          shipments jsonb
);


--
-- Name: recalldeclaration_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.recalldeclaration_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: recallexception; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.recallexception (
                                        id integer NOT NULL,
                                        person_id integer,
                                        type character varying(255) NOT NULL
);


--
-- Name: recallexception_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.recallexception_id_seq
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
-- Name: role_datetype; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.role_datetype (
                                      datetype_id integer NOT NULL,
                                      role_id integer NOT NULL
);


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
-- Name: spentline; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.spentline (
                                  id integer NOT NULL,
                                  syncid character varying(255) NOT NULL,
                                  pfi character varying(255) DEFAULT NULL::character varying,
                                  rldnr character varying(255) DEFAULT '9A'::character varying,
                                  btart character varying(255) DEFAULT '0250'::character varying,
                                  numsifac character varying(255) DEFAULT NULL::character varying,
                                  numcommandeaff character varying(255) DEFAULT NULL::character varying,
                                  numpiece character varying(255) DEFAULT NULL::character varying,
                                  numfournisseur character varying(255) DEFAULT NULL::character varying,
                                  pieceref character varying(255) DEFAULT NULL::character varying,
                                  codesociete character varying(255) DEFAULT NULL::character varying,
                                  codeservicefait character varying(255) DEFAULT NULL::character varying,
                                  codedomainefonct character varying(255) DEFAULT NULL::character varying,
                                  designation character varying(255) DEFAULT NULL::character varying,
                                  textefacture character varying(255) DEFAULT NULL::character varying,
                                  typedocument character varying(255) DEFAULT NULL::character varying,
                                  montant double precision,
                                  centredeprofit character varying(255) DEFAULT NULL::character varying,
                                  comptebudgetaire character varying(255) DEFAULT NULL::character varying,
                                  centrefinancier character varying(255) DEFAULT NULL::character varying,
                                  comptegeneral character varying(255) DEFAULT NULL::character varying,
                                  datepiece character varying(255) DEFAULT NULL::character varying,
                                  datecomptable character varying(255) DEFAULT NULL::character varying,
                                  dateanneeexercice character varying(255) DEFAULT NULL::character varying,
                                  datepaiement character varying(255) DEFAULT NULL::character varying,
                                  dateservicefait character varying(255) DEFAULT NULL::character varying
);


--
-- Name: spentline_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.spentline_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: spenttypegroup; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.spenttypegroup (
                                       id integer NOT NULL,
                                       parent_id integer,
                                       label character varying(255) NOT NULL,
                                       description character varying(255) NOT NULL,
                                       code character varying(255) NOT NULL,
                                       annexe character varying(255) DEFAULT ''::character varying,
                                       rgt integer NOT NULL,
                                       lft integer NOT NULL,
                                       blind boolean DEFAULT false NOT NULL,
                                       status integer,
                                       datecreated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                       dateupdated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                       datedeleted timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                       createdby_id integer,
                                       updatedby_id integer,
                                       deletedby_id integer
);


--
-- Name: spenttypegroup_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.spenttypegroup_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: tabdocument; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.tabdocument (
                                    id integer NOT NULL,
                                    label character varying(255) NOT NULL,
                                    description character varying(255) DEFAULT NULL::character varying,
                                    isdefault boolean DEFAULT false NOT NULL
);


--
-- Name: tabdocument_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.tabdocument_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: tabsdocumentsroles; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.tabsdocumentsroles (
                                           id integer NOT NULL,
                                           role_id integer,
                                           access integer NOT NULL,
                                           tabdocument_id integer
);


--
-- Name: tabsdocumentsroles_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.tabsdocumentsroles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


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
-- Name: timesheetcommentperiod; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.timesheetcommentperiod (
                                               id integer NOT NULL,
                                               declarer_id integer,
                                               object character varying(255) NOT NULL,
                                               objectgroup character varying(255) NOT NULL,
                                               object_id character varying(255) NOT NULL,
                                               comment text,
                                               month integer NOT NULL,
                                               year integer NOT NULL
);


--
-- Name: timesheetcommentperiod_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.timesheetcommentperiod_id_seq
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
                                     deletedby_id integer,
                                     isdefault boolean DEFAULT false NOT NULL,
                                     signatureflow_id integer
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
-- Name: unicaen_signature_notification; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.unicaen_signature_notification (
                                                       id integer NOT NULL,
                                                       datecreated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                                       datelastsend timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                                       context character varying(32) DEFAULT NULL::character varying,
                                                       send boolean DEFAULT false NOT NULL,
                                                       message character varying(255) DEFAULT NULL::character varying,
                                                       signaturerecipient_id integer,
                                                       signatureobserver_id integer,
                                                       subject character varying(255) DEFAULT NULL::character varying
);


--
-- Name: unicaen_signature_notification_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.unicaen_signature_notification_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: unicaen_signature_observer; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.unicaen_signature_observer (
                                                   id integer NOT NULL,
                                                   signature_id integer,
                                                   firstname character varying(64) DEFAULT NULL::character varying,
                                                   lastname character varying(64) DEFAULT NULL::character varying,
                                                   email character varying(256) NOT NULL
);


--
-- Name: unicaen_signature_observer_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.unicaen_signature_observer_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: unicaen_signature_process; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.unicaen_signature_process (
                                                  id integer NOT NULL,
                                                  datecreated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                                  lastupdate timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                                  status integer,
                                                  currentstep integer NOT NULL,
                                                  document_name character varying(255) NOT NULL,
                                                  signatureflow_id integer
);


--
-- Name: unicaen_signature_process_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.unicaen_signature_process_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: unicaen_signature_process_step; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.unicaen_signature_process_step (
                                                       id integer NOT NULL,
                                                       process_id integer,
                                                       signature_id integer,
                                                       signatureflowstep_id integer
);


--
-- Name: unicaen_signature_process_step_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.unicaen_signature_process_step_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: unicaen_signature_recipient; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.unicaen_signature_recipient (
                                                    id integer NOT NULL,
                                                    signature_id integer,
                                                    status integer DEFAULT 101 NOT NULL,
                                                    firstname character varying(64) DEFAULT NULL::character varying,
                                                    lastname character varying(64) DEFAULT NULL::character varying,
                                                    email character varying(256) NOT NULL,
                                                    phone character varying(20) DEFAULT NULL::character varying,
                                                    dateupdate timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                                    datefinished timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                                    keyaccess character varying(255) DEFAULT NULL::character varying,
                                                    informations character varying(255) DEFAULT NULL::character varying,
                                                    urldocument character varying(255) DEFAULT NULL::character varying
);


--
-- Name: unicaen_signature_recipient_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.unicaen_signature_recipient_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: unicaen_signature_signature; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.unicaen_signature_signature (
                                                    id integer NOT NULL,
                                                    datecreated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                                    type character varying(32) DEFAULT NULL::character varying,
                                                    status integer DEFAULT 101 NOT NULL,
                                                    ordering integer DEFAULT 0 NOT NULL,
                                                    label character varying(255) DEFAULT NULL::character varying,
                                                    description character varying(255) DEFAULT NULL::character varying,
                                                    datesend timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                                    dateupdate timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                                    document_path character varying(255) NOT NULL,
                                                    document_remotekey character varying(255) DEFAULT NULL::character varying,
                                                    document_localkey character varying(255) DEFAULT NULL::character varying,
                                                    context_short character varying(255),
                                                    context_long text,
                                                    letterfile_key character varying(255) DEFAULT NULL::character varying,
                                                    letterfile_process character varying(255) DEFAULT NULL::character varying,
                                                    letterfile_url character varying(255) DEFAULT NULL::character varying,
                                                    allsigntocomplete boolean DEFAULT false NOT NULL,
                                                    notificationsrecipients boolean DEFAULT false NOT NULL
);


--
-- Name: unicaen_signature_signature_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.unicaen_signature_signature_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: unicaen_signature_signatureflow; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.unicaen_signature_signatureflow (
                                                        id integer NOT NULL,
                                                        label character varying(255) DEFAULT NULL::character varying,
                                                        description character varying(255) DEFAULT NULL::character varying,
                                                        enabled boolean DEFAULT false NOT NULL
);


--
-- Name: unicaen_signature_signatureflow_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.unicaen_signature_signatureflow_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: unicaen_signature_signatureflowstep; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.unicaen_signature_signatureflowstep (
                                                            id integer NOT NULL,
                                                            recipientsmethod character varying(64) DEFAULT NULL::character varying,
                                                            label character varying(64) DEFAULT NULL::character varying,
                                                            letterfilename character varying(256) NOT NULL,
                                                            signlevel character varying(256) NOT NULL,
                                                            ordering integer NOT NULL,
                                                            allrecipientssign boolean DEFAULT true NOT NULL,
                                                            notificationsrecipients boolean DEFAULT false NOT NULL,
                                                            editablerecipients boolean DEFAULT false NOT NULL,
                                                            options text,
                                                            observers_options text,
                                                            observersmethod character varying(64) DEFAULT NULL::character varying,
                                                            signatureflow_id integer
);


--
-- Name: unicaen_signature_signatureflowstep_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.unicaen_signature_signatureflowstep_id_seq
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
                                  principal boolean DEFAULT false NOT NULL,
                                  displayed boolean DEFAULT true NOT NULL,
                                  accessible_exterieur boolean DEFAULT true NOT NULL
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
                                         status character varying(255) NOT NULL,
                                         validatorsprjdefault boolean DEFAULT true NOT NULL,
                                         validatorsscidefault boolean DEFAULT true NOT NULL,
                                         validatorsadmdefault boolean DEFAULT true NOT NULL,
                                         comment text
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

COPY public.activity (id, project_id, type_id, centaureid, centaurenumconvention, codeeotp, label, description, hassheet, duration, justifyworkingtime, justifycost, amount, datestart, dateend, datesigned, dateopened, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id, activitytype_id, currency_id, tva_id, oscarid, oscarnum, timesheetformat, numbers, financialimpact, fraisdegestion, notefinanciere, assiettesubventionnable, pcruvalidpolecompetitivite, fraisdegestionparthebergeur, fraisdegestionpartunite, totalspent, datetotalspent, pcrupolecompetitivite_id, pcrusourcefinancement_id) FROM stdin;
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
-- Data for Name: activitypcruinfos; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.activitypcruinfos (id, activity_id, objet, codeunitelabintel, sigleunite, numcontrattutellegestionnaire, equipe, acronyme, contratsassocies, responsablescientifique, employeurresponsablescientifique, coordinateurconsortium, partenaires, partenaireprincipal, idpartenaireprincipal, lieuexecution, datedernieresignature, duree, datedebut, datefin, montantpercuunite, couttotaletude, montanttotal, validepolecompetivite, polecompetivite, errorsremote, status, error, warnings, commentaires, pia, reference, accordcadre, cifre, chaireindustrielle, presencepartenaireindustriel, documentid, typecontrat_id, sourcefinancement_id) FROM stdin;
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
709	21	22	1	2024-09-03 13:51:16	\N	\N	\N	\N	\N	INTERREG		0	\N
710	23	24	1	2024-09-03 13:52:04	\N	\N	\N	\N	\N	H2020		0	\N
699	3	4	1	2024-09-03 13:45:25	\N	\N	\N	\N	\N	Doctorant		0	\N
701	14	33	1	2024-09-03 13:46:25	\N	\N	\N	\N	\N	Programme UE		0	\N
693	1	42	1	2024-06-26 14:39:30	\N	\N	\N	\N	\N	ROOT		Recherche et valorisation	\N
711	25	32	1	2024-09-03 13:52:19	\N	\N	\N	\N	\N	Horizon Europe		0	\N
695	2	7	1	2024-09-03 13:41:36	\N	\N	\N	\N	\N	Subvention région		0	\N
697	9	10	1	2024-09-03 13:43:34	\N	\N	\N	\N	\N	ANR		0	\N
696	8	13	1	2024-09-03 13:41:57	\N	\N	\N	\N	\N	Subvention nationale		0	\N
698	11	12	1	2024-09-03 13:43:49	\N	\N	\N	\N	\N	PIA		0	\N
700	5	6	1	2024-09-03 13:46:02	\N	\N	\N	\N	\N	Tremplin		0	\N
703	35	36	1	2024-09-03 13:49:32	\N	\N	\N	\N	\N	CPER - Etat		0	\N
704	37	38	1	2024-09-03 13:49:46	\N	\N	\N	\N	\N	CPER - Région		0	\N
702	34	41	1	2024-09-03 13:47:37	\N	\N	\N	\N	\N	CPER		0	\N
705	39	40	1	2024-09-03 13:50:17	\N	\N	\N	\N	\N	CPER - FEDER		0	\N
714	30	31	1	2024-09-03 13:55:17	\N	\N	\N	\N	\N	Cluster 1 : Santé		0	\N
712	26	27	1	2024-09-03 13:53:41	\N	\N	\N	\N	\N	P1 ERC		0	\N
706	15	16	1	2024-09-03 13:50:31	\N	\N	\N	\N	\N	EUREKA		0	\N
713	28	29	1	2024-09-03 13:54:26	\N	\N	\N	\N	\N	P1 Action Marie Curie		0	\N
707	17	18	1	2024-09-03 13:50:45	\N	\N	\N	\N	\N	FP6		0	\N
708	19	20	1	2024-09-03 13:50:57	\N	\N	\N	\N	\N	FP7		0	\N
\.


--
-- Data for Name: administrativedocument; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.administrativedocument (id, person_id, dateupdoad, path, information, filetypemime, filesize, filename, version, status, section_id) FROM stdin;
\.


--
-- Data for Name: administrativedocumentsection; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.administrativedocumentsection (id, label, description) FROM stdin;
1	ANR	\N
2	REGION	\N
3	FEDER	\N
4	INTERREG	\N
\.


--
-- Data for Name: authentification; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.authentification (id, username, email, display_name, password, state, datelogin, settings, secret) FROM stdin;
	\N		\N
\.


--
-- Data for Name: authentification_role; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.authentification_role (authentification_id, role_id) FROM stdin;
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
10	SIGNATURE	Signatures éléctroniques	\N
\.


--
-- Data for Name: contractdocument; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.contractdocument (id, grant_id, person_id, dateupdoad, path, information, centaureid, filetypemime, filesize, filename, version, typedocument_id, status, datedeposit, datesend, process_id, private, signable, location, tabdocument_id) FROM stdin;
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
-- Data for Name: country3166; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.country3166 (id, fr, en, alpha2, alpha3, "numeric") FROM stdin;
5	Andorre	Andorra	AD	AND	20
233	Émirats arabes unis	United Arab Emirates (the)	AE	ARE	784
1	Afghanistan	Afghanistan	AF	AFG	4
9	Antigua-et-Barbuda	Antigua and Barbuda	AG	ATG	28
7	Anguilla	Anguilla	AI	AIA	660
2	Albanie	Albania	AL	ALB	8
11	Arménie	Armenia	AM	ARM	51
6	Angola	Angola	AO	AGO	24
8	Antarctique	Antarctica	AQ	ATA	10
10	Argentine	Argentina	AR	ARG	32
4	Samoa américaines	American Samoa	AS	ASM	16
14	Autriche	Austria	AT	AUT	40
13	Australie	Australia	AU	AUS	36
12	Aruba	Aruba	AW	ABW	533
249	Åland(les Îles)	Åland Islands	AX	ALA	248
15	Azerbaïdjan	Azerbaijan	AZ	AZE	31
28	Bosnie-Herzégovine	Bosnia and Herzegovina	BA	BIH	70
19	Barbade	Barbados	BB	BRB	52
18	Bangladesh	Bangladesh	BD	BGD	50
21	Belgique	Belgium	BE	BEL	56
35	Burkina Faso	Burkina Faso	BF	BFA	854
34	Bulgarie	Bulgaria	BG	BGR	100
17	Bahreïn	Bahrain	BH	BHR	48
36	Burundi	Burundi	BI	BDI	108
23	Bénin	Benin	BJ	BEN	204
185	Saint-Barthélemy	Saint Barthélemy	BL	BLM	652
24	Bermudes	Bermuda	BM	BMU	60
33	Brunéi Darussalam	Brunei Darussalam	BN	BRN	96
26	Bolivie (État plurinational de)	Bolivia (Plurinational State of)	BO	BOL	68
27	Bonaire, Saint-Eustache et Saba	Bonaire, Sint Eustatius and Saba	BQ	BES	535
31	Brésil	Brazil	BR	BRA	76
16	Bahamas	Bahamas (the)	BS	BHS	44
25	Bhoutan	Bhutan	BT	BTN	64
30	Bouvet (l'Île)	Bouvet Island	BV	BVT	74
29	Botswana	Botswana	BW	BWA	72
20	Bélarus	Belarus	BY	BLR	112
22	Belize	Belize	BZ	BLZ	84
40	Canada	Canada	CA	CAN	124
47	Cocos (les Îles)/ Keeling (les Îles)	Cocos (Keeling) Islands (the)	CC	CCK	166
50	Congo (la République démocratique du)	Congo (the Democratic Republic of the)	CD	COD	180
42	République centrafricaine	Central African Republic (the)	CF	CAF	140
51	Congo	Congo (the)	CG	COG	178
215	Suisse	Switzerland	CH	CHE	756
59	Côte d'Ivoire	Côte d'Ivoire	CI	CIV	384
52	Cook (les Îles)	Cook Islands (the)	CK	COK	184
44	Chili	Chile	CL	CHL	152
39	Cameroun	Cameroon	CM	CMR	120
45	Chine	China	CN	CHN	156
48	Colombie	Colombia	CO	COL	170
53	Costa Rica	Costa Rica	CR	CRI	188
55	Cuba	Cuba	CU	CUB	192
37	Cabo Verde	Cabo Verde	CV	CPV	132
56	Curaçao	Curaçao	CW	CUW	531
46	Christmas (l'Île)	Christmas Island	CX	CXR	162
57	Chypre	Cyprus	CY	CYP	196
58	Tchéquie	Czechia	CZ	CZE	203
83	Allemagne	Germany	DE	DEU	276
61	Djibouti	Djibouti	DJ	DJI	262
60	Danemark	Denmark	DK	DNK	208
62	Dominique	Dominica	DM	DMA	212
63	dominicaine (la République)	Dominican Republic (the)	DO	DOM	214
3	Algérie	Algeria	DZ	DZA	12
64	Équateur	Ecuador	EC	ECU	218
69	Estonie	Estonia	EE	EST	233
65	Égypte	Egypt	EG	EGY	818
245	Sahara occidental	Western Sahara*	EH	ESH	732
68	Érythrée	Eritrea	ER	ERI	232
209	Espagne	Spain	ES	ESP	724
71	Éthiopie	Ethiopia	ET	ETH	231
75	Finlande	Finland	FI	FIN	246
74	Fidji	Fiji	FJ	FJI	242
72	Falkland (les Îles)/Malouines (les Îles)	Falkland Islands (the) [Malvinas]	FK	FLK	238
144	Micronésie (États fédérés de)	Micronesia (Federated States of)	FM	FSM	583
73	Féroé (les Îles)	Faroe Islands (the)	FO	FRO	234
76	France	France	FR	FRA	250
80	Gabon	Gabon	GA	GAB	266
234	Royaume-Uni de Grande-Bretagne et d'Irlande du Nord	United Kingdom of Great Britain and Northern Ireland (the)	GB	GBR	826
88	Grenade	Grenada	GD	GRD	308
82	Géorgie	Georgia	GE	GEO	268
77	Guyane française (la )	French Guiana	GF	GUF	254
92	Guernesey	Guernsey	GG	GGY	831
84	Ghana	Ghana	GH	GHA	288
85	Gibraltar	Gibraltar	GI	GIB	292
87	Groenland	Greenland	GL	GRL	304
81	Gambie	Gambia (the)	GM	GMB	270
93	Guinée	Guinea	GN	GIN	324
89	Guadeloupe	Guadeloupe	GP	GLP	312
67	Guinée équatoriale	Equatorial Guinea	GQ	GNQ	226
86	Grèce	Greece	GR	GRC	300
207	Géorgie du Sud-et-les Îles Sandwich du Sud	South Georgia and the South Sandwich Islands	GS	SGS	239
91	Guatemala	Guatemala	GT	GTM	320
90	Guam	Guam	GU	GUM	316
94	Guinée-Bissau	Guinea-Bissau	GW	GNB	624
95	Guyana	Guyana	GY	GUY	328
100	Hong Kong	Hong Kong	HK	HKG	344
97	Heard-et-Îles MacDonald (l'Île)	Heard Island and McDonald Islands	HM	HMD	334
99	Honduras	Honduras	HN	HND	340
54	Croatie	Croatia	HR	HRV	191
96	Haïti	Haiti	HT	HTI	332
101	Hongrie	Hungary	HU	HUN	348
104	Indonésie	Indonesia	ID	IDN	360
107	Irlande	Ireland	IE	IRL	372
109	Israël	Israel	IL	ISR	376
108	Île de Man	Isle of Man	IM	IMN	833
103	Inde	India	IN	IND	356
32	Indien (le Territoire britannique de l'océan)	British Indian Ocean Territory (the)	IO	IOT	86
106	Iraq	Iraq	IQ	IRQ	368
105	Iran (République Islamique d')	Iran (Islamic Republic of)	IR	IRN	364
102	Islande	Iceland	IS	ISL	352
110	Italie	Italy	IT	ITA	380
113	Jersey	Jersey	JE	JEY	832
111	Jamaïque	Jamaica	JM	JAM	388
114	Jordanie	Jordan	JO	JOR	400
112	Japon	Japan	JP	JPN	392
116	Kenya	Kenya	KE	KEN	404
121	Kirghizistan	Kyrgyzstan	KG	KGZ	417
38	Cambodge	Cambodia	KH	KHM	116
117	Kiribati	Kiribati	KI	KIR	296
49	Comores	Comoros (the)	KM	COM	174
187	Saint-Kitts-et-Nevis	Saint Kitts and Nevis	KN	KNA	659
118	Corée (la République populaire démocratique de)	Korea (the Democratic People's Republic of)	KP	PRK	408
119	Corée (la République de)	Korea (the Republic of)	KR	KOR	410
120	Koweït	Kuwait	KW	KWT	414
41	Caïmans (les Îles)	Cayman Islands (the)	KY	CYM	136
115	Kazakhstan	Kazakhstan	KZ	KAZ	398
122	Lao (la République démocratique populaire)	Lao People's Democratic Republic (the)	LA	LAO	418
124	Liban	Lebanon	LB	LBN	422
188	Sainte-Lucie	Saint Lucia	LC	LCA	662
128	Liechtenstein	Liechtenstein	LI	LIE	438
210	Sri Lanka	Sri Lanka	LK	LKA	144
126	Libéria	Liberia	LR	LBR	430
125	Lesotho	Lesotho	LS	LSO	426
129	Lituanie	Lithuania	LT	LTU	440
130	Luxembourg	Luxembourg	LU	LUX	442
123	Lettonie	Latvia	LV	LVA	428
127	Libye	Libya	LY	LBY	434
150	Maroc	Morocco	MA	MAR	504
146	Monaco	Monaco	MC	MCO	492
145	Moldova (la République de)	Moldova (the Republic of)	MD	MDA	498
148	Monténégro	Montenegro	ME	MNE	499
189	Saint-Martin (partie française)	Saint Martin (French part)	MF	MAF	663
132	Madagascar	Madagascar	MG	MDG	450
138	Marshall (les Îles)	Marshall Islands (the)	MH	MHL	584
164	Macédoine du Nord	North Macedonia	MK	MKD	807
136	Mali	Mali	ML	MLI	466
152	Myanmar	Myanmar	MM	MMR	104
147	Mongolie	Mongolia	MN	MNG	496
131	Macao	Macao	MO	MAC	446
165	Mariannes du Nord (les Îles)	Northern Mariana Islands (the)	MP	MNP	580
139	Martinique	Martinique	MQ	MTQ	474
140	Mauritanie	Mauritania	MR	MRT	478
149	Montserrat	Montserrat	MS	MSR	500
137	Malte	Malta	MT	MLT	470
141	Maurice	Mauritius	MU	MUS	480
135	Maldives	Maldives	MV	MDV	462
133	Malawi	Malawi	MW	MWI	454
143	Mexique	Mexico	MX	MEX	484
134	Malaisie	Malaysia	MY	MYS	458
151	Mozambique	Mozambique	MZ	MOZ	508
153	Namibie	Namibia	NA	NAM	516
157	Nouvelle-Calédonie	New Caledonia	NC	NCL	540
160	Niger	Niger (the)	NE	NER	562
163	Norfolk (l'Île)	Norfolk Island	NF	NFK	574
161	Nigéria	Nigeria	NG	NGA	566
159	Nicaragua	Nicaragua	NI	NIC	558
156	Pays-Bas	Netherlands (the)	NL	NLD	528
166	Norvège	Norway	NO	NOR	578
155	Népal	Nepal	NP	NPL	524
154	Nauru	Nauru	NR	NRU	520
162	Niue	Niue	NU	NIU	570
158	Nouvelle-Zélande	New Zealand	NZ	NZL	554
167	Oman	Oman	OM	OMN	512
171	Panama	Panama	PA	PAN	591
174	Pérou	Peru	PE	PER	604
78	Polynésie française	French Polynesia	PF	PYF	258
172	Papouasie-Nouvelle-Guinée	Papua New Guinea	PG	PNG	598
175	Philippines	Philippines (the)	PH	PHL	608
168	Pakistan	Pakistan	PK	PAK	586
177	Pologne	Poland	PL	POL	616
190	Saint-Pierre-et-Miquelon	Saint Pierre and Miquelon	PM	SPM	666
176	Pitcairn	Pitcairn	PN	PCN	612
179	Porto Rico	Puerto Rico	PR	PRI	630
170	Palestine, État de	Palestine, State of	PS	PSE	275
178	Portugal	Portugal	PT	PRT	620
169	Palaos	Palau	PW	PLW	585
173	Paraguay	Paraguay	PY	PRY	600
180	Qatar	Qatar	QA	QAT	634
184	Réunion	Réunion	RE	REU	638
181	Roumanie	Romania	RO	ROU	642
197	Serbie	Serbia	RS	SRB	688
182	Russie (la Fédération de)	Russian Federation (the)	RU	RUS	643
183	Rwanda	Rwanda	RW	RWA	646
195	Arabie saoudite	Saudi Arabia	SA	SAU	682
204	Salomon (les Îles)	Solomon Islands	SB	SLB	90
198	Seychelles	Seychelles	SC	SYC	690
211	Soudan	Sudan (the)	SD	SDN	729
214	Suède	Sweden	SE	SWE	752
200	Singapour	Singapore	SG	SGP	702
186	Sainte-Hélène, Ascension et Tristan da Cunha	Saint Helena, Ascension and Tristan da Cunha	SH	SHN	654
203	Slovénie	Slovenia	SI	SVN	705
213	Svalbard et l'Île Jan Mayen	Svalbard and Jan Mayen	SJ	SJM	744
202	Slovaquie	Slovakia	SK	SVK	703
199	Sierra Leone	Sierra Leone	SL	SLE	694
193	Saint-Marin	San Marino	SM	SMR	674
196	Sénégal	Senegal	SN	SEN	686
205	Somalie	Somalia	SO	SOM	706
212	Suriname	Suriname	SR	SUR	740
208	Soudan du Sud	South Sudan	SS	SSD	728
194	Sao Tomé-et-Principe	Sao Tome and Principe	ST	STP	678
66	El Salvador	El Salvador	SV	SLV	222
201	Saint-Martin (partie néerlandaise)	Sint Maarten (Dutch part)	SX	SXM	534
216	République arabe syrienne	Syrian Arab Republic (the)	SY	SYR	760
70	Eswatini	Eswatini	SZ	SWZ	748
229	Turks-et-Caïcos (les Îles)	Turks and Caicos Islands (the)	TC	TCA	796
43	Tchad	Chad	TD	TCD	148
79	Terres australes françaises	French Southern Territories (the)	TF	ATF	260
222	Togo	Togo	TG	TGO	768
220	Thaïlande	Thailand	TH	THA	764
218	Tadjikistan	Tajikistan	TJ	TJK	762
223	Tokelau	Tokelau	TK	TKL	772
221	Timor-Leste	Timor-Leste	TL	TLS	626
228	Turkménistan	Turkmenistan	TM	TKM	795
226	Tunisie	Tunisia	TN	TUN	788
224	Tonga	Tonga	TO	TON	776
227	Turquie	Turkey	TR	TUR	792
225	Trinité-et-Tobago	Trinidad and Tobago	TT	TTO	780
230	Tuvalu	Tuvalu	TV	TUV	798
217	Taïwan (Province de Chine)	Taiwan (Province of China)	TW	TWN	158
219	Tanzanie (la République-Unie de)	Tanzania, the United Republic of	TZ	TZA	834
232	Ukraine	Ukraine	UA	UKR	804
231	Ouganda	Uganda	UG	UGA	800
235	Îles mineures éloignées des États-Unis	United States Minor Outlying Islands (the)	UM	UMI	581
236	États-Unis d'Amérique	United States of America (the)	US	USA	840
237	Uruguay	Uruguay	UY	URY	858
238	Ouzbékistan	Uzbekistan	UZ	UZB	860
98	Saint-Siège	Holy See (the)	VA	VAT	336
191	Saint-Vincent-et-les Grenadines	Saint Vincent and the Grenadines	VC	VCT	670
240	Venezuela (République bolivarienne du)	Venezuela (Bolivarian Republic of)	VE	VEN	862
242	Vierges britanniques (les Îles)	Virgin Islands (British)	VG	VGB	92
243	Vierges des États-Unis (les Îles)	Virgin Islands (U.S.)	VI	VIR	850
241	Viet Nam	Viet Nam	VN	VNM	704
239	Vanuatu	Vanuatu	VU	VUT	548
244	Wallis-et-Futuna	Wallis and Futuna	WF	WLF	876
192	Samoa	Samoa	WS	WSM	882
246	Yémen	Yemen	YE	YEM	887
142	Mayotte	Mayotte	YT	MYT	175
206	Afrique du Sud	South Africa	ZA	ZAF	710
247	Zambie	Zambia	ZM	ZMB	894
248	Zimbabwe	Zimbabwe	ZW	ZWE	716
\.


--
-- Data for Name: currency; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.currency (id, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id, label, symbol, rate) FROM stdin;
1	1	2015-11-03 14:48:10	\N	\N	\N	\N	\N	Euro	€	1
4	1	2015-11-03 14:58:31	\N	\N	\N	\N	\N	Yens	¥	132.651
3	1	2015-11-03 14:57:20	\N	\N	\N	\N	\N	Livre	£	0.7133
2	1	2015-11-03 14:56:38	\N	\N	\N	\N	\N	Dollars	$	1.096
\.


--
-- Data for Name: datetype; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.datetype (id, label, description, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id, facet, recursivity, finishable) FROM stdin;
19	Rapport final		1	2016-02-08 13:30:42	\N	\N	\N	\N	\N	Scientifique	\N	f
21	Soutenance de thèse		1	2016-02-08 13:31:40	\N	\N	\N	\N	\N	Scientifique	\N	f
53	Rapport financier		1	2016-08-26 13:53:40	\N	\N	\N	\N	\N	Financier		t
55	Soumission du projet		1	2018-02-08 18:09:50	\N	\N	\N	\N	\N	Administratif		t
52	Date de fin d'éligibilité des dépenses		1	2016-04-07 12:58:56	\N	\N	\N	\N	\N	Financier		f
3	Début d'éligibilité des dépenses		1	2016-01-27 14:26:21	\N	\N	\N	\N	\N	Financier		f
7	Dépôt de dossier		1	2016-01-27 14:49:01	\N	\N	\N	\N	\N	Administratif		f
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
-- Data for Name: doctrine_migration_versions; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.doctrine_migration_versions (version, executed_at, execution_time) FROM stdin;
\.


--
-- Data for Name: estimatedspentline; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.estimatedspentline (id, activity_id, year, amount, account) FROM stdin;
\.


--
-- Data for Name: logactivity; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.logactivity (id, datecreated, message, context, contextid, userid, level, type, ip, datas) FROM stdin;
221	2024-09-03 14:17:39	2:Admin a supprimé le rôle Chargé de mission Europe	Application	-1	2	100	info	127.0.0.1	a:1:{s:11:"REQUEST_URI";s:28:"/administration/api/roles/20";}
226	2024-09-04 14:04:19	2:Admin a ajouté [Person:1:Stéphane Bouvry] à la liste des personnes	Person:new	1	2	200	info	127.0.0.1	a:1:{s:11:"REQUEST_URI";s:11:"/person/new";}
222	2024-09-03 14:17:57	2:Admin a supprimé le rôle Directeur de composante	Application	-1	2	100	info	127.0.0.1	a:1:{s:11:"REQUEST_URI";s:28:"/administration/api/roles/22";}
227	2024-09-04 14:06:09	2:Admin a supprimé le rôle Chargé de mission Europe	Application	-1	2	100	info	127.0.0.1	a:1:{s:11:"REQUEST_URI";s:28:"/administration/api/roles/20";}
223	2024-09-03 14:19:13	2:Admin a supprimé le rôle Directeur de composante	Application	-1	2	100	info	127.0.0.1	a:1:{s:11:"REQUEST_URI";s:28:"/administration/api/roles/22";}
228	2024-09-04 14:06:42	2:Admin a mis à jour le rôle Directeur	Application	-1	2	100	info	127.0.0.1	a:1:{s:11:"REQUEST_URI";s:28:"/administration/api/roles/21";}
224	2024-09-03 15:01:20	2:Admin a supprimé le rôle Responsable administratif et gestionnaire de composante	Application	-1	2	100	info	127.0.0.1	a:1:{s:11:"REQUEST_URI";s:28:"/administration/api/roles/23";}
229	2024-09-04 14:07:07	2:Admin a mis à jour le rôle Gestionnaire	Application	-1	2	100	info	127.0.0.1	a:1:{s:11:"REQUEST_URI";s:28:"/administration/api/roles/24";}
225	2024-09-03 15:01:31	2:Admin a supprimé le rôle Directeur de composante	Application	-1	2	100	info	127.0.0.1	a:1:{s:11:"REQUEST_URI";s:28:"/administration/api/roles/22";}
201	2024-08-29 08:43:16	2:Admin a déclenché un circuit de signature pour '1-contrat-recherche-v3-final.pdf'	Activity	1	2	100	info	127.0.0.1	a:1:{s:11:"REQUEST_URI";s:42:"/documents-des-contracts/process/create/19";}
202	2024-08-29 12:50:46	2:Admin a déclenché un circuit de signature pour '1-contrat-recherche-v3-final.pdf'	Activity	1	2	100	info	127.0.0.1	a:1:{s:11:"REQUEST_URI";s:42:"/documents-des-contracts/process/create/19";}
203	2024-08-29 14:28:32	2:Admin a annulé le circuit de signature pour '1-contrat-recherche-v3-final.pdf'	Activity	1	2	100	info	127.0.0.1	a:1:{s:11:"REQUEST_URI";s:42:"/documents-des-contracts/process/delete/19";}
204	2024-08-29 14:30:19	2:Admin a déclenché un circuit de signature pour '1-contrat-recherche-v3-final.pdf'	Activity	1	2	100	info	127.0.0.1	a:1:{s:11:"REQUEST_URI";s:42:"/documents-des-contracts/process/create/19";}
205	2024-09-02 14:46:51	2:Admin a ajouté le jalon 15 Sep 2024 (Soumission du projet) dans l'activité [Activity:2:RANDOM]	Activity	2	2	100	info	127.0.0.1	a:1:{s:11:"REQUEST_URI";s:9:"/jalons/2";}
206	2024-09-02 14:46:51	2:Admin a ajouté le jalon 15 Sep 2024 (Soumission du projet) dans  l'activité [Activity:2:RANDOM]	Activity	2	2	100	info	127.0.0.1	a:1:{s:11:"REQUEST_URI";s:9:"/jalons/2";}
207	2024-09-02 14:46:59	2:Admin a supprimé le jalon 15 Sep 2024 (Soumission du projet) dans  l'activité [Activity:2:RANDOM]	Activity	2	2	100	info	127.0.0.1	a:1:{s:11:"REQUEST_URI";s:14:"/jalons/2?id=1";}
208	2024-09-02 14:50:33	2:Admin a ajouté le jalon 10 Sep 2024 (Soumission du projet) dans l'activité [Activity:2:RANDOM]	Activity	2	2	100	info	127.0.0.1	a:1:{s:11:"REQUEST_URI";s:9:"/jalons/2";}
209	2024-09-02 14:50:33	2:Admin a ajouté le jalon 10 Sep 2024 (Soumission du projet) dans  l'activité [Activity:2:RANDOM]	Activity	2	2	100	info	127.0.0.1	a:1:{s:11:"REQUEST_URI";s:9:"/jalons/2";}
210	2024-09-02 14:50:55	2:Admin a ajouté le jalon 01 Sep 2024 (Soumission du projet) dans l'activité [Activity:1:TEST]	Activity	1	2	100	info	127.0.0.1	a:1:{s:11:"REQUEST_URI";s:9:"/jalons/1";}
211	2024-09-02 14:50:55	2:Admin a ajouté le jalon 01 Sep 2024 (Soumission du projet) dans  l'activité [Activity:1:TEST]	Activity	1	2	100	info	127.0.0.1	a:1:{s:11:"REQUEST_URI";s:9:"/jalons/1";}
212	2024-09-02 14:51:03	2:Admin a modifié l'état du jalon 01 Sep 2024 (Soumission du projet) dans  l'activité [Activity:1:TEST] pour cancel	Activity	1	2	100	info	127.0.0.1	a:1:{s:11:"REQUEST_URI";s:9:"/jalons/1";}
213	2024-09-02 14:51:03	2:Admin a modifié la progression du jalon 01 Sep 2024 (Soumission du projet) dans l'activité [Activity:1:TEST]	Activity	1	2	100	info	127.0.0.1	a:1:{s:11:"REQUEST_URI";s:9:"/jalons/1";}
214	2024-09-03 13:39:42	2:Admin a supprimé [Person:1:STEPHANE BOUVRY](Responsable scientifique) dans l'activité [Activity:2:RANDOM] 	Activity:person	2	2	100	info	127.0.0.1	a:1:{s:11:"REQUEST_URI";s:32:"/activites-de-recherche/delete/2";}
215	2024-09-03 13:39:42	2:Admin a supprimé [Person:2:ANTONY LE COURTES](Chargé de valorisation) dans l'activité [Activity:2:RANDOM] 	Activity:person	2	2	100	info	127.0.0.1	a:1:{s:11:"REQUEST_URI";s:32:"/activites-de-recherche/delete/2";}
216	2024-09-03 13:39:42	2:Admin a supprimé [Person:2:ANTONY LE COURTES](Responsable scientifique) dans l'activité [Activity:2:RANDOM] 	Activity:person	2	2	100	info	127.0.0.1	a:1:{s:11:"REQUEST_URI";s:32:"/activites-de-recherche/delete/2";}
217	2024-09-03 13:39:42	2:Admin  a supprimé l'organisation [Organization:1:[CODEA] LELABO Laboratoire de Recherche (Caen)] de l'activité [Activity:2:RANDOM]	Activity	2	2	100	info	127.0.0.1	a:1:{s:11:"REQUEST_URI";s:32:"/activites-de-recherche/delete/2";}
218	2024-09-03 13:39:43	2:Admin  a supprimé l'organisation [Organization:1:[CODEA] LELABO Laboratoire de Recherche (Caen)] de l'activité [Activity:2:RANDOM]	Activity	2	2	100	info	127.0.0.1	a:1:{s:11:"REQUEST_URI";s:32:"/activites-de-recherche/delete/2";}
219	2024-09-03 13:39:43	2:Admin  a supprimé l'organisation [Organization:1:[CODEA] LELABO Laboratoire de Recherche (Caen)] de l'activité [Activity:2:RANDOM]	Activity	2	2	100	info	127.0.0.1	a:1:{s:11:"REQUEST_URI";s:32:"/activites-de-recherche/delete/2";}
220	2024-09-03 13:39:52	2:Admin a supprimé [Person:1:STEPHANE BOUVRY](Responsable scientifique) dans l'activité [Activity:1:TEST] 	Activity:person	1	2	100	info	127.0.0.1	a:1:{s:11:"REQUEST_URI";s:32:"/activites-de-recherche/delete/1";}
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

COPY public.organization (id, centaureid, shortname, fullname, code, email, url, description, street1, street2, street3, city, zipcode, phone, dateupdated, datecreated, dateend, datestart, status, datedeleted, createdby_id, updatedby_id, deletedby_id, ldapsupanncodeentite, country, sifacid, codepays, siret, bp, type, sifacgroup, sifacgroupid, numtvaca, connectors, typeobj_id, parent_id, labintel, rnsr, duns, tvaintra) FROM stdin;
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
-- Data for Name: pcrupolecompetitivite; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.pcrupolecompetitivite (id, label) FROM stdin;
\.


--
-- Data for Name: pcrusourcefinancement; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.pcrusourcefinancement (id, label) FROM stdin;
\.


--
-- Data for Name: pcrutypecontract; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.pcrutypecontract (id, label, activitytype_id) FROM stdin;
\.


--
-- Data for Name: person; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.person (id, firstname, lastname, codeharpege, centaureid, codeldap, email, ldapstatus, ldapsitelocation, ldapaffectation, ldapdisabled, ldapfininscription, ladaplogin, phone, datesyncldap, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id, emailprive, harpegeinm, connectors, ldapmemberof, customsettings, schedulekey) FROM stdin;
\.


--
-- Data for Name: person_activity_validator_adm; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.person_activity_validator_adm (activity_id, person_id) FROM stdin;
\.


--
-- Data for Name: person_activity_validator_prj; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.person_activity_validator_prj (activity_id, person_id) FROM stdin;
\.


--
-- Data for Name: person_activity_validator_sci; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.person_activity_validator_sci (activity_id, person_id) FROM stdin;
\.


--
-- Data for Name: persons_documents; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.persons_documents (contractdocument_id, person_id) FROM stdin;
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
13	2	EXPORT	Exporter les données des activités	\N	17	4
15	2	ORGANIZATION_MANAGE	Gestion des partenaires d'une activité	\N	31	7
19	2	EDIT	Modifier les informations générales d'une activité	\N	18	7
17	2	INDEX	Afficher / rechercher dans les activités	\N	\N	4
23	2	MILESTONE_MANAGE	Peut gérer les jalons	\N	22	7
25	2	DOCUMENT_MANAGE	Peut gérer les documents (Ajouter)	\N	24	7
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
84	3	MANAGE_SCHEDULE	Peut  modifier et valider la répartition horaire d'une personne	\N	36	7
85	3	SHOW_SCHEDULE	Peut  voir la répartition horaire d'une personne	\N	36	7
86	4	DELETE	Autorise la suppression définitive d'une organisation	\N	40	4
87	2	TIMESHEET_VALIDATE_ACTIVITY	Validation niveau activité des feuilles de temps	\N	78	7
89	2	REQUEST	Faire une demande d'activité	\N	\N	4
90	6	TVA_MANAGE	Configurer les TVAs disponibles	\N	\N	7
96	2	REQUEST_MANAGE	Traiter les demandes d'activité	\N	\N	4
97	2	REQUEST_ADMIN	Administrer toutes les demandes d'activité	\N	\N	4
101	3	FEED_TIMESHEET	Peut compléter les feuilles de temps de n'importe quel déclarant	\N	\N	7
2	1	INDEX	Lister et rechercher dans les projets	\N	\N	6
16	2	PAYMENT_MANAGE	Gestion des versements d'une activité	\N	20	7
26	2	DUPLICATE	Peut dupliquer l'activité	\N	\N	4
88	6	VALIDATION_MANAGE	Peut gérer, modifier ou supprimer l'état des déclarations envoyées	\N	\N	7
102	7	API_ACCESS	Gérer les accès à l'API	\N	\N	4
103	2	CREATE	Créer une nouvelle activité de recherche	\N	\N	4
104	2	PCRU	Permet d'afficher les informations PCRU de l'activité de recherche	\N	18	7
105	2	PCRU_ACTIVATE	Permet d'activer les données PCRU pour une activité	\N	18	7
106	2	CONTRACT_SHOW	Voir le contrat signé	\N	18	7
107	2	CONTRACT_SEND	Soumettre un contrat signé	\N	18	7
108	2	ESTIMATEDSPENT_SHOW	Voir les dépenses prévisionnelles	\N	\N	7
109	2	ESTIMATEDSPENT_MANAGE	Gestion des dépenses prévisionnelle de l'activité	\N	108	7
110	2	DOCUMENT_DELETEDSIGNED	Peut supprimer un document signé	\N	24	7
111	9	DETAILS	Voir le détail des dépenses	\N	\N	7
112	9	SYNC	Peut forcer la synchronisation des dépenses	\N	\N	7
113	9	DOWNLOAD	Peut télécharger les dépenses (Excel/CSV)	\N	\N	7
114	9	RECETTES	Peut voir les recettes	\N	\N	7
115	9	IGNORED	Peut voir les données ignorées	\N	\N	7
116	6	SPENDTYPEGROUP_MANAGE	Configuration des types de dépenses	\N	\N	7
117	6	PCRU_LIST	Peut visualiser la liste des données PCRU	\N	\N	7
118	6	PCRU_UPLOAD	Peut déclencher manuellement le transfert des donnèes vers PCRU	\N	\N	7
119	6	DOCUMENTTYPE_MANAGE	Configurer les types de document disponibles	\N	\N	7
120	6	DOCPUBSEC_MANAGE	Configurer les sections des documents publiques	\N	\N	7
121	6	NUMEROTATION_MANAGE	Configurer les numérotations disponibles pour les activités	\N	\N	7
122	6	PARAMETERS_MANAGE	Peut gérer les paramètres	\N	\N	7
123	10	SIGNATURE_INDEX	Liste des signatures	\N	\N	7
124	10	SIGNATURE_DELETE	Suppression des signatures	\N	\N	7
125	10	SIGNATURE_CREATE	Création de signature	\N	\N	7
126	10	SIGNATURE_SYNC	Synchronisation de signature	\N	\N	7
127	10	SIGNATURE_ADMIN	Accès à l'interface d'administration / gestion des signatures et processus en cours	\N	\N	7
128	10	SIGNATURE_ADMIN_CONFIG	Configuration des processus métier	\N	\N	7
74	6	NOTIFICATION_PERSON	Peut notifier manuellement une personne	\N	\N	7
129	6	SIGNATURE_DELETE	Peut supprimer les documents signés	\N	\N	7
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
-- Data for Name: recalldeclaration; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.recalldeclaration (id, person_id, periodyear, periodmonth, context, startprocess, lastsend, history, shipments) FROM stdin;
\.


--
-- Data for Name: recallexception; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.recallexception (id, person_id, type) FROM stdin;
\.


--
-- Data for Name: referent; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.referent (id, referent_id, person_id, datestart, dateend) FROM stdin;
\.


--
-- Data for Name: role_datetype; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.role_datetype (datetype_id, role_id) FROM stdin;
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
10	1
10	7
11	1
11	7
12	1
12	7
1	1
1	7
1	6
2	1
2	7
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
18	1
18	7
20	1
20	7
22	1
22	7
23	1
23	7
24	1
24	7
25	1
25	7
26	1
26	7
27	7
27	1
28	1
70	1
69	1
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
17	24
20	24
24	24
31	24
39	24
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
67	24
78	10
78	21
78	24
78	7
78	8
78	9
78	15
87	10
89	21
83	1
62	1
89	1
96	1
89	6
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
116	1
117	1
118	1
119	1
120	1
121	1
122	1
103	1
123	1
124	1
125	1
126	1
127	1
128	1
129	1
111	1
112	1
113	1
115	1
62	9
111	9
113	9
114	9
115	9
62	10
62	21
62	24
62	7
111	7
113	7
114	7
114	24
114	21
114	10
114	15
62	15
62	8
114	8
115	7
51	15
\.


--
-- Data for Name: spentline; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.spentline (id, syncid, pfi, rldnr, btart, numsifac, numcommandeaff, numpiece, numfournisseur, pieceref, codesociete, codeservicefait, codedomainefonct, designation, textefacture, typedocument, montant, centredeprofit, comptebudgetaire, centrefinancier, comptegeneral, datepiece, datecomptable, dateanneeexercice, datepaiement, dateservicefait) FROM stdin;
\.


--
-- Data for Name: spenttypegroup; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.spenttypegroup (id, parent_id, label, description, code, annexe, rgt, lft, blind, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id) FROM stdin;
\.


--
-- Data for Name: tabdocument; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.tabdocument (id, label, description, isdefault) FROM stdin;
1	Test		f
\.


--
-- Data for Name: tabsdocumentsroles; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.tabsdocumentsroles (id, role_id, access, tabdocument_id) FROM stdin;
40	1	2	1
41	8	1	1
42	9	1	1
43	11	0	1
44	15	1	1
46	10	2	1
48	21	1	1
49	24	1	1
51	7	2	1
52	6	0	1
\.


--
-- Data for Name: timesheet; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.timesheet (id, workpackage_id, person_id, datefrom, dateto, comment, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id, activity_id, label, sendby, icsuid, icsfileuid, icsfilename, icsfiledateadded, datesync, syncid, validationperiod_id) FROM stdin;
\.


--
-- Data for Name: timesheetcommentperiod; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.timesheetcommentperiod (id, declarer_id, object, objectgroup, object_id, comment, month, year) FROM stdin;
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
3	Taux normal (19,6%)	19.6	t	1	\N	\N	\N	\N	\N	\N
4	Taux DOM-TOM	8.5	t	1	\N	\N	\N	\N	\N	\N
5	Taux réduit 7%	7	t	1	\N	\N	\N	\N	\N	\N
6	Taux normal 20%	20	t	1	\N	\N	\N	\N	\N	\N
7	Taux réduit 10%	10	f	1	\N	\N	\N	\N	\N	\N
\.


--
-- Data for Name: typedocument; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.typedocument (id, label, description, codecentaure, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id, isdefault, signatureflow_id) FROM stdin;
1	Bordereau d'envoi	Importé depuis centaure	BORD	1	2015-12-03 14:36:30	\N	\N	\N	\N	\N	f	\N
2	Fiche d'analyse	Importé depuis centaure	ANA	1	2015-12-03 14:36:30	\N	\N	\N	\N	\N	f	\N
3	Document de travail	Importé depuis centaure	DOC	1	2015-12-03 14:36:30	\N	\N	\N	\N	\N	f	\N
4	Annexe	Importé depuis centaure	ANN	1	2015-12-03 14:36:30	\N	\N	\N	\N	\N	f	\N
6	Contrat Version Définitive Signée	Importé depuis centaure	VDEF	1	2015-12-03 14:36:30	\N	\N	\N	\N	\N	f	\N
7	Annexe budgétaire lors de l'ouverture du contrat	Importé depuis centaure	ANN_BUDGET	1	2015-12-03 14:36:30	\N	\N	\N	\N	\N	f	\N
10	Fiche mouvement Contractuel	\N	\N	1	2016-05-18 11:06:54	\N	\N	\N	\N	\N	f	\N
\.


--
-- Data for Name: unicaen_signature_notification; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.unicaen_signature_notification (id, datecreated, datelastsend, context, send, message, signaturerecipient_id, signatureobserver_id, subject) FROM stdin;
\.


--
-- Data for Name: unicaen_signature_observer; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.unicaen_signature_observer (id, signature_id, firstname, lastname, email) FROM stdin;
\.


--
-- Data for Name: unicaen_signature_process; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.unicaen_signature_process (id, datecreated, lastupdate, status, currentstep, document_name, signatureflow_id) FROM stdin;
\.


--
-- Data for Name: unicaen_signature_process_step; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.unicaen_signature_process_step (id, process_id, signature_id, signatureflowstep_id) FROM stdin;
\.


--
-- Data for Name: unicaen_signature_recipient; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.unicaen_signature_recipient (id, signature_id, status, firstname, lastname, email, phone, dateupdate, datefinished, keyaccess, informations, urldocument) FROM stdin;
\.


--
-- Data for Name: unicaen_signature_signature; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.unicaen_signature_signature (id, datecreated, type, status, ordering, label, description, datesend, dateupdate, document_path, document_remotekey, document_localkey, context_short, context_long, letterfile_key, letterfile_process, letterfile_url, allsigntocomplete, notificationsrecipients) FROM stdin;
\.


--
-- Data for Name: unicaen_signature_signatureflow; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.unicaen_signature_signatureflow (id, label, description, enabled) FROM stdin;
\.


--
-- Data for Name: unicaen_signature_signatureflowstep; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.unicaen_signature_signatureflowstep (id, recipientsmethod, label, letterfilename, signlevel, ordering, allrecipientssign, notificationsrecipients, editablerecipients, options, observers_options, observersmethod, signatureflow_id) FROM stdin;
\.


--
-- Data for Name: user_role; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.user_role (id, parent_id, role_id, is_default, ldap_filter, spot, description, principal, displayed, accessible_exterieur) FROM stdin;
1	\N	Administrateur	f	\N	4	\N	f	t	t
8	\N	Responsable RH	f	\N	6	\N	f	t	t
9	\N	Responsable financier	f	(memberOf=cn=projet_oscar_agence_comptable,ou=groups,dc=unicaen,dc=fr)	6	\N	f	t	t
11	\N	Ingénieur	f	\N	1	\N	f	t	t
15	\N	Responsable juridique	f	\N	6	\N	f	t	t
10	\N	Responsable scientifique	f	\N	3		t	t	t
7	\N	Chargé de valorisation	f	(memberOf=cn=structure_dir-recherche-innov,ou=groups,dc=unicaen,dc=fr)	7		t	t	t
6	\N	user	t	\N	4	Rôle par défaut	f	t	t
21	\N	Directeur	f	\N	2	Contient la liste des directeurs de laboratoires/composante et assimilés (directeurs adjoints, directeurs temporaire, etc.)	t	t	t
24	\N	Gestionnaire	f	\N	2	Gestionnaire de laboratoire / composante	t	t	t
\.


--
-- Data for Name: useraccessdefinition; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.useraccessdefinition (id, context, label, description, key) FROM stdin;
\.


--
-- Data for Name: validationperiod; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.validationperiod (id, declarer_id, object, objectgroup, object_id, month, year, datesend, log, validationactivityat, validationactivityby, validationactivitybyid, validationactivitymessage, validationsciat, validationsciby, validationscibyid, validationscimessage, validationadmat, validationadmby, validationadmbyid, validationadmmessage, rejectactivityat, rejectactivityby, rejectactivitybyid, rejectactivitymessage, rejectsciat, rejectsciby, rejectscibyid, rejectscimessage, rejectadmat, rejectadmby, rejectadmbyid, rejectadmmessage, schedule, status, validatorsprjdefault, validatorsscidefault, validatorsadmdefault, comment) FROM stdin;
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

SELECT pg_catalog.setval('public.activity_id_seq', 2, true);


--
-- Name: activitydate_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.activitydate_id_seq', 3, true);


--
-- Name: activityorganization_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.activityorganization_id_seq', 5, true);


--
-- Name: activitypayment_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.activitypayment_id_seq', 1, false);


--
-- Name: activitypcruinfos_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.activitypcruinfos_id_seq', 1, false);


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

SELECT pg_catalog.setval('public.activitytype_id_seq', 715, false);


--
-- Name: administrativedocument_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.administrativedocument_id_seq', 1, false);


--
-- Name: administrativedocumentsection_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.administrativedocumentsection_id_seq', 4, true);


--
-- Name: authentification_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.authentification_id_seq', 3, false);


--
-- Name: categorie_privilege_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.categorie_privilege_id_seq', 11, false);


--
-- Name: contractdocument_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.contractdocument_id_seq', 1, false);


--
-- Name: contracttype_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.contracttype_id_seq', 232, false);


--
-- Name: country3166_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.country3166_id_seq', 249, true);


--
-- Name: currency_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.currency_id_seq', 5, false);


--
-- Name: datetype_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.datetype_id_seq', 56, false);


--
-- Name: discipline_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.discipline_id_seq', 120, false);


--
-- Name: estimatedspentline_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.estimatedspentline_id_seq', 1, false);


--
-- Name: grantsource_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.grantsource_id_seq', 33, true);


--
-- Name: logactivity_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.logactivity_id_seq', 229, true);


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
-- Name: pcrupolecompetitivite_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.pcrupolecompetitivite_id_seq', 1, false);


--
-- Name: pcrusourcefinancement_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.pcrusourcefinancement_id_seq', 1, false);


--
-- Name: pcrutypecontract_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.pcrutypecontract_id_seq', 1, false);


--
-- Name: person_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.person_id_seq', 1, false);


--
-- Name: privilege_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.privilege_id_seq', 130, false);


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

SELECT pg_catalog.setval('public.projectmember_id_seq', 1, false);


--
-- Name: projectpartner_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.projectpartner_id_seq', 1, false);


--
-- Name: recalldeclaration_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.recalldeclaration_id_seq', 1, false);


--
-- Name: recallexception_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.recallexception_id_seq', 1, false);


--
-- Name: referent_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.referent_id_seq', 1, true);


--
-- Name: role_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.role_id_seq', 25, false);


--
-- Name: spentline_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.spentline_id_seq', 1, false);


--
-- Name: spenttypegroup_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.spenttypegroup_id_seq', 1, false);


--
-- Name: tabdocument_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.tabdocument_id_seq', 2, true);


--
-- Name: tabsdocumentsroles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.tabsdocumentsroles_id_seq', 63, true);


--
-- Name: timesheet_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.timesheet_id_seq', 1, false);


--
-- Name: timesheetcommentperiod_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.timesheetcommentperiod_id_seq', 1, false);


--
-- Name: tva_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.tva_id_seq', 8, false);


--
-- Name: typedocument_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.typedocument_id_seq', 11, false);


--
-- Name: unicaen_signature_notification_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.unicaen_signature_notification_id_seq', 29, true);


--
-- Name: unicaen_signature_observer_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.unicaen_signature_observer_id_seq', 303, true);


--
-- Name: unicaen_signature_process_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.unicaen_signature_process_id_seq', 79, true);


--
-- Name: unicaen_signature_process_step_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.unicaen_signature_process_step_id_seq', 186, true);


--
-- Name: unicaen_signature_recipient_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.unicaen_signature_recipient_id_seq', 226, true);


--
-- Name: unicaen_signature_signature_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.unicaen_signature_signature_id_seq', 199, true);


--
-- Name: unicaen_signature_signatureflow_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.unicaen_signature_signatureflow_id_seq', 2, true);


--
-- Name: unicaen_signature_signatureflowstep_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.unicaen_signature_signatureflowstep_id_seq', 5, true);


--
-- Name: user_role_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.user_role_id_seq', 25, false);


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

SELECT pg_catalog.setval('public.workpackage_id_seq', 1, false);


--
-- Name: workpackageperson_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.workpackageperson_id_seq', 1, false);


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
-- Name: activitypcruinfos activitypcruinfos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activitypcruinfos
    ADD CONSTRAINT activitypcruinfos_pkey PRIMARY KEY (id);


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
-- Name: administrativedocumentsection administrativedocumentsection_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.administrativedocumentsection
    ADD CONSTRAINT administrativedocumentsection_pkey PRIMARY KEY (id);


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
-- Name: country3166 country3166_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.country3166
    ADD CONSTRAINT country3166_pkey PRIMARY KEY (id);


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
-- Name: doctrine_migration_versions doctrine_migration_versions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.doctrine_migration_versions
    ADD CONSTRAINT doctrine_migration_versions_pkey PRIMARY KEY (version);


--
-- Name: estimatedspentline estimatedspentline_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.estimatedspentline
    ADD CONSTRAINT estimatedspentline_pkey PRIMARY KEY (id);


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
-- Name: pcrupolecompetitivite pcrupolecompetitivite_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pcrupolecompetitivite
    ADD CONSTRAINT pcrupolecompetitivite_pkey PRIMARY KEY (id);


--
-- Name: pcrusourcefinancement pcrusourcefinancement_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pcrusourcefinancement
    ADD CONSTRAINT pcrusourcefinancement_pkey PRIMARY KEY (id);


--
-- Name: pcrutypecontract pcrutypecontract_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pcrutypecontract
    ADD CONSTRAINT pcrutypecontract_pkey PRIMARY KEY (id);


--
-- Name: person_activity_validator_adm person_activity_validator_adm_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.person_activity_validator_adm
    ADD CONSTRAINT person_activity_validator_adm_pkey PRIMARY KEY (activity_id, person_id);


--
-- Name: person_activity_validator_prj person_activity_validator_prj_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.person_activity_validator_prj
    ADD CONSTRAINT person_activity_validator_prj_pkey PRIMARY KEY (activity_id, person_id);


--
-- Name: person_activity_validator_sci person_activity_validator_sci_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.person_activity_validator_sci
    ADD CONSTRAINT person_activity_validator_sci_pkey PRIMARY KEY (activity_id, person_id);


--
-- Name: person person_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.person
    ADD CONSTRAINT person_pkey PRIMARY KEY (id);


--
-- Name: persons_documents persons_documents_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.persons_documents
    ADD CONSTRAINT persons_documents_pkey PRIMARY KEY (contractdocument_id, person_id);


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
-- Name: recalldeclaration recalldeclaration_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.recalldeclaration
    ADD CONSTRAINT recalldeclaration_pkey PRIMARY KEY (id);


--
-- Name: recallexception recallexception_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.recallexception
    ADD CONSTRAINT recallexception_pkey PRIMARY KEY (id);


--
-- Name: referent referent_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.referent
    ADD CONSTRAINT referent_pkey PRIMARY KEY (id);


--
-- Name: role_datetype role_datetype_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.role_datetype
    ADD CONSTRAINT role_datetype_pkey PRIMARY KEY (datetype_id, role_id);


--
-- Name: role_privilege role_privilege_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.role_privilege
    ADD CONSTRAINT role_privilege_pkey PRIMARY KEY (privilege_id, role_id);


--
-- Name: spentline spentline_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.spentline
    ADD CONSTRAINT spentline_pkey PRIMARY KEY (id);


--
-- Name: spenttypegroup spenttypegroup_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.spenttypegroup
    ADD CONSTRAINT spenttypegroup_pkey PRIMARY KEY (id);


--
-- Name: tabdocument tabdocument_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tabdocument
    ADD CONSTRAINT tabdocument_pkey PRIMARY KEY (id);


--
-- Name: tabsdocumentsroles tabsdocumentsroles_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tabsdocumentsroles
    ADD CONSTRAINT tabsdocumentsroles_pkey PRIMARY KEY (id);


--
-- Name: timesheet timesheet_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.timesheet
    ADD CONSTRAINT timesheet_pkey PRIMARY KEY (id);


--
-- Name: timesheetcommentperiod timesheetcommentperiod_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.timesheetcommentperiod
    ADD CONSTRAINT timesheetcommentperiod_pkey PRIMARY KEY (id);


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
-- Name: unicaen_signature_notification unicaen_signature_notification_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.unicaen_signature_notification
    ADD CONSTRAINT unicaen_signature_notification_pkey PRIMARY KEY (id);


--
-- Name: unicaen_signature_observer unicaen_signature_observer_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.unicaen_signature_observer
    ADD CONSTRAINT unicaen_signature_observer_pkey PRIMARY KEY (id);


--
-- Name: unicaen_signature_process unicaen_signature_process_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.unicaen_signature_process
    ADD CONSTRAINT unicaen_signature_process_pkey PRIMARY KEY (id);


--
-- Name: unicaen_signature_process_step unicaen_signature_process_step_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.unicaen_signature_process_step
    ADD CONSTRAINT unicaen_signature_process_step_pkey PRIMARY KEY (id);


--
-- Name: unicaen_signature_recipient unicaen_signature_recipient_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.unicaen_signature_recipient
    ADD CONSTRAINT unicaen_signature_recipient_pkey PRIMARY KEY (id);


--
-- Name: unicaen_signature_signature unicaen_signature_signature_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.unicaen_signature_signature
    ADD CONSTRAINT unicaen_signature_signature_pkey PRIMARY KEY (id);


--
-- Name: unicaen_signature_signatureflow unicaen_signature_signatureflow_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.unicaen_signature_signatureflow
    ADD CONSTRAINT unicaen_signature_signatureflow_pkey PRIMARY KEY (id);


--
-- Name: unicaen_signature_signatureflowstep unicaen_signature_signatureflowstep_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.unicaen_signature_signatureflowstep
    ADD CONSTRAINT unicaen_signature_signatureflowstep_pkey PRIMARY KEY (id);


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
-- Name: idx_317c034e217bbb47; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_317c034e217bbb47 ON public.person_activity_validator_adm USING btree (person_id);


--
-- Name: idx_317c034e81c06096; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_317c034e81c06096 ON public.person_activity_validator_adm USING btree (activity_id);


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
-- Name: idx_3f07201e3174800f; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_3f07201e3174800f ON public.spenttypegroup USING btree (createdby_id);


--
-- Name: idx_3f07201e63d8c20e; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_3f07201e63d8c20e ON public.spenttypegroup USING btree (deletedby_id);


--
-- Name: idx_3f07201e65ff1aec; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_3f07201e65ff1aec ON public.spenttypegroup USING btree (updatedby_id);


--
-- Name: idx_3f07201e727aca70; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_3f07201e727aca70 ON public.spenttypegroup USING btree (parent_id);


--
-- Name: idx_48506726217bbb47; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_48506726217bbb47 ON public.validationperiod_adm USING btree (person_id);


--
-- Name: idx_4850672625e297e4; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_4850672625e297e4 ON public.validationperiod_adm USING btree (validationperiod_id);


--
-- Name: idx_4a390fe81b50f2d9; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_4a390fe81b50f2d9 ON public.contractdocument USING btree (tabdocument_id);


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
-- Name: idx_4a390fe87ec2f574; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_4a390fe87ec2f574 ON public.contractdocument USING btree (process_id);


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
-- Name: idx_55026b0c8c8fc2fe; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_55026b0c8c8fc2fe ON public.activity USING btree (pcrupolecompetitivite_id);


--
-- Name: idx_55026b0ca1b4b28c; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_55026b0ca1b4b28c ON public.activity USING btree (activitytype_id);


--
-- Name: idx_55026b0cb49d04; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_55026b0cb49d04 ON public.activity USING btree (pcrusourcefinancement_id);


--
-- Name: idx_55026b0cc54c8c93; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_55026b0cc54c8c93 ON public.activity USING btree (type_id);


--
-- Name: idx_5511ad90217bbb47; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_5511ad90217bbb47 ON public.persons_documents USING btree (person_id);


--
-- Name: idx_5511ad90b9352966; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_5511ad90b9352966 ON public.persons_documents USING btree (contractdocument_id);


--
-- Name: idx_57175ded81c06096; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_57175ded81c06096 ON public.estimatedspentline USING btree (activity_id);


--
-- Name: idx_5a6aef97d60322ac; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_5a6aef97d60322ac ON public.role_datetype USING btree (role_id);


--
-- Name: idx_5a6aef97d8cb54f3; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_5a6aef97d8cb54f3 ON public.role_datetype USING btree (datetype_id);


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
-- Name: idx_6547bd50b4090c8a; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_6547bd50b4090c8a ON public.typedocument USING btree (signatureflow_id);


--
-- Name: idx_66f2268e217bbb47; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_66f2268e217bbb47 ON public.person_activity_validator_sci USING btree (person_id);


--
-- Name: idx_66f2268e81c06096; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_66f2268e81c06096 ON public.person_activity_validator_sci USING btree (activity_id);


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
-- Name: idx_7358d996217bbb47; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_7358d996217bbb47 ON public.recallexception USING btree (person_id);


--
-- Name: idx_78e42a72217bbb47; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_78e42a72217bbb47 ON public.recalldeclaration USING btree (person_id);


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
-- Name: idx_994855d2b4090c8a; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_994855d2b4090c8a ON public.unicaen_signature_process USING btree (signatureflow_id);


--
-- Name: idx_a36732106f04e0; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_a36732106f04e0 ON public.activitypcruinfos USING btree (sourcefinancement_id);


--
-- Name: idx_a3673210ae24e5c2; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_a3673210ae24e5c2 ON public.activitypcruinfos USING btree (typecontrat_id);


--
-- Name: idx_a575dc3eb4090c8a; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_a575dc3eb4090c8a ON public.unicaen_signature_signatureflowstep USING btree (signatureflow_id);


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
-- Name: idx_a8a6ec6e3c21f464; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_a8a6ec6e3c21f464 ON public.timesheetcommentperiod USING btree (declarer_id);


--
-- Name: idx_ae64ea7d217bbb47; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_ae64ea7d217bbb47 ON public.person_activity_validator_prj USING btree (person_id);


--
-- Name: idx_ae64ea7d81c06096; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_ae64ea7d81c06096 ON public.person_activity_validator_prj USING btree (activity_id);


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
-- Name: idx_c311ba72d823e37a; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_c311ba72d823e37a ON public.administrativedocument USING btree (section_id);


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
-- Name: idx_cf70b0a57ec2f574; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_cf70b0a57ec2f574 ON public.unicaen_signature_process_step USING btree (process_id);


--
-- Name: idx_cf70b0a5c352c4; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_cf70b0a5c352c4 ON public.unicaen_signature_process_step USING btree (signatureflowstep_id);


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
-- Name: idx_d7f103ac1b50f2d9; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_d7f103ac1b50f2d9 ON public.tabsdocumentsroles USING btree (tabdocument_id);


--
-- Name: idx_d7f103acd60322ac; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_d7f103acd60322ac ON public.tabsdocumentsroles USING btree (role_id);


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
-- Name: idx_d9dfb884727aca70; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_d9dfb884727aca70 ON public.organization USING btree (parent_id);


--
-- Name: idx_d9dfb884e5915d19; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_d9dfb884e5915d19 ON public.organization USING btree (typeobj_id);


--
-- Name: idx_dc74ea6642e26054; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_dc74ea6642e26054 ON public.unicaen_signature_notification USING btree (signaturerecipient_id);


--
-- Name: idx_dc74ea669f268069; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_dc74ea669f268069 ON public.unicaen_signature_notification USING btree (signatureobserver_id);


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
-- Name: idx_eac19423ed61183a; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_eac19423ed61183a ON public.unicaen_signature_observer USING btree (signature_id);


--
-- Name: idx_f40fcdc4a1b4b28c; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_f40fcdc4a1b4b28c ON public.pcrutypecontract USING btree (activitytype_id);


--
-- Name: idx_f47c5330ed61183a; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_f47c5330ed61183a ON public.unicaen_signature_recipient USING btree (signature_id);


--
-- Name: polecompetivitelabel_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX polecompetivitelabel_idx ON public.pcrupolecompetitivite USING btree (label);


--
-- Name: sourcefinancementlabel_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX sourcefinancementlabel_idx ON public.pcrusourcefinancement USING btree (label);


--
-- Name: typecontractlabel_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX typecontractlabel_idx ON public.pcrutypecontract USING btree (label);


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
-- Name: uniq_a367321081c06096; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX uniq_a367321081c06096 ON public.activitypcruinfos USING btree (activity_id);


--
-- Name: uniq_a7821830ea750e8; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX uniq_a7821830ea750e8 ON public.organizationrole USING btree (label);


--
-- Name: uniq_cf70b0a5ed61183a; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX uniq_cf70b0a5ed61183a ON public.unicaen_signature_process_step USING btree (signature_id);


--
-- Name: activity activity_numauto; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER activity_numauto AFTER INSERT ON public.activity FOR EACH ROW EXECUTE FUNCTION public.oscar_activity_numauto();


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
-- Name: person_activity_validator_adm fk_317c034e217bbb47; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.person_activity_validator_adm
    ADD CONSTRAINT fk_317c034e217bbb47 FOREIGN KEY (person_id) REFERENCES public.person(id) ON DELETE CASCADE;


--
-- Name: person_activity_validator_adm fk_317c034e81c06096; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.person_activity_validator_adm
    ADD CONSTRAINT fk_317c034e81c06096 FOREIGN KEY (activity_id) REFERENCES public.activity(id) ON DELETE CASCADE;


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
-- Name: spenttypegroup fk_3f07201e3174800f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.spenttypegroup
    ADD CONSTRAINT fk_3f07201e3174800f FOREIGN KEY (createdby_id) REFERENCES public.person(id);


--
-- Name: spenttypegroup fk_3f07201e63d8c20e; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.spenttypegroup
    ADD CONSTRAINT fk_3f07201e63d8c20e FOREIGN KEY (deletedby_id) REFERENCES public.person(id);


--
-- Name: spenttypegroup fk_3f07201e65ff1aec; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.spenttypegroup
    ADD CONSTRAINT fk_3f07201e65ff1aec FOREIGN KEY (updatedby_id) REFERENCES public.person(id);


--
-- Name: spenttypegroup fk_3f07201e727aca70; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.spenttypegroup
    ADD CONSTRAINT fk_3f07201e727aca70 FOREIGN KEY (parent_id) REFERENCES public.spenttypegroup(id);


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
-- Name: contractdocument fk_4a390fe81b50f2d9; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.contractdocument
    ADD CONSTRAINT fk_4a390fe81b50f2d9 FOREIGN KEY (tabdocument_id) REFERENCES public.tabdocument(id);


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
-- Name: contractdocument fk_4a390fe87ec2f574; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.contractdocument
    ADD CONSTRAINT fk_4a390fe87ec2f574 FOREIGN KEY (process_id) REFERENCES public.unicaen_signature_process(id) ON DELETE SET NULL;


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
-- Name: activity fk_55026b0c8c8fc2fe; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activity
    ADD CONSTRAINT fk_55026b0c8c8fc2fe FOREIGN KEY (pcrupolecompetitivite_id) REFERENCES public.pcrupolecompetitivite(id) ON DELETE SET NULL;


--
-- Name: activity fk_55026b0ca1b4b28c; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activity
    ADD CONSTRAINT fk_55026b0ca1b4b28c FOREIGN KEY (activitytype_id) REFERENCES public.activitytype(id);


--
-- Name: activity fk_55026b0cb49d04; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activity
    ADD CONSTRAINT fk_55026b0cb49d04 FOREIGN KEY (pcrusourcefinancement_id) REFERENCES public.pcrusourcefinancement(id) ON DELETE SET NULL;


--
-- Name: activity fk_55026b0cc54c8c93; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activity
    ADD CONSTRAINT fk_55026b0cc54c8c93 FOREIGN KEY (type_id) REFERENCES public.contracttype(id);


--
-- Name: persons_documents fk_5511ad90217bbb47; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.persons_documents
    ADD CONSTRAINT fk_5511ad90217bbb47 FOREIGN KEY (person_id) REFERENCES public.person(id) ON DELETE CASCADE;


--
-- Name: persons_documents fk_5511ad90b9352966; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.persons_documents
    ADD CONSTRAINT fk_5511ad90b9352966 FOREIGN KEY (contractdocument_id) REFERENCES public.contractdocument(id) ON DELETE CASCADE;


--
-- Name: estimatedspentline fk_57175ded81c06096; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.estimatedspentline
    ADD CONSTRAINT fk_57175ded81c06096 FOREIGN KEY (activity_id) REFERENCES public.activity(id);


--
-- Name: role_datetype fk_5a6aef97d60322ac; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.role_datetype
    ADD CONSTRAINT fk_5a6aef97d60322ac FOREIGN KEY (role_id) REFERENCES public.user_role(id) ON DELETE CASCADE;


--
-- Name: role_datetype fk_5a6aef97d8cb54f3; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.role_datetype
    ADD CONSTRAINT fk_5a6aef97d8cb54f3 FOREIGN KEY (datetype_id) REFERENCES public.datetype(id) ON DELETE CASCADE;


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
-- Name: typedocument fk_6547bd50b4090c8a; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.typedocument
    ADD CONSTRAINT fk_6547bd50b4090c8a FOREIGN KEY (signatureflow_id) REFERENCES public.unicaen_signature_signatureflow(id);


--
-- Name: person_activity_validator_sci fk_66f2268e217bbb47; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.person_activity_validator_sci
    ADD CONSTRAINT fk_66f2268e217bbb47 FOREIGN KEY (person_id) REFERENCES public.person(id) ON DELETE CASCADE;


--
-- Name: person_activity_validator_sci fk_66f2268e81c06096; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.person_activity_validator_sci
    ADD CONSTRAINT fk_66f2268e81c06096 FOREIGN KEY (activity_id) REFERENCES public.activity(id) ON DELETE CASCADE;


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
-- Name: recallexception fk_7358d996217bbb47; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.recallexception
    ADD CONSTRAINT fk_7358d996217bbb47 FOREIGN KEY (person_id) REFERENCES public.person(id);


--
-- Name: recalldeclaration fk_78e42a72217bbb47; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.recalldeclaration
    ADD CONSTRAINT fk_78e42a72217bbb47 FOREIGN KEY (person_id) REFERENCES public.person(id);


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
    ADD CONSTRAINT fk_87209a8779066886 FOREIGN KEY (root_id) REFERENCES public.privilege(id) ON DELETE SET NULL;


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
-- Name: unicaen_signature_process fk_994855d2b4090c8a; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.unicaen_signature_process
    ADD CONSTRAINT fk_994855d2b4090c8a FOREIGN KEY (signatureflow_id) REFERENCES public.unicaen_signature_signatureflow(id);


--
-- Name: activitypcruinfos fk_a36732106f04e0; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activitypcruinfos
    ADD CONSTRAINT fk_a36732106f04e0 FOREIGN KEY (sourcefinancement_id) REFERENCES public.pcrusourcefinancement(id);


--
-- Name: activitypcruinfos fk_a367321081c06096; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activitypcruinfos
    ADD CONSTRAINT fk_a367321081c06096 FOREIGN KEY (activity_id) REFERENCES public.activity(id);


--
-- Name: activitypcruinfos fk_a3673210ae24e5c2; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activitypcruinfos
    ADD CONSTRAINT fk_a3673210ae24e5c2 FOREIGN KEY (typecontrat_id) REFERENCES public.pcrutypecontract(id);


--
-- Name: unicaen_signature_signatureflowstep fk_a575dc3eb4090c8a; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.unicaen_signature_signatureflowstep
    ADD CONSTRAINT fk_a575dc3eb4090c8a FOREIGN KEY (signatureflow_id) REFERENCES public.unicaen_signature_signatureflow(id);


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
-- Name: timesheetcommentperiod fk_a8a6ec6e3c21f464; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.timesheetcommentperiod
    ADD CONSTRAINT fk_a8a6ec6e3c21f464 FOREIGN KEY (declarer_id) REFERENCES public.person(id);


--
-- Name: person_activity_validator_prj fk_ae64ea7d217bbb47; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.person_activity_validator_prj
    ADD CONSTRAINT fk_ae64ea7d217bbb47 FOREIGN KEY (person_id) REFERENCES public.person(id) ON DELETE CASCADE;


--
-- Name: person_activity_validator_prj fk_ae64ea7d81c06096; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.person_activity_validator_prj
    ADD CONSTRAINT fk_ae64ea7d81c06096 FOREIGN KEY (activity_id) REFERENCES public.activity(id) ON DELETE CASCADE;


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
-- Name: administrativedocument fk_c311ba72d823e37a; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.administrativedocument
    ADD CONSTRAINT fk_c311ba72d823e37a FOREIGN KEY (section_id) REFERENCES public.administrativedocumentsection(id);


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
-- Name: unicaen_signature_process_step fk_cf70b0a57ec2f574; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.unicaen_signature_process_step
    ADD CONSTRAINT fk_cf70b0a57ec2f574 FOREIGN KEY (process_id) REFERENCES public.unicaen_signature_process(id);


--
-- Name: unicaen_signature_process_step fk_cf70b0a5c352c4; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.unicaen_signature_process_step
    ADD CONSTRAINT fk_cf70b0a5c352c4 FOREIGN KEY (signatureflowstep_id) REFERENCES public.unicaen_signature_signatureflowstep(id);


--
-- Name: unicaen_signature_process_step fk_cf70b0a5ed61183a; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.unicaen_signature_process_step
    ADD CONSTRAINT fk_cf70b0a5ed61183a FOREIGN KEY (signature_id) REFERENCES public.unicaen_signature_signature(id);


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
-- Name: tabsdocumentsroles fk_d7f103ac1b50f2d9; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tabsdocumentsroles
    ADD CONSTRAINT fk_d7f103ac1b50f2d9 FOREIGN KEY (tabdocument_id) REFERENCES public.tabdocument(id);


--
-- Name: tabsdocumentsroles fk_d7f103acd60322ac; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tabsdocumentsroles
    ADD CONSTRAINT fk_d7f103acd60322ac FOREIGN KEY (role_id) REFERENCES public.user_role(id);


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
-- Name: organization fk_d9dfb884727aca70; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.organization
    ADD CONSTRAINT fk_d9dfb884727aca70 FOREIGN KEY (parent_id) REFERENCES public.organization(id);


--
-- Name: organization fk_d9dfb884e5915d19; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.organization
    ADD CONSTRAINT fk_d9dfb884e5915d19 FOREIGN KEY (typeobj_id) REFERENCES public.organizationtype(id);


--
-- Name: unicaen_signature_notification fk_dc74ea6642e26054; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.unicaen_signature_notification
    ADD CONSTRAINT fk_dc74ea6642e26054 FOREIGN KEY (signaturerecipient_id) REFERENCES public.unicaen_signature_recipient(id);


--
-- Name: unicaen_signature_notification fk_dc74ea669f268069; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.unicaen_signature_notification
    ADD CONSTRAINT fk_dc74ea669f268069 FOREIGN KEY (signatureobserver_id) REFERENCES public.unicaen_signature_observer(id);


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
-- Name: unicaen_signature_observer fk_eac19423ed61183a; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.unicaen_signature_observer
    ADD CONSTRAINT fk_eac19423ed61183a FOREIGN KEY (signature_id) REFERENCES public.unicaen_signature_signature(id);


--
-- Name: pcrutypecontract fk_f40fcdc4a1b4b28c; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pcrutypecontract
    ADD CONSTRAINT fk_f40fcdc4a1b4b28c FOREIGN KEY (activitytype_id) REFERENCES public.activitytype(id);


--
-- Name: unicaen_signature_recipient fk_f47c5330ed61183a; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.unicaen_signature_recipient
    ADD CONSTRAINT fk_f47c5330ed61183a FOREIGN KEY (signature_id) REFERENCES public.unicaen_signature_signature(id);


--
-- PostgreSQL database dump complete
--

