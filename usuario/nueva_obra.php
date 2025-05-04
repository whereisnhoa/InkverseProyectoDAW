
<?php

    include '/includes/funciones.php';
    verificarLogin();
    session_start();
    include '/includes/header.php';

?>

<h2>Subir nueva obra</h2>

<form action="/controllers/procesar-obra.php" method="POST">

  <label for="titulo">Título de la obra:</label><br>
  <input type="text" name="titulo" required><br><br>

  <label for="descripcion">Descripción:</label><br>
  <textarea name="descripcion" rows="5" cols="40"></textarea><br><br>

  <label for="tipo">Tipo de obra:</label><br>
  <select name="tipo" required>
    <option value="libro">Libro</option>
    <option value="manga">Manga/Cómic</option>
  </select><br><br>

  <label for="genero">Género:</label><br>
  <input type="text" name="genero"><br><br>

  <label>
    <input type="checkbox" name="es_adulto" value="1">
    Esta obra contiene contenido para adultos (+18)
  </label><br><br>

  <button type="submit">Publicar obra</button>

</form>

<?php 

    include '/includes/footer.php'; 

?>
