// Tourism Management System - JavaScript

// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    
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
        galleryImages.forEach(function(image) {
            image.addEventListener('click', function() {
                const modal = new bootstrap.Modal(document.getElementById('image-modal'));
                const modalImg = document.getElementById('modal-image');
                modalImg.src = this.src;
                modal.show();
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