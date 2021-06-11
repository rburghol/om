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
-- Name: scen_model_run_elements; Type: TABLE; Schema: public; Owner: robertwb
--

CREATE TABLE public.scen_model_run_elements (
    runid integer DEFAULT '-1'::integer,
    elementid integer,
    starttime timestamp without time zone,
    endtime timestamp without time zone,
    elem_xml text,
    output_file character varying(255),
    run_date timestamp without time zone DEFAULT '2010-08-30 16:00:00'::timestamp without time zone,
    host character varying(255),
    fullpath character varying(255),
    run_summary character varying(2048),
    run_verified integer DEFAULT 0,
    remote_path character varying(255),
    exec_time_mean double precision DEFAULT 0.0,
    verified_date date,
    remote_url character varying(512),
    elemoperators text[] DEFAULT ARRAY[''::text],
    debugfile character varying(255),
    report character varying(1024)
);


ALTER TABLE public.scen_model_run_elements OWNER TO robertwb;

--
-- Name: smre_ex; Type: INDEX; Schema: public; Owner: robertwb
--

CREATE INDEX smre_ex ON public.scen_model_run_elements USING btree (elementid);


--
-- Name: smre_rx; Type: INDEX; Schema: public; Owner: robertwb
--

CREATE INDEX smre_rx ON public.scen_model_run_elements USING btree (runid);


--
-- Name: TABLE scen_model_run_elements; Type: ACL; Schema: public; Owner: robertwb
--

REVOKE ALL ON TABLE public.scen_model_run_elements FROM PUBLIC;
REVOKE ALL ON TABLE public.scen_model_run_elements FROM robertwb;
GRANT ALL ON TABLE public.scen_model_run_elements TO robertwb;
GRANT ALL ON TABLE public.scen_model_run_elements TO jkleiner;
GRANT ALL ON TABLE public.scen_model_run_elements TO postgres;


--
-- PostgreSQL database dump complete
--

