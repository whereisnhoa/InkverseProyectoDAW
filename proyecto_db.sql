-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 28-05-2025 a las 22:38:18
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `proyecto_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `capitulos_comic`
--

CREATE TABLE `capitulos_comic` (
  `id` int(11) NOT NULL,
  `comic_id` int(11) NOT NULL,
  `numero` int(11) NOT NULL,
  `titulo` varchar(255) DEFAULT NULL,
  `archivo` text DEFAULT NULL,
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `icono` varchar(50) DEFAULT NULL,
  `color` varchar(7) DEFAULT NULL,
  `tipo` enum('libro','comic') NOT NULL DEFAULT 'libro'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`, `descripcion`, `icono`, `color`, `tipo`) VALUES
(213, 'Poesía', 'Versos, sonetos y expresiones líricas del alma', NULL, NULL, 'libro'),
(214, 'Fantasía', 'Mundos mágicos, criaturas fantásticas y aventuras épicas', NULL, NULL, 'libro'),
(215, 'Ciencia Ficción', 'Futuros posibles, tecnología avanzada y exploración espacial', NULL, NULL, 'libro'),
(216, 'LGBT+', 'Narrativas que exploran la diversidad sexual y de género', NULL, NULL, 'libro'),
(217, 'Misterio', 'Enigmas por resolver, crímenes y secretos ocultos', NULL, NULL, 'libro'),
(218, 'Suspense', 'Tensión constante, giros inesperados y finales sorprendentes', NULL, NULL, 'libro'),
(219, 'Drama', 'Conflictos humanos profundos y emociones intensas', NULL, NULL, 'libro'),
(220, 'Novela', 'Narrativa extensa con desarrollo complejo de personajes', NULL, NULL, 'libro'),
(221, 'Ensayo', 'Reflexiones y análisis sobre diversos temas', NULL, NULL, 'libro'),
(222, 'Histórica', 'Ambientada en épocas pasadas con rigor histórico', NULL, NULL, 'libro'),
(223, 'Biografía', 'Relatos de vidas reales y personajes históricos', NULL, NULL, 'libro'),
(224, 'Autoayuda', 'Guías para el crecimiento personal y profesional', NULL, NULL, 'libro'),
(225, 'Aventura', 'Expediciones, viajes y hazañas emocionantes', NULL, NULL, 'libro'),
(226, 'Distopía', 'Sociedades futuras opresivas y mundos post-apocalípticos', NULL, NULL, 'libro'),
(227, 'Realismo Mágico', 'Elementos fantásticos en contextos realistas', NULL, NULL, 'libro'),
(228, 'Thriller', 'Acción intensa, persecuciones y peligro constante', NULL, NULL, 'libro'),
(229, 'Humor', 'Comedia, sátira y entretenimiento ligero', NULL, NULL, 'libro'),
(230, 'Cómic', 'Historietas visuales tradicionales', NULL, NULL, 'comic'),
(231, 'Manga', 'Cómics japoneses', NULL, NULL, 'comic'),
(232, 'Manhwa', 'Cómics coreanos', NULL, NULL, 'comic'),
(233, 'Manhua', 'Cómics chinos', NULL, NULL, 'comic'),
(234, 'Shonen', 'Dirigido a jóvenes masculinos, acción y aventura', NULL, NULL, 'comic'),
(235, 'Shojo', 'Dirigido a jóvenes femeninas, romance y drama', NULL, NULL, 'comic'),
(236, 'Seinen', 'Para adultos masculinos, temas maduros y complejos', NULL, NULL, 'comic'),
(237, 'Josei', 'Para mujeres adultas, romance realista y drama', NULL, NULL, 'comic'),
(238, 'Romance', 'Historias de amor y relaciones románticas', NULL, NULL, 'comic'),
(239, 'Yuri', 'Romance entre mujeres, amor lésbico', NULL, NULL, 'comic'),
(240, 'Yaoi', 'Romance entre hombres, amor gay', NULL, NULL, 'comic'),
(241, 'Terror', 'Horror, miedo y elementos sobrenaturales', NULL, NULL, 'comic'),
(242, 'Gore', 'Violencia gráfica y contenido sangriento', NULL, NULL, 'comic'),
(243, 'Futurista', 'Ambientado en el futuro, ciencia ficción', NULL, NULL, 'comic'),
(244, 'Magia', 'Elementos mágicos, hechicería y fantasía', NULL, NULL, 'comic'),
(245, 'Sobrenatural', 'Fenómenos paranormales y criaturas místicas', NULL, NULL, 'comic'),
(246, 'Samurai', 'Guerreros japoneses, honor y combate', NULL, NULL, 'comic'),
(247, 'Niños', 'Contenido apropiado para audiencia infantil', NULL, NULL, 'comic'),
(248, 'Música', 'Temática musical, bandas y artistas', NULL, NULL, 'comic'),
(249, 'Superhéroes', 'Héroes con poderes especiales salvando el mundo', NULL, NULL, 'comic'),
(250, 'Slice of Life', 'Vida cotidiana, situaciones realistas', NULL, NULL, 'comic'),
(251, 'Mecha', 'Robots gigantes y tecnología avanzada', NULL, NULL, 'comic'),
(252, 'Deportes', 'Competencias deportivas y superación personal', NULL, NULL, 'comic'),
(253, 'Escolar', 'Ambientado en escuelas e institutos', NULL, NULL, 'comic'),
(254, 'Comedia', 'Humor, situaciones divertidas y entretenimiento', NULL, NULL, 'comic'),
(255, 'Acción', 'Peleas, combates y secuencias dinámicas', NULL, NULL, 'comic'),
(256, 'Psicológico', 'Exploración de la mente humana y emociones', NULL, NULL, 'comic'),
(257, 'Histórico', 'Ambientado en épocas históricas específicas', NULL, NULL, 'comic');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias_arte`
--

CREATE TABLE `categorias_arte` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `icono` varchar(50) DEFAULT 'fa-image'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias_arte`
--

INSERT INTO `categorias_arte` (`id`, `nombre`, `descripcion`, `icono`) VALUES
(1, 'Portadas', 'Diseños de portadas para libros y cómics', 'fa-book-open'),
(2, 'Personajes', 'Ilustraciones de personajes de obras literarias', 'fa-user'),
(3, 'Escenarios', 'Paisajes y escenarios de mundos literarios', 'fa-map'),
(4, 'Fan Art', 'Arte inspirado en obras populares', 'fa-paint-brush'),
(5, 'Conceptual', 'Arte conceptual y bocetos', 'fa-lightbulb');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias_foro`
--

CREATE TABLE `categorias_foro` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `icono` varchar(50) DEFAULT 'fa-comments',
  `orden` int(11) DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias_foro`
--

INSERT INTO `categorias_foro` (`id`, `nombre`, `descripcion`, `icono`, `orden`, `fecha_creacion`) VALUES
(1, 'Discusiones Literarias', 'Debate sobre obras clásicas y contemporáneas, análisis de personajes y tramas.', 'fa-book', 1, '2025-05-15 09:57:39'),
(2, 'Escritores y Creadores', 'Comparte tus experiencias como escritor, consejos de escritura y recursos.', 'fa-pencil-alt', 2, '2025-05-15 09:57:39'),
(3, 'Arte y Diseño', 'Discusiones sobre ilustración, diseño de portadas y arte conceptual.', 'fa-palette', 3, '2025-05-15 09:57:39'),
(4, 'Recomendaciones', 'Recomienda y descubre nuevas obras literarias y cómics.', 'fa-thumbs-up', 4, '2025-05-15 09:57:39'),
(5, 'Ayuda y Soporte', 'Preguntas y respuestas sobre el uso de la plataforma.', 'fa-question-circle', 5, '2025-05-15 09:57:39');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comentarios`
--

CREATE TABLE `comentarios` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `contenido` text NOT NULL,
  `tipo_contenido` enum('libro','comic') NOT NULL,
  `contenido_id` int(11) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comentarios_arte`
--

CREATE TABLE `comentarios_arte` (
  `id` int(11) NOT NULL,
  `arte_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `comentario` text NOT NULL,
  `fecha_comentario` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comics`
--

CREATE TABLE `comics` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `autor` varchar(100) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `portada` varchar(255) DEFAULT NULL,
  `publico_adulto` tinyint(1) DEFAULT 0,
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp(),
  `categoria_id` int(11) DEFAULT NULL,
  `destacado` tinyint(1) DEFAULT 0,
  `fecha_destacado` timestamp NULL DEFAULT NULL,
  `visitas` int(11) DEFAULT 0,
  `favoritos` int(11) DEFAULT 0,
  `valoracion` decimal(3,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `favoritos`
--

CREATE TABLE `favoritos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tipo_contenido` enum('libro','comic') NOT NULL,
  `contenido_id` int(11) NOT NULL,
  `fecha_guardado` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `galeria_arte`
--

CREATE TABLE `galeria_arte` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `imagen` varchar(255) NOT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `me_gusta` int(11) DEFAULT 0,
  `publico_adulto` tinyint(1) DEFAULT 0,
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `libros`
--

CREATE TABLE `libros` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `autor` varchar(100) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `portada` varchar(255) DEFAULT NULL,
  `archivo` text DEFAULT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `publico_adulto` tinyint(1) DEFAULT 0,
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp(),
  `destacado` tinyint(1) DEFAULT 0,
  `fecha_destacado` timestamp NULL DEFAULT NULL,
  `visitas` int(11) DEFAULT 0,
  `favoritos` int(11) DEFAULT 0,
  `valoracion` decimal(3,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensajes`
--

CREATE TABLE `mensajes` (
  `id` int(11) NOT NULL,
  `remitente_id` int(11) NOT NULL,
  `destinatario_id` int(11) NOT NULL,
  `asunto` varchar(255) NOT NULL,
  `contenido` text NOT NULL,
  `leido` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_envio` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `me_gusta_arte`
--

CREATE TABLE `me_gusta_arte` (
  `id` int(11) NOT NULL,
  `arte_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `contenido_id` int(11) DEFAULT NULL,
  `tipo_contenido` varchar(50) DEFAULT NULL,
  `mensaje` text NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `leida` tinyint(1) DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `obras`
--

CREATE TABLE `obras` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `contenido` longtext DEFAULT NULL,
  `autor_id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `portada` varchar(255) DEFAULT NULL,
  `estado` enum('borrador','publicada','pausada','completada') DEFAULT 'borrador',
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `visitas` int(11) DEFAULT 0,
  `activa` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `respuestas_foro`
--

CREATE TABLE `respuestas_foro` (
  `id` int(11) NOT NULL,
  `tema_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `contenido` text NOT NULL,
  `es_solucion` tinyint(1) DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_edicion` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguidores`
--

CREATE TABLE `seguidores` (
  `id` int(11) NOT NULL,
  `seguidor_id` int(11) NOT NULL,
  `seguido_id` int(11) NOT NULL,
  `fecha_seguimiento` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `temas_foro`
--

CREATE TABLE `temas_foro` (
  `id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `contenido` text NOT NULL,
  `vistas` int(11) DEFAULT 0,
  `respuestas` int(11) DEFAULT 0,
  `estado` enum('abierto','cerrado','destacado') DEFAULT 'abierto',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultima_actividad` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `password` varchar(255) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `avatar` varchar(255) DEFAULT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `biografia` text DEFAULT NULL,
  `sitio_web` varchar(255) DEFAULT NULL,
  `ubicacion` varchar(100) DEFAULT NULL,
  `privacidad` enum('publico','privado') DEFAULT 'publico',
  `fecha_actualizacion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `usuario`, `email`, `fecha_nacimiento`, `password`, `fecha_registro`, `avatar`, `nombre`, `biografia`, `sitio_web`, `ubicacion`, `privacidad`, `fecha_actualizacion`) VALUES
(1, 'whereisnhoa', 'ainhoalr1203@gmail.com', '2003-12-11', '$2y$10$5gHha3xkeCQp13Od0HFVwOPBIIIUYWu7Wg78I.8LHQnehYstVV/nq', '2025-05-23 12:01:52', NULL, 'Ainhoa', ':b', '', '', 'publico', '2025-05-28 22:33:37');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `capitulos_comic`
--
ALTER TABLE `capitulos_comic`
  ADD PRIMARY KEY (`id`),
  ADD KEY `comic_id` (`comic_id`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`),
  ADD KEY `idx_categorias_tipo` (`tipo`);

--
-- Indices de la tabla `categorias_arte`
--
ALTER TABLE `categorias_arte`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `categorias_foro`
--
ALTER TABLE `categorias_foro`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `comentarios`
--
ALTER TABLE `comentarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `comentarios_arte`
--
ALTER TABLE `comentarios_arte`
  ADD PRIMARY KEY (`id`),
  ADD KEY `arte_id` (`arte_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `comics`
--
ALTER TABLE `comics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_comics_categoria` (`categoria_id`);

--
-- Indices de la tabla `favoritos`
--
ALTER TABLE `favoritos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_id` (`usuario_id`,`tipo_contenido`,`contenido_id`);

--
-- Indices de la tabla `galeria_arte`
--
ALTER TABLE `galeria_arte`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Indices de la tabla `libros`
--
ALTER TABLE `libros`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categoria_id` (`categoria_id`),
  ADD KEY `idx_libros_categoria` (`categoria_id`);

--
-- Indices de la tabla `mensajes`
--
ALTER TABLE `mensajes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `remitente_id` (`remitente_id`),
  ADD KEY `destinatario_id` (`destinatario_id`);

--
-- Indices de la tabla `me_gusta_arte`
--
ALTER TABLE `me_gusta_arte`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `arte_usuario` (`arte_id`,`usuario_id`),
  ADD KEY `arte_id` (`arte_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `obras`
--
ALTER TABLE `obras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `autor_id` (`autor_id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Indices de la tabla `respuestas_foro`
--
ALTER TABLE `respuestas_foro`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tema_id` (`tema_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `seguidores`
--
ALTER TABLE `seguidores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `seguidor_id` (`seguidor_id`,`seguido_id`),
  ADD KEY `seguido_id` (`seguido_id`);

--
-- Indices de la tabla `temas_foro`
--
ALTER TABLE `temas_foro`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categoria_id` (`categoria_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `capitulos_comic`
--
ALTER TABLE `capitulos_comic`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=258;

--
-- AUTO_INCREMENT de la tabla `categorias_arte`
--
ALTER TABLE `categorias_arte`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `categorias_foro`
--
ALTER TABLE `categorias_foro`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `comentarios`
--
ALTER TABLE `comentarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `comentarios_arte`
--
ALTER TABLE `comentarios_arte`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `comics`
--
ALTER TABLE `comics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `favoritos`
--
ALTER TABLE `favoritos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `galeria_arte`
--
ALTER TABLE `galeria_arte`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `libros`
--
ALTER TABLE `libros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `mensajes`
--
ALTER TABLE `mensajes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `me_gusta_arte`
--
ALTER TABLE `me_gusta_arte`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `obras`
--
ALTER TABLE `obras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `respuestas_foro`
--
ALTER TABLE `respuestas_foro`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `seguidores`
--
ALTER TABLE `seguidores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `temas_foro`
--
ALTER TABLE `temas_foro`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `capitulos_comic`
--
ALTER TABLE `capitulos_comic`
  ADD CONSTRAINT `capitulos_comic_ibfk_1` FOREIGN KEY (`comic_id`) REFERENCES `comics` (`id`);

--
-- Filtros para la tabla `comentarios`
--
ALTER TABLE `comentarios`
  ADD CONSTRAINT `comentarios_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `comentarios_arte`
--
ALTER TABLE `comentarios_arte`
  ADD CONSTRAINT `fk_comentarios_arte` FOREIGN KEY (`arte_id`) REFERENCES `galeria_arte` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_comentarios_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `favoritos`
--
ALTER TABLE `favoritos`
  ADD CONSTRAINT `favoritos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `galeria_arte`
--
ALTER TABLE `galeria_arte`
  ADD CONSTRAINT `fk_arte_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias_arte` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_arte_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `libros`
--
ALTER TABLE `libros`
  ADD CONSTRAINT `libros_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`);

--
-- Filtros para la tabla `mensajes`
--
ALTER TABLE `mensajes`
  ADD CONSTRAINT `mensajes_ibfk_1` FOREIGN KEY (`remitente_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mensajes_ibfk_2` FOREIGN KEY (`destinatario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `me_gusta_arte`
--
ALTER TABLE `me_gusta_arte`
  ADD CONSTRAINT `fk_megusta_arte` FOREIGN KEY (`arte_id`) REFERENCES `galeria_arte` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_megusta_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `fk_notificaciones_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `obras`
--
ALTER TABLE `obras`
  ADD CONSTRAINT `obras_ibfk_1` FOREIGN KEY (`autor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `obras_ibfk_2` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`);

--
-- Filtros para la tabla `respuestas_foro`
--
ALTER TABLE `respuestas_foro`
  ADD CONSTRAINT `fk_respuestas_tema` FOREIGN KEY (`tema_id`) REFERENCES `temas_foro` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_respuestas_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `seguidores`
--
ALTER TABLE `seguidores`
  ADD CONSTRAINT `seguidores_ibfk_1` FOREIGN KEY (`seguidor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `seguidores_ibfk_2` FOREIGN KEY (`seguido_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `temas_foro`
--
ALTER TABLE `temas_foro`
  ADD CONSTRAINT `fk_temas_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias_foro` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_temas_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
