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
-- Name: scenario; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.scenario (
    scenarioid integer NOT NULL,
    scenario character varying(255),
    projectid integer,
    landuseyear integer,
    locked integer DEFAULT 0,
    model_scen character varying(64),
    shortname character varying(64),
    ownerid integer DEFAULT 1,
    groupid integer DEFAULT 1,
    operms integer DEFAULT 7,
    gperms integer DEFAULT 0,
    pperms integer DEFAULT 0
);


ALTER TABLE public.scenario OWNER TO postgres;

--
-- Name: scenario_scenarioid_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.scenario_scenarioid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.scenario_scenarioid_seq OWNER TO postgres;

--
-- Name: scenario_scenarioid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.scenario_scenarioid_seq OWNED BY public.scenario.scenarioid;


--
-- Name: scenario scenarioid; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.scenario ALTER COLUMN scenarioid SET DEFAULT nextval('public.scenario_scenarioid_seq'::regclass);


--
-- Name: TABLE scenario; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE public.scenario FROM PUBLIC;
REVOKE ALL ON TABLE public.scenario FROM postgres;
GRANT ALL ON TABLE public.scenario TO postgres;
GRANT ALL ON TABLE public.scenario TO robertwb;
GRANT ALL ON TABLE public.scenario TO jkleiner;


--
-- Name: SEQUENCE scenario_scenarioid_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON SEQUENCE public.scenario_scenarioid_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE public.scenario_scenarioid_seq FROM postgres;
GRANT ALL ON SEQUENCE public.scenario_scenarioid_seq TO postgres;
GRANT ALL ON SEQUENCE public.scenario_scenarioid_seq TO robertwb;
GRANT ALL ON SEQUENCE public.scenario_scenarioid_seq TO jkleiner;


--
-- PostgreSQL database dump complete
--

