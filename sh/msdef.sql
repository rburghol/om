--
-- PostgreSQL database dump
--

SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: data_scenario; Type: TABLE; Schema: public; Owner: postgres; Tablespace:
--

CREATE TABLE data_scenario (
    runid integer NOT NULL,
    run_parent integer,
    starttime timestamp without time zone,
    endtime timestamp without time zone,
    rundate timestamp without time zone DEFAULT now()
);


ALTER TABLE public.data_scenario OWNER TO postgres;

--
-- Name: data_scenario_runid_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE data_scenario_runid_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.data_scenario_runid_seq OWNER TO postgres;

--
-- Name: data_scenario_runid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE data_scenario_runid_seq OWNED BY data_scenario.runid;


--
-- Name: map_element_scenario; Type: TABLE; Schema: public; Owner: postgres; Tablespace:
--

CREATE TABLE map_element_scenario (
    runid integer,
    elementid integer
);


ALTER TABLE public.map_element_scenario OWNER TO postgres;

--
-- Name: run_log; Type: TABLE; Schema: public; Owner: postgres; Tablespace:
--

CREATE TABLE run_log (
    elementid integer,
    last_rundate timestamp without time zone DEFAULT now(),
    runid integer DEFAULT (-1)
);


ALTER TABLE public.run_log OWNER TO postgres;

--
-- Name: session_tbl_log; Type: TABLE; Schema: public; Owner: postgres; Tablespace:
--

CREATE TABLE session_tbl_log (
    tablename character varying(255),
    creation_date timestamp without time zone DEFAULT now()
);


ALTER TABLE public.session_tbl_log OWNER TO postgres;

--
-- Name: sessions; Type: TABLE; Schema: public; Owner: postgres; Tablespace:
--

CREATE TABLE sessions (
    sid integer NOT NULL,
    session_id character varying(255),
    time_created timestamp without time zone DEFAULT now()
);


ALTER TABLE public.sessions OWNER TO postgres;

--
-- Name: sessions_sid_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE sessions_sid_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


ALTER TABLE public.sessions_sid_seq OWNER TO postgres;

--
-- Name: sessions_sid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE sessions_sid_seq OWNED BY sessions.sid;


--
-- Name: runid; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE data_scenario ALTER COLUMN runid SET DEFAULT nextval('data_scenario_runid_seq'::regclass);


--
-- Name: sid; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE sessions ALTER COLUMN sid SET DEFAULT nextval('sessions_sid_seq'::regclass);


--
-- PostgreSQL database dump complete
--

