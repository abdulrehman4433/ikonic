<?php get_header(); ?>
<main>
    <?php
    while (have_posts()) : the_post();
        the_title('<h1>', '</h1>');
        the_content();
    endwhile;
    ?>
</main>
<?php get_footer(); ?>