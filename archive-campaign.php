<?php
/**
 * Campaign Archive Template
 * Campaign post type list page
 * LUMIRA WordPress Theme
 */
get_header();

$opts = lumira_get_options();
?>

<!-- ======================================================
     CAMPAIGN ARCHIVE HERO
     ====================================================== -->
<?php
$opts = lumira_get_options();
$archive_hero_img_id = intval( $opts['campaign_archive_hero_image_id'] ?? 0 );
$archive_hero_img_url = $archive_hero_img_id ? wp_get_attachment_image_url( $archive_hero_img_id, 'lumira-hero' ) ?: wp_get_attachment_image_url( $archive_hero_img_id, 'full' ) : '';
?>
<?php if ( $archive_hero_img_url ) : ?>
<section class="page-hero" style="background-image:url('<?php echo esc_url( $archive_hero_img_url ); ?>');">
  <div class="page-hero__overlay"></div>
  <div class="page-hero__content">
    <div class="container">
      <h1 class="page-hero__title">キャンペーン</h1>
      <p class="page-hero__subtitle">最新のキャンペーン情報をチェック</p>
    </div>
  </div>
</section>
<?php else : ?>
<section class="page-hero page-hero--no-image">
  <div class="container">
    <h1 class="page-hero__title">キャンペーン</h1>
    <p class="page-hero__subtitle">最新のキャンペーン情報をチェック</p>
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
      <span class="breadcrumb-current">キャンペーン</span>
    </nav>
  </div>
</div>

<!-- ======================================================
     CAMPAIGN LIST WITH SIDEBAR
     ====================================================== -->
<main id="main-content" class="page-main">
  <div class="container">
    <div class="campaign-content-wrapper">

      <!-- Campaign Grid -->
      <div class="campaign-list-main">
        <div class="campaign-grid">
          <?php
          if ( have_posts() ) {
            while ( have_posts() ) {
              the_post();
              $featured_image = get_the_post_thumbnail_url( get_the_ID(), 'medium' );
              ?>
              <article class="campaign-card">
                <a href="<?php the_permalink(); ?>" class="campaign-card__link">
                  <?php if ( $featured_image ) : ?>
                    <div class="campaign-card__image" style="background-image:url('<?php echo esc_url( $featured_image ); ?>');">
                    </div>
                  <?php else : ?>
                    <div class="campaign-card__image campaign-card__image--placeholder"></div>
                  <?php endif; ?>

                  <div class="campaign-card__content">
                    <div class="campaign-card__meta">
                      <time class="campaign-card__date"><?php echo esc_html( get_the_date( 'Y.m.d' ) ); ?></time>
                      <?php
                      $categories = get_the_category();
                      if ( $categories ) {
                        echo '<span class="campaign-card__category">' . esc_html( $categories[0]->name ) . '</span>';
                      }
                      ?>
                    </div>
                    <h3 class="campaign-card__title"><?php the_title(); ?></h3>
                    <?php if ( get_the_excerpt() ) : ?>
                      <p class="campaign-card__excerpt"><?php echo wp_kses_post( wp_trim_words( get_the_excerpt(), 20 ) ); ?></p>
                    <?php endif; ?>
                  </div>
                </a>
              </article>
              <?php
            }
          } else {
            echo '<p>キャンペーンはまだありません。</p>';
          }
          wp_reset_postdata();
          ?>
        </div>

        <!-- Pagination -->
        <?php
        $pagination = paginate_links( [
          'type' => 'array',
          'prev_text' => '← 前へ',
          'next_text' => '次へ →',
          'mid_size' => 1,
          'end_size' => 1,
        ] );
        if ( $pagination ) :
          ?>
          <nav class="campaign-pagination">
            <?php echo implode( ' ', $pagination ); ?>
          </nav>
          <?php
        endif;
        ?>
      </div>

      <!-- Sidebar -->
      <aside class="campaign-sidebar">
        <!-- Categories -->
        <div class="sidebar-widget">
          <h3 class="sidebar-widget__title">カテゴリー</h3>
          <ul class="sidebar-widget__list">
            <?php
            $categories = get_categories( [ 'number' => 10 ] );
            foreach ( $categories as $cat ) {
              $active = ( is_category( $cat->term_id ) ) ? ' active' : '';
              echo '<li><a href="' . esc_url( get_category_link( $cat->term_id ) ) . '" class="' . esc_attr( $active ) . '">' . esc_html( $cat->name ) . '</a></li>';
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
