-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 03-06-2025 a las 01:04:19
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
-- Base de datos: `db_proyecto`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `biblioteca`
--

CREATE TABLE `biblioteca` (
  `usuario_id` int(11) NOT NULL,
  `obra_id` int(11) NOT NULL,
  `estado` varchar(20) NOT NULL,
  `tipo` varchar(12) NOT NULL DEFAULT 'libro'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `biblioteca`
--

INSERT INTO `biblioteca` (`usuario_id`, `obra_id`, `estado`, `tipo`) VALUES
(1, 5, 'leyendo', 'libro'),
(1, 1, 'planeado', 'libro');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `capitulos_comic`
--

CREATE TABLE `capitulos_comic` (
  `id` int(11) NOT NULL,
  `comic_id` int(11) NOT NULL,
  `numero` int(11) NOT NULL,
  `titulo` varchar(255) DEFAULT NULL,
  `contenido` text DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `fecha_publicacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `capitulos_libro`
--

CREATE TABLE `capitulos_libro` (
  `id` int(11) NOT NULL,
  `libro_id` int(11) NOT NULL,
  `numero` int(11) NOT NULL,
  `titulo` varchar(255) DEFAULT NULL,
  `contenido` text DEFAULT NULL,
  `fecha_publicacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `capitulos_libro`
--

INSERT INTO `capitulos_libro` (`id`, `libro_id`, `numero`, `titulo`, `contenido`, `fecha_publicacion`) VALUES
(1, 1, 1, 'Capítulo 1', 'Capítulo 1 — La maldición de la mirada\r\n\r\nEn los antiguos tiempos, cuando los dioses aún caminaban entre los hombres y la tierra susurraba mitos entre las montañas, vivía en Atenas una joven sacerdotisa llamada Medusa. Dedicada al culto de Atenea, diosa de la sabiduría y la guerra justa, Medusa era conocida en toda Grecia no solo por su devoción, sino por una belleza que parecía obra de los mismos dioses.\r\n\r\nSus cabellos caían como hilos de oro líquido, reflejando la luz del sol naciente, y su rostro, sereno y puro, parecía esculpido en mármol por el mismísimo Fidias. Su mirada era clara, inocente, poderosa, y muchos decían que al observarla, uno sentía la necesidad de protegerla... o de poseerla.\r\n\r\nAquel rostro, sin embargo, estaba reservado a los dioses, pues Medusa había hecho voto de castidad al consagrarse al templo. Día tras día cuidaba los altares de Atenea, limpiaba estatuas, preparaba ofrendas, y se mantenía alejada de los placeres del mundo terrenal. Pero la fama de su hermosura llegó a oídos de uno de los más volátiles del Olimpo: Poseidón, señor de los mares.\r\n\r\nPoseidón no era un dios paciente. Al verla, no quiso cortejarla, ni hablarle; la deseó. Y como muchos dioses de su linaje, confundió deseo con derecho. Una noche de luna alta, mientras Medusa oraba sola en el templo, el rugido del mar resonó en las paredes de piedra. Entre sombras y salitre, Poseidón apareció en forma humana, imponente, y con la mirada encendida.\r\n\r\nElla gritó. Nadie la oyó. Allí, en el lugar sagrado que prometía refugio, fue forzada por el dios. Los ídolos de Atenea fueron testigos mudos de aquella tragedia, y el incienso ardiente no pudo ocultar el olor a furia y miedo.\r\n\r\nCuando Medusa, rota y temblorosa, imploró clemencia a la diosa que había servido con lealtad, Atenea descendió entre llamas y viento. Pero en lugar de consolarla o castigar al agresor, desvió su mirada con desdén. La pureza del templo había sido mancillada, y en los ojos de una diosa, la responsabilidad recaía en la mujer que lo permitió, aunque no por voluntad propia.\r\n\r\nComo castigo, Atenea maldijo a Medusa. Su dorado cabello se convirtió en un nido de serpientes vivas, cada una siseando con la furia de la injusticia. Su piel se volvió más pálida que la muerte, y sus ojos, otrora dulces y cálidos, adquirieron el terrible poder de convertir en piedra a quien los enfrentara.\r\n\r\n“Así,” dijo Atenea con voz implacable, “como los hombres han osado verte con deseo, ahora les devuelves la mirada con muerte.”\r\n\r\nDesterrada, deformada y temida, Medusa huyó al exilio. Se refugió en las ruinas olvidadas más allá del mundo civilizado, donde nadie osaba entrar. Los héroes hablaban de ella como un monstruo, los poetas como una leyenda, pero dentro de su corazón aún latía una mujer, rota por los dioses y traicionada por aquello que juró proteger.\r\n\r\nAllí nació la criatura temida, pero también la figura trágica: Medusa, la mujer cuya belleza fue castigada, cuya historia fue distorsionada, y cuyo rostro fue condenado a vivir en el mármol del olvido.', '2025-06-02 12:55:53'),
(2, 2, 1, 'Capítulo 1', 'Capítulo 1\r\n\r\nEn las llanuras de Lidia, una joven llamada Aracne tejía. No solo tejía; sus manos eran una extensión de la gracia divina, capaces de transformar hilos simples en tapices de una belleza que desafiaba la imaginación. Las ninfas dejaban sus arroyos y los sátiros sus bosques para contemplar su arte, y murmuraban que incluso Atenea, la diosa de la sabiduría y la artesanía, no podría superarla.\r\n\r\nAracne, joven y con la confianza que da el talento inigualable, escuchaba esos murmullos con una sonrisa. No era una diosa, ni una ninfa, solo una humilde mortal. Pero su arte era puro, forjado con paciencia y una habilidad innata. No se jactaba de su linaje, solo de sus manos. Sin embargo, la fama a menudo trae consigo la soberbia, y la soberbia, la desgracia.\r\n\r\nUn día, los susurros de la gente llegaron a oídos de la misma Atenea. La diosa, patrona de las artes y protectora de los tejedores, se sintió ofendida por la audacia de una mortal que osaba compararse con ella. Decidió visitar a Aracne, disfrazada como una anciana de cabellos grises y rostro surcado por el tiempo.\r\n\r\n\"Joven\", dijo Atenea con voz suave, \"acepta el consejo de una anciana: no desafíes a los dioses. Pide perdón a Atenea, y quizás ella te perdone tu insolencia.\"\r\n\r\nAracne, sin reconocer a la diosa disfrazada, soltó una carcajada. \"¡Guárdese sus consejos, vieja! Mis manos hablan por sí solas. Que venga Atenea si se atreve. Si no puede igualar mi arte, que no se presente.\"\r\n\r\nLa ira encendió los ojos de Atenea, y la anciana se transformó en la gloriosa diosa, su égida resplandeciente y su lanza brillando. Los espectadores temblaron y se arrodillaron, pero Aracne se mantuvo firme, aunque su corazón latía con un miedo repentino. La soberbia aún la aferraba, y su orgullo no le permitía retroceder.\r\n\r\n\"Demostraré, mortal insolente, que no hay igual para el arte de una diosa\", declaró Atenea con voz que resonó en el aire. \"Que empiece el concurso.\"\r\n\r\nY así, con el destino de Aracne pendiente de cada hebra, comenzó el desafío. Atenea tejió la majestuosidad de los dioses del Olimpo, mostrando su poder y su gloria, advirtiendo a los mortales sobre su ira. Su tapiz era una obra maestra de divinidad y autoridad.\r\n\r\nAracne, por su parte, tejió la historia de los dioses, sí, pero no su gloria. Tejió sus engaños, sus pasiones secretas, sus errores y sus castigos a los mortales. Su tapiz era una verdad cruda y hermosa, ejecutada con una perfección que Atenea no pudo negar. Las figuras eran tan vivas, el movimiento tan real, que el tapiz parecía respirar. Era, a su manera, tan impecable y hermoso como el de la diosa, si no más.', '2025-06-02 14:32:13'),
(3, 3, 1, 'Capítulo 1', 'Capítulo 1: El eco del asfalto\r\n\r\nLa sirena aullaba una nota monocorde que se perdía en el vientre gris de la ciudad. El detective Marco Ruiz apenas la escuchaba. Su mirada, curtida por años de asfalto y miseria, estaba clavada en el charco de sangre que se expandía perezosamente sobre el hormigón mojado. La lluvia, fina y persistente, había intentado lavar el rastro, pero solo había conseguido extender la mancha, como un tatuaje indeleble en el alma de la urbe.\r\n\r\nEl cuerpo, un amasijo desfigurado de lo que alguna vez fue un hombre, yacía bajo la mortecina luz de una farola parpadeante. Un empresario local, de esos que construyen imperios sobre promesas vacías y apretones de manos sospechosos. Nada sorprendente. La sorpresa, para Marco, residía en la precisión del golpe. No había sido un robo al uso, no un ajuste de cuentas chapucero. Esto era diferente. Un mensaje.\r\n\r\nMarco se agachó, el frío calándole los huesos a pesar de su gabardina empapada. Observó los detalles, esos pequeños fragmentos de la realidad que la mayoría ignora. La mano derecha de la víctima estaba abierta, la palma hacia arriba, y sobre ella, una pequeña y pulcra orquídea negra. Un detalle anacrónico, casi poético, en medio de tanta brutalidad.\r\n\r\n\"¿Qué tenemos, Ruiz?\", la voz rasposa del sargento Torres, un veterano con más millas que el metro, rompió el silencio. Fumaba un cigarrillo, el humo serpenteando hacia el cielo plomizo.\r\n\r\n\"Un asesinato, Torres. Un mensaje. Y una flor que no encaja\". Marco se puso de pie, su mirada recorriendo el callejón, buscando algo, cualquier cosa que la lluvia no se hubiera llevado. \"Nadie se molesta en dejar flores a sus víctimas, a menos que quiera decir algo. Y esta orquídea... es extraña\".\r\n\r\nTorres se encogió de hombros, dejando caer la ceniza. \"Los hay que tienen gustos peculiares. Ya sabes cómo es la gente de pasta\".\r\n\r\nPero Marco sabía que no era tan simple. La orquídea negra era una flor rara, exótica, casi mística. Su presencia en ese escenario de crimen gritaba una intencionalidad que iba más allá de la simple excentricidad. Era una firma.\r\n\r\nMientras los forenses comenzaban su metódico trabajo, Marco sintió el peso de la noche. La ciudad se extendía ante él, una maraña de luces y sombras, cada una guardando sus propios secretos. Sabía que este caso no sería un paseo. La orquídea negra era solo la primera hebra de una madeja mucho más grande, más oscura, y estaba seguro de que el eco de este asesinato resonaría mucho más allá de este callejón, hasta los cimientos de la propia ciudad. Y él, Marco Ruiz, sería el encargado de escuchar y descifrar cada nota de esa sinfonía macabra.', '2025-06-02 15:13:55'),
(4, 4, 1, 'Capítulo 1', 'Capítulo 1: El Bosque Susurrante\r\n\r\nEl sol se filtraba a través de las hojas, pintando el bosque con pinceladas doradas. Un niño, de no más de diez años, con el cabello alborotado y ojos brillantes de curiosidad, se adentraba en el Bosque Susurrante. Su nombre era Alex, y una pequeña mochila con diseños de estrellas colgaba de su espalda. El bosque, con sus árboles de hojas esmeralda y zafiro, siempre lo había llamado, pero hoy, un susurro diferente, casi mágico, lo había atraído más adentro que nunca.\r\n\r\nCriaturas fantásticas se asomaban entre los árboles: un conejo blanco con orejas largas y brillantes lo observaba con curiosidad, y pequeñas hadas con alas translúcidas revoloteaban a su alrededor. Alex sentía una emoción extraña, una mezcla de miedo y asombro, pero su corazón latía con la promesa de una gran aventura.\r\n\r\nEn la distancia, entre las montañas, un imponente castillo de piedra se alzaba, con torres altas que parecían tocar las nubes. Era el Castillo de la Luna, un lugar envuelto en leyendas y misterios. Alex sintió un impulso irresistible de ir hacia él, como si una fuerza invisible lo llamara.', '2025-06-02 16:04:57'),
(5, 5, 1, 'Capítulo 1', 'El Secreto de las Estrellas Fugaces\r\nCapítulo 1: Arena entre los Dedos\r\nEl sol de verano en Bahía Escondida se aferraba a la tarde con una obstinación dorada. Maya, con el cabello castaño revuelto por la brisa salada, enterraba los dedos en la arena tibia, observando cómo las olas desdibujaban sus huellas. A su lado, Sofía, su mejor amiga desde que recordaba, reía mientras intentaba mantener en pie un castillo de arena improvisado. Su risa era como campanillas de viento, ligera y melodiosa, y Maya sintió esa familiar punzada de calidez en el pecho.\r\n\r\n\"Se va a caer, Sof\", advirtió Maya, una sonrisa asomando en sus labios.\r\n\r\nSofía, con el pelo oscuro recogido en una coleta alta que desafiaba la gravedad, resopló. \"¡No si lo refuerzo con esta fortaleza inexpugnable de conchas!\" Dijo, colocando una concha de vieira como si fuera una piedra angular. El siguiente golpe de una ola desmoronó el castillo, arrastrando las \"murallas\" al mar.\r\n\r\nSofía suspiró, pero luego se echó a reír con más ganas, un sonido contagioso que hizo que Maya también sonriera de verdad. Se quedaron en silencio por un momento, el sonido del mar llenando el espacio entre ellas. Era un silencio cómodo, ese tipo de silencio que solo compartes con alguien que te conoce mejor que tú mismo.\r\n\r\n\"¿Estrellas esta noche?\", preguntó Sofía, rompiendo la quietud. No era una pregunta, era una tradición. Cada noche de verano despejada, desde que eran lo suficientemente mayores como para quedarse despiertas hasta tarde, iban a la cala secreta a observar el cielo.\r\n\r\nMaya asintió, su mirada fija en el horizonte donde el sol comenzaba su descenso, tiñendo el cielo de naranjas y rosas. \"Por supuesto. Dicen que hay una lluvia de líridas esta semana. Podríamos ver muchas\".\r\n\r\nSofía se apoyó en el hombro de Maya, sus brazos rozándose. Un pequeño escalofrío recorrió a Maya, una sensación que no tenía nada que ver con la brisa. Miró a Sofía, que tenía los ojos cerrados, disfrutando del calor del sol menguante. La piel de Sofía, siempre un poco más bronceada que la suya, brillaba. Había una peca justo debajo de su ojo izquierdo que Maya siempre había encontrado curiosamente encantadora.\r\n\r\n\"Espero que no tengamos que pedir los mismos deseos de siempre\", murmuró Sofía, sin abrir los ojos.\r\n\r\n\"¿Y cuáles son esos?\", preguntó Maya, aunque lo sabía perfectamente. \"Que el verano nunca termine y que siempre seamos amigas\".\r\n\r\nSofía abrió los ojos y se giró para mirarla, sus ojos oscuros llenos de una chispa juguetona. \"Sí, esos. Pero, ¿y si esta vez tenemos deseos nuevos? ¿Deseos... diferentes?\"\r\n\r\nEl corazón de Maya dio un vuelco. No sabía si era por la forma en que Sofía la miraba, o por el sol que se hundía y teñía sus mejillas de un rubor que no era solo del calor. Lo único que sabía era que el verano en Bahía Escondida, y su amistad con Sofía, estaban a punto de cambiar de una forma que ni las estrellas fugaces podrían predecir.', '2025-06-02 17:02:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`, `descripcion`, `tipo`) VALUES
(1, 'Fantasía', 'Mundos imaginarios, magia y criaturas fantásticas.', 'libro'),
(2, 'Ciencia Ficción', 'Tecnología avanzada, futuros alternativos, viajes espaciales.', 'libro'),
(3, 'Romance', 'Historias de amor y relaciones personales.', 'libro'),
(4, 'Misterio', 'Novelas de detectives, crímenes y suspenso.', 'libro'),
(5, 'Terror', 'Cuentos y novelas para asustar y perturbar.', 'libro'),
(6, 'Aventura', 'Viajes, exploraciones y grandes desafíos.', 'libro'),
(7, 'Histórica', 'Narraciones ambientadas en épocas pasadas.', 'libro'),
(8, 'Juvenil', 'Literatura pensada para jóvenes y adolescentes.', 'libro'),
(9, 'Clásicos', 'Obras reconocidas de la literatura universal.', 'libro'),
(10, 'Poesía', 'Poemas y versos de diferentes estilos.', 'libro'),
(11, 'Drama', 'Historias intensas centradas en conflictos personales.', 'libro'),
(12, 'Humor', 'Libros para reír y pasar un buen rato.', 'libro'),
(13, 'Mitología', 'Mitos, leyendas y tradiciones de diferentes culturas.', 'libro'),
(14, 'LGTB+', 'Literatura con protagonistas, temáticas o autores LGTB+.', 'libro'),
(15, 'Biografía y Memorias', 'Relatos de vidas reales y experiencias personales.', 'libro'),
(16, 'Ensayo', 'Textos argumentativos y reflexivos sobre temas diversos.', 'libro'),
(17, 'Infantil', 'Libros dirigidos a niños y niñas.', 'libro'),
(18, 'Realismo Mágico', 'Historias realistas con elementos mágicos o fantásticos.', 'libro'),
(19, 'Cuento', 'Narraciones breves de ficción.', 'libro'),
(20, 'Policíaca', 'Historias de crímenes y detectives.', 'libro'),
(21, 'Cómic', 'Historietas occidentales de todo tipo.', 'comic'),
(22, 'Manga', 'Cómics japoneses', 'comic'),
(23, 'Manhwa', 'Cómics coreanos, usualmente a color.', 'comic'),
(24, 'Manhua', 'Cómics chinos', 'comic'),
(25, 'Shonen', 'Manga dirigido a chicos adolescentes (acción, aventuras).', 'comic'),
(26, 'Shojo', 'Manga dirigido a chicas adolescentes (amor, amistad).', 'comic'),
(27, 'Seinen', 'Manga para adultos jóvenes, temas más complejos.', 'comic'),
(28, 'Josei', 'Manga para mujeres adultas, historias maduras.', 'comic'),
(29, 'Fantasía', 'Cómics y mangas de mundos mágicos y fantásticos.', 'comic'),
(30, 'Ciencia Ficción', 'Tecnología, espacio y futuros alternativos en viñetas.', 'comic'),
(31, 'Romance', 'Relatos de amor y relaciones en formato gráfico.', 'comic'),
(32, 'Aventura', 'Viajes, exploraciones y acción en cómics/manga.', 'comic'),
(33, 'Comedia', 'Historias llenas de humor y situaciones divertidas.', 'comic'),
(34, 'Terror', 'Cómics y mangas de horror y suspenso.', 'comic'),
(35, 'Deportivo', 'Historias centradas en deportes y superación.', 'comic'),
(36, 'Histórico', 'Cómics/mangas ambientados en otras épocas.', 'comic'),
(37, 'Slice of Life', 'Relatos cotidianos y realistas.', 'comic'),
(38, 'Mecha', 'Robots gigantes y batallas tecnológicas.', 'comic'),
(39, 'Magical Girls', 'Chicas mágicas y poderes especiales.', 'comic'),
(40, 'Yuri', 'Historias de romance entre chicas (Girls’ Love).', 'comic'),
(41, 'Yaoi', 'Historias de romance entre chicos (Boys’ Love).', 'comic'),
(42, 'LGTB+', 'Cómics y mangas con protagonistas o temáticas LGTB+.', 'comic'),
(43, 'Gore', 'Historias con contenido gráfico, sangriento o violento.', 'comic'),
(44, 'Psicológico', 'Tramas profundas, intriga mental y conflictos internos.', 'comic'),
(45, 'Escolar', 'Historias ambientadas en escuelas e institutos.', 'comic');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias_arte`
--

CREATE TABLE `categorias_arte` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias_foro`
--

CREATE TABLE `categorias_foro` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comentarios`
--

CREATE TABLE `comentarios` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tipo_contenido` varchar(20) NOT NULL,
  `contenido_id` int(11) NOT NULL,
  `comentario` text NOT NULL,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comentarios_arte`
--

CREATE TABLE `comentarios_arte` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `arte_id` int(11) NOT NULL,
  `comentario` text NOT NULL,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comics`
--

CREATE TABLE `comics` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `portada` varchar(255) DEFAULT NULL,
  `fecha_subida` datetime DEFAULT current_timestamp(),
  `publico_adulto` tinyint(1) DEFAULT 0,
  `visitas` int(11) DEFAULT 0,
  `destacado` tinyint(1) DEFAULT 0,
  `fecha_destacado` datetime DEFAULT NULL,
  `valoracion` float DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comic_categorias`
--

CREATE TABLE `comic_categorias` (
  `comic_id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `favoritos`
--

CREATE TABLE `favoritos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tipo_contenido` varchar(20) NOT NULL,
  `contenido_id` int(11) NOT NULL,
  `fecha_agregado` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `galeria_arte`
--

CREATE TABLE `galeria_arte` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `fecha_subida` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `libros`
--

CREATE TABLE `libros` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `portada` varchar(255) DEFAULT NULL,
  `archivo_obra` varchar(255) DEFAULT NULL,
  `fecha_subida` datetime DEFAULT current_timestamp(),
  `publico_adulto` tinyint(1) DEFAULT 0,
  `visitas` int(11) DEFAULT 0,
  `destacado` tinyint(1) DEFAULT 0,
  `fecha_destacado` datetime DEFAULT NULL,
  `valoracion` float DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `libros`
--

INSERT INTO `libros` (`id`, `usuario_id`, `categoria_id`, `titulo`, `descripcion`, `portada`, `archivo_obra`, `fecha_subida`, `publico_adulto`, `visitas`, `destacado`, `fecha_destacado`, `valoracion`) VALUES
(1, 1, NULL, 'Medusa, la mirada del castigo', 'Una historia mitológica sobre Medusa, la bella sacerdotisa condenada por los dioses a una existencia maldita. A través de su mirada convertida en castigo, se revela la injusticia divina y el poder de una mujer que pasó de víctima a símbolo eterno del miedo y la transformación.', 'portada_683d8338ef135.jpg', NULL, '2025-06-02 12:55:53', 0, 0, 0, NULL, 0),
(2, 1, NULL, 'El Desafío de Aracne', 'Esta obra explora la legendaria confrontación entre una talentosa mortal y una poderosa diosa, tejiendo una historia de arte, orgullo y las peligrosas consecuencias de la hybris.\r\n\r\nDescubre cómo la inigualable habilidad de Aracne con el telar la llevó a desafiar a la mismísima Atenea.\r\n\r\n¿Fue la perfección de Aracne una ofensa imperdonable o un testamento del potencial mortal? Un viaje a través de este mito atemporal que te hará cuestionar la naturaleza del arte, la humildad y el precio de la ambición.', 'portada_683d99cd42703.jpg', NULL, '2025-06-02 14:32:13', 0, 0, 0, NULL, 0),
(3, 1, NULL, 'Sombras en la Ciudad', 'En una metrópolis consumida por la oscuridad y los secretos, el detective Marco Ruiz se enfrenta a un caso que lo llevará al límite. Un asesinato brutal desata una serie de eventos que revelarán la corrupción en los rincones más profundos de la ciudad. Mientras las sombras se alargan, Marco deberá desentrañar una red de mentiras y traiciones para encontrar al culpable antes de que la ciudad se convierta en su propia tumba.', 'portada_683da3935ff06.jpg', NULL, '2025-06-02 15:13:55', 0, 0, 0, NULL, 0),
(4, 1, NULL, 'El Bosque de los Secretos Brillantes', 'En un mundo donde los árboles susurran secretos y las flores brillan con luz propia, un niño llamado Alex descubre un mapa que lo lleva a una aventura inolvidable. Junto a criaturas mágicas y paisajes asombrosos, Alex deberá superar pruebas y desentrañar misterios para salvar el Bosque de los Secretos Brillantes de una sombra que amenaza con apagar su magia.', 'portada_683daf896993d.jpg', NULL, '2025-06-02 16:04:57', 0, 0, 0, NULL, 0),
(5, 1, NULL, 'El Secreto de las Estrellas Fugaces', 'En Bahía Escondida, la amistad de Maya y Sofía se transforma en un romance inesperado bajo el cielo de verano. Entre noches de estrellas y secretos compartidos, deberán enfrentar sus miedos y las expectativas ajenas para descubrir si su amor puede brillar tan fuerte como una estrella fugaz.', 'portada_683dbd04c1b85.jpg', NULL, '2025-06-02 17:02:28', 0, 0, 0, NULL, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `libro_categorias`
--

CREATE TABLE `libro_categorias` (
  `libro_id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `libro_categorias`
--

INSERT INTO `libro_categorias` (`libro_id`, `categoria_id`) VALUES
(1, 13),
(2, 13),
(3, 4),
(3, 11),
(3, 20),
(4, 6),
(4, 17),
(5, 3),
(5, 8),
(5, 14);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensajes`
--

CREATE TABLE `mensajes` (
  `id` int(11) NOT NULL,
  `emisor_id` int(11) NOT NULL,
  `receptor_id` int(11) NOT NULL,
  `contenido` text NOT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  `leida` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `me_gusta_arte`
--

CREATE TABLE `me_gusta_arte` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `arte_id` int(11) NOT NULL,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tipo` varchar(20) DEFAULT 'sistema',
  `mensaje` text NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `leido` tinyint(1) DEFAULT 0,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `obras`
--

CREATE TABLE `obras` (
  `id` int(11) NOT NULL,
  `tipo` varchar(20) NOT NULL,
  `contenido_id` int(11) NOT NULL,
  `destacado` tinyint(1) DEFAULT 0
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
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguidores`
--

CREATE TABLE `seguidores` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `seguido_id` int(11) NOT NULL,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `temas_foro`
--

CREATE TABLE `temas_foro` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `titulo` varchar(255) NOT NULL,
  `contenido` text NOT NULL,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `usuario` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  `fecha_nacimiento` date DEFAULT NULL,
  `nombre` varchar(150) DEFAULT NULL,
  `biografia` text DEFAULT NULL,
  `email_verificado` tinyint(1) DEFAULT 0,
  `token_verificacion` varchar(64) DEFAULT NULL,
  `token_recuperacion` varchar(80) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `banner_color` varchar(20) DEFAULT '#4361ee',
  `banner_img` varchar(255) DEFAULT NULL,
  `sitio_web` varchar(255) DEFAULT NULL,
  `ubicacion` varchar(255) DEFAULT NULL,
  `privacidad` enum('publico','privado') DEFAULT 'publico',
  `X` varchar(255) DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `facebook` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `usuario`, `email`, `password`, `fecha_registro`, `fecha_nacimiento`, `nombre`, `biografia`, `email_verificado`, `token_verificacion`, `token_recuperacion`, `avatar`, `banner_color`, `banner_img`, `sitio_web`, `ubicacion`, `privacidad`, `X`, `instagram`, `facebook`) VALUES
(1, 'whereisnhoa', 'ainhoalr1203@gmail.com', '$2y$10$9ktkNqrnhFDpX0GEA0joN.X23sleu8VfCXA2BYP7Mh.7MYRuGT/ZS', '2025-05-31 22:36:53', '2003-12-11', 'Ainhoa', 'Esto es la biografía.', 1, NULL, 'dd551ac1a9f142d2603b9d2824bb14d5a17c29d74c102d7844a3c39415be24b3', 'avatar_683cba1367225.jpg', '#eec144', NULL, '', 'San Roque, Cádiz (España)', 'publico', '', 'https://instagram.com/whereisnhoa', '');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `biblioteca`
--
ALTER TABLE `biblioteca`
  ADD PRIMARY KEY (`usuario_id`,`obra_id`,`tipo`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_obra` (`obra_id`),
  ADD KEY `idx_tipo` (`tipo`),
  ADD KEY `idx_estado` (`estado`);

--
-- Indices de la tabla `capitulos_comic`
--
ALTER TABLE `capitulos_comic`
  ADD PRIMARY KEY (`id`),
  ADD KEY `comic_id` (`comic_id`);

--
-- Indices de la tabla `capitulos_libro`
--
ALTER TABLE `capitulos_libro`
  ADD PRIMARY KEY (`id`),
  ADD KEY `libro_id` (`libro_id`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

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
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `comics`
--
ALTER TABLE `comics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Indices de la tabla `comic_categorias`
--
ALTER TABLE `comic_categorias`
  ADD PRIMARY KEY (`comic_id`,`categoria_id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Indices de la tabla `favoritos`
--
ALTER TABLE `favoritos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

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
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Indices de la tabla `libro_categorias`
--
ALTER TABLE `libro_categorias`
  ADD PRIMARY KEY (`libro_id`,`categoria_id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Indices de la tabla `mensajes`
--
ALTER TABLE `mensajes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `emisor_id` (`emisor_id`),
  ADD KEY `receptor_id` (`receptor_id`);

--
-- Indices de la tabla `me_gusta_arte`
--
ALTER TABLE `me_gusta_arte`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `arte_id` (`arte_id`);

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
  ADD PRIMARY KEY (`id`);

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
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `seguido_id` (`seguido_id`);

--
-- Indices de la tabla `temas_foro`
--
ALTER TABLE `temas_foro`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `capitulos_comic`
--
ALTER TABLE `capitulos_comic`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `capitulos_libro`
--
ALTER TABLE `capitulos_libro`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT de la tabla `categorias_arte`
--
ALTER TABLE `categorias_arte`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `categorias_foro`
--
ALTER TABLE `categorias_foro`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
-- Filtros para la tabla `capitulos_libro`
--
ALTER TABLE `capitulos_libro`
  ADD CONSTRAINT `capitulos_libro_ibfk_1` FOREIGN KEY (`libro_id`) REFERENCES `libros` (`id`);

--
-- Filtros para la tabla `comentarios`
--
ALTER TABLE `comentarios`
  ADD CONSTRAINT `comentarios_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `comentarios_arte`
--
ALTER TABLE `comentarios_arte`
  ADD CONSTRAINT `comentarios_arte_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `comics`
--
ALTER TABLE `comics`
  ADD CONSTRAINT `comics_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `comics_ibfk_2` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`);

--
-- Filtros para la tabla `comic_categorias`
--
ALTER TABLE `comic_categorias`
  ADD CONSTRAINT `comic_categorias_ibfk_1` FOREIGN KEY (`comic_id`) REFERENCES `comics` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comic_categorias_ibfk_2` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `favoritos`
--
ALTER TABLE `favoritos`
  ADD CONSTRAINT `favoritos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `galeria_arte`
--
ALTER TABLE `galeria_arte`
  ADD CONSTRAINT `galeria_arte_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `galeria_arte_ibfk_2` FOREIGN KEY (`categoria_id`) REFERENCES `categorias_arte` (`id`);

--
-- Filtros para la tabla `libros`
--
ALTER TABLE `libros`
  ADD CONSTRAINT `libros_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `libros_ibfk_2` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`);

--
-- Filtros para la tabla `libro_categorias`
--
ALTER TABLE `libro_categorias`
  ADD CONSTRAINT `libro_categorias_ibfk_1` FOREIGN KEY (`libro_id`) REFERENCES `libros` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `libro_categorias_ibfk_2` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `mensajes`
--
ALTER TABLE `mensajes`
  ADD CONSTRAINT `mensajes_ibfk_1` FOREIGN KEY (`emisor_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `mensajes_ibfk_2` FOREIGN KEY (`receptor_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `me_gusta_arte`
--
ALTER TABLE `me_gusta_arte`
  ADD CONSTRAINT `me_gusta_arte_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `me_gusta_arte_ibfk_2` FOREIGN KEY (`arte_id`) REFERENCES `galeria_arte` (`id`);

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `respuestas_foro`
--
ALTER TABLE `respuestas_foro`
  ADD CONSTRAINT `respuestas_foro_ibfk_1` FOREIGN KEY (`tema_id`) REFERENCES `temas_foro` (`id`),
  ADD CONSTRAINT `respuestas_foro_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `seguidores`
--
ALTER TABLE `seguidores`
  ADD CONSTRAINT `seguidores_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `seguidores_ibfk_2` FOREIGN KEY (`seguido_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `temas_foro`
--
ALTER TABLE `temas_foro`
  ADD CONSTRAINT `temas_foro_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `temas_foro_ibfk_2` FOREIGN KEY (`categoria_id`) REFERENCES `categorias_foro` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
