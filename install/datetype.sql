Password: 
--
-- PostgreSQL database dump
--

-- Dumped from database version 13.5 (Debian 13.5-1.pgdg110+1)
-- Dumped by pg_dump version 13.11 (Debian 13.11-0+deb11u1)

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

--
-- Data for Name: datetype; Type: TABLE DATA; Schema: public; Owner: oscar
--

COPY public.datetype (id, label, description, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id, facet, recursivity, finishable) FROM stdin;
1	Début du contrat		1	2016-01-27 14:20:48	\N	\N	\N	\N	\N	\N	\N	f
7	Dépôt de dossier		1	2016-01-27 14:49:01	\N	\N	\N	\N	\N	\N	\N	f
8	Signature		1	2016-01-27 14:49:14	\N	\N	\N	\N	\N	\N	\N	f
12	Rapport de thèse		1	2016-02-08 12:54:00	\N	\N	\N	\N	\N	Scientifique	\N	f
11	Publication d'article		1	2016-02-04 09:34:18	\N	\N	\N	\N	\N	Scientifique	\N	f
15	Rapport d'étude		1	2016-02-08 13:23:55	\N	\N	\N	\N	\N	Scientifique	\N	f
16	Prototype		1	2016-02-08 13:26:10	\N	\N	\N	\N	\N	Scientifique	\N	f
17	Logiciel		1	2016-02-08 13:29:37	\N	\N	\N	\N	\N	Scientifique	\N	f
18	Rapport de recherche		1	2016-02-08 13:30:10	\N	\N	\N	\N	\N	Scientifique	\N	f
19	Rapport final		1	2016-02-08 13:30:42	\N	\N	\N	\N	\N	Scientifique	\N	f
20	Rapport scientifique intermédiaire		1	2016-02-08 13:31:20	\N	\N	\N	\N	\N	Scientifique	\N	f
21	Soutenance de thèse		1	2016-02-08 13:31:40	\N	\N	\N	\N	\N	Scientifique	\N	f
54	Fin de période de rapport/reporting		1	2016-08-26 13:54:00	\N	\N	\N	\N	\N	Général	\N	f
53	Rapport financier		1	2016-08-26 13:53:40	\N	\N	\N	\N	\N	Financier		t
57	Rapport scientifique	\N	1	\N	\N	\N	\N	\N	\N	\N	\N	f
58	Fin des dépenses	\N	1	\N	\N	\N	\N	\N	\N	\N	\N	f
59	Bilan intermédiaire	Test en réunion de sprint 11/12/2023	1	\N	\N	\N	\N	\N	\N	Financier	20,10,1	t
52	Date de fin d'éligibilité des dépenses d'investissement		1	2016-04-07 12:58:56	\N	\N	\N	\N	\N	Financier	10,1,0	f
60	Jalon bidon	un jalon bidon pour tester	1	\N	\N	\N	\N	\N	\N	Général	5,2,1	t
55	Soumission du projet	Même si le montage de projet n'est pas géré sous Oscar, ce jalon peut être utilisé pour mémoire (conserver la date de soumission du projet à toutes fins utiles).\r\nAttention il ne s'agit pas de la date de clôture de l'appel à projets.	1	2018-02-08 18:09:50	\N	\N	\N	\N	\N	Administratif		f
3	Début d'éligibilité des dépenses		1	2016-01-27 14:26:21	\N	\N	\N	\N	\N	Financier		f
4	Fin d'éligibilité des dépenses		1	2016-01-27 14:31:13	\N	\N	\N	\N	\N	Financier		f
9	Première dépense	Date au plus tard de la première dépense payée lorsque le financeur impose un délai maximal.	1	2016-01-27 14:49:42	\N	\N	\N	\N	\N	Financier	60,30	f
\.


--
-- PostgreSQL database dump complete
--

