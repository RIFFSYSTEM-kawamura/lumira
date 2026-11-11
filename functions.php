<?php
/**
 * LUMIRA Theme - functions.php
 * RIFF SYSTEM developed WordPress Theme
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// バージョン管理 - 開発時は常に新しいバージョンを生成（キャッシュ自動クリア）
if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
    define( 'LUMIRA_VERSION', time() . rand( 1000, 9999 ) );
} else {
    define( 'LUMIRA_VERSION', '1.0.2' );
}
define( 'LUMIRA_DIR',     get_template_directory() );
define( 'LUMIRA_URI',     get_template_directory_uri() );

/* ==========================================================================
   1. Theme Setup
   ========================================================================== */
function lumira_setup() {
    load_theme_textdomain( 'lumira', LUMIRA_DIR . '/languages' );

    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', [ 'search-form','comment-form','comment-list','gallery','caption','style','script' ] );
    add_theme_support( 'custom-logo', [
        'height'      => 80,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ] );
    add_theme_support( 'responsive-embeds' );
    add_theme_support( 'align-wide' );

    register_nav_menus( [
        'primary' => __( 'プライマリーナビゲーション', 'lumira' ),
        'mobile'  => __( 'モバイルナビゲーション', 'lumira' ),
        'footer'  => __( 'フッターナビゲーション', 'lumira' ),
    ] );

    add_image_size( 'lumira-hero',    1920, 1080, true );
    add_image_size( 'lumira-card',    800,  600,  true );
    add_image_size( 'lumira-square',  800,  800,  true );
}
add_action( 'after_setup_theme', 'lumira_setup' );

/* ==========================================================================
   1.5. Disable WordPress Admin Bar
   ========================================================================== */
function lumira_disable_admin_bar() {
    show_admin_bar( false );
}
add_action( 'after_setup_theme', 'lumira_disable_admin_bar' );

/* ==========================================================================
   2. Enqueue Scripts & Styles
   ========================================================================== */
function lumira_enqueue_assets() {
    $v = LUMIRA_VERSION;

    // Google Fonts
    wp_enqueue_style(
        'lumira-fonts',
        'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Noto+Serif+JP:wght@200;300;400;500&family=Jost:wght@200;300;400;500&display=swap',
        [],
        null
    );

    // Main stylesheet
    wp_enqueue_style( 'lumira-main', LUMIRA_URI . '/assets/css/main.css', ['lumira-fonts'], $v );

    // CDN Libraries
    wp_enqueue_script( 'gsap',    'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js',          [], '3.12.5', true );
    wp_enqueue_script( 'gsap-st', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js', ['gsap'], '3.12.5', true );
    wp_enqueue_script( 'lenis',   'https://cdn.jsdelivr.net/npm/@studio-freight/lenis@1/bundled/lenis.min.js', [], '1.0', true );
    wp_enqueue_script( 'barba',   'https://cdn.jsdelivr.net/npm/@barba/core@2.9.7/dist/barba.umd.min.js',    [], '2.9.7',  true );

    // Three.js + Nav WebGL (conditional - only load if navigation needs WebGL)
    wp_enqueue_script( 'threejs', 'https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js', [], 'r128', true );
    wp_enqueue_script( 'nav-webgl', LUMIRA_URI . '/assets/js/nav-webgl.js', [ 'threejs' ], $v, true );

    // Main JS
    wp_enqueue_script(
        'lumira-main',
        LUMIRA_URI . '/assets/js/main.js',
        [ 'gsap', 'gsap-st', 'lenis', 'barba' ],
        $v,
        true
    );

    // Pass PHP options to JS
    $opts = lumira_get_options();
    $hero_img_id  = intval( $opts['hero_image_id'] ?? 0 );
    $hero_img_url = $hero_img_id
        ? ( wp_get_attachment_image_url( $hero_img_id, 'lumira-hero' )
            ?: wp_get_attachment_image_url( $hero_img_id, 'full' )
            ?: '' )
        : '';

    wp_localize_script( 'lumira-main', 'LUMIRA', [
        'homeUrl'       => esc_url( home_url( '/' ) ),
        'themeUri'      => LUMIRA_URI,
        'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
        'isHome'        => is_front_page() ? 'true' : 'false',
        'heroVideoUrl'  => esc_url( $opts['hero_video_url'] ?? '' ),
        'heroImageUrl'  => esc_url( $hero_img_url ),
        'headerStyle'   => esc_attr( $opts['header_style'] ?? 'transparent' ),
        'primaryColor'  => esc_attr( $opts['primary_color'] ?? '#6b4226' ),
    ] );
}
add_action( 'wp_enqueue_scripts', 'lumira_enqueue_assets' );

/* ==========================================================================
   3. Options Helper
   ========================================================================== */
function lumira_get_options() {
    $defaults = [
        // --- Header ---
        'header_style'        => 'transparent',   // transparent | solid
        'header_logo_type'    => 'text',          // text | image
        'header_logo_text'    => 'LUMIRA',
        'header_logo_image_id'=> 0,
        'nav_bg_image_id'     => 0,
        'sns_instagram'       => '',
        'sns_instagram_show'  => 0,
        'sns_twitter'         => '',
        'sns_twitter_show'    => 0,
        'sns_facebook'        => '',
        'sns_facebook_show'   => 0,
        'sns_line'            => '',
        'sns_line_show'       => 0,
        'sns_threads'         => '',
        'sns_threads_show'    => 0,
        'sns_youtube'         => '',
        'sns_youtube_show'    => 0,

        // --- Basic ---
        'primary_color'       => '#6b4226',
        'secondary_color'     => '#ffffff',

        // --- Business Information (for LocalBusiness Schema) ---
        'business_name'       => 'LUMIRA',
        'business_address'    => '',
        'business_phone'      => '',
        'business_email'      => '',
        'business_hours'      => '',

        // --- Hero ---
        'hero_bg_type'        => 'image',     // 'image' | 'video'
        'hero_image_id_2'     => 0,
        'hero_image_id_3'     => 0,
        'hero_slide_interval' => '5',         // seconds
        'hero_video_url_2'    => '',
        'hero_video_url_3'    => '',
        'hero_video_interval' => '7',         // seconds
        'hero_title_jp'       => '空間を、光で彩る。',
        'hero_subtitle'       => "一人ひとりのライフスタイルに寄り添い、理想の暮らしを形にする。\nそれが、私たちLUMIRAの使命です。",
        'hero_video_url'      => '',
        'hero_image_id'       => 0,
        'hero_overlay_opacity'=> '0.55',
        'hero_overlay_color'  => '#080810',

        // --- Hero Animations ---
        'hero_image_anim_1'   => 'none',
        'hero_image_anim_2'   => 'none',
        'hero_image_anim_3'   => 'none',
        'hero_video_anim_1'   => 'none',
        'hero_video_anim_2'   => 'none',
        'hero_video_anim_3'   => 'none',

        // --- About Section ---
        'about_title_en'      => 'About Us',
        'about_title_ja'      => 'LUMIRAについて',
        'about_lead'          => '独自のコンセプトで、暮らしに寄い添う空間をつくります。',
        'about_description'   => '私たちLUMIRAは、お客様のライフスタイルに合わせた最高品質の空間を提供いたします。',
        'about_link'          => '/about',
        'about_btn_text'      => 'VIEW MORE',
        'about_bg_type'       => 'color',
        'about_bg_color'      => '#f2ede6',
        'about_bg_image_id'   => 0,
        'orb_color_main'      => '#d4a373',
        'orb_color_mid'       => '#a07050',
        'orb_color_dark'      => '#6b4226',

        // --- Marquee Section ---
        'marquee_text'        => 'LUMIRA ◆ 光と空間のデザイン ◆ LUMIRA ◆ 光と空間のデザイン ◆',
        'marquee_speed'       => '40',
        'marquee_text_color'  => '#2c1a10',
        'marquee_font_size'   => '80',
        'marquee_bg_color'    => '#f2ede6',

        // --- Campaign Section ---
        'campaign_title_ja'   => '初回体験キャンペーン',
        'campaign_subtitle'   => '～LUMIRAの本格エステをお得にお試し～',
        'campaign_image_1'    => 0,
        'campaign_label_1'    => 'PICK UP',
        'campaign_title_1'    => 'モデル美脚ブーストコース',
        'campaign_description_1' => '(70分)',
        'campaign_original_price_1' => '¥2,300',
        'campaign_price_1'    => '¥1,500',
        'campaign_image_2'    => 0,
        'campaign_label_2'    => 'PICK UP',
        'campaign_title_2'    => 'オーダーセレクト美痩身コース',
        'campaign_description_2' => '(100分/カウンセリング付)',
        'campaign_original_price_2' => '¥27,500',
        'campaign_price_2'    => '¥5,000',
        'campaign_image_3'    => 0,
        'campaign_label_3'    => 'PICK UP',
        'campaign_title_3'    => '汗ばくセルライトproコース',
        'campaign_description_3' => '(70分)',
        'campaign_original_price_3' => '¥29,700',
        'campaign_price_3'    => '¥3,000',
        'campaign_image_4'    => 0,
        'campaign_label_4'    => '',
        'campaign_title_4'    => '花嫁トータルビューティーコース',
        'campaign_description_4' => '(60分/花嫁様限定)',
        'campaign_original_price_4' => '',
        'campaign_price_4'    => '¥7,000',
        'campaign_image_5'    => 0,
        'campaign_label_5'    => '',
        'campaign_title_5'    => 'ブライダルコース',
        'campaign_description_5' => '(40分～/花嫁様限定)',
        'campaign_original_price_5' => '',
        'campaign_price_5'    => '¥5,000',
        'campaign_image_6'    => 0,
        'campaign_label_6'    => '',
        'campaign_title_6'    => '睡眠スイッチコース',
        'campaign_description_6' => '(80分/睡眠ドリンク1本付)',
        'campaign_original_price_6' => '',
        'campaign_price_6'    => '¥5,980',
        'campaign_link'       => '/campaigns',
        'campaign_btn_text'   => 'キャンペーン一覧を見る',

        // --- Menu Section ---
        'menu_title_en'       => 'MENU',
        'menu_title_ja'       => 'メニュー',
        'menu_lead'           => '体の中から本当の美しさを。',
        'menu_description'    => '当店ならではの充実したエステメニュー',
        'menu_image_1'        => 0,
        'menu_image_2'        => 0,
        'menu_image_3'        => 0,
        'menu_image_4'        => 0,
        'menu_image_5'        => 0,
        'menu_image_6'        => 0,

        // --- Sticky CTA Button ---
        'sticky_btn_show'     => 1,
        'sticky_btn_count'    => '2',        // 1 or 2
        'sticky_btn_1_line1'  => '無料体験レッスン',
        'sticky_btn_1_url'    => '/contact',
        'sticky_btn_1_color'  => '#6b4226',
        'sticky_btn_1_text_color' => '#ffffff',
        'sticky_btn_2_line1'  => 'ご予約相談',
        'sticky_btn_2_url'    => '/booking',
        'sticky_btn_2_color'  => '#a07050',
        'sticky_btn_2_text_color' => '#ffffff',
    ];

    $saved = get_option( 'riff_lumira_options', [] );
    return wp_parse_args( $saved, $defaults );
}

/* ==========================================================================
   3.5. SNS Icons
   ========================================================================== */
function lumira_get_sns_icons() {
    return [
        'x'         => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.742l7.733-8.835L1.254 2.25H8.08l4.253 5.622zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
        'instagram' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>',
        'tiktok'    => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05A6.34 6.34 0 003.15 15.3a6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.34-6.34V8.69a8.27 8.27 0 004.84 1.55V6.79a4.85 4.85 0 01-1.08-.1z"/></svg>',
        'facebook'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>',
        'threads'   => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.186 24h-.007c-3.581-.024-6.334-1.205-8.184-3.509C2.35 18.44 1.5 15.586 1.472 12.01v-.017c.03-3.579.858-6.43 2.523-8.482C5.852 1.206 8.6.024 12.18 0h.014c2.746.02 5.043.725 6.826 2.098 1.677 1.29 2.858 3.13 3.509 5.467l-2.04.569c-1.104-3.96-3.898-5.984-8.304-6.015-2.91.022-5.11.936-6.54 2.717C4.307 6.504 3.616 8.914 3.589 12c.027 3.086.718 5.496 2.057 7.164 1.43 1.783 3.631 2.698 6.54 2.717 2.623-.02 4.358-.631 5.8-2.045 1.647-1.613 1.618-3.593 1.09-4.798-.31-.71-.873-1.3-1.634-1.75-.192 1.352-.622 2.446-1.284 3.272-.886 1.102-2.14 1.704-3.73 1.79-1.202.065-2.361-.218-3.259-.801-1.063-.689-1.685-1.74-1.752-2.964-.065-1.19.408-2.285 1.33-3.082.88-.76 2.119-1.207 3.583-1.291a13.853 13.853 0 013.02.142c-.126-.742-.375-1.332-.75-1.757-.513-.586-1.288-.883-2.299-.89h-.029c-.803 0-1.88.199-2.56.896l-1.699-1.327c.87-1.157 2.251-1.849 3.938-1.859h.033c3.164.023 5.028 1.988 5.188 5.396.41.092.811.214 1.198.368 1.968.771 3.303 2.133 3.949 4.037.987 2.913.217 6.111-2.02 8.282C18.304 23.252 15.775 24 12.186 24zm-.224-8.66c1.307-.065 2.11-.72 2.376-1.919a7.942 7.942 0 00.148-1.481c-.877-.218-1.771-.326-2.654-.319-1.89.11-2.831.941-2.766 2.086.04.72.48 1.576 2.124 1.636.257.009.515.006.772-.003z"/></svg>',
        'line'      => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M19.365 9.863c.349 0 .63.285.63.631 0 .345-.281.63-.63.63H17.61v1.125h1.755c.349 0 .63.283.63.63 0 .344-.281.629-.63.629h-2.386c-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63h2.386c.346 0 .627.285.627.63 0 .349-.281.63-.63.63H17.61v1.125h1.755zm-3.855 3.016c0 .27-.174.51-.432.596-.064.021-.133.031-.199.031-.211 0-.391-.09-.51-.25l-2.443-3.317v2.94c0 .344-.279.629-.631.629-.346 0-.626-.285-.626-.629V8.108c0-.27.173-.51.43-.595.06-.023.136-.033.194-.033.195 0 .375.104.495.254l2.462 3.33V8.108c0-.345.282-.63.63-.63.345 0 .63.285.63.63v4.771zm-5.741 0c0 .344-.282.629-.631.629-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63.346 0 .628.285.628.63v4.771zm-2.466.629H4.917c-.345 0-.63-.285-.63-.629V8.108c0-.345.285-.63.63-.63.348 0 .63.285.63.63v4.141h1.756c.348 0 .629.283.629.63 0 .344-.282.629-.629.629M24 10.314C24 4.943 18.615.572 12 .572S0 4.943 0 10.314c0 4.811 4.27 8.842 10.035 9.608.391.082.923.258 1.058.59.12.301.079.766.038 1.08l-.164 1.02c-.045.301-.24 1.186 1.049.645 1.291-.539 6.916-4.078 9.436-6.975C23.176 14.393 24 12.458 24 10.314"/></svg>',
        'youtube'   => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>',
    ];
}

/* ==========================================================================
   4. Body Classes
   ========================================================================== */
function lumira_body_classes( $classes ) {
    if ( is_front_page() ) $classes[] = 'is-front-page';
    if ( ! is_singular() ) $classes[] = 'is-archive';
    return $classes;
}
add_filter( 'body_class', 'lumira_body_classes' );

/* ==========================================================================
   5. Excerpt
   ========================================================================== */
function lumira_excerpt_length() { return 60; }
add_filter( 'excerpt_length', 'lumira_excerpt_length' );

function lumira_excerpt_more( $more ) { return '…'; }
add_filter( 'excerpt_more', 'lumira_excerpt_more' );

/* ==========================================================================
   6. Nav Walker – clean ul/li output
   ========================================================================== */
class Lumira_Nav_Walker extends Walker_Nav_Menu {
    public function start_lvl( &$output, $depth = 0, $args = null ) {}
    public function end_lvl( &$output, $depth = 0, $args = null ) {}

    public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
        $classes   = implode( ' ', $item->classes );
        $is_active = in_array( 'current-menu-item', $item->classes );
        $output   .= '<li class="nav-item' . ( $is_active ? ' is-active' : '' ) . '">';
        $output   .= '<a href="' . esc_url( $item->url ) . '" class="nav-link">';
        $output   .= '<span class="nav-link__en">'  . esc_html( $item->title ) . '</span>';
        $output   .= '</a>';
    }
    public function end_el( &$output, $item, $depth = 0, $args = null ) {
        $output .= '</li>';
    }
}

/* ==========================================================================
   7. SEO / AEO – Structured Data (JSON-LD) (Theme-managed)
   ========================================================================== */
function lumira_output_structured_data() {
    // Skip if All in One SEO is active (use plugin version instead)
    if ( function_exists( 'aioseo' ) ) {
        return;
    }

    $name = get_bloginfo( 'name' );
    $desc = get_bloginfo( 'description' );
    $url  = home_url( '/' );
    $opts = lumira_get_options();

    // Build sameAs from SNS URLs
    $same_as = array_values( array_filter( [
        $opts['sns_instagram'] ?? '',
        $opts['sns_twitter']   ?? '',
        $opts['sns_facebook']  ?? '',
    ] ) );

    // Custom logo URL
    $logo_id  = get_theme_mod( 'custom_logo' );
    $logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : '';

    $opts = lumira_get_options();
    $business_address = $opts['business_address'] ?? '';
    $business_phone = $opts['business_phone'] ?? '';
    $business_email = $opts['business_email'] ?? '';
    $business_hours = $opts['business_hours'] ?? '';

    $graph = [
        [
            '@type'           => 'WebSite',
            '@id'             => $url . '#website',
            'url'             => $url,
            'name'            => $name,
            'description'     => $desc,
            'inLanguage'      => get_bloginfo( 'language' ),
            'potentialAction' => [
                '@type'       => 'SearchAction',
                'target'      => [ '@type' => 'EntryPoint', 'urlTemplate' => $url . '?s={search_term_string}' ],
                'query-input' => 'required name=search_term_string',
            ],
        ],
        [
            '@type'  => 'Organization',
            '@id'    => $url . '#organization',
            'name'   => $name,
            'url'    => $url,
            'sameAs' => $same_as ?: null,
            'logo'   => $logo_url ? [ '@type' => 'ImageObject', 'url' => $logo_url ] : null,
        ],
    ];

    // Add LocalBusiness schema if business information is available
    if ( is_front_page() && ! empty( $business_address ) ) {
        $local_business = [
            '@type'   => 'LocalBusiness',
            '@id'     => $url . '#localbusiness',
            'name'    => $name,
            'url'     => $url,
            'address' => [ '@type' => 'PostalAddress', 'addressCountry' => 'JP', 'addressRegion' => '', 'streetAddress' => $business_address ],
        ];

        if ( $business_phone ) {
            $local_business['telephone'] = $business_phone;
        }
        if ( $business_email ) {
            $local_business['email'] = $business_email;
        }
        if ( $business_hours ) {
            $local_business['openingHoursSpecification'] = $business_hours;
        }
        if ( $logo_url ) {
            $local_business['image'] = [ '@type' => 'ImageObject', 'url' => $logo_url ];
        }

        $graph[] = array_filter( $local_business );
    }

    // Remove null values
    $graph = array_map( function( $item ) {
        return array_filter( $item, fn( $v ) => ! is_null( $v ) );
    }, $graph );

    $schema = [
        '@context' => 'https://schema.org',
        '@graph'   => $graph,
    ];

    echo '<script type="application/ld+json">'
        . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT )
        . '</script>' . "\n";
}
add_action( 'wp_head', 'lumira_output_structured_data', 1 );

/* ==========================================================================
   7.4. BreadcrumbList Schema
   ========================================================================== */
function lumira_output_breadcrumblist() {
    if ( is_admin() || is_feed() || is_front_page() ) {
        return;
    }

    // Skip if All in One SEO is active (use plugin version instead)
    if ( function_exists( 'aioseo' ) ) {
        return;
    }

    $breadcrumbs = [];
    $url = home_url( '/' );

    // Home
    $breadcrumbs[] = [
        '@type'    => 'ListItem',
        'position' => 1,
        'name'     => get_bloginfo( 'name' ),
        'item'     => $url,
    ];

    // Current page
    if ( is_singular( 'page' ) ) {
        $breadcrumbs[] = [
            '@type'    => 'ListItem',
            'position' => 2,
            'name'     => get_the_title(),
            'item'     => get_permalink(),
        ];
    }

    if ( count( $breadcrumbs ) < 2 ) {
        return;
    }

    $schema = [
        '@context'          => 'https://schema.org',
        '@type'             => 'BreadcrumbList',
        'itemListElement'   => $breadcrumbs,
    ];

    echo '<script type="application/ld+json">'
        . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT )
        . '</script>' . "\n";
}
add_action( 'wp_head', 'lumira_output_breadcrumblist', 2 );

/* ==========================================================================
   7.5. Open Graph & SEO Meta Tags (Theme-managed)
   ========================================================================== */
function lumira_output_og_tags() {
    if ( is_admin() || is_feed() ) return;

    // Skip if All in One SEO is active (use plugin version instead)
    if ( function_exists( 'aioseo' ) ) {
        return;
    }

    $opts = lumira_get_options();
    $title = get_the_title();
    $desc = get_the_excerpt() ?: get_bloginfo( 'description' );
    $url = get_permalink();
    $ogImg = '';

    // Get OG image from featured image or hero image
    if ( has_post_thumbnail() ) {
        $ogImg = wp_get_attachment_image_url( get_post_thumbnail_id(), 'full' );
    } elseif ( is_front_page() && ! empty( $opts['hero_image_id'] ) ) {
        $ogImg = wp_get_attachment_image_url( $opts['hero_image_id'], 'lumira-hero' )
            ?: wp_get_attachment_image_url( $opts['hero_image_id'], 'full' );
    }

    echo '<meta property="og:type" content="' . ( is_front_page() ? 'website' : 'article' ) . '">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr( substr( $desc, 0, 160 ) ) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
    if ( $ogImg ) {
        echo '<meta property="og:image" content="' . esc_url( $ogImg ) . '">' . "\n";
        echo '<meta property="og:image:width" content="1200">' . "\n";
        echo '<meta property="og:image:height" content="630">' . "\n";
    }
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
}
add_action( 'wp_head', 'lumira_output_og_tags', 1 );

/* ==========================================================================
   7.6. Canonical URL (Theme-managed)
   ========================================================================== */
function lumira_output_canonical() {
    if ( is_admin() || is_feed() ) return;

    // Skip if All in One SEO is active (use plugin version instead)
    if ( function_exists( 'aioseo' ) ) {
        return;
    }

    $url = get_permalink();
    if ( get_query_var( 'paged' ) ) {
        $url = get_pagenum_link( get_query_var( 'paged' ) );
    }

    echo '<link rel="canonical" href="' . esc_url( $url ) . '">' . "\n";
}
add_action( 'wp_head', 'lumira_output_canonical', 2 );

/* ==========================================================================
   8. Dynamic CSS Output – colors & custom CSS
   ========================================================================== */
function lumira_output_dynamic_css() {
    $opts = lumira_get_options();

    $primary   = sanitize_hex_color( $opts['primary_color']   ?? '#6b4226' );
    $secondary = sanitize_hex_color( $opts['secondary_color'] ?? '#ffffff' );
    $sticky_1_bg = sanitize_hex_color( $opts['sticky_btn_1_color'] ?? '#6b4226' );
    $sticky_1_text = sanitize_hex_color( $opts['sticky_btn_1_text_color'] ?? '#ffffff' );
    $sticky_2_bg = sanitize_hex_color( $opts['sticky_btn_2_color'] ?? '#a07050' );
    $sticky_2_text = sanitize_hex_color( $opts['sticky_btn_2_text_color'] ?? '#ffffff' );

    echo "<style id=\"riff-dynamic-css\">\n";
    echo ":root {\n";
    echo "  --riff-primary: " . esc_attr( $primary ) . ";\n";
    echo "  --riff-secondary: " . esc_attr( $secondary ) . ";\n";
    echo "  --sticky-btn-1-bg: " . esc_attr( $sticky_1_bg ) . ";\n";
    echo "  --sticky-btn-1-text: " . esc_attr( $sticky_1_text ) . ";\n";
    echo "  --sticky-btn-2-bg: " . esc_attr( $sticky_2_bg ) . ";\n";
    echo "  --sticky-btn-2-text: " . esc_attr( $sticky_2_text ) . ";\n";
    echo "}\n";
    echo "</style>\n";
}
add_action( 'wp_head', 'lumira_output_dynamic_css', 99 );

/* ==========================================================================
   7.5. Campaign Section Styles
   ========================================================================== */
function lumira_output_campaign_styles() {
    echo '<style id="lumira-campaign-styles">';
    echo '.home-campaign__link{display:block;text-decoration:none;color:inherit;height:100%;}';
    echo '.home-campaign__item.is-pickup{box-shadow:inset 0 0 0 2px var(--color-accent);}';
    echo '.home-campaign__item.is-pickup:hover{box-shadow:inset 0 0 0 2px var(--color-accent);}';
    echo '.home-campaign__pickup-badge{position:absolute;top:50%;right:-40px;transform:translateY(-50%) rotate(45deg);background:var(--color-accent);color:#fff;padding:4px 40px;font-size:10px;font-weight:600;letter-spacing:0.1em;text-transform:uppercase;width:120px;text-align:center;z-index:9;}';
    echo '</style>';
}
add_action( 'wp_head', 'lumira_output_campaign_styles', 100 );

/* ==========================================================================
   8. Admin Dashboard – RIFF Theme
   ========================================================================== */
require_once LUMIRA_DIR . '/inc/riff-options.php';

/* ==========================================================================
   9. Remove WordPress version from head
   ========================================================================== */
remove_action( 'wp_head', 'wp_generator' );

/* ==========================================================================
   9.2. Performance Headers - Caching & Security
   ========================================================================== */
function lumira_set_performance_headers() {
    // Browser caching
    header( 'Cache-Control: public, max-age=31536000, immutable', true );

    // Compression (assuming server has gzip enabled)
    if ( ! ini_get( 'zlib.output_compression' ) ) {
        header( 'Vary: Accept-Encoding', true );
    }
}
add_action( 'send_headers', 'lumira_set_performance_headers' );

/* ==========================================================================
   9.3. Remove Unused Emoji Support
   ========================================================================== */
function lumira_disable_emoji() {
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
}
add_action( 'init', 'lumira_disable_emoji' );

/* ==========================================================================
   9.5. Core Web Vitals - Preload Hero Image
   ========================================================================== */
function lumira_preload_hero_image() {
    if ( ! is_front_page() ) {
        return;
    }

    $opts = lumira_get_options();
    $hero_img_id = intval( $opts['hero_image_id'] ?? 0 );

    if ( ! $hero_img_id ) {
        return;
    }

    $hero_img_url = wp_get_attachment_image_url( $hero_img_id, 'lumira-hero' )
        ?: wp_get_attachment_image_url( $hero_img_id, 'full' );

    if ( $hero_img_url ) {
        echo '<link rel="preload" as="image" href="' . esc_url( $hero_img_url ) . '" fetchpriority="high">' . "\n";
    }
}
add_action( 'wp_head', 'lumira_preload_hero_image', 1 );

/* ==========================================================================
   9.8. Custom Post Type – Campaign
   ========================================================================== */
function lumira_register_campaign_post_type() {
    register_post_type( 'campaign', [
        'label'               => 'キャンペーン',
        'public'              => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'query_var'           => true,
        'rewrite'             => [ 'slug' => 'campaign', 'with_front' => false ],
        'capability_type'     => 'post',
        'has_archive'         => 'campaign',
        'hierarchical'        => false,
        'menu_position'       => 5,
        'menu_icon'           => 'dashicons-images-alt2',
        'supports'            => [ 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ],
        'show_in_rest'        => true,
        'rest_base'           => 'campaigns',
    ] );
}
add_action( 'init', 'lumira_register_campaign_post_type' );

/* ==========================================================================
   10. Page Hero Section Meta Box
   ========================================================================== */
function lumira_register_page_hero_meta() {
    add_meta_box(
        'lumira_page_hero',
        'ページヒーロー設定',
        'lumira_page_hero_callback',
        [ 'page' ],
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'lumira_register_page_hero_meta' );

function lumira_enqueue_media_picker() {
    if ( get_current_screen()->id === 'page' ) {
        wp_enqueue_media();
        wp_enqueue_script( 'lumira-media-picker', get_template_directory_uri() . '/assets/js/media-picker.js', [ 'jquery', 'media-upload' ], '1.0', true );
    }
}
add_action( 'admin_enqueue_scripts', 'lumira_enqueue_media_picker' );

function lumira_page_hero_callback( $post ) {
    wp_nonce_field( 'lumira_page_hero_nonce', 'lumira_page_hero_nonce' );

    $hero_image_id = get_post_meta( $post->ID, '_lumira_page_hero_image', true );
    $hero_title = get_post_meta( $post->ID, '_lumira_page_hero_title', true );
    $hero_subtitle = get_post_meta( $post->ID, '_lumira_page_hero_subtitle', true );

    $hero_image_url = $hero_image_id ? wp_get_attachment_image_url( $hero_image_id, 'thumbnail' ) : '';
    ?>
    <div style="margin-bottom: 20px;">
      <label style="display: block; margin-bottom: 8px; font-weight: 600;">
        ヒーロー背景画像
      </label>
      <div id="hero-image-preview" style="margin-bottom: 10px;">
        <?php if ( $hero_image_url ) : ?>
          <img src="<?php echo esc_url( $hero_image_url ); ?>" style="max-width: 300px; height: auto;">
        <?php endif; ?>
      </div>
      <input type="hidden" name="lumira_page_hero_image" id="lumira_page_hero_image" value="<?php echo esc_attr( $hero_image_id ); ?>">
      <button type="button" id="lumira-hero-upload-btn" class="button">画像を選択</button>
      <?php if ( $hero_image_id ) : ?>
        <button type="button" id="lumira-hero-remove-btn" class="button" style="color: #d32f2f;">削除</button>
      <?php endif; ?>
    </div>

    <div style="margin-bottom: 20px;">
      <label for="lumira_page_hero_title" style="display: block; margin-bottom: 8px; font-weight: 600;">
        ヒーロータイトル
      </label>
      <input type="text" name="lumira_page_hero_title" id="lumira_page_hero_title"
             class="large-text" value="<?php echo esc_attr( $hero_title ); ?>"
             placeholder="<?php echo esc_attr( get_the_title() ); ?>">
    </div>

    <div>
      <label for="lumira_page_hero_subtitle" style="display: block; margin-bottom: 8px; font-weight: 600;">
        ヒーロー副タイトル
      </label>
      <textarea name="lumira_page_hero_subtitle" id="lumira_page_hero_subtitle"
                class="large-text" rows="3" style="width: 100%;"><?php echo esc_textarea( $hero_subtitle ); ?></textarea>
      <p style="font-size: 12px; color: #646970; margin-top: 4px;">HTML タグ（&lt;br&gt; など）が使用できます</p>
    </div>
    <?php
}

function lumira_save_page_hero_meta( $post_id ) {
    if ( ! isset( $_POST['lumira_page_hero_nonce'] ) ||
         ! wp_verify_nonce( $_POST['lumira_page_hero_nonce'], 'lumira_page_hero_nonce' ) ||
         ! current_user_can( 'edit_post', $post_id ) ||
         wp_is_post_autosave( $post_id ) ||
         wp_is_post_revision( $post_id ) ) {
        return;
    }

    $hero_image = isset( $_POST['lumira_page_hero_image'] ) ? absint( $_POST['lumira_page_hero_image'] ) : '';
    $hero_title = isset( $_POST['lumira_page_hero_title'] ) ? sanitize_text_field( wp_unslash( $_POST['lumira_page_hero_title'] ) ) : '';
    $hero_subtitle = isset( $_POST['lumira_page_hero_subtitle'] ) ? wp_kses_post( wp_unslash( $_POST['lumira_page_hero_subtitle'] ) ) : '';

    update_post_meta( $post_id, '_lumira_page_hero_image', $hero_image );
    update_post_meta( $post_id, '_lumira_page_hero_title', $hero_title );
    update_post_meta( $post_id, '_lumira_page_hero_subtitle', $hero_subtitle );
}
add_action( 'save_post', 'lumira_save_page_hero_meta' );

/* ==========================================================================
   10.5. Page Custom CSS Meta Box
   ========================================================================== */
function lumira_register_page_custom_css_meta() {
    add_meta_box(
        'lumira_page_custom_css',
        'ページカスタムCSS',
        'lumira_page_custom_css_callback',
        [ 'page' ],
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'lumira_register_page_custom_css_meta' );

function lumira_page_custom_css_callback( $post ) {
    wp_nonce_field( 'lumira_page_custom_css_nonce', 'lumira_page_custom_css_nonce' );
    $custom_css = get_post_meta( $post->ID, '_lumira_page_custom_css', true );
    ?>
    <div style="margin-bottom: 8px;">
        <p style="font-size: 12px; color: #646970; margin: 0;">このページに追加するカスタムCSSを記述してください。</p>
    </div>
    <textarea name="lumira_page_custom_css" id="lumira_page_custom_css"
              class="large-text code" rows="15"
              style="font-family: monospace; font-size: 13px; width: 100%;"><?php echo esc_textarea( $custom_css ); ?></textarea>
    <?php
}

function lumira_save_page_custom_css( $post_id ) {
    if ( ! isset( $_POST['lumira_page_custom_css_nonce'] ) ||
         ! wp_verify_nonce( $_POST['lumira_page_custom_css_nonce'], 'lumira_page_custom_css_nonce' ) ||
         ! current_user_can( 'edit_post', $post_id ) ||
         wp_is_post_autosave( $post_id ) ||
         wp_is_post_revision( $post_id ) ) {
        return;
    }

    $custom_css = isset( $_POST['lumira_page_custom_css'] )
        ? sanitize_textarea_field( wp_unslash( $_POST['lumira_page_custom_css'] ) )
        : '';

    update_post_meta( $post_id, '_lumira_page_custom_css', $custom_css );
}
add_action( 'save_post', 'lumira_save_page_custom_css' );

function lumira_output_page_custom_css() {
    if ( ! is_page() || is_front_page() ) {
        return;
    }

    $custom_css = get_post_meta( get_the_ID(), '_lumira_page_custom_css', true );

    if ( ! empty( $custom_css ) ) {
        echo "<style id=\"lumira-page-custom-css\">\n";
        echo wp_strip_all_tags( $custom_css );
        echo "\n</style>\n";
    }
}
add_action( 'wp_head', 'lumira_output_page_custom_css', 99 );

/* ==========================================================================
   Restore Demo Campaign Posts (11件 - ページネーション表示用)
   ========================================================================== */
function lumira_restore_demo_campaigns() {
    if ( ! isset( $_GET['lumira_restore_demo'] ) || ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $campaigns = array(
        array(
            'title' => 'キャンペーン1 - 春のスペシャル',
            'excerpt' => '限定50%オフのキャンペーン',
            'content' => '<p>春のスペシャルキャンペーンが開始しました。</p>'
        ),
        array(
            'title' => 'キャンペーン2 - 夏の美肌',
            'excerpt' => '紫外線対策メニュー',
            'content' => '<p>夏の美肌メニューが新登場。</p>'
        ),
        array(
            'title' => 'キャンペーン3 - 新メニュー',
            'excerpt' => '最新の美容技術を導入',
            'content' => '<p>新しいメニューが登場します。</p>'
        ),
        array(
            'title' => 'キャンペーン4 - 学割',
            'excerpt' => '学生向けの特別プラン',
            'content' => '<p>学生向けプランをご用意しました。</p>'
        ),
        array(
            'title' => 'キャンペーン5 - リニューアル',
            'excerpt' => '店舗リニューアルキャンペーン',
            'content' => '<p>店舗がリニューアルしました。</p>'
        ),
        array(
            'title' => 'キャンペーン6 - 親子割',
            'excerpt' => '親子での来店で割引',
            'content' => '<p>親子で一緒にご来店ください。</p>'
        ),
        array(
            'title' => 'キャンペーン7 - SNS拡散',
            'excerpt' => 'SNSシェアで500円クーポン',
            'content' => '<p>SNSでシェアしてクーポンをGET。</p>'
        ),
        array(
            'title' => 'キャンペーン8 - セール',
            'excerpt' => 'シーズン最後のセール',
            'content' => '<p>季節限定メニューがお買得。</p>'
        ),
        array(
            'title' => 'キャンペーン9 - 誕生月',
            'excerpt' => '誕生月15%オフ',
            'content' => '<p>誕生月の方限定キャンペーン。</p>'
        ),
        array(
            'title' => 'キャンペーン10 - 感謝',
            'excerpt' => '年末感謝キャンペーン',
            'content' => '<p>一年間のご愛顧ありがとう。</p>'
        ),
        array(
            'title' => 'キャンペーン11 - 新春',
            'excerpt' => '新春キャンペーン',
            'content' => '<p>新しい季節がスタートします。</p>'
        )
    );

    foreach ( $campaigns as $index => $campaign ) {
        wp_insert_post( array(
            'post_type' => 'campaign',
            'post_title' => $campaign['title'],
            'post_content' => $campaign['content'],
            'post_excerpt' => $campaign['excerpt'],
            'post_status' => 'publish',
            'post_date' => date( 'Y-m-d H:i:s', strtotime( '-' . ( $index * 5 ) . ' days' ) ),
            'comment_status' => 'closed'
        ) );
    }

    update_option( 'lumira_demo_campaigns_restored', true );
    wp_safe_redirect( home_url( '/campaign/' ) );
    exit;
}
add_action( 'init', 'lumira_restore_demo_campaigns' );
