<?php
/**
 * Template Name: Projects Page
 *
 * A template to display all projects.
 */
get_header();
?>

<div class="projects-container">
    <h1>All Projects</h1>

    <?php
    $args = array(
        'post_type'      => 'projects',
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC'
    );
    $projects_query = new WP_Query($args);

    if ($projects_query->have_posts()) :
        while ($projects_query->have_posts()) : $projects_query->the_post();
            $project_id = get_the_ID();
            
            // Fetch description from wp_posts
            $project_description = get_the_content(); // Fetches content from post table

            // Fetch meta values from wp_postmeta
            $project_start_date = get_post_meta($project_id, '_project_start_date', true);
            $project_end_date = get_post_meta($project_id, '_project_end_date', true);

            // Format the dates properly
            $formatted_start_date = !empty($project_start_date) ? date("F j, Y", strtotime($project_start_date)) : 'N/A';
            $formatted_end_date = !empty($project_end_date) ? date("F j, Y", strtotime($project_end_date)) : 'N/A';
    ?>
            <div class="project-card">
                <h2 class="project-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                
                <p class="project-meta">
                    <strong>Start Date:</strong> <?php echo esc_html($formatted_start_date); ?> | 
                    <strong>End Date:</strong> <?php echo esc_html($formatted_end_date); ?>
                </p>

                <p><?php echo !empty($project_description) ? esc_html($project_description) : 'No description available.'; ?></p>
                
                <a class="read-more" href="<?php echo get_permalink(get_the_ID()); ?>">Read More</a>
            </div>
    <?php
        endwhile;
        wp_reset_postdata();
    else :
        echo '<p>No projects found.</p>';
    endif;
    ?>
</div>

<?php get_footer(); ?>