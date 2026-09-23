document.addEventListener("DOMContentLoaded", function () {
    // Check if IntersectionObserver is supported
    if ('IntersectionObserver' in window) {
        const animatedElements = document.querySelectorAll(
            '.step-card, .rate-card, .hero-section, .section-title, .content-container > div, .custom-navbar'
        );

        // Add base hidden state classes for animation
        animatedElements.forEach((el, index) => {
            el.classList.add('scroll-animate-init');
        });

        const observerOptions = {
            root: null,
            rootMargin: '0px 0px -50px 0px',
            threshold: 0.15
        };

        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('scroll-animate-active');
                    // Optional: stop observing once animated if you only want it to animate once
                    // observer.unobserve(entry.target);
                } else {
                    // Allows reverse smooth fade out when scrolling back up (optional, keep subtle)
                    if (entry.boundingClientRect.top > 0) {
                        entry.target.classList.remove('scroll-animate-active');
                    }
                }
            });
        }, observerOptions);

        animatedElements.forEach(el => observer.observe(el));
    }

    // Subtle Hero Image / Background Parallax on Scroll
    window.addEventListener('scroll', function () {
        const scrolled = window.pageYOffset;
        const hero = document.querySelector('.hero-section');
        if (hero) {
            hero.style.backgroundPositionY = -(scrolled * 0.15) + 'px';
        }
    });
});