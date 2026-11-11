<?php
/**
 * Page Template
 * Fixed Page with Hero, Breadcrumb, Shortcoder support
 * LUMIRA WordPress Theme
 */
get_header();

$post_id = get_the_ID();
$hero_image_id = get_post_meta( $post_id, '_lumira_page_hero_image', true );
$hero_title = get_post_meta( $post_id, '_lumira_page_hero_title', true ) ?: get_the_title();
$hero_subtitle = get_post_meta( $post_id, '_lumira_page_hero_subtitle', true ) ?: '';

$hero_img_url = $hero_image_id ? wp_get_attachment_image_url( $hero_image_id, 'lumira-hero' ) ?: wp_get_attachment_image_url( $hero_image_id, 'full' ) : '';
?>

<!-- ======================================================
     PAGE HERO SECTION
     ====================================================== -->
<?php if ( $hero_img_url ) : ?>
<section class="page-hero" style="background-image:url('<?php echo esc_url( $hero_img_url ); ?>');" aria-label="<?php echo esc_attr( $hero_title ); ?>">
  <div class="page-hero__overlay"></div>
  <div class="page-hero__content">
    <div class="container">
      <h1 class="page-hero__title"><?php echo esc_html( $hero_title ); ?></h1>
      <?php if ( $hero_subtitle ) : ?>
      <p class="page-hero__subtitle"><?php echo wp_kses_post( $hero_subtitle ); ?></p>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php else : ?>
<section class="page-hero page-hero--no-image">
  <div class="container">
    <h1 class="page-hero__title"><?php echo esc_html( $hero_title ); ?></h1>
    <?php if ( $hero_subtitle ) : ?>
    <p class="page-hero__subtitle"><?php echo wp_kses_post( $hero_subtitle ); ?></p>
    <?php endif; ?>
  </div>
</section>
<?php endif; ?>

<!-- ======================================================
     BREADCRUMB
     ====================================================== -->
<div class="page-breadcrumb">
  <div class="container">
    <nav aria-label="パンくずリスト">
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>">ホーム</a>
      <span class="breadcrumb-separator">/</span>
      <span class="breadcrumb-current"><?php echo esc_html( get_the_title() ); ?></span>
    </nav>
  </div>
</div>

<!-- ======================================================
     PAGE CONTENT
     ====================================================== -->
<main id="main-content" class="page-main">
  <div class="container">
    <div class="page-content">
      <?php
      while ( have_posts() ) {
        the_post();
        ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
          <div class="entry-content">
            <?php
            // Support Shortcoder and do_shortcode
            the_content();
            wp_link_pages( array(
              'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'lumira' ),
              'after'  => '</div>',
            ) );
            ?>
          </div>
        </article>
        <?php
      }
      wp_reset_postdata();
      ?>
    </div>
  </div>
</main>

<?php
get_footer();
