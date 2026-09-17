<?php
/**
 * Contact Us Page
 */

require_once 'includes/functions.php';

$page_title = 'Contact Us';
require_once 'includes/header.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize_input($_POST['name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $subject = sanitize_input($_POST['subject'] ?? '');
    $message = sanitize_input($_POST['message'] ?? '');

    // Validation
    if (empty($name)) {
        $errors[] = 'Name is required';
    }
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!validate_email($email)) {
        $errors[] = 'Invalid email format';
    }
    if (empty($subject)) {
        $errors[] = 'Subject is required';
    }
    if (empty($message)) {
        $errors[] = 'Message is required';
    }

    // Submit message if no errors
    if (empty($errors)) {
        $sql = "INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)";
        
        if (prepared_execute($sql, "ssss", [$name, $email, $subject, $message])) {
            $success = true;
            set_flash_message('success', 'Thank you for contacting us! We will get back to you soon.');
            redirect(SITE_URL . '/contact.php');
        } else {
            $errors[] = 'Failed to submit message. Please try again.';
        }
    }
}
?>

<!-- Page Header -->
<section class="bg-success text-white py-5">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1 class="display-4 fw-bold">Contact Us</h1>
                <p class="lead">We'd love to hear from you</p>
            </div>
        </div>
    </div>
</section>

<!-- Contact Content -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Contact Information -->
            <div class="col-lg-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h3 class="mb-4">Get in Touch</h3>
                        
                        <div class="mb-4">
                            <div class="d-flex align-items-start">
                                <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; font-size: 20px;">
                                    <i class="bi bi-geo-alt"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1">Address</h5>
                                    <p class="text-muted mb-0">123 Anandanagar Street<br>Dhumbarahi<br>Kathmandu 44600</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="d-flex align-items-start">
                                <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; font-size: 20px;">
                                    <i class="bi bi-telephone"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1">Phone</h5>
                                    <p class="text-muted mb-0">+977 9876543210<br>+977 9876543211</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="d-flex align-items-start">
                                <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; font-size: 20px;">
                                    <i class="bi bi-envelope"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1">Email</h5>
                                    <p class="text-muted mb-0">info@greenbasket.com<br>support@greenbasket.com</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="d-flex align-items-start">
                                <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; font-size: 20px;">
                                    <i class="bi bi-clock"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1">Business Hours</h5>
                                    <p class="text-muted mb-0">Mon - Fri: 9:00 AM - 6:00 PM<br>Sat: 10:00 AM - 4:00 PM<br>Sun: Closed</p>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <h5 class="mb-3">Follow Us</h5>
                            <div class="social-links">
                                <a href="#" class="btn btn-outline-success btn-sm me-2"><i class="bi bi-facebook"></i></a>
                                <a href="#" class="btn btn-outline-success btn-sm me-2"><i class="bi bi-twitter"></i></a>
                                <a href="#" class="btn btn-outline-success btn-sm me-2"><i class="bi bi-instagram"></i></a>
                                <a href="#" class="btn btn-outline-success btn-sm me-2"><i class="bi bi-linkedin"></i></a>
                                <a href="#" class="btn btn-outline-success btn-sm"><i class="bi bi-youtube"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Contact Form -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="mb-4">Send us a Message</h3>
                        
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="subject" class="form-label">Subject *</label>
                                <input type="text" class="form-control" id="subject" name="subject" 
                                       value="<?php echo htmlspecialchars($subject ?? ''); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="message" class="form-label">Message *</label>
                                <textarea class="form-control" id="message" name="message" rows="6" required><?php echo htmlspecialchars($message ?? ''); ?></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-success btn-lg">Send Message</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="section-title">Frequently Asked Questions</h2>
        <p class="section-subtitle">Quick answers to common questions</p>
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item mb-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                What is your return policy?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                We accept returns within 30 days of purchase for unused products in original packaging. Contact our support team to initiate a return.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item mb-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                How long does shipping take?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Standard shipping takes 5-7 business days. Express shipping (2-3 days) is available for an additional fee. Free shipping is available on orders above Rs999.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item mb-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Are your products really eco-friendly?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes! Every product is carefully vetted to ensure it meets our strict sustainability criteria. We work directly with ethical manufacturers and provide transparency about materials and sourcing.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item mb-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                How do Eco Points work?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                You earn 1 Eco Point for every Rs10 spent on purchases. Points can be tracked in your dashboard and redeemed for discounts on future orders.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                Do you ship internationally?
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Currently, we only ship within Nepal. We're working on expanding our shipping to international markets. Stay tuned for updates!
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>