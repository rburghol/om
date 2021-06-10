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
-- Name: groups; Type: TABLE; Schema: public; Owner: robertwb
--

CREATE TABLE public.groups (
    groupid integer NOT NULL,
    ownerid integer,
    groupname character varying(255)
);


ALTER TABLE public.groups OWNER TO robertwb;

--
-- Name: groups_groupid_seq; Type: SEQUENCE; Schema: public; Owner: robertwb
--

CREATE SEQUENCE public.groups_groupid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.groups_groupid_seq OWNER TO robertwb;

--
-- Name: groups_groupid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: robertwb
--

ALTER SEQUENCE public.groups_groupid_seq OWNED BY public.groups.groupid;


--
-- Name: groups groupid; Type: DEFAULT; Schema: public; Owner: robertwb
--

ALTER TABLE ONLY public.groups ALTER COLUMN groupid SET DEFAULT nextval('public.groups_groupid_seq'::regclass);


--
-- Name: TABLE groups; Type: ACL; Schema: public; Owner: robertwb
--

REVOKE ALL ON TABLE public.groups FROM PUBLIC;
REVOKE ALL ON TABLE public.groups FROM robertwb;
GRANT ALL ON TABLE public.groups TO robertwb;
GRANT ALL ON TABLE public.groups TO jkleiner;
GRANT ALL ON TABLE public.groups TO postgres;


--
-- Name: SEQUENCE groups_groupid_seq; Type: ACL; Schema: public; Owner: robertwb
--

REVOKE ALL ON SEQUENCE public.groups_groupid_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE public.groups_groupid_seq FROM robertwb;
GRANT ALL ON SEQUENCE public.groups_groupid_seq TO robertwb;
GRANT ALL ON SEQUENCE public.groups_groupid_seq TO jkleiner;
GRANT ALL ON SEQUENCE public.groups_groupid_seq TO postgres;


--
-- PostgreSQL database dump complete
--

