<?php
// Get the current post type
$post_type = get_post_type();
if ($post_type === 'post') {
    $template = get_template_directory() . '/template/template-single-blog.php';
} elseif ($post_type === 'projects') {
    $template = get_template_directory() . '/template/template-single-project.php';
}


if (file_exists($template)) {
    include $template;
    exit; 
} else {
    get_header();
    echo '<h2>Template not found!</h2>';
    get_footer();
}
?>