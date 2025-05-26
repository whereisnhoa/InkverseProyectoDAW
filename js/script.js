/**
 * Script principal para InkVerse 
 * Maneja la funcionalidad del frontend con mejoras en dropdowns y navegación
 */

// Variables globales
let currentTheme = localStorage.getItem("theme") || "light"
const BASE_URL = window.BASE_URL || ""
const TEXTOS_JS = window.TEXTOS_JS || {}
const IS_USER_MINOR = window.IS_USER_MINOR || false

// Inicialización cuando el DOM está listo
document.addEventListener("DOMContentLoaded", () => {
  initializeTheme()
  initializeDropdowns()
  initializeMobileMenu()
  initializeNotifications()
  initializeRandomContent()
  initializeForms()
  initializeFavoritos()
  initializeSearch()
  initializeLanguageSelector()
})

/**
 * Inicializar tema oscuro/claro
 */
function initializeTheme() {
  const themeToggle = document.querySelector(".dark-mode-toggle")
  const body = document.body

  // Aplicar tema guardado
  if (currentTheme === "dark") {
    body.setAttribute("data-theme", "dark")
    if (themeToggle) {
      themeToggle.innerHTML = '<i class="fas fa-sun"></i>'
    }
  }

  // Event listener para cambio de tema
  if (themeToggle) {
    themeToggle.addEventListener("click", function (e) {
      e.preventDefault()
      e.stopPropagation()

      if (currentTheme === "light") {
        currentTheme = "dark"
        body.setAttribute("data-theme", "dark")
        this.innerHTML = '<i class="fas fa-sun"></i>'
      } else {
        currentTheme = "light"
        body.removeAttribute("data-theme")
        this.innerHTML = '<i class="fas fa-moon"></i>'
      }

      localStorage.setItem("theme", currentTheme)
    })
  }
}

/**
 * Inicializar dropdowns
 */
function initializeDropdowns() {
  const dropdowns = document.querySelectorAll(".dropdown")

  dropdowns.forEach((dropdown) => {
    const toggle = dropdown.querySelector(".dropdown-toggle")
    const content = dropdown.querySelector(".dropdown-content")

    if (toggle && content) {
      // Prevenir comportamiento por defecto en enlaces
      toggle.addEventListener("click", (e) => {
        e.preventDefault()
        e.stopPropagation()

        // Cerrar otros dropdowns
        dropdowns.forEach((otherDropdown) => {
          if (otherDropdown !== dropdown) {
            otherDropdown.classList.remove("active")
          }
        })

        // Toggle current dropdown
        dropdown.classList.toggle("active")
      })

      // Mantener dropdown abierto al hacer click dentro del contenido
      content.addEventListener("click", (e) => {
        e.stopPropagation()
      })
    }
  })

  // Cerrar dropdowns al hacer click fuera
  document.addEventListener("click", (e) => {
    if (!e.target.closest(".dropdown")) {
      dropdowns.forEach((dropdown) => {
        dropdown.classList.remove("active")
      })
    }
  })

  // Cerrar dropdowns con Escape
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      dropdowns.forEach((dropdown) => {
        dropdown.classList.remove("active")
      })
    }
  })

  // Manejar hover en desktop para mega dropdowns
  dropdowns.forEach((dropdown) => {
    if (dropdown.querySelector(".mega-dropdown")) {
      let hoverTimeout

      dropdown.addEventListener("mouseenter", function () {
        clearTimeout(hoverTimeout)
        if (window.innerWidth > 768) {
          this.classList.add("active")
        }
      })

      dropdown.addEventListener("mouseleave", function () {
        if (window.innerWidth > 768) {
          hoverTimeout = setTimeout(() => {
            this.classList.remove("active")
          }, 300)
        }
      })
    }
  })
}

/**
 * Inicializar menú móvil
 */
function initializeMobileMenu() {
  const mobileToggle = document.querySelector(".mobile-menu-toggle")
  const mobileMenu = document.querySelector(".mobile-nav-menu")
  const mobileOverlay = document.querySelector(".mobile-nav-overlay")
  const closeBtn = document.querySelector(".mobile-nav-menu .close-btn")
  const mobileDropdowns = document.querySelectorAll(".dropdown-mobile")

  // Abrir menú móvil
  if (mobileToggle && mobileMenu && mobileOverlay) {
    mobileToggle.addEventListener("click", (e) => {
      e.preventDefault()
      e.stopPropagation()
      openMobileMenu()
    })

    // Cerrar menú móvil
    function closeMobileMenu() {
      mobileMenu.classList.remove("active")
      mobileOverlay.classList.remove("active")
      document.body.style.overflow = ""
      document.body.classList.remove("mobile-menu-open")
    }

    function openMobileMenu() {
      mobileMenu.classList.add("active")
      mobileOverlay.classList.add("active")
      document.body.style.overflow = "hidden"
      document.body.classList.add("mobile-menu-open")
    }

    if (closeBtn) {
      closeBtn.addEventListener("click", (e) => {
        e.preventDefault()
        closeMobileMenu()
      })
    }

    mobileOverlay.addEventListener("click", closeMobileMenu)

    // Dropdowns móviles
    mobileDropdowns.forEach((dropdown) => {
      const toggle = dropdown.querySelector(".dropdown-toggle")
      const content = dropdown.querySelector(".dropdown-mobile-content")

      if (toggle && content) {
        toggle.addEventListener("click", (e) => {
          e.preventDefault()
          dropdown.classList.toggle("active")
        })
      }
    })

    // Cerrar menú móvil al cambiar a desktop
    window.addEventListener("resize", () => {
      if (window.innerWidth > 768) {
        closeMobileMenu()
      }
    })
  }
}

/**
 * Inicializar sistema de notificaciones
 */
function initializeNotifications() {
  const notificationDropdown = document.querySelector(".notification-dropdown")
  if (notificationDropdown) {
    const toggle = notificationDropdown.querySelector(".dropdown-toggle")
    const markAllBtn = notificationDropdown.querySelector(".notification-mark-all")

    if (toggle) {
      toggle.addEventListener("click", function (e) {
        e.preventDefault()
        e.stopPropagation()

        // Marcar notificaciones como vistas después de un delay
        setTimeout(() => {
          const badge = this.querySelector(".notification-badge")
          if (badge && badge.textContent !== "0") {
            // Aquí podrías hacer una llamada AJAX para marcar como vistas
            console.log("Marcando notificaciones como vistas...")
          }
        }, 1000)
      })
    }

    if (markAllBtn) {
      markAllBtn.addEventListener("click", function (e) {
        e.preventDefault()

        // Hacer petición AJAX para marcar todas como leídas
        fetch(this.href, {
          method: "GET",
          headers: {
            "X-Requested-With": "XMLHttpRequest",
          },
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              // Actualizar UI
              const badge = document.querySelector(".notification-badge")
              if (badge) {
                badge.style.display = "none"
              }

              // Marcar todas las notificaciones como leídas visualmente
              document.querySelectorAll(".notification-item.unread").forEach((item) => {
                item.classList.remove("unread")
              })

              showAlert("Todas las notificaciones han sido marcadas como leídas", "success")
            }
          })
          .catch((error) => {
            console.error("Error:", error)
            showAlert("Error al marcar notificaciones", "error")
          })
      })
    }
  }
}

/**
 * Inicializar búsqueda
 */
function initializeSearch() {
  const searchForm = document.querySelector(".search-form")
  const searchInput = document.querySelector(".search-input")

  if (searchForm && searchInput) {
    searchForm.addEventListener("submit", (e) => {
      const query = searchInput.value.trim()
      if (!query) {
        e.preventDefault()
        showAlert("Por favor, ingresa un término de búsqueda", "warning")
        searchInput.focus()
      }
    })

    // Búsqueda con Enter
    searchInput.addEventListener("keypress", (e) => {
      if (e.key === "Enter") {
        searchForm.dispatchEvent(new Event("submit"))
      }
    })
  }
}

/**
 * Inicializar selector de idioma
 */
function initializeLanguageSelector() {
  const languageDropdown = document.querySelector(".language-dropdown")
  if (languageDropdown) {
    const flagItems = languageDropdown.querySelectorAll(".flag-item")

    flagItems.forEach((item) => {
      item.addEventListener("click", function (e) {
        e.preventDefault()

        // Obtener el idioma del href
        const url = new URL(this.href)
        const lang = url.searchParams.get("lang")

        if (lang) {
          // Actualizar URL manteniendo otros parámetros
          const currentUrl = new URL(window.location)
          currentUrl.searchParams.set("lang", lang)
          window.location.href = currentUrl.toString()
        }
      })
    })
  }
}

/**
 * Inicializar contenido aleatorio
 */
function initializeRandomContent() {
  const randomButtons = document.querySelectorAll("[data-random-type]")
  const randomContainer = document.querySelector(".random-obra-container")

  randomButtons.forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault()
      const type = this.getAttribute("data-random-type")
      loadRandomContent(type, randomContainer)
    })
  })
}

/**
 * Cargar contenido aleatorio
 */
function loadRandomContent(type, container) {
  if (!container) return

  // Mostrar spinner
  container.innerHTML = '<div class="spinner"></div>'

  // Hacer petición AJAX
  fetch(`${BASE_URL}/controllers/contenido-aleatorio.php?tipo=${type}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.obra) {
        displayRandomContent(data.obra, container)
      } else {
        container.innerHTML = `
                    <div class="text-center">
                        <p>${TEXTOS_JS.no_obras_disponibles || "No hay obras disponibles."}</p>
                    </div>
                `
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      container.innerHTML = `
                <div class="text-center">
                    <p>Error al cargar el contenido.</p>
                </div>
            `
    })
}

/**
 * Mostrar contenido aleatorio
 */
function displayRandomContent(obra, container) {
  const adultBadge = obra.publico_adulto && !IS_USER_MINOR ? '<span class="badge badge-warning">+18</span>' : ""

  container.innerHTML = `
        <div class="random-obra-card">
            <div class="random-obra-image">
                <img src="${obra.portada}" alt="${obra.titulo}" loading="lazy">
            </div>
            <div class="random-obra-content">
                <h3>${obra.titulo} ${adultBadge}</h3>
                <p class="random-obra-author">${TEXTOS_JS.por || "Por"} ${obra.autor}</p>
                <p class="random-obra-description">${obra.descripcion}</p>
                <div class="random-obra-meta">
                    <span class="categoria">${obra.categoria}</span>
                    <span class="fecha">${obra.fecha_subida}</span>
                </div>
                <div class="random-obra-actions">
                    <a href="${BASE_URL}/public/obra.php?id=${obra.id}&tipo=${obra.tipo}" class="btn btn-accent">
                        ${TEXTOS_JS.ver_obra || "Ver Obra"}
                    </a>
                    <button onclick="loadRandomContent('${obra.tipo}', this.closest('.random-obra-container'))" class="btn btn-outline">
                        ${TEXTOS_JS.otra_obra_aleatoria || "Otra Obra"}
                    </button>
                </div>
            </div>
        </div>
    `
}

/**
 * Inicializar formularios
 */
function initializeForms() {
  // Validación en tiempo real
  const forms = document.querySelectorAll("form[data-validate]")
  forms.forEach((form) => {
    const inputs = form.querySelectorAll("input, textarea, select")
    inputs.forEach((input) => {
      input.addEventListener("blur", function () {
        validateField(this)
      })

      input.addEventListener("input", function () {
        if (this.classList.contains("is-invalid")) {
          validateField(this)
        }
      })
    })

    form.addEventListener("submit", (e) => {
      let isValid = true
      inputs.forEach((input) => {
        if (!validateField(input)) {
          isValid = false
        }
      })

      if (!isValid) {
        e.preventDefault()
      }
    })
  })
}

/**
 * Validar campo individual
 */
function validateField(field) {
  const value = field.value.trim()
  const type = field.type
  const required = field.hasAttribute("required")
  let isValid = true
  let message = ""

  // Limpiar errores previos
  field.classList.remove("is-invalid")
  const existingFeedback = field.parentNode.querySelector(".invalid-feedback")
  if (existingFeedback) {
    existingFeedback.remove()
  }

  // Validar campo requerido
  if (required && !value) {
    isValid = false
    message = "Este campo es obligatorio."
  }

  // Validaciones específicas por tipo
  if (value && isValid) {
    switch (type) {
      case "email":
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
        if (!emailRegex.test(value)) {
          isValid = false
          message = "Introduce un email válido."
        }
        break

      case "password":
        if (value.length < 6) {
          isValid = false
          message = "La contraseña debe tener al menos 6 caracteres."
        }
        break

      case "url":
        try {
          new URL(value)
        } catch {
          isValid = false
          message = "Introduce una URL válida."
        }
        break
    }
  }

  // Validación personalizada
  const pattern = field.getAttribute("pattern")
  if (pattern && value && !new RegExp(pattern).test(value)) {
    isValid = false
    message = field.getAttribute("data-error-message") || "Formato no válido."
  }

  // Mostrar error si no es válido
  if (!isValid) {
    field.classList.add("is-invalid")
    const feedback = document.createElement("div")
    feedback.className = "invalid-feedback"
    feedback.textContent = message
    field.parentNode.appendChild(feedback)
  }

  return isValid
}

/**
 * Inicializar sistema de favoritos
 */
function initializeFavoritos() {
  // Botones de favoritos en las páginas mejoradas
  document.querySelectorAll(".btn-favorito").forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault()
      const obraId = this.dataset.obraId
      const tipo = this.dataset.tipo
      toggleFavorito(obraId, tipo, this)
    })
  })

  // Botones de quitar favoritos
  document.querySelectorAll(".btn-quitar-favorito").forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault()
      const obraId = this.dataset.obraId
      const tipo = this.dataset.tipo

      if (confirm("¿Estás seguro de que quieres quitar esta obra de tus favoritos?")) {
        quitarDeFavoritos(obraId, tipo, this)
      }
    })
  })
}

/**
 * Función mejorada para manejar favoritos
 */
function toggleFavorito(obraId, tipo, button = null) {
  if (!obraId || !tipo) {
    showAlert("Error: Datos de obra inválidos", "error")
    return
  }

  // Determinar la ruta base según la ubicación actual
  const currentPath = window.location.pathname
  let basePath = ""

  if (currentPath.includes("/public/")) {
    basePath = "../controllers/"
  } else if (currentPath.includes("/usuario/")) {
    basePath = "../controllers/"
  } else {
    basePath = "./controllers/"
  }

  // Hacer petición AJAX
  fetch(basePath + "procesar-favorito.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
      "X-Requested-With": "XMLHttpRequest",
    },
    body: `accion=toggle&contenido_id=${obraId}&tipo_contenido=${tipo}`,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        if (button) {
          updateFavoritoButton(button, data.agregado)
        }

        // Actualizar todos los botones de la misma obra
        document.querySelectorAll(`[data-obra-id="${obraId}"][data-tipo="${tipo}"]`).forEach((btn) => {
          if (btn !== button) {
            updateFavoritoButton(btn, data.agregado)
          }
        })

        showAlert(data.mensaje, data.agregado ? "success" : "info")
      } else {
        showAlert(data.mensaje || "Error al procesar favorito", "error")
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      showAlert("Error de conexión", "error")
    })
}

/**
 * Función para quitar de favoritos
 */
function quitarDeFavoritos(obraId, tipo, button = null) {
  if (!obraId || !tipo) {
    showAlert("Error: Datos de obra inválidos", "error")
    return
  }

  const currentPath = window.location.pathname
  let basePath = ""

  if (currentPath.includes("/public/")) {
    basePath = "../controllers/"
  } else if (currentPath.includes("/usuario/")) {
    basePath = "../controllers/"
  } else {
    basePath = "./controllers/"
  }

  fetch(basePath + "procesar-favorito.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
      "X-Requested-With": "XMLHttpRequest",
    },
    body: `accion=quitar&contenido_id=${obraId}&tipo_contenido=${tipo}`,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Si estamos en la página de favoritos, remover la tarjeta
        if (window.location.pathname.includes("favoritos")) {
          const card = document.querySelector(`[data-obra-id="${obraId}"][data-tipo="${tipo}"]`)
          if (card && card.closest(".obra-card")) {
            const obraCard = card.closest(".obra-card")
            obraCard.style.animation = "fadeOut 0.3s ease-out"
            setTimeout(() => {
              obraCard.remove()

              // Si no quedan obras, recargar la página
              if (document.querySelectorAll(".obra-card").length === 0) {
                location.reload()
              }
            }, 300)
          }
        } else if (button) {
          updateFavoritoButton(button, false)
        }

        showAlert(data.mensaje, "success")
      } else {
        showAlert(data.mensaje || "Error al quitar de favoritos", "error")
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      showAlert("Error de conexión", "error")
    })
}

/**
 * Actualizar apariencia del botón de favorito
 */
function updateFavoritoButton(button, isFavorito) {
  if (!button) return

  const icon = button.querySelector("i")

  if (isFavorito) {
    // Está en favoritos
    if (icon) {
      icon.className = "fas fa-heart"
    }
    button.classList.add("favorited")
    button.style.background = "#e74c3c"
    button.style.color = "white"
    button.title = "Quitar de favoritos"
  } else {
    // No está en favoritos
    if (icon) {
      icon.className = "far fa-heart"
    }
    button.classList.remove("favorited")
    button.style.background = "rgba(255,255,255,0.9)"
    button.style.color = "#333"
    button.title = "Añadir a favoritos"
  }
}

/**
 * Utilidades generales
 */

// Función para hacer peticiones AJAX
function makeRequest(url, options = {}) {
  const defaultOptions = {
    method: "GET",
    headers: {
      "Content-Type": "application/json",
    },
  }

  return fetch(url, { ...defaultOptions, ...options }).then((response) => {
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }
    return response.json()
  })
}

// Función mejorada para mostrar alertas
function showAlert(message, type = "info") {
  // Remover alertas existentes
  const existingAlerts = document.querySelectorAll(".alerta")
  existingAlerts.forEach((alert) => alert.remove())

  const alert = document.createElement("div")
  alert.className = `alerta alerta-${type}`
  alert.textContent = message
  alert.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
        padding: 12px 20px;
        border-radius: 6px;
        color: white;
        font-weight: 500;
        animation: slideIn 0.3s ease-out;
        max-width: 300px;
        word-wrap: break-word;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    `

  // Colores según el tipo
  switch (type) {
    case "success":
      alert.style.background = "#27ae60"
      break
    case "error":
      alert.style.background = "#e74c3c"
      break
    case "info":
      alert.style.background = "#3498db"
      break
    case "warning":
      alert.style.background = "#f39c12"
      break
    default:
      alert.style.background = "#6c757d"
  }

  document.body.appendChild(alert)

  // Auto-remove after 4 seconds
  setTimeout(() => {
    alert.style.animation = "slideOut 0.3s ease-out"
    setTimeout(() => {
      if (alert.parentNode) {
        alert.parentNode.removeChild(alert)
      }
    }, 300)
  }, 4000)
}

// Función para formatear fechas
function formatDate(dateString) {
  const date = new Date(dateString)
  return date.toLocaleDateString("es-ES", {
    year: "numeric",
    month: "long",
    day: "numeric",
  })
}

// Función para truncar texto
function truncateText(text, maxLength) {
  if (text.length <= maxLength) return text
  return text.substr(0, maxLength) + "..."
}

// Lazy loading para imágenes
function initializeLazyLoading() {
  const images = document.querySelectorAll('img[loading="lazy"]')

  if ("IntersectionObserver" in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          const img = entry.target
          img.src = img.dataset.src || img.src
          img.classList.remove("lazy")
          imageObserver.unobserve(img)
        }
      })
    })

    images.forEach((img) => imageObserver.observe(img))
  }
}

// Inicializar lazy loading cuando el DOM esté listo
document.addEventListener("DOMContentLoaded", initializeLazyLoading)

// Añadir animaciones CSS dinámicamente
const style = document.createElement("style")
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    
    @keyframes fadeOut {
        from { opacity: 1; transform: scale(1); }
        to { opacity: 0; transform: scale(0.8); }
    }
    
    .btn-favorito.favorited {
        background: #e74c3c !important;
        color: white !important;
    }
    
    .spinner {
        border: 4px solid #f3f3f3;
        border-top: 4px solid #3498db;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 2s linear infinite;
        margin: 20px auto;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    /* Mejoras para dropdowns */
    .dropdown.active .dropdown-content {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
    
    .dropdown-content {
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.3s ease;
    }
    
    /* Mejoras para menú móvil */
    .mobile-nav-overlay.active {
        opacity: 1;
        visibility: visible;
    }
    
    .mobile-nav-overlay {
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }
    
    .mobile-nav-menu.active {
        transform: translateX(0);
    }
    
    .mobile-nav-menu {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }
    
    /* Prevenir scroll cuando el menú móvil está abierto */
    body.mobile-menu-open {
        overflow: hidden;
    }
`
document.head.appendChild(style)

// Exportar funciones para uso global
window.InkVerse = {
  toggleFavorito,
  quitarDeFavoritos,
  showAlert,
  makeRequest,
  formatDate,
  truncateText,
  updateFavoritoButton,
}

// Hacer funciones disponibles globalmente para compatibilidad
window.toggleFavorito = toggleFavorito
window.quitarDeFavoritos = quitarDeFavoritos
window.showAlert = showAlert
