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
-- Name: wooomm_toolgroups; Type: TABLE; Schema: public; Owner: robertwb
--

CREATE TABLE public.wooomm_toolgroups (
    groupid integer NOT NULL,
    groupname character varying(128),
    description character varying(512)
);


ALTER TABLE public.wooomm_toolgroups OWNER TO robertwb;

--
-- Name: wooomm_toolgroups_groupid_seq; Type: SEQUENCE; Schema: public; Owner: robertwb
--

CREATE SEQUENCE public.wooomm_toolgroups_groupid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.wooomm_toolgroups_groupid_seq OWNER TO robertwb;

--
-- Name: wooomm_toolgroups_groupid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: robertwb
--

ALTER SEQUENCE public.wooomm_toolgroups_groupid_seq OWNED BY public.wooomm_toolgroups.groupid;


--
-- Name: wooomm_toolgroups groupid; Type: DEFAULT; Schema: public; Owner: robertwb
--

ALTER TABLE ONLY public.wooomm_toolgroups ALTER COLUMN groupid SET DEFAULT nextval('public.wooomm_toolgroups_groupid_seq'::regclass);


--
-- Name: TABLE wooomm_toolgroups; Type: ACL; Schema: public; Owner: robertwb
--

REVOKE ALL ON TABLE public.wooomm_toolgroups FROM PUBLIC;
REVOKE ALL ON TABLE public.wooomm_toolgroups FROM robertwb;
GRANT ALL ON TABLE public.wooomm_toolgroups TO robertwb;
GRANT ALL ON TABLE public.wooomm_toolgroups TO jkleiner;
GRANT ALL ON TABLE public.wooomm_toolgroups TO postgres;


--
-- Name: SEQUENCE wooomm_toolgroups_groupid_seq; Type: ACL; Schema: public; Owner: robertwb
--

REVOKE ALL ON SEQUENCE public.wooomm_toolgroups_groupid_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE public.wooomm_toolgroups_groupid_seq FROM robertwb;
GRANT ALL ON SEQUENCE public.wooomm_toolgroups_groupid_seq TO robertwb;
GRANT ALL ON SEQUENCE public.wooomm_toolgroups_groupid_seq TO jkleiner;
GRANT ALL ON SEQUENCE public.wooomm_toolgroups_groupid_seq TO postgres;


--
-- PostgreSQL database dump complete
--

