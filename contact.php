<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$site_name = getSiteSetting('site_name', 'YK Digital Hub');
$page_title = 'Contact Us | ' . $site_name;
$page_description = 'Get in touch with YK Digital Hub for web design, development, and digital marketing. We respond within one business day.';
$page_canonical = BASE_URL . '/contact/';

// ----- Your fixed contact details -----
$MY_WHATSAPP = '923069776937';
$MY_EMAIL    = 'ykdigitalhub@outlook.com';
$MY_WHATSAPP_URL = 'https://wa.me/' . $MY_WHATSAPP;

$errors = [];
$success = false;

$form = [
    'first_name' => '',
    'last_name'  => '',
    'company'    => '',
    'website'    => '',
    'email'      => '',
    'phone'      => '',
    'services'   => [],
    'message'    => '',
];

$available_services = [
    'Digital Marketing',
    'Website Design',
    'Website Development',
    'SEO',
    'Local SEO',
    'Social Media Marketing',
    'Content Marketing',
    'Google Ads / PPC',
    'E-Commerce Marketing',
    'WordPress Development',
    'Shopify Development',
    'WooCommerce',
    'UI / UX Design',
    'Website Redesign',
    'Healthcare Websites',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Your session expired. Please refresh and try again.';
    } else {
        $form['first_name'] = trim($_POST['first_name'] ?? '');
        $form['last_name']  = trim($_POST['last_name']  ?? '');
        $form['company']    = trim($_POST['company']    ?? '');
        $form['website']    = trim($_POST['website']    ?? '');
        $form['email']      = trim($_POST['email']      ?? '');
        $form['phone']      = trim($_POST['phone']      ?? '');
        $form['message']    = trim($_POST['message']    ?? '');
        $form['services']   = array_values(array_filter((array)($_POST['services'] ?? []), function ($s) use ($available_services) {
            return in_array($s, $available_services, true);
        }));

        // Validation
        if ($form['first_name'] === '') $errors[] = 'First name is required.';
        if ($form['last_name'] === '')  $errors[] = 'Last name is required.';

        if ($form['email'] === '') {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }

        if ($form['message'] === '') $errors[] = 'Please tell us about your project.';
        if (mb_strlen($form['message']) > 5000) $errors[] = 'Message is too long.';

        if (empty($errors)) {
            $full_name = trim($form['first_name'] . ' ' . $form['last_name']);
            $services_str = !empty($form['services']) ? implode(', ', $form['services']) : '';

            // Build a message body that includes everything
            $message_body = $form['message'];
            if ($form['website'] !== '') {
                $message_body = "Website: {$form['website']}\n\n" . $message_body;
            }
            if ($services_str !== '') {
                $message_body = "Services of interest: {$services_str}\n\n" . $message_body;
            }

            try {
                $stmt = $db->prepare("
                    INSERT INTO contact_messages
                    (name, email, phone, company, service_id, subject, message, source, status)
                    VALUES (:name, :email, :phone, :company, :service_id, :subject, :message, 'contact-page', 'new')
                ");
                $stmt->execute([
                    ':name'       => $full_name,
                    ':email'      => $form['email'],
                    ':phone'      => $form['phone'] ?: null,
                    ':company'    => $form['company'] ?: null,
                    ':service_id' => null,
                    ':subject'    => $services_str ?: null,
                    ':message'    => $message_body,
                ]);

                             // ---- Notify by email + WhatsApp (non-blocking) ----
                require_once __DIR__ . '/includes/notifier.php';
                $notifyData = [
                    'name'    => $full_name,
                    'email'   => $form['email'],
                    'phone'   => $form['phone'],
                    'company' => $form['company'],
                    'subject' => $services_str,
                    'message' => $form['message'],
                ];
                notifyByEmail($notifyData);
                notifyByWhatsApp($notifyData);

                setFlash('success', 'Thank you! Your message has been received. We will get back to you within one business day.');
                header('Location: ' . BASE_URL . '/contact');
                exit;
           } catch (PDOException $e) {
    error_log('[YK Contact] DB insert failed: ' . $e->getMessage());
    $errors[] = 'We could not save your message. Please try again in a moment.';
}
        }
    }
}

ob_start();
include ROOT_PATH . '/includes/header.php';
if (file_exists(ROOT_PATH . '/includes/navbar.php')) include ROOT_PATH . '/includes/navbar.php';
?>

<!-- ===================== HERO ===================== -->
<section class="py-20 lg:py-28 bg-gradient-to-b from-slate-50 to-white">
    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 text-center">
        <h1 class="text-5xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight mb-6 leading-[1.05]">
            <span style="background: linear-gradient(135deg, #0084FF 0%, #0066CC 55%, #FF8A00 100%); -webkit-background-clip: text; background-clip: text; color: transparent;">
                Contact Us
            </span>
        </h1>
        <p class="text-slate-500 text-lg lg:text-xl leading-relaxed max-w-3xl mx-auto">
            We'd love to hear from you. Whether you have questions about our services, need a quote,
            or want to discuss a project — we're here to help. At <strong style="color:#2B3F5C;">YK Digital Hub</strong>,
            we build strong relationships with our clients and help them grow online.
        </p>
    </div>
</section>

<!-- ===================== MAIN: FORM + STEPS ===================== -->
<section class="pb-20 pt-4 bg-white">
    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">

        <?php if ($errors): ?>
            <div class="max-w-6xl mx-auto mb-8 bg-red-50 border-l-4 border-red-500 rounded-r-lg p-4">
                <ul class="list-disc list-inside text-red-700 text-sm space-y-1">
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="grid lg:grid-cols-12 gap-12 lg:gap-16">

            <!-- ============ LEFT: FORM ============ -->
            <div class="lg:col-span-7">
                <span class="text-xs font-extrabold tracking-[0.2em] uppercase" style="color:#FF8A00;">Contact Us</span>
                <h2 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight mt-3 mb-8 leading-[1.05]" style="color:#2B3F5C;">
                    Experience Real<br>Results
                </h2>

                <form method="POST" action="<?= BASE_URL ?>/contact" novalidate class="space-y-4">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                    <!-- Row 1: First + Last -->
                    <div class="grid sm:grid-cols-2 gap-4">
                        <input type="text" name="first_name" required maxlength="80"
                               value="<?= htmlspecialchars($form['first_name']) ?>"
                               placeholder="First Name*"
                               class="w-full px-5 py-4 border-2 border-slate-200 rounded-lg text-[15px] focus:border-[#0084FF] focus:outline-none transition placeholder:text-slate-400">
                        <input type="text" name="last_name" required maxlength="80"
                               value="<?= htmlspecialchars($form['last_name']) ?>"
                               placeholder="Last Name*"
                               class="w-full px-5 py-4 border-2 border-slate-200 rounded-lg text-[15px] focus:border-[#0084FF] focus:outline-none transition placeholder:text-slate-400">
                    </div>

                    <!-- Row 2: Company + Website -->
                    <div class="grid sm:grid-cols-2 gap-4">
                        <input type="text" name="company" maxlength="120"
                               value="<?= htmlspecialchars($form['company']) ?>"
                               placeholder="Company / Organization*"
                               class="w-full px-5 py-4 border-2 border-slate-200 rounded-lg text-[15px] focus:border-[#0084FF] focus:outline-none transition placeholder:text-slate-400">
                        <input type="url" name="website" maxlength="200"
                               value="<?= htmlspecialchars($form['website']) ?>"
                               placeholder="Website"
                               class="w-full px-5 py-4 border-2 border-slate-200 rounded-lg text-[15px] focus:border-[#0084FF] focus:outline-none transition placeholder:text-slate-400">
                    </div>

                    <!-- Row 3: Email + Phone -->
                    <div class="grid sm:grid-cols-2 gap-4">
                        <input type="email" name="email" required maxlength="120"
                               value="<?= htmlspecialchars($form['email']) ?>"
                               placeholder="Email Address*"
                               class="w-full px-5 py-4 border-2 border-slate-200 rounded-lg text-[15px] focus:border-[#0084FF] focus:outline-none transition placeholder:text-slate-400">
                        <input type="tel" name="phone" maxlength="40"
                               value="<?= htmlspecialchars($form['phone']) ?>"
                               placeholder="Phone*"
                               class="w-full px-5 py-4 border-2 border-slate-200 rounded-lg text-[15px] focus:border-[#0084FF] focus:outline-none transition placeholder:text-slate-400">
                    </div>

                    <!-- Services checkboxes -->
                    <div class="pt-4">
                        <label class="block text-xs font-extrabold tracking-wider uppercase mb-4" style="color:#2B3F5C;">
                            Select Service
                        </label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-4 gap-y-3">
                            <?php foreach ($available_services as $svc): ?>
                                <label class="flex items-center gap-2 cursor-pointer text-[13px] font-semibold tracking-wide uppercase text-slate-700 hover:text-[#0084FF] transition">
                                    <input type="checkbox" name="services[]" value="<?= htmlspecialchars($svc) ?>"
                                           <?= in_array($svc, $form['services'], true) ? 'checked' : '' ?>
                                           class="w-4 h-4 rounded border-2 border-slate-300 text-[#0084FF] focus:ring-[#0084FF] cursor-pointer">
                                    <?= htmlspecialchars($svc) ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Message -->
                    <div class="pt-4">
                        <textarea name="message" rows="6" required maxlength="5000"
                                  placeholder="Tell us about your business and what you're looking for..."
                                  class="w-full px-5 py-4 border-2 border-slate-200 rounded-lg text-[15px] focus:border-[#0084FF] focus:outline-none transition placeholder:text-slate-400 resize-y"><?= htmlspecialchars($form['message']) ?></textarea>
                    </div>

                    <!-- Send button -->
                    <button type="submit"
                            class="w-full sm:w-auto px-14 py-4 text-white font-bold text-base rounded-full transition hover:opacity-90 hover:-translate-y-0.5 shadow-lg"
                            style="background: linear-gradient(90deg, #FF8A00 0%, #FF6B00 100%);">
                        Send
                    </button>

                    <p class="text-xs text-slate-400 pt-2">
                        We'll never share your details with third parties. Typical reply time: within 1 business day.
                    </p>
                </form>
            </div>

            <!-- ============ RIGHT: 3 STEPS ============ -->
            <div class="lg:col-span-5">
                <div class="flex items-start gap-4 mb-8">
                    <i class="fas fa-arrow-right text-3xl mt-1" style="color:#2B3F5C;"></i>
                    <div>
                        <h3 class="text-2xl font-extrabold mb-2" style="color:#2B3F5C;">Ready to Elevate Your Brand?</h3>
                        <p class="text-slate-500 text-sm leading-relaxed">
                            Kickstart your digital journey with YK Digital Hub in 3 simple steps:
                        </p>
                    </div>
                </div>

                <div class="space-y-6">
                    <!-- Step 1 -->
                    <div class="bg-slate-100 rounded-3xl p-6 lg:p-7 flex gap-5">
                        <div class="shrink-0 w-11 h-11 rounded-full flex items-center justify-center text-white font-extrabold text-lg" style="background:#0f172a;">
                            1
                        </div>
                        <div>
                            <h4 class="text-lg font-bold mb-2" style="color:#2B3F5C;">Get in Touch</h4>
                            <p class="text-slate-600 text-sm leading-relaxed">
                                Fill out our quick contact form, and our team will reach out to understand your goals and how we can support your business.
                            </p>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="bg-slate-100 rounded-3xl p-6 lg:p-7 flex gap-5">
                        <div class="shrink-0 w-11 h-11 rounded-full flex items-center justify-center text-white font-extrabold text-lg" style="background:#0f172a;">
                            2
                        </div>
                        <div>
                            <h4 class="text-lg font-bold mb-2" style="color:#2B3F5C;">Craft Custom Strategies</h4>
                            <p class="text-slate-600 text-sm leading-relaxed">
                                Our experts will collaborate with you to design and execute tailored strategies that deliver real, measurable results.
                            </p>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="bg-slate-100 rounded-3xl p-6 lg:p-7 flex gap-5">
                        <div class="shrink-0 w-11 h-11 rounded-full flex items-center justify-center text-white font-extrabold text-lg" style="background:#0f172a;">
                            3
                        </div>
                        <div>
                            <h4 class="text-lg font-bold mb-2" style="color:#2B3F5C;">Experience Sustainable Growth</h4>
                            <p class="text-slate-600 text-sm leading-relaxed">
                                Enhance your online presence, grow your audience, and watch your business thrive with consistent, long-term success.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Direct contact -->
                <div class="mt-8 pt-8 border-t border-slate-200 space-y-3">
                    <p class="text-xs font-extrabold tracking-wider uppercase mb-3" style="color:#2B3F5C;">Prefer to reach us directly?</p>

                    <a href="mailto:<?= htmlspecialchars($MY_EMAIL) ?>"
                       class="flex items-center gap-3 text-slate-700 hover:text-[#0084FF] transition font-medium text-sm">
                        <span class="w-9 h-9 rounded-full flex items-center justify-center" style="background:#eff6ff;">
                            <i class="fas fa-envelope text-sm" style="color:#0084FF;"></i>
                        </span>
                        <?= htmlspecialchars($MY_EMAIL) ?>
                    </a>

                    <a href="<?= $MY_WHATSAPP_URL ?>" target="_blank" rel="noopener noreferrer"
                       class="flex items-center gap-3 text-slate-700 hover:text-[#25D366] transition font-medium text-sm">
                        <span class="w-9 h-9 rounded-full flex items-center justify-center" style="background:#e7f8ed;">
                            <i class="fab fa-whatsapp text-base" style="color:#25D366;"></i>
                        </span>
                        +<?= htmlspecialchars($MY_WHATSAPP) ?>
                    </a>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ===================== BOTTOM CTA BANNER ===================== -->
<section class="relative overflow-hidden" style="background:#000; min-height: 280px;">
    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10 grid md:grid-cols-2 items-center gap-0">

        <!-- Left: person (optional image) -->
        <div class="hidden md:flex justify-center items-end pt-8" aria-hidden="true">
            <div class="w-full max-w-md aspect-square relative">
                <!-- Placeholder until you add the image -->
                <div class="w-full h-full flex items-center justify-center text-white/10">
                    <i class="fas fa-user-tie" style="font-size: 220px; color: rgba(255,255,255,0.06);"></i>
                </div>
            </div>
        </div>

        <!-- Right: text -->
        <div class="py-14 md:py-20">
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white leading-[1.1] mb-8">
                READY TO TRANSFORM YOUR<br>BUSINESS?
            </h2>
            <a href="<?= $MY_WHATSAPP_URL ?>" target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center gap-3 px-8 py-4 bg-white text-slate-900 font-bold text-sm rounded-full shadow-lg hover:shadow-2xl hover:-translate-y-0.5 transition">
                Get Started — Free Consultation
                <i class="fas fa-arrow-right text-xs"></i>
            </a>
        </div>
    </div>
</section>

<?php
include ROOT_PATH . '/includes/footer.php';
ob_end_flush();