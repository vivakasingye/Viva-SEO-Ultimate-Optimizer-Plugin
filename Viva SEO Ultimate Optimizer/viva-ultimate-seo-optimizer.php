<?php
/*
Plugin Name: Viva SEO Ultimate Optimizer
Description: Premium SEO solution developed by Viva Kasingye to boost your website to #1 rankings.
Version: 1.1
Author: Viva Kasingye
Author URI: https://x.com/vivakasingye1
Author Email: vivakasingye2@gmail.com
Author Phone: +256753070283
*/

// Prevent direct access
defined('ABSPATH') || exit;

// ========================
// CORE SEO OPTIMIZATIONS
// ========================

// 1. Essential Meta Tags
function viva_seo_essential_meta() {
    echo '<meta charset="' . esc_attr(get_bloginfo('charset')) . '">' . "\n";
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">' . "\n";
    
    // Non-production environments should not be indexed
    if (wp_get_environment_type() !== 'production') {
        echo '<meta name="robots" content="noindex,nofollow">' . "\n";
    } else {
        echo '<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">' . "\n";
    }
}
add_action('wp_head', 'viva_seo_essential_meta', 1);

// 2. Remove WordPress Version (Security + SEO)
remove_action('wp_head', 'wp_generator');

// ========================
// META BOX IMPLEMENTATION
// ========================

// Add meta box to posts and pages
function viva_seo_add_meta_box() {
    add_meta_box(
        'viva_seo_meta_box',
        'Viva SEO Settings',
        'viva_seo_meta_box_callback',
        ['post', 'page'],
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'viva_seo_add_meta_box');

// Meta box callback function
function viva_seo_meta_box_callback($post) {
    wp_nonce_field('viva_seo_save_meta', 'viva_seo_nonce');
    
    $focus_keyword = get_post_meta($post->ID, '_viva_seo_focus_keyword', true);
    $meta_description = get_post_meta($post->ID, '_viva_seo_meta_description', true);
    $keywords = get_post_meta($post->ID, '_viva_seo_keywords', true);
    ?>
    <div class="viva-seo-fields">
        <div class="viva-seo-field">
            <label for="viva_seo_focus_keyword">Focus Keyword</label>
            <input type="text" id="viva_seo_focus_keyword" name="viva_seo_focus_keyword" 
                   value="<?php echo esc_attr($focus_keyword); ?>" 
                   placeholder="Enter your primary keyword" />
            <p class="description">The main keyword you want to rank for</p>
        </div>
        
        <div class="viva-seo-field">
            <label for="viva_seo_keywords">Additional Keywords</label>
            <input type="text" id="viva_seo_keywords" name="viva_seo_keywords" 
                   value="<?php echo esc_attr($keywords); ?>" 
                   placeholder="keyword1, keyword2, keyword3" />
            <p class="description">Separate keywords with commas</p>
        </div>
        
        <div class="viva-seo-field">
            <label for="viva_seo_meta_description">Meta Description</label>
            <textarea id="viva_seo_meta_description" name="viva_seo_meta_description" 
                      rows="3"><?php echo esc_textarea($meta_description); ?></textarea>
            <p class="description">Recommended length: 150-160 characters</p>
            <div class="viva-seo-counter"><?php echo strlen($meta_description); ?>/160</div>
        </div>
    </div>
    
    <style>
        .viva-seo-fields { padding: 15px; }
        .viva-seo-field { margin-bottom: 20px; }
        .viva-seo-field label { display: block; margin-bottom: 5px; font-weight: 600; }
        .viva-seo-field input[type="text"], 
        .viva-seo-field textarea { width: 100%; padding: 8px; }
        .viva-seo-field textarea { min-height: 80px; }
        .viva-seo-counter { text-align: right; color: #666; font-size: 12px; }
        .description { color: #666; font-size: 13px; margin-top: 3px; }
    </style>
    
    <script>
    jQuery(document).ready(function($) {
        $('#viva_seo_meta_description').on('input', function() {
            $('.viva-seo-counter').text($(this).val().length + '/160');
        });
    });
    </script>
    <?php
}

// Save meta box data
function viva_seo_save_meta($post_id) {
    if (!isset($_POST['viva_seo_nonce']) || 
        !wp_verify_nonce($_POST['viva_seo_nonce'], 'viva_seo_save_meta')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    if (isset($_POST['viva_seo_focus_keyword'])) {
        update_post_meta(
            $post_id,
            '_viva_seo_focus_keyword',
            sanitize_text_field($_POST['viva_seo_focus_keyword'])
        );
    }
    
    if (isset($_POST['viva_seo_keywords'])) {
        update_post_meta(
            $post_id,
            '_viva_seo_keywords',
            sanitize_text_field($_POST['viva_seo_keywords'])
        );
    }
    
    if (isset($_POST['viva_seo_meta_description'])) {
        update_post_meta(
            $post_id,
            '_viva_seo_meta_description',
            sanitize_text_field($_POST['viva_seo_meta_description'])
        );
    }
}
add_action('save_post', 'viva_seo_save_meta');

// ========================
// SCHEMA MARKUP
// ========================

function viva_seo_schema_markup() {
    if (is_singular()) {
        global $post;
        
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => esc_attr(get_the_title()),
            'description' => esc_attr(get_post_meta($post->ID, '_viva_seo_meta_description', true)),
            'datePublished' => esc_attr(get_the_date('c')),
            'dateModified' => esc_attr(get_the_modified_date('c')),
            'author' => [
                '@type' => 'Person',
                'name' => esc_attr(get_the_author())
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => esc_attr(get_bloginfo('name')),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => esc_url(get_site_icon_url())
                ]
            ]
        ];
        
        // Add keywords if available
        $keywords = get_post_meta($post->ID, '_viva_seo_keywords', true);
        if (!empty($keywords)) {
            $schema['keywords'] = esc_attr($keywords);
        }
        
        // Add image if available
        if (has_post_thumbnail()) {
            $schema['image'] = [
                '@type' => 'ImageObject',
                'url' => esc_url(get_the_post_thumbnail_url(null, 'full')),
                'width' => 1200,
                'height' => 630
            ];
        }
        
        echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    }
}
add_action('wp_head', 'viva_seo_schema_markup');

// ========================
// ADMIN DASHBOARD
// ========================

function viva_seo_admin_menu() {
    add_menu_page(
        'Viva SEO',
        'Viva SEO',
        'manage_options',
        'viva-seo',
        'viva_seo_admin_page',
        'dashicons-search',
        80
    );
}
add_action('admin_menu', 'viva_seo_admin_menu');

function viva_seo_admin_page() {
    ?>
    <div class="wrap">
        <h1>Viva SEO Ultimate Optimizer</h1>
        
        <div class="viva-seo-dashboard">
            <div class="viva-seo-card">
                <h2>SEO Settings</h2>
                <form method="post" action="options.php">
                    <?php settings_fields('viva_seo_settings'); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row">Default Meta Description</th>
                            <td>
                                <textarea name="viva_seo_default_description" rows="3" style="width:100%"></textarea>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button(); ?>
                </form>
            </div>
            
            <div class="viva-seo-card">
                <h2>Contact Viva Kasingye</h2>
                <p>For premium SEO services and guaranteed ranking improvements:</p>
                <ul>
                    <li>Twitter: <a href="https://x.com/vivakasingye1" target="_blank">@vivakasingye1</a></li>
                    <li>Email: <a href="mailto:vivakasingye2@gmail.com">vivakasingye2@gmail.com</a></li>
                    <li>Phone: <a href="tel:+256753070283">+256753070283</a></li>
                </ul>
            </div>
        </div>
    </div>
    
    <style>
        .viva-seo-dashboard { display: flex; gap: 20px; margin-top: 20px; }
        .viva-seo-card { background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); flex: 1; }
    </style>
    <?php
}

// ========================
// ACTIVATION HOOKS
// ========================

function viva_seo_activate() {
    // Set default options on activation
    add_option('viva_seo_default_description', '');
    
    // Generate initial sitemap
    viva_seo_generate_sitemap();
}
register_activation_hook(__FILE__, 'viva_seo_activate');

// Sitemap generation function
function viva_seo_generate_sitemap() {
    $posts = get_posts([
        'numberposts' => -1,
        'post_type' => ['post', 'page'],
        'post_status' => 'publish'
    ]);
    
    $sitemap = '<?xml version="1.0" encoding="UTF-8"?>';
    $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    
    foreach ($posts as $post) {
        $sitemap .= '<url>';
        $sitemap .= '<loc>' . esc_url(get_permalink($post->ID)) . '</loc>';
        $sitemap .= '<lastmod>' . esc_attr(get_the_modified_date('c', $post->ID)) . '</lastmod>';
        $sitemap .= '<changefreq>weekly</changefreq>';
        $sitemap .= '<priority>0.8</priority>';
        $sitemap .= '</url>';
    }
    
    $sitemap .= '</urlset>';
    
    file_put_contents(ABSPATH . 'sitemap.xml', $sitemap);
}
