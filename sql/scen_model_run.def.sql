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
-- Name: scen_model_run; Type: TABLE; Schema: public; Owner: robertwb
--

CREATE TABLE public.scen_model_run (
    runid integer NOT NULL,
    run_name character varying(64),
    run_desc text,
    run_parent_id integer
);


ALTER TABLE public.scen_model_run OWNER TO robertwb;

--
-- Name: scen_model_run_runid_seq; Type: SEQUENCE; Schema: public; Owner: robertwb
--

CREATE SEQUENCE public.scen_model_run_runid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.scen_model_run_runid_seq OWNER TO robertwb;

--
-- Name: scen_model_run_runid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: robertwb
--

ALTER SEQUENCE public.scen_model_run_runid_seq OWNED BY public.scen_model_run.runid;


--
-- Name: scen_model_run runid; Type: DEFAULT; Schema: public; Owner: robertwb
--

ALTER TABLE ONLY public.scen_model_run ALTER COLUMN runid SET DEFAULT nextval('public.scen_model_run_runid_seq'::regclass);


--
-- Name: smr_px; Type: INDEX; Schema: public; Owner: robertwb
--

CREATE INDEX smr_px ON public.scen_model_run USING btree (run_parent_id);


--
-- Name: smr_rx; Type: INDEX; Schema: public; Owner: robertwb
--

CREATE INDEX smr_rx ON public.scen_model_run USING btree (runid);


--
-- Name: TABLE scen_model_run; Type: ACL; Schema: public; Owner: robertwb
--

REVOKE ALL ON TABLE public.scen_model_run FROM PUBLIC;
REVOKE ALL ON TABLE public.scen_model_run FROM robertwb;
GRANT ALL ON TABLE public.scen_model_run TO robertwb;
GRANT ALL ON TABLE public.scen_model_run TO jkleiner;
GRANT ALL ON TABLE public.scen_model_run TO postgres;


--
-- Name: SEQUENCE scen_model_run_runid_seq; Type: ACL; Schema: public; Owner: robertwb
--

REVOKE ALL ON SEQUENCE public.scen_model_run_runid_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE public.scen_model_run_runid_seq FROM robertwb;
GRANT ALL ON SEQUENCE public.scen_model_run_runid_seq TO robertwb;
GRANT ALL ON SEQUENCE public.scen_model_run_runid_seq TO jkleiner;
GRANT ALL ON SEQUENCE public.scen_model_run_runid_seq TO postgres;


--
-- PostgreSQL database dump complete
--

