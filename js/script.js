
// Esperar a que el DOM esté completamente cargado
document.addEventListener("DOMContentLoaded", () => {

  // idiomas
  const languageSwitcher = document.getElementById('language-switcher');

  if (languageSwitcher) 
    {
      languageSwitcher.addEventListener('change', (event) => {
          const selectedLang = event.target.value;

          // Obtiene la URL actual
          let currentUrl = window.location.href;

          // Construye un nuevo objeto URL para manipular los parámetros
          const url = new URL(currentUrl);

          // Establece el parámetro 'lang' con el idioma seleccionado
          url.searchParams.set('lang', selectedLang);

          // Redirige a la nueva URL
          window.location.href = url.toString();
      });
  }

  // Toggle del menú móvil
  const mobileMenuToggle = document.querySelector(".mobile-menu-toggle")
  const nav = document.querySelector("nav")

  if (mobileMenuToggle && nav) {
    mobileMenuToggle.addEventListener("click", () => {
      nav.classList.toggle("active")
    })
  }

  // Toggle del modo oscuro
  const darkModeToggle = document.querySelector(".dark-mode-toggle")

  if (darkModeToggle) {
    // Verificar si el modo oscuro está guardado en localStorage
    const isDarkMode = localStorage.getItem("darkMode") === "true"

    // Aplicar modo oscuro si estaba activado
    if (isDarkMode) {
      document.body.classList.add("dark-mode")
      updateDarkModeIcon(true)
    }

    // Manejar el clic en el botón de modo oscuro
    darkModeToggle.addEventListener("click", () => {
      const isDarkModeNow = document.body.classList.toggle("dark-mode")
      localStorage.setItem("darkMode", isDarkModeNow)
      updateDarkModeIcon(isDarkModeNow)
    })
  }

  // Actualizar el icono del modo oscuro
  function updateDarkModeIcon(isDark) {
    if (!darkModeToggle) return

    const icon = darkModeToggle.querySelector("i")
    if (icon) {
      if (isDark) {
        icon.classList.remove("fa-moon")
        icon.classList.add("fa-sun")
      } else {
        icon.classList.remove("fa-sun")
        icon.classList.add("fa-moon")
      }
    }
  }

  // Ocultar alertas después de 5 segundos
  const alertas = document.querySelectorAll(".alerta")
  alertas.forEach((alerta) => {
    setTimeout(() => {
      alerta.style.opacity = "0"
      setTimeout(() => {
        alerta.style.display = "none"
      }, 500)
    }, 5000)
  })

  // Manejar los menús desplegables
  const dropdownToggles = document.querySelectorAll(".dropdown-toggle")

  dropdownToggles.forEach((toggle) => {
    toggle.addEventListener("click", function (e) {
      e.preventDefault()
      e.stopPropagation()

      const parent = this.parentElement

      // Si ya está activo, cerrarlo
      if (parent.classList.contains("active")) {
        parent.classList.remove("active")
        return
      }

      // Cerrar todos los otros menús desplegables
      document.querySelectorAll(".dropdown").forEach((dropdown) => {
        dropdown.classList.remove("active")
      })

      // Abrir este menú
      parent.classList.add("active")
    })
  })

  // Cerrar menús al hacer clic fuera de ellos
  document.addEventListener("click", (e) => {
    if (!e.target.closest(".dropdown")) {
      document.querySelectorAll(".dropdown.active").forEach((dropdown) => {
        dropdown.classList.remove("active")
      })
    }
  })
})


    