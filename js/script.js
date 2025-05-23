
/**
 * Script principal para InkVerse
 * Maneja la funcionalidad del frontend
 */

// Variables globales
let currentTheme = localStorage.getItem('theme') || 'light';

// Inicialización cuando el DOM está listo
document.addEventListener('DOMContentLoaded', function() {
    initializeTheme();
    initializeDropdowns();
    initializeMobileMenu();
    initializeNotifications();
    initializeRandomContent();
    initializeForms();
});

/**
 * Inicializar tema oscuro/claro
 */
function initializeTheme() {
    const themeToggle = document.querySelector('.dark-mode-toggle');
    const body = document.body;
    
    // Aplicar tema guardado
    if (currentTheme === 'dark') {
        body.setAttribute('data-theme', 'dark');
        if (themeToggle) {
            themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
        }
    }
    
    // Event listener para cambio de tema
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            if (currentTheme === 'light') {
                currentTheme = 'dark';
                body.setAttribute('data-theme', 'dark');
                this.innerHTML = '<i class="fas fa-sun"></i>';
            } else {
                currentTheme = 'light';
                body.removeAttribute('data-theme');
                this.innerHTML = '<i class="fas fa-moon"></i>';
            }
            
            localStorage.setItem('theme', currentTheme);
        });
    }
}

/**
 * Inicializar dropdowns
 */
function initializeDropdowns() {
    const dropdowns = document.querySelectorAll('.dropdown');
    
    dropdowns.forEach(dropdown => {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        const content = dropdown.querySelector('.dropdown-content');
        
        if (toggle && content) {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Cerrar otros dropdowns
                dropdowns.forEach(otherDropdown => {
                    if (otherDropdown !== dropdown) {
                        otherDropdown.classList.remove('active');
                    }
                });
                
                // Toggle current dropdown
                dropdown.classList.toggle('active');
            });
        }
    });
    
    // Cerrar dropdowns al hacer click fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
            dropdowns.forEach(dropdown => {
                dropdown.classList.remove('active');
            });
        }
    });
    
    // Cerrar dropdowns con Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            dropdowns.forEach(dropdown => {
                dropdown.classList.remove('active');
            });
        }
    });
}

/**
 * Inicializar menú móvil
 */
function initializeMobileMenu() {
    const mobileToggle = document.querySelector('.mobile-menu-toggle');
    const mobileMenu = document.querySelector('.mobile-nav-menu');
    const mobileOverlay = document.querySelector('.mobile-nav-overlay');
    const closeBtn = document.querySelector('.mobile-nav-menu .close-btn');
    const mobileDropdowns = document.querySelectorAll('.dropdown-mobile');
    
    // Abrir menú móvil
    if (mobileToggle && mobileMenu && mobileOverlay) {
        mobileToggle.addEventListener('click', function() {
            mobileMenu.classList.add('active');
            mobileOverlay.style.display = 'block';
            document.body.style.overflow = 'hidden';
        });
        
        // Cerrar menú móvil
        function closeMobileMenu() {
            mobileMenu.classList.remove('active');
            mobileOverlay.style.display = 'none';
            document.body.style.overflow = '';
        }
        
        if (closeBtn) {
            closeBtn.addEventListener('click', closeMobileMenu);
        }
        
        mobileOverlay.addEventListener('click', closeMobileMenu);
        
        // Dropdowns móviles
        mobileDropdowns.forEach(dropdown => {
            const toggle = dropdown.querySelector('.dropdown-toggle');
            const content = dropdown.querySelector('.dropdown-mobile-content');
            
            if (toggle && content) {
                toggle.addEventListener('click', function() {
                    dropdown.classList.toggle('active');
                });
            }
        });
    }
}

/**
 * Inicializar sistema de notificaciones
 */
function initializeNotifications() {
    // Marcar notificaciones como leídas al abrir el dropdown
    const notificationDropdown = document.querySelector('.notification-dropdown');
    if (notificationDropdown) {
        const toggle = notificationDropdown.querySelector('.dropdown-toggle');
        if (toggle) {
            toggle.addEventListener('click', function() {
                // Aquí podrías hacer una llamada AJAX para marcar como leídas
                setTimeout(() => {
                    const badge = this.querySelector('.notification-badge');
                    if (badge) {
                        badge.style.display = 'none';
                    }
                }, 1000);
            });
        }
    }
}

/**
 * Inicializar contenido aleatorio
 */
function initializeRandomContent() {
    const randomButtons = document.querySelectorAll('[data-random-type]');
    const randomContainer = document.querySelector('.random-obra-container');
    
    randomButtons.forEach(button => {
        button.addEventListener('click', function() {
            const type = this.getAttribute('data-random-type');
            loadRandomContent(type, randomContainer);
        });
    });
}

/**
 * Cargar contenido aleatorio
 */
function loadRandomContent(type, container) {
    if (!container) return;
    
    // Mostrar spinner
    container.innerHTML = '<div class="spinner"></div>';
    
    // Hacer petición AJAX
    fetch(`${BASE_URL}/controllers/contenido-aleatorio.php?tipo=${type}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.obra) {
                displayRandomContent(data.obra, container);
            } else {
                container.innerHTML = `
                    <div class="text-center">
                        <p>${TEXTOS_JS.no_obras_disponibles || 'No hay obras disponibles.'}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = `
                <div class="text-center">
                    <p>Error al cargar el contenido.</p>
                </div>
            `;
        });
}

/**
 * Mostrar contenido aleatorio
 */
function displayRandomContent(obra, container) {
    const adultBadge = obra.publico_adulto && !IS_USER_MINOR ? 
        '<span class="badge badge-warning">+18</span>' : '';
    
    container.innerHTML = `
        <div class="random-obra-card">
            <div class="random-obra-image">
                <img src="${obra.portada}" alt="${obra.titulo}" loading="lazy">
            </div>
            <div class="random-obra-content">
                <h3>${obra.titulo} ${adultBadge}</h3>
                <p class="random-obra-author">${TEXTOS_JS.por || 'Por'} ${obra.autor}</p>
                <p class="random-obra-description">${obra.descripcion}</p>
                <div class="random-obra-meta">
                    <span class="categoria">${obra.categoria}</span>
                    <span class="fecha">${obra.fecha_subida}</span>
                </div>
                <div class="random-obra-actions">
                    <a href="${BASE_URL}/public/obra.php?id=${obra.id}&tipo=${obra.tipo}" class="btn btn-accent">
                        ${TEXTOS_JS.ver_obra || 'Ver Obra'}
                    </a>
                    <button onclick="loadRandomContent('${obra.tipo}', this.closest('.random-obra-container'))" class="btn btn-outline">
                        ${TEXTOS_JS.otra_obra_aleatoria || 'Otra Obra'}
                    </button>
                </div>
            </div>
        </div>
    `;
}

/**
 * Inicializar formularios
 */
function initializeForms() {
    // Validación en tiempo real
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
                if (this.classList.contains('is-invalid')) {
                    validateField(this);
                }
            });
        });
        
        form.addEventListener('submit', function(e) {
            let isValid = true;
            inputs.forEach(input => {
                if (!validateField(input)) {
                    isValid = false;
                }
            });
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    });
}

/**
 * Validar campo individual
 */
function validateField(field) {
    const value = field.value.trim();
    const type = field.type;
    const required = field.hasAttribute('required');
    let isValid = true;
    let message = '';
    
    // Limpiar errores previos
    field.classList.remove('is-invalid');
    const existingFeedback = field.parentNode.querySelector('.invalid-feedback');
    if (existingFeedback) {
        existingFeedback.remove();
    }
    
    // Validar campo requerido
    if (required && !value) {
        isValid = false;
        message = 'Este campo es obligatorio.';
    }
    
    // Validaciones específicas por tipo
    if (value && !isValid === false) {
        switch (type) {
            case 'email':
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    isValid = false;
                    message = 'Introduce un email válido.';
                }
                break;
                
            case 'password':
                if (value.length < 6) {
                    isValid = false;
                    message = 'La contraseña debe tener al menos 6 caracteres.';
                }
                break;
                
            case 'url':
                try {
                    new URL(value);
                } catch {
                    isValid = false;
                    message = 'Introduce una URL válida.';
                }
                break;
        }
    }
    
    // Validación personalizada
    const pattern = field.getAttribute('pattern');
    if (pattern && value && !new RegExp(pattern).test(value)) {
        isValid = false;
        message = field.getAttribute('data-error-message') || 'Formato no válido.';
    }
    
    // Mostrar error si no es válido
    if (!isValid) {
        field.classList.add('is-invalid');
        const feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        feedback.textContent = message;
        field.parentNode.appendChild(feedback);
    }
    
    return isValid;
}

/**
 * Utilidades generales
 */

// Función para hacer peticiones AJAX
function makeRequest(url, options = {}) {
    const defaultOptions = {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        },
    };
    
    return fetch(url, { ...defaultOptions, ...options })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        });
}

// Función para mostrar alertas
function showAlert(message, type = 'info') {
    const alertContainer = document.querySelector('.alert-container') || document.body;
    const alert = document.createElement('div');
    alert.className = `alerta alerta-${type} fade-in`;
    alert.textContent = message;
    
    alertContainer.insertBefore(alert, alertContainer.firstChild);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        alert.style.opacity = '0';
        setTimeout(() => {
            if (alert.parentNode) {
                alert.parentNode.removeChild(alert);
            }
        }, 300);
    }, 5000);
}

// Función para formatear fechas
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

// Función para truncar texto
function truncateText(text, maxLength) {
    if (text.length <= maxLength) return text;
    return text.substr(0, maxLength) + '...';
}

// Lazy loading para imágenes
function initializeLazyLoading() {
    const images = document.querySelectorAll('img[loading="lazy"]');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src || img.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        images.forEach(img => imageObserver.observe(img));
    }
}

// Inicializar lazy loading cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', initializeLazyLoading);

// Función para manejar favoritos
function toggleFavorito(obraId, tipo) {
    makeRequest(`${BASE_URL}/controllers/procesar-favoritos.php`, {
        method: 'POST',
        body: JSON.stringify({
            obra_id: obraId,
            tipo: tipo
        })
    })
    .then(data => {
        if (data.success) {
            const button = document.querySelector(`[data-obra-id="${obraId}"]`);
            if (button) {
                const icon = button.querySelector('i');
                if (data.status === 'added') {
                    icon.className = 'fas fa-heart';
                    button.classList.add('favorited');
                    showAlert('Añadido a favoritos', 'success');
                } else {
                    icon.className = 'far fa-heart';
                    button.classList.remove('favorited');
                    showAlert('Eliminado de favoritos', 'info');
                }
            }
        } else {
            showAlert('Error al procesar favorito', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error de conexión', 'error');
    });
}

// Exportar funciones para uso global
window.InkVerse = {
    toggleFavorito,
    showAlert,
    makeRequest,
    formatDate,
    truncateText
};