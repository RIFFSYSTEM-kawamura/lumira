<?php
/**
 * Template Name: Front Page
 * Homepage template with Hero section
 */
get_header();

$opts     = lumira_get_options();
$title_jp = $opts['hero_title_jp']    ?? '空間を、光で彩る。';
$subtitle = $opts['hero_subtitle']    ?? '一人ひとりのライフスタイルに寄り添い、理想の暮らしを形にする。';

// Normalize literal \n sequences saved before the defaults fix
$subtitle  = str_replace( '\\n', "\n", $subtitle );
$bg_type        = $opts['hero_bg_type']        ?? 'image';
$slide_interval = intval( $opts['hero_slide_interval'] ?? 5 );
$video_interval = intval( $opts['hero_video_interval'] ?? 7 );

// Animation classes per slot (validate against allowlist to preserve camelCase)
$allowed_anims = [ 'none', 'moveRight', 'moveLeft', 'moveUp', 'zoomIn', 'zoomOut' ];
$img_anims = [
    $opts['hero_image_anim_1'] ?? 'none',
    $opts['hero_image_anim_2'] ?? 'none',
    $opts['hero_image_anim_3'] ?? 'none',
];
$vid_anims = [
    $opts['hero_video_anim_1'] ?? 'none',
    $opts['hero_video_anim_2'] ?? 'none',
    $opts['hero_video_anim_3'] ?? 'none',
];
$get_anim_class = function( $anim ) use ( $allowed_anims ) {
    return ( is_string( $anim ) && $anim !== 'none' && in_array( $anim, $allowed_anims, true ) )
        ? ' hero-anim-' . $anim : '';
};
$overlay_opacity = $opts['hero_overlay_opacity'] ?? '0.55';
$overlay_color   = $opts['hero_overlay_color']   ?? '#080810';

// Collect active video URLs (up to 3)
$video_urls = [];
foreach ( [
    $opts['hero_video_url']   ?? '',
    $opts['hero_video_url_2'] ?? '',
    $opts['hero_video_url_3'] ?? '',
] as $vraw ) {
    if ( $vraw ) $video_urls[] = $vraw;
}

// Collect up to 3 slide images
$slide_urls = [];
foreach ( [
    intval( $opts['hero_image_id']   ?? 0 ),
    intval( $opts['hero_image_id_2'] ?? 0 ),
    intval( $opts['hero_image_id_3'] ?? 0 ),
] as $img_id ) {
    if ( ! $img_id ) continue;
    $url = wp_get_attachment_image_url( $img_id, 'lumira-hero' )
        ?: wp_get_attachment_image_url( $img_id, 'full' )
        ?: wp_get_attachment_image_url( $img_id, 'large' )
        ?: '';
    if ( $url ) $slide_urls[] = $url;
}

// Convert hex+opacity to rgba for CSS
$hex = ltrim( $overlay_color, '#' );
$r   = hexdec( substr( $hex, 0, 2 ) );
$g   = hexdec( substr( $hex, 2, 2 ) );
$b   = hexdec( substr( $hex, 4, 2 ) );
$overlay_css = "rgba({$r},{$g},{$b},{$overlay_opacity})";
?>

<!-- ======================================================
     HERO SECTION
     ====================================================== -->

<section class="hero" id="hero" aria-label="ヒーローセクション">

    <!-- Background -->
    <div class="hero__bg">
        <?php if ( $bg_type === 'video' && ! empty( $video_urls ) ) :
            $ext_map = [ 'webm' => 'video/webm', 'ogv' => 'video/ogg', 'ogg' => 'video/ogg' ];
            if ( count( $video_urls ) === 1 ) :
                $ext    = strtolower( pathinfo( parse_url( $video_urls[0], PHP_URL_PATH ), PATHINFO_EXTENSION ) );
                $v_mime = $ext_map[ $ext ] ?? 'video/mp4';
        ?>
        <video class="hero__video<?php echo esc_attr( $get_anim_class( $vid_anims[0] ) ); ?>" autoplay muted loop playsinline aria-hidden="true">
            <source src="<?php echo esc_url( $video_urls[0] ); ?>" type="<?php echo esc_attr( $v_mime ); ?>">
        </video>
        <?php else : ?>
        <div class="hero__video-reel"
             data-interval="<?php echo esc_attr( $video_interval * 1000 ); ?>"
             aria-hidden="true">
            <?php foreach ( $video_urls as $vi => $vurl ) :
                $ext    = strtolower( pathinfo( parse_url( $vurl, PHP_URL_PATH ), PATHINFO_EXTENSION ) );
                $v_mime = $ext_map[ $ext ] ?? 'video/mp4';
            ?>
            <video class="hero__video-slide<?php echo $vi === 0 ? ' is-active' : ''; ?><?php echo esc_attr( $get_anim_class( $vid_anims[ $vi ] ?? 'none' ) ); ?>"
                   autoplay muted loop playsinline>
                <source src="<?php echo esc_url( $vurl ); ?>" type="<?php echo esc_attr( $v_mime ); ?>">
            </video>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php elseif ( ! empty( $slide_urls ) ) : ?>
        <div class="hero__slideshow"
             data-interval="<?php echo esc_attr( $slide_interval * 1000 ); ?>"
             aria-hidden="true">
            <?php foreach ( $slide_urls as $i => $url ) : ?>
            <div class="hero__slide<?php echo $i === 0 ? ' is-active' : ''; ?><?php echo esc_attr( $get_anim_class( $img_anims[ $i ] ?? 'none' ) ); ?>"
                 style="background-image:url('<?php echo esc_url( $url ); ?>');">
            </div>
            <?php endforeach; ?>
        </div>
        <?php else : ?>
        <div class="hero__gradient" aria-hidden="true"></div>
        <?php endif; ?>

        <!-- Overlay -->
        <div class="hero__overlay"
             style="background: <?php echo esc_attr( $overlay_css ); ?>;"
             aria-hidden="true">
        </div>

        <!-- Noise texture -->
        <div class="hero__noise" aria-hidden="true"></div>
    </div><!-- .hero__bg -->

    <!-- Content -->
    <div class="hero__content">
        <div class="hero__content-inner">

            <!-- Japanese Title -->
            <h1 class="hero__title-jp" data-hero-animate="title-jp" aria-label="<?php echo esc_attr( $title_jp ); ?>">
                <?php echo esc_html( $title_jp ); ?>
            </h1>

            <!-- Subtitle / Description -->
            <p class="hero__subtitle" data-hero-animate="subtitle">
                <?php echo wp_kses_post( $subtitle ); ?>
            </p>

        </div><!-- .hero__content-inner -->
    </div><!-- .hero__content -->

    <!-- News Ticker (sliding posts) -->
    <?php
    $news_items = new WP_Query( [ 'post_type' => 'post', 'posts_per_page' => 5, 'post_status' => 'publish', 'orderby' => 'date', 'order' => 'DESC' ] );
    if ( $news_items->have_posts() ) :
    ?>
    <div class="hero__news" data-hero-animate="news" data-news-interval="5000">
        <div class="hero__news-container">
            <?php while ( $news_items->have_posts() ) : $news_items->the_post(); ?>
            <a href="<?php the_permalink(); ?>" class="hero__news-link">
                <span class="hero__news-label">News</span>
                <span class="hero__news-date"><?php echo get_the_date( 'Y.m.d' ); ?></span>
                <span class="hero__news-title"><?php the_title(); ?></span>
                <span class="hero__news-arrow" aria-hidden="true">→</span>
            </a>
            <?php endwhile; ?>
        </div>
    </div>
    <?php
    wp_reset_postdata();
    endif;
    ?>

</section><!-- .hero -->

<!-- ======================================================
     CAMPAIGN SECTION
     ====================================================== -->
<?php
$opts_campaign = lumira_get_options();
$campaign_title_ja = $opts_campaign['campaign_title_ja'] ?? '初回体験キャンペーン';
$campaign_subtitle = $opts_campaign['campaign_subtitle'] ?? '～LUMIRAの本格エステをお得にお試し～';
$pickup_items = $opts_campaign['campaign_pickup'] ?? [];

$default_course_names = [
    1 => 'モデル美脚ブーストコース',
    2 => 'オーダーセレクト美痩身コース',
    3 => '汗ばくセルライトproコース',
    4 => '花嫁トータルビューティーコース',
    5 => 'ブライダルコース',
    6 => '睡眠スイッチコース',
];

$campaign_items = [];
for ( $i = 1; $i <= 6; $i++ ) {
    $img_id = intval( $opts_campaign[ "campaign_image_$i" ] ?? 0 );
    $label = $opts_campaign[ "campaign_label_$i" ] ?? '';
    $title = $opts_campaign[ "campaign_title_$i" ] ?? '';
    $description = $opts_campaign[ "campaign_description_$i" ] ?? '';
    $original_price = $opts_campaign[ "campaign_original_price_$i" ] ?? '';
    $price = $opts_campaign[ "campaign_price_$i" ] ?? '';
    $url = $opts_campaign[ "campaign_url_$i" ] ?? '';
    $course_name = $opts_campaign[ "campaign_course_name_$i" ] ?? $default_course_names[ $i ];

    if ( $label || $title ) {
        $img_url = $img_id ? wp_get_attachment_image_url( $img_id, 'lumira-card' ) : '';
        $campaign_items[] = [
            'image' => $img_url,
            'label' => $label,
            'title' => $title,
            'description' => $description,
            'original_price' => $original_price,
            'price' => $price,
            'url' => $url,
            'index' => $i,
            'is_pickup' => in_array( $i, $pickup_items ),
            'course_name' => $course_name,
        ];
    }
}
?>
<section class="home-campaign" id="campaign" aria-label="Campaign">
  <div class="container">
    <div class="home-campaign__head" data-reveal>
      <h2 class="home-campaign__title"><?php echo esc_html( $campaign_title_ja ); ?></h2>
      <p class="home-campaign__subtitle"><?php echo esc_html( $campaign_subtitle ); ?></p>
    </div>

    <div class="home-campaign__grid" data-reveal>
      <?php foreach ( $campaign_items as $item ) : ?>
      <div class="home-campaign__item<?php echo $item['is_pickup'] ? ' is-pickup' : ''; ?>">
        <?php if ( $item['url'] ) : ?>
        <a href="<?php echo esc_url( $item['url'] ); ?>" class="home-campaign__link" aria-label="<?php echo esc_attr( $item['title'] ); ?>">
        <?php endif; ?>
        <?php if ( $item['image'] ) : ?>
        <div class="home-campaign__image" style="background-image:url('<?php echo esc_url( $item['image'] ); ?>');" aria-hidden="true"></div>
        <?php endif; ?>
        <?php if ( $item['label'] ) : ?>
        <span class="home-campaign__badge"><?php echo esc_html( $item['label'] ); ?></span>
        <?php endif; ?>
        <div class="home-campaign__content">
          <h3 class="home-campaign__item-title"><?php echo wp_kses_post( $item['title'] ); ?></h3>
          <?php if ( $item['description'] ) : ?>
          <p class="home-campaign__item-description"><?php echo wp_kses_post( $item['description'] ); ?></p>
          <?php endif; ?>
          <?php if ( $item['original_price'] || $item['price'] ) : ?>
          <div class="home-campaign__price">
            <?php if ( $item['original_price'] ) : ?>
            <span class="home-campaign__original-price"><?php echo esc_html( $item['original_price'] ); ?></span>
            <span class="home-campaign__price-separator">→</span>
            <?php endif; ?>
            <span class="home-campaign__campaign-price"><?php echo esc_html( $item['price'] ); ?></span>
            <?php if ( $item['original_price'] ) : ?>
            <span class="home-campaign__tax-note">(税込)</span>
            <?php endif; ?>
          </div>
          <?php endif; ?>
        </div>
        <?php if ( $item['is_pickup'] ) : ?>
        <span class="home-campaign__pickup-badge">PICK UP</span>
        <?php endif; ?>
        <?php if ( $item['url'] ) : ?>
        </a>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Campaign CTA Button -->
    <div class="home-campaign__cta" data-reveal>
      <a href="<?php echo esc_url( $opts_campaign['campaign_link'] ?? '/campaign' ); ?>" class="home-campaign__btn">
        <?php echo esc_html( $opts_campaign['campaign_btn_text'] ?? 'キャンペーン一覧を見る' ); ?>
      </a>
    </div>
  </div>
</section><!-- .home-campaign -->

<!-- ======================================================
     ABOUT US SECTION
     ====================================================== -->
<?php
$opts_about = lumira_get_options();
$about_title_ja    = $opts_about['about_title_ja'] ?? 'LUMIRAについて';
$about_lead        = $opts_about['about_lead'] ?? '';
$about_description = $opts_about['about_description'] ?? '';
$about_link        = $opts_about['about_link'] ?? '/about';
$about_btn_text    = $opts_about['about_btn_text'] ?? 'VIEW MORE';

// リンク処理：相対パスまたは絶対URLをサポート
if ( $about_link && strpos( $about_link, 'http' ) === false ) {
    // 相対パスの場合：home_url() でプリペンド
    $about_link = home_url( $about_link );
}
$about_bg_type     = $opts_about['about_bg_type'] ?? 'color';
$about_bg_color    = $opts_about['about_bg_color'] ?? '#f2ede6';
$about_bg_img_id   = intval( $opts_about['about_bg_image_id'] ?? 0 );
$about_bg_img_url  = $about_bg_img_id
    ? ( wp_get_attachment_image_url( $about_bg_img_id, 'full' ) ?: '' )
    : '';
$orb_color_main    = $opts_about['orb_color_main'] ?? '#d4a373';
$orb_color_mid     = $opts_about['orb_color_mid'] ?? '#a07050';
$orb_color_dark    = $opts_about['orb_color_dark'] ?? '#6b4226';
$about_title_en    = $opts_about['about_title_en'] ?? 'About Us';
?>
<section class="home-about" id="about" aria-label="About Us"
  style="--orb-color-main: <?php echo esc_attr( $orb_color_main ); ?>; --orb-color-mid: <?php echo esc_attr( $orb_color_mid ); ?>; --orb-color-dark: <?php echo esc_attr( $orb_color_dark ); ?>;"
>

  <!-- Background Layer -->
  <div class="home-about__bg" aria-hidden="true"
    <?php if ( $about_bg_type === 'image' && $about_bg_img_url ) : ?>
      style="background-image:url('<?php echo esc_url( $about_bg_img_url ); ?>');background-size:cover;background-position:center"
    <?php elseif ( $about_bg_type === 'color' && $about_bg_color ) : ?>
      style="background-color:<?php echo esc_attr( $about_bg_color ); ?>"
    <?php else : ?>
      style="background-color:var(--color-bg-2)"
    <?php endif; ?>
  ></div>

  <div class="container">
    <div class="home-about__inner">

      <!-- Left: Decorative orbs -->
      <div class="home-about__head" data-reveal>
        <div class="home-about__label-row">
          <span class="home-about__orb-sm" aria-hidden="true"></span>
          <div class="home-about__title-group">
            <h2 style="display:flex;flex-direction:column;gap:4px;margin:0;">
              <span class="home-about__title-en"><?php echo esc_html( $about_title_en ); ?></span>
              <span class="home-about__title-ja-sub"><?php echo esc_html( $about_title_ja ); ?></span>
            </h2>
          </div>
        </div>
        <div class="home-about__orb-lg" aria-hidden="true"></div>
      </div>

      <!-- Right: Content -->
      <div class="home-about__body" data-reveal>
        <p class="home-about__lead"><?php echo wp_kses_post( $about_lead ); ?></p>
        <div class="home-about__description">
          <?php echo wp_kses_post( $about_description ); ?>
        </div>
        <hr class="home-about__sep">
        <a href="<?php echo esc_url( $about_link ); ?>" class="home-about__more">
          <?php echo esc_html( $about_btn_text ); ?>
        </a>
      </div>

    </div>
  </div>

</section><!-- .home-about -->

<!-- ======================================================
     MARQUEE SECTION
     ====================================================== -->
<?php
$opts_marquee   = lumira_get_options();
$marquee_text   = $opts_marquee['marquee_text']       ?? 'LUMIRA ◆ 光と空間のデザイン ◆ LUMIRA ◆ 光と空間のデザイン ◆';
$marquee_speed  = intval( $opts_marquee['marquee_speed'] ?? 40 );
$marquee_color  = $opts_marquee['marquee_text_color'] ?? '#2c1a10';
$marquee_size   = intval( $opts_marquee['marquee_font_size'] ?? 80 );
$marquee_bg     = $opts_marquee['marquee_bg_color']   ?? '#f2ede6';
?>
<section class="home-marquee" aria-hidden="true"
  style="--marquee-duration:<?php echo esc_attr( $marquee_speed ); ?>s;
         --marquee-color:<?php echo esc_attr( $marquee_color ); ?>;
         --marquee-size:<?php echo esc_attr( $marquee_size ); ?>px;
         --marquee-bg:<?php echo esc_attr( $marquee_bg ); ?>;">
  <div class="home-marquee__track">
    <div class="home-marquee__content">
      <span class="home-marquee__item"><?php echo esc_html( $marquee_text ); ?></span>
      <span class="home-marquee__item"><?php echo esc_html( $marquee_text ); ?></span>
    </div>
  </div>
</section><!-- .home-marquee -->

<!-- ======================================================
     MENU SECTION
     ====================================================== -->
<?php
$opts_menu = lumira_get_options();
$menu_title_en    = $opts_menu['menu_title_en'] ?? 'MENU';
$menu_title_ja    = $opts_menu['menu_title_ja'] ?? 'メニュー';
$menu_lead        = $opts_menu['menu_lead'] ?? '体の中から本当の美しさを。';
$menu_description = $opts_menu['menu_description'] ?? '当店ならではの充実したエステメニュー';
$menu_items       = [
    [ 'label' => 'BODY CARE', 'image_id' => intval( $opts_menu['menu_image_1'] ?? 0 ) ],
    [ 'label' => 'LEG CARE', 'image_id' => intval( $opts_menu['menu_image_2'] ?? 0 ) ],
    [ 'label' => 'DETOX CARE', 'image_id' => intval( $opts_menu['menu_image_3'] ?? 0 ) ],
    [ 'label' => 'HAND CARE', 'image_id' => intval( $opts_menu['menu_image_4'] ?? 0 ) ],
    [ 'label' => 'HIP CARE', 'image_id' => intval( $opts_menu['menu_image_5'] ?? 0 ) ],
    [ 'label' => 'FACE CARE', 'image_id' => intval( $opts_menu['menu_image_6'] ?? 0 ) ],
];
?>
<section class="home-menu" id="menu" aria-label="Menu">
  <div class="container">
    <div class="home-menu__head" data-reveal>
      <div class="home-menu__label-row">
        <h2 style="display:flex;flex-direction:column;gap:4px;margin:0;" class="home-menu__title-group">
          <span class="home-menu__title-en"><?php echo esc_html( $menu_title_en ); ?></span>
          <span class="home-menu__title-ja-sub"><?php echo esc_html( $menu_title_ja ); ?></span>
        </h2>
      </div>
      <p class="home-menu__lead"><?php echo esc_html( $menu_lead ); ?></p>
      <p class="home-menu__description"><?php echo esc_html( $menu_description ); ?></p>
    </div>

    <div class="home-menu__grid" data-reveal>
      <?php foreach ( $menu_items as $index => $item ) :
        $img_url = $item['image_id']
          ? ( wp_get_attachment_image_url( $item['image_id'], 'lumira-menu' )
              ?: wp_get_attachment_image_url( $item['image_id'], 'medium' )
              ?: '' )
          : '';
        $menu_link = home_url( '/menu/#menu-' . sanitize_title( $item['label'] ) );
      ?>
      <a href="<?php echo esc_url( $menu_link ); ?>" class="home-menu__item" aria-label="<?php echo esc_attr( $item['label'] ); ?>">
        <?php if ( $img_url ) : ?>
        <div class="home-menu__image" style="background-image:url('<?php echo esc_url( $img_url ); ?>');" aria-hidden="true"></div>
        <?php endif; ?>
        <p class="home-menu__label"><?php echo esc_html( $item['label'] ); ?></p>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- Menu CTA Button -->
    <div class="home-menu__cta" data-reveal>
      <a href="<?php echo esc_url( home_url( '/menu/' ) ); ?>" class="btn btn--primary">
        メニュー一覧へ
      </a>
    </div>
  </div>
</section><!-- .home-menu -->

<?php get_footer(); ?>
