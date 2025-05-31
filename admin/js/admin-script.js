document.addEventListener('DOMContentLoaded', function() {
    // Add animation classes to elements
    document.querySelectorAll('.card').forEach(card => {
        card.classList.add('animate-fade-in');
    });
    
    // Progress bars animation
    document.querySelectorAll('.progress-bar').forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0';
        setTimeout(() => {
            bar.style.width = width;
        }, 100);
    });
    
    // File input display
    const fileInput = document.getElementById('timetable_file');
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : 'No file chosen';
            document.querySelector('.file-input-name').textContent = fileName;
        });
    }
    
    // Alert auto-dismiss
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });
    
    // Table row hover effect
    document.querySelectorAll('tbody tr').forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.classList.add('hover');
        });
        row.addEventListener('mouseleave', function() {
            this.classList.remove('hover');
        });
    });
    
    // Responsive sidebar toggle
    const mediaQuery = window.matchMedia('(max-width: 768px)');
    if (mediaQuery.matches) {
        const sidebarLinks = document.querySelectorAll('.sidebar-link');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (!this.classList.contains('active') && !this.href.includes('logout.php')) {
                    document.querySelector('.sidebar-nav').classList.add('collapsed');
                }
            });
        });
        
        document.querySelector('.admin-title').addEventListener('click', function() {
            document.querySelector('.sidebar-nav').classList.toggle('collapsed');
        });
    }
});