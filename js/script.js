// Tourism Management System - JavaScript
console.log('Tourism script loaded successfully!');

// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM fully loaded - initializing features');
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.style.display = 'none';
            }, 500);
        }, 5000);
    });

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Booking form validation
    const bookingForm = document.getElementById('booking-form');
    if (bookingForm) {
        bookingForm.addEventListener('submit', function(event) {
            const travelDate = document.getElementById('travel-date');
            const numTravelers = document.getElementById('num-travelers');
            
            // Check if travel date is in the future
            if (new Date(travelDate.value) <= new Date()) {
                event.preventDefault();
                alert('Travel date must be in the future.');
                return false;
            }
            
            // Check if number of travelers is valid
            if (parseInt(numTravelers.value) < 1) {
                event.preventDefault();
                alert('Number of travelers must be at least 1.');
                return false;
            }
            
            return true;
        });
    }

    // Contact form validation
    const contactForm = document.getElementById('contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', function(event) {
            const email = document.getElementById('email');
            const message = document.getElementById('message');
            
            // Simple email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email.value)) {
                event.preventDefault();
                alert('Please enter a valid email address.');
                return false;
            }
            
            // Check message length
            if (message.value.length < 10) {
                event.preventDefault();
                alert('Message must be at least 10 characters long.');
                return false;
            }
            
            return true;
        });
    }

    // Image gallery lightbox (for destination details)
    const galleryImages = document.querySelectorAll('.gallery-image');
    if (galleryImages.length > 0) {
        // Create lightbox elements if they don't exist
        if (!document.getElementById('lightbox-container')) {
            const lightboxContainer = document.createElement('div');
            lightboxContainer.id = 'lightbox-container';
            lightboxContainer.className = 'lightbox-container';
            lightboxContainer.innerHTML = `
                <div class="lightbox-content">
                    <span class="lightbox-close">&times;</span>
                    <img id="lightbox-image" class="lightbox-image">
                    <div class="lightbox-caption"></div>
                </div>
            `;
            document.body.appendChild(lightboxContainer);
            
            // Add CSS for lightbox
            const style = document.createElement('style');
            style.textContent = `
                .lightbox-container {
                    display: none;
                    position: fixed;
                    z-index: 9999;
                    left: 0;
                    top: 0;
                    width: 100%;
                    height: 100%;
                    background-color: rgba(0, 0, 0, 0.9);
                    opacity: 0;
                    transition: opacity 0.3s ease;
                }
                .lightbox-content {
                    position: relative;
                    margin: auto;
                    padding: 0;
                    width: 80%;
                    max-width: 1200px;
                    height: 100%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    flex-direction: column;
                }
                .lightbox-image {
                    max-width: 90%;
                    max-height: 80vh;
                    object-fit: contain;
                    border: 10px solid white;
                    box-shadow: 0 0 20px rgba(0,0,0,0.3);
                    transform: scale(0.95);
                    transition: transform 0.3s ease;
                }
                .lightbox-container.active .lightbox-image {
                    transform: scale(1);
                }
                .lightbox-caption {
                    color: white;
                    margin-top: 10px;
                    font-size: 16px;
                }
                .lightbox-close {
                    position: absolute;
                    top: 20px;
                    right: 35px;
                    color: #f1f1f1;
                    font-size: 40px;
                    font-weight: bold;
                    cursor: pointer;
                    z-index: 10000;
                    transition: 0.3s;
                }
                .lightbox-close:hover {
                    color: #bbb;
                    transform: scale(1.2);
                }
            `;
            document.head.appendChild(style);
            
            // Add close functionality
            const lightboxClose = document.querySelector('.lightbox-close');
            const lightboxElement = document.getElementById('lightbox-container');
            
            lightboxClose.addEventListener('click', function() {
                closeLightbox();
            });
            
            // Close on clicking outside the image
            lightboxElement.addEventListener('click', function(e) {
                if (e.target === lightboxElement) {
                    closeLightbox();
                }
            });
            
            // Close on ESC key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeLightbox();
                }
            });
            
            // Helper function to close lightbox
            function closeLightbox() {
                lightboxElement.style.opacity = '0';
                lightboxElement.classList.remove('active');
                setTimeout(function() {
                    lightboxElement.style.display = 'none';
                }, 300);
            }
        }
        
        // Set up click handlers for all gallery images
        galleryImages.forEach(function(image) {
            image.addEventListener('click', function(e) {
                e.preventDefault();
                const imgSrc = this.href || this.getAttribute('href');
                const imgAlt = this.querySelector('img').alt || '';
                
                console.log('Gallery image clicked:', imgSrc); // Debug
                
                const lightboxDisplay = document.getElementById('lightbox-container');
                const lightboxImage = document.getElementById('lightbox-image');
                const lightboxCaption = document.querySelector('.lightbox-caption');
                
                lightboxImage.src = imgSrc;
                lightboxCaption.textContent = imgAlt;
                
                lightboxDisplay.style.display = 'block';
                setTimeout(function() {
                    lightboxDisplay.style.opacity = '1';
                    lightboxDisplay.classList.add('active');
                }, 10);
            });
        });
    }

    // Price calculator for booking
    const calculatePrice = function() {
        const basePrice = parseFloat(document.getElementById('base-price').value);
        const numTravelers = parseInt(document.getElementById('num-travelers').value);
        const totalPriceElement = document.getElementById('total-price');
        
        if (!isNaN(basePrice) && !isNaN(numTravelers) && numTravelers > 0) {
            const totalPrice = basePrice * numTravelers;
            // Format the number with commas for thousands separator
            totalPriceElement.textContent = totalPrice.toLocaleString('en-IN');
        }
    };

    // Add event listeners for price calculation
    const numTravelersInput = document.getElementById('num-travelers');
    if (numTravelersInput) {
        numTravelersInput.addEventListener('change', calculatePrice);
        numTravelersInput.addEventListener('keyup', calculatePrice);
        // Initial calculation
        calculatePrice();
    }
}); 