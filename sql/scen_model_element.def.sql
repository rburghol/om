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
-- Name: scen_model_element; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.scen_model_element (
    elementid integer NOT NULL,
    scenarioid integer,
    modelid integer,
    elemname character varying(255),
    file_based integer DEFAULT 0,
    elem_xml text,
    elem_path character varying(512),
    objectclass character varying(512),
    the_geom public.geometry,
    elemprops text,
    eleminputs text,
    operms integer DEFAULT 7,
    elemcomponents text,
    elemoperators text[] DEFAULT ARRAY[''::text],
    component_type integer DEFAULT 1,
    gperms integer DEFAULT 4,
    pperms integer DEFAULT 4,
    groupid integer,
    ownerid integer DEFAULT 1,
    output_cache text,
    poly_geom public.geometry,
    line_geom public.geometry,
    point_geom public.geometry,
    geomtype integer DEFAULT 1,
    hier_order integer DEFAULT 0,
    custom1 character varying(24),
    custom2 character varying(64),
    cacheable integer DEFAULT 1,
    cached_queries character varying[],
    hydrocode character varying(255),
    riverseg character varying(255),
    wdtype character varying(6),
    CONSTRAINT enforce_dims_line_geom CHECK ((public.st_ndims(line_geom) = 2)),
    CONSTRAINT enforce_dims_point_geom CHECK ((public.st_ndims(point_geom) = 2)),
    CONSTRAINT enforce_dims_poly_geom CHECK ((public.st_ndims(poly_geom) = 2)),
    CONSTRAINT enforce_dims_the_geom CHECK ((public.st_ndims(the_geom) = 2)),
    CONSTRAINT enforce_geotype_line_geom CHECK (((public.geometrytype(line_geom) = 'MULTILINESTRING'::text) OR (line_geom IS NULL))),
    CONSTRAINT enforce_geotype_point_geom CHECK (((public.geometrytype(point_geom) = 'POINT'::text) OR (point_geom IS NULL))),
    CONSTRAINT enforce_geotype_poly_geom CHECK (((public.geometrytype(poly_geom) = 'MULTIPOLYGON'::text) OR (poly_geom IS NULL))),
    CONSTRAINT enforce_geotype_the_geom CHECK (((public.geometrytype(the_geom) = 'POINT'::text) OR (the_geom IS NULL))),
    CONSTRAINT enforce_srid_line_geom CHECK ((public.st_srid(line_geom) = 4326)),
    CONSTRAINT enforce_srid_point_geom CHECK ((public.st_srid(point_geom) = 4326)),
    CONSTRAINT enforce_srid_poly_geom CHECK ((public.st_srid(poly_geom) = 4326)),
    CONSTRAINT enforce_srid_the_geom CHECK ((public.st_srid(the_geom) = 4326))
);


ALTER TABLE public.scen_model_element OWNER TO postgres;

--
-- Name: scen_model_element_elementid_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.scen_model_element_elementid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.scen_model_element_elementid_seq OWNER TO postgres;

--
-- Name: scen_model_element_elementid_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.scen_model_element_elementid_seq OWNED BY public.scen_model_element.elementid;


--
-- Name: scen_model_element elementid; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.scen_model_element ALTER COLUMN elementid SET DEFAULT nextval('public.scen_model_element_elementid_seq'::regclass);


--
-- Name: sme_c1ix; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sme_c1ix ON public.scen_model_element USING btree (custom1);


--
-- Name: sme_c2ix; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sme_c2ix ON public.scen_model_element USING btree (custom2);


--
-- Name: sme_eix; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sme_eix ON public.scen_model_element USING btree (elementid);


--
-- Name: sme_mpgix; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sme_mpgix ON public.scen_model_element USING gist (poly_geom);


--
-- Name: sme_pgix; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sme_pgix ON public.scen_model_element USING gist (point_geom);


--
-- Name: sme_scix; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sme_scix ON public.scen_model_element USING btree (scenarioid);


--
-- Name: TABLE scen_model_element; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON TABLE public.scen_model_element FROM PUBLIC;
REVOKE ALL ON TABLE public.scen_model_element FROM postgres;
GRANT ALL ON TABLE public.scen_model_element TO postgres;
GRANT SELECT ON TABLE public.scen_model_element TO wsp_ro;
GRANT ALL ON TABLE public.scen_model_element TO robertwb;
GRANT ALL ON TABLE public.scen_model_element TO jkleiner;


--
-- Name: SEQUENCE scen_model_element_elementid_seq; Type: ACL; Schema: public; Owner: postgres
--

REVOKE ALL ON SEQUENCE public.scen_model_element_elementid_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE public.scen_model_element_elementid_seq FROM postgres;
GRANT ALL ON SEQUENCE public.scen_model_element_elementid_seq TO postgres;
GRANT ALL ON SEQUENCE public.scen_model_element_elementid_seq TO robertwb;
GRANT ALL ON SEQUENCE public.scen_model_element_elementid_seq TO jkleiner;


--
-- PostgreSQL database dump complete
--

