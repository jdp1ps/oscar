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
-- Data for Name: activitytype; Type: TABLE DATA; Schema: public; Owner: oscar
--

COPY public.activitytype (id, lft, rgt, status, datecreated, dateupdated, datedeleted, createdby_id, updatedby_id, deletedby_id, label, description, nature, centaureid) FROM stdin;
437	3	4	1	2016-03-14 13:05:25	\N	\N	\N	\N	\N	Accord de confidentialité SUPPR		0	\N
421	74	75	1	2016-03-14 12:36:46	\N	\N	\N	\N	\N	International hors UE SUPPR		0	\N
1	1	126	\N	\N	\N	\N	\N	\N	\N	ROOT	\N	\N	\N
486	95	96	1	2018-04-06 10:06:01	\N	\N	\N	\N	\N	Avenant Contrat EUROPE		0	\N
499	118	125	1	2023-11-13 11:18:21	\N	\N	\N	\N	\N	Structures de recherche		0	\N
501	123	124	1	2023-11-13 11:19:41	\N	\N	\N	\N	\N	Unité de recherche		0	\N
496	111	112	1	2023-11-13 11:15:02	\N	\N	\N	\N	\N	Convention de séjour recherche		0	\N
507	87	88	1	2023-11-20 10:42:13	\N	\N	\N	\N	\N	Accord cadre cotutelle		0	\N
506	71	72	1	2023-11-20 10:37:42	\N	\N	\N	\N	\N	EC grant agreement		0	\N
482	61	62	1	2018-02-05 15:48:01	\N	\N	\N	\N	\N	ERANET - JPI		0	\N
510	39	40	1	2023-11-20 10:53:04	\N	\N	\N	\N	\N	Bonus Qualité Recherche		0	\N
511	41	42	1	2023-11-20 10:53:29	\N	\N	\N	\N	\N	Politique scientifique		0	\N
455	11	12	1	2016-03-14 13:15:36	\N	\N	\N	\N	\N	Convention d'aide à l'édition		0	\N
440	15	16	1	2016-03-14 13:06:49	\N	\N	\N	\N	\N	Accord de consortium (hors financement CE)	Accord de consortium si le financement n'est pas issu de la commission européenne. Sinon, choisir la valeur "accord de consortium Europe"	0	\N
488	17	18	1	2023-10-16 13:50:36	\N	\N	\N	\N	\N	Convention de partenariat avec financement (entrant)		0	\N
412	2	13	1	2016-03-14 12:21:48	\N	\N	\N	\N	\N	Valorisation		0	\N
489	81	82	1	2023-11-13 10:50:56	\N	\N	\N	\N	\N	Convention COFRA		0	\N
449	77	78	1	2016-03-14 13:12:36	\N	\N	\N	\N	\N	Contrat doctoral Région		0	\N
493	19	20	1	2023-11-13 11:03:57	\N	\N	\N	\N	\N	Convention de partenariat sans financement		0	\N
451	7	8	1	2016-03-14 13:13:33	\N	\N	\N	\N	\N	Cession de logiciel		0	\N
439	5	6	1	2016-03-14 13:06:25	\N	\N	\N	\N	\N	Accord de copropriété avec exploitation		0	\N
474	9	10	1	2016-03-14 13:24:11	\N	\N	\N	\N	\N	Contrat de licence (brevet)		0	\N
413	14	23	1	2016-03-14 12:24:04	\N	\N	\N	\N	\N	Recherche partenariale		0	\N
419	44	73	1	2016-03-14 12:33:48	\N	\N	\N	\N	\N	Union Européenne SUUPR		0	\N
446	33	34	1	2016-03-14 13:10:20	\N	\N	\N	\N	\N	Partenariat ANR PIA		0	\N
502	113	114	1	2023-11-20 10:32:46	\N	\N	\N	\N	\N	Convention jeune docteur associé		0	\N
490	104	107	1	2023-11-13 10:54:31	\N	\N	\N	\N	\N	Chaire		0	\N
500	119	120	1	2023-11-13 11:18:52	\N	\N	\N	\N	\N	École doctorale		0	\N
497	83	84	1	2023-11-13 11:16:14	\N	\N	\N	\N	\N	Cotutelle de thèse		0	\N
508	89	90	1	2023-11-20 10:46:01	\N	\N	\N	\N	\N	Contrat doctoral autre que Région		0	\N
466	57	58	1	2016-03-14 13:21:19	\N	\N	\N	\N	\N	FP7 - Marie curie SUPPR		0	\N
462	51	52	1	2016-03-14 13:19:32	\N	\N	\N	\N	\N	FEAMP		0	\N
463	53	54	1	2016-03-14 13:19:43	\N	\N	\N	\N	\N	FEDER - 2014 / 2020		0	\N
447	45	46	1	2016-03-14 13:11:02	\N	\N	\N	\N	\N	Autres financements UE		0	\N
418	38	43	1	2016-03-14 12:28:29	\N	\N	\N	\N	\N	Appels à projets internes		0	\N
415	24	31	1	2016-03-14 12:26:44	\N	\N	\N	\N	\N	Allocations de recherche		0	\N
444	27	28	1	2016-03-14 13:09:12	\N	\N	\N	\N	\N	Post-doc subvention autre que Région		0	\N
495	21	22	1	2023-11-13 11:13:54	\N	\N	\N	\N	\N	Convention de partenariat avec financement (sortant)		0	\N
487	67	68	1	2019-01-30 10:21:19	\N	\N	\N	\N	\N	ERASMUS+		0	\N
505	69	70	1	2023-11-20 10:36:24	\N	\N	\N	\N	\N	Una Europa grant letter		0	\N
477	59	60	1	2016-03-14 13:28:10	\N	\N	\N	\N	\N	Interreg III		0	\N
483	63	64	1	2018-02-05 15:48:53	\N	\N	\N	\N	\N	FEADER		0	\N
484	65	66	1	2018-02-05 15:50:18	\N	\N	\N	\N	\N	Direction Générale Europe		0	\N
422	76	91	1	2016-03-14 12:38:18	\N	\N	\N	\N	\N	Doctorat		0	\N
478	121	122	1	2017-10-24 09:58:46	\N	\N	\N	\N	\N	Réseau / partenariat (GIP-GIS)		0	\N
503	115	116	1	2023-11-20 10:33:26	\N	\N	\N	\N	\N	Convention de rattachement		0	\N
485	93	94	1	2018-04-06 10:05:37	\N	\N	\N	\N	\N	Accord de consortium (financement CE)		0	\N
425	92	97	1	2016-03-14 12:42:22	\N	\N	\N	\N	\N	Contrats européens		0	\N
464	55	56	1	2016-03-14 13:19:55	\N	\N	\N	\N	\N	FEDER - 2007 / 2013		0	\N
460	47	48	1	2016-03-14 13:18:43	\N	\N	\N	\N	\N	COST		0	\N
509	35	36	1	2023-11-20 10:48:41	\N	\N	\N	\N	\N	Partenariat ANR hors PIA		0	\N
454	79	80	1	2016-03-14 13:15:14	\N	\N	\N	\N	\N	Convention CIFRE		0	\N
443	25	26	1	2016-03-14 13:08:30	\N	\N	\N	\N	\N	Thèse subvention autre que Région SUPPR		0	\N
491	108	117	1	2023-11-13 10:56:09	\N	\N	\N	\N	\N	Accueil et séjour		0	\N
492	109	110	1	2023-11-13 10:56:47	\N	\N	\N	\N	\N	Convention d'accueil CERFA : « Passeport Talents »		0	\N
504	105	106	1	2023-11-20 10:34:20	\N	\N	\N	\N	\N	Convention chaire professeur junior		0	\N
435	100	101	1	2016-03-14 13:03:05	\N	\N	\N	\N	\N	Organisation colloque/conférence		0	\N
432	98	99	1	2016-03-14 12:57:56	\N	\N	\N	\N	\N	Autres collectivités territoriales		0	\N
498	85	86	1	2023-11-13 11:17:09	\N	\N	\N	\N	\N	Codirection de thèse		0	\N
417	32	37	1	2016-03-14 12:27:57	\N	\N	\N	\N	\N	ANR                                                                                                                                                                                                                                                            		0	\N
461	49	50	1	2016-03-14 13:19:03	\N	\N	\N	\N	\N	EUREKA		0	\N
445	29	30	1	2016-03-14 13:09:51	\N	\N	\N	\N	\N	Post-doc subvention Région		0	\N
\.


--
-- PostgreSQL database dump complete
--

