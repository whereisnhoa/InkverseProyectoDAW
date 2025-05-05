
</main>

    <footer>
        <p>Proyecto Ainhoa &copy; <?php echo date('Y'); ?></p>
    </footer>

    <!-- Scripts al final del documento para mejorar el rendimiento -->
    <script>
        // Código JavaScript común para todas las páginas
        document.addEventListener('DOMContentLoaded', function() {
            // Ocultar alertas después de 5 segundos
            const alertas = document.querySelectorAll('.alerta');
            alertas.forEach(function(alerta) {
                setTimeout(function() {
                    alerta.style.opacity = '0';
                    setTimeout(function() {
                        alerta.style.display = 'none';
                    }, 500);
                }, 5000);
            });
        });
    </script>
</body>
</html>

