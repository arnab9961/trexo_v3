    </div> <!-- End of main container -->

    <!-- Scroll to Top Button -->
    <a href="#" class="scroll-to-top">
        <i class="fas fa-arrow-up"></i>
    </a>

    <!-- Footer -->
    <footer class="footer-section">
        <div class="footer-top">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4 col-md-6 mb-4 mb-md-0 footer-info">
                        <div class="footer-logo mb-3">
                            <img src="images/logo.png" alt="TREXO Logo" class="img-fluid footer-brand-logo">
                        </div>
                        <p>Your perfect travel companion for discovering amazing destinations around the world. We make travel dreams come true with exceptional service and unforgettable experiences.</p>
                        <div class="social-icons">
                            <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                            <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                            <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-6 mb-4 mb-md-0 footer-links">
                        <h5>Quick Links</h5>
                        <ul class="list-unstyled">
                            <li><a href="index.php">Home</a></li>
                            <li><a href="about.php">About Us</a></li>
                            <li><a href="destinations.php">Destinations</a></li>
                            <li><a href="packages.php">Packages</a></li>
                            <li><a href="contact.php">Contact</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4 mb-md-0 footer-links">
                        <h5>Popular Destinations</h5>
                        <ul class="list-unstyled">
                            <li><a href="destination_details.php?id=1">Cox's Bazar</a></li>
                            <li><a href="destination_details.php?id=2">Sundarbans</a></li>
                            <li><a href="destination_details.php?id=3">Sajek Valley</a></li>
                            <li><a href="destination_details.php?id=4">Bandarban</a></li>
                            <li><a href="destinations.php">View All</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4 mb-md-0 footer-contact">
                        <h5>Contact Us</h5>
                        <address>
                            <div class="d-flex mb-3">
                                <div class="contact-icon-wrapper">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="ms-3">
                                    <p class="mb-0">Rainkhola, Mirpur 1</p>
                                    <p class="mb-0">Dhaka, Bangladesh</p>
                                </div>
                            </div>
                            <div class="d-flex mb-3">
                                <div class="contact-icon-wrapper">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="ms-3">
                                    <p class="mb-0">+880 1939424320</p>
                                </div>
                            </div>
                            <div class="d-flex mb-3">
                                <div class="contact-icon-wrapper">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="ms-3">
                                    <p class="mb-0">info@trexo.com</p>
                                </div>
                            </div>
                            <div class="d-flex">
                                <div class="contact-icon-wrapper">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="ms-3">
                                    <p class="mb-0">Sat - Tue: 9:00 AM - 6:00 PM</p>
                                    <p class="mb-0">Wed - Thu: 10:00 AM - 4:00 PM</p>
                                    <p class="mb-0">Friday: Closed</p>
                                </div>
                            </div>
                        </address>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <p class="mb-md-0">&copy; <?php echo date('Y'); ?> TREXO. All rights reserved.</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="footer-links-bottom">
                            <a href="privacy.php">Privacy Policy</a>
                            <a href="terms.php">Terms of Service</a>
                            <a href="faq.php">FAQ</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="js/script.js"></script>

    <style>
    /* Footer Styles */
    .footer-section {
        background-color: #212529;
        color: rgba(255, 255, 255, 0.8);
        position: relative;
        overflow: hidden;
    }

    .footer-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary), var(--primary-dark));
    }

    .footer-top {
        padding: 4rem 0 3rem;
        position: relative;
    }
    
    .footer-middle {
        background-color: rgba(0, 0, 0, 0.15);
        border-top: 1px solid rgba(255, 255, 255, 0.05);
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .footer-bottom {
        background-color: rgba(0, 0, 0, 0.2);
        padding: 1.5rem 0;
        font-size: 0.9rem;
    }

    .footer-logo {
        margin-bottom: 1.5rem;
    }

    .footer-brand-logo {
        max-width: 150px;
        height: auto;
        filter: brightness(1) drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
        transition: all 0.3s ease;
    }

    .footer-brand-logo:hover {
        filter: brightness(1.1) drop-shadow(0 4px 8px rgba(13, 110, 253, 0.3));
        transform: scale(1.05);
    }

    .footer-section h5 {
        color: white;
        font-weight: 600;
        margin-bottom: 1.5rem;
        position: relative;
        padding-bottom: 0.8rem;
    }

    .footer-section h5::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 50px;
        height: 2px;
        background-color: var(--primary);
    }
    
    .footer-middle h5::after {
        left: 50%;
        transform: translateX(-50%);
    }

    .footer-links ul li {
        margin-bottom: 0.8rem;
        transition: all 0.3s ease;
    }

    .footer-links ul li a {
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-block;
        position: relative;
        padding-left: 1.2rem;
    }

    .footer-links ul li a::before {
        content: '→';
        position: absolute;
        left: 0;
        transition: all 0.3s ease;
    }

    .footer-links ul li a:hover {
        color: var(--primary);
        transform: translateX(5px);
    }

    .footer-links ul li a:hover::before {
        color: var(--primary);
    }

    .social-icons {
        display: flex;
        gap: 0.8rem;
        margin-top: 1.5rem;
    }

    .social-icons a {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        background-color: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        color: white;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .social-icons a:hover {
        background-color: var(--primary);
        color: white;
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    }

    .contact-icon-wrapper {
        width: 40px;
        height: 40px;
        background-color: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        flex-shrink: 0;
    }

    .footer-contact:hover .contact-icon-wrapper {
        background-color: var(--primary);
        transform: scale(1.1);
    }

    .contact-icon-wrapper i {
        font-size: 18px;
        color: white;
    }

    .footer-links-bottom {
        display: flex;
        justify-content: flex-end;
        gap: 1.5rem;
    }

    .footer-links-bottom a {
        color: rgba(255, 255, 255, 0.7);
        text-decoration: none;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }

    .footer-links-bottom a:hover {
        color: var(--primary);
    }
    
    .newsletter-form .form-control {
        background-color: rgba(255, 255, 255, 0.1);
        border: none;
        color: white;
        padding: 0.75rem 1.25rem;
        border-radius: 30px 0 0 30px;
    }
    
    .newsletter-form .form-control::placeholder {
        color: rgba(255, 255, 255, 0.6);
    }
    
    .newsletter-form .form-control:focus {
        background-color: rgba(255, 255, 255, 0.15);
        box-shadow: none;
    }
    
    .newsletter-form .btn {
        border-radius: 0 30px 30px 0;
        padding-left: 1.5rem;
        padding-right: 1.5rem;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    
    .payment-methods {
        font-size: 1.5rem;
        color: rgba(255, 255, 255, 0.7);
    }
    
    .payment-methods i {
        transition: all 0.3s ease;
    }
    
    .payment-methods i:hover {
        color: var(--primary);
        transform: translateY(-3px);
    }

    /* Scroll to Top Button */
    .scroll-to-top {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 50px;
        height: 50px;
        background-color: var(--primary);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        z-index: 999;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }

    .scroll-to-top.visible {
        opacity: 1;
        visibility: visible;
    }

    .scroll-to-top:hover {
        background-color: var(--primary-dark);
        transform: translateY(-5px);
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
        color: white;
    }

    @media (max-width: 991.98px) {
        .footer-top {
            padding: 3rem 0 2rem;
        }
        
        .footer-section h5 {
            margin-top: 1.5rem;
        }
    }

    @media (max-width: 767.98px) {
        .footer-section h5::after {
            left: 50%;
            transform: translateX(-50%);
        }
        
        .footer-section h5,
        .footer-links ul li a,
        .footer-info p,
        .footer-contact address {
            text-align: center;
        }
        
        .footer-links ul li a {
            padding-left: 0;
        }
        
        .footer-links ul li a::before {
            display: none;
        }
        
        .social-icons {
            justify-content: center;
        }
        
        .footer-contact .d-flex {
            flex-direction: column;
            align-items: center;
        }
        
        .contact-icon-wrapper {
            margin-bottom: 0.5rem;
        }
        
        .footer-links-bottom {
            justify-content: center;
            margin-top: 1rem;
        }
        
        .footer-bottom .text-md-end {
            text-align: center !important;
        }
    }
    </style>

    <script>
    // Scroll to Top Button Functionality
    document.addEventListener('DOMContentLoaded', function() {
        const scrollToTopBtn = document.querySelector('.scroll-to-top');
        
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                scrollToTopBtn.classList.add('visible');
            } else {
                scrollToTopBtn.classList.remove('visible');
            }
        });
        
        scrollToTopBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    });
    </script>
</body>
</html> 