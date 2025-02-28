<?php get_header(); ?>

<main class="single-container">
    <?php if (have_posts()) : while (have_posts()) : the_post();
        $project_id = get_the_ID();

        // Fetch meta values
        $project_start_date = get_post_meta($project_id, 'project_start_date', true);
        $project_end_date = get_post_meta($project_id, 'project_end_date', true);

        // Format dates
        $formatted_start_date = !empty($project_start_date) ? date("F j, Y", strtotime($project_start_date)) : 'N/A';
        $formatted_end_date = !empty($project_end_date) ? date("F j, Y", strtotime($project_end_date)) : 'N/A';
    ?>

        <article class="single-content">
            <h1 class="single-title"><?php the_title(); ?></h1>

            <div class="single-meta">
                <span>By <?php the_author(); ?></span> | 
                <span>Start Date: <?php echo esc_html($formatted_start_date); ?></span> | 
                <span>End Date: <?php echo esc_html($formatted_end_date); ?></span>
            </div>

            <div class="single-body">
                <?php the_content(); ?>
            </div>

            <!-- Post Tags -->
            <div class="single-tags">
                <?php the_tags('<strong>Tags:</strong> ', ', ', ''); ?>
            </div>

        </article>

    <?php endwhile;
    endif; ?>
</main>

<?php get_footer(); ?>