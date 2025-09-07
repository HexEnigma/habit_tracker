<?php
require_once 'config.php';
$page_title = "Contact Us - HabitTracker";
$csrf_token = generate_csrf_token();

// Handle contact form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    $errors = [];

    // Validation
    if (empty($name)) $errors['name'] = 'Name is required';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Valid email is required';
    if (empty($subject)) $errors['subject'] = 'Subject is required';
    if (empty($message)) $errors['message'] = 'Message is required';

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO user_queries (name, email, subject, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $subject, $message]);
            $success = "Thank you for your message! We'll get back to you within 24 hours.";
        } catch (PDOException $e) {
            $errors['general'] = "Sorry, we couldn't send your message. Please try again later.";
        }
    }
}

require_once 'header.php';
?>

<div class="max-w-4xl mx-auto">
    <div class="text-center mb-12">
        <h1 class="text-3xl font-bold text-gray-800 mb-4">Contact Us</h1>
        <p class="text-gray-600">Have questions or need support? We're here to help!</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Contact Form -->
        <div class="app-card p-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-6">Send us a Message</h2>

            <?php if (isset($success)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($errors['general'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $errors['general']; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                    <input type="text" name="name" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                        value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                    <?php if (isset($errors['name'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?php echo $errors['name']; ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                    <input type="email" name="email" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                    <?php if (isset($errors['email'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?php echo $errors['email']; ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Subject *</label>
                    <input type="text" name="subject" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                        value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>" required>
                    <?php if (isset($errors['subject'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?php echo $errors['subject']; ?></p>
                    <?php endif; ?>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Message *</label>
                    <textarea name="message" rows="5" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                    <?php if (isset($errors['message'])): ?>
                        <p class="text-red-500 text-sm mt-1"><?php echo $errors['message']; ?></p>
                    <?php endif; ?>
                </div>

                <button type="submit" class="w-full bg-blue-500 text-white py-3 px-6 rounded-lg font-semibold hover:bg-blue-600 transition">
                    Send Message
                </button>
            </form>
        </div>

        <!-- Contact Information -->
        <div class="app-card p-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-6">Get in Touch</h2>

            <div class="space-y-6">
                <div class="flex items-start">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                        <i class="fas fa-envelope text-blue-500"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800">Email</h3>
                        <p class="text-gray-600">hamimshahriar17@gmail.com</p>
                        <p class="text-sm text-gray-500">We'll respond within 24 hours</p>
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                        <i class="fas fa-phone text-green-500"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800">Phone</h3>
                        <p class="text-gray-600">+8801795-190000</p>
                        <p class="text-sm text-gray-500">Mon-Fri, 9AM-5PM (GMT+6)</p>
                    </div>
                </div>

                <div class="flex items-start">
                    <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                        <i class="fas fa-map-marker-alt text-purple-500"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-800">Location</h3>
                        <p class="text-gray-600">Dhaka, Bangladesh</p>
                        <p class="text-sm text-gray-500">Serving users worldwide</p>
                    </div>
                </div>
            </div>

            <div class="mt-8">
                <h3 class="font-semibold text-gray-800 mb-4">Follow Us</h3>
                <div class="flex space-x-4">
                    <a href="https://www.linkedin.com/in/hamim-shahriar" target="_blank" class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center hover:bg-blue-700 transition">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                    <a href="https://github.com/HexEnigma" target="_blank" class="w-10 h-10 bg-gray-800 text-white rounded-full flex items-center justify-center hover:bg-gray-900 transition">
                        <i class="fab fa-github"></i>
                    </a>
                    <a href="https://www.facebook.com/HamimShahriar4" target="_blank" class="w-10 h-10 bg-blue-500 text-white rounded-full flex items-center justify-center hover:bg-blue-600 transition">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                </div>
            </div>

            <div class="mt-8 p-4 bg-gray-50 rounded-lg">
                <h3 class="font-semibold text-gray-800 mb-2">Response Time</h3>
                <p class="text-sm text-gray-600">
                    We typically respond to all inquiries within 24 hours during business days.
                    For urgent matters, please call or use the WhatsApp link.
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>