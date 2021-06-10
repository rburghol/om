--
-- PostgreSQL database dump
--

-- Dumped from database version 9.5.4
-- Dumped by pg_dump version 12.7 (Ubuntu 12.7-0ubuntu0.20.04.1)

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

SET default_tablespace = '';

--
-- Name: project; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.project (
    projectname character varying(255),
    owner integer DEFAULT 1,
    projectid integer NOT NULL,
    mapfile character varying(1024),
    defscenario integer DEFAULT '-1'::integer
);


ALTER TABLE public.project OWNER TO postgres;

--
-- Name: project_projectid_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.project_projectid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.project_projectid_seq OWNER TO postgres;

--
-- Name: project_projectid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.project_projectid_seq OWNED BY public.project.projectid;


--
-- Name: project projectid; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.project ALTER COLUMN projectid SET DEFAULT nextval('public.project_projectid_seq'::regclass);


--
-- Name: project project_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.project
    ADD CONSTRAINT project_pkey PRIMARY KEY (projectid);


--
-- Name: TABLE project; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE public.project FROM PUBLIC;
REVOKE ALL ON TABLE public.project FROM postgres;
GRANT ALL ON TABLE public.project TO postgres;
GRANT ALL ON TABLE public.project TO robertwb;
GRANT ALL ON TABLE public.project TO jkleiner;


--
-- Name: SEQUENCE project_projectid_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON SEQUENCE public.project_projectid_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE public.project_projectid_seq FROM postgres;
GRANT ALL ON SEQUENCE public.project_projectid_seq TO postgres;
GRANT ALL ON SEQUENCE public.project_projectid_seq TO robertwb;
GRANT ALL ON SEQUENCE public.project_projectid_seq TO jkleiner;


--
-- PostgreSQL database dump complete
--

