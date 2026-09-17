<?php
/**
 * About Us Page
 */

require_once 'includes/functions.php';

$page_title = 'About Us';
require_once 'includes/header.php';
?>

<!-- Page Header -->
<section class="bg-success text-white py-5">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1 class="display-4 fw-bold">About GreenBasket</h1>
                <p class="lead">Our mission to make sustainable living accessible to everyone</p>
            </div>
        </div>
    </div>
</section>

<!-- About Content -->
<section class="py-5">
    <div class="container">
        <div class="row align-items-center mb-5">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="bg-light rounded-3 p-5 text-center">
                    <i class="bi bi-basket2" style="font-size: 150px; color: var(--primary-green);"></i>
                </div>
            </div>
            <div class="col-lg-6">
                <h2 class="mb-4">Our Story</h2>
                <p class="mb-3">GreenBasket was founded with a simple yet powerful vision: to make sustainable living accessible and affordable for everyone. We believe that small changes in our daily choices can create a significant positive impact on our planet.</p>
                <p class="mb-3">What started as a small initiative has grown into a community of eco-conscious individuals who are committed to reducing their environmental footprint without compromising on quality or lifestyle.</p>
                <p>Every product in our store is carefully curated to ensure it meets our strict sustainability criteria. We work directly with ethical manufacturers and artisans who share our passion for the environment.</p>
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h2 class="mb-4">Our Mission</h2>
                <ul class="list-unstyled">
                    <li class="mb-3"><i class="bi bi-check-circle-fill text-success me-2"></i> Provide high-quality eco-friendly products at affordable prices</li>
                    <li class="mb-3"><i class="bi bi-check-circle-fill text-success me-2"></i> Educate and inspire people to adopt sustainable habits</li>
                    <li class="mb-3"><i class="bi bi-check-circle-fill text-success me-2"></i> Support ethical manufacturers and local artisans</li>
                    <li class="mb-3"><i class="bi bi-check-circle-fill text-success me-2"></i> Reduce plastic waste and promote circular economy</li>
                    <li class="mb-3"><i class="bi bi-check-circle-fill text-success me-2"></i> Build a community of environmentally conscious consumers</li>
                </ul>
            </div>
            <div class="col-lg-6">
                <h2 class="mb-4">Our Values</h2>
                <div class="card bg-light border-0 mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-leaf text-success me-2"></i>Sustainability</h5>
                        <p class="card-text mb-0">Every product we offer is designed with sustainability in mind, from sourcing to packaging.</p>
                    </div>
                </div>
                <div class="card bg-light border-0 mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-shield-check text-success me-2"></i>Quality</h5>
                        <p class="card-text mb-0">We never compromise on quality. Our products are durable, effective, and built to last.</p>
                    </div>
                </div>
                <div class="card bg-light border-0 mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-heart text-success me-2"></i>Transparency</h5>
                        <p class="card-text mb-0">We believe in complete transparency about our products, their origins, and their environmental impact.</p>
                    </div>
                </div>
                <div class="card bg-light border-0">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-people text-success me-2"></i>Community</h5>
                        <p class="card-text mb-0">We're building a community of like-minded individuals who support each other in their sustainability journey.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
