<?php
/*
Template Name: Blog
*/
get_header();
?>

<main class="blog-container">
    <h1 class="blog-title"><?php the_title(); ?></h1>

    <div class="blog-grid">
        <?php
        $query = new WP_Query([
            'post_type'      => 'post',
            'posts_per_page' => 9, // Adjust the number of posts per page
            'paged'          => get_query_var('paged') ? get_query_var('paged') : 1,
        ]);

        if ($query->have_posts()) :
            while ($query->have_posts()) : $query->the_post();
        ?>
                <article class="blog-card">
                    <a href="<?php the_permalink(); ?>">
                        <div class="blog-card-image">
                            <?php if (has_post_thumbnail()) : ?>
                                <?php the_post_thumbnail('medium_large'); ?>
                            <?php else : ?>
                                <img src="<?php echo get_template_directory_uri(); ?>/assets/default-image.jpg" alt="Default Image">
                            <?php endif; ?>
                        </div>
                    </a>
                    <div class="blog-card-content">
                        <h2 class="blog-card-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h2>
                        <p class="blog-card-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 20); ?></p>
                        <a href="<?php the_permalink(); ?>" class="read-more">Read More</a>
                    </div>
                </article>
        <?php
            endwhile;
            wp_reset_postdata();
        else :
            echo '<p>No posts found.</p>';
        endif;
        ?>
    </div>

    <div class="pagination">
        <?php the_posts_pagination(); ?>
    </div>
</main>

<?php get_footer(); ?>