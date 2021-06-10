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
-- Name: who_xmlobjects; Type: TABLE; Schema: public; Owner: robertwb
--

CREATE TABLE public.who_xmlobjects (
    classname character varying(255),
    classxml text,
    type integer,
    parent character varying(255),
    name character varying(255),
    parentprops character varying(512),
    description character varying(512),
    localprops character varying(512),
    toolgroup integer DEFAULT 7,
    geomtype integer DEFAULT 1
);


ALTER TABLE public.who_xmlobjects OWNER TO robertwb;

--
-- Name: wxo_name; Type: INDEX; Schema: public; Owner: robertwb
--

CREATE INDEX wxo_name ON public.who_xmlobjects USING btree (classname);


--
-- Name: TABLE who_xmlobjects; Type: ACL; Schema: public; Owner: robertwb
--

REVOKE ALL ON TABLE public.who_xmlobjects FROM PUBLIC;
REVOKE ALL ON TABLE public.who_xmlobjects FROM robertwb;
GRANT ALL ON TABLE public.who_xmlobjects TO robertwb;
GRANT ALL ON TABLE public.who_xmlobjects TO jkleiner;
GRANT ALL ON TABLE public.who_xmlobjects TO postgres;


--
-- PostgreSQL database dump complete
--

