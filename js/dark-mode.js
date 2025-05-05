
document.addEventListener('DOMContentLoaded', function() 
{
    // obtener el botón de cambio de tema
    const themeToggle = document.getElementById('theme-toggle');
    const themeIcon = document.getElementById('theme-icon');
    
    // comprobar si hay una preferencia guardada
    const currentTheme = localStorage.getItem('theme');
    
    // si hay una preferencia guardada, aplicarla
    if (currentTheme) 
    {
        document.body.classList.toggle('dark-mode', currentTheme === 'dark');
        updateIcon(currentTheme === 'dark');

    } else {
        // si no hay preferencia, comprobar la preferencia del sistema
        const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');
        if (prefersDarkScheme.matches) 
        {
            document.body.classList.add('dark-mode');
            localStorage.setItem('theme', 'dark');
            updateIcon(true);
        }
    }
    
    // función para cambiar el tema
    function toggleTheme() 
    {
        const isDarkMode = document.body.classList.toggle('dark-mode');
        localStorage.setItem('theme', isDarkMode ? 'dark' : 'light');
        updateIcon(isDarkMode);
    }
    
    // función para actualizar el icono
    function updateIcon(isDarkMode) 
    {
        if (isDarkMode) {
            themeIcon.className = 'fas fa-sun';
            themeToggle.setAttribute('title', 'Cambiar a modo claro');
        } else {
            themeIcon.className = 'fas fa-moon';
            themeToggle.setAttribute('title', 'Cambiar a modo oscuro');
        }
    }
    
    // añadir evento al botón
    themeToggle.addEventListener('click', toggleTheme);
    
});