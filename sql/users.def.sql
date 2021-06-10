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
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    userid integer NOT NULL,
    username character varying(64),
    usertype integer,
    userpass character varying(32),
    defaultproject integer DEFAULT 1,
    indir character varying(255),
    outdir character varying(255),
    firstname character varying(64),
    lastname character varying(64),
    email character varying(128),
    groupid integer DEFAULT 2,
    defscenario integer DEFAULT '-1'::integer,
    date_created date DEFAULT (now())::date,
    remote_passkey character varying(1024)
);


ALTER TABLE public.users OWNER TO postgres;

--
-- Name: users_userid_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.users_userid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.users_userid_seq OWNER TO postgres;

--
-- Name: users_userid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.users_userid_seq OWNED BY public.users.userid;


--
-- Name: users userid; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN userid SET DEFAULT nextval('public.users_userid_seq'::regclass);


--
-- Name: usr_uix; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX usr_uix ON public.users USING btree (userid);


--
-- Name: TABLE users; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE public.users FROM PUBLIC;
REVOKE ALL ON TABLE public.users FROM postgres;
GRANT ALL ON TABLE public.users TO postgres;
GRANT ALL ON TABLE public.users TO robertwb;
GRANT ALL ON TABLE public.users TO jkleiner;


--
-- Name: SEQUENCE users_userid_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON SEQUENCE public.users_userid_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE public.users_userid_seq FROM postgres;
GRANT ALL ON SEQUENCE public.users_userid_seq TO postgres;
GRANT ALL ON SEQUENCE public.users_userid_seq TO robertwb;
GRANT ALL ON SEQUENCE public.users_userid_seq TO jkleiner;


--
-- PostgreSQL database dump complete
--

