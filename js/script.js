
// esperar a que el DOM esté completamente cargado
document.addEventListener("DOMContentLoaded", () => {
    // toggle del menú móvil
    const mobileMenuToggle = document.querySelector(".mobile-menu-toggle")
    const nav = document.querySelector("nav")
  
    if (mobileMenuToggle && nav) {
      mobileMenuToggle.addEventListener("click", () => {
        nav.classList.toggle("active")
      })
    }
  
    // toggle del modo oscuro
    const darkModeToggle = document.querySelector(".dark-mode-toggle")
  
    if (darkModeToggle) {
      // verificar si el modo oscuro está guardado en localStorage
      const isDarkMode = localStorage.getItem("darkMode") === "true"
  
      // aplicar modo oscuro si estaba activado
      if (isDarkMode) {
        document.body.classList.add("dark-mode")
        updateDarkModeIcon(true)
      }
  
      // clic en el botón de modo oscuro
      darkModeToggle.addEventListener("click", () => {
        const isDarkModeNow = document.body.classList.toggle("dark-mode")
        localStorage.setItem("darkMode", isDarkModeNow)
        updateDarkModeIcon(isDarkModeNow)
      })
    }
  
    // actualizar el icono del modo oscuro
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
  
    // ocultar alertas después de 5 segundos
    const alertas = document.querySelectorAll(".alerta")
    alertas.forEach((alerta) => {
      setTimeout(() => {
        alerta.style.opacity = "0"
        setTimeout(() => {
          alerta.style.display = "none"
        }, 500)
      }, 5000)
    })
  
    // mejorar interacción con los menús desplegables en móvil
    const dropdownToggles = document.querySelectorAll(".dropdown-toggle")
  
    if (window.innerWidth <= 768) {
      dropdownToggles.forEach((toggle) => {
        toggle.addEventListener("click", function (e) {
          e.preventDefault()
          const parent = this.parentElement
          const dropdownContent = parent.querySelector(".dropdown-content")
  
          // Cerrar todos los otros menús desplegables
          document.querySelectorAll(".dropdown-content").forEach((content) => {
            if (content !== dropdownContent) {
              content.style.display = "none"
            }
          })
  
          // Toggle el menú actual
          if (dropdownContent.style.display === "block") {
            dropdownContent.style.display = "none"
          } else {
            dropdownContent.style.display = "block"
          }
        })
      })
    }
  })
  