--
-- PostgreSQL database dump
--

-- Dumped from database version 14.18
-- Dumped by pg_dump version 15.13 (Debian 15.13-0+deb12u1)

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


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: activity; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.activity (
                                 id integer NOT NULL,
                                 project_id integer,
                                 type_id integer,
                                 currency_id integer,
                                 tva_id integer,
                                 oscarid character varying(255) DEFAULT NULL::character varying,
                                 centaureid character varying(128) DEFAULT NULL::character varying,
                                 oscarnum character varying(20) DEFAULT NULL::character varying,
                                 centaurenumconvention character varying(64) DEFAULT NULL::character varying,
                                 pcruvalidpolecompetitivite boolean DEFAULT false NOT NULL,
                                 codeeotp character varying(64) DEFAULT NULL::character varying,
                                 fraisdegestion character varying(255) DEFAULT NULL::character varying,
                                 fraisdegestionparthebergeur character varying(255) DEFAULT NULL::character varying,
                                 fraisdegestionpartunite character varying(255) DEFAULT NULL::character varying,
                                 label character varying(255) DEFAULT NULL::character varying,
                                 description text,
                                 hassheet boolean,
                                 duration integer,
                                 justifyworkingtime integer,
                                 justifycost double precision,
                                 amount double precision,
                                 totalspent double precision,
                                 datetotalspent timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                 datestart date,
                                 dateend date,
                                 datesigned date,
                                 dateopened date,
                                 financialimpact character varying(32) DEFAULT 'Recette'::character varying NOT NULL,
                                 notefinanciere text,
                                 assiettesubventionnable double precision,
                                 timesheetformat character varying(255) DEFAULT 'none'::character varying NOT NULL,
                                 numbers text,
                                 status integer,
                                 datecreated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                 dateupdated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                 datedeleted timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                 activitytype_id integer,
                                 pcrupolecompetitivite_id integer,
                                 pcrusourcefinancement_id integer,
                                 createdby_id integer,
                                 updatedby_id integer,
                                 deletedby_id integer,
                                 fraisdegestionpartgestionnaire character varying(255) DEFAULT NULL::character varying,
                                 datecached date,
                                 datenegociation date,
                                 cache text DEFAULT ''::text,
                                 cachelocked boolean DEFAULT false NOT NULL,
                                 cachelockedreason character varying(255) DEFAULT ''::character varying,
                                 locked boolean DEFAULT false NOT NULL
);

CREATE TRIGGER activity_numauto AFTER INSERT ON public.activity FOR EACH ROW EXECUTE FUNCTION public.oscar_activity_numauto();

--
-- Name: COLUMN activity.numbers; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.activity.numbers IS '(DC2Type:object)';


--
-- Name: activity_activitymotcle; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.activity_activitymotcle (
                                                activity_id integer NOT NULL,
                                                activitymotcle_id integer NOT NULL
);


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
-- Name: activityavenant; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.activityavenant (
                                        id integer NOT NULL,
                                        activity_id integer,
                                        dateavenant timestamp(0) without time zone NOT NULL,
                                        filename character varying(255) NOT NULL,
                                        comment character varying(255) NOT NULL,
                                        status integer DEFAULT 100 NOT NULL
);


--
-- Name: activityavenant_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.activityavenant_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: activityavenant_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.activityavenant_id_seq OWNED BY public.activityavenant.id;


--
-- Name: activityavenantmodification; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.activityavenantmodification (
                                                    id integer NOT NULL,
                                                    avenant_id integer,
                                                    type character varying(255) NOT NULL,
                                                    newvalue1 character varying(255) DEFAULT NULL::character varying,
                                                    oldvalue1 character varying(255) DEFAULT NULL::character varying,
                                                    newvalue2 character varying(255) DEFAULT NULL::character varying,
                                                    oldvalue2 character varying(255) DEFAULT NULL::character varying,
                                                    info character varying(255) DEFAULT NULL::character varying
);


--
-- Name: activityavenantmodification_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.activityavenantmodification_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: activityavenantmodification_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.activityavenantmodification_id_seq OWNED BY public.activityavenantmodification.id;


--
-- Name: activitydate; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.activitydate (
                                     id integer NOT NULL,
                                     type_id integer,
                                     activity_id integer,
                                     datestart date NOT NULL,
                                     comment text,
                                     finished integer,
                                     finishedby character varying(255) DEFAULT NULL::character varying,
                                     datefinish date,
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

CREATE SEQUENCE public.activitydate_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: activitymotcle; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.activitymotcle (
                                       id integer NOT NULL,
                                       label character varying(128) NOT NULL,
                                       datecreated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                       createdby_id integer
);


--
-- Name: activitymotcle_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.activitymotcle_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: activitymotcle_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.activitymotcle_id_seq OWNED BY public.activitymotcle.id;


--
-- Name: activitynote; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.activitynote (
                                     id integer NOT NULL,
                                     activity_id integer,
                                     content text NOT NULL,
                                     datecreated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                     dateupdated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                     createdby_id integer,
                                     updatedby_id integer
);


--
-- Name: activitynote_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.activitynote_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: activitynote_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.activitynote_id_seq OWNED BY public.activitynote.id;


--
-- Name: activityorganization; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.activityorganization (
                                             id integer NOT NULL,
                                             organization_id integer,
                                             activity_id integer,
                                             main boolean,
                                             role character varying(255) DEFAULT NULL::character varying,
                                             datestart timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                             dateend timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                             status integer,
                                             datecreated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                             dateupdated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                             datedeleted timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                             roleobj_id integer,
                                             createdby_id integer,
                                             updatedby_id integer,
                                             deletedby_id integer
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
                                        datepredicted date,
                                        amount double precision NOT NULL,
                                        rate double precision,
                                        codetransaction character varying(255) DEFAULT NULL::character varying,
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
                                       datestart timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                       dateend timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                       status integer,
                                       datecreated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                       dateupdated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                       datedeleted timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                       roleobj_id integer,
                                       createdby_id integer,
                                       updatedby_id integer,
                                       deletedby_id integer
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
                                        organisation_id integer,
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
                                        deletedby_id integer
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
                                              activityrequest_id integer,
                                              description text,
                                              status integer,
                                              datecreated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                              dateupdated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                              datedeleted timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
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
                                     label character varying(255) DEFAULT NULL::character varying,
                                     description character varying(255) DEFAULT NULL::character varying,
                                     nature character varying(255) DEFAULT NULL::character varying,
                                     lft integer NOT NULL,
                                     rgt integer NOT NULL,
                                     centaureid character varying(255) DEFAULT NULL::character varying,
                                     status integer,
                                     datecreated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                     dateupdated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                     datedeleted timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                     createdby_id integer,
                                     updatedby_id integer,
                                     deletedby_id integer
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
                                               section_id integer,
                                               dateupdoad timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
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
                                         person_id integer,
                                         grant_id integer,
                                         process_id integer,
                                         dateupdoad timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                         path character varying(255) NOT NULL,
                                         information text,
                                         filetypemime character varying(255) DEFAULT NULL::character varying,
                                         filesize integer,
                                         filename character varying(255) DEFAULT NULL::character varying,
                                         version integer,
                                         status integer DEFAULT 1 NOT NULL,
                                         centaureid character varying(255) DEFAULT NULL::character varying,
                                         private boolean,
                                         signable boolean DEFAULT false NOT NULL,
                                         datedeposit date,
                                         datesend date,
                                         location character varying(255) DEFAULT 'local'::character varying NOT NULL,
                                         typedocument_id integer,
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
                                 label character varying(20) NOT NULL,
                                 symbol character varying(4) NOT NULL,
                                 rate double precision NOT NULL,
                                 status integer,
                                 datecreated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                 dateupdated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                 datedeleted timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                 createdby_id integer,
                                 updatedby_id integer,
                                 deletedby_id integer
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
                                 facet character varying(255) DEFAULT NULL::character varying,
                                 description character varying(255) DEFAULT NULL::character varying,
                                 recursivity character varying(255) DEFAULT NULL::character varying,
                                 finishable boolean DEFAULT false NOT NULL,
                                 status integer,
                                 datecreated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                 dateupdated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                 datedeleted timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                 createdby_id integer,
                                 updatedby_id integer,
                                 deletedby_id integer
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
                                   centaureid character varying(10) DEFAULT NULL::character varying,
                                   label character varying(128) NOT NULL
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
                                     parent_id integer,
                                     centaureid character varying(10) DEFAULT NULL::character varying,
                                     shortname character varying(128) DEFAULT NULL::character varying,
                                     fullname character varying(255) DEFAULT NULL::character varying,
                                     code character varying(255) DEFAULT NULL::character varying,
                                     email character varying(255) DEFAULT NULL::character varying,
                                     url character varying(255) DEFAULT NULL::character varying,
                                     description text,
                                     street1 character varying(255) DEFAULT NULL::character varying,
                                     street2 character varying(255) DEFAULT NULL::character varying,
                                     street3 character varying(255) DEFAULT NULL::character varying,
                                     city character varying(255) DEFAULT NULL::character varying,
                                     zipcode character varying(255) DEFAULT NULL::character varying,
                                     phone character varying(255) DEFAULT NULL::character varying,
                                     datestart timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                     dateend timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
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
                                     labintel character varying(255) DEFAULT NULL::character varying,
                                     rnsr character varying(255) DEFAULT NULL::character varying,
                                     connectors text,
                                     duns character varying(255) DEFAULT NULL::character varying,
                                     tvaintra character varying(255) DEFAULT NULL::character varying,
                                     status integer,
                                     datecreated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                     dateupdated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                     datedeleted timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                     typeobj_id integer,
                                     createdby_id integer,
                                     updatedby_id integer,
                                     deletedby_id integer
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
-- Name: organizationperson; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.organizationperson (
                                           id integer NOT NULL,
                                           person_id integer,
                                           organization_id integer,
                                           origin character varying(255) DEFAULT NULL::character varying,
                                           main boolean,
                                           role character varying(255) DEFAULT NULL::character varying,
                                           datestart timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                           dateend timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                           status integer,
                                           datecreated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                           dateupdated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                           datedeleted timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                           roleobj_id integer,
                                           createdby_id integer,
                                           updatedby_id integer,
                                           deletedby_id integer
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
                               connectors text,
                               email character varying(255) DEFAULT NULL::character varying,
                               emailprive character varying(255) DEFAULT NULL::character varying,
                               ldapstatus character varying(255) DEFAULT NULL::character varying,
                               ldapsitelocation character varying(255) DEFAULT NULL::character varying,
                               ldapaffectation character varying(255) DEFAULT NULL::character varying,
                               ldapdisabled boolean,
                               ldapfininscription character varying(255) DEFAULT NULL::character varying,
                               ladaplogin character varying(255) DEFAULT NULL::character varying,
                               phone character varying(255) DEFAULT NULL::character varying,
                               datesyncldap timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                               harpegeinm character varying(255) DEFAULT NULL::character varying,
                               ldapmemberof text,
                               schedulekey character varying(255) DEFAULT NULL::character varying,
                               customsettings text,
                               status integer,
                               datecreated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                               dateupdated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                               datedeleted timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                               createdby_id integer,
                               updatedby_id integer,
                               deletedby_id integer
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
                                  root_id integer,
                                  code character varying(150) NOT NULL,
                                  libelle character varying(200) NOT NULL,
                                  ordre integer,
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
                                acronym character varying(255) DEFAULT NULL::character varying,
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
-- Name: projectmember; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.projectmember (
                                      id integer NOT NULL,
                                      project_id integer,
                                      person_id integer,
                                      main boolean,
                                      role character varying(255) DEFAULT NULL::character varying,
                                      datestart timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                      dateend timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                      status integer,
                                      datecreated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                      dateupdated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                      datedeleted timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                      roleobj_id integer,
                                      createdby_id integer,
                                      updatedby_id integer,
                                      deletedby_id integer
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
                                       main boolean,
                                       role character varying(255) DEFAULT NULL::character varying,
                                       datestart timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                       dateend timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                       status integer,
                                       datecreated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                       dateupdated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                       datedeleted timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                       roleobj_id integer,
                                       createdby_id integer,
                                       updatedby_id integer,
                                       deletedby_id integer
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
                                  activity_id integer,
                                  person_id integer,
                                  datefrom timestamp(0) without time zone NOT NULL,
                                  dateto timestamp(0) without time zone NOT NULL,
                                  datesync timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                  syncid character varying(255) DEFAULT NULL::character varying,
                                  comment text,
                                  label text,
                                  icsuid text,
                                  icsfileuid text,
                                  icsfilename text,
                                  icsfiledateadded timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                  sendby character varying(255) DEFAULT NULL::character varying,
                                  status integer,
                                  datecreated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                  dateupdated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                  datedeleted timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                  validationperiod_id integer,
                                  createdby_id integer,
                                  updatedby_id integer,
                                  deletedby_id integer
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
                                     isdefault boolean DEFAULT false NOT NULL,
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
                                                  signatureflow_id integer,
                                                  label text
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
                                                       label text
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
                                                    context_short character varying(255) DEFAULT NULL::character varying,
                                                    context_long text,
                                                    refused_text text,
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
                                         validatorsprjdefault boolean DEFAULT true NOT NULL,
                                         validatorsscidefault boolean DEFAULT true NOT NULL,
                                         validatorsadmdefault boolean DEFAULT true NOT NULL,
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
                                    code character varying(255) NOT NULL,
                                    label character varying(255) NOT NULL,
                                    description text,
                                    datestart date,
                                    dateend date,
                                    status integer,
                                    datecreated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                    dateupdated timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                    datedeleted timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
                                    createdby_id integer,
                                    updatedby_id integer,
                                    deletedby_id integer
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
-- Name: activityavenant id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activityavenant ALTER COLUMN id SET DEFAULT nextval('public.activityavenant_id_seq'::regclass);


--
-- Name: activityavenantmodification id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activityavenantmodification ALTER COLUMN id SET DEFAULT nextval('public.activityavenantmodification_id_seq'::regclass);


--
-- Name: activitymotcle id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activitymotcle ALTER COLUMN id SET DEFAULT nextval('public.activitymotcle_id_seq'::regclass);


--
-- Name: activitynote id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activitynote ALTER COLUMN id SET DEFAULT nextval('public.activitynote_id_seq'::regclass);


--
-- Data for Name: activitytype; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.activitytype (id, label, description, nature, lft, rgt, centaureid, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id) FROM stdin;
3	CPER		0	2	3	\N	1	2025-06-27 09:57:17	\N	\N	\N	\N	\N
11	EUREKA		0	13	14	\N	1	2025-06-27 10:00:23	\N	\N	\N	\N	\N
12	INTERREG		0	15	16	\N	1	2025-06-27 10:01:10	\N	\N	\N	\N	\N
4	Subvention européenne		0	12	19	\N	1	2025-06-27 09:57:31	\N	\N	\N	\N	\N
15	Partenariat		0	4	11	\N	1	2025-06-27 10:02:44	\N	\N	\N	\N	\N
5	Subvention nationale		0	20	29	\N	1	2025-06-27 09:58:13	\N	\N	\N	\N	\N
2	ROOT		Recherche et valorisation	1	40	\N	1	2025-06-27 09:57:09	\N	\N	\N	\N	\N
10	Région Innovation 2021-2027		0	33	34	\N	1	2025-06-27 09:59:54	\N	\N	\N	\N	\N
9	Région Innovation 2014-2020		0	31	32	\N	1	2025-06-27 09:59:39	\N	\N	\N	\N	\N
7	Tremplin		0	35	36	\N	1	2025-06-27 09:58:59	\N	\N	\N	\N	\N
6	Subvention régionale		0	30	39	\N	1	2025-06-27 09:58:42	\N	\N	\N	\N	\N
8	Autre		0	37	38	\N	1	2025-06-27 09:59:17	\N	\N	\N	\N	\N
14	Accords cadre		0	5	6	\N	1	2025-06-27 10:02:21	\N	\N	\N	\N	\N
16	Accord de confidentialité		0	7	8	\N	1	2025-06-27 10:03:18	\N	\N	\N	\N	\N
1	ANR		0	21	22	\N	1	2025-06-27 09:57:09	\N	\N	\N	\N	\N
13	Horizon Europe		0	17	18	\N	1	2025-06-27 10:01:32	\N	\N	\N	\N	\N
17	Accord de consortium		0	9	10	\N	1	2025-06-27 10:03:37	\N	\N	\N	\N	\N
18	BPI		0	23	24	\N	1	2025-06-27 10:04:02	\N	\N	\N	\N	\N
19	PIA		0	25	26	\N	1	2025-06-27 10:04:10	\N	\N	\N	\N	\N
20	Autre		0	27	28	\N	1	2025-06-27 10:04:20	\N	\N	\N	\N	\N
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


COPY public.tabdocument (id,"label",description,isdefault) FROM stdin;
1	Général	\N	true
\.

COPY public.tabsdocumentsroles (id,role_id,"access",tabdocument_id) FROM stdin;
1	1	2	1
2	3	2	1
3	5	0	1
4	2	2	1
5	4	1	1
\.

COPY public.typedocument (id,"label",description,codecentaure,isdefault,status,datecreated,dateupdated,datedeleted,createdby_id,updatedby_id,deletedby_id) FROM stdin;
1	Document de travail	\N	\N	true	1	'2025-07-01 16:28:09'	\N	\N	\N	\N	\N
2	Annexe financière	 \N	\N	false	1	'2025-07-01 16:28:47'	\N	\N	\N	\N	\N
3	Contrat signé	\N	\N	false	1	'2025-07-01 16:28:59'	\N	\N	\N	\N	\N
\.

--	Data 	o	 Name: country3166; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.country3166 (id, fr, en, alpha2, alpha3, "numeric") FROM stdin;
1	Afghanistan	Afghanistan	AF	AFG	4
2	Albanie	Albania	AL	ALB	8
3	Algérie	Algeria	DZ	DZA	12
4	Samoa américaines	American Samoa	AS	ASM	16
5	Andorre	Andorra	AD	AND	20
6	Angola	Angola	AO	AGO	24
7	Anguilla	Anguilla	AI	AIA	660
8	Antarctique	Antarctica	AQ	ATA	10
9	Antigua-et-Barbuda	Antigua and Barbuda	AG	ATG	28
10	Argentine	Argentina	AR	ARG	32
11	Arménie	Armenia	AM	ARM	51
12	Aruba	Aruba	AW	ABW	533
13	Australie	Australia	AU	AUS	36
14	Autriche	Austria	AT	AUT	40
15	Azerbaïdjan	Azerbaijan	AZ	AZE	31
16	Bahamas	Bahamas (the)	BS	BHS	44
17	Bahreïn	Bahrain	BH	BHR	48
18	Bangladesh	Bangladesh	BD	BGD	50
19	Barbade	Barbados	BB	BRB	52
20	Bélarus	Belarus	BY	BLR	112
21	Belgique	Belgium	BE	BEL	56
22	Belize	Belize	BZ	BLZ	84
23	Bénin	Benin	BJ	BEN	204
24	Bermudes	Bermuda	BM	BMU	60
25	Bhoutan	Bhutan	BT	BTN	64
26	Bolivie (État plurinational de)	Bolivia (Plurinational State of)	BO	BOL	68
27	Bonaire, Saint-Eustache et Saba	Bonaire, Sint Eustatius and Saba	BQ	BES	535
28	Bosnie-Herzégovine	Bosnia and Herzegovina	BA	BIH	70
29	Botswana	Botswana	BW	BWA	72
30	Bouvet (l'Île)	Bouvet Island	BV	BVT	74
31	Brésil	Brazil	BR	BRA	76
32	Indien (le Territoire britannique de l'océan)	British Indian Ocean Territory (the)	IO	IOT	86
33	Brunéi Darussalam	Brunei Darussalam	BN	BRN	96
34	Bulgarie	Bulgaria	BG	BGR	100
35	Burkina Faso	Burkina Faso	BF	BFA	854
36	Burundi	Burundi	BI	BDI	108
37	Cabo Verde	Cabo Verde	CV	CPV	132
38	Cambodge	Cambodia	KH	KHM	116
39	Cameroun	Cameroon	CM	CMR	120
40	Canada	Canada	CA	CAN	124
41	Caïmans (les Îles)	Cayman Islands (the)	KY	CYM	136
42	République centrafricaine	Central African Republic (the)	CF	CAF	140
43	Tchad	Chad	TD	TCD	148
44	Chili	Chile	CL	CHL	152
45	Chine	China	CN	CHN	156
46	Christmas (l'Île)	Christmas Island	CX	CXR	162
47	Cocos (les Îles)/ Keeling (les Îles)	Cocos (Keeling) Islands (the)	CC	CCK	166
48	Colombie	Colombia	CO	COL	170
49	Comores	Comoros (the)	KM	COM	174
50	Congo (la République démocratique du)	Congo (the Democratic Republic of the)	CD	COD	180
51	Congo	Congo (the)	CG	COG	178
52	Cook (les Îles)	Cook Islands (the)	CK	COK	184
53	Costa Rica	Costa Rica	CR	CRI	188
54	Croatie	Croatia	HR	HRV	191
55	Cuba	Cuba	CU	CUB	192
56	Curaçao	Curaçao	CW	CUW	531
57	Chypre	Cyprus	CY	CYP	196
58	Tchéquie	Czechia	CZ	CZE	203
59	Côte d'Ivoire	Côte d'Ivoire	CI	CIV	384
60	Danemark	Denmark	DK	DNK	208
61	Djibouti	Djibouti	DJ	DJI	262
62	Dominique	Dominica	DM	DMA	212
63	dominicaine (la République)	Dominican Republic (the)	DO	DOM	214
64	Équateur	Ecuador	EC	ECU	218
65	Égypte	Egypt	EG	EGY	818
66	El Salvador	El Salvador	SV	SLV	222
67	Guinée équatoriale	Equatorial Guinea	GQ	GNQ	226
68	Érythrée	Eritrea	ER	ERI	232
69	Estonie	Estonia	EE	EST	233
70	Eswatini	Eswatini	SZ	SWZ	748
71	Éthiopie	Ethiopia	ET	ETH	231
72	Falkland (les Îles)/Malouines (les Îles)	Falkland Islands (the) [Malvinas]	FK	FLK	238
73	Féroé (les Îles)	Faroe Islands (the)	FO	FRO	234
74	Fidji	Fiji	FJ	FJI	242
75	Finlande	Finland	FI	FIN	246
76	France	France	FR	FRA	250
77	Guyane française (la )	French Guiana	GF	GUF	254
78	Polynésie française	French Polynesia	PF	PYF	258
79	Terres australes françaises	French Southern Territories (the)	TF	ATF	260
80	Gabon	Gabon	GA	GAB	266
81	Gambie	Gambia (the)	GM	GMB	270
82	Géorgie	Georgia	GE	GEO	268
83	Allemagne	Germany	DE	DEU	276
84	Ghana	Ghana	GH	GHA	288
85	Gibraltar	Gibraltar	GI	GIB	292
86	Grèce	Greece	GR	GRC	300
87	Groenland	Greenland	GL	GRL	304
88	Grenade	Grenada	GD	GRD	308
89	Guadeloupe	Guadeloupe	GP	GLP	312
90	Guam	Guam	GU	GUM	316
91	Guatemala	Guatemala	GT	GTM	320
92	Guernesey	Guernsey	GG	GGY	831
93	Guinée	Guinea	GN	GIN	324
94	Guinée-Bissau	Guinea-Bissau	GW	GNB	624
95	Guyana	Guyana	GY	GUY	328
96	Haïti	Haiti	HT	HTI	332
97	Heard-et-Îles MacDonald (l'Île)	Heard Island and McDonald Islands	HM	HMD	334
98	Saint-Siège	Holy See (the)	VA	VAT	336
99	Honduras	Honduras	HN	HND	340
100	Hong Kong	Hong Kong	HK	HKG	344
101	Hongrie	Hungary	HU	HUN	348
102	Islande	Iceland	IS	ISL	352
103	Inde	India	IN	IND	356
104	Indonésie	Indonesia	ID	IDN	360
105	Iran (République Islamique d')	Iran (Islamic Republic of)	IR	IRN	364
106	Iraq	Iraq	IQ	IRQ	368
107	Irlande	Ireland	IE	IRL	372
108	Île de Man	Isle of Man	IM	IMN	833
109	Israël	Israel	IL	ISR	376
110	Italie	Italy	IT	ITA	380
111	Jamaïque	Jamaica	JM	JAM	388
112	Japon	Japan	JP	JPN	392
113	Jersey	Jersey	JE	JEY	832
114	Jordanie	Jordan	JO	JOR	400
115	Kazakhstan	Kazakhstan	KZ	KAZ	398
116	Kenya	Kenya	KE	KEN	404
117	Kiribati	Kiribati	KI	KIR	296
118	Corée (la République populaire démocratique de)	Korea (the Democratic People's Republic of)	KP	PRK	408
119	Corée (la République de)	Korea (the Republic of)	KR	KOR	410
120	Koweït	Kuwait	KW	KWT	414
121	Kirghizistan	Kyrgyzstan	KG	KGZ	417
122	Lao (la République démocratique populaire)	Lao People's Democratic Republic (the)	LA	LAO	418
123	Lettonie	Latvia	LV	LVA	428
124	Liban	Lebanon	LB	LBN	422
125	Lesotho	Lesotho	LS	LSO	426
126	Libéria	Liberia	LR	LBR	430
127	Libye	Libya	LY	LBY	434
128	Liechtenstein	Liechtenstein	LI	LIE	438
129	Lituanie	Lithuania	LT	LTU	440
130	Luxembourg	Luxembourg	LU	LUX	442
131	Macao	Macao	MO	MAC	446
132	Madagascar	Madagascar	MG	MDG	450
133	Malawi	Malawi	MW	MWI	454
134	Malaisie	Malaysia	MY	MYS	458
135	Maldives	Maldives	MV	MDV	462
136	Mali	Mali	ML	MLI	466
137	Malte	Malta	MT	MLT	470
138	Marshall (les Îles)	Marshall Islands (the)	MH	MHL	584
139	Martinique	Martinique	MQ	MTQ	474
140	Mauritanie	Mauritania	MR	MRT	478
141	Maurice	Mauritius	MU	MUS	480
142	Mayotte	Mayotte	YT	MYT	175
143	Mexique	Mexico	MX	MEX	484
144	Micronésie (États fédérés de)	Micronesia (Federated States of)	FM	FSM	583
145	Moldova (la République de)	Moldova (the Republic of)	MD	MDA	498
146	Monaco	Monaco	MC	MCO	492
147	Mongolie	Mongolia	MN	MNG	496
148	Monténégro	Montenegro	ME	MNE	499
149	Montserrat	Montserrat	MS	MSR	500
150	Maroc	Morocco	MA	MAR	504
151	Mozambique	Mozambique	MZ	MOZ	508
152	Myanmar	Myanmar	MM	MMR	104
153	Namibie	Namibia	NA	NAM	516
154	Nauru	Nauru	NR	NRU	520
155	Népal	Nepal	NP	NPL	524
156	Pays-Bas	Netherlands (the)	NL	NLD	528
157	Nouvelle-Calédonie	New Caledonia	NC	NCL	540
158	Nouvelle-Zélande	New Zealand	NZ	NZL	554
159	Nicaragua	Nicaragua	NI	NIC	558
160	Niger	Niger (the)	NE	NER	562
161	Nigéria	Nigeria	NG	NGA	566
162	Niue	Niue	NU	NIU	570
163	Norfolk (l'Île)	Norfolk Island	NF	NFK	574
164	Macédoine du Nord	North Macedonia	MK	MKD	807
165	Mariannes du Nord (les Îles)	Northern Mariana Islands (the)	MP	MNP	580
166	Norvège	Norway	NO	NOR	578
167	Oman	Oman	OM	OMN	512
168	Pakistan	Pakistan	PK	PAK	586
169	Palaos	Palau	PW	PLW	585
170	Palestine, État de	Palestine, State of	PS	PSE	275
171	Panama	Panama	PA	PAN	591
172	Papouasie-Nouvelle-Guinée	Papua New Guinea	PG	PNG	598
173	Paraguay	Paraguay	PY	PRY	600
174	Pérou	Peru	PE	PER	604
175	Philippines	Philippines (the)	PH	PHL	608
176	Pitcairn	Pitcairn	PN	PCN	612
177	Pologne	Poland	PL	POL	616
178	Portugal	Portugal	PT	PRT	620
179	Porto Rico	Puerto Rico	PR	PRI	630
180	Qatar	Qatar	QA	QAT	634
181	Roumanie	Romania	RO	ROU	642
182	Russie (la Fédération de)	Russian Federation (the)	RU	RUS	643
183	Rwanda	Rwanda	RW	RWA	646
184	Réunion	Réunion	RE	REU	638
185	Saint-Barthélemy	Saint Barthélemy	BL	BLM	652
186	Sainte-Hélène, Ascension et Tristan da Cunha	Saint Helena, Ascension and Tristan da Cunha	SH	SHN	654
187	Saint-Kitts-et-Nevis	Saint Kitts and Nevis	KN	KNA	659
188	Sainte-Lucie	Saint Lucia	LC	LCA	662
189	Saint-Martin (partie française)	Saint Martin (French part)	MF	MAF	663
190	Saint-Pierre-et-Miquelon	Saint Pierre and Miquelon	PM	SPM	666
191	Saint-Vincent-et-les Grenadines	Saint Vincent and the Grenadines	VC	VCT	670
192	Samoa	Samoa	WS	WSM	882
193	Saint-Marin	San Marino	SM	SMR	674
194	Sao Tomé-et-Principe	Sao Tome and Principe	ST	STP	678
195	Arabie saoudite	Saudi Arabia	SA	SAU	682
196	Sénégal	Senegal	SN	SEN	686
197	Serbie	Serbia	RS	SRB	688
198	Seychelles	Seychelles	SC	SYC	690
199	Sierra Leone	Sierra Leone	SL	SLE	694
200	Singapour	Singapore	SG	SGP	702
201	Saint-Martin (partie néerlandaise)	Sint Maarten (Dutch part)	SX	SXM	534
202	Slovaquie	Slovakia	SK	SVK	703
203	Slovénie	Slovenia	SI	SVN	705
204	Salomon (les Îles)	Solomon Islands	SB	SLB	90
205	Somalie	Somalia	SO	SOM	706
206	Afrique du Sud	South Africa	ZA	ZAF	710
207	Géorgie du Sud-et-les Îles Sandwich du Sud	South Georgia and the South Sandwich Islands	GS	SGS	239
208	Soudan du Sud	South Sudan	SS	SSD	728
209	Espagne	Spain	ES	ESP	724
210	Sri Lanka	Sri Lanka	LK	LKA	144
211	Soudan	Sudan (the)	SD	SDN	729
212	Suriname	Suriname	SR	SUR	740
213	Svalbard et l'Île Jan Mayen	Svalbard and Jan Mayen	SJ	SJM	744
214	Suède	Sweden	SE	SWE	752
215	Suisse	Switzerland	CH	CHE	756
216	République arabe syrienne	Syrian Arab Republic (the)	SY	SYR	760
217	Taïwan (Province de Chine)	Taiwan (Province of China)	TW	TWN	158
218	Tadjikistan	Tajikistan	TJ	TJK	762
219	Tanzanie (la République-Unie de)	Tanzania, the United Republic of	TZ	TZA	834
220	Thaïlande	Thailand	TH	THA	764
221	Timor-Leste	Timor-Leste	TL	TLS	626
222	Togo	Togo	TG	TGO	768
223	Tokelau	Tokelau	TK	TKL	772
224	Tonga	Tonga	TO	TON	776
225	Trinité-et-Tobago	Trinidad and Tobago	TT	TTO	780
226	Tunisie	Tunisia	TN	TUN	788
227	Turquie	Turkey	TR	TUR	792
228	Turkménistan	Turkmenistan	TM	TKM	795
229	Turks-et-Caïcos (les Îles)	Turks and Caicos Islands (the)	TC	TCA	796
230	Tuvalu	Tuvalu	TV	TUV	798
231	Ouganda	Uganda	UG	UGA	800
232	Ukraine	Ukraine	UA	UKR	804
233	Émirats arabes unis	United Arab Emirates (the)	AE	ARE	784
234	Royaume-Uni de Grande-Bretagne et d'Irlande du Nord	United Kingdom of Great Britain and Northern Ireland (the)	GB	GBR	826
235	Îles mineures éloignées des États-Unis	United States Minor Outlying Islands (the)	UM	UMI	581
236	États-Unis d'Amérique	United States of America (the)	US	USA	840
237	Uruguay	Uruguay	UY	URY	858
238	Ouzbékistan	Uzbekistan	UZ	UZB	860
239	Vanuatu	Vanuatu	VU	VUT	548
240	Venezuela (République bolivarienne du)	Venezuela (Bolivarian Republic of)	VE	VEN	862
241	Viet Nam	Viet Nam	VN	VNM	704
242	Vierges britanniques (les Îles)	Virgin Islands (British)	VG	VGB	92
243	Vierges des États-Unis (les Îles)	Virgin Islands (U.S.)	VI	VIR	850
244	Wallis-et-Futuna	Wallis and Futuna	WF	WLF	876
245	Sahara occidental	Western Sahara*	EH	ESH	732
246	Yémen	Yemen	YE	YEM	887
247	Zambie	Zambia	ZM	ZMB	894
248	Zimbabwe	Zimbabwe	ZW	ZWE	716
249	Åland(les Îles)	Åland Islands	AX	ALA	248
\.


--
-- Data for Name: currency; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.currency (id, label, symbol, rate, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id) FROM stdin;
1	Euro	€	1	1	2015-11-03 14:48:10	\N	\N	\N	\N	\N
4	Yens	¥	132.651	1	2015-11-03 14:58:31	\N	\N	\N	\N	\N
3	Livre	£	0.7133	1	2015-11-03 14:57:20	\N	\N	\N	\N	\N
2	Dollars	$	1.096	1	2015-11-03 14:56:38	\N	\N	\N	\N	\N
\.


--
-- Data for Name: datetype; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.datetype (id, label, facet, description, recursivity, finishable, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id) FROM stdin;
1	Rapport financier	Financier		30,15,7,3	t	1	\N	\N	\N	\N	\N	\N
2	Rapport scientifique	Scientifique		30,15,7	t	1	\N	\N	\N	\N	\N	\N
3	Début des dépenses	Financier			f	1	\N	\N	\N	\N	\N	\N
4	Fin des dépenses	Financier		60,30,15	f	1	\N	\N	\N	\N	\N	\N
5	Soumission du contrat	Général			t	1	\N	\N	\N	\N	\N	\N
\.


--
-- Data for Name: organizationrole; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.organizationrole (id, label, description, principal, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id) FROM stdin;
1	Laboratoire		t	1	2025-06-27 10:53:47	\N	\N	\N	\N	\N
2	Composante		t	1	2025-06-27 10:54:00	\N	\N	\N	\N	\N
3	Partenaire tiers		f	1	2025-06-27 10:54:28	\N	\N	\N	\N	\N
\.


--
-- Data for Name: organizationtype; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.organizationtype (id, root_id, label, description, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id) FROM stdin;
1	\N	Collectivité territoriale	\N	1	\N	\N	\N	\N	\N	\N
2	\N	Groupement d'intérêt économique	\N	1	\N	\N	\N	\N	\N	\N
3	\N	Institution	\N	1	\N	\N	\N	\N	\N	\N
4	\N	Laboratoire	\N	1	\N	\N	\N	\N	\N	\N
5	\N	Société	\N	1	\N	\N	\N	\N	\N	\N
6	\N	Établissement publique	\N	1	\N	\N	\N	\N	\N	\N
7	\N	Fondation		1	\N	\N	\N	\N	\N	\N
8	\N	Association		1	\N	\N	\N	\N	\N	\N
9	\N	Groupement d'intérêt public		1	\N	\N	\N	\N	\N	\N
10	\N	Structure de recherche		1	\N	\N	\N	\N	\N	\N
\.



--
-- Data for Name: privilege; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.privilege (id, categorie_id, root_id, code, libelle, ordre, spot) FROM stdin;
6	7	\N	privilege-visualisation	Privilèges - Visualisation	\N	7
18	2	\N	SHOW	Afficher la fiche d'une activité	\N	7
20	2	\N	PAYMENT_SHOW	Voir les versements et le budget	\N	7
22	2	\N	MILESTONE_SHOW	Peut voir les jalons	\N	7
24	2	\N	DOCUMENT_SHOW	Peut voir les documents	\N	7
30	2	\N	PERSON_SHOW	Peut voir les membres d'une activité	\N	7
31	2	\N	ORGANIZATION_SHOW	Peut voir les partenaires d'un projet	\N	7
3	1	\N	SHOW	Voir les détails d'un projet	\N	7
32	1	\N	PERSON_SHOW	Voir les membres d'un projet	\N	7
33	1	\N	ORGANIZATION_SHOW	Voir les partenaires d'un projet	\N	7
34	1	\N	DOCUMENT_SHOW	Voir les documents d'un projet	\N	7
35	1	\N	ACTIVITY_SHOW	Voir les activités d'un projet	\N	7
54	2	\N	WORKPACKAGE_SHOW	Voir les lots de travail d'une activité	\N	7
56	2	\N	WORKPACKAGE_COMMIT	Déclarer des heures pour un lot de travail	\N	7
62	9	\N	SHOW	Voir les dépenses	\N	7
70	6	\N	CONNECTOR_ACCESS	Peut exécuter la synchronisation des données	\N	7
69	2	\N	TIMESHEET_USURPATION	Peut remplir les feuilles de temps des déclarants d'une activité	\N	7
72	2	\N	NOTIFICATIONS_SHOW	Peut voir les notifications planifiées dans la fiche activité	\N	7
75	2	\N	PERSON_ACCESS	Voir les personnes qui ont la vision sur l'activité	\N	7
76	3	\N	VIEW_TIMESHEET	Peut voir les feuilles de temps de n'importe quelle personne	\N	7
78	2	\N	TIMESHEET_VIEW	Voir les feuilles de temps	\N	7
79	6	\N	ACTIVITYTYPE_MANAGE	Configurer les types d'activités disponibles	\N	7
80	6	\N	MILESTONETYPE_MANAGE	Configurer les types de jalons disponibles	\N	7
81	6	\N	ORGANIZATIONTYPE_MANAGE	Configurer les types d'organisation disponibles	\N	7
82	6	\N	SEARCH_BUILD	Peut lancer la reconstruction de l'index de recherche des activités	\N	7
4	7	\N	role-visualisation	Visualisation des rôles	\N	4
5	7	4	role-edition	Édition des rôles	\N	4
7	7	6	privilege-edition	Privilèges - Édition	\N	4
8	1	\N	CREATE	Création d'un nouveau projet	\N	4
9	1	3	EDIT	Modifier un projet	\N	4
10	1	\N	ACTIVITY-ADD	Ajouter une activité dans le projet	\N	4
11	1	32	PERSON_MANAGE	Gérer les membres d'un projet	\N	7
12	1	33	ORGANIZATION_MANAGE	Gérer les partenaires d'un projet	\N	7
1	1	\N	DASHBOARD	Tableau de bord	\N	4
13	2	17	EXPORT	Exporter les données des activités	\N	4
15	2	31	ORGANIZATION_MANAGE	Gestion des partenaires d'une activité	\N	7
19	2	18	EDIT	Modifier les informations générales d'une activité	\N	7
17	2	\N	INDEX	Afficher / rechercher dans les activités	\N	4
23	2	22	MILESTONE_MANAGE	Peut gérer les jalons	\N	7
25	2	24	DOCUMENT_MANAGE	Peut gérer les documents (Ajouter)	\N	7
27	2	\N	CHANGE_PROJECT	Peut modifier le projet d'une activité	\N	4
28	2	\N	DELETE	Peut supprimer définitivement une activité	\N	4
29	2	\N	STATUS_OFF	Peut modifier le statut vers "Désactivé"	\N	4
36	3	\N	SHOW	Voir la fiche d'une personne	\N	4
37	3	36	EDIT	Modifier la fiche d'une personne	\N	4
41	4	\N	SHOW	Voir la fiche d'une organisation	\N	4
42	4	41	EDIT	Modifier la fiche d'une organisation	\N	4
14	2	30	PERSON_MANAGE	Gestion des membres d'une activité	\N	7
51	6	\N	MENU_ADMIN	Accès au menu d'administration	\N	4
40	4	\N	INDEX	Voir la liste des organisations	\N	4
39	3	\N	INDEX	Voir la liste des personnes	\N	4
53	3	36	PROJECTS	Voir les projets d'une personnes	\N	7
52	3	36	INFOS_RH	Voir les données administratives	\N	4
55	2	54	WORKPACKAGE_MANAGE	Gérer les lots de travail d'une activité	\N	7
58	8	\N	DOCUMENT_INDEX	Voir les documents adminstratifs	\N	4
60	8	58	DOCUMENT_DELETE	Supprimer un document	\N	4
61	8	58	DOCUMENT_DOWNLOAD	Télécharger un document	\N	4
59	8	58	DOCUMENT_NEW	Téléverser un nouveau document	\N	4
63	7	\N	USER_VISUALISATION	Voir les authentifications utilisateur	\N	4
64	7	\N	USER_EDITION	Gérer les authentifications des utilisateurs	\N	4
65	7	\N	ROLEORGA_VISUALISATION	Voir les rôles des organisations	\N	4
66	7	65	ROLEORGA_EDITION	Configurer les rôles des organisations	\N	4
73	2	72	NOTIFICATIONS_GENERATE	Peut regénérer manuellement les notifications d'une activité	\N	7
67	2	78	TIMESHEET_VALIDATE_SCI	Validation scientifique des feuilles de temps	\N	7
68	2	78	TIMESHEET_VALIDATE_ADM	Validation administrative des feuilles de temps	\N	7
77	2	22	MILESTONE_PROGRESSION	Peut gérer l'état d'avancement des jalons	\N	7
83	6	\N	DISCIPLINE_MANAGE	Configurer les disciplines disponibles pour les activités	\N	7
38	3	36	SYNC_LDAP	Synchroniser les données depuis les connecteurs	\N	4
43	4	41	SYNC_LDAP	Synchroniser les données avec les connecteurs	\N	4
84	3	36	MANAGE_SCHEDULE	Peut  modifier et valider la répartition horaire d'une personne	\N	7
85	3	36	SHOW_SCHEDULE	Peut  voir la répartition horaire d'une personne	\N	7
86	4	40	DELETE	Autorise la suppression définitive d'une organisation	\N	4
87	2	78	TIMESHEET_VALIDATE_ACTIVITY	Validation niveau activité des feuilles de temps	\N	7
89	2	\N	REQUEST	Faire une demande d'activité	\N	4
90	2	\N	REQUEST_MANAGE	Traiter les demandes d'activité	\N	4
91	2	\N	REQUEST_ADMIN	Administrer toutes les demandes d'activité	\N	4
92	3	\N	FEED_TIMESHEET	Peut compléter les feuilles de temps de n'importe quel déclarant	\N	7
93	6	\N	DOCUMENTTYPE_MANAGE	Configurer les types de document disponibles	\N	7
2	1	\N	INDEX	Lister et rechercher dans les projets	\N	6
16	2	20	PAYMENT_MANAGE	Gestion des versements d'une activité	\N	7
26	2	\N	DUPLICATE	Peut dupliquer l'activité	\N	4
94	6	\N	TVA_MANAGE	Configurer les TVAs disponibles	\N	7
95	6	\N	NUMEROTATION_MANAGE	Configurer les numérotations disponibles pour les activités	\N	7
74	6	\N	NOTIFICATION_PERSON	Peut notifier manuellement une personne	\N	7
96	6	\N	DOCPUBSEC_MANAGE	Configurer les sections des documents publiques	\N	7
97	7	\N	API_ACCESS	Gérer les accès à l'API	\N	4
98	2	\N	CREATE	Créer une nouvelle activité de recherche	\N	4
99	2	\N	ESTIMATEDSPENT_SHOW	Voir les dépenses prévisionnelles	\N	7
100	2	99	ESTIMATEDSPENT_MANAGE	Gestion des dépenses prévisionnelle de l'activité	\N	7
101	9	\N	SYNC	Peut forcer la synchronisation des dépenses	\N	7
102	9	\N	DOWNLOAD	Peut télécharger les dépenses (Excel/CSV)	\N	7
103	6	\N	SPENDTYPEGROUP_MANAGE	Configuration des types de dépenses	\N	7
88	6	\N	VALIDATION_MANAGE	Peut gérer, modifier ou supprimer l'état des déclarations envoyées	\N	7
104	6	\N	PARAMETERS_MANAGE	Peut gérer les paramètres	\N	7
105	9	\N	RECETTES	Peut voir les recettes	\N	7
106	9	\N	IGNORED	Peut voir les données ignorées	\N	7
107	9	\N	DETAILS	Voir le détail des dépenses	\N	7
108	2	18	PCRU	Permet d'afficher les informations PCRU de l'activité de recherche	\N	7
109	2	18	PCRU_ACTIVATE	Permet d'activer les données PCRU pour une activité	\N	7
110	6	\N	PCRU_LIST	Peut visualiser la liste des données PCRU	\N	7
111	6	\N	PCRU_UPLOAD	Peut déclencher manuellement le transfert des donnèes vers PCRU	\N	7
112	2	18	CONTRACT_SHOW	Voir le contrat signé	\N	7
113	2	18	CONTRACT_SEND	Soumettre un contrat signé	\N	7
114	10	\N	SIGNATURE_INDEX	Liste des signatures	\N	7
115	10	\N	SIGNATURE_DELETE	Suppression des signatures	\N	7
116	10	\N	SIGNATURE_CREATE	Création de signature	\N	7
117	10	\N	SIGNATURE_SYNC	Synchronisation de signature	\N	7
118	10	\N	SIGNATURE_ADMIN	Accès à l'interface d'administration / gestion des signatures et processus en cours	\N	7
119	10	\N	SIGNATURE_ADMIN_CONFIG	Configuration des processus métier	\N	7
120	2	18	EDIT_LOCKED	Modifier les informations générales d'une activité VEROUILLEE	\N	7
121	2	24	DOCUMENT_DELETEDSIGNED	Peut supprimer un document signé	\N	7
122	6	\N	SIGNATURE_DELETE	Peut supprimer les documents signés	\N	7
123	2	\N	AVENANTS_SHOW	Voir les avenants	\N	7
124	2	123	AVENANTS_MANAGE	Créer/Modifier/Supprimer/Appliquer un avenant	\N	7
125	2	\N	NOTES_SHOW	Voir les notes	\N	7
126	2	125	NOTES_MANAGE_USER	Créer/Modifier/Supprimer mes propres notes	\N	7
127	2	125	NOTES_MANAGE_ADMIN	Créer/Modifier/Supprimer toutes les notes	\N	7
\.


--
-- Data for Name: role_privilege; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.role_privilege (privilege_id, role_id) FROM stdin;
6	1
18	1
20	1
22	1
24	1
30	1
31	1
3	1
32	1
33	1
34	1
35	1
54	1
56	1
62	1
70	1
69	1
72	1
75	1
76	1
78	1
79	1
80	1
81	1
82	1
4	1
5	1
7	1
8	1
9	1
10	1
11	1
12	1
1	1
13	1
15	1
19	1
17	1
23	1
25	1
27	1
28	1
29	1
36	1
37	1
41	1
42	1
14	1
51	1
40	1
39	1
53	1
52	1
55	1
58	1
60	1
61	1
59	1
63	1
64	1
65	1
66	1
73	1
67	1
68	1
77	1
83	1
38	1
43	1
84	1
85	1
86	1
87	1
89	1
90	1
91	1
92	1
93	1
2	1
16	1
26	1
94	1
95	1
74	1
96	1
97	1
98	1
99	1
100	1
101	1
102	1
103	1
88	1
104	1
105	1
106	1
107	1
108	1
109	1
110	1
111	1
112	1
113	1
114	1
115	1
116	1
117	1
118	1
119	1
120	1
121	1
122	1
123	1
124	1
125	1
126	1
127	1
3	3
32	3
33	3
34	3
35	3
18	3
20	3
22	3
24	3
30	3
31	3
54	3
17	3
123	3
125	3
126	3
62	3
102	3
105	3
106	3
107	3
3	4
9	4
32	4
11	4
33	4
12	4
34	4
35	4
18	4
19	4
20	4
16	4
22	4
77	4
77	3
23	4
24	4
25	4
25	3
30	4
14	4
31	4
15	4
54	4
78	4
78	3
17	4
13	4
27	2
13	2
17	2
78	2
72	2
54	2
15	2
31	2
14	2
30	2
25	2
24	2
23	2
77	2
22	2
20	2
16	2
18	2
19	2
3	2
3	5
9	2
32	2
32	5
33	2
34	5
34	2
35	2
35	5
8	2
10	2
1	2
2	2
18	5
24	5
30	5
31	5
54	5
76	2
76	4
36	4
36	2
52	2
53	2
41	2
41	4
40	2
40	4
79	2
80	2
81	2
94	2
93	2
83	2
82	2
51	2
95	2
74	2
96	2
103	2
88	2
104	2
110	2
111	2
6	2
4	2
63	2
65	2
58	2
59	2
61	2
60	2
58	4
58	3
62	4
62	2
101	2
101	4
102	4
102	2
105	4
106	4
107	4
105	2
106	2
107	2
114	2
116	2
117	2
118	2
26	2
98	2
39	2
39	4
123	2
125	2
125	4
123	4
98	4
42	2
127	4
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
\.


--
-- Data for Name: tabsdocumentsroles; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.tabsdocumentsroles (id, role_id, access, tabdocument_id) FROM stdin;
\.


--
-- Data for Name: timesheet; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.timesheet (id, workpackage_id, activity_id, person_id, datefrom, dateto, datesync, syncid, comment, label, icsuid, icsfileuid, icsfilename, icsfiledateadded, sendby, status, datecreated, dateupdated, datedeleted, validationperiod_id, createdby_id, updatedby_id, deletedby_id) FROM stdin;
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
7	Taux réduit 10%	10	t	1	\N	\N	\N	\N	\N	\N
\.


--
-- Data for Name: typedocument; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.typedocument (id, label, description, codecentaure, isdefault, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id) FROM stdin;
\.


--
-- Data for Name: unicaen_signature_observer; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.unicaen_signature_observer (id, signature_id, firstname, lastname, email) FROM stdin;
\.


--
-- Data for Name: unicaen_signature_process; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.unicaen_signature_process (id, datecreated, lastupdate, status, currentstep, document_name, signatureflow_id, label) FROM stdin;
\.


--
-- Data for Name: unicaen_signature_process_step; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.unicaen_signature_process_step (id, process_id, signature_id, label) FROM stdin;
\.


--
-- Data for Name: unicaen_signature_recipient; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.unicaen_signature_recipient (id, signature_id, status, firstname, lastname, email, phone, dateupdate, datefinished, keyaccess, informations, urldocument) FROM stdin;
\.


--
-- Data for Name: unicaen_signature_signature; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.unicaen_signature_signature (id, datecreated, type, status, ordering, label, description, datesend, dateupdate, document_path, document_remotekey, document_localkey, context_short, context_long, refused_text, letterfile_key, letterfile_process, letterfile_url, allsigntocomplete, notificationsrecipients) FROM stdin;
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
1	\N	Administrateur	f	\N	7	\N	f	t	t
3	\N	Responsable	f	\N	1	Responsable sur un contrat	t	f	t
5	\N	Déclarant	f	\N	1	Personne identifiée sur un contrat	f	f	t
2	\N	Chargé de projet	f	\N	15	Personne responsable du suivi des contrats	t	f	t
4	\N	Gestionnaire	f	\N	2	Personne gestionnaire dans un laboratoire ou une composante	t	f	t
\.


--
-- Data for Name: useraccessdefinition; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.useraccessdefinition (id, context, label, description, key) FROM stdin;
\.


--
-- Data for Name: validationperiod; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.validationperiod (id, declarer_id, validatorsprjdefault, validatorsscidefault, validatorsadmdefault, object, objectgroup, object_id, month, year, datesend, log, validationactivityat, validationactivityby, validationactivitybyid, validationactivitymessage, validationsciat, validationsciby, validationscibyid, validationscimessage, validationadmat, validationadmby, validationadmbyid, validationadmmessage, rejectactivityat, rejectactivityby, rejectactivitybyid, rejectactivitymessage, rejectsciat, rejectsciby, rejectscibyid, rejectscimessage, rejectadmat, rejectadmby, rejectadmbyid, rejectadmmessage, schedule, status, comment) FROM stdin;
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

COPY public.workpackage (id, activity_id, code, label, description, datestart, dateend, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id) FROM stdin;
\.


--
-- Data for Name: workpackageperson; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.workpackageperson (id, person_id, duration, status, datecreated, dateupdated, datedeleted, workpackage_id, createdby_id, updatedby_id, deletedby_id) FROM stdin;
\.

--
-- Name: activity_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.activity_id_seq', 3, true);


--
-- Name: activityavenant_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.activityavenant_id_seq', 1, false);


--
-- Name: activityavenantmodification_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.activityavenantmodification_id_seq', 1, false);


--
-- Name: activitydate_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.activitydate_id_seq', 1, false);


--
-- Name: activitymotcle_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.activitymotcle_id_seq', 2, true);


--
-- Name: activitynote_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.activitynote_id_seq', 1, true);


--
-- Name: activityorganization_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.activityorganization_id_seq', 3, true);


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

SELECT pg_catalog.setval('public.activityperson_id_seq', 4, true);


--
-- Name: activityrequest_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.activityrequest_id_seq', 1, false);


--
-- Name: activityrequestfollow_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.activityrequestfollow_id_seq', 1, false);


--
-- Name: activitytype_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.activitytype_id_seq', 1, false);


--
-- Name: administrativedocument_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.administrativedocument_id_seq', 1, false);


--
-- Name: administrativedocumentsection_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.administrativedocumentsection_id_seq', 1, false);


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

SELECT pg_catalog.setval('public.contracttype_id_seq', 1, false);


--
-- Name: country3166_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.country3166_id_seq', 249, true);


--
-- Name: currency_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.currency_id_seq', 1, false);


--
-- Name: datetype_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.datetype_id_seq', 1, false);


--
-- Name: discipline_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.discipline_id_seq', 1, false);


--
-- Name: estimatedspentline_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.estimatedspentline_id_seq', 1, false);


--
-- Name: logactivity_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.logactivity_id_seq', 21, true);


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

SELECT pg_catalog.setval('public.organization_id_seq', 5, true);


--
-- Name: organizationperson_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.organizationperson_id_seq', 3, true);


--
-- Name: organizationrole_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.organizationrole_id_seq', 1, false);


--
-- Name: organizationtype_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.organizationtype_id_seq', 1, false);


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

SELECT pg_catalog.setval('public.person_id_seq', 9, true);


--
-- Name: privilege_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.privilege_id_seq', 1, false);


--
-- Name: project_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.project_id_seq', 2, true);


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

SELECT pg_catalog.setval('public.referent_id_seq', 1, false);


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

SELECT pg_catalog.setval('public.tabdocument_id_seq', 1, false);


--
-- Name: tabsdocumentsroles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.tabsdocumentsroles_id_seq', 1, false);


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

SELECT pg_catalog.setval('public.tva_id_seq', 1, false);


--
-- Name: typedocument_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.typedocument_id_seq', 1, false);


--
-- Name: unicaen_signature_observer_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.unicaen_signature_observer_id_seq', 1, false);


--
-- Name: unicaen_signature_process_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.unicaen_signature_process_id_seq', 1, false);


--
-- Name: unicaen_signature_process_step_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.unicaen_signature_process_step_id_seq', 1, false);


--
-- Name: unicaen_signature_recipient_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.unicaen_signature_recipient_id_seq', 1, false);


--
-- Name: unicaen_signature_signature_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.unicaen_signature_signature_id_seq', 1, false);


--
-- Name: unicaen_signature_signatureflow_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.unicaen_signature_signatureflow_id_seq', 1, false);


--
-- Name: unicaen_signature_signatureflowstep_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.unicaen_signature_signatureflowstep_id_seq', 1, false);


--
-- Name: user_role_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.user_role_id_seq', 1, false);


--
-- Name: useraccessdefinition_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.useraccessdefinition_id_seq', 1, false);


--
-- Name: validationperiod_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.validationperiod_id_seq', 1, false);


--
-- Name: workpackage_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.workpackage_id_seq', 1, false);


--
-- Name: workpackageperson_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.workpackageperson_id_seq', 1, false);


--
-- Name: activity_activitymotcle activity_activitymotcle_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activity_activitymotcle
    ADD CONSTRAINT activity_activitymotcle_pkey PRIMARY KEY (activity_id, activitymotcle_id);


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
-- Name: activityavenant activityavenant_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activityavenant
    ADD CONSTRAINT activityavenant_pkey PRIMARY KEY (id);


--
-- Name: activityavenantmodification activityavenantmodification_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activityavenantmodification
    ADD CONSTRAINT activityavenantmodification_pkey PRIMARY KEY (id);


--
-- Name: activitydate activitydate_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activitydate
    ADD CONSTRAINT activitydate_pkey PRIMARY KEY (id);


--
-- Name: activitymotcle activitymotcle_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activitymotcle
    ADD CONSTRAINT activitymotcle_pkey PRIMARY KEY (id);


--
-- Name: activitynote activitynote_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activitynote
    ADD CONSTRAINT activitynote_pkey PRIMARY KEY (id);


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
-- Name: idx_22294f543174800f; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_22294f543174800f ON public.activitymotcle USING btree (createdby_id);


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
-- Name: idx_48ec09aa3174800f; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_48ec09aa3174800f ON public.activitynote USING btree (createdby_id);


--
-- Name: idx_48ec09aa65ff1aec; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_48ec09aa65ff1aec ON public.activitynote USING btree (updatedby_id);


--
-- Name: idx_48ec09aa81c06096; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_48ec09aa81c06096 ON public.activitynote USING btree (activity_id);


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
-- Name: idx_4ca4a03a81c06096; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_4ca4a03a81c06096 ON public.activity_activitymotcle USING btree (activity_id);


--
-- Name: idx_4ca4a03a93593db7; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_4ca4a03a93593db7 ON public.activity_activitymotcle USING btree (activitymotcle_id);


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
-- Name: idx_cfd9096585631a3a; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_cfd9096585631a3a ON public.activityavenantmodification USING btree (avenant_id);


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
-- Name: idx_eec35c6481c06096; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_eec35c6481c06096 ON public.activityavenant USING btree (activity_id);


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
-- Name: activitymotcle fk_22294f543174800f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activitymotcle
    ADD CONSTRAINT fk_22294f543174800f FOREIGN KEY (createdby_id) REFERENCES public.person(id);


--
-- Name: notificationperson fk_22ba6515217bbb47; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notificationperson
    ADD CONSTRAINT fk_22ba6515217bbb47 FOREIGN KEY (person_id) REFERENCES public.person(id) ON DELETE CASCADE;


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
-- Name: activitynote fk_48ec09aa3174800f; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activitynote
    ADD CONSTRAINT fk_48ec09aa3174800f FOREIGN KEY (createdby_id) REFERENCES public.person(id);


--
-- Name: activitynote fk_48ec09aa65ff1aec; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activitynote
    ADD CONSTRAINT fk_48ec09aa65ff1aec FOREIGN KEY (updatedby_id) REFERENCES public.person(id);


--
-- Name: activitynote fk_48ec09aa81c06096; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activitynote
    ADD CONSTRAINT fk_48ec09aa81c06096 FOREIGN KEY (activity_id) REFERENCES public.activity(id);


--
-- Name: contractdocument fk_4a390fe81b50f2d9; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.contractdocument
    ADD CONSTRAINT fk_4a390fe81b50f2d9 FOREIGN KEY (tabdocument_id) REFERENCES public.tabdocument(id);


--
-- Name: contractdocument fk_4a390fe8217bbb47; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.contractdocument
    ADD CONSTRAINT fk_4a390fe8217bbb47 FOREIGN KEY (person_id) REFERENCES public.person(id) ON DELETE SET NULL;


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
-- Name: activity_activitymotcle fk_4ca4a03a81c06096; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activity_activitymotcle
    ADD CONSTRAINT fk_4ca4a03a81c06096 FOREIGN KEY (activity_id) REFERENCES public.activity(id) ON DELETE CASCADE;


--
-- Name: activity_activitymotcle fk_4ca4a03a93593db7; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activity_activitymotcle
    ADD CONSTRAINT fk_4ca4a03a93593db7 FOREIGN KEY (activitymotcle_id) REFERENCES public.activitymotcle(id) ON DELETE CASCADE;


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
    ADD CONSTRAINT fk_c311ba72217bbb47 FOREIGN KEY (person_id) REFERENCES public.person(id) ON DELETE SET NULL;


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
-- Name: unicaen_signature_process_step fk_cf70b0a5ed61183a; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.unicaen_signature_process_step
    ADD CONSTRAINT fk_cf70b0a5ed61183a FOREIGN KEY (signature_id) REFERENCES public.unicaen_signature_signature(id);


--
-- Name: activityavenantmodification fk_cfd9096585631a3a; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activityavenantmodification
    ADD CONSTRAINT fk_cfd9096585631a3a FOREIGN KEY (avenant_id) REFERENCES public.activityavenant(id);


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
-- Name: activityavenant fk_eec35c6481c06096; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activityavenant
    ADD CONSTRAINT fk_eec35c6481c06096 FOREIGN KEY (activity_id) REFERENCES public.activity(id);


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
-- Name: SCHEMA public; Type: ACL; Schema: -; Owner: -
--

REVOKE USAGE ON SCHEMA public FROM PUBLIC;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- PostgreSQL database dump complete
--

