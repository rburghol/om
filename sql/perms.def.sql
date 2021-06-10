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
-- Name: perms; Type: TABLE; Schema: public; Owner: robertwb
--

CREATE TABLE public.perms (
    permno integer,
    permdesc character varying(24)
);


ALTER TABLE public.perms OWNER TO robertwb;

--
-- Name: TABLE perms; Type: ACL; Schema: public; Owner: robertwb
--

REVOKE ALL ON TABLE public.perms FROM PUBLIC;
REVOKE ALL ON TABLE public.perms FROM robertwb;
GRANT ALL ON TABLE public.perms TO robertwb;
GRANT ALL ON TABLE public.perms TO jkleiner;
GRANT ALL ON TABLE public.perms TO postgres;


--
-- PostgreSQL database dump complete
--

