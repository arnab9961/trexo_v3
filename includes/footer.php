    </div> <!-- End of main container -->

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <h5>Tourism Management System</h5>
                    <p>Your perfect travel companion for discovering amazing destinations around the world.</p>
                    <div class="social-icons">
                        <a href="#" class="text-white me-2"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-2"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-white">Home</a></li>
                        <li><a href="destinations.php" class="text-white">Destinations</a></li>
                        <li><a href="packages.php" class="text-white">Packages</a></li>
                        <li><a href="about.php" class="text-white">About Us</a></li>
                        <li><a href="contact.php" class="text-white">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-3">
                    <h5>Contact Us</h5>
                    <address>
                        <div class="d-flex align-items-start mb-3">
                            <div class="contact-icon-wrapper me-3">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <p class="mb-0">123 Gulshan Avenue, Dhaka, Bangladesh</p>
                        </div>
                        <div class="d-flex align-items-start mb-3">
                            <div class="contact-icon-wrapper me-3">
                                <i class="fas fa-phone"></i>
                            </div>
                            <p class="mb-0">+880 1234 567890</p>
                        </div>
                        <div class="d-flex align-items-start mb-3">
                            <div class="contact-icon-wrapper me-3">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <p class="mb-0">info@tourismmanagement.com</p>
                        </div>
                        <div class="d-flex align-items-start">
                            <div class="contact-icon-wrapper me-3">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div>
                                <p class="mb-0">Monday - Friday: 9:00 AM - 6:00 PM</p>
                                <p class="mb-0">Saturday: 10:00 AM - 4:00 PM</p>
                                <p class="mb-0">Sunday: Closed</p>
                            </div>
                        </div>
                    </address>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p>&copy; <?php echo date('Y'); ?> Tourism Management System. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="js/script.js"></script>
    
    <style>
        .contact-icon-wrapper {
            width: 40px;
            height: 40px;
            background-color: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            margin-top: 3px;
        }
        
        .contact-icon-wrapper:hover {
            background-color: #0d6efd;
            transform: scale(1.1);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }
        
        .contact-icon-wrapper i {
            font-size: 18px;
            color: #fff;
        }
        
        footer address p {
            color: rgba(255, 255, 255, 0.8);
        }
        
        footer address div:hover p {
            color: #fff;
        }
    </style>
</body>
</html> 