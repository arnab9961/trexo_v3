<?php
require_once 'includes/header.php';
require_once 'includes/navbar.php';
// Process contact form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $subject = sanitize_input($_POST['subject']);
    $message = sanitize_input($_POST['message']);
    $error = '';
    
    // Validate input
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Insert inquiry
        $query = "INSERT INTO inquiries (name, email, subject, message) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $subject, $message);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_message'] = 'Your message has been sent successfully. We will get back to you soon!';
            redirect('contact.php');
        } else {
            $error = 'Failed to send message. Please try again later.';
        }
    }
}
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <h1>Contact Us</h1>
        <p>Have questions or need assistance? We're here to help!</p>
    </div>
</section>

<br>
<br>

<div class="container">
    <div class="row mb-5">
        <div class="col-md-6">
            <div class="card mb-4 shadow-sm border-0 h-100">
                <div class="card-body p-4">
                    <h3 class="mb-4 border-bottom pb-3">Get in Touch</h3>
                    
                    <?php if (isset($error) && !empty($error)): ?>
                        <?php echo display_error($error); ?>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="contact-form">
                        <div class="mb-3">
                            <label for="name" class="form-label">Your Name *</label>
                            <input type="text" class="form-control" id="name" name="name" required value="<?php echo isset($name) ? $name : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="email" name="email" required value="<?php echo isset($email) ? $email : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject *</label>
                            <input type="text" class="form-control" id="subject" name="subject" required value="<?php echo isset($subject) ? $subject : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message *</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required><?php echo isset($message) ? $message : ''; ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg w-100">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card mb-4 shadow-sm border-0 h-100">
                <div class="card-body p-4">
                    <h3 class="mb-4 border-bottom pb-3">Contact Information</h3>
                    <p class="lead mb-4">Feel free to reach out to us using any of the following contact methods:</p>
                    
                    <div class="contact-item mb-4 d-flex align-items-center">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="contact-details">
                            <h5>Address</h5>
                            <p class="mb-0">Rainkhola, Mirpur 1, Dhaka, Bangladesh</p>
                        </div>
                    </div>
                    
                    <div class="contact-item mb-4 d-flex align-items-center">
                        <div class="contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="contact-details">
                            <h5>Phone</h5>
                            <p class="mb-0">+880 1939424320</p>
                        </div>
                    </div>
                    
                    <div class="contact-item mb-4 d-flex align-items-center">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="contact-details">
                            <h5>Email</h5>
                            <p class="mb-0">info@trexo.com</p>
                        </div>
                    </div>
                    
                    <div class="contact-item mb-4 d-flex align-items-center">
                        <div class="contact-icon">
                            <i class="far fa-clock"></i>
                        </div>
                        <div class="contact-details">
                            <h5>Business Hours</h5>
                            <p class="mb-0">Saturday - Tuesday: 9:00 AM - 6:00 PM<br>
                            Wednesday - Thursday: 10:00 AM - 4:00 PM<br>
                            Friday: Closed</p>
                        </div>
                    </div>
                    
                    <div class="social-icons mt-5 text-center">
                        <h5 class="mb-3">Connect With Us</h5>
                        <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Google Map (Dhaka, Bangladesh) -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="ratio ratio-16x9">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d233668.38703692693!2d90.27923991057244!3d23.780573258035957!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3755b8b087026b81%3A0x8fa563bbdd5904c2!2sDhaka%2C%20Bangladesh!5e0!3m2!1sen!2sus!4v1646579736822!5m2!1sen!2sus" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>

<style>
    .contact-item {
        transition: all 0.3s ease;
    }
    
    .contact-item:hover {
        transform: translateX(5px);
    }
    
    .contact-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #0d6efd, #0dcaf0);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 20px;
        box-shadow: 0 4px 10px rgba(13, 110, 253, 0.3);
        transition: all 0.3s ease;
    }
    
    .contact-icon i {
        font-size: 20px;
        color: white;
    }
    
    .contact-item:hover .contact-icon {
        transform: scale(1.1) rotate(10deg);
        box-shadow: 0 6px 15px rgba(13, 110, 253, 0.4);
    }
    
    .contact-details h5 {
        font-weight: 600;
        margin-bottom: 5px;
        color: #333;
    }
    
    .contact-details p {
        color: #666;
    }
    
    .social-icons {
        display: flex;
        gap: 15px;
        justify-content: center;
    }
    
    .social-icon {
        width: 45px;
        height: 45px;
        background: #f8f9fa;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #0d6efd;
        text-decoration: none;
        transition: all 0.3s ease;
        border: 1px solid #dee2e6;
        font-size: 1.2rem;
    }
    
    .social-icon:hover {
        background: #0d6efd;
        color: white;
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
    }
    
    .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    
    @media (max-width: 767.98px) {
        .social-icons {
            margin-top: 2rem;
        }
    }
</style> 