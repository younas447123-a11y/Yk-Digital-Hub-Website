<?php
/**
 * Reusable FAQ section component (zero-JS, uses native <details>).
 * Premium typography with inline styles to override theme CSS.
 */

if (!function_exists('renderFAQ')) {

    function renderFAQ($opts = []) {
        global $db;

        $title    = $opts['title']    ?? 'Frequently Asked Questions';
        $subtitle = $opts['subtitle'] ?? "Can't find your answer? WhatsApp us anytime — we respond fast.";
        $items    = $opts['items']    ?? [];

        if (empty($items) && !empty($opts['service_id'])) {
            try {
                $stmt = $db->prepare("
                    SELECT question, answer
                    FROM service_faqs
                    WHERE service_id = :sid AND status = 1
                    ORDER BY sort_order ASC, id ASC
                ");
                $stmt->execute([':sid' => (int)$opts['service_id']]);
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $items = [];
            }
        }

        if (empty($items)) return;
        ?>

        <style>
            .yk-faq summary::-webkit-details-marker { display: none; }
            .yk-faq summary { list-style: none; }
            .yk-faq details[open] .yk-faq-icon {
                transform: rotate(45deg);
                background: linear-gradient(135deg, #FF8A00, #FF6B00);
                box-shadow: 0 6px 18px rgba(255,138,0,0.35);
            }
            .yk-faq details[open] .yk-faq-icon i { color: #fff; }
            .yk-faq details[open] > summary {
                background: linear-gradient(to right, #FFF8EF, #ffffff);
            }
            .yk-faq details[open] > summary .yk-faq-q { color: #FF8A00; }
            .yk-faq details[open] .yk-faq-a {
                animation: ykFaqFade 0.3s ease-out;
            }
            @keyframes ykFaqFade {
                from { opacity: 0; transform: translateY(-6px); }
                to   { opacity: 1; transform: translateY(0); }
            }

            /* Mobile overrides */
            @media (max-width: 640px) {
                .yk-faq .yk-faq-q { font-size: 18px !important; }
                .yk-faq .yk-faq-a,
                .yk-faq .yk-faq-a p { font-size: 16px !important; }
                .yk-faq summary { padding: 20px 20px !important; }
                .yk-faq .yk-faq-a { padding: 0 20px 20px 20px !important; }
            }
        </style>

        <section class="yk-faq" style="padding: 6rem 0; background: #f8fafc;">
            <div style="max-width: 1600px; margin: 0 auto; padding: 0 1.5rem;">

                <!-- Header -->
                <div style="text-align: center; max-width: 48rem; margin: 0 auto 4rem auto;">
                    <span style="display: inline-block; padding: 8px 20px; font-size: 13px; font-weight: 800; letter-spacing: 0.22em; text-transform: uppercase; border-radius: 999px; color: #FF8A00; background: #FFF4E6; border: 1px solid #FFE1C2;">
                        <span style="color:#FF8A00;">✦</span> FAQ
                    </span>
                    <h2 style="font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif; font-size: 3rem; line-height: 1.05; font-weight: 800; letter-spacing: -0.02em; color: #2B3F5C; margin: 20px 0 16px 0;">
                        <?php
                        $words = explode(' ', $title);
                        $last  = array_pop($words);
                        $first = implode(' ', $words);
                        ?>
                        <?= htmlspecialchars($first) ?>
                        <span style="color:#FF8A00;"><?= htmlspecialchars($last) ?></span>
                    </h2>
                    <?php if ($subtitle): ?>
                        <p style="font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif; font-size: 19px; line-height: 1.6; color: #64748b; margin: 0; max-width: 42rem; margin-left: auto; margin-right: auto;">
                            <?= htmlspecialchars($subtitle) ?>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- FAQ list -->
                <div style="max-width: 56rem; margin: 0 auto; display: flex; flex-direction: column; gap: 16px;">
                    <?php foreach ($items as $f):
                        $q = $f['question'] ?? '';
                        $a = $f['answer']   ?? '';
                        if ($q === '') continue;
                    ?>
                        <details class="yk-faq-item" style="background: #ffffff; border: 2px solid #e2e8f0; border-radius: 16px; overflow: hidden; transition: all 0.2s ease;">
                            <summary class="yk-faq-summary" style="display: flex; align-items: center; justify-content: space-between; gap: 24px; text-align: left; padding: 28px 36px; cursor: pointer; user-select: none; outline: none; transition: background 0.2s ease;">

                                <span class="yk-faq-q" style="font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif; color: #2B3F5C; font-size: 21px; line-height: 1.4; font-weight: 700; padding-right: 8px; transition: color 0.2s ease;">
                                    <?= htmlspecialchars($q) ?>
                                </span>

                                <span class="yk-faq-icon" style="flex-shrink: 0; width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #FFF4E6; transition: all 0.3s ease;">
                                    <i class="fas fa-plus" style="color: #FF8A00; font-size: 16px;"></i>
                                </span>
                            </summary>

                            <div class="yk-faq-a" style="padding: 0 36px 32px 36px; font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;">
                                <?php
                                // Split answer into paragraphs on double line breaks
                                $paragraphs = preg_split('/\n\s*\n/', trim($a));
                                foreach ($paragraphs as $p):
                                    if (trim($p) === '') continue;
                                ?>
                                    <p style="margin: 0 0 14px 0; font-size: 17px; line-height: 1.8; color: #475569; font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif; font-weight: 400;">
                                        <?= nl2br(htmlspecialchars(trim($p))) ?>
                                    </p>
                                <?php endforeach; ?>
                            </div>
                        </details>
                    <?php endforeach; ?>
                </div>

            </div>
        </section>

        <?php
    }
}