-- =====================================================
-- YK Digital Hub - Seed Data
-- =====================================================

USE `yk_digital_hub`;

-- -----------------------------------------------------
-- 1. Fixed Parent Service Categories (is_fixed = 1)
-- -----------------------------------------------------
INSERT INTO `service_categories` (`parent_id`, `name`, `slug`, `short_description`, `is_fixed`, `sort_order`, `status`)
VALUES
    (NULL, 'Web Design & Development', 'web-design-development', 'Custom web solutions for modern businesses.', 1, 1, 1),
    (NULL, 'Digital Marketing', 'digital-marketing', 'Data-driven marketing strategies to grow your brand.', 1, 2, 1);

-- -----------------------------------------------------
-- 2. Example Child Service Categories (for testing)
-- -----------------------------------------------------
-- Get the IDs of the parent categories (assume they are 1 and 2)
INSERT INTO `service_categories` (`parent_id`, `name`, `slug`, `short_description`, `is_fixed`, `sort_order`, `status`)
VALUES
    (1, 'Business Website Development', 'business-website-development', 'Professional websites for SMEs and enterprises.', 0, 1, 1),
    (1, 'Dental Website Development', 'dental-website-development', 'Specialized websites for dental practices.', 0, 2, 1),
    (1, 'E-commerce Development', 'ecommerce-development', 'Online stores with seamless checkout experiences.', 0, 3, 1),
    (2, 'SEO', 'seo', 'Organic search engine optimization to boost rankings.', 0, 1, 1),
    (2, 'Local SEO', 'local-seo', 'Optimize your local presence and Google Maps visibility.', 0, 2, 1),
    (2, 'Google Ads', 'google-ads', 'PPC campaigns that drive qualified leads.', 0, 3, 1);

-- -----------------------------------------------------
-- 3. Fixed Parent Portfolio Categories (is_fixed = 1)
-- -----------------------------------------------------
INSERT INTO `portfolio_categories` (`parent_id`, `name`, `slug`, `short_description`, `is_fixed`, `sort_order`, `status`)
VALUES
    (NULL, 'Web Design & Development', 'web-design-development', 'Showcasing our web development projects.', 1, 1, 1),
    (NULL, 'Digital Marketing', 'digital-marketing', 'Showcasing our digital marketing campaigns.', 1, 2, 1);

-- -----------------------------------------------------
-- 4. Example Child Portfolio Categories (for testing)
-- -----------------------------------------------------
INSERT INTO `portfolio_categories` (`parent_id`, `name`, `slug`, `short_description`, `is_fixed`, `sort_order`, `status`)
VALUES
    (3, 'Dental Websites', 'dental-websites', 'Websites designed for dental professionals.', 0, 1, 1),
    (3, 'Business Websites', 'business-websites', 'Corporate websites for various industries.', 0, 2, 1),
    (3, 'E-commerce Websites', 'ecommerce-websites', 'Online stores built with leading platforms.', 0, 3, 1),
    (4, 'SEO', 'seo-cases', 'SEO success stories and case studies.', 0, 1, 1),
    (4, 'Local SEO', 'local-seo-cases', 'Local SEO campaigns that delivered results.', 0, 2, 1),
    (4, 'Digital Marketing', 'digital-marketing-cases', 'Integrated digital marketing campaigns.', 0, 3, 1);

-- -----------------------------------------------------
-- 5. Default Admin User (password = "password")
-- Hash generated using PHP password_hash("password", PASSWORD_DEFAULT)
-- For MySQL, we store the hash directly.
-- -----------------------------------------------------
INSERT INTO `users` (`name`, `email`, `password`, `role`, `status`)
VALUES
    ('Admin User', 'admin@ykdigitalhub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1);

-- -----------------------------------------------------
-- 6. Optional Demo Service (to test structure)
-- -----------------------------------------------------
-- Insert a sample service under 'Business Website Development' category (ID likely 3)
-- But we don't know the exact ID; we can use subquery, but seed.sql is run after schema, so we can use subqueries.
-- However, we'll keep it simple and not add demo data to avoid clutter. The user said "only where necessary".
-- We'll skip adding demo services/projects to keep the seed clean.
-- -----------------------------------------------------

-- -----------------------------------------------------
-- 7. Example Site Settings
-- -----------------------------------------------------
INSERT INTO `site_settings` (`setting_key`, `setting_value`, `setting_type`, `setting_group`)
VALUES
    ('site_name', 'YK Digital Hub', 'string', 'general'),
    ('primary_email', 'info@ykdigitalhub.com', 'string', 'contact'),
    ('phone', '+1 (555) 123-4567', 'string', 'contact'),
    ('default_seo_title', 'YK Digital Hub - Web Design & Digital Marketing Agency', 'string', 'seo'),
    ('default_meta_description', 'We build high-performance websites and data-driven marketing campaigns to help your business grow.', 'text', 'seo');

-- =====================================================
-- End of Seed
-- =====================================================