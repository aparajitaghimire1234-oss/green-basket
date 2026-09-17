<?php
/**
 * Eco Tips Page
 * Daily environmental awareness tips
 */

require_once 'includes/functions.php';

$page_title = 'Eco Tips';
require_once 'includes/header.php';

$eco_tips = [
    [
        'icon' => 'droplet',
        'title' => 'Save Water Every Day',
        'tips' => [
            'Turn off the tap while brushing teeth - saves up to 8 gallons per day',
            'Fix leaky faucets immediately - a drip can waste 20+ gallons daily',
            'Take shorter showers - each minute saves 2.5 gallons',
            'Use a bucket for washing vehicles instead of running hose',
            'Collect rainwater for gardening'
        ]
    ],
    [
        'icon' => 'lightbulb',
        'title' => 'Energy Conservation',
        'tips' => [
            'Switch to LED bulbs - use 75% less energy and last 25x longer',
            'Unplug devices when not in use - phantom power accounts for 10% of energy use',
            'Use natural light whenever possible',
            'Set AC to 24-26°C - each degree saves 6% energy',
            'Air dry clothes instead of using dryer when possible'
        ]
    ],
    [
        'icon' => 'bag',
        'title' => 'Reduce Plastic Use',
        'tips' => [
            'Carry reusable bags - one bag replaces 700+ plastic bags annually',
            'Use reusable water bottles - stop buying single-use plastic bottles',
            'Choose products with minimal packaging',
            'Say no to plastic straws and cutlery',
            'Buy in bulk to reduce packaging waste'
        ]
    ],
    [
        'icon' => 'recycle',
        'title' => 'Recycle Right',
        'tips' => [
            'Learn your local recycling rules - different areas have different guidelines',
            'Clean containers before recycling - food contamination ruins batches',
            'Separate wet and dry waste at home',
            'Compost organic waste - reduces landfill by 30%',
            'Buy products made from recycled materials'
        ]
    ],
    [
        'icon' => 'bicycle',
        'title' => 'Green Transportation',
        'tips' => [
            'Walk or cycle for short distances - zero emissions and healthy',
            'Use public transport - reduces individual carbon footprint',
            'Carpool when possible - share rides, share emissions',
            'Maintain your vehicle properly - improves fuel efficiency',
            'Consider electric vehicles for your next purchase'
        ]
    ],
    [
        'icon' => 'flower1',
        'title' => 'Sustainable Gardening',
        'tips' => [
            'Start composting - turn kitchen waste into nutrient-rich soil',
            'Plant native species - require less water and maintenance',
            'Use organic fertilizers instead of chemicals',
            'Collect rainwater for plants',
            'Grow your own herbs and vegetables - reduces food miles'
        ]
    ],
    [
        'icon' => 'cart',
        'title' => 'Conscious Shopping',
        'tips' => [
            'Buy only what you need - reduces waste and saves money',
            'Choose quality over quantity - durable products last longer',
            'Support local businesses - reduces transportation emissions',
            'Buy second-hand when possible - gives items a second life',
            'Bring your own containers for bulk purchases'
        ]
    ],
    [
        'icon' => 'egg-fried',
        'title' => 'Sustainable Eating',
        'tips' => [
            'Eat more plant-based meals - reduces carbon footprint significantly',
            'Buy local and seasonal produce - supports local farmers',
            'Reduce food waste - plan meals and store properly',
            'Grow your own herbs and vegetables',
            'Choose organic when possible - better for you and the planet'
        ]
    ]
];
?>

<!-- Page Header -->
<section class="bg-success text-white py-5">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1 class="display-4 fw-bold">Eco Tips</h1>
                <p class="lead">Simple daily habits for a greener planet</p>
            </div>
        </div>
    </div>
</section>

<!-- Eco Tips Content -->
<section class="py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h2 class="mb-3">Daily Environmental Awareness Tips</h2>
                <p class="text-muted">Small actions, when done consistently, create significant positive impact on our environment.</p>
            </div>
        </div>

        <div class="row">
            <?php foreach ($eco_tips as $index => $tip): ?>
                <div class="col-lg-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px; font-size: 25px;">
                                    <i class="bi bi-<?php echo $tip['icon']; ?>"></i>
                                </div>
                                <h4 class="card-title mb-0"><?php echo htmlspecialchars($tip['title']); ?></h4>
                            </div>
                            <ul class="list-unstyled mb-0">
                                <?php foreach ($tip['tips'] as $tip_item): ?>
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                                        <?php echo htmlspecialchars($tip_item); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Impact Calculator Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card border-0 shadow">
                    <div class="card-body p-5">
                        <h3 class="text-center mb-4">Your Eco Impact Calculator</h3>
                        <p class="text-center text-muted mb-4">See how small changes add up to big impact over time</p>
                        
                        <div class="row text-center">
                            <div class="col-md-4 mb-4">
                                <div class="p-3 bg-success text-white rounded">
                                    <h4 class="display-6 fw-bold">365</h4>
                                    <p class="mb-0">Plastic bags saved per year</p>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="p-3 bg-success text-white rounded">
                                    <h4 class="display-6 fw-bold">2,920</h4>
                                    <p class="mb-0">Gallons of water saved per year</p>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="p-3 bg-success text-white rounded">
                                    <h4 class="display-6 fw-bold">500</h4>
                                    <p class="mb-0">kg CO2 reduced per year</p>
                                </div>
                            </div>
                        </div>
                        
                        <p class="text-center text-muted small mt-4">*Based on average daily eco-friendly habits</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>