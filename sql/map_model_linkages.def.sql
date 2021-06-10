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
-- Name: map_model_linkages; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.map_model_linkages (
    projectid integer,
    scenarioid integer,
    linktype integer,
    src_id integer,
    dest_id integer,
    src_prop character varying(255),
    dest_prop character varying(255),
    linkid integer NOT NULL
);


ALTER TABLE public.map_model_linkages OWNER TO postgres;

--
-- Name: map_model_linkages_linkid_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.map_model_linkages_linkid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.map_model_linkages_linkid_seq OWNER TO postgres;

--
-- Name: map_model_linkages_linkid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.map_model_linkages_linkid_seq OWNED BY public.map_model_linkages.linkid;


--
-- Name: map_model_linkages linkid; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.map_model_linkages ALTER COLUMN linkid SET DEFAULT nextval('public.map_model_linkages_linkid_seq'::regclass);


--
-- Name: mml_did; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX mml_did ON public.map_model_linkages USING btree (dest_id);


--
-- Name: mml_ltix; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX mml_ltix ON public.map_model_linkages USING btree (linktype);


--
-- Name: mml_pid; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX mml_pid ON public.map_model_linkages USING btree (projectid);


--
-- Name: mml_sid; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX mml_sid ON public.map_model_linkages USING btree (scenarioid);


--
-- Name: mml_srid; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX mml_srid ON public.map_model_linkages USING btree (src_id);


--
-- Name: TABLE map_model_linkages; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE public.map_model_linkages FROM PUBLIC;
REVOKE ALL ON TABLE public.map_model_linkages FROM postgres;
GRANT ALL ON TABLE public.map_model_linkages TO postgres;
GRANT ALL ON TABLE public.map_model_linkages TO robertwb;
GRANT ALL ON TABLE public.map_model_linkages TO jkleiner;


--
-- Name: SEQUENCE map_model_linkages_linkid_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON SEQUENCE public.map_model_linkages_linkid_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE public.map_model_linkages_linkid_seq FROM postgres;
GRANT ALL ON SEQUENCE public.map_model_linkages_linkid_seq TO postgres;
GRANT ALL ON SEQUENCE public.map_model_linkages_linkid_seq TO robertwb;
GRANT ALL ON SEQUENCE public.map_model_linkages_linkid_seq TO jkleiner;


--
-- PostgreSQL database dump complete
--

