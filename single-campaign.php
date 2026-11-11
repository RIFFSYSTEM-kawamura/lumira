<?php
/**
 * Single Campaign Template
 * Campaign post type detail page
 * LUMIRA WordPress Theme
 */
get_header();

$post_id = get_the_ID();
$hero_image_id = get_post_thumbnail_id( $post_id );
$hero_image_url = $hero_image_id ? wp_get_attachment_image_url( $hero_image_id, 'lumira-hero' ) ?: wp_get_attachment_image_url( $hero_image_id, 'full' ) : '';
$hero_title = get_the_title( $post_id );
?>

<!-- ======================================================
     CAMPAIGN HERO SECTION
     ====================================================== -->
<?php
// Use theme-wide single campaign hero image for all campaign posts
// This ensures all posts have a unified hero section
$opts = lumira_get_options();
$single_hero_img_id = intval( $opts['campaign_single_hero_image_id'] ?? 0 );
$single_hero_img_url = $single_hero_img_id ? wp_get_attachment_image_url( $single_hero_img_id, 'lumira-hero' ) ?: wp_get_attachment_image_url( $single_hero_img_id, 'full' ) : '';
?>
<?php if ( $single_hero_img_url ) : ?>
<section class="page-hero" style="background-image:url('<?php echo esc_url( $single_hero_img_url ); ?>');" aria-label="<?php echo esc_attr( $hero_title ); ?>">
  <div class="page-hero__overlay"></div>
  <div class="page-hero__content">
    <div class="container">
      <h1 class="page-hero__title"><?php echo esc_html( $hero_title ); ?></h1>
      <?php if ( get_the_excerpt() ) : ?>
      <p class="page-hero__subtitle"><?php echo wp_kses_post( get_the_excerpt() ); ?></p>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php else : ?>
<section class="page-hero page-hero--no-image">
  <div class="container">
    <h1 class="page-hero__title"><?php echo esc_html( $hero_title ); ?></h1>
    <?php if ( get_the_excerpt() ) : ?>
    <p class="page-hero__subtitle"><?php echo wp_kses_post( get_the_excerpt() ); ?></p>
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
      <a href="<?php echo esc_url( get_post_type_archive_link( 'campaign' ) ); ?>">キャンペーン</a>
      <span class="breadcrumb-separator">/</span>
      <span class="breadcrumb-current"><?php echo esc_html( get_the_title() ); ?></span>
    </nav>
  </div>
</div>

<!-- ======================================================
     CAMPAIGN CONTENT WITH SIDEBAR
     ====================================================== -->
<main id="main-content" class="page-main">
  <div class="container">
    <div class="campaign-content-wrapper">

      <!-- Main Content -->
      <div class="campaign-main">
        <?php
        while ( have_posts() ) {
          the_post();
          ?>
          <article id="post-<?php the_ID(); ?>" <?php post_class( 'campaign-post' ); ?>>
            <div class="entry-content">
              <?php
              the_content();
              wp_link_pages( array(
                'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'lumira' ),
                'after'  => '</div>',
              ) );
              ?>
            </div>
          </article>

          <!-- Campaign Meta Info -->
          <div class="campaign-meta">
            <div class="campaign-meta__date">
              <span class="label">公開日</span>
              <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'Y年m月d日' ) ); ?></time>
            </div>
            <?php if ( get_the_category() ) : ?>
            <div class="campaign-meta__category">
              <span class="label">カテゴリー</span>
              <div class="category-links">
                <?php
                $categories = get_the_category();
                foreach ( $categories as $cat ) {
                  echo '<a href="' . esc_url( get_category_link( $cat->term_id ) ) . '" class="category-link">' . esc_html( $cat->name ) . '</a>';
                }
                ?>
              </div>
            </div>
            <?php endif; ?>
          </div>

          <!-- Social Share -->
          <div class="campaign-share">
            <span class="label">この記事をシェアする</span>
            <div class="share-buttons">
              <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode( get_permalink() ); ?>&text=<?php echo urlencode( get_the_title() ); ?>" class="share-btn share-twitter" target="_blank" rel="noopener">Twitter</a>
              <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode( get_permalink() ); ?>" class="share-btn share-facebook" target="_blank" rel="noopener">Facebook</a>
              <a href="line://msg/text/<?php echo urlencode( get_the_title() . ' ' . get_permalink() ); ?>" class="share-btn share-line" target="_blank" rel="noopener">LINE</a>
            </div>
          </div>

          <!-- Navigation -->
          <div class="campaign-nav">
            <?php
            $prev_post = get_previous_post();
            $next_post = get_next_post();
            ?>
            <div class="campaign-nav__prev">
              <?php if ( $prev_post ) : ?>
                <a href="<?php echo esc_url( get_permalink( $prev_post ) ); ?>">← 前へ</a>
              <?php endif; ?>
            </div>
            <div class="campaign-nav__next">
              <?php if ( $next_post ) : ?>
                <a href="<?php echo esc_url( get_permalink( $next_post ) ); ?>">次へ →</a>
              <?php endif; ?>
            </div>
          </div>
          <?php
        }
        wp_reset_postdata();
        ?>
      </div>

      <!-- Sidebar -->
      <aside class="campaign-sidebar">
        <!-- Categories -->
        <div class="sidebar-widget">
          <h3 class="sidebar-widget__title">カテゴリー</h3>
          <ul class="sidebar-widget__list">
            <?php
            $categories = get_categories( [ 'taxonomy' => 'category', 'number' => 10 ] );
            foreach ( $categories as $cat ) {
              echo '<li><a href="' . esc_url( get_category_link( $cat->term_id ) ) . '">' . esc_html( $cat->name ) . '</a></li>';
            }
            ?>
          </ul>
        </div>

        <!-- Recent Campaigns - Grouped by Date -->
        <div class="sidebar-widget">
          <h3 class="sidebar-widget__title">過去記事</h3>
          <?php
          $recent = new WP_Query( [
            'post_type' => 'campaign',
            'posts_per_page' => 20,
            'post__not_in' => [ get_the_ID() ],
            'orderby' => 'date',
            'order' => 'DESC',
          ] );

          $posts_by_month = [];
          while ( $recent->have_posts() ) {
            $recent->the_post();
            $year_month = get_the_date( 'Y年n月' );
            if ( ! isset( $posts_by_month[ $year_month ] ) ) {
              $posts_by_month[ $year_month ] = [];
            }
            $posts_by_month[ $year_month ][] = [
              'title' => get_the_title(),
              'url' => get_permalink(),
            ];
          }
          wp_reset_postdata();

          $month_index = 0;
          foreach ( $posts_by_month as $month => $posts ) :
            $is_first = $month_index === 0;
            $is_expanded = $is_first ? 'true' : 'false';
            $is_collapsed_class = $is_first ? '' : 'is-collapsed';
            ?>
            <div class="campaign-archive-month">
              <div class="campaign-archive-month__header" role="button" tabindex="0" aria-expanded="<?php echo esc_attr( $is_expanded ); ?>">
                <span class="campaign-archive-month__header-text"><?php echo esc_html( $month ); ?></span>
                <span class="campaign-archive-month__toggle-icon"></span>
              </div>
              <ul class="campaign-archive-month__items sidebar-widget__list <?php echo esc_attr( $is_collapsed_class ); ?>">
                <?php foreach ( $posts as $post ) : ?>
                <li><a href="<?php echo esc_url( $post['url'] ); ?>"><?php echo esc_html( $post['title'] ); ?></a></li>
                <?php endforeach; ?>
              </ul>
            </div>
            <?php
            $month_index++;
          endforeach;
          ?>
        </div>
      </aside>

    </div>
  </div>
</main>

<?php
get_footer();
