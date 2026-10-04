-- =====================================================
-- YK Digital Hub - Database Schema
-- MySQL 8+, InnoDB, utf8mb4_unicode_ci
-- =====================================================

CREATE DATABASE IF NOT EXISTS `yk_digital_hub`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `yk_digital_hub`;

-- =====================================================
-- 1. USERS (Admin & Editors)
-- =====================================================
CREATE TABLE `users` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin','editor') NOT NULL DEFAULT 'editor',
    `profile_image` VARCHAR(255) NULL,
    `bio` TEXT NULL,
    `status` TINYINT(1) NOT NULL DEFAULT 1,
    `last_login` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_users_email` (`email`),
    INDEX `idx_users_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. SERVICE CATEGORIES (Hierarchical, Fixed Parents)
-- =====================================================
CREATE TABLE `service_categories` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `parent_id` BIGINT UNSIGNED NULL,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(120) NOT NULL UNIQUE,
    `short_description` VARCHAR(255) NULL,
    `description` TEXT NULL,
    `image` VARCHAR(255) NULL,
    `icon` VARCHAR(100) NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `status` TINYINT(1) NOT NULL DEFAULT 1,
    `is_fixed` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_service_categories_slug` (`slug`),
    INDEX `idx_service_categories_parent` (`parent_id`),
    INDEX `idx_service_categories_status` (`status`),
    INDEX `idx_service_categories_sort` (`sort_order`),
    CONSTRAINT `fk_service_categories_parent`
        FOREIGN KEY (`parent_id`) REFERENCES `service_categories` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. SERVICES (Main Service Entries)
-- =====================================================
CREATE TABLE `services` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `category_id` BIGINT UNSIGNED NULL,
    `title` VARCHAR(200) NOT NULL,
    `slug` VARCHAR(200) NOT NULL UNIQUE,
    `short_description` VARCHAR(255) NULL,
    `card_description` VARCHAR(255) NULL,
    `hero_title` VARCHAR(200) NULL,
    `hero_subtitle` VARCHAR(200) NULL,
    `hero_description` TEXT NULL,
    `hero_image` VARCHAR(255) NULL,
    `hero_badge` VARCHAR(100) NULL,
    `icon` VARCHAR(100) NULL,
    `featured` TINYINT(1) NOT NULL DEFAULT 0,
    `sort_order` INT NOT NULL DEFAULT 0,
    `status` TINYINT(1) NOT NULL DEFAULT 1,
    -- Business info
    `starting_price` DECIMAL(10,2) NULL,
    `price_label` VARCHAR(50) NULL,
    `delivery_time` VARCHAR(50) NULL,
    `service_location` VARCHAR(100) NULL,
    -- CTAs
    `primary_cta_text` VARCHAR(100) NULL,
    `primary_cta_url` VARCHAR(255) NULL,
    `secondary_cta_text` VARCHAR(100) NULL,
    `secondary_cta_url` VARCHAR(255) NULL,
    -- SEO
    `seo_title` VARCHAR(255) NULL,
    `meta_description` TEXT NULL,
    `focus_keyword` VARCHAR(255) NULL,
    `canonical_url` VARCHAR(255) NULL,
    `og_title` VARCHAR(255) NULL,
    `og_description` TEXT NULL,
    `og_image` VARCHAR(255) NULL,
    `robots` VARCHAR(100) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_services_slug` (`slug`),
    INDEX `idx_services_category` (`category_id`),
    INDEX `idx_services_status` (`status`),
    INDEX `idx_services_featured` (`featured`),
    INDEX `idx_services_sort` (`sort_order`),
    CONSTRAINT `fk_services_category`
        FOREIGN KEY (`category_id`) REFERENCES `service_categories` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. SERVICE SECTIONS (Dynamic Content Blocks)
-- =====================================================
CREATE TABLE `service_sections` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `service_id` BIGINT UNSIGNED NOT NULL,
    `section_type` VARCHAR(50) NOT NULL,
    `section_label` VARCHAR(100) NULL,
    `heading` VARCHAR(255) NULL,
    `subheading` VARCHAR(255) NULL,
    `content` LONGTEXT NULL,
    `image` VARCHAR(255) NULL,
    `video_url` VARCHAR(255) NULL,
    `background_image` VARCHAR(255) NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `status` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_service_sections_service` (`service_id`),
    INDEX `idx_service_sections_type` (`section_type`),
    INDEX `idx_service_sections_sort` (`sort_order`),
    CONSTRAINT `fk_service_sections_service`
        FOREIGN KEY (`service_id`) REFERENCES `services` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. SERVICE FEATURES
-- =====================================================
CREATE TABLE `service_features` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `service_id` BIGINT UNSIGNED NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT NULL,
    `icon` VARCHAR(100) NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `status` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_service_features_service` (`service_id`),
    CONSTRAINT `fk_service_features_service`
        FOREIGN KEY (`service_id`) REFERENCES `services` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 6. SERVICE PROCESS STEPS
-- =====================================================
CREATE TABLE `service_process_steps` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `service_id` BIGINT UNSIGNED NOT NULL,
    `step_number` INT NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT NULL,
    `icon` VARCHAR(100) NULL,
    `image` VARCHAR(255) NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `status` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_service_process_service` (`service_id`),
    CONSTRAINT `fk_service_process_service`
        FOREIGN KEY (`service_id`) REFERENCES `services` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 7. SERVICE STATISTICS
-- =====================================================
CREATE TABLE `service_stats` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `service_id` BIGINT UNSIGNED NOT NULL,
    `value` VARCHAR(50) NOT NULL,
    `label` VARCHAR(100) NOT NULL,
    `description` VARCHAR(255) NULL,
    `icon` VARCHAR(100) NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `status` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_service_stats_service` (`service_id`),
    CONSTRAINT `fk_service_stats_service`
        FOREIGN KEY (`service_id`) REFERENCES `services` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 8. SERVICE TECHNOLOGIES / TAGS
-- =====================================================
CREATE TABLE `service_technologies` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `service_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `icon` VARCHAR(100) NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `status` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_service_technologies_service` (`service_id`),
    CONSTRAINT `fk_service_technologies_service`
        FOREIGN KEY (`service_id`) REFERENCES `services` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 9. SERVICE FAQs
-- =====================================================
CREATE TABLE `service_faqs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `service_id` BIGINT UNSIGNED NOT NULL,
    `question` VARCHAR(255) NOT NULL,
    `answer` LONGTEXT NOT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `status` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_service_faqs_service` (`service_id`),
    CONSTRAINT `fk_service_faqs_service`
        FOREIGN KEY (`service_id`) REFERENCES `services` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 10. PORTFOLIO CATEGORIES (Hierarchical, Fixed Parents)
-- =====================================================
CREATE TABLE `portfolio_categories` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `parent_id` BIGINT UNSIGNED NULL,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(120) NOT NULL UNIQUE,
    `short_description` VARCHAR(255) NULL,
    `description` TEXT NULL,
    `image` VARCHAR(255) NULL,
    `icon` VARCHAR(100) NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `status` TINYINT(1) NOT NULL DEFAULT 1,
    `is_fixed` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_portfolio_categories_slug` (`slug`),
    INDEX `idx_portfolio_categories_parent` (`parent_id`),
    INDEX `idx_portfolio_categories_status` (`status`),
    INDEX `idx_portfolio_categories_sort` (`sort_order`),
    CONSTRAINT `fk_portfolio_categories_parent`
        FOREIGN KEY (`parent_id`) REFERENCES `portfolio_categories` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 11. PORTFOLIO PROJECTS (Case Studies)
-- =====================================================
CREATE TABLE `portfolio_projects` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `category_id` BIGINT UNSIGNED NULL,
    `title` VARCHAR(200) NOT NULL,
    `slug` VARCHAR(200) NOT NULL UNIQUE,
    `client_name` VARCHAR(200) NULL,
    `project_type` VARCHAR(100) NULL,
    `industry` VARCHAR(100) NULL,
    `location` VARCHAR(100) NULL,
    `website_url` VARCHAR(255) NULL,
    `short_description` VARCHAR(255) NULL,
    `card_description` VARCHAR(255) NULL,
    -- Hero
    `hero_title` VARCHAR(200) NULL,
    `hero_subtitle` VARCHAR(200) NULL,
    `hero_description` TEXT NULL,
    `hero_image` VARCHAR(255) NULL,
    `hero_video_url` VARCHAR(255) NULL,
    -- Project Info
    `project_year` YEAR NULL,
    `project_duration` VARCHAR(50) NULL,
    `team_size` INT NULL,
    `platform` VARCHAR(100) NULL,
    `technologies` VARCHAR(255) NULL,
    `project_scope` TEXT NULL,
    -- Business
    `client_challenge` TEXT NULL,
    `project_goal` TEXT NULL,
    `project_summary` TEXT NULL,
    -- CTA
    `cta_title` VARCHAR(200) NULL,
    `cta_description` TEXT NULL,
    `cta_button_text` VARCHAR(100) NULL,
    `cta_button_url` VARCHAR(255) NULL,
    -- Display
    `featured` TINYINT(1) NOT NULL DEFAULT 0,
    `sort_order` INT NOT NULL DEFAULT 0,
    `status` TINYINT(1) NOT NULL DEFAULT 1,
    -- SEO
    `seo_title` VARCHAR(255) NULL,
    `meta_description` TEXT NULL,
    `focus_keyword` VARCHAR(255) NULL,
    `canonical_url` VARCHAR(255) NULL,
    `og_title` VARCHAR(255) NULL,
    `og_description` TEXT NULL,
    `og_image` VARCHAR(255) NULL,
    `robots` VARCHAR(100) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_portfolio_projects_slug` (`slug`),
    INDEX `idx_portfolio_projects_category` (`category_id`),
    INDEX `idx_portfolio_projects_status` (`status`),
    INDEX `idx_portfolio_projects_featured` (`featured`),
    INDEX `idx_portfolio_projects_sort` (`sort_order`),
    CONSTRAINT `fk_portfolio_projects_category`
        FOREIGN KEY (`category_id`) REFERENCES `portfolio_categories` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 12. PORTFOLIO SECTIONS (Dynamic Content Blocks)
-- =====================================================
CREATE TABLE `portfolio_sections` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `project_id` BIGINT UNSIGNED NOT NULL,
    `section_type` VARCHAR(50) NOT NULL,
    `section_label` VARCHAR(100) NULL,
    `heading` VARCHAR(255) NULL,
    `subheading` VARCHAR(255) NULL,
    `content` LONGTEXT NULL,
    `image` VARCHAR(255) NULL,
    `video_url` VARCHAR(255) NULL,
    `background_image` VARCHAR(255) NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `status` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_portfolio_sections_project` (`project_id`),
    INDEX `idx_portfolio_sections_type` (`section_type`),
    CONSTRAINT `fk_portfolio_sections_project`
        FOREIGN KEY (`project_id`) REFERENCES `portfolio_projects` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 13. PORTFOLIO CHALLENGES
-- =====================================================
CREATE TABLE `portfolio_challenges` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `project_id` BIGINT UNSIGNED NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT NULL,
    `icon` VARCHAR(100) NULL,
    `image` VARCHAR(255) NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `status` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_portfolio_challenges_project` (`project_id`),
    CONSTRAINT `fk_portfolio_challenges_project`
        FOREIGN KEY (`project_id`) REFERENCES `portfolio_projects` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 14. PORTFOLIO SOLUTIONS
-- =====================================================
CREATE TABLE `portfolio_solutions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `project_id` BIGINT UNSIGNED NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT NULL,
    `content` LONGTEXT NULL,
    `image` VARCHAR(255) NULL,
    `icon` VARCHAR(100) NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `status` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_portfolio_solutions_project` (`project_id`),
    CONSTRAINT `fk_portfolio_solutions_project`
        FOREIGN KEY (`project_id`) REFERENCES `portfolio_projects` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 15. PORTFOLIO RESULTS
-- =====================================================
CREATE TABLE `portfolio_results` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `project_id` BIGINT UNSIGNED NOT NULL,
    `metric_value` VARCHAR(50) NOT NULL,
    `metric_label` VARCHAR(100) NOT NULL,
    `description` VARCHAR(255) NULL,
    `icon` VARCHAR(100) NULL,
    `image` VARCHAR(255) NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `status` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_portfolio_results_project` (`project_id`),
    CONSTRAINT `fk_portfolio_results_project`
        FOREIGN KEY (`project_id`) REFERENCES `portfolio_projects` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 16. PORTFOLIO FEATURES
-- =====================================================
CREATE TABLE `portfolio_features` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `project_id` BIGINT UNSIGNED NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT NULL,
    `icon` VARCHAR(100) NULL,
    `image` VARCHAR(255) NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `status` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_portfolio_features_project` (`project_id`),
    CONSTRAINT `fk_portfolio_features_project`
        FOREIGN KEY (`project_id`) REFERENCES `portfolio_projects` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 17. PORTFOLIO IMAGES (Gallery)
-- =====================================================
CREATE TABLE `portfolio_images` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `project_id` BIGINT UNSIGNED NOT NULL,
    `image` VARCHAR(255) NOT NULL,
    `thumbnail` VARCHAR(255) NULL,
    `alt_text` VARCHAR(255) NULL,
    `caption` VARCHAR(255) NULL,
    `image_type` VARCHAR(50) NOT NULL DEFAULT 'gallery',
    `sort_order` INT NOT NULL DEFAULT 0,
    `status` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_portfolio_images_project` (`project_id`),
    CONSTRAINT `fk_portfolio_images_project`
        FOREIGN KEY (`project_id`) REFERENCES `portfolio_projects` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 18. MARKETING CASE STUDIES (Extended Info)
-- =====================================================
CREATE TABLE `marketing_case_studies` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `project_id` BIGINT UNSIGNED NOT NULL UNIQUE,
    `marketing_objective` TEXT NULL,
    `target_audience` TEXT NULL,
    `target_location` VARCHAR(100) NULL,
    `campaign_duration` VARCHAR(50) NULL,
    `campaign_budget` DECIMAL(12,2) NULL,
    `starting_position` VARCHAR(255) NULL,
    `strategy_summary` TEXT NULL,
    `execution_summary` TEXT NULL,
    `results_summary` TEXT NULL,
    `roi` VARCHAR(50) NULL,
    `traffic_growth` VARCHAR(50) NULL,
    `leads_growth` VARCHAR(50) NULL,
    `conversion_growth` VARCHAR(50) NULL,
    `revenue_growth` VARCHAR(50) NULL,
    `status` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_marketing_case_project` (`project_id`),
    CONSTRAINT `fk_marketing_case_project`
        FOREIGN KEY (`project_id`) REFERENCES `portfolio_projects` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 19. MARKETING CASE STUDY SERVICES
-- =====================================================
CREATE TABLE `portfolio_marketing_services` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `project_id` BIGINT UNSIGNED NOT NULL,
    `service_name` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `icon` VARCHAR(100) NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `status` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_marketing_services_project` (`project_id`),
    CONSTRAINT `fk_marketing_services_project`
        FOREIGN KEY (`project_id`) REFERENCES `portfolio_projects` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 20. MARKETING CASE STUDY CHANNELS
-- =====================================================
CREATE TABLE `portfolio_marketing_channels` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `project_id` BIGINT UNSIGNED NOT NULL,
    `channel_name` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `icon` VARCHAR(100) NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `status` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_marketing_channels_project` (`project_id`),
    CONSTRAINT `fk_marketing_channels_project`
        FOREIGN KEY (`project_id`) REFERENCES `portfolio_projects` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 21. MARKETING CASE STUDY RESULTS (Metrics)
-- =====================================================
CREATE TABLE `portfolio_marketing_results` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `project_id` BIGINT UNSIGNED NOT NULL,
    `metric_name` VARCHAR(100) NOT NULL,
    `metric_value` VARCHAR(50) NOT NULL,
    `previous_value` VARCHAR(50) NULL,
    `improvement_percentage` VARCHAR(20) NULL,
    `description` TEXT NULL,
    `source` VARCHAR(100) NULL,
    `icon` VARCHAR(100) NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `status` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_marketing_results_project` (`project_id`),
    CONSTRAINT `fk_marketing_results_project`
        FOREIGN KEY (`project_id`) REFERENCES `portfolio_projects` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 22. BLOG CATEGORIES
-- =====================================================
CREATE TABLE `blog_categories` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(120) NOT NULL UNIQUE,
    `description` TEXT NULL,
    `image` VARCHAR(255) NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `status` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_blog_categories_slug` (`slug`),
    INDEX `idx_blog_categories_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 23. BLOG TAGS
-- =====================================================
CREATE TABLE `blog_tags` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(120) NOT NULL UNIQUE,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_blog_tags_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 24. BLOG POSTS
-- =====================================================
CREATE TABLE `blog_posts` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `category_id` BIGINT UNSIGNED NULL,
    `author_id` BIGINT UNSIGNED NULL,
    `title` VARCHAR(200) NOT NULL,
    `slug` VARCHAR(200) NOT NULL UNIQUE,
    `excerpt` TEXT NULL,
    `content` LONGTEXT NOT NULL,
    `featured_image` VARCHAR(255) NULL,
    `featured_image_alt` VARCHAR(255) NULL,
    `reading_time` INT NULL,
    `published_at` DATETIME NULL,
    `status` ENUM('draft','published','archived') NOT NULL DEFAULT 'draft',
    `featured` TINYINT(1) NOT NULL DEFAULT 0,
    `views` BIGINT UNSIGNED NOT NULL DEFAULT 0,
    -- SEO
    `seo_title` VARCHAR(255) NULL,
    `meta_description` TEXT NULL,
    `focus_keyword` VARCHAR(255) NULL,
    `canonical_url` VARCHAR(255) NULL,
    `og_title` VARCHAR(255) NULL,
    `og_description` TEXT NULL,
    `og_image` VARCHAR(255) NULL,
    `robots` VARCHAR(100) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_blog_posts_slug` (`slug`),
    INDEX `idx_blog_posts_category` (`category_id`),
    INDEX `idx_blog_posts_author` (`author_id`),
    INDEX `idx_blog_posts_status` (`status`),
    INDEX `idx_blog_posts_published` (`published_at`),
    INDEX `idx_blog_posts_featured` (`featured`),
    CONSTRAINT `fk_blog_posts_category`
        FOREIGN KEY (`category_id`) REFERENCES `blog_categories` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_blog_posts_author`
        FOREIGN KEY (`author_id`) REFERENCES `users` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 25. BLOG POST TAGS (Many-to-Many)
-- =====================================================
CREATE TABLE `blog_post_tags` (
    `post_id` BIGINT UNSIGNED NOT NULL,
    `tag_id` BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (`post_id`, `tag_id`),
    INDEX `idx_blog_post_tags_tag` (`tag_id`),
    CONSTRAINT `fk_blog_post_tags_post`
        FOREIGN KEY (`post_id`) REFERENCES `blog_posts` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_blog_post_tags_tag`
        FOREIGN KEY (`tag_id`) REFERENCES `blog_tags` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 26. TESTIMONIALS
-- =====================================================
CREATE TABLE `testimonials` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `client_name` VARCHAR(100) NOT NULL,
    `company_name` VARCHAR(100) NULL,
    `position` VARCHAR(100) NULL,
    `client_photo` VARCHAR(255) NULL,
    `rating` TINYINT UNSIGNED NULL,
    `testimonial` TEXT NOT NULL,
    `service_id` BIGINT UNSIGNED NULL,
    `project_id` BIGINT UNSIGNED NULL,
    `location` VARCHAR(100) NULL,
    `featured` TINYINT(1) NOT NULL DEFAULT 0,
    `sort_order` INT NOT NULL DEFAULT 0,
    `status` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_testimonials_service` (`service_id`),
    INDEX `idx_testimonials_project` (`project_id`),
    INDEX `idx_testimonials_featured` (`featured`),
    INDEX `idx_testimonials_status` (`status`),
    CONSTRAINT `fk_testimonials_service`
        FOREIGN KEY (`service_id`) REFERENCES `services` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_testimonials_project`
        FOREIGN KEY (`project_id`) REFERENCES `portfolio_projects` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 27. CONTACT MESSAGES
-- =====================================================
CREATE TABLE `contact_messages` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(50) NULL,
    `company` VARCHAR(100) NULL,
    `service_id` BIGINT UNSIGNED NULL,
    `subject` VARCHAR(200) NULL,
    `message` TEXT NOT NULL,
    `source` VARCHAR(50) NULL,
    `status` ENUM('new','read','contacted','converted','archived') NOT NULL DEFAULT 'new',
    `admin_notes` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_contact_messages_service` (`service_id`),
    INDEX `idx_contact_messages_email` (`email`),
    INDEX `idx_contact_messages_status` (`status`),
    CONSTRAINT `fk_contact_messages_service`
        FOREIGN KEY (`service_id`) REFERENCES `services` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 28. MEDIA LIBRARY
-- =====================================================
CREATE TABLE `media` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `file_name` VARCHAR(255) NOT NULL,
    `original_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(255) NOT NULL,
    `file_type` VARCHAR(50) NOT NULL,
    `mime_type` VARCHAR(100) NOT NULL,
    `file_size` BIGINT UNSIGNED NOT NULL,
    `alt_text` VARCHAR(255) NULL,
    `title` VARCHAR(255) NULL,
    `caption` TEXT NULL,
    `uploaded_by` BIGINT UNSIGNED NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_media_uploaded_by` (`uploaded_by`),
    INDEX `idx_media_file_type` (`file_type`),
    CONSTRAINT `fk_media_uploaded_by`
        FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 29. SITE SETTINGS (Key-Value Store)
-- =====================================================
CREATE TABLE `site_settings` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` LONGTEXT NULL,
    `setting_type` VARCHAR(50) NOT NULL DEFAULT 'string',
    `setting_group` VARCHAR(50) NOT NULL DEFAULT 'general',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_site_settings_key` (`setting_key`),
    INDEX `idx_site_settings_group` (`setting_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 30. GLOBAL SEO SETTINGS (for static pages)
-- =====================================================
CREATE TABLE `seo_settings` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `page_type` VARCHAR(50) NOT NULL,
    `page_identifier` VARCHAR(100) NOT NULL,
    `seo_title` VARCHAR(255) NULL,
    `meta_description` TEXT NULL,
    `focus_keyword` VARCHAR(255) NULL,
    `canonical_url` VARCHAR(255) NULL,
    `og_title` VARCHAR(255) NULL,
    `og_description` TEXT NULL,
    `og_image` VARCHAR(255) NULL,
    `robots` VARCHAR(100) NULL,
    `schema_type` VARCHAR(100) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_seo_settings_page` (`page_type`, `page_identifier`),
    INDEX `idx_seo_settings_page_type` (`page_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 31. REDIRECTS
-- =====================================================
CREATE TABLE `redirects` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `old_url` VARCHAR(255) NOT NULL UNIQUE,
    `new_url` VARCHAR(255) NOT NULL,
    `redirect_type` SMALLINT UNSIGNED NOT NULL DEFAULT 301,
    `status` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_redirects_old_url` (`old_url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- End of Schema
-- =====================================================