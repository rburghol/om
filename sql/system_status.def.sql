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
-- Name: system_status; Type: TABLE; Schema: public; Owner: robertwb
--

CREATE TABLE public.system_status (
    element_key integer,
    element_name character varying(32),
    status_flag integer,
    status_mesg character varying(255),
    process_ownerid integer,
    last_updated timestamp with time zone DEFAULT now(),
    pid integer DEFAULT 0,
    host character varying(128),
    runid integer DEFAULT '-1'::integer
);


ALTER TABLE public.system_status OWNER TO robertwb;

--
-- Name: TABLE system_status; Type: ACL; Schema: public; Owner: robertwb
--

REVOKE ALL ON TABLE public.system_status FROM PUBLIC;
REVOKE ALL ON TABLE public.system_status FROM robertwb;
GRANT ALL ON TABLE public.system_status TO robertwb;
GRANT ALL ON TABLE public.system_status TO jkleiner;
GRANT ALL ON TABLE public.system_status TO postgres;


--
-- PostgreSQL database dump complete
--

