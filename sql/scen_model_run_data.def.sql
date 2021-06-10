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
-- Name: scen_model_run_data; Type: TABLE; Schema: public; Owner: robertwb
--

CREATE TABLE public.scen_model_run_data (
    datid integer NOT NULL,
    runid integer,
    elementid integer,
    temporal_res character varying(32),
    starttime timestamp without time zone,
    endtime timestamp without time zone,
    dataname character varying(32),
    dataval double precision,
    date_created timestamp without time zone DEFAULT now(),
    datatext character varying(128),
    model_report_time timestamp without time zone,
    reporting_frequency character varying(32)
);


ALTER TABLE public.scen_model_run_data OWNER TO robertwb;

--
-- Name: scen_model_run_data_datid_seq; Type: SEQUENCE; Schema: public; Owner: robertwb
--

CREATE SEQUENCE public.scen_model_run_data_datid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.scen_model_run_data_datid_seq OWNER TO robertwb;

--
-- Name: scen_model_run_data_datid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: robertwb
--

ALTER SEQUENCE public.scen_model_run_data_datid_seq OWNED BY public.scen_model_run_data.datid;


--
-- Name: scen_model_run_data datid; Type: DEFAULT; Schema: public; Owner: robertwb
--

ALTER TABLE ONLY public.scen_model_run_data ALTER COLUMN datid SET DEFAULT nextval('public.scen_model_run_data_datid_seq'::regclass);


--
-- Name: TABLE scen_model_run_data; Type: ACL; Schema: public; Owner: robertwb
--

REVOKE ALL ON TABLE public.scen_model_run_data FROM PUBLIC;
REVOKE ALL ON TABLE public.scen_model_run_data FROM robertwb;
GRANT ALL ON TABLE public.scen_model_run_data TO robertwb;
GRANT ALL ON TABLE public.scen_model_run_data TO wsp_ro;
GRANT ALL ON TABLE public.scen_model_run_data TO jkleiner;
GRANT ALL ON TABLE public.scen_model_run_data TO postgres;


--
-- Name: SEQUENCE scen_model_run_data_datid_seq; Type: ACL; Schema: public; Owner: robertwb
--

REVOKE ALL ON SEQUENCE public.scen_model_run_data_datid_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE public.scen_model_run_data_datid_seq FROM robertwb;
GRANT ALL ON SEQUENCE public.scen_model_run_data_datid_seq TO robertwb;
GRANT ALL ON SEQUENCE public.scen_model_run_data_datid_seq TO jkleiner;
GRANT ALL ON SEQUENCE public.scen_model_run_data_datid_seq TO postgres;


--
-- PostgreSQL database dump complete
--

