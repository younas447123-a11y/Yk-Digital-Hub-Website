<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

$page_title = 'Our Portfolio | YK Digital Hub';
$page_description = 'Explore website design and digital marketing projects by YK Digital Hub.';
$page_canonical = BASE_URL . '/portfolio/';

$stmt = $db->query("
    SELECT p.title, p.slug, p.short_description, p.hero_image, p.featured,
           c.name AS child_name, c.slug AS child_slug,
           par.name AS parent_name, par.slug AS parent_slug
    FROM portfolio_projects p
    JOIN portfolio_categories c ON p.category_id = c.id
    JOIN portfolio_categories par ON c.parent_id = par.id
    WHERE p.status = 1 AND c.status = 1 AND par.status = 1
    ORDER BY p.featured DESC, p.sort_order, p.id DESC
");
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
include ROOT_PATH . '/includes/header.php';
include ROOT_PATH . '/includes/navbar.php';
?>

<main>
    <section class="py-16 lg:py-20 bg-slate-50">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-10">
            <div class="text-center max-w-3xl mx-auto mb-14">
                <span class="inline-block px-4 py-1.5 text-xs font-bold tracking-[0.2em] uppercase rounded-full mb-5"
                      style="color:#FF8A00; background:#FFF4E6; border:1px solid #FFE1C2;">Our Work</span>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight mb-5" style="color:#2B3F5C;">
                    Portfolio
                </h1>
                <p class="text-slate-500 text-lg leading-relaxed">
                    Explore websites and digital marketing projects delivered for our clients.
                </p>
            </div>

            <?php if (empty($projects)): ?>
                <p class="text-center text-slate-500 text-lg">No portfolio projects are available yet.</p>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-7">
                    <?php foreach ($projects as $project):
                        $url = BASE_URL . '/portfolio/' . rawurlencode($project['parent_slug']) . '/'
                            . rawurlencode($project['child_slug']) . '/' . rawurlencode($project['slug']) . '/';
                    ?>
                        <article class="group flex flex-col bg-white rounded-2xl border border-slate-200 overflow-hidden hover:shadow-lg transition-all">
                            <a href="<?= htmlspecialchars($url) ?>" class="relative block aspect-[16/10] overflow-hidden bg-slate-100">
                                <?php if (!empty($project['hero_image'])): ?>
                                    <img src="<?= htmlspecialchars($project['hero_image']) ?>"
                                         alt="<?= htmlspecialchars($project['title']) ?>"
                                         width="800" height="500"
                                         loading="lazy"
                                         class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center">
                                        <i class="fas fa-briefcase text-4xl text-slate-300" aria-hidden="true"></i>
                                    </div>
                                <?php endif; ?>
                            </a>
                            <div class="p-6 flex flex-col flex-1">
                                <p class="text-xs font-bold tracking-wider uppercase mb-2" style="color:#FF8A00;">
                                    <?= htmlspecialchars($project['parent_name']) ?> · <?= htmlspecialchars($project['child_name']) ?>
                                </p>
                                <h2 class="text-xl font-bold mb-2" style="color:#2B3F5C;">
                                    <a href="<?= htmlspecialchars($url) ?>" class="hover:text-[#0084FF] transition-colors">
                                        <?= htmlspecialchars($project['title']) ?>
                                    </a>
                                </h2>
                                <?php if (!empty($project['short_description'])): ?>
                                    <p class="text-slate-500 text-sm leading-relaxed">
                                        <?= htmlspecialchars($project['short_description']) ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php
include ROOT_PATH . '/includes/footer.php';
ob_end_flush();
