<?php
/*
Plugin Name: Viva SEO Ultimate Pro - Bugema University
Description: AI-powered SEO optimization specifically for Bugema University Kampala Campus. Guaranteed ranking improvements.
Version: 3.0
Author: Viva Kasingye
Author URI: https://x.com/vivakasingye1
Author Email: vivakasingye2@gmail.com
Author Phone: +256753070283
*/

// Exit if accessed directly
defined('ABSPATH') || exit;

// ========================
// PLUGIN CONSTANTS
// ========================
define('VIVA_SEO_VERSION', '3.0');
define('VIVA_SEO_PATH', plugin_dir_path(__FILE__));
define('VIVA_SEO_URL', plugin_dir_url(__FILE__));

// ========================
// CORE FUNCTIONALITY
// ========================

/**
 * 1. INITIAL SETUP FOR BUGEMA UNIVERSITY
 */
function viva_seo_initialize() {
    // Set default options for Bugema University Kampala Campus
    if (get_option('viva_seo_installed') === false) {
        update_option('viva_seo_site_title', 'Bugema University Kampala Campus | Quality Adventist Education');
        update_option('viva_seo_site_description', 'Bugema University Kampala Campus offers quality Adventist education with accredited programs in Uganda');
        update_option('viva_seo_default_keywords', 'Bugema University, Kampala Campus, Adventist Education, Uganda Universities, Higher Education Uganda');
        update_option('viva_seo_installed', time());
        
        // Auto-optimize existing content
        viva_seo_optimize_all_content();
    }
}
register_activation_hook(__FILE__, 'viva_seo_initialize');

/**
 * Optimize all existing content for Bugema University
 */
function viva_seo_optimize_all_content() {
    $posts = get_posts([
        'numberposts' => -1,
        'post_type' => ['post', 'page'],
        'post_status' => 'publish'
    ]);
    
    foreach ($posts as $post) {
        viva_seo_auto_optimize_post($post->ID);
    }
}

/**
 * AI-powered post optimization for Bugema content
 */
function viva_seo_auto_optimize_post($post_id) {
    $post = get_post($post_id);
    
    // 1. Generate focus keyphrase using AI logic
    if (empty(get_post_meta($post_id, '_viva_seo_focus_keyphrase', true))) {
        $title = sanitize_text_field($post->post_title);
        $content = strip_tags($post->post_content);
        
        // AI-powered keyword extraction (simplified)
        $keywords = viva_seo_extract_keywords($title . ' ' . $content);
        $focus_keyphrase = !empty($keywords) ? $keywords[0] : 'Bugema University Kampala';
        
        update_post_meta($post_id, '_viva_seo_focus_keyphrase', $focus_keyphrase);
    }
    
    // 2. Generate meta description using AI logic
    if (empty(get_post_meta($post_id, '_viva_seo_meta_description', true))) {
        $excerpt = !empty($post->post_excerpt) ? $post->post_excerpt : $post->post_content;
        $meta_desc = viva_seo_generate_description($excerpt);
        update_post_meta($post_id, '_viva_seo_meta_description', $meta_desc);
    }
    
    // 3. Generate related keywords
    if (empty(get_post_meta($post_id, '_viva_seo_keywords', true))) {
        $keywords = viva_seo_extract_keywords($post->post_content);
        $keywords_str = implode(', ', array_slice($keywords, 0, 5));
        update_post_meta($post_id, '_viva_seo_keywords', $keywords_str);
    }
    
    // 4. Calculate initial SEO score
    viva_seo_calculate_score($post_id);
}

/**
 * AI-powered keyword extraction
 */
function viva_seo_extract_keywords($text) {
    $text = strip_tags(strtolower($text));
    
    // Remove stop words
    $stop_words = ['a', 'an', 'the', 'and', 'but', 'or', 'for', 'nor', 'on', 'at', 'to', 'from', 'by', 'of', 'in', 'out', 'with', 'about'];
    $words = preg_split('/\s+/', $text);
    $words = array_diff($words, $stop_words);
    
    // Count word frequency
    $word_counts = array_count_values($words);
    arsort($word_counts);
    
    // Prioritize Bugema-related terms
    $bugema_terms = ['bugema', 'university', 'kampala', 'campus', 'education', 'adventist', 'uganda', 'program', 'study'];
    foreach ($bugema_terms as $term) {
        if (isset($word_counts[$term])) {
            $word_counts[$term] *= 2; // Boost Bugema-related terms
        }
    }
    
    arsort($word_counts);
    return array_keys(array_slice($word_counts, 0, 10));
}

/**
 * AI-powered description generation
 */
function viva_seo_generate_description($content) {
    $content = strip_tags($content);
    $sentences = preg_split('/(?<=[.?!])\s+(?=[a-z])/i', $content);
    
    // Find the most important sentence (simplified AI logic)
    $best_sentence = '';
    $max_score = 0;
    
    foreach ($sentences as $sentence) {
        $score = 0;
        
        // Score based on length (ideal 20-25 words)
        $word_count = str_word_count($sentence);
        if ($word_count > 15 && $word_count < 30) $score += 5;
        
        // Score based on Bugema keywords
        $bugema_terms = ['bugema', 'university', 'kampala', 'campus', 'adventist', 'education'];
        foreach ($bugema_terms as $term) {
            if (stripos($sentence, $term) !== false) $score += 3;
        }
        
        // Score based on power words
        $power_words = ['quality', 'accredited', 'learn', 'study', 'program', 'degree', 'excellence'];
        foreach ($power_words as $word) {
            if (stripos($sentence, $word) !== false) $score += 2;
        }
        
        if ($score > $max_score) {
            $max_score = $score;
            $best_sentence = $sentence;
        }
    }
    
    // Fallback if no good sentence found
    if (empty($best_sentence)) {
        $best_sentence = "Bugema University Kampala Campus offers quality Adventist education with accredited programs in Uganda. " . 
                         wp_trim_words($content, 20);
    }
    
    return substr($best_sentence, 0, 160);
}

// ========================
// YOAST-LIKE META BOX (ENHANCED)
// ========================

/**
 * Add the Viva SEO meta box
 */
function viva_seo_add_meta_box() {
    $screens = ['post', 'page'];
    foreach ($screens as $screen) {
        add_meta_box(
            'viva_seo_meta_box',
            'Viva SEO Ultimate Pro - Bugema Optimized',
            'viva_seo_meta_box_callback',
            $screen,
            'normal',
            'high'
        );
    }
}
add_action('add_meta_boxes', 'viva_seo_add_meta_box');

/**
 * Meta box callback function
 */
function viva_seo_meta_box_callback($post) {
    wp_nonce_field('viva_seo_save_meta', 'viva_seo_nonce');
    
    // Get existing values
    $focus_keyphrase = get_post_meta($post->ID, '_viva_seo_focus_keyphrase', true);
    $keywords = get_post_meta($post->ID, '_viva_seo_keywords', true);
    $meta_desc = get_post_meta($post->ID, '_viva_seo_meta_description', true);
    $seo_score = get_post_meta($post->ID, '_viva_seo_score', true);
    
    // Calculate SEO score if not exists
    if (empty($seo_score)) {
        $seo_score = viva_seo_calculate_score($post->ID);
    }
    
    // Perform content analysis
    $content_analysis = viva_seo_analyze_content($post);
    
    // Get AI-suggested keywords
    $suggested_keywords = viva_seo_extract_keywords($post->post_content);
    ?>
    
    <div class="viva-seo-container">
        <!-- Focus Keyphrase -->
        <div class="viva-seo-section">
            <label for="viva_seo_focus_keyphrase">Focus Keyphrase</label>
            <input type="text" id="viva_seo_focus_keyphrase" name="viva_seo_focus_keyphrase" 
                   value="<?php echo esc_attr($focus_keyphrase); ?>" 
                   placeholder="E.g., Bugema University Kampala" />
            <p class="description">The primary keyphrase you want to rank for.</p>
        </div>
        
        <!-- Additional Keywords -->
        <div class="viva-seo-section">
            <label for="viva_seo_keywords">Additional Keywords</label>
            <input type="text" id="viva_seo_keywords" name="viva_seo_keywords" 
                   value="<?php echo esc_attr($keywords); ?>" 
                   placeholder="E.g., Adventist education, Uganda universities" />
            <p class="description">Separate keywords with commas. Suggested: <?php echo implode(', ', array_slice($suggested_keywords, 0, 5)); ?></p>
        </div>
        
        <!-- Meta Description -->
        <div class="viva-seo-section">
            <label for="viva_seo_meta_description">Meta Description</label>
            <textarea id="viva_seo_meta_description" name="viva_seo_meta_description" rows="3"><?php echo esc_textarea($meta_desc); ?></textarea>
            <p class="description">Write a compelling description (155-160 characters).</p>
            <div class="viva-seo-counter"><?php echo strlen($meta_desc); ?>/160</div>
            <button type="button" class="button button-small viva-seo-generate-desc">Generate with AI</button>
        </div>
        
        <!-- SEO Score & Analysis -->
        <div class="viva-seo-analysis">
            <div class="viva-seo-score-circle" data-score="<?php echo esc_attr($seo_score); ?>">
                <svg class="viva-seo-progress" viewBox="0 0 100 100">
                    <circle class="viva-seo-progress-bg" cx="50" cy="50" r="45"></circle>
                    <circle class="viva-seo-progress-fill" cx="50" cy="50" r="45" 
                            stroke-dasharray="283" 
                            stroke-dashoffset="<?php echo 283 - (283 * ($seo_score / 100)); ?>"></circle>
                </svg>
                <div class="viva-seo-score-value"><?php echo esc_html($seo_score); ?></div>
            </div>
            
            <div class="viva-seo-feedback">
                <h3>SEO Analysis for Bugema Content</h3>
                <ul>
                    <?php foreach ($content_analysis as $item): ?>
                        <li class="<?php echo $item['status']; ?>">
                            <span class="dashicons dashicons-<?php echo $item['icon']; ?>"></span>
                            <?php echo $item['message']; ?>
                            <?php if (!empty($item['suggestion'])): ?>
                                <div class="viva-seo-suggestion"><?php echo $item['suggestion']; ?></div>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        
        <!-- Social Media Preview -->
        <div class="viva-seo-preview">
            <h3>Social Media Preview</h3>
            <div class="viva-seo-preview-container">
                <div class="viva-seo-preview-image">
                    <?php if (has_post_thumbnail($post->ID)): ?>
                        <?php echo get_the_post_thumbnail($post->ID, 'medium'); ?>
                    <?php else: ?>
                        <div class="viva-seo-preview-image-placeholder">
                            <span class="dashicons dashicons-format-image"></span>
                            <p>Set featured image for better social sharing</p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="viva-seo-preview-text">
                    <div class="viva-seo-preview-title"><?php echo esc_html($post->post_title); ?></div>
                    <div class="viva-seo-preview-url"><?php echo esc_url(get_permalink($post->ID)); ?></div>
                    <div class="viva-seo-preview-description"><?php echo esc_html($meta_desc); ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        .viva-seo-container { display: flex; flex-wrap: wrap; gap: 20px; }
        .viva-seo-section { flex: 1 1 100%; background: #fff; padding: 15px; border-radius: 4px; box-shadow: 0 1px 1px rgba(0,0,0,0.05); }
        .viva-seo-section label { display: block; margin-bottom: 5px; font-weight: 600; }
        .viva-seo-section input[type="text"], 
        .viva-seo-section textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px; }
        .viva-seo-section textarea { min-height: 80px; }
        .viva-seo-counter { text-align: right; color: #666; font-size: 12px; margin-top: 5px; }
        .description { color: #666; font-size: 13px; margin-top: 3px; }
        .button-small { padding: 0 8px 1px; height: 24px; line-height: 22px; margin-top: 5px; }
        
        /* Score Circle */
        .viva-seo-analysis { flex: 1; display: flex; gap: 20px; background: #fff; padding: 15px; border-radius: 4px; box-shadow: 0 1px 1px rgba(0,0,0,0.05); }
        .viva-seo-score-circle { position: relative; width: 100px; height: 100px; flex-shrink: 0; }
        .viva-seo-progress { transform: rotate(-90deg); }
        .viva-seo-progress-bg { fill: none; stroke: #f0f0f0; stroke-width: 8; }
        .viva-seo-progress-fill { fill: none; stroke: #46b450; stroke-width: 8; stroke-linecap: round; }
        .viva-seo-score-value { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 24px; font-weight: bold; color: #46b450; }
        
        /* Analysis Feedback */
        .viva-seo-feedback { flex: 1; }
        .viva-seo-feedback h3 { margin-top: 0; }
        .viva-seo-feedback ul { list-style: none; padding-left: 0; margin: 0; }
        .viva-seo-feedback li { padding: 5px 0; display: flex; align-items: flex-start; }
        .viva-seo-feedback .dashicons { margin-right: 5px; margin-top: 3px; }
        .viva-seo-feedback .good { color: #46b450; }
        .viva-seo-feedback .bad { color: #dc3232; }
        .viva-seo-feedback .ok { color: #ffb900; }
        .viva-seo-suggestion { font-size: 12px; color: #666; margin-left: 24px; margin-top: 3px; }
        
        /* Social Preview */
        .viva-seo-preview { flex: 1 1 100%; background: #fff; padding: 15px; border-radius: 4px; box-shadow: 0 1px 1px rgba(0,0,0,0.05); }
        .viva-seo-preview-container { display: flex; gap: 15px; font-family: Arial, sans-serif; max-width: 600px; border: 1px solid #ddd; padding: 10px; }
        .viva-seo-preview-image { width: 150px; flex-shrink: 0; }
        .viva-seo-preview-image img { width: 100%; height: auto; }
        .viva-seo-preview-image-placeholder { background: #f5f5f5; height: 150px; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #999; }
        .viva-seo-preview-image-placeholder .dashicons { font-size: 40px; width: 40px; height: 40px; }
        .viva-seo-preview-text { flex: 1; }
        .viva-seo-preview-title { color: #1d2327; font-size: 16px; font-weight: bold; margin-bottom: 5px; }
        .viva-seo-preview-url { color: #50575e; font-size: 12px; margin-bottom: 5px; }
        .viva-seo-preview-description { color: #3c434a; font-size: 14px; line-height: 1.4; }
    </style>
    
    <script>
    jQuery(document).ready(function($) {
        // Character counter
        $('#viva_seo_meta_description').on('input', function() {
            $('.viva-seo-counter').text($(this).val().length + '/160');
        });
        
        // Generate description with AI
        $('.viva-seo-generate-desc').click(function() {
            var button = $(this);
            button.text('Generating...').prop('disabled', true);
            
            $.post(ajaxurl, {
                action: 'viva_seo_generate_description',
                post_id: <?php echo $post->ID; ?>,
                _wpnonce: '<?php echo wp_create_nonce('viva_seo_generate_desc'); ?>'
            }, function(response) {
                if (response.success) {
                    $('#viva_seo_meta_description').val(response.data.description);
                    $('.viva-seo-counter').text(response.data.description.length + '/160');
                } else {
                    alert('Error: ' + response.data.message);
                }
                button.text('Generate with AI').prop('disabled', false);
            }).fail(function() {
                alert('Error generating description');
                button.text('Generate with AI').prop('disabled', false);
            });
        });
        
        // Real-time score calculation
        $('input, textarea').on('input', function() {
            // Calculate new score based on inputs
            var score = 70; // Base score
            
            // Focus keyphrase exists
            if ($('#viva_seo_focus_keyphrase').val().length > 0) score += 10;
            
            // Meta description length
            var descLength = $('#viva_seo_meta_description').val().length;
            if (descLength >= 120 && descLength <= 160) score += 10;
            else if (descLength > 0) score += 5;
            
            // Update score display
            var offset = 283 - (283 * (score / 100));
            $('.viva-seo-progress-fill').attr('stroke-dashoffset', offset);
            $('.viva-seo-score-value').text(score);
            
            // Update score color
            if (score >= 80) {
                $('.viva-seo-progress-fill').attr('stroke', '#46b450');
                $('.viva-seo-score-value').css('color', '#46b450');
            } else if (score >= 60) {
                $('.viva-seo-progress-fill').attr('stroke', '#ffb900');
                $('.viva-seo-score-value').css('color', '#ffb900');
            } else {
                $('.viva-seo-progress-fill').attr('stroke', '#dc3232');
                $('.viva-seo-score-value').css('color', '#dc3232');
            }
        });
    });
    </script>
    <?php
}

/**
 * Save meta box data
 */
function viva_seo_save_meta($post_id) {
    if (!isset($_POST['viva_seo_nonce']) || !wp_verify_nonce($_POST['viva_seo_nonce'], 'viva_seo_save_meta')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Save all fields
    if (isset($_POST['viva_seo_focus_keyphrase'])) {
        update_post_meta($post_id, '_viva_seo_focus_keyphrase', sanitize_text_field($_POST['viva_seo_focus_keyphrase']));
    }
    
    if (isset($_POST['viva_seo_keywords'])) {
        update_post_meta($post_id, '_viva_seo_keywords', sanitize_text_field($_POST['viva_seo_keywords']));
    }
    
    if (isset($_POST['viva_seo_meta_description'])) {
        update_post_meta($post_id, '_viva_seo_meta_description', sanitize_text_field($_POST['viva_seo_meta_description']));
    }
    
    // Recalculate SEO score
    viva_seo_calculate_score($post_id);
}
add_action('save_post', 'viva_seo_save_meta');

/**
 * Calculate SEO score for a post
 */
function viva_seo_calculate_score($post_id) {
    $post = get_post($post_id);
    $score = 50; // Base score
    
    // 1. Focus Keyphrase (20 points)
    $focus_keyphrase = get_post_meta($post_id, '_viva_seo_focus_keyphrase', true);
    if (!empty($focus_keyphrase)) {
        $score += 10;
        
        // Keyphrase in title
        if (strpos(strtolower($post->post_title), strtolower($focus_keyphrase)) !== false) {
            $score += 5;
        }
        
        // Keyphrase in content
        if (strpos(strtolower($post->post_content), strtolower($focus_keyphrase)) !== false) {
            $score += 5;
        }
    }
    
    // 2. Meta Description (15 points)
    $meta_desc = get_post_meta($post_id, '_viva_seo_meta_description', true);
    if (!empty($meta_desc)) {
        $score += 5;
        
        // Length check
        $desc_length = strlen($meta_desc);
        if ($desc_length >= 120 && $desc_length <= 160) {
            $score += 10;
        } elseif ($desc_length > 0) {
            $score += 5;
        }
    }
    
    // 3. Content Quality (25 points)
    $word_count = str_word_count(strip_tags($post->post_content));
    if ($word_count > 1500) $score += 15;
    elseif ($word_count > 800) $score += 10;
    elseif ($word_count > 300) $score += 5;
    
    // Images with alt text
    preg_match_all('/<img[^>]+alt="([^"]*)"/i', $post->post_content, $matches);
    $images_with_alt = count(array_filter($matches[1]));
    $total_images = preg_match_all('/<img/i', $post->post_content);
    if ($total_images > 0 && $images_with_alt/$total_images > 0.8) {
        $score += 10;
    }
    
    // 4. Readability (10 points)
    $paragraphs = substr_count(strtolower($post->post_content), '<p');
    $headings = substr_count(strtolower($post->post_content), '<h');
    if ($paragraphs > 5 && $headings >= 2) {
        $score += 10;
    }
    
    // 5. Featured Image (10 points)
    if (has_post_thumbnail($post_id)) {
        $score += 10;
    }
    
    // Ensure score is between 0-100
    $score = max(0, min(100, $score));
    
    // Save the score
    update_post_meta($post_id, '_viva_seo_score', $score);
    
    return $score;
}

/**
 * Analyze post content with Bugema-specific checks
 */
function viva_seo_analyze_content($post) {
    $analysis = [];
    $focus_keyphrase = get_post_meta($post->ID, '_viva_seo_focus_keyphrase', true);
    $meta_desc = get_post_meta($post->ID, '_viva_seo_meta_description', true);
    
    // 1. Focus keyphrase checks
    if (empty($focus_keyphrase)) {
        $analysis[] = [
            'status' => 'bad',
            'icon' => 'no',
            'message' => 'No focus keyphrase set',
            'suggestion' => 'Add a focus keyphrase related to Bugema University Kampala Campus'
        ];
    } else {
        // Keyphrase in title
        $in_title = strpos(strtolower($post->post_title), strtolower($focus_keyphrase)) !== false;
        $analysis[] = [
            'status' => $in_title ? 'good' : 'bad',
            'icon' => $in_title ? 'yes' : 'no',
            'message' => $in_title ? 'Keyphrase in title' : 'Keyphrase not in title',
            'suggestion' => $in_title ? '' : 'Include your keyphrase in the title'
        ];
        
        // Keyphrase in content
        $in_content = strpos(strtolower($post->post_content), strtolower($focus_keyphrase)) !== false;
        $analysis[] = [
            'status' => $in_content ? 'good' : 'bad',
            'icon' => $in_content ? 'yes' : 'no',
            'message' => $in_content ? 'Keyphrase in content' : 'Keyphrase not in content',
            'suggestion' => $in_content ? '' : 'Use your keyphrase 2-3 times in the content'
        ];
    }
    
    // 2. Meta description checks
    if (empty($meta_desc)) {
        $analysis[] = [
            'status' => 'bad',
            'icon' => 'no',
            'message' => 'No meta description set',
            'suggestion' => 'Add a compelling meta description with Bugema keywords'
        ];
    } else {
        $desc_length = strlen($meta_desc);
        if ($desc_length >= 120 && $desc_length <= 160) {
            $analysis[] = [
                'status' => 'good',
                'icon' => 'yes',
                'message' => 'Good meta description length (' . $desc_length . ' chars)',
                'suggestion' => ''
            ];
        } else {
            $analysis[] = [
                'status' => 'ok',
                'icon' => 'warning',
                'message' => 'Meta description should be 120-160 chars (' . $desc_length . ' chars)',
                'suggestion' => 'Adjust length to optimal 120-160 characters'
            ];
        }
        
        // Check for Bugema keywords
        $bugema_terms = ['bugema', 'university', 'kampala', 'campus', 'adventist', 'education'];
        $has_bugema_term = false;
        foreach ($bugema_terms as $term) {
            if (stripos($meta_desc, $term) !== false) {
                $has_bugema_term = true;
                break;
            }
        }
        
        $analysis[] = [
            'status' => $has_bugema_term ? 'good' : 'bad',
            'icon' => $has_bugema_term ? 'yes' : 'no',
            'message' => $has_bugema_term ? 'Meta includes Bugema terms' : 'Meta missing Bugema terms',
            'suggestion' => $has_bugema_term ? '' : 'Include "Bugema University" or similar in meta'
        ];
    }
    
    // 3. Content length
    $word_count = str_word_count(strip_tags($post->post_content));
    if ($word_count > 1500) {
        $analysis[] = [
            'status' => 'good',
            'icon' => 'yes',
            'message' => 'Excellent content length (' . $word_count . ' words)',
            'suggestion' => ''
        ];
    } elseif ($word_count > 800) {
        $analysis[] = [
            'status' => 'ok',
            'icon' => 'yes',
            'message' => 'Good content length (' . $word_count . ' words)',
            'suggestion' => 'Consider adding more details to reach 1500+ words'
        ];
    } else {
        $analysis[] = [
            'status' => 'bad',
            'icon' => 'no',
            'message' => 'Content too short (' . $word_count . ' words)',
            'suggestion' => 'Expand content to at least 800 words for better ranking'
        ];
    }
    
    // 4. Images with alt text
    preg_match_all('/<img[^>]+alt="([^"]*)"/i', $post->post_content, $matches);
    $images_with_alt = count(array_filter($matches[1]));
    $total_images = preg_match_all('/<img/i', $post->post_content);
    
    if ($total_images > 0) {
        if ($images_with_alt/$total_images > 0.8) {
            $analysis[] = [
                'status' => 'good',
                'icon' => 'yes',
                'message' => 'Good: ' . $images_with_alt . '/' . $total_images . ' images have alt text',
                'suggestion' => ''
            ];
        } else {
            $analysis[] = [
                'status' => 'bad',
                'icon' => 'no',
                'message' => 'Add alt text to images (' . ($total_images - $images_with_alt) . ' missing)',
                'suggestion' => 'Add descriptive alt text with keywords'
            ];
        }
    }
    
    // 5. Featured image
    if (has_post_thumbnail($post->ID)) {
        $analysis[] = [
            'status' => 'good',
            'icon' => 'yes',
            'message' => 'Has featured image',
            'suggestion' => ''
        ];
    } else {
        $analysis[] = [
            'status' => 'bad',
            'icon' => 'no',
            'message' => 'Missing featured image',
            'suggestion' => 'Add a featured image for better social sharing and SEO'
        ];
    }
    
    // 6. Internal links
    $internal_links = preg_match_all('/<a[^>]+href="' . preg_quote(home_url(), '/') . '[^"]*"[^>]*>/i', $post->post_content);
    if ($internal_links >= 3) {
        $analysis[] = [
            'status' => 'good',
            'icon' => 'yes',
            'message' => 'Good internal linking (' . $internal_links . ' links)',
            'suggestion' => ''
        ];
    } else {
        $analysis[] = [
            'status' => 'ok',
            'icon' => 'warning',
            'message' => 'Add more internal links (' . $internal_links . ' found)',
            'suggestion' => 'Link to 3+ other Bugema University pages'
        ];
    }
    
    return $analysis;
}

// ========================
// AJAX HANDLERS
// ========================

/**
 * Generate description via AJAX
 */
function viva_seo_generate_description_ajax() {
    if (!wp_verify_nonce($_POST['_wpnonce'], 'viva_seo_generate_desc')) {
        wp_send_json_error(['message' => 'Nonce verification failed']);
    }
    
    $post_id = intval($_POST['post_id']);
    $post = get_post($post_id);
    
    if (!$post) {
        wp_send_json_error(['message' => 'Post not found']);
    }
    
    $description = viva_seo_generate_description($post->post_content);
    wp_send_json_success(['description' => $description]);
}
add_action('wp_ajax_viva_seo_generate_description', 'viva_seo_generate_description_ajax');

// ========================
// SOCIAL MEDIA & OPEN GRAPH
// ========================

/**
 * Add Open Graph and Twitter Card meta tags
 */
function viva_seo_social_meta() {
    if (!is_singular()) return;
    
    global $post;
    
    // Default image for Bugema University
    $default_image = VIVA_SEO_URL . 'assets/images/bugema-default-social.jpg';
    
    // Get featured image or default
    $image_url = has_post_thumbnail($post->ID) ? 
        get_the_post_thumbnail_url($post->ID, 'large') : 
        $default_image;
    
    // Get description
    $description = get_post_meta($post->ID, '_viva_seo_meta_description', true);
    if (empty($description)) {
        $description = wp_trim_words($post->post_content, 25);
    }
    ?>
    
    <!-- Open Graph / Facebook -->
    <meta property="og:locale" content="en_US" />
    <meta property="og:type" content="article" />
    <meta property="og:title" content="<?php echo esc_attr(get_the_title()); ?>" />
    <meta property="og:description" content="<?php echo esc_attr($description); ?>" />
    <meta property="og:url" content="<?php echo esc_url(get_permalink()); ?>" />
    <meta property="og:site_name" content="Bugema University Kampala Campus" />
    <meta property="og:image" content="<?php echo esc_url($image_url); ?>" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?php echo esc_attr(get_the_title()); ?>" />
    <meta name="twitter:description" content="<?php echo esc_attr($description); ?>" />
    <meta name="twitter:image" content="<?php echo esc_url($image_url); ?>" />
    <?php
}
add_action('wp_head', 'viva_seo_social_meta', 5);

// ========================
// ADMIN DASHBOARD
// ========================

/**
 * Add admin menu
 */
function viva_seo_admin_menu() {
    add_menu_page(
        'Viva SEO Pro - Bugema',
        'Viva SEO',
        'manage_options',
        'viva-seo',
        'viva_seo_admin_page',
        'dashicons-chart-line',
        80
    );
    
    add_submenu_page(
        'viva-seo',
        'Dashboard',
        'Dashboard',
        'manage_options',
        'viva-seo',
        'viva_seo_admin_page'
    );
    
    add_submenu_page(
        'viva-seo',
        'Settings',
        'Settings',
        'manage_options',
        'viva-seo-settings',
        'viva_seo_settings_page'
    );

    add_submenu_page(
        'viva-seo',
        'Content Analysis',
        'Content Analysis',
        'manage_options',
        'viva-seo-analysis',
        'viva_seo_analysis_page'
    );
}
add_action('admin_menu', 'viva_seo_admin_menu');

/**
 * Admin dashboard page
 */
function viva_seo_admin_page() {
    $total_posts = wp_count_posts('post')->publish;
    $total_pages = wp_count_posts('page')->publish;
    $optimized_posts = viva_seo_count_optimized_posts();
    $average_score = viva_seo_get_average_score();
    ?>
    <div class="wrap viva-seo-admin">
        <h1>Viva SEO Ultimate Pro - Bugema University</h1>

        <div class="viva-seo-stats">
            <div class="viva-seo-stat-card">
                <h3>Overall SEO Score</h3>
                <div class="viva-seo-score-large"><?php echo $average_score; ?>/100</div>
                <div class="viva-seo-progress-bar">
                    <div class="viva-seo-progress-fill" style="width: <?php echo $average_score; ?>%"></div>
                </div>
                <p class="viva-seo-stat-desc">Higher scores mean better rankings for Bugema University</p>
            </div>
            
            <div class="viva-seo-stat-card">
                <h3>Optimized Content</h3>
                <div class="viva-seo-count"><?php echo $optimized_posts; ?></div>
                <p>of <?php echo ($total_posts + $total_pages); ?> total posts/pages</p>
                <div class="viva-seo-progress-bar">
                    <div class="viva-seo-progress-fill" style="width: <?php echo ($optimized_posts/($total_posts + $total_pages)) * 100; ?>%"></div>
                </div>
                <button id="viva-seo-optimize-all" class="button button-small">Optimize All Now</button>
            </div>
            
            <div class="viva-seo-stat-card">
                <h3>Content Health</h3>
                <?php
                $good = viva_seo_count_posts_by_score(80, 100);
                $ok = viva_seo_count_posts_by_score(50, 79);
                $bad = viva_seo_count_posts_by_score(0, 49);
                ?>
                <div class="viva-seo-health-meter">
                    <div class="viva-seo-health-good" style="width: <?php echo ($good/($total_posts + $total_pages)) * 100; ?>%"></div>
                    <div class="viva-seo-health-ok" style="width: <?php echo ($ok/($total_posts + $total_pages)) * 100; ?>%"></div>
                    <div class="viva-seo-health-bad" style="width: <?php echo ($bad/($total_posts + $total_pages)) * 100; ?>%"></div>
                </div>
                <div class="viva-seo-health-legend">
                    <span><span class="viva-seo-health-indicator good"></span> Good: <?php echo $good; ?></span>
                    <span><span class="viva-seo-health-indicator ok"></span> OK: <?php echo $ok; ?></span>
                    <span><span class="viva-seo-health-indicator bad"></span> Needs Work: <?php echo $bad; ?></span>
                </div>
                <a href="<?php echo admin_url('admin.php?page=viva-seo-analysis'); ?>" class="button button-small">View Details</a>
            </div>
        </div>
        
        <div class="viva-seo-actions">
            <div class="viva-seo-action-card">
                <h3><span class="dashicons dashicons-admin-generic"></span> SEO Settings</h3>
                <p>Configure default SEO settings for Bugema University</p>
                <a href="<?php echo admin_url('admin.php?page=viva-seo-settings'); ?>" class="button">Configure</a>
            </div>
            
            <div class="viva-seo-action-card">
                <h3><span class="dashicons dashicons-update"></span> Generate Sitemap</h3>
                <p>Create/update your XML sitemap for search engines</p>
                <button id="viva-seo-generate-sitemap" class="button">Generate Now</button>
            </div>
            
            <div class="viva-seo-action-card">
                <h3><span class="dashicons dashicons-star-filled"></span> Premium Support</h3>
                <p>Get guaranteed #1 rankings with Viva's premium services</p>
                <a href="mailto:vivakasingye2@gmail.com?subject=Premium SEO Services for Bugema" class="button button-primary">Contact Viva</a>
            </div>
        </div>
        
        <div class="viva-seo-contact">
            <h3>Need Help? Contact Viva Kasingye Directly</h3>
            <p>For guaranteed ranking improvements and premium SEO services for Bugema University:</p>
            <ul>
                <li><strong>Twitter:</strong> <a href="https://x.com/vivakasingye1" target="_blank">@vivakasingye1</a></li>
                <li><strong>Email:</strong> <a href="mailto:vivakasingye2@gmail.com">vivakasingye2@gmail.com</a></li>
                <li><strong>Phone:</strong> <a href="tel:+256753070283">+256753070283</a></li>
            </ul>
            <p><strong>Special Offer:</strong> Mention "Bugema SEO" for 20% discount on premium services</p>
        </div>
    </div>

    <style>
        .viva-seo-admin { max-width: 1200px; }
        .viva-seo-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0; }
        .viva-seo-stat-card { background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); position: relative; }
        .viva-seo-score-large { font-size: 48px; font-weight: bold; color: #46b450; text-align: center; margin: 10px 0; }
        .viva-seo-count { font-size: 36px; font-weight: bold; color: #2271b1; text-align: center; margin: 10px 0; }
        .viva-seo-progress-bar { height: 10px; background: #f0f0f0; border-radius: 5px; margin: 10px 0; overflow: hidden; }
        .viva-seo-progress-fill { height: 100%; background: #46b450; }
        .viva-seo-stat-desc { text-align: center; color: #666; font-size: 13px; margin-top: 5px; }
        .viva-seo-health-meter { height: 10px; background: #f0f0f0; border-radius: 5px; margin: 15px 0; display: flex; }
        .viva-seo-health-good { background: #46b450; }
        .viva-seo-health-ok { background: #ffb900; }
        .viva-seo-health-bad { background: #dc3232; }
        .viva-seo-health-legend { display: flex; justify-content: space-between; font-size: 13px; }
        .viva-seo-health-indicator { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: 5px; }
        .viva-seo-health-indicator.good { background: #46b450; }
        .viva-seo-health-indicator.ok { background: #ffb900; }
        .viva-seo-health-indicator.bad { background: #dc3232; }
        .viva-seo-actions { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0; }
        .viva-seo-action-card { background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .viva-seo-action-card h3 { margin-top: 0; display: flex; align-items: center; }
        .viva-seo-action-card h3 .dashicons { margin-right: 10px; color: #2271b1; }
        .viva-seo-action-card .button { margin-top: 10px; }
        .viva-seo-contact { background: #f8f9fc; padding: 20px; border-radius: 5px; border-left: 4px solid #00a0d2; }
        .viva-seo-contact h3 { margin-top: 0; }
        .viva-seo-contact ul { list-style: none; padding-left: 0; }
        .viva-seo-contact li { margin-bottom: 5px; }
    </style>

    <script>
    jQuery(document).ready(function($) {
        // Optimize all content
        $('#viva-seo-optimize-all').click(function() {
            if (confirm('This will analyze and optimize all your existing content for Bugema University. Continue?')) {
                $(this).text('Optimizing...').prop('disabled', true);
                $.post(ajaxurl, {
                    action: 'viva_seo_optimize_all',
                    _wpnonce: '<?php echo wp_create_nonce('viva_seo_optimize_all'); ?>'
                }, function(response) {
                    if (response.success) {
                        alert('Optimization complete! ' + response.data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                }).fail(function() {
                    alert('Error during optimization');
                }).always(function() {
                    $('#viva-seo-optimize-all').text('Optimize All Now').prop('disabled', false);
                });
            }
        });

        // Generate sitemap
        $('#viva-seo-generate-sitemap').click(function() {
            $(this).text('Generating...').prop('disabled', true);
            $.post(ajaxurl, {
                action: 'viva_seo_generate_sitemap',
                _wpnonce: '<?php echo wp_create_nonce('viva_seo_generate_sitemap'); ?>'
            }, function(response) {
                if (response.success) {
                    alert('Sitemap generated successfully!');
                } else {
                    alert('Error: ' + response.data.message);
                }
            }).fail(function() {
                alert('Error generating sitemap');
            }).always(function() {
                $('#viva-seo-generate-sitemap').text('Generate Now').prop('disabled', false);
            });
        });
    });
    </script>
    <?php
}

/**
 * Settings page
 */
function viva_seo_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    // Save settings if form submitted
    if (isset($_POST['viva_seo_settings_nonce']) && wp_verify_nonce($_POST['viva_seo_settings_nonce'], 'viva_seo_save_settings')) {
        update_option('viva_seo_site_title', sanitize_text_field($_POST['site_title']));
        update_option('viva_seo_site_description', sanitize_text_field($_POST['site_description']));
        update_option('viva_seo_default_keywords', sanitize_text_field($_POST['default_keywords']));
        update_option('viva_seo_fb_app_id', sanitize_text_field($_POST['fb_app_id']));
        update_option('viva_seo_twitter_handle', sanitize_text_field($_POST['twitter_handle']));

        echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
    }

    // Get current settings
    $site_title = get_option('viva_seo_site_title', 'Bugema University Kampala Campus | Quality Adventist Education');
    $site_description = get_option('viva_seo_site_description', 'Bugema University Kampala Campus offers quality Adventist education with accredited programs in Uganda');
    $default_keywords = get_option('viva_seo_default_keywords', 'Bugema University, Kampala Campus, Adventist Education, Uganda Universities, Higher Education Uganda');
    $fb_app_id = get_option('viva_seo_fb_app_id', '');
    $twitter_handle = get_option('viva_seo_twitter_handle', '');
    ?>
    <div class="wrap">
        <h1>Viva SEO Settings - Bugema University</h1>

        <form method="post" action="">
            <?php wp_nonce_field('viva_seo_save_settings', 'viva_seo_settings_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="site_title">Site Title</label></th>
                    <td>
                        <input type="text" name="site_title" id="site_title" value="<?php echo esc_attr($site_title); ?>" class="regular-text">
                        <p class="description">This appears in search results and social shares</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="site_description">Site Description</label></th>
                    <td>
                        <textarea name="site_description" id="site_description" rows="3" class="large-text"><?php echo esc_textarea($site_description); ?></textarea>
                        <p class="description">Default description for your entire site</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="default_keywords">Default Keywords</label></th>
                    <td>
                        <input type="text" name="default_keywords" id="default_keywords" value="<?php echo esc_attr($default_keywords); ?>" class="large-text">
                        <p class="description">Comma-separated list of keywords relevant to Bugema University</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="fb_app_id">Facebook App ID</label></th>
                    <td>
                        <input type="text" name="fb_app_id" id="fb_app_id" value="<?php echo esc_attr($fb_app_id); ?>" class="regular-text">
                        <p class="description">For enhanced Facebook sharing analytics</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="twitter_handle">Twitter Handle</label></th>
                    <td>
                        <input type="text" name="twitter_handle" id="twitter_handle" value="<?php echo esc_attr($twitter_handle); ?>" class="regular-text" placeholder="@username">
                        <p class="description">Your official Twitter account (without @)</p>
                    </td>
                </tr>
            </table>
            
            <?php submit_button('Save Settings'); ?>
        </form>
    </div>
    <?php
}

/**
 * Content analysis page
 */
function viva_seo_analysis_page() {
    $posts = get_posts([
        'numberposts' => -1,
        'post_type' => ['post', 'page'],
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC'
    ]);
    ?>
    <div class="wrap">
        <h1>Content Analysis - Bugema University</h1>

        <div class="viva-seo-analysis-table-container">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th width="100">Type</th>
                        <th width="100">SEO Score</th>
                        <th width="120">Focus Keyphrase</th>
                        <th width="120">Last Modified</th>
                        <th width="100">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $post): 
                        $score = get_post_meta($post->ID, '_viva_seo_score', true);
                        $focus_keyphrase = get_post_meta($post->ID, '_viva_seo_focus_keyphrase', true);
                        $edit_url = get_edit_post_link($post->ID);
                        $view_url = get_permalink($post->ID);
                    ?>
                        <tr>
                            <td>
                                <strong><a href="<?php echo esc_url($edit_url); ?>"><?php echo esc_html($post->post_title); ?></a></strong>
                                <div class="row-actions">
                                    <span class="edit"><a href="<?php echo esc_url($edit_url); ?>">Edit</a> | </span>
                                    <span class="view"><a href="<?php echo esc_url($view_url); ?>" target="_blank">View</a></span>
                                </div>
                            </td>
                            <td><?php echo esc_html($post->post_type); ?></td>
                            <td>
                                <div class="viva-seo-score-indicator" data-score="<?php echo esc_attr($score); ?>">
                                    <?php echo esc_html($score); ?>
                                </div>
                            </td>
                            <td><?php echo esc_html($focus_keyphrase); ?></td>
                            <td><?php echo date('M j, Y', strtotime($post->post_modified)); ?></td>
                            <td>
                                <a href="<?php echo esc_url($edit_url); ?>" class="button button-small">Optimize</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <style>
        .viva-seo-analysis-table-container { margin-top: 20px; }
        .viva-seo-score-indicator { display: inline-block; width: 30px; height: 30px; border-radius: 50%; text-align: center; line-height: 30px; font-weight: bold; color: white; }
        .viva-seo-score-indicator[data-score="0"] { background: #f0f0f0; color: #333; }
        .viva-seo-score-indicator[data-score^="9"] { background: #46b450; }
        .viva-seo-score-indicator[data-score^="8"] { background: #6bc167; }
        .viva-seo-score-indicator[data-score^="7"] { background: #8fd18b; }
        .viva-seo-score-indicator[data-score^="6"] { background: #b3e0b0; color: #333; }
        .viva-seo-score-indicator[data-score^="5"] { background: #ffb900; color: #333; }
        .viva-seo-score-indicator[data-score^="4"] { background: #ff8e00; color: white; }
        .viva-seo-score-indicator[data-score^="3"] { background: #e65054; color: white; }
        .viva-seo-score-indicator[data-score^="2"] { background: #dc3232; color: white; }
        .viva-seo-score-indicator[data-score^="1"] { background: #a00; color: white; }
        .viva-seo-score-indicator[data-score^="0"] { background: #f0f0f0; color: #333; }
    </style>
    <?php
}

// ========================
// HELPER FUNCTIONS
// ========================

/**
 * Count optimized posts (with focus keyphrase)
 */
function viva_seo_count_optimized_posts() {
    $posts = get_posts([
        'numberposts' => -1,
        'post_type' => ['post', 'page'],
        'meta_key' => '_viva_seo_focus_keyphrase',
        'meta_compare' => 'EXISTS'
    ]);

    return count($posts);
}

/**
 * Count posts by score range
 */
function viva_seo_count_posts_by_score($min, $max) {
    global $wpdb;

    $count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $wpdb->postmeta
        WHERE meta_key = '_viva_seo_score'
        AND meta_value BETWEEN %d AND %d",
        $min, $max
    ));

    return $count ? $count : 0;
}

/**
 * Get average SEO score
 */
function viva_seo_get_average_score() {
    global $wpdb;

    $average = $wpdb->get_var(
        "SELECT AVG(meta_value) FROM $wpdb->postmeta
        WHERE meta_key = '_viva_seo_score'"
    );

    return $average ? round($average) : 0;
}

// ========================
// FRONTEND OUTPUT
// ========================

/**
 * Output optimized title tag
 */
function viva_seo_title_tag() {
    // Skip if SEO plugin is already handling titles
    if (defined('WPSEO_VERSION')) return;

    if (is_singular()) {
        $title = get_the_title();
        $site_title = get_option('viva_seo_site_title', get_bloginfo('name'));
        echo '<title>' . esc_html($title . ' | ' . $site_title) . '</title>' . "\n";
    } else {
        // Default WordPress title for other pages
        wp_title('|', true, 'right');
    }
}
add_action('wp_head', 'viva_seo_title_tag', 1);

/**
 * Output meta description
 */
function viva_seo_meta_description() {
    if (is_singular()) {
        $meta_desc = get_post_meta(get_the_ID(), '_viva_seo_meta_description', true);
        if (!empty($meta_desc)) {
            echo '<meta name="description" content="' . esc_attr($meta_desc) . '">' . "\n";
        }
    } elseif (is_home() || is_front_page()) {
        $site_desc = get_option('viva_seo_site_description', get_bloginfo('description'));
        if (!empty($site_desc)) {
            echo '<meta name="description" content="' . esc_attr($site_desc) . '">' . "\n";
        }
    }
}
add_action('wp_head', 'viva_seo_meta_description', 2);

// ========================
// ACTIVATION/DEACTIVATION
// ========================

/**
 * Plugin activation
 */
function viva_seo_activate() {
    // Set default options for Bugema University
    update_option('viva_seo_site_title', 'Bugema University Kampala Campus | Quality Adventist Education');
    update_option('viva_seo_site_description', 'Bugema University Kampala Campus offers quality Adventist education with accredited programs in Uganda');
    update_option('viva_seo_default_keywords', 'Bugema University, Kampala Campus, Adventist Education, Uganda Universities, Higher Education Uganda');

    // Generate initial sitemap
    viva_seo_generate_sitemap();

    // Schedule daily optimization
    if (!wp_next_scheduled('viva_seo_daily_optimization')) {
        wp_schedule_event(time(), 'daily', 'viva_seo_daily_optimization');
    }
}
register_activation_hook(__FILE__, 'viva_seo_activate');

/**
 * Plugin deactivation
 */
function viva_seo_deactivate() {
    // Clear scheduled events
    wp_clear_scheduled_hook('viva_seo_daily_optimization');
}
register_deactivation_hook(__FILE__, 'viva_seo_deactivate');

// ========================
// DAILY OPTIMIZATION TASK
// ========================

/**
 * Daily optimization tasks
 */
function viva_seo_daily_optimization() {
    // Update sitemap
    viva_seo_generate_sitemap();

    // Recalculate scores for all posts
    $posts = get_posts(['numberposts' => -1]);
    foreach ($posts as $post) {
        viva_seo_calculate_score($post->ID);
    }
}
add_action('viva_seo_daily_optimization', 'viva_seo_daily_optimization');

// ========================
// SITEMAP GENERATION
// ========================

/**
 * Generate XML sitemap
 */
function viva_seo_generate_sitemap() {
    $posts = get_posts([
        'numberposts' => -1,
        'post_type' => ['post', 'page'],
        'post_status' => 'publish'
    ]);
    
    $sitemap = '<?xml version="1.0" encoding="UTF-8"?>';
    $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    
    // Add homepage
    $sitemap .= '<url>';
    $sitemap .= '<loc>' . esc_url(home_url('/')) . '</loc>';
    $sitemap .= '<lastmod>' . date('Y-m-d') . '</lastmod>';
    $sitemap .= '<changefreq>daily</changefreq>';
    $sitemap .= '<priority>1.0</priority>';
    $sitemap .= '</url>';
    
    // Add posts and pages
    foreach ($posts as $post) {
        $sitemap .= '<url>';
        $sitemap .= '<loc>' . esc_url(get_permalink($post->ID)) . '</loc>';
        $sitemap .= '<lastmod>' . date('Y-m-d', strtotime($post->post_modified)) . '</lastmod>';
        $sitemap .= '<changefreq>weekly</changefreq>';
        $sitemap .= '<priority>0.8</priority>';
        $sitemap .= '</url>';
    }
    
    $sitemap .= '</urlset>';
    
    // Save sitemap
    file_put_contents(ABSPATH . 'sitemap.xml', $sitemap);
    
    return true;
}

/**
 * Generate sitemap via AJAX
 */
function viva_seo_generate_sitemap_ajax() {
    if (!wp_verify_nonce($_POST['_wpnonce'], 'viva_seo_generate_sitemap')) {
        wp_send_json_error(['message' => 'Nonce verification failed']);
    }
    
    $result = viva_seo_generate_sitemap();
    
    if ($result) {
        wp_send_json_success(['message' => 'Sitemap generated successfully']);
    } else {
        wp_send_json_error(['message' => 'Error generating sitemap']);
    }
}
add_action('wp_ajax_viva_seo_generate_sitemap', 'viva_seo_generate_sitemap_ajax');

/**
 * Optimize all content via AJAX
 */
function viva_seo_optimize_all_ajax() {
    if (!wp_verify_nonce($_POST['_wpnonce'], 'viva_seo_optimize_all')) {
        wp_send_json_error(['message' => 'Nonce verification failed']);
    }
    
    viva_seo_optimize_all_content();
    
    wp_send_json_success(['message' => 'All content optimized successfully']);
}
add_action('wp_ajax_viva_seo_optimize_all', 'viva_seo_optimize_all_ajax');
