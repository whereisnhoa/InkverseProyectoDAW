
document.addEventListener('DOMContentLoaded', function() 
{
    // Manejo de dropdowns
    const dropdowns = document.querySelectorAll('.dropdown');
    
    dropdowns.forEach(dropdown => 
    {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        
        toggle.addEventListener('click', function(e) 
        {
            e.preventDefault();
            
            // Cerrar otros dropdowns
            dropdowns.forEach(otherDropdown => {
                if (otherDropdown !== dropdown && otherDropdown.classList.contains('active')) 
                {
                    otherDropdown.classList.remove('active');
                }
            });
            
            // Toggle actual dropdown
            dropdown.classList.toggle('active');
        });
    });
    
    // Cerrar dropdowns al hacer clic fuera
    document.addEventListener('click', function(e) 
    {
        if (!e.target.closest('.dropdown')) 
        {
            dropdowns.forEach(dropdown => {
                dropdown.classList.remove('active');
            });
        }
    });
    
    // Menú móvil
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const mobileMenu = document.querySelector('.mobile-menu');
    
    if (mobileMenuToggle && mobileMenu) 
    {
        mobileMenuToggle.addEventListener('click', function() 
        {
            mobileMenu.classList.toggle('active');
            
            // Cambiar icono
            const icon = mobileMenuToggle.querySelector('i');
            if (mobileMenu.classList.contains('active')) 
            {
                icon.className = 'fas fa-times';
                
            } else {
                icon.className = 'fas fa-bars';
            }
        });
    }
});