// Initialize usage chart
document.addEventListener('DOMContentLoaded', function() {
    // Usage Chart
    const ctx = document.getElementById('usageChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: Array.from({length: 30}, (_, i) => {
                    const d = new Date();
                    d.setDate(d.getDate() - (29 - i));
                    return d.toLocaleDateString('id-ID', {day: 'numeric', month: 'short'});
                }),
                datasets: [{
                    label: 'API Requests',
                    data: Array(30).fill(0),
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }

    // Copy API key to clipboard
    document.querySelectorAll('.copy-btn').forEach(button => {
        button.addEventListener('click', function() {
            const apiKey = this.dataset.copy;
            navigator.clipboard.writeText(apiKey).then(() => {
                // Change button icon and text temporarily
                const icon = this.querySelector('i');
                icon.classList.remove('fa-copy');
                icon.classList.add('fa-check');
                
                setTimeout(() => {
                    icon.classList.remove('fa-check');
                    icon.classList.add('fa-copy');
                }, 2000);
            });
        });
    });

    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    // Check password strength
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        const strengthMeter = document.querySelector('.password-strength');
        
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            // Length check
            if (password.length >= 8) strength++;
            
            // Uppercase check
            if (/[A-Z]/.test(password)) strength++;
            
            // Lowercase check
            if (/[a-z]/.test(password)) strength++;
            
            // Number check
            if (/[0-9]/.test(password)) strength++;
            
            // Special character check
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            strengthMeter.innerHTML = '<div></div>';
            strengthMeter.className = 'password-strength';
            
            if (strength >= 4) {
                strengthMeter.classList.add('strong');
            } else if (strength >= 3) {
                strengthMeter.classList.add('medium');
            } else if (strength >= 1) {
                strengthMeter.classList.add('weak');
            }
        });
    }

    // Add loading animation to buttons
    const actionButtons = document.querySelectorAll('.btn[data-loading]');
    actionButtons.forEach(button => {
        button.addEventListener('click', function() {
            const loadingText = this.getAttribute('data-loading') || 'Loading...';
            const originalHTML = this.innerHTML;
            
            this.setAttribute('disabled', 'disabled');
            this.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>${loadingText}`;
            
            // Reset button after action (you should modify this based on your actual action duration)
            setTimeout(() => {
                this.removeAttribute('disabled');
                this.innerHTML = originalHTML;
            }, 2000);
        });
    });

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Add smooth scrolling to all links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });

    // Add fade-in animation to cards
    const observerOptions = {
        threshold: 0.1
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    document.querySelectorAll('.card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
        observer.observe(card);
    });
}); 