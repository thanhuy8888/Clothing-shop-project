// Simple dropdown menu functionality
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle (if needed in future)
    const navItems = document.querySelectorAll('.nav-item');
    
    navItems.forEach(item => {
        const link = item.querySelector('a');
        if (link && item.querySelector('.dropdown-menu')) {
            link.addEventListener('click', function(e) {
                // Prevent default if on mobile
                if (window.innerWidth <= 768) {
                    e.preventDefault();
                    const menu = item.querySelector('.dropdown-menu');
                    if (menu) {
                        menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
                    }
                }
            });
        }
    });
    
    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = '#dc3545';
                } else {
                    field.style.borderColor = '';
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Vui lòng điền đầy đủ thông tin bắt buộc');
            }
        });
    });
    
    // Quantity input validation
    const qtyInputs = document.querySelectorAll('.qty-input');
    qtyInputs.forEach(input => {
        input.addEventListener('change', function() {
            const max = parseInt(this.getAttribute('max'));
            const min = parseInt(this.getAttribute('min'));
            let value = parseInt(this.value);
            
            if (value < min) value = min;
            if (max && value > max) value = max;
            
            this.value = value;
        });
    });
});

