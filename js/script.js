/**
 * Script principal para InkVerse
 * Maneja la funcionalidad del frontend: temas, menús, formularios, favoritos, alertas, etc.
 */

// ==========================
// Variables globales y config
// ==========================
let currentTheme = localStorage.getItem("theme") || "light";
const BASE_URL = window.BASE_URL || "";
const TEXTOS_JS = window.TEXTOS_JS || {};
const IS_USER_MINOR = window.IS_USER_MINOR || false;

// =============================
// Inicialización principal
// =============================
document.addEventListener("DOMContentLoaded", () => {
  initializeTheme();
  initializeDropdowns();
  initializeMobileMenu();
  initializeNotifications();
  initializeRandomContent();
  initializeForms();
  initializeFavoritos();
  initializeSearch();
  initializeLanguageSelector();
  initializeAuthScripts();
  initializeLazyLoading();
});

// =============================
// 1. Tema oscuro/claro
// =============================
function initializeTheme() {
  const themeToggle = document.querySelector(".dark-mode-toggle");
  const body = document.body;

  if (currentTheme === "dark") {
    body.setAttribute("data-theme", "dark");
    if (themeToggle) {
      themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
    }
  }

  if (themeToggle) {
    themeToggle.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();
      if (currentTheme === "light") {
        currentTheme = "dark";
        body.setAttribute("data-theme", "dark");
        this.innerHTML = '<i class="fas fa-sun"></i>';
      } else {
        currentTheme = "light";
        body.removeAttribute("data-theme");
        this.innerHTML = '<i class="fas fa-moon"></i>';
      }
      localStorage.setItem("theme", currentTheme);
    });
  }
}

// =============================
// 2. Dropdowns (menú, notificaciones, etc.)
// =============================
function initializeDropdowns() {
  const dropdowns = document.querySelectorAll(".dropdown");
  dropdowns.forEach((dropdown) => {
    const toggle = dropdown.querySelector(".dropdown-toggle");
    const content = dropdown.querySelector(".dropdown-content");

    if (toggle && content) {
      toggle.addEventListener("click", (e) => {
        e.preventDefault();
        e.stopPropagation();
        dropdowns.forEach((otherDropdown) => {
          if (otherDropdown !== dropdown) {
            otherDropdown.classList.remove("active");
          }
        });
        dropdown.classList.toggle("active");
      });
      content.addEventListener("click", (e) => {
        e.stopPropagation();
      });
    }
  });

  document.addEventListener("click", (e) => {
    if (!e.target.closest(".dropdown")) {
      dropdowns.forEach((dropdown) => {
        dropdown.classList.remove("active");
      });
    }
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      dropdowns.forEach((dropdown) => {
        dropdown.classList.remove("active");
      });
    }
  });

  dropdowns.forEach((dropdown) => {
    if (dropdown.querySelector(".mega-dropdown")) {
      let hoverTimeout;
      dropdown.addEventListener("mouseenter", function () {
        clearTimeout(hoverTimeout);
        if (window.innerWidth > 768) {
          this.classList.add("active");
        }
      });
      dropdown.addEventListener("mouseleave", function () {
        if (window.innerWidth > 768) {
          hoverTimeout = setTimeout(() => {
            this.classList.remove("active");
          }, 300);
        }
      });
    }
  });
}

// =============================
// 3. Menú móvil
// =============================
function initializeMobileMenu() {
  const mobileToggle = document.querySelector(".mobile-menu-toggle");
  const mobileMenu = document.querySelector(".mobile-nav-menu");
  const mobileOverlay = document.querySelector(".mobile-nav-overlay");
  const closeBtn = document.querySelector(".mobile-nav-menu .close-btn");
  const mobileDropdowns = document.querySelectorAll(".dropdown-mobile");

  if (mobileToggle && mobileMenu && mobileOverlay) {
    mobileToggle.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      openMobileMenu();
    });

    function closeMobileMenu() {
      mobileMenu.classList.remove("active");
      mobileOverlay.classList.remove("active");
      document.body.style.overflow = "";
      document.body.classList.remove("mobile-menu-open");
    }

    function openMobileMenu() {
      mobileMenu.classList.add("active");
      mobileOverlay.classList.add("active");
      document.body.style.overflow = "hidden";
      document.body.classList.add("mobile-menu-open");
    }

    if (closeBtn) {
      closeBtn.addEventListener("click", (e) => {
        e.preventDefault();
        closeMobileMenu();
      });
    }

    mobileOverlay.addEventListener("click", closeMobileMenu);

    mobileDropdowns.forEach((dropdown) => {
      const toggle = dropdown.querySelector(".dropdown-toggle");
      const content = dropdown.querySelector(".dropdown-mobile-content");
      if (toggle && content) {
        toggle.addEventListener("click", (e) => {
          e.preventDefault();
          dropdown.classList.toggle("active");
        });
      }
    });

    window.addEventListener("resize", () => {
      if (window.innerWidth > 768) {
        closeMobileMenu();
      }
    });
  }
}

// =============================
// 4. Notificaciones
// =============================
function initializeNotifications() {
  const notificationDropdown = document.querySelector(".notification-dropdown");
  if (notificationDropdown) {
    const toggle = notificationDropdown.querySelector(".dropdown-toggle");
    const markAllBtn = notificationDropdown.querySelector(".notification-mark-all");

    if (toggle) {
      toggle.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();
        setTimeout(() => {
          const badge = this.querySelector(".notification-badge");
          if (badge && badge.textContent !== "0") {
            // Marcar como vistas
          }
        }, 1000);
      });
    }

    if (markAllBtn) {
      markAllBtn.addEventListener("click", function (e) {
        e.preventDefault();
        fetch(this.href, {
          method: "GET",
          headers: { "X-Requested-With": "XMLHttpRequest" },
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              const badge = document.querySelector(".notification-badge");
              if (badge) badge.style.display = "none";
              document.querySelectorAll(".notification-item.unread").forEach((item) => {
                item.classList.remove("unread");
              });
              showAlert("Todas las notificaciones han sido marcadas como leídas", "success");
            }
          })
          .catch((error) => {
            showAlert("Error al marcar notificaciones", "error");
          });
      });
    }
  }
}

// =============================
// 5. Búsqueda
// =============================
function initializeSearch() {
  const searchForm = document.querySelector(".search-form");
  const searchInput = document.querySelector(".search-input");

  if (searchForm && searchInput) {
    searchForm.addEventListener("submit", (e) => {
      const query = searchInput.value.trim();
      if (!query) {
        e.preventDefault();
        showAlert("Por favor, ingresa un término de búsqueda", "warning");
        searchInput.focus();
      }
    });

    searchInput.addEventListener("keypress", (e) => {
      if (e.key === "Enter") {
        searchForm.dispatchEvent(new Event("submit"));
      }
    });
  }
}

// =============================
// 6. Selector de idioma
// =============================
function initializeLanguageSelector() {
  const languageDropdown = document.querySelector(".language-dropdown");
  if (languageDropdown) {
    const flagItems = languageDropdown.querySelectorAll(".flag-item");
    flagItems.forEach((item) => {
      item.addEventListener("click", function (e) {
        e.preventDefault();
        const url = new URL(this.href);
        const lang = url.searchParams.get("lang");
        if (lang) {
          const currentUrl = new URL(window.location);
          currentUrl.searchParams.set("lang", lang);
          window.location.href = currentUrl.toString();
        }
      });
    });
  }
}

// =============================
// 7. Contenido aleatorio ("Obra aleatoria")
// =============================
function initializeRandomContent() {
  const randomButtons = document.querySelectorAll("[data-random-type]");
  const randomContainer = document.querySelector(".random-obra-container");

  randomButtons.forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault();
      const type = this.getAttribute("data-random-type");
      loadRandomContent(type, randomContainer);
    });
  });
}

function loadRandomContent(type, container) {
  if (!container) return;
  container.innerHTML = '<div class="spinner"></div>';
  fetch(`${BASE_URL}/controllers/contenido-aleatorio.php?tipo=${type}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.obra) {
        displayRandomContent(data.obra, container);
      } else {
        container.innerHTML = `
          <div class="text-center">
            <p>${TEXTOS_JS.no_obras_disponibles || "No hay obras disponibles."}</p>
          </div>
        `;
      }
    })
    .catch((error) => {
      container.innerHTML = `
        <div class="text-center">
          <p>Error al cargar el contenido.</p>
        </div>
      `;
    });
}

function displayRandomContent(obra, container) {
  const adultBadge = obra.publico_adulto && !IS_USER_MINOR ? '<span class="badge badge-warning">+18</span>' : "";
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
  `;
}

// =============================
// 8. Formularios (validación básica)
// =============================
function initializeForms() {
  const forms = document.querySelectorAll("form[data-validate]");
  forms.forEach((form) => {
    const inputs = form.querySelectorAll("input, textarea, select");
    inputs.forEach((input) => {
      input.addEventListener("blur", function () {
        validateField(this);
      });
      input.addEventListener("input", function () {
        if (this.classList.contains("is-invalid")) {
          validateField(this);
        }
      });
    });

    form.addEventListener("submit", (e) => {
      let isValid = true;
      inputs.forEach((input) => {
        if (!validateField(input)) isValid = false;
      });
      if (!isValid) e.preventDefault();
    });
  });
}

function validateField(field) {
  const value = field.value.trim();
  const type = field.type;
  const required = field.hasAttribute("required");
  let isValid = true, message = "";

  field.classList.remove("is-invalid");
  const existingFeedback = field.parentNode.querySelector(".invalid-feedback");
  if (existingFeedback) existingFeedback.remove();

  if (required && !value) {
    isValid = false;
    message = "Este campo es obligatorio.";
  }

  if (value && isValid) {
    switch (type) {
      case "email":
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
          isValid = false;
          message = "Introduce un email válido.";
        }
        break;
      case "password":
        if (value.length < 6) {
          isValid = false;
          message = "La contraseña debe tener al menos 6 caracteres.";
        }
        break;
      case "url":
        try { new URL(value); } catch {
          isValid = false;
          message = "Introduce una URL válida.";
        }
        break;
    }
  }

  const pattern = field.getAttribute("pattern");
  if (pattern && value && !new RegExp(pattern).test(value)) {
    isValid = false;
    message = field.getAttribute("data-error-message") || "Formato no válido.";
  }

  if (!isValid) {
    field.classList.add("is-invalid");
    const feedback = document.createElement("div");
    feedback.className = "invalid-feedback";
    feedback.textContent = message;
    field.parentNode.appendChild(feedback);
  }

  return isValid;
}

// =============================
// 9. Favoritos (añadir/quitar)
// =============================
function initializeFavoritos() {
  document.querySelectorAll(".btn-favorito").forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault();
      const obraId = this.dataset.obraId;
      const tipo = this.dataset.tipo;
      toggleFavorito(obraId, tipo, this);
    });
  });

  document.querySelectorAll(".btn-quitar-favorito").forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault();
      const obraId = this.dataset.obraId;
      const tipo = this.dataset.tipo;
      if (confirm("¿Estás seguro de que quieres quitar esta obra de tus favoritos?")) {
        quitarDeFavoritos(obraId, tipo, this);
      }
    });
  });
}

function toggleFavorito(obraId, tipo, button = null) {
  if (!obraId || !tipo) {
    showAlert("Error: Datos de obra inválidos", "error");
    return;
  }
  fetch("controllers/procesar-favorito.php", {
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
        if (button) updateFavoritoButton(button, data.agregado);
        document.querySelectorAll(`[data-obra-id="${obraId}"][data-tipo="${tipo}"]`).forEach((btn) => {
          if (btn !== button) updateFavoritoButton(btn, data.agregado);
        });
        showAlert(data.mensaje, data.agregado ? "success" : "info");
      } else {
        showAlert(data.mensaje || "Error al procesar favorito", "error");
      }
    })
    .catch((error) => showAlert("Error de conexión", "error"));
}

function quitarDeFavoritos(obraId, tipo, button = null) {
  if (!obraId || !tipo) {
    showAlert("Error: Datos de obra inválidos", "error");
    return;
  }
  fetch("controllers/procesar-favorito.php", {
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
        if (window.location.pathname.includes("favoritos")) {
          const card = document.querySelector(`[data-obra-id="${obraId}"][data-tipo="${tipo}"]`);
          if (card && card.closest(".obra-card")) {
            const obraCard = card.closest(".obra-card");
            obraCard.style.animation = "fadeOut 0.3s ease-out";
            setTimeout(() => {
              obraCard.remove();
              if (document.querySelectorAll(".obra-card").length === 0) {
                location.reload();
              }
            }, 300);
          }
        } else if (button) {
          updateFavoritoButton(button, false);
        }
        showAlert(data.mensaje, "success");
      } else {
        showAlert(data.mensaje || "Error al quitar de favoritos", "error");
      }
    })
    .catch((error) => showAlert("Error de conexión", "error"));
}

function updateFavoritoButton(button, isFavorito) {
  if (!button) return;
  const icon = button.querySelector("i");
  if (isFavorito) {
    if (icon) icon.className = "fas fa-heart";
    button.classList.add("favorited");
    button.style.background = "#e74c3c";
    button.style.color = "white";
    button.title = "Quitar de favoritos";
    button.innerHTML = `<i class="fas fa-heart"></i> Quitar de favoritos`;
  } else {
    if (icon) icon.className = "far fa-heart";
    button.classList.remove("favorited");
    button.style.background = "rgba(255,255,255,0.9)";
    button.style.color = "#333";
    button.title = "Añadir a favoritos";
    button.innerHTML = `<i class="far fa-heart"></i> Añadir a favoritos`;
  }
}

// =============================
// 10. LOGIN Y REGISTRO (mostrar/ocultar contraseña, validación, etc.)
// =============================

function initializeAuthScripts() {
  const passwordInput = document.getElementById('password');
  const togglePassword = document.getElementById('togglePassword');
  if(passwordInput && togglePassword) {
    togglePassword.addEventListener('click', function() {
      const icon = togglePassword.querySelector('i');
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
        togglePassword.setAttribute('aria-label', 'Ocultar contraseña');
      } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
        togglePassword.setAttribute('aria-label', 'Mostrar contraseña');
      }
    });
  }

  const confirmPasswordInput = document.getElementById('confirm_password');
  const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
  if(confirmPasswordInput && toggleConfirmPassword) {
    toggleConfirmPassword.addEventListener('click', function() {
      const icon = toggleConfirmPassword.querySelector('i');
      if (confirmPasswordInput.type === 'password') {
        confirmPasswordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
        toggleConfirmPassword.setAttribute('aria-label', 'Ocultar contraseña');
      } else {
        confirmPasswordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
        toggleConfirmPassword.setAttribute('aria-label', 'Mostrar contraseña');
      }
    });
  }

  if(passwordInput && confirmPasswordInput) {
    const passwordStrengthContainer = document.getElementById('passwordStrengthContainer');
    const passwordStrength = document.getElementById('passwordStrength');
    const passwordStrengthBar = document.getElementById('passwordStrengthBar');
    const passwordStrengthText = document.getElementById('passwordStrengthText');
    const passwordMatchError = document.getElementById('password-match-error');
    const reqLength = document.getElementById('req-length');
    const reqUppercase = document.getElementById('req-uppercase');
    const reqLowercase = document.getElementById('req-lowercase');
    const reqNumber = document.getElementById('req-number');
    const reqSpecial = document.getElementById('req-special');
    const submitBtn = document.getElementById('submitBtn');
    const form = document.getElementById('registroForm');

    function updateRequirement(element, isMet) {
      if (!element) return;
      if (isMet) {
        element.classList.remove('unmet');
        element.classList.add('met');
        element.querySelector('i').className = 'fas fa-check-circle';
      } else {
        element.classList.remove('met');
        element.classList.add('unmet');
        element.querySelector('i').className = 'fas fa-times-circle';
      }
    }

    function checkPasswordStrength(password) {
      if (!passwordStrengthContainer || !passwordStrength || !passwordStrengthBar || !passwordStrengthText) return true;

      if (password === '') {
        passwordStrengthContainer.style.display = 'none';
        return false;
      }
      passwordStrengthContainer.style.display = 'block';

      let strength = 0;
      let hasLength = password.length >= 8;
      let hasUppercase = /[A-Z]/.test(password);
      let hasLowercase = /[a-z]/.test(password);
      let hasNumber = /[0-9]/.test(password);
      let hasSpecial = /[!@#$%^&*]/.test(password);

      updateRequirement(reqLength, hasLength);
      updateRequirement(reqUppercase, hasUppercase);
      updateRequirement(reqLowercase, hasLowercase);
      updateRequirement(reqNumber, hasNumber);
      updateRequirement(reqSpecial, hasSpecial);

      if (hasLength) strength += 1;
      if (hasUppercase) strength += 1;
      if (hasLowercase) strength += 1;
      if (hasNumber) strength += 1;
      if (hasSpecial) strength += 1;

      passwordStrength.className = 'password-strength';
      if (strength < 3) {
        passwordStrength.classList.add('strength-weak');
        passwordStrengthText.textContent = 'Contraseña débil';
      } else if (strength < 5) {
        passwordStrength.classList.add('strength-medium');
        passwordStrengthText.textContent = 'Contraseña moderada';
      } else {
        passwordStrength.classList.add('strength-strong');
        passwordStrengthText.textContent = 'Contraseña fuerte';
      }

      return hasLength && hasUppercase && hasLowercase && hasNumber && hasSpecial;
    }

    function checkPasswordMatch() {
      if (!passwordMatchError) return true;
      const password = passwordInput.value;
      const confirmPassword = confirmPasswordInput.value;
      if (confirmPassword === '') {
        passwordMatchError.style.display = 'none';
        return true;
      }
      if (password === confirmPassword) {
        passwordMatchError.style.display = 'none';
        return true;
      } else {
        passwordMatchError.style.display = 'block';
        return false;
      }
    }

    passwordInput.addEventListener('input', function() {
      checkPasswordStrength(this.value);
      if (confirmPasswordInput.value !== '') {
        checkPasswordMatch();
      }
    });
    confirmPasswordInput.addEventListener('input', function() {
      checkPasswordMatch();
    });

    if(form) {
      form.addEventListener('submit', function(e) {
        const isPasswordValid = checkPasswordStrength(passwordInput.value);
        const doPasswordsMatch = checkPasswordMatch();
        if (!isPasswordValid) {
          e.preventDefault();
          showAlert('La contraseña debe cumplir todos los requisitos de seguridad.', 'error');
          return;
        }
        if (!doPasswordsMatch) {
          e.preventDefault();
          showAlert('Las contraseñas no coinciden.', 'error');
          return;
        }
      });
    }
  }
}

// =============================
// 11. Lazy Loading para imágenes
// =============================
function initializeLazyLoading() {
  const images = document.querySelectorAll('img[loading="lazy"]');
  if ("IntersectionObserver" in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          const img = entry.target;
          img.src = img.dataset.src || img.src;
          img.classList.remove("lazy");
          imageObserver.unobserve(img);
        }
      });
    });
    images.forEach((img) => imageObserver.observe(img));
  }
}

// =============================
// 12. Utilidades generales
// =============================

function showAlert(message, type = "info") {
  const existingAlerts = document.querySelectorAll(".alerta");
  existingAlerts.forEach((alert) => alert.remove());

  const alert = document.createElement("div");
  alert.className = `alerta alerta-${type}`;
  alert.textContent = message;
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
  `;
  switch (type) {
    case "success": alert.style.background = "#27ae60"; break;
    case "error": alert.style.background = "#e74c3c"; break;
    case "info": alert.style.background = "#3498db"; break;
    case "warning": alert.style.background = "#f39c12"; break;
    default: alert.style.background = "#6c757d";
  }
  document.body.appendChild(alert);
  setTimeout(() => {
    alert.style.animation = "slideOut 0.3s ease-out";
    setTimeout(() => { if (alert.parentNode) alert.parentNode.removeChild(alert); }, 300);
  }, 4000);
}

// Función para formatear fechas
function formatDate(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString("es-ES", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
}

// Función para truncar texto
function truncateText(text, maxLength) {
  if (text.length <= maxLength) return text;
  return text.substr(0, maxLength) + "...";
}

// =============================
// 13. Estilos dinámicos para animaciones y mejoras varias
// =============================
const style = document.createElement("style");
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
    body.mobile-menu-open {
        overflow: hidden;
    }
`;
document.head.appendChild(style);

// ===== 14. USUARIO: Perfil y edición de avatar, banner, redes sociales =====

function initializeUsuarioPage() {
  const inputAvatar = document.getElementById('avatar');
  const previewAvatar = document.querySelector('#preview-avatar img');
  if (inputAvatar && previewAvatar) {
    inputAvatar.addEventListener('change', function() {
      const file = this.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          previewAvatar.src = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    });
  }
  const inputBanner = document.getElementById('banner');
  const previewBanner = document.querySelector('#preview-banner img');
  if (inputBanner && previewBanner) {
    inputBanner.addEventListener('change', function() {
      const file = this.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          previewBanner.src = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    });
  }

  const modal = document.getElementById('modal-avatar');
  const btnCambiarAvatar = document.getElementById('cambiar-avatar');
  const cerrarModal = document.querySelector('.cerrar-modal');
  const cancelarAvatar = document.getElementById('cancelar-avatar');

  function abrirModalAvatar() {
    if (modal) modal.classList.add('show');
  }
  function cerrarModalAvatar() {
    if (modal) modal.classList.remove('show');
  }
  if (btnCambiarAvatar && modal) {
    btnCambiarAvatar.addEventListener('click', abrirModalAvatar);
  }
  if (cerrarModal && modal) {
    cerrarModal.addEventListener('click', cerrarModalAvatar);
  }
  if (cancelarAvatar && modal) {
    cancelarAvatar.addEventListener('click', cerrarModalAvatar);
  }
  if (modal) {
    window.addEventListener('click', function(event) {
      if (event.target === modal) cerrarModalAvatar();
    });
  }
}

document.addEventListener('DOMContentLoaded', function() {
  if (
    document.querySelector('.perfil-container') ||
    document.querySelector('.editar-perfil-container')
  ) {
    initializeUsuarioPage();
  }
});

// === 15. Funciones específicas para la página de categorías ===

document.addEventListener('DOMContentLoaded', function() {
  if (typeof AOS !== "undefined" && AOS.init) {
    AOS.init({
      duration: 800,
      easing: 'ease-out-cubic',
      once: true,
      offset: 100
    });
  }
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      if (this.hash && document.querySelector(this.hash)) {
        e.preventDefault();
        const target = document.querySelector(this.hash);
        target.scrollIntoView({
          behavior: 'smooth',
          block: 'start'
        });
      }
    });
  });

  window.addEventListener('scroll', function() {
    const scrolled = window.pageYOffset;
    const parallax = document.querySelectorAll('.floating-cat');
    const speed = 0.5;
    parallax.forEach(element => {
      const yPos = -(scrolled * speed);
      element.style.transform = `translateY(${yPos}px)`;
    });
  });
});

// =============================
// 16. Exportar funciones globales
// =============================
window.InkVerse = {
  toggleFavorito,
  quitarDeFavoritos,
  showAlert,
  formatDate,
  truncateText,
  updateFavoritoButton,
};

window.toggleFavorito = toggleFavorito;
window.quitarDeFavoritos = quitarDeFavoritos;
window.showAlert = showAlert;