<?php get_header(); ?>

<main class="ik-single-project">
    <div class="container">
        <div class="project-content">
            <h1 class="project-title"><?php the_title(); ?></h1>
            
            <div class="project-meta">
                <p><strong>Start Date:</strong> <?php echo esc_html(get_post_meta(get_the_ID(), '_project_start_date', true)); ?></p>
                <p><strong>End Date:</strong> <?php echo esc_html(get_post_meta(get_the_ID(), '_project_end_date', true)); ?></p>
            </div>

            <div class="project-description">
                <p><?php echo esc_html(get_post_meta(get_the_ID(), '_project_desc', true)); ?></p>
            </div>

        </div>
    </div>
</main>

<?php get_footer(); ?>
