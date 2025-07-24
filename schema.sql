--
-- PostgreSQL database dump
--

-- Dumped from database version 14.18 (Ubuntu 14.18-0ubuntu0.22.04.1)
-- Dumped by pg_dump version 14.10

-- Started on 2025-07-24 07:23:19 UTC

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

-- Role: pub_admin
-- DROP ROLE IF EXISTS pub_admin;
CREATE ROLE pub_admin WITH
  LOGIN
  NOSUPERUSER
  INHERIT
  NOCREATEDB
  NOCREATEROLE
  NOREPLICATION
  PASSWORD 'adm4321';

-- Role: pub_user
-- DROP ROLE IF EXISTS pub_user;

CREATE ROLE pub_user WITH
  LOGIN
  NOSUPERUSER
  INHERIT
  NOCREATEDB
  NOCREATEROLE
  NOREPLICATION
  PASSWORD 'user1234';

--
-- TOC entry 3365 (class 1262 OID 17175)
-- Name: publications; Type: DATABASE; Schema: -; Owner: -
--

CREATE DATABASE publications WITH TEMPLATE = template0 ENCODING = 'UTF8' LOCALE = 'ru_RU.UTF-8';


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 212 (class 1259 OID 17186)
-- Name: comments; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.comments (
    id bigint NOT NULL,
    post_id bigint NOT NULL,
    name text NOT NULL,
    email character varying(254) NOT NULL,
    body text NOT NULL
);


--
-- TOC entry 3366 (class 0 OID 0)
-- Dependencies: 212
-- Name: TABLE comments; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.comments IS 'Post comments';


--
-- TOC entry 211 (class 1259 OID 17185)
-- Name: comments_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.comments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3367 (class 0 OID 0)
-- Dependencies: 211
-- Name: comments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.comments_id_seq OWNED BY public.comments.id;


--
-- TOC entry 210 (class 1259 OID 17177)
-- Name: posts; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.posts (
    id bigint NOT NULL,
    title text NOT NULL,
    body text NOT NULL
);


--
-- TOC entry 3368 (class 0 OID 0)
-- Dependencies: 210
-- Name: TABLE posts; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE public.posts IS 'User posts';


--
-- TOC entry 209 (class 1259 OID 17176)
-- Name: posts_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.posts_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3369 (class 0 OID 0)
-- Dependencies: 209
-- Name: posts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.posts_id_seq OWNED BY public.posts.id;


--
-- TOC entry 3215 (class 2604 OID 17189)
-- Name: comments id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.comments ALTER COLUMN id SET DEFAULT nextval('public.comments_id_seq'::regclass);


--
-- TOC entry 3214 (class 2604 OID 17180)
-- Name: posts id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.posts ALTER COLUMN id SET DEFAULT nextval('public.posts_id_seq'::regclass);


--
-- TOC entry 3219 (class 2606 OID 17193)
-- Name: comments comments_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.comments
    ADD CONSTRAINT comments_pkey PRIMARY KEY (id);


--
-- TOC entry 3217 (class 2606 OID 17184)
-- Name: posts posts_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.posts
    ADD CONSTRAINT posts_pkey PRIMARY KEY (id);


--
-- TOC entry 3220 (class 2606 OID 17194)
-- Name: comments post_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.comments
    ADD CONSTRAINT post_id_fk FOREIGN KEY (post_id) REFERENCES public.posts(id) ON DELETE CASCADE NOT VALID;


-- Completed on 2025-07-24 07:23:19 UTC

--
-- PostgreSQL database dump complete
--
