<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Core Web Vitals - DNS Prefetch & Preconnect -->
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="dns-prefetch" href="//cdnjs.cloudflare.com">
    <link rel="dns-prefetch" href="//cdn.jsdelivr.net">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <?php
    // Open Graph / Twitter Card (complement SEO plugins)
    $og_title = is_front_page() ? get_bloginfo( 'name' ) : ( get_the_title() . ' – ' . get_bloginfo( 'name' ) );
    $og_desc  = is_front_page() ? get_bloginfo( 'description' ) : wp_strip_all_tags( get_the_excerpt() );
    $og_url   = is_singular() ? get_permalink() : home_url( '/' );
    ?>
    <meta property="og:type"        content="<?php echo is_singular() ? 'article' : 'website'; ?>">
    <meta property="og:title"       content="<?php echo esc_attr( $og_title ); ?>">
    <meta property="og:description" content="<?php echo esc_attr( $og_desc ); ?>">
    <meta property="og:url"         content="<?php echo esc_url( $og_url ); ?>">
    <meta property="og:site_name"   content="<?php bloginfo( 'name' ); ?>">
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="<?php echo esc_attr( $og_title ); ?>">
    <meta name="twitter:description" content="<?php echo esc_attr( $og_desc ); ?>">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php
$opts          = lumira_get_options();
$logo_text     = $opts['header_logo_text'] ?? 'LUMIRA';
$logo_type     = $opts['header_logo_type'] ?? 'text';
$logo_image_id = $opts['header_logo_image_id'] ?? 0;
$header_style  = $opts['header_style'] ?? 'transparent';
$nav_bg_id   = $opts['nav_bg_image_id'] ?? 0;
$nav_bg_url  = $nav_bg_id ? wp_get_attachment_image_url( $nav_bg_id, 'full' ) : '';

$sns_icons = lumira_get_sns_icons();
$sns_data = [
    'line'      => [ 'url' => $opts['sns_line'] ?? '', 'show' => $opts['sns_line_show'] ?? 0 ],
    'x'         => [ 'url' => $opts['sns_twitter'] ?? '', 'show' => $opts['sns_twitter_show'] ?? 0 ],
    'instagram' => [ 'url' => $opts['sns_instagram'] ?? '', 'show' => $opts['sns_instagram_show'] ?? 0 ],
    'facebook'  => [ 'url' => $opts['sns_facebook'] ?? '', 'show' => $opts['sns_facebook_show'] ?? 0 ],
    'threads'   => [ 'url' => $opts['sns_threads'] ?? '', 'show' => $opts['sns_threads_show'] ?? 0 ],
    'youtube'   => [ 'url' => $opts['sns_youtube'] ?? '', 'show' => $opts['sns_youtube_show'] ?? 0 ],
];
?>

<!-- ======================================================
     LOADING SCREEN
     ====================================================== -->
<div class="loading-screen" id="loading-screen" aria-hidden="true">
    <div class="loading-screen__inner">
        <div class="loading-screen__logo">
            <?php
            if ( 'image' === $logo_type && $logo_image_id ) {
                $logo_url = wp_get_attachment_image_url( $logo_image_id, 'full' );
                if ( $logo_url ) {
                    echo '<img src="' . esc_url( $logo_url ) . '" alt="' . esc_attr( $logo_text ) . '" style="height:auto;max-width:200px;">';
                } else {
                    echo '<span class="loading-screen__logo-text">' . esc_html( $logo_text ) . '</span>';
                }
            } else {
                echo '<span class="loading-screen__logo-text">' . esc_html( $logo_text ) . '</span>';
            }
            ?>
        </div>
        <div class="loading-screen__bar-wrap">
            <div class="loading-screen__bar" id="loading-bar"></div>
        </div>
        <span class="loading-screen__counter" id="loading-counter">0</span>
    </div>
</div>

<!-- ======================================================
     CURSOR (カスタムカーソル)
     ====================================================== -->
<div class="custom-cursor" id="custom-cursor" aria-hidden="true">
    <div class="custom-cursor__outer"></div>
    <div class="custom-cursor__inner"></div>
</div>

<!-- ======================================================
     SITE HEADER
     ====================================================== -->
<header class="site-header site-header--<?php echo esc_attr( $header_style ); ?>"
        id="site-header"
        role="banner">
    <div class="site-header__inner">

        <!-- Logo -->
        <div class="site-header__logo">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>"
               class="site-header__logo-link"
               aria-label="<?php echo esc_attr( $logo_text ); ?> - ホームへ">
                <?php
                if ( 'image' === $logo_type && $logo_image_id ) {
                    $logo_url = wp_get_attachment_image_url( $logo_image_id, 'full' );
                    if ( $logo_url ) {
                        echo '<img src="' . esc_url( $logo_url ) . '" alt="' . esc_attr( $logo_text ) . '" style="height:auto;max-width:200px;">';
                    } else {
                        echo '<span class="site-header__logo-text">' . esc_html( $logo_text ) . '</span><span class="site-header__logo-sub">Interior Design</span>';
                    }
                } else {
                    echo '<span class="site-header__logo-text">' . esc_html( $logo_text ) . '</span><span class="site-header__logo-sub">Interior Design</span>';
                }
                ?>
            </a>
        </div>

        <!-- Desktop Navigation -->
        <nav class="site-header__nav" aria-label="プライマリーナビゲーション">
            <?php
            if ( has_nav_menu( 'primary' ) ) :
                wp_nav_menu( [
                    'theme_location' => 'primary',
                    'menu_class'     => 'site-header__nav-list',
                    'container'      => false,
                    'walker'         => new Lumira_Nav_Walker(),
                    'depth'          => 1,
                ] );
            else :
            ?>
            <ul class="site-header__nav-list">
                <li class="nav-item"><a href="#about"   class="nav-link"><span class="nav-link__en">About</span></a></li>
                <li class="nav-item"><a href="#works"   class="nav-link"><span class="nav-link__en">Works</span></a></li>
                <li class="nav-item"><a href="#service" class="nav-link"><span class="nav-link__en">Service</span></a></li>
                <li class="nav-item"><a href="#recruit" class="nav-link"><span class="nav-link__en">Recruit</span></a></li>
            </ul>
            <?php endif; ?>
        </nav>

        <!-- Header Actions -->
        <div class="site-header__actions">
            <!-- 4-dot nav trigger -->
            <button class="nav-trigger"
                    id="hamburger"
                    type="button"
                    aria-label="ナビゲーションを開く"
                    aria-expanded="false"
                    aria-controls="mobile-menu">
                <span class="nav-trigger__dots" aria-hidden="true">
                    <span class="nav-trigger__dot"></span>
                    <span class="nav-trigger__dot"></span>
                    <span class="nav-trigger__dot"></span>
                    <span class="nav-trigger__dot"></span>
                </span>
            </button>
        </div>

    </div><!-- .site-header__inner -->
</header><!-- .site-header -->

<!-- ======================================================
     FULLSCREEN NAV
     ====================================================== -->
<nav id="mobile-menu" class="nav" aria-hidden="true" role="dialog" aria-modal="true" aria-label="ナビゲーション">

    <!-- Clip-path layer -->
    <div class="nav__layer" data-nav="content">
        <div class="nav__bg" data-nav="bg">
            <!-- WebGL canvas for displacement ripple -->
            <canvas id="nav-webgl-canvas" style="position: absolute; inset: 0; z-index: 0;" aria-hidden="true"></canvas>
            <div class="nav__body">

                <!-- Logo -->
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="nav__logo-link">
                    <?php if ( has_custom_logo() ) : the_custom_logo();
                    else : ?>
                    <span class="nav__logo-text"><?php echo esc_html( $logo_text ); ?></span>
                    <?php endif; ?>
                </a>

                <!-- Primary nav list -->
                <nav class="nav__primary" aria-label="メインナビゲーション">
                    <?php if ( has_nav_menu( 'mobile' ) || has_nav_menu( 'primary' ) ) :
                        wp_nav_menu( [
                            'theme_location' => has_nav_menu( 'mobile' ) ? 'mobile' : 'primary',
                            'container'      => false,
                            'menu_class'     => 'nav__list',
                            'walker'         => new Lumira_FullNav_Walker(),
                            'depth'          => 1,
                        ] );
                    else : ?>
                    <ul class="nav__list">
                        <?php
                        $default_links = [
                            ['href' => '#about',   'en' => 'About',   'ja' => 'について'],
                            ['href' => '#works',   'en' => 'Works',   'ja' => '実績'],
                            ['href' => '#service', 'en' => 'Service', 'ja' => 'サービス'],
                            ['href' => esc_url( home_url( '/contact' ) ), 'en' => 'Contact', 'ja' => 'お問い合わせ'],
                        ];
                        foreach ( $default_links as $lnk ) : ?>
                        <li class="nav__item">
                            <a href="<?php echo esc_attr( $lnk['href'] ); ?>" class="nav__link" data-nav="link">
                                <div class="nav__link-en">
                                    <span class="nav__link-en-wrap"><span class="nav__link-en-text" data-nav="link-en-text"><?php echo esc_html( $lnk['en'] ); ?></span></span>
                                </div>
                                <div class="nav__link-ja">
                                    <span class="nav__link-ja-wrap"><span class="nav__link-ja-text" data-nav="link-ja-text"><?php echo esc_html( $lnk['ja'] ); ?></span></span>
                                </div>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </nav>

                <!-- Divider -->
                <div class="nav__divider"></div>

                <!-- Footer: SNS + copy -->
                <div class="nav__footer-area">
                    <ul class="nav__sns-list">
                        <?php foreach ( [ 'line', 'x', 'instagram', 'facebook', 'threads', 'youtube' ] as $platform ) : ?>
                            <?php if ( $sns_data[ $platform ]['show'] && isset( $sns_icons[ $platform ] ) ) : ?>
                            <li><a href="<?php echo esc_url( $sns_data[ $platform ]['url'] ); ?>" class="nav__sns-link nav__sns-link--<?php echo esc_attr( $platform ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( ucfirst( $platform ) ); ?>" data-nav="sns-icon"><?php echo $sns_icons[ $platform ]; ?></a></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                    <p class="nav__copy">&copy; <?php echo date('Y'); ?> <?php echo esc_html( $logo_text ); ?></p>
                </div>

            </div><!-- .nav__body -->
        </div><!-- .nav__bg -->
    </div><!-- .nav__layer -->

    <!-- Overlay -->
    <div class="nav__overlay" data-nav="overlay"></div>

</nav><!-- .nav -->

<!-- ======================================================
     STICKY CTA BUTTONS
     ====================================================== -->
<?php if ( $opts['sticky_btn_show'] ) : ?>
<div class="sticky-cta-wrapper" id="sticky-cta-wrapper"
     data-button-count="<?php echo esc_attr( $opts['sticky_btn_count'] ?? '1' ); ?>">

  <!-- Button 1 -->
  <a href="<?php echo esc_url( $opts['sticky_btn_1_url'] ); ?>"
     class="sticky-cta sticky-cta--1"
     aria-label="<?php echo esc_attr( str_replace( "\n", "", $opts['sticky_btn_1_line1'] ) ); ?>">
    <span class="sticky-cta__text"><?php echo esc_html( $opts['sticky_btn_1_line1'] ); ?></span>
  </a>

  <!-- Button 2 (optional) -->
  <?php if ( intval( $opts['sticky_btn_count'] ) === 2 ) : ?>
  <a href="<?php echo esc_url( $opts['sticky_btn_2_url'] ); ?>"
     class="sticky-cta sticky-cta--2"
     aria-label="<?php echo esc_attr( str_replace( "\n", "", $opts['sticky_btn_2_line1'] ) ); ?>">
    <span class="sticky-cta__text"><?php echo esc_html( $opts['sticky_btn_2_line1'] ); ?></span>
  </a>
  <?php endif; ?>

</div>
<?php endif; ?>

<!-- ======================================================
     BARBA.JS WRAPPER
     ====================================================== -->
<div data-barba="wrapper">
<div data-barba="container" data-barba-namespace="<?php echo is_front_page() ? 'home' : 'page'; ?>">

<?php
/* ==========================================================================
   Mobile Nav Walker
   ========================================================================== */
/* ==========================================================================
   Fullscreen Nav Walker
   ========================================================================== */
class Lumira_FullNav_Walker extends Walker_Nav_Menu {
    private static $ja_map = [
        'Home'     => 'ホーム',
        'About'    => 'について',
        'About Us' => 'について',
        'Works'    => '実績',
        'Service'  => 'サービス',
        'Recruit'  => '採用情報',
        'Contact'  => 'お問い合わせ',
        'News'     => 'お知らせ',
    ];
    public function start_lvl( &$output, $depth = 0, $args = null ) {}
    public function end_lvl( &$output, $depth = 0, $args = null ) {}
    public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
        $is_current = in_array( 'current-menu-item', $item->classes );
        $title      = esc_html( $item->title );
        $ja         = $item->description ? esc_html( $item->description ) : ( self::$ja_map[ $title ] ?? '' );
        $output .= '<li class="nav__item' . ( $is_current ? ' is-current' : '' ) . '">';
        $output .= '<a href="' . esc_url( $item->url ) . '" class="nav__link' . ( $is_current ? ' is-current' : '' ) . '" data-nav="link">';
        $output .= '<div class="nav__link-en"><span class="nav__link-en-wrap"><span class="nav__link-en-text" data-nav="link-en-text">' . $title . '</span></span>';
        if ( $is_current ) $output .= '<div class="nav__link-line" data-nav="link-line"></div>';
        $output .= '</div>';
        if ( $ja ) $output .= '<div class="nav__link-ja"><span class="nav__link-ja-wrap"><span class="nav__link-ja-text" data-nav="link-ja-text">' . $ja . '</span></span></div>';
        $output .= '</a>';
    }
    public function end_el( &$output, $item, $depth = 0, $args = null ) { $output .= '</li>'; }
}

/* ==========================================================================
   Mobile Nav Walker (legacy – kept for reference)
   ========================================================================== */
class Lumira_Mobile_Nav_Walker extends Walker_Nav_Menu {
    private $count = 0;
    public function start_lvl( &$output, $depth = 0, $args = null ) {}
    public function end_lvl( &$output, $depth = 0, $args = null ) {}
    public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
        $this->count++;
        $num = str_pad( $this->count, 2, '0', STR_PAD_LEFT );
        $is_active = in_array( 'current-menu-item', $item->classes );
        $output .= '<li class="mobile-menu__item' . ( $is_active ? ' is-active' : '' ) . '">';
        $output .= '<a href="' . esc_url( $item->url ) . '" class="mobile-menu__link">';
        $output .= '<span class="mobile-menu__num">' . $num . '</span>';
        $output .= '<span class="mobile-menu__name">' . esc_html( $item->title ) . '</span>';
        $output .= '</a>';
    }
    public function end_el( &$output, $item, $depth = 0, $args = null ) {
        $output .= '</li>';
    }
}
?>
