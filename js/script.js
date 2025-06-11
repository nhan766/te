 // Simple script for FAQ accordion
        document.querySelectorAll('.faq-question').forEach(question => {
            question.addEventListener('click', () => {
                const item = question.parentNode;
                const isActive = item.classList.contains('active');
                
                // Close all items
                document.querySelectorAll('.faq-item').forEach(faqItem => {
                    faqItem.classList.remove('active');
                });
                
                // If the clicked item wasn't active, open it
                if (!isActive) {
                    item.classList.add('active');
                }
            });
        });
        
        // Rewards tab system
        document.querySelectorAll('.tab-btn').forEach(tab => {
            tab.addEventListener('click', () => {
                // Remove active class from all tabs
                document.querySelectorAll('.tab-btn').forEach(t => {
                    t.classList.remove('active');
                });
                
                // Add active class to clicked tab
                tab.classList.add('active');
                
                // Filter rewards based on selected tab
                const selectedCategory = tab.getAttribute('data-tab');
                
                if (selectedCategory === 'all-rewards') {
                    document.querySelectorAll('.reward-card').forEach(card => {
                        card.style.display = 'block';
                    });
                } else {
                    document.querySelectorAll('.reward-card').forEach(card => {
                        if (card.classList.contains(selectedCategory)) {
                            card.style.display = 'block';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                }
            });
        });
        // Search functionality
          const faders = document.querySelectorAll('.fade-in');
    const appearOptions = { threshold: 0.2 };
    const appearOnScroll = new IntersectionObserver(function(entries, observer) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, appearOptions);
    faders.forEach(fader => appearOnScroll.observe(fader));
        
        // Pagination
        document.querySelectorAll('.pagination-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.pagination-btn').forEach(b => {
                    b.classList.remove('active');
                });
                btn.classList.add('active');
                
                // Normally would load new rewards or change page
                // For demo, we'll just show the same rewards
            });
        });

        window.addEventListener('load', function() {
  const loader = document.querySelector('.loader');
  setTimeout(function() {
    loader.classList.add('hidden');
  }, 1000);
});


        