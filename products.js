// Product Filtering
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        // Remove active class from all buttons
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        // Add active class to clicked button
        btn.classList.add('active');

        // Get filter value
        const filterValue = btn.getAttribute('data-filter');

        // Filter products
        document.querySelectorAll('.filter-item').forEach(item => {
            if (filterValue === 'all') {
                item.style.display = 'block';
            } else {
                if (item.getAttribute('data-slug') === filterValue) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            }
        });
    });
});

// Navigation hamburger menu
document.querySelector('.hamburger')?.addEventListener('click', () => {
    document.querySelector('.nav-menu')?.classList.toggle('active');
});

// Close menu when a link is clicked
document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', () => {
        document.querySelector('.nav-menu')?.classList.remove('active');
    });
});
