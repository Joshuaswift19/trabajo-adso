--
-- PostgreSQL database dump
--

-- Dumped from database version 17.4
-- Dumped by pg_dump version 17.4

-- Started on 2025-05-01 09:54:01

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 225 (class 1259 OID 16609)
-- Name: calificacion; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.calificacion (
    usuario_id bigint NOT NULL,
    documento_id bigint NOT NULL,
    puntuacion integer NOT NULL,
    CONSTRAINT calificacion_puntuacion_check CHECK (((puntuacion >= 1) AND (puntuacion <= 5)))
);


ALTER TABLE public.calificacion OWNER TO postgres;

--
-- TOC entry 220 (class 1259 OID 16512)
-- Name: comentario; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.comentario (
    id bigint NOT NULL,
    contenido text NOT NULL,
    idusuario bigint NOT NULL,
    documento_id bigint NOT NULL,
    fecha timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.comentario OWNER TO postgres;

--
-- TOC entry 226 (class 1259 OID 16631)
-- Name: comentario_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.comentario_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.comentario_id_seq OWNER TO postgres;

--
-- TOC entry 4962 (class 0 OID 0)
-- Dependencies: 226
-- Name: comentario_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.comentario_id_seq OWNED BY public.comentario.id;


--
-- TOC entry 227 (class 1259 OID 16639)
-- Name: comunidad; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.comunidad (
    id bigint DEFAULT nextval('public.comentario_id_seq'::regclass) NOT NULL,
    contenido text NOT NULL,
    idusuario bigint NOT NULL,
    idpublicacion bigint,
    respuesta text
);


ALTER TABLE public.comunidad OWNER TO postgres;

--
-- TOC entry 223 (class 1259 OID 16580)
-- Name: deseado; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.deseado (
    id integer NOT NULL,
    usuario_id integer NOT NULL,
    documento_id integer NOT NULL
);


ALTER TABLE public.deseado OWNER TO postgres;

--
-- TOC entry 224 (class 1259 OID 16591)
-- Name: deseado_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.deseado_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.deseado_id_seq OWNER TO postgres;

--
-- TOC entry 4963 (class 0 OID 0)
-- Dependencies: 224
-- Name: deseado_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.deseado_id_seq OWNED BY public.deseado.id;


--
-- TOC entry 222 (class 1259 OID 16578)
-- Name: documento_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.documento_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.documento_id_seq OWNER TO postgres;

--
-- TOC entry 218 (class 1259 OID 16488)
-- Name: documento; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.documento (
    id bigint DEFAULT nextval('public.documento_id_seq'::regclass) NOT NULL,
    titulo text NOT NULL,
    descripcion text NOT NULL,
    categoria text NOT NULL,
    autor text NOT NULL,
    enlace text NOT NULL,
    idusuario integer,
    imagen_url text NOT NULL,
    creado_en timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    publication_date date NOT NULL
);


ALTER TABLE public.documento OWNER TO postgres;

--
-- TOC entry 219 (class 1259 OID 16500)
-- Name: publicacion; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.publicacion (
    id bigint NOT NULL,
    titulo character(1) NOT NULL,
    contenido text NOT NULL,
    fecha date NOT NULL,
    autor character(1) NOT NULL,
    idusuario integer
);


ALTER TABLE public.publicacion OWNER TO postgres;

--
-- TOC entry 217 (class 1259 OID 16483)
-- Name: usuarios; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.usuarios (
    id bigint NOT NULL,
    nombre character varying NOT NULL,
    identificacion bigint NOT NULL,
    edad bigint NOT NULL,
    correo character varying NOT NULL,
    ficha bigint NOT NULL,
    password character varying NOT NULL,
    rol smallint NOT NULL,
    reset_token character varying(255),
    reset_token_expiration timestamp without time zone,
    foto_perfil character varying,
    CONSTRAINT usuarios_rol_check CHECK ((rol = ANY (ARRAY[1, 2])))
);


ALTER TABLE public.usuarios OWNER TO postgres;

--
-- TOC entry 221 (class 1259 OID 16541)
-- Name: usuarios_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.usuarios_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.usuarios_id_seq OWNER TO postgres;

--
-- TOC entry 4964 (class 0 OID 0)
-- Dependencies: 221
-- Name: usuarios_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.usuarios_id_seq OWNED BY public.usuarios.id;


--
-- TOC entry 4772 (class 2604 OID 16632)
-- Name: comentario id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.comentario ALTER COLUMN id SET DEFAULT nextval('public.comentario_id_seq'::regclass);


--
-- TOC entry 4774 (class 2604 OID 16592)
-- Name: deseado id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.deseado ALTER COLUMN id SET DEFAULT nextval('public.deseado_id_seq'::regclass);


--
-- TOC entry 4769 (class 2604 OID 16542)
-- Name: usuarios id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuarios ALTER COLUMN id SET DEFAULT nextval('public.usuarios_id_seq'::regclass);


--
-- TOC entry 4954 (class 0 OID 16609)
-- Dependencies: 225
-- Data for Name: calificacion; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.calificacion (usuario_id, documento_id, puntuacion) FROM stdin;
9	19	5
14	19	4
\.


--
-- TOC entry 4949 (class 0 OID 16512)
-- Dependencies: 220
-- Data for Name: comentario; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.comentario (id, contenido, idusuario, documento_id, fecha) FROM stdin;
12	muy bueno	9	19	2025-04-29 13:16:03.667204
16	Me sirvio mucho	14	19	2025-04-30 15:21:44.632851
\.


--
-- TOC entry 4956 (class 0 OID 16639)
-- Dependencies: 227
-- Data for Name: comunidad; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.comunidad (id, contenido, idusuario, idpublicacion, respuesta) FROM stdin;
13	sdjfñaslgjsfñhoñj	9	10	jfaiosjifweopfijo
14	huhojipóo+k\r\n	9	10	hfadsfsfasf
15	ugñhujoñh	14	10	\N
\.


--
-- TOC entry 4952 (class 0 OID 16580)
-- Dependencies: 223
-- Data for Name: deseado; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.deseado (id, usuario_id, documento_id) FROM stdin;
\.


--
-- TOC entry 4947 (class 0 OID 16488)
-- Dependencies: 218
-- Data for Name: documento; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.documento (id, titulo, descripcion, categoria, autor, enlace, idusuario, imagen_url, creado_en, publication_date) FROM stdin;
14	Aprenda a Pensar Como un Programador con Python	Python es un lenguaje de programación divertido, fácil de usar y muy popular en los últimos años. Creado por Guido van Rossum hace una década, su sencilla sintaxis se inspira en ABC, un lenguaje educativo de los 80, pero también incorpora características de C++, Java, Modula-3 y Scheme. Esto lo hace atractivo para programadores profesionales, científicos, artistas y educadores. Aunque lenguajes como C++ o Java son populares, Python destaca por ser más divertido y productivo para programar y enseñar.	programacion	Allen Downey	/Uploads/documents/file_6807bef1cffa0_aprenda-a-pensar-como-un-programador-con-python.pdf	\N	/Uploads/img/img_6807bef1cde91_Captura de pantalla 2025-04-22 110550.png	2025-04-22 11:08:17.864846	2002-01-01
10	Padre Rico Padre Pobre	"Padre Rico, Padre Pobre" es un libro de finanzas personales escrito por Robert T. Kiyosaki, que se ha convertido en un bestseller mundial. El libro contrasta las enseñanzas de dos figuras paternas en la vida del autor: su padre biológico, que era educado pero siempre tuvo problemas financieros, y su padre adoptivo, que era un empresario exitoso y le enseñó sobre la creación de riqueza y la inversión. Kiyosaki enfatiza la importancia de la educación financiera y cómo los ricos piensan de manera diferente sobre el dinero en comparación con los pobres y la clase media.	Finanzas	Robert T. Kiyosaki	/Uploads/documents/file_6802d37b696d6_Padre rico padre pobre - Robert Toru Kiyosaki.pdf	\N	/Uploads/img/img_6802d37b693f6_padre rico padre pobre.jpg	2025-04-18 17:34:35.435733	1997-04-08
15	El lenguaje de programación C#	C# es un lenguaje de programación que toma las mejores características de lenguajes preexistentes como Visual Basic, Java o C++ y las combina en uno solo.	programacion	José Antonio González Seco	/Uploads/documents/file_6807c0822fd4b_El lenguaje de programación C#.pdf	\N	/Uploads/img/img_6807c0822f6ae_Captura de pantalla 2025-04-22 111147.png	2025-04-22 11:14:58.199972	2018-06-08
16	Eloquent JavaScript	Este libro trata de JavaScript, programación y los maravillosos mundos digitales.	programacion	Marijn Haverbeke	/Uploads/documents/file_6807c201a2d70_Eloquent_JavaScript.pdf	\N	/Uploads/img/img_6807c201a2908_Captura de pantalla 2025-04-22 111856.png	2025-04-22 11:21:21.669914	2018-01-01
13	Algoritmos y programación	Desde 2004, el Instituto Nuestra Señora de la Asunción (INSA) imparte un curso de Algoritmos y Programación para estudiantes de 4° y 5° de primaria, ajustando contenidos y métodos para integrar la programación con la resolución de problemas matemáticos. La experiencia muestra que programar en entornos como Logo mejora la comprensión de conceptos matemáticos al resolver problemas desafiantes, requiriendo competencias previas en lectura y matemáticas básicas.\n\nFruto de años de trabajo, se presenta una Guía para docentes de Informática (grados 3° a 9°) y un Cuaderno de Trabajo para estudiantes de 4° y 5°, que integran la programación con el desarrollo de habilidades para resolver problemas. La Guía, con cuatro unidades, es adaptable a secundaria ajustando ejemplos y ejercicios según las capacidades de los estudiantes.	programacion	JUAN CARLOS LÓPEZ GARCÍA	/Uploads/documents/file_6807b63c183d3_AlgoritmosProgramacion.pdf	\N	/Uploads/img/img_6807b63c17869_Captura de pantalla 2025-04-22 102746.png	2025-04-22 10:31:08.11534	2009-01-01
17	Fundamentos de programación en Java	Este es un curso completo de programación Java, aprende los fundamentos del lenguaje Java	programacion	Jorge Martínez Ladrón de Guevara	/Uploads/documents/file_68082bedcee0a_Fundamentos de programación en Java.pdf	\N	/Uploads/img/img_68082bedce6d0_Captura de pantalla 2025-04-22 184938.png	2025-04-22 18:53:17.851097	2022-07-03
18	Introducción a la programación	Libro digital de programación. Se estructura en tres capítulos. Capítulo 1. Estructura lineal. Capítulo 2. Estructuras de selectivas. Capítulo 3. Estructuras de repetitivas y arreglos unidimensionales. Se buscó abordar los contenidos de manera práctica en su totalidad. Se introduce a los estudiantes a la descripción lógica matemática a resolver, primero con la metodología español estructurado con su prueba de escritorio para el razonamiento lógico matemático, donde se incluyen las estructuras algorítmicas, desde las básicas a las complejas. se valida con la prueba de escritorio manual y con el software PseInt con la finalidad de simular la ejecución del funcionamiento del problema abordado paso a paso. Después, se utiliza la programación Java, para reconocer las características propias y diferentes de un lenguaje de programación; básicamente, identificar poco a poco la sintaxis de Java.	programacion	Javier Pino Herrera	/Uploads/documents/file_6808336098ac4_Introducción a la Programación.pdf	\N	/Uploads/img/img_6808336098298_Captura de pantalla 2025-04-22 192040.png	2025-04-22 19:25:04.63258	2020-08-01
19	Fundamentos de Java	El libro presenta los fundamentos, la manera de compilar y ejecutar un programa en Java. Luego analiza cada palabra clave en este lenguaje y concluye con algunas de las características más avanzadas de Java, como la programación con varios subprocesos, las opciones genéricas y los applets.	programacion	Herbert Schildt	/Uploads/documents/file_680834fb2eb3e_java.pdf	\N	/Uploads/img/img_680834fb2e3a3_Captura de pantalla 2025-04-22 192903.png	2025-04-22 19:31:55.195337	2007-01-01
22	Ciberseguridad paso a paso	¿Sabías que el 60 % de las empresas que son atacadas cierra su negocio a los 6 meses? En la nueva era digital, es vital elaborar una adecuada estrategia de ciberseguridad que nos permita protegernos de las amenazas de ciberseguridad y de los nuevos actores de amenazas del ciberespacio.	ciberseguridad	María Ángeles Caballero	/Uploads/documents/file_680ff9a638f74_ciberseguridad-paso-a-paso.pdf	\N	/Uploads/img/img_680ff9a638006_Captura de pantalla 2025-04-28 165328.png	2025-04-28 16:56:54.242255	2023-03-21
\.


--
-- TOC entry 4948 (class 0 OID 16500)
-- Dependencies: 219
-- Data for Name: publicacion; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.publicacion (id, titulo, contenido, fecha, autor, idusuario) FROM stdin;
\.


--
-- TOC entry 4946 (class 0 OID 16483)
-- Dependencies: 217
-- Data for Name: usuarios; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.usuarios (id, nombre, identificacion, edad, correo, ficha, password, rol, reset_token, reset_token_expiration, foto_perfil) FROM stdin;
12	juan	12345678	18	juanpablo@gmail.com	2931558	$2y$10$GqCPWFriEAc/K182UgdNA./WS5Cw/ncAr8yUglfObfvCJpaFEuZRC	1	\N	\N	\N
11	Nelson Arias	1010100	20	josuelkrackfreefire@gmail.com	2931558	$2y$10$.qnwcHeKxF8VFqwQeHWI0uxJJRuK7YVQWzQGUuR5pqH6CBwQyLtZK	2	\N	\N	assets/uploads/user-photo/user_11_1746030340.jpg
14	Yessid Niño	23521515	20	yessidarias133@gmail.com	2931558	$2y$10$B4bEdwzLt8CYSBRXk/0.MOSiURk8nXqxCFRQ0kdqU0WN40kgtehU.	1	\N	\N	assets/uploads/user-photo/user_14_1746040115.jpg
9	Josué Ariza	1101754786	18	josue091206@gmail.com	2931558	$2y$10$1NGr8Ufs.ELiognUrBDpv.2TpzABWFzgK1lMy22OscAWFYkOdQA8.	1	0a841e9d6c1801b03c654ffa728ea961	2025-04-29 17:39:17	\N
13	jose	1101755776	35	jhoset40@gmail.com	2931558	$2y$10$BIfiVZLb53zHzWT00.FmweeFjO8TMY9RRTHc4x856B4Mxh7t81d42	1	f438495bf232cd155bbaf16409366eb1	2025-04-29 17:39:06	\N
\.


--
-- TOC entry 4965 (class 0 OID 0)
-- Dependencies: 226
-- Name: comentario_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.comentario_id_seq', 16, true);


--
-- TOC entry 4966 (class 0 OID 0)
-- Dependencies: 224
-- Name: deseado_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.deseado_id_seq', 22, true);


--
-- TOC entry 4967 (class 0 OID 0)
-- Dependencies: 222
-- Name: documento_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.documento_id_seq', 22, true);


--
-- TOC entry 4968 (class 0 OID 0)
-- Dependencies: 221
-- Name: usuarios_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.usuarios_id_seq', 14, true);


--
-- TOC entry 4789 (class 2606 OID 16614)
-- Name: calificacion calificacion_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.calificacion
    ADD CONSTRAINT calificacion_pkey PRIMARY KEY (usuario_id, documento_id);


--
-- TOC entry 4791 (class 2606 OID 16646)
-- Name: comunidad comentario_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.comunidad
    ADD CONSTRAINT comentario_key PRIMARY KEY (id);


--
-- TOC entry 4785 (class 2606 OID 16518)
-- Name: comentario comentario_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.comentario
    ADD CONSTRAINT comentario_pkey PRIMARY KEY (id);


--
-- TOC entry 4787 (class 2606 OID 16594)
-- Name: deseado deseado_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.deseado
    ADD CONSTRAINT deseado_pkey PRIMARY KEY (id);


--
-- TOC entry 4781 (class 2606 OID 16494)
-- Name: documento documento_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.documento
    ADD CONSTRAINT documento_pkey PRIMARY KEY (id);


--
-- TOC entry 4783 (class 2606 OID 16506)
-- Name: publicacion publicacion_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.publicacion
    ADD CONSTRAINT publicacion_pkey PRIMARY KEY (id);


--
-- TOC entry 4779 (class 2606 OID 16487)
-- Name: usuarios usuarios_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.usuarios
    ADD CONSTRAINT usuarios_pkey PRIMARY KEY (id);


--
-- TOC entry 4798 (class 2606 OID 16620)
-- Name: calificacion calificacion_documento_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.calificacion
    ADD CONSTRAINT calificacion_documento_id_fkey FOREIGN KEY (documento_id) REFERENCES public.documento(id) ON DELETE CASCADE;


--
-- TOC entry 4799 (class 2606 OID 16615)
-- Name: calificacion calificacion_usuario_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.calificacion
    ADD CONSTRAINT calificacion_usuario_id_fkey FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id) ON DELETE CASCADE;


--
-- TOC entry 4796 (class 2606 OID 16604)
-- Name: deseado fk_documento; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.deseado
    ADD CONSTRAINT fk_documento FOREIGN KEY (documento_id) REFERENCES public.documento(id) ON DELETE CASCADE;


--
-- TOC entry 4794 (class 2606 OID 16626)
-- Name: comentario fk_documento_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.comentario
    ADD CONSTRAINT fk_documento_id FOREIGN KEY (documento_id) REFERENCES public.documento(id) ON DELETE CASCADE;


--
-- TOC entry 4792 (class 2606 OID 16572)
-- Name: documento fk_idusuario; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.documento
    ADD CONSTRAINT fk_idusuario FOREIGN KEY (idusuario) REFERENCES public.usuarios(id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- TOC entry 4793 (class 2606 OID 16507)
-- Name: publicacion fk_usuario_publi; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.publicacion
    ADD CONSTRAINT fk_usuario_publi FOREIGN KEY (idusuario) REFERENCES public.usuarios(id);


--
-- TOC entry 4797 (class 2606 OID 16599)
-- Name: deseado fk_usuarios; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.deseado
    ADD CONSTRAINT fk_usuarios FOREIGN KEY (usuario_id) REFERENCES public.usuarios(id) ON DELETE CASCADE;


--
-- TOC entry 4795 (class 2606 OID 16524)
-- Name: comentario usuario; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.comentario
    ADD CONSTRAINT usuario FOREIGN KEY (idusuario) REFERENCES public.usuarios(id);


--
-- TOC entry 4800 (class 2606 OID 16647)
-- Name: comunidad usuario; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.comunidad
    ADD CONSTRAINT usuario FOREIGN KEY (idusuario) REFERENCES public.usuarios(id);


-- Completed on 2025-05-01 09:54:01

--
-- PostgreSQL database dump complete
--

