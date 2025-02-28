<?php
// Load Theme Assets
function ikonic_enqueue_assets() {
    wp_enqueue_style('ikonic-style', get_stylesheet_uri());
    wp_enqueue_style('ikonic-custom-style', get_template_directory_uri() . '/assets/ik-style.css', array(), '1.0', 'all');
    wp_enqueue_script('ikonic-scripts', get_template_directory_uri() . '/assets/ik-script.js', array('jquery'), '1.0', true);
}
add_action('wp_enqueue_scripts', 'ikonic_enqueue_assets');

// Register Menus
function ikonic_register_menus() {
    register_nav_menus(array(
        'primary-menu' => __('Primary Menu', 'ikonic-dev'),
    ));
}
add_action('after_setup_theme', 'ikonic_register_menus');

// Enable Theme Support
function ikonic_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');
}
add_action('after_setup_theme', 'ikonic_theme_setup');


// Add Custom Post Type
function ikonic_register_projects_cpt() {
    $labels = array(
        'name'               => __('Projects', 'ikonic-dev'),
        'singular_name'      => __('Project', 'ikonic-dev'),
        'menu_name'          => __('Projects', 'ikonic-dev'),
        'add_new'            => __('Add New', 'ikonic-dev'),
        'add_new_item'       => __('Add New Project', 'ikonic-dev'),
        'edit_item'          => __('Edit Project', 'ikonic-dev'),
        'new_item'           => __('New Project', 'ikonic-dev'),
        'view_item'          => __('View Project', 'ikonic-dev'),
        'all_items'          => __('All Projects', 'ikonic-dev'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-portfolio',
        'supports'           => array('title', 'editor', 'thumbnail'),
        'has_archive'        => true,
        'rewrite'            => array('slug' => 'projects'),
    );

    register_post_type('projects', $args);
}
add_action('init', 'ikonic_register_projects_cpt');

function ikonic_add_project_meta_boxes() {
    add_meta_box(
        'ikonic_project_details',
        'Project Details',
        'ikonic_project_meta_callback',
        'projects',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'ikonic_add_project_meta_boxes');

//CPT With Code
function ikonic_project_meta_callback($post) {
    $project_name = get_post_meta($post->ID, '_project_name', true);
    $project_desc = get_post_meta($post->ID, '_project_desc', true);
    $start_date = get_post_meta($post->ID, '_project_start_date', true);
    $end_date = get_post_meta($post->ID, '_project_end_date', true);
    $project_url = get_post_meta($post->ID, '_project_url', true);

    wp_nonce_field('ikonic_project_nonce', 'ikonic_project_nonce_field');

    echo '<label>Project Name:</label>';
    echo '<input type="text" name="project_name" value="' . esc_attr($project_name) . '" class="widefat"/>';

    echo '<label>Project Description:</label>';
    echo '<textarea name="project_desc" class="widefat">' . esc_textarea($project_desc) . '</textarea>';

    echo '<label>Start Date:</label>';
    echo '<input type="date" name="project_start_date" value="' . esc_attr($start_date) . '" class="widefat"/>';

    echo '<label>End Date:</label>';
    echo '<input type="date" name="project_end_date" value="' . esc_attr($end_date) . '" class="widefat"/>';

    echo '<label>Project URL:</label>';
    echo '<input type="url" name="project_url" value="' . esc_url($project_url) . '" class="widefat"/>';
}

function ikonic_save_project_meta($post_id) {
    if (!isset($_POST['ikonic_project_nonce_field']) || !wp_verify_nonce($_POST['ikonic_project_nonce_field'], 'ikonic_project_nonce')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    update_post_meta($post_id, '_project_name', sanitize_text_field($_POST['project_name']));
    update_post_meta($post_id, '_project_desc', sanitize_textarea_field($_POST['project_desc']));
    update_post_meta($post_id, '_project_start_date', sanitize_text_field($_POST['project_start_date']));
    update_post_meta($post_id, '_project_end_date', sanitize_text_field($_POST['project_end_date']));
    update_post_meta($post_id, '_project_url', esc_url($_POST['project_url']));
}
add_action('save_post', 'ikonic_save_project_meta');

function ikonic_redirect_project_templates($template) {
    if (is_post_type_archive('projects')) {
        $new_template = locate_template(array('template/template-projects.php'));
        if (!empty($new_template)) {
            return $new_template;
        }
    }
    return $template;
}
add_filter('template_include', 'ikonic_redirect_project_templates');


// API EndPoint
function ikonic_get_projects_api() {
    $projects = get_posts(array('post_type' => 'projects', 'numberposts' => -1));

    $data = array();
    foreach ($projects as $project) {
        $data[] = array(
            'title' => $project->post_title,
            'url' => get_post_meta($project->ID, '_project_url', true),
            'start_date' => get_post_meta($project->ID, '_project_start_date', true),
            'end_date' => get_post_meta($project->ID, '_project_end_date', true),
        );
    }
    return rest_ensure_response($data);
}

add_action('rest_api_init', function () {
    register_rest_route('ikonic/v1', '/projects/', array(
        'methods' => 'GET',
        'callback' => 'ikonic_get_projects_api',
    ));
});


// For media file
function ikonic_add_unused_media_menu() {
    add_menu_page(
        'Unused Media',
        'Unused Media',
        'manage_options',
        'ikonic-unused-media',
        'ikonic_unused_media_page',
        'dashicons-trash',
        17
    );
}
add_action('admin_menu', 'ikonic_add_unused_media_menu');

function ikonic_get_all_media_status() {
    global $wpdb;

    $attachments = $wpdb->get_results("SELECT ID, post_title, guid FROM {$wpdb->posts} WHERE post_type = 'attachment'");

    $media_list = [];

    foreach ($attachments as $attachment) {
        $media_id = $attachment->ID;
        $guid = esc_url($attachment->guid);
        $status = 'Unused'; 

        $file_name = basename(parse_url($guid, PHP_URL_PATH));
        $partial_url = parse_url($guid, PHP_URL_PATH);

        $used_in_post = $wpdb->get_var($wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} WHERE post_content LIKE %s LIMIT 1",
            '%' . $wpdb->esc_like($partial_url) . '%'
        ));

        $used_in_meta = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_id FROM {$wpdb->postmeta} WHERE meta_value LIKE %s LIMIT 1",
            '%' . $wpdb->esc_like($partial_url) . '%'
        ));

        $used_as_thumbnail = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_thumbnail_id' AND meta_value = %d LIMIT 1",
            $media_id
        ));

        $used_in_options = $wpdb->get_var($wpdb->prepare(
            "SELECT option_id FROM {$wpdb->options} WHERE option_value LIKE %s LIMIT 1",
            '%' . $wpdb->esc_like($partial_url) . '%'
        ));

        $widgets = $wpdb->get_results("SELECT option_value FROM {$wpdb->options} WHERE option_name LIKE 'widget_%'");
        $used_in_widgets = false;
        foreach ($widgets as $widget) {
            $unserialized = maybe_unserialize($widget->option_value);
            if (is_array($unserialized) && strpos(wp_json_encode($unserialized), $partial_url) !== false) {
                $used_in_widgets = true;
                break;
            }
        }

        if ($used_in_post || $used_in_meta || $used_as_thumbnail || $used_in_options || $used_in_widgets) {
            $status = 'Used';
        }

        $media_list[] = [
            'ID' => $media_id,
            'title' => $attachment->post_title,
            'guid' => $guid,
            'status' => $status
        ];
    }

    return $media_list;
}


function ikonic_unused_media_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $media_list = ikonic_get_all_media_status();

    echo '<div class="wrap">';
    echo '<h1>Media Usage Report</h1>';

    if (!empty($media_list)) {
        echo '<table class="widefat">';
        echo '<thead><tr><th>Thumbnail</th><th>File Name</th><th>Status</th><th>Action</th></tr></thead>';
        echo '<tbody>';

        foreach ($media_list as $media) {
            $thumb = wp_get_attachment_thumb_url($media['ID']);
            $is_used = ($media['status'] === 'Used');

            echo '<tr id="media-row-' . esc_attr($media['ID']) . '">';
            echo '<td><img src="' . esc_url($thumb) . '" width="50"></td>';
            echo '<td>' . esc_html($media['title']) . '</td>';
            echo '<td><strong>' . esc_html($media['status']) . '</strong></td>';

            if ($is_used) {
                echo '<td><button class="ikonic-delete-media" disabled style="background-color: #ccc; cursor: not-allowed;">Delete</button></td>';
            } else {
                echo '<td><button class="ikonic-delete-media" data-id="' . esc_attr($media['ID']) . '">Delete</button></td>';
            }

            echo '</tr>';
        }

        echo '</tbody></table>';
    } else {
        echo '<p>No media files found.</p>';
    }

    echo '</div>';

    // AJAX script
    ?>
    <script>
        jQuery(document).ready(function($) {
            $(".ikonic-delete-media").on("click", function() {
                var mediaID = $(this).data("id");
                var row = $("#media-row-" + mediaID);

                if (!mediaID) return;

                if (confirm("Are you sure you want to delete this media file?")) {
                    $.ajax({
                        url: ajaxurl,
                        type: "POST",
                        data: {
                            action: "ikonic_delete_media",
                            media_id: mediaID,
                            nonce: "<?php echo wp_create_nonce('ikonic_delete_media_nonce'); ?>"
                        },
                        success: function(response) {
                            if (response.success) {
                                row.fadeOut();
                            } else {
                                alert("Failed to delete media.");
                            }
                        }
                    });
                }
            });
        });
    </script>
    <?php
}


function ikonic_delete_media_ajax() {

    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ikonic_delete_media_nonce')) {
        wp_send_json_error('Invalid request');
    }

    // Verify user capability
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }

    $media_id = intval($_POST['media_id']);
    
    if (!$media_id) {
        wp_send_json_error('Invalid media ID');
    }

    $deleted = wp_delete_attachment($media_id, true);

    if ($deleted) {
        wp_send_json_success();
    } else {
        wp_send_json_error('Failed to delete media');
    }
}
add_action('wp_ajax_ikonic_delete_media', 'ikonic_delete_media_ajax');


?>
