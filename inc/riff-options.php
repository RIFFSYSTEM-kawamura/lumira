<?php
/**
 * RIFF Theme Admin Dashboard
 * LUMIRA WordPress Theme
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ==========================================================================
   1. Register Admin Menu
   ========================================================================== */
function riff_admin_menu() {
    add_menu_page(
        'RIFF テーマ設定',
        'RIFFテーマ',
        'manage_options',
        'riff-theme-options',
        'riff_options_page',
        'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>' ),
        60
    );
}
add_action( 'admin_menu', 'riff_admin_menu' );

/* ==========================================================================
   2. Enqueue Admin Assets
   ========================================================================== */
function riff_admin_enqueue( $hook ) {
    if ( strpos( $hook, 'riff-theme-options' ) === false ) return;

    wp_enqueue_media();
    wp_enqueue_style(
        'riff-admin-style',
        LUMIRA_URI . '/assets/css/admin.css',
        [ 'wp-color-picker' ],
        LUMIRA_VERSION
    );
    wp_enqueue_script( 'wp-color-picker' );
    wp_enqueue_script(
        'riff-admin-js',
        LUMIRA_URI . '/assets/js/admin.js',
        [ 'jquery', 'wp-color-picker' ],
        LUMIRA_VERSION,
        true
    );
    wp_localize_script( 'riff-admin-js', 'RIFF_ADMIN', [
        'nonce' => wp_create_nonce( 'riff_admin_nonce' ),
    ] );
}
add_action( 'admin_enqueue_scripts', 'riff_admin_enqueue' );

/* ==========================================================================
   3. Save Handler
   ========================================================================== */
function riff_save_options() {
    if (
        ! isset( $_POST['riff_nonce'] ) ||
        ! wp_verify_nonce( $_POST['riff_nonce'], 'riff_save_options' ) ||
        ! current_user_can( 'manage_options' )
    ) {
        wp_die( 'セキュリティエラーが発生しました。', 403 );
    }

    // WordPress core options
    if ( isset( $_POST['site_title'] ) ) {
        update_option( 'blogname', sanitize_text_field( wp_unslash( $_POST['site_title'] ) ) );
    }
    if ( isset( $_POST['site_description'] ) ) {
        update_option( 'blogdescription', sanitize_textarea_field( wp_unslash( $_POST['site_description'] ) ) );
    }

    $text_fields = [
        'header_style', 'header_logo_type', 'header_logo_text',
        'hero_bg_type', 'hero_slide_interval', 'hero_video_interval',
        'hero_title_jp',
        'hero_overlay_opacity', 'hero_overlay_color',
        'primary_color', 'secondary_color',
        // Business Information
        'business_name', 'business_address', 'business_phone', 'business_email', 'business_hours',
        'hero_image_anim_1', 'hero_image_anim_2', 'hero_image_anim_3',
        'hero_video_anim_1', 'hero_video_anim_2', 'hero_video_anim_3',
        'about_title_en', 'about_title_ja', 'about_btn_text', 'about_bg_color',
        'orb_color_main', 'orb_color_mid', 'orb_color_dark',
        'marquee_text', 'marquee_speed', 'marquee_text_color', 'marquee_font_size', 'marquee_bg_color',
        'sticky_btn_count',
        'sticky_btn_1_line1', 'sticky_btn_1_color', 'sticky_btn_1_text_color',
        'sticky_btn_2_line1', 'sticky_btn_2_color', 'sticky_btn_2_text_color',
        // Campaign course names
        'campaign_course_name_1', 'campaign_course_name_2', 'campaign_course_name_3',
        'campaign_course_name_4', 'campaign_course_name_5', 'campaign_course_name_6',
    ];

    // HTML-allowed fields (campaign, menu - allow <br class="pc"> etc)
    $html_fields = [
        // Menu fields
        'menu_title_en', 'menu_title_ja', 'menu_lead', 'menu_description',
        // Campaign fields
        'campaign_title_ja', 'campaign_subtitle',
        'campaign_label_1', 'campaign_title_1', 'campaign_description_1', 'campaign_original_price_1', 'campaign_price_1',
        'campaign_label_2', 'campaign_title_2', 'campaign_description_2', 'campaign_original_price_2', 'campaign_price_2',
        'campaign_label_3', 'campaign_title_3', 'campaign_description_3', 'campaign_original_price_3', 'campaign_price_3',
        'campaign_label_4', 'campaign_title_4', 'campaign_description_4', 'campaign_original_price_4', 'campaign_price_4',
        'campaign_label_5', 'campaign_title_5', 'campaign_description_5', 'campaign_original_price_5', 'campaign_price_5',
        'campaign_label_6', 'campaign_title_6', 'campaign_description_6', 'campaign_original_price_6', 'campaign_price_6',
        // Menu item fields
        'menu_label_1', 'menu_title_1', 'menu_description_1',
        'menu_label_2', 'menu_title_2', 'menu_description_2',
        'menu_label_3', 'menu_title_3', 'menu_description_3',
        'menu_label_4', 'menu_title_4', 'menu_description_4',
        'menu_label_5', 'menu_title_5', 'menu_description_5',
        'menu_label_6', 'menu_title_6', 'menu_description_6',
    ];

    $current = get_option( 'riff_lumira_options', [] );

    foreach ( $text_fields as $field ) {
        if ( isset( $_POST[ $field ] ) ) {
            $current[ $field ] = sanitize_text_field( wp_unslash( $_POST[ $field ] ) );
        }
    }

    // Textarea fields (allow HTML tags like <br class="pc">)
    foreach ( [ 'hero_subtitle', 'about_lead' ] as $tf ) {
        if ( isset( $_POST[ $tf ] ) ) {
            $current[ $tf ] = wp_kses_post( wp_unslash( $_POST[ $tf ] ) );
        }
    }

    // HTML-allowed fields (campaign, menu - allow <br class="pc"> etc)
    foreach ( $html_fields as $hf ) {
        if ( isset( $_POST[ $hf ] ) ) {
            $current[ $hf ] = wp_kses_post( wp_unslash( $_POST[ $hf ] ) );
        }
    }

    // About background type
    $current['about_bg_type'] = in_array( $_POST['about_bg_type'] ?? '', [ 'color', 'image' ], true )
        ? $_POST['about_bg_type']
        : 'color';

    // HTML-allowed fields (wp_kses_post)
    if ( isset( $_POST['about_description'] ) ) {
        $current['about_description'] = wp_kses_post( wp_unslash( $_POST['about_description'] ) );
    }

    // URL fields (strict validation for external URLs)
    foreach ( [ 'hero_video_url', 'hero_video_url_2', 'hero_video_url_3', 'sns_instagram', 'sns_twitter', 'sns_facebook', 'sns_line', 'sns_threads', 'sns_youtube', 'sticky_btn_1_url', 'sticky_btn_2_url', 'campaign_url_1', 'campaign_url_2', 'campaign_url_3', 'campaign_url_4', 'campaign_url_5', 'campaign_url_6' ] as $uf ) {
        if ( isset( $_POST[ $uf ] ) ) {
            $current[ $uf ] = esc_url_raw( wp_unslash( $_POST[ $uf ] ) );
        }
    }

    // campaign_link と about_link: 相対パスと絶対URLの両方をサポート
    foreach ( [ 'campaign_link', 'about_link' ] as $link_field ) {
        if ( isset( $_POST[ $link_field ] ) ) {
            $link_val = sanitize_text_field( wp_unslash( $_POST[ $link_field ] ) );
            $current[ $link_field ] = $link_val;
        }
    }

    // SNS show/hide checkboxes
    foreach ( [ 'sns_instagram_show', 'sns_twitter_show', 'sns_facebook_show', 'sns_line_show', 'sns_threads_show', 'sns_youtube_show' ] as $cb ) {
        $current[ $cb ] = isset( $_POST[ $cb ] ) ? 1 : 0;
    }

    // Sticky CTA show checkbox
    $current['sticky_btn_show'] = isset( $_POST['sticky_btn_show'] ) ? 1 : 0;

    // Campaign PICK UP checkboxes
    $pickup_items = [];
    for ( $i = 1; $i <= 6; $i++ ) {
        if ( isset( $_POST[ "campaign_pickup_$i" ] ) ) {
            $pickup_items[] = $i;
        }
    }
    $current['campaign_pickup'] = $pickup_items;

    foreach ( [ 'header_logo_image_id', 'hero_image_id', 'hero_image_id_2', 'hero_image_id_3', 'nav_bg_image_id', 'about_bg_image_id', 'menu_image_1', 'menu_image_2', 'menu_image_3', 'menu_image_4', 'menu_image_5', 'menu_image_6', 'campaign_image_1', 'campaign_image_2', 'campaign_image_3', 'campaign_image_4', 'campaign_image_5', 'campaign_image_6', 'campaign_archive_hero_image_id', 'campaign_single_hero_image_id' ] as $img_field ) {
        if ( isset( $_POST[ $img_field ] ) ) {
            $current[ $img_field ] = absint( $_POST[ $img_field ] );
        }
    }

    update_option( 'riff_lumira_options', $current );

    $tab = isset( $_POST['current_tab'] ) ? sanitize_key( $_POST['current_tab'] ) : 'basic';
    wp_redirect( add_query_arg( [ 'page' => 'riff-theme-options', 'tab' => $tab, 'saved' => '1' ], admin_url( 'admin.php' ) ) );
    exit;
}
add_action( 'admin_post_riff_save_options', 'riff_save_options' );

/* ==========================================================================
   4. Options Page HTML
   ========================================================================== */
function riff_options_page() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    $opts  = lumira_get_options();
    $saved = isset( $_GET['saved'] ) && $_GET['saved'] === '1';
    $tab   = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'basic';

    $tabs = [
        'basic'      => '基本設定',
        'header'     => 'ヘッダー設定',
        'hero'       => 'ヒーロー設定',
        'front'      => 'フロントページ',
        'menu'       => 'メニューセクション',
        'campaign'   => 'キャンペーンセクション',
        'sections'   => 'セクション設定',
        'sticky_cta' => 'スティッキーCTA',
        'cta'        => 'CTA設定',
        'footer_tab' => 'フッター設定',
        'seo'        => 'SEO設定',
        'faq'        => 'FAQ設定',
        'archive'    => 'アーカイブ',
        'animation'  => 'アニメーション',
    ];
    ?>
    <div class="wrap riff-wrap">

        <h1 class="riff-page-title">
            <span class="riff-badge">RIFF</span>
            テーマ設定
        </h1>

        <?php if ( $saved ) : ?>
        <div class="notice notice-success is-dismissible">
            <p>設定を保存しました。</p>
        </div>
        <?php endif; ?>

        <nav class="nav-tab-wrapper riff-tab-nav">
            <?php foreach ( $tabs as $key => $label ) : ?>
            <a href="<?php echo esc_url( add_query_arg( [ 'page' => 'riff-theme-options', 'tab' => $key ], admin_url( 'admin.php' ) ) ); ?>"
               class="nav-tab<?php echo $tab === $key ? ' nav-tab-active' : ''; ?>">
                <?php echo esc_html( $label ); ?>
            </a>
            <?php endforeach; ?>
        </nav>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="riff-options-form">
            <?php wp_nonce_field( 'riff_save_options', 'riff_nonce' ); ?>
            <input type="hidden" name="action" value="riff_save_options">
            <input type="hidden" name="current_tab" value="<?php echo esc_attr( $tab ); ?>">

            <div class="riff-tab-content">

                <?php if ( $tab === 'basic' ) : ?>
                <!-- ======= 基本設定 ======= -->
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">
                            <label for="site_title">サイトタイトル</label>
                        </th>
                        <td>
                            <input type="text" name="site_title" id="site_title"
                                   class="regular-text"
                                   value="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="site_description">サイト説明</label>
                        </th>
                        <td>
                            <textarea name="site_description" id="site_description"
                                      class="large-text" rows="3"><?php echo esc_textarea( get_bloginfo( 'description' ) ); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="primary_color">プライマリーカラー</label>
                        </th>
                        <td>
                            <input type="text" name="primary_color" id="primary_color"
                                   class="riff-color-picker"
                                   value="<?php echo esc_attr( $opts['primary_color'] ); ?>"
                                   data-default-color="#6b4226">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="secondary_color">セカンダリーカラー</label>
                        </th>
                        <td>
                            <input type="text" name="secondary_color" id="secondary_color"
                                   class="riff-color-picker"
                                   value="<?php echo esc_attr( $opts['secondary_color'] ); ?>"
                                   data-default-color="#ffffff">
                        </td>
                    </tr>
                </table>

                <?php elseif ( $tab === 'header' ) : ?>
                <!-- ======= ヘッダー設定 ======= -->
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">ロゴタイプ</th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="radio" name="header_logo_type" value="text"
                                        <?php checked( $opts['header_logo_type'], 'text' ); ?>>
                                    テキストロゴ
                                </label><br>
                                <label style="margin-top:6px;display:inline-block;">
                                    <input type="radio" name="header_logo_type" value="image"
                                        <?php checked( $opts['header_logo_type'], 'image' ); ?>>
                                    画像ロゴ
                                </label>
                                <p class="description">画像ロゴはこのページで下記に設定できます</p>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">ロゴ画像</th>
                        <td>
                            <div id="logo-image-preview" style="margin-bottom:15px;">
                                <?php
                                $logo_img_id = $opts['header_logo_image_id'] ?? 0;
                                if ( $logo_img_id ) {
                                    $img = wp_get_attachment_image_url( $logo_img_id, 'medium' );
                                    if ( $img ) {
                                        echo '<img src="' . esc_url( $img ) . '" style="max-width:200px;height:auto;" alt="Logo Preview">';
                                        echo '<p><a href="#" id="logo-remove-btn" style="color:#d32f2f;text-decoration:none;">削除</a></p>';
                                    }
                                }
                                ?>
                            </div>
                            <input type="hidden" name="header_logo_image_id" id="header_logo_image_id" value="<?php echo esc_attr( $logo_img_id ); ?>">
                            <button type="button" id="logo-upload-btn" class="button button-primary">画像をアップロード</button>
                            <p class="description">ロゴとして使用する画像をアップロードしてください。推奨サイズ: 200×80px 以上</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="header_logo_text">ロゴテキスト</label>
                        </th>
                        <td>
                            <input type="text" name="header_logo_text" id="header_logo_text"
                                   class="regular-text"
                                   value="<?php echo esc_attr( $opts['header_logo_text'] ); ?>"
                                   placeholder="LUMIRA">
                            <p class="description">テキストロゴを選択した場合に表示されます</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">ヘッダースタイル</th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="radio" name="header_style" value="transparent"
                                        <?php checked( $opts['header_style'], 'transparent' ); ?>>
                                    透過（デフォルト）
                                </label><br>
                                <label style="margin-top:6px;display:inline-block;">
                                    <input type="radio" name="header_style" value="solid"
                                        <?php checked( $opts['header_style'], 'solid' ); ?>>
                                    固定背景
                                </label>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">メニュー背景画像</th>
                        <td>
                            <div id="nav-bg-image-preview" style="margin-bottom:15px;">
                                <?php
                                $nav_bg_id = $opts['nav_bg_image_id'] ?? 0;
                                if ( $nav_bg_id ) {
                                    $img = wp_get_attachment_image_url( $nav_bg_id, 'medium' );
                                    if ( $img ) {
                                        echo '<img src="' . esc_url( $img ) . '" style="max-width:200px;height:auto;" alt="Preview">';
                                        echo '<p><a href="#" id="nav-bg-remove-btn" style="color:#d32f2f;text-decoration:none;">削除</a></p>';
                                    }
                                }
                                ?>
                            </div>
                            <input type="hidden" name="nav_bg_image_id" id="nav_bg_image_id" value="<?php echo esc_attr( $nav_bg_id ); ?>">
                            <button type="button" id="nav-bg-upload-btn" class="button button-primary">画像をアップロード</button>
                            <p class="description">ナビゲーションメニューを開いたときの背景画像です。リップル効果で歪みます。</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">SNS リンク</th>
                        <td>
                            <style>
                                .riff-sns-item { margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #eee; }
                                .riff-sns-item:last-child { border-bottom: none; }
                                .riff-sns-item label { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; }
                                .riff-sns-item input[type="checkbox"] { margin: 0; }
                                .riff-sns-item input[type="url"] { width: 100%; }
                            </style>
                            <div class="riff-sns-item">
                                <label><input type="checkbox" name="sns_instagram_show" value="1" <?php checked( $opts['sns_instagram_show'], 1 ); ?>> Instagram を表示</label>
                                <input type="url" name="sns_instagram" class="regular-text"
                                       value="<?php echo esc_attr( $opts['sns_instagram'] ); ?>"
                                       placeholder="https://www.instagram.com/youraccount/">
                            </div>
                            <div class="riff-sns-item">
                                <label><input type="checkbox" name="sns_twitter_show" value="1" <?php checked( $opts['sns_twitter_show'], 1 ); ?>> X (Twitter) を表示</label>
                                <input type="url" name="sns_twitter" class="regular-text"
                                       value="<?php echo esc_attr( $opts['sns_twitter'] ); ?>"
                                       placeholder="https://twitter.com/youraccount">
                            </div>
                            <div class="riff-sns-item">
                                <label><input type="checkbox" name="sns_facebook_show" value="1" <?php checked( $opts['sns_facebook_show'], 1 ); ?>> Facebook を表示</label>
                                <input type="url" name="sns_facebook" class="regular-text"
                                       value="<?php echo esc_attr( $opts['sns_facebook'] ); ?>"
                                       placeholder="https://www.facebook.com/yourpage">
                            </div>
                            <div class="riff-sns-item">
                                <label><input type="checkbox" name="sns_line_show" value="1" <?php checked( $opts['sns_line_show'], 1 ); ?>> LINE を表示</label>
                                <input type="url" name="sns_line" class="regular-text"
                                       value="<?php echo esc_attr( $opts['sns_line'] ); ?>"
                                       placeholder="https://line.me/R/ti/p/...">
                            </div>
                            <div class="riff-sns-item">
                                <label><input type="checkbox" name="sns_threads_show" value="1" <?php checked( $opts['sns_threads_show'], 1 ); ?>> Threads を表示</label>
                                <input type="url" name="sns_threads" class="regular-text"
                                       value="<?php echo esc_attr( $opts['sns_threads'] ); ?>"
                                       placeholder="https://www.threads.net/@youraccount">
                            </div>
                            <div class="riff-sns-item">
                                <label><input type="checkbox" name="sns_youtube_show" value="1" <?php checked( $opts['sns_youtube_show'], 1 ); ?>> YouTube を表示</label>
                                <input type="url" name="sns_youtube" class="regular-text"
                                       value="<?php echo esc_attr( $opts['sns_youtube'] ); ?>"
                                       placeholder="https://www.youtube.com/@yourchannel">
                            </div>
                        </td>
                    </tr>
                </table>

                <?php elseif ( $tab === 'hero' ) : ?>
                <!-- ======= ヒーロー設定 ======= -->
                <?php
                $bg_type       = $opts['hero_bg_type'] ?? 'image';
                $slide_interval = intval( $opts['hero_slide_interval'] ?? 5 );

                // Animation options helper
                $anim_options = [
                    'none'      => 'なし',
                    'moveRight' => '→ 右に移動',
                    'moveLeft'  => '← 左に移動',
                    'moveUp'    => '↑ 上に移動',
                    'zoomIn'    => '⊕ ズームイン',
                    'zoomOut'   => '⊖ ズームアウト',
                ];

                // Helper: render one image upload slot
                $render_img_slot = function( $field, $btn_id, $remove_id, $preview_id, $anim_field = '' ) use ( $opts, $anim_options ) {
                    $img_id       = intval( $opts[ $field ] ?? 0 );
                    $img_url      = $img_id ? ( wp_get_attachment_image_url( $img_id, 'thumbnail' ) ?: '' ) : '';
                    $current_anim = $anim_field ? ( $opts[ $anim_field ] ?? 'none' ) : 'none';
                    ?>
                    <div class="riff-media-wrap" style="margin-bottom:8px;">
                        <div class="riff-media-preview" id="<?php echo esc_attr( $preview_id ); ?>">
                            <?php if ( $img_url ) : ?>
                            <img src="<?php echo esc_url( $img_url ); ?>" alt=""
                                 style="max-width:100px;height:68px;object-fit:cover;border-radius:4px;display:block;">
                            <?php else : ?>
                            <span class="riff-no-image">未設定</span>
                            <?php endif; ?>
                        </div>
                        <input type="hidden" name="<?php echo esc_attr( $field ); ?>"
                               id="<?php echo esc_attr( $field ); ?>"
                               value="<?php echo esc_attr( $img_id ); ?>">
                        <button type="button" class="button riff-img-upload"
                                id="<?php echo esc_attr( $btn_id ); ?>"
                                data-preview="<?php echo esc_attr( $preview_id ); ?>"
                                data-input="<?php echo esc_attr( $field ); ?>">
                            選択
                        </button>
                        <?php if ( $img_url ) : ?>
                        <button type="button" class="button riff-img-remove"
                                id="<?php echo esc_attr( $remove_id ); ?>"
                                data-preview="<?php echo esc_attr( $preview_id ); ?>"
                                data-input="<?php echo esc_attr( $field ); ?>"
                                style="color:#d63638;margin-left:4px;">
                            削除
                        </button>
                        <?php endif; ?>
                    </div>
                    <?php if ( $anim_field ) : ?>
                    <div style="margin-top:4px;">
                        <label style="font-size:11px;color:#646970;">アニメーション：</label>
                        <select name="<?php echo esc_attr( $anim_field ); ?>" style="font-size:12px;">
                            <?php foreach ( $anim_options as $val => $label ) : ?>
                            <option value="<?php echo esc_attr( $val ); ?>"<?php selected( $current_anim, $val ); ?>>
                                <?php echo esc_html( $label ); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    <?php
                };
                ?>
                <table class="form-table" role="presentation">

                    <!-- 背景タイプ -->
                    <tr>
                        <th scope="row">背景タイプ</th>
                        <td>
                            <fieldset>
                                <label style="margin-right:20px;">
                                    <input type="radio" name="hero_bg_type" value="image"
                                           id="bg_type_image"
                                        <?php checked( $bg_type, 'image' ); ?>>
                                    画像スライドショー（最大3枚）
                                </label>
                                <label>
                                    <input type="radio" name="hero_bg_type" value="video"
                                           id="bg_type_video"
                                        <?php checked( $bg_type, 'video' ); ?>>
                                    動画（MP4）
                                </label>
                            </fieldset>
                        </td>
                    </tr>

                    <!-- 画像スライドショー設定 -->
                    <tr id="row-slideshow" <?php echo $bg_type === 'video' ? 'style="display:none"' : ''; ?>>
                        <th scope="row">背景画像</th>
                        <td>
                            <p style="font-size:12px;color:#646970;margin-bottom:12px;">
                                1枚のみ設定した場合は静止画になります。2〜3枚で自動フェード切替します。
                            </p>
                            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;max-width:560px;">
                                <div>
                                    <p style="font-size:12px;font-weight:600;color:#3c3c3c;margin-bottom:6px;">画像 1</p>
                                    <?php $render_img_slot( 'hero_image_id', 'img-upload-1', 'img-remove-1', 'img-preview-1', 'hero_image_anim_1' ); ?>
                                </div>
                                <div>
                                    <p style="font-size:12px;font-weight:600;color:#3c3c3c;margin-bottom:6px;">画像 2（任意）</p>
                                    <?php $render_img_slot( 'hero_image_id_2', 'img-upload-2', 'img-remove-2', 'img-preview-2', 'hero_image_anim_2' ); ?>
                                </div>
                                <div>
                                    <p style="font-size:12px;font-weight:600;color:#3c3c3c;margin-bottom:6px;">画像 3（任意）</p>
                                    <?php $render_img_slot( 'hero_image_id_3', 'img-upload-3', 'img-remove-3', 'img-preview-3', 'hero_image_anim_3' ); ?>
                                </div>
                            </div>
                        </td>
                    </tr>

                    <!-- スライドショー間隔 -->
                    <tr id="row-interval" <?php echo $bg_type === 'video' ? 'style="display:none"' : ''; ?>>
                        <th scope="row">
                            <label for="hero_slide_interval">切替間隔（秒）</label>
                        </th>
                        <td>
                            <input type="range" name="hero_slide_interval" id="slide-interval-range"
                                   min="3" max="12" step="1"
                                   value="<?php echo esc_attr( $slide_interval ); ?>"
                                   style="width:180px;vertical-align:middle;">
                            <span id="slide-interval-val" style="margin-left:10px;font-weight:600;color:#6b4226;">
                                <?php echo esc_html( $slide_interval ); ?>
                            </span> 秒
                        </td>
                    </tr>

                    <!-- 動画設定 (最大3本) -->
                    <tr id="row-video" <?php echo $bg_type !== 'video' ? 'style="display:none"' : ''; ?>>
                        <th scope="row">背景動画</th>
                        <td>
                            <p style="font-size:12px;color:#646970;margin-bottom:14px;">
                                1本のみ設定した場合はループ再生。2〜3本で自動フェード切替します。
                            </p>
                            <?php
                            $video_slots = [
                                [ 'label' => '動画 1',        'field' => 'hero_video_url',   'input_id' => 'video_url_1',   'preview_id' => 'video-preview-1', 'anim_field' => 'hero_video_anim_1' ],
                                [ 'label' => '動画 2（任意）', 'field' => 'hero_video_url_2', 'input_id' => 'video_url_2',   'preview_id' => 'video-preview-2', 'anim_field' => 'hero_video_anim_2' ],
                                [ 'label' => '動画 3（任意）', 'field' => 'hero_video_url_3', 'input_id' => 'video_url_3',   'preview_id' => 'video-preview-3', 'anim_field' => 'hero_video_anim_3' ],
                            ];
                            foreach ( $video_slots as $slot ) :
                                $vurl         = $opts[ $slot['field'] ] ?? '';
                                $current_anim = $opts[ $slot['anim_field'] ] ?? 'none';
                            ?>
                            <div style="margin-bottom:16px;padding:12px;background:#faf9f8;border:1px solid #e8e3de;border-radius:4px;">
                                <p style="font-size:12px;font-weight:600;color:#3c3c3c;margin-bottom:8px;"><?php echo esc_html( $slot['label'] ); ?></p>
                                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:6px;">
                                    <input type="url"
                                           name="<?php echo esc_attr( $slot['field'] ); ?>"
                                           id="<?php echo esc_attr( $slot['input_id'] ); ?>"
                                           class="regular-text riff-video-url"
                                           style="flex:1;min-width:180px;max-width:380px;"
                                           value="<?php echo esc_attr( $vurl ); ?>"
                                           placeholder="https://example.com/video.mp4"
                                           data-preview="<?php echo esc_attr( $slot['preview_id'] ); ?>">
                                    <button type="button" class="button riff-video-select"
                                            data-input="<?php echo esc_attr( $slot['input_id'] ); ?>"
                                            data-preview="<?php echo esc_attr( $slot['preview_id'] ); ?>">
                                        選択
                                    </button>
                                    <button type="button" class="button riff-video-clear"
                                            data-input="<?php echo esc_attr( $slot['input_id'] ); ?>"
                                            data-preview="<?php echo esc_attr( $slot['preview_id'] ); ?>"
                                            style="color:#d63638;">
                                        削除
                                    </button>
                                </div>
                                <div id="<?php echo esc_attr( $slot['preview_id'] ); ?>">
                                    <?php if ( $vurl ) : ?>
                                    <video style="max-width:200px;border-radius:4px;display:block;" controls muted>
                                        <source src="<?php echo esc_url( $vurl ); ?>">
                                    </video>
                                    <?php endif; ?>
                                </div>
                                <div style="margin-top:6px;">
                                    <label style="font-size:11px;color:#646970;">アニメーション：</label>
                                    <select name="<?php echo esc_attr( $slot['anim_field'] ); ?>" style="font-size:12px;">
                                        <?php foreach ( $anim_options as $val => $label ) : ?>
                                        <option value="<?php echo esc_attr( $val ); ?>"<?php selected( $current_anim, $val ); ?>>
                                            <?php echo esc_html( $label ); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <p class="description">MP4 / WebM 対応。各動画はミュート・自動再生・ループで表示されます。</p>
                        </td>
                    </tr>

                    <!-- 動画切替間隔 -->
                    <tr id="row-video-interval" <?php echo $bg_type !== 'video' ? 'style="display:none"' : ''; ?>>
                        <th scope="row">
                            <label for="video-interval-range">切替間隔（秒）</label>
                        </th>
                        <td>
                            <input type="range" name="hero_video_interval" id="video-interval-range"
                                   min="3" max="15" step="1"
                                   value="<?php echo esc_attr( intval( $opts['hero_video_interval'] ?? 7 ) ); ?>"
                                   style="width:180px;vertical-align:middle;">
                            <span id="video-interval-val" style="margin-left:10px;font-weight:600;color:#6b4226;">
                                <?php echo esc_html( intval( $opts['hero_video_interval'] ?? 7 ) ); ?>
                            </span> 秒
                            <p class="description">動画が2本以上ある場合のみ有効</p>
                        </td>
                    </tr>

                    <tr><td colspan="2" style="padding:0;border-bottom:1px solid #f4f2f0;"></td></tr>

                    <!-- テキスト設定 -->
                    <tr>
                        <th scope="row">
                            <label for="hero_title_jp">メインキャッチコピー（日本語）</label>
                        </th>
                        <td>
                            <input type="text" name="hero_title_jp" id="hero_title_jp"
                                   class="regular-text"
                                   value="<?php echo esc_attr( $opts['hero_title_jp'] ); ?>"
                                   placeholder="空間を、光で彩る。">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="hero_subtitle">サブテキスト（説明文）</label>
                        </th>
                        <td>
                            <textarea name="hero_subtitle" id="hero_subtitle"
                                      class="large-text" rows="3"
                                      placeholder="一人ひとりのライフスタイルに寄り添い、理想の暮らしを形にする。"><?php echo esc_textarea( $opts['hero_subtitle'] ); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="overlay-range">オーバーレイ不透明度</label>
                        </th>
                        <td>
                            <input type="range" name="hero_overlay_opacity" id="overlay-range"
                                   min="0" max="1" step="0.05"
                                   value="<?php echo esc_attr( $opts['hero_overlay_opacity'] ); ?>"
                                   style="width:200px;vertical-align:middle;">
                            <span id="overlay-range-val" style="margin-left:10px;font-weight:600;color:#6b4226;">
                                <?php echo esc_html( $opts['hero_overlay_opacity'] ); ?>
                            </span>
                            <p class="description">0 = 透明、1 = 完全不透明</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="hero_overlay_color">オーバーレイカラー</label>
                        </th>
                        <td>
                            <input type="text" name="hero_overlay_color" id="hero_overlay_color"
                                   class="riff-color-picker"
                                   value="<?php echo esc_attr( $opts['hero_overlay_color'] ); ?>"
                                   data-default-color="#080810">
                        </td>
                    </tr>
                </table>

                <?php elseif ( $tab === 'menu' ) : ?>
                <!-- ======= メニューセクション設定 ======= -->
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">
                            <label for="menu_title_en">タイトル（英語）</label>
                        </th>
                        <td>
                            <input type="text" name="menu_title_en" id="menu_title_en"
                                   class="regular-text"
                                   value="<?php echo esc_attr( $opts['menu_title_en'] ); ?>"
                                   placeholder="MENU">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="menu_title_ja">タイトル（日本語）</label>
                        </th>
                        <td>
                            <input type="text" name="menu_title_ja" id="menu_title_ja"
                                   class="regular-text"
                                   value="<?php echo esc_attr( $opts['menu_title_ja'] ); ?>"
                                   placeholder="メニュー">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="menu_lead">リード文</label>
                        </th>
                        <td>
                            <input type="text" name="menu_lead" id="menu_lead"
                                   class="regular-text"
                                   value="<?php echo esc_attr( $opts['menu_lead'] ); ?>"
                                   placeholder="体の中から本当の美しさを。">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="menu_description">説明文</label>
                        </th>
                        <td>
                            <input type="text" name="menu_description" id="menu_description"
                                   class="regular-text"
                                   value="<?php echo esc_attr( $opts['menu_description'] ); ?>"
                                   placeholder="当店ならではの充実したエステメニュー">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">メニューアイテム画像</th>
                        <td>
                            <p style="font-size:12px;color:#646970;margin-bottom:16px;">
                                6つのメニュー画像を設定してください。推奨サイズ: 800×1000px
                            </p>
                            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;max-width:900px;">
                                <?php
                                $menu_items_render = [
                                    [ 'num' => 1, 'label' => 'BODY CARE' ],
                                    [ 'num' => 2, 'label' => 'LEG CARE' ],
                                    [ 'num' => 3, 'label' => 'DETOX CARE' ],
                                    [ 'num' => 4, 'label' => 'HAND CARE' ],
                                    [ 'num' => 5, 'label' => 'HIP CARE' ],
                                    [ 'num' => 6, 'label' => 'FACE CARE' ],
                                ];
                                foreach ( $menu_items_render as $item ) :
                                    $img_id = $opts[ 'menu_image_' . $item['num'] ] ?? 0;
                                    $upload_btn_id = 'menu-upload-btn-' . $item['num'];
                                    $preview_id = 'menu-preview-' . $item['num'];
                                    $remove_btn_id = 'menu-remove-btn-' . $item['num'];
                                    $input_id = 'menu_image_' . $item['num'];
                                ?>
                                <div>
                                    <p style="font-size:12px;font-weight:600;color:#3c3c3c;margin-bottom:8px;"><?php echo esc_html( $item['label'] ); ?></p>
                                    <div id="<?php echo esc_attr( $preview_id ); ?>" style="background:#f0ede8;aspect-ratio:2/2.5;border-radius:4px;margin-bottom:8px;display:flex;align-items:center;justify-content:center;overflow:hidden;">
                                        <?php
                                        if ( $img_id ) {
                                            $img_url = wp_get_attachment_image_url( $img_id, 'medium' );
                                            if ( $img_url ) {
                                                echo '<img src="' . esc_url( $img_url ) . '" style="width:100%;height:100%;object-fit:cover;" alt="Menu Preview">';
                                            }
                                        } else {
                                            echo '<span style="color:#999;font-size:12px;">プレビュー</span>';
                                        }
                                        ?>
                                    </div>
                                    <input type="hidden" name="<?php echo esc_attr( $input_id ); ?>" id="<?php echo esc_attr( $input_id ); ?>" value="<?php echo esc_attr( $img_id ); ?>">
                                    <button type="button" id="<?php echo esc_attr( $upload_btn_id ); ?>" class="button button-small" style="width:100%;margin-bottom:4px;">アップロード</button>
                                    <?php if ( $img_id ) : ?>
                                    <button type="button" id="<?php echo esc_attr( $remove_btn_id ); ?>" class="button button-small" style="width:100%;color:#d32f2f;">削除</button>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </td>
                    </tr>
                </table>

                <?php elseif ( $tab === 'campaign' ) : ?>
                <!-- ======= キャンペーンセクション設定 ======= -->
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">
                            <label for="campaign_title_ja">タイトル</label>
                        </th>
                        <td>
                            <input type="text" name="campaign_title_ja" id="campaign_title_ja"
                                   class="regular-text"
                                   value="<?php echo esc_attr( $opts['campaign_title_ja'] ); ?>"
                                   placeholder="初回体験キャンペーン">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="campaign_subtitle">サブタイトル</label>
                        </th>
                        <td>
                            <input type="text" name="campaign_subtitle" id="campaign_subtitle"
                                   class="regular-text"
                                   value="<?php echo esc_attr( $opts['campaign_subtitle'] ); ?>"
                                   placeholder="～LUMIRAの本格エステをお得にお試し～">
                        </td>
                    </tr>
                    <tr style="background:#f9f5f0;">
                        <th scope="row" colspan="2" style="padding:12px 15px;">
                            <strong>コース名設定</strong>
                        </th>
                    </tr>
                    <?php
                    $default_course_names = [
                        1 => 'モデル美脚ブーストコース',
                        2 => 'オーダーセレクト美痩身コース',
                        3 => '汗ばくセルライトproコース',
                        4 => '花嫁トータルビューティーコース',
                        5 => 'ブライダルコース',
                        6 => '睡眠スイッチコース',
                    ];
                    for ( $i = 1; $i <= 6; $i++ ) :
                        $course_name = $opts[ "campaign_course_name_$i" ] ?? $default_course_names[ $i ];
                    ?>
                    <tr>
                        <th scope="row">
                            <label for="campaign_course_name_<?php echo $i; ?>">コース <?php echo $i; ?> の名前</label>
                        </th>
                        <td>
                            <input type="text" name="campaign_course_name_<?php echo $i; ?>"
                                   id="campaign_course_name_<?php echo $i; ?>"
                                   class="regular-text"
                                   value="<?php echo esc_attr( $course_name ); ?>"
                                   placeholder="<?php echo esc_attr( $default_course_names[ $i ] ); ?>">
                        </td>
                    </tr>
                    <?php endfor; ?>
                    <tr>
                        <th scope="row">キャンペーン画像</th>
                        <td>
                            <p style="font-size:12px;color:#646970;margin-bottom:16px;">
                                6つのキャンペーン画像を設定してください。推奨サイズ: 800×600px
                            </p>
                            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;max-width:900px;">
                                <?php
                                $default_render_names = [
                                    1 => 'モデル美脚ブーストコース',
                                    2 => 'オーダーセレクト美痩身コース',
                                    3 => '汗ばくセルライトproコース',
                                    4 => '花嫁トータルビューティーコース',
                                    5 => 'ブライダルコース',
                                    6 => '睡眠スイッチコース',
                                ];
                                $pickup_items = $opts['campaign_pickup'] ?? [];
                                for ( $num = 1; $num <= 6; $num++ ) :
                                    $item_label = $opts[ "campaign_course_name_$num" ] ?? $default_render_names[ $num ];
                                    $img_id = $opts[ 'campaign_image_' . $num ] ?? 0;
                                    $upload_btn_id = 'campaign-upload-btn-' . $num;
                                    $preview_id = 'campaign-preview-' . $num;
                                    $remove_btn_id = 'campaign-remove-btn-' . $num;
                                    $input_id = 'campaign_image_' . $num;
                                    $is_pickup = in_array( $num, $pickup_items );
                                ?>
                                <div>
                                    <p style="font-size:12px;font-weight:600;color:#3c3c3c;margin-bottom:8px;">
                                        <?php echo esc_html( $item_label ); ?>
                                        <?php if ( $is_pickup ) : ?>
                                        <span style="background:#d4a373;color:#fff;padding:2px 6px;border-radius:2px;font-size:10px;margin-left:4px;">PICK UP</span>
                                        <?php endif; ?>
                                    </p>
                                    <div id="<?php echo esc_attr( $preview_id ); ?>" style="background:#f0ede8;aspect-ratio:4/3;border-radius:4px;margin-bottom:8px;display:flex;align-items:center;justify-content:center;overflow:hidden;">
                                        <?php
                                        if ( $img_id ) {
                                            $img_url = wp_get_attachment_image_url( $img_id, 'medium' );
                                            if ( $img_url ) {
                                                echo '<img src="' . esc_url( $img_url ) . '" style="width:100%;height:100%;object-fit:cover;" alt="Campaign Preview">';
                                            }
                                        } else {
                                            echo '<span style="color:#999;font-size:12px;">プレビュー</span>';
                                        }
                                        ?>
                                    </div>
                                    <input type="hidden" name="<?php echo esc_attr( $input_id ); ?>" id="<?php echo esc_attr( $input_id ); ?>" value="<?php echo esc_attr( $img_id ); ?>">
                                    <button type="button" id="<?php echo esc_attr( $upload_btn_id ); ?>" class="button button-small" style="width:100%;margin-bottom:4px;">アップロード</button>
                                    <?php if ( $img_id ) : ?>
                                    <button type="button" id="<?php echo esc_attr( $remove_btn_id ); ?>" class="button button-small" style="width:100%;color:#d32f2f;">削除</button>
                                    <?php endif; ?>
                                    <label style="display:block;margin-top:8px;font-size:12px;">
                                        <input type="checkbox" name="campaign_pickup_<?php echo esc_attr( $num ); ?>" value="1" <?php checked( $is_pickup ); ?>>
                                        PICK UPに表示
                                    </label>
                                </div>
                                <?php endfor; ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="campaign_link">キャンペーン一覧ページのリンク先</label>
                        </th>
                        <td>
                            <input type="text" name="campaign_link" id="campaign_link"
                                   class="regular-text"
                                   value="<?php echo esc_attr( $opts['campaign_link'] ?? '/campaign' ); ?>"
                                   placeholder="/campaign">
                            <p class="description">相対パス（例: /campaign）または完全なURLを入力してください</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="campaign_btn_text">ボタンテキスト</label>
                        </th>
                        <td>
                            <input type="text" name="campaign_btn_text" id="campaign_btn_text"
                                   class="regular-text"
                                   value="<?php echo esc_attr( $opts['campaign_btn_text'] ?? 'キャンペーン一覧を見る' ); ?>"
                                   placeholder="キャンペーン一覧を見る">
                        </td>
                    </tr>
                    <tr style="background:#f9f5f0;">
                        <th scope="row" colspan="2" style="padding:12px 15px;">
                            <strong>各カードのリンク先URL設定</strong>
                        </th>
                    </tr>
                    <?php
                    $default_url_names = [
                        1 => 'モデル美脚ブーストコース',
                        2 => 'オーダーセレクト美痩身コース',
                        3 => '汗ばくセルライトproコース',
                        4 => '花嫁トータルビューティーコース',
                        5 => 'ブライダルコース',
                        6 => '睡眠スイッチコース',
                    ];
                    for ( $url_num = 1; $url_num <= 6; $url_num++ ) :
                        $url_course_name = $opts[ "campaign_course_name_$url_num" ] ?? $default_url_names[ $url_num ];
                        $campaign_url = $opts[ 'campaign_url_' . $url_num ] ?? '';
                    ?>
                    <tr>
                        <th scope="row">
                            <label for="campaign_url_<?php echo esc_attr( $url_num ); ?>">
                                <?php echo esc_html( $url_course_name ); ?> のリンク先
                            </label>
                        </th>
                        <td>
                            <input type="text" name="campaign_url_<?php echo esc_attr( $url_num ); ?>"
                                   id="campaign_url_<?php echo esc_attr( $url_num ); ?>"
                                   class="regular-text"
                                   value="<?php echo esc_attr( $campaign_url ); ?>"
                                   placeholder="/campaign/%e3%83%a2%e3%83%87%e3%83%ab%e7%be%8e%e8%84%9a%e3%83%96%e3%83%bc%e3%82%b9%e3%83%88%e3%82%b3%e3%83%bc%e3%82%b9-<?php echo $url_num; ?>/">
                            <p class="description">投稿スラッグまたは外部URLを入力してください。投稿の詳細ページURLをコピー＆ペーストしてください。</p>
                        </td>
                    </tr>
                    <?php endfor; ?>
                    <tr style="background:#f9f5f0;">
                        <th scope="row" colspan="2" style="padding:12px 15px;">
                            <strong>キャンペーン一覧・詳細ページのヒーロー背景画像</strong>
                        </th>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="campaign_archive_hero_image_id">キャンペーン一覧ページのヒーロー背景</label>
                        </th>
                        <td>
                            <input type="hidden" name="campaign_archive_hero_image_id" id="campaign_archive_hero_image_id"
                                   value="<?php echo esc_attr( $opts['campaign_archive_hero_image_id'] ?? 0 ); ?>">
                            <?php
                            $archive_img_id = intval( $opts['campaign_archive_hero_image_id'] ?? 0 );
                            $archive_img_url = $archive_img_id ? wp_get_attachment_image_url( $archive_img_id, 'thumbnail' ) : '';
                            ?>
                            <img id="campaign-archive-hero-preview" src="<?php echo esc_url( $archive_img_url ); ?>"
                                 style="max-width:200px;display:<?php echo $archive_img_url ? 'block' : 'none'; ?>;margin-bottom:10px;">
                            <button type="button" class="button riff-img-upload" data-input="campaign_archive_hero_image_id" data-preview="campaign-archive-hero-preview">画像を選択</button>
                            <button type="button" class="button riff-img-remove" data-input="campaign_archive_hero_image_id" data-preview="campaign-archive-hero-preview">削除</button>
                            <p class="description">推奨サイズ: 1920×600px</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="campaign_single_hero_image_id">キャンペーン詳細ページのヒーロー背景</label>
                        </th>
                        <td>
                            <input type="hidden" name="campaign_single_hero_image_id" id="campaign_single_hero_image_id"
                                   value="<?php echo esc_attr( $opts['campaign_single_hero_image_id'] ?? 0 ); ?>">
                            <?php
                            $single_img_id = intval( $opts['campaign_single_hero_image_id'] ?? 0 );
                            $single_img_url = $single_img_id ? wp_get_attachment_image_url( $single_img_id, 'thumbnail' ) : '';
                            ?>
                            <img id="campaign-single-hero-preview" src="<?php echo esc_url( $single_img_url ); ?>"
                                 style="max-width:200px;display:<?php echo $single_img_url ? 'block' : 'none'; ?>;margin-bottom:10px;">
                            <button type="button" class="button riff-img-upload" data-input="campaign_single_hero_image_id" data-preview="campaign-single-hero-preview">画像を選択</button>
                            <button type="button" class="button riff-img-remove" data-input="campaign_single_hero_image_id" data-preview="campaign-single-hero-preview">削除</button>
                            <p class="description">推奨サイズ: 1920×600px</p>
                        </td>
                    </tr>
                </table>

                <?php elseif ( $tab === 'sections' ) : ?>
                <!-- ======= セクション設定（About Us） ======= -->
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row" colspan="2" style="background:#f9f5f0;padding:12px 15px;">
                            <strong>見出し・テキスト設定</strong>
                        </th>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="about_title_en">タイトル（英語）</label>
                        </th>
                        <td>
                            <input type="text" name="about_title_en" id="about_title_en"
                                   class="regular-text"
                                   value="<?php echo esc_attr( $opts['about_title_en'] ); ?>"
                                   placeholder="About Us">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="about_title_ja">タイトル（日本語）</label>
                        </th>
                        <td>
                            <input type="text" name="about_title_ja" id="about_title_ja"
                                   class="regular-text"
                                   value="<?php echo esc_attr( $opts['about_title_ja'] ); ?>"
                                   placeholder="LUMIRAについて">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" colspan="2" style="background:#f9f5f0;padding:12px 15px;">
                            <strong>背景設定</strong>
                        </th>
                    </tr>
                    <tr>
                        <th scope="row">背景タイプ</th>
                        <td>
                            <fieldset>
                                <label style="margin-right:20px;">
                                    <input type="radio" name="about_bg_type" value="color"
                                        <?php checked( $opts['about_bg_type'], 'color' ); ?>>
                                    背景色
                                </label>
                                <label>
                                    <input type="radio" name="about_bg_type" value="image"
                                        <?php checked( $opts['about_bg_type'], 'image' ); ?>>
                                    背景画像
                                </label>
                            </fieldset>
                        </td>
                    </tr>
                    <tr id="about-color-row">
                        <th scope="row">
                            <label for="about_bg_color">背景色</label>
                        </th>
                        <td>
                            <input type="text" name="about_bg_color" id="about_bg_color"
                                   class="riff-color-picker regular-text"
                                   value="<?php echo esc_attr( $opts['about_bg_color'] ); ?>">
                            <p class="description">About Us セクションの背景色を選択します。</p>
                        </td>
                    </tr>
                    <tr id="about-image-row">
                        <th scope="row">
                            <label for="about_bg_image_id">背景画像</label>
                        </th>
                        <td>
                            <input type="hidden" name="about_bg_image_id" id="about_bg_image_id"
                                   value="<?php echo esc_attr( $opts['about_bg_image_id'] ); ?>">
                            <?php
                            $img_id = intval( $opts['about_bg_image_id'] ?? 0 );
                            $img_url = $img_id ? wp_get_attachment_image_url( $img_id, 'thumbnail' ) : '';
                            ?>
                            <img id="about-bg-preview" src="<?php echo esc_url( $img_url ); ?>"
                                 style="max-width:200px;display:<?php echo $img_url ? 'block' : 'none'; ?>;margin-bottom:10px;">
                            <button type="button" class="button riff-img-upload" data-input="about_bg_image_id" data-preview="about-bg-preview">画像を選択</button>
                            <button type="button" class="button riff-img-remove" data-input="about_bg_image_id" data-preview="about-bg-preview">削除</button>
                            <p class="description">About Us セクションの背景として使用する画像をアップロードします。</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" colspan="2" style="background:#f9f5f0;padding:12px 15px;">
                            <strong>スパークル（菱形）のカラー設定</strong>
                        </th>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="orb_color_main">メイン色</label>
                        </th>
                        <td>
                            <input type="text" name="orb_color_main" id="orb_color_main"
                                   class="riff-color-picker regular-text"
                                   value="<?php echo esc_attr( $opts['orb_color_main'] ); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="orb_color_mid">ミッド色</label>
                        </th>
                        <td>
                            <input type="text" name="orb_color_mid" id="orb_color_mid"
                                   class="riff-color-picker regular-text"
                                   value="<?php echo esc_attr( $opts['orb_color_mid'] ); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="orb_color_dark">ダーク色</label>
                        </th>
                        <td>
                            <input type="text" name="orb_color_dark" id="orb_color_dark"
                                   class="riff-color-picker regular-text"
                                   value="<?php echo esc_attr( $opts['orb_color_dark'] ); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="about_title_ja">About Us 日本語タイトル</label>
                        </th>
                        <td>
                            <input type="text" name="about_title_ja" id="about_title_ja"
                                   class="regular-text"
                                   value="<?php echo esc_attr( $opts['about_title_ja'] ); ?>"
                                   placeholder="LUMIRAについて">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="about_lead">メインキャッチ（リード文）</label>
                        </th>
                        <td>
                            <textarea name="about_lead" id="about_lead"
                                      class="large-text" rows="3"
                                      placeholder="独自のコンセプトで、暮らしに寄り添う空間をつくります。"><?php echo esc_textarea( $opts['about_lead'] ); ?></textarea>
                            <p class="description">About セクションの目立つ説明文です。改行で複数行表示できます。</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="about_description">説明文（詳細）</label>
                        </th>
                        <td>
                            <textarea name="about_description" id="about_description"
                                      class="large-text" rows="6"
                                      placeholder="私たちLUMIRAは、お客様のライフスタイルに合わせた最高品質の空間を提供いたします。"><?php echo esc_textarea( $opts['about_description'] ); ?></textarea>
                            <p class="description">段落や改行など、簡単なHTMLが使用できます。（&lt;p&gt;, &lt;br&gt;, &lt;strong&gt;, &lt;em&gt; など）</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="about_link">VIEW MORE ボタンのリンク先</label>
                        </th>
                        <td>
                            <input type="text" name="about_link" id="about_link"
                                   class="regular-text"
                                   value="<?php echo esc_attr( $opts['about_link'] ); ?>"
                                   placeholder="/about">
                            <p class="description">相対パス（/about）または完全なURLを入力してください</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="about_btn_text">ボタンテキスト</label>
                        </th>
                        <td>
                            <input type="text" name="about_btn_text" id="about_btn_text"
                                   class="regular-text"
                                   value="<?php echo esc_attr( $opts['about_btn_text'] ); ?>"
                                   placeholder="VIEW MORE">
                        </td>
                    </tr>
                </table>

                <!-- ======= マーキーセクション設定 ======= -->
                <h3 style="margin-top:32px;">マーキーセクション</h3>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">
                            <label for="marquee_text">テキスト</label>
                        </th>
                        <td>
                            <input type="text" name="marquee_text" id="marquee_text"
                                   class="large-text"
                                   value="<?php echo esc_attr( $opts['marquee_text'] ); ?>"
                                   placeholder="LUMIRA ◆ 光と空間のデザイン ◆">
                            <p class="description">スクロールするテキストを入力してください。</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="marquee_speed">スクロール速度（秒）</label>
                        </th>
                        <td>
                            <input type="range" name="marquee_speed" id="marquee-speed-range"
                                   min="10" max="120" step="5"
                                   value="<?php echo esc_attr( $opts['marquee_speed'] ); ?>"
                                   style="width:180px;vertical-align:middle;">
                            <span id="marquee-speed-val"><?php echo esc_html( $opts['marquee_speed'] ); ?></span>秒
                            <p class="description">10秒（高速）〜 120秒（低速）の間で調整できます。</p>
                            <script>
                                document.getElementById('marquee-speed-range').addEventListener('input', function() {
                                    document.getElementById('marquee-speed-val').textContent = this.value;
                                });
                            </script>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="marquee_text_color">テキストカラー</label>
                        </th>
                        <td>
                            <input type="text" name="marquee_text_color" id="marquee_text_color"
                                   class="riff-color-picker regular-text"
                                   value="<?php echo esc_attr( $opts['marquee_text_color'] ); ?>"
                                   data-default-color="#2c1a10">
                            <p class="description">テキストの色を選択します。</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="marquee_font_size">フォントサイズ（px）</label>
                        </th>
                        <td>
                            <input type="range" name="marquee_font_size" id="marquee-size-range"
                                   min="40" max="160" step="4"
                                   value="<?php echo esc_attr( $opts['marquee_font_size'] ); ?>"
                                   style="width:180px;vertical-align:middle;">
                            <span id="marquee-size-val"><?php echo esc_html( $opts['marquee_font_size'] ); ?></span>px
                            <p class="description">40px（小）〜 160px（大）の間で調整できます。</p>
                            <script>
                                document.getElementById('marquee-size-range').addEventListener('input', function() {
                                    document.getElementById('marquee-size-val').textContent = this.value;
                                });
                            </script>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="marquee_bg_color">背景色</label>
                        </th>
                        <td>
                            <input type="text" name="marquee_bg_color" id="marquee_bg_color"
                                   class="riff-color-picker regular-text"
                                   value="<?php echo esc_attr( $opts['marquee_bg_color'] ); ?>"
                                   data-default-color="#f2ede6">
                            <p class="description">マーキーセクションの背景色を選択します。</p>
                        </td>
                    </tr>
                </table>

                <?php elseif ( $tab === 'sticky_cta' ) : ?>
                <!-- ======= スティッキーCTA設定 ======= -->
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">
                            <label><input type="checkbox" name="sticky_btn_show" value="1" <?php checked( $opts['sticky_btn_show'], 1 ); ?>> 表示する</label>
                        </th>
                        <td>
                            <p class="description">スクロールに追従するボタンを表示するかどうかを選択してください。</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">ボタンの数</th>
                        <td>
                            <fieldset>
                                <label style="margin-right:20px;">
                                    <input type="radio" name="sticky_btn_count" value="1" id="btn_count_1"
                                        <?php checked( $opts['sticky_btn_count'], '1' ); ?>>
                                    1つ表示
                                </label>
                                <label>
                                    <input type="radio" name="sticky_btn_count" value="2" id="btn_count_2"
                                        <?php checked( $opts['sticky_btn_count'], '2' ); ?>>
                                    2つ表示
                                </label>
                            </fieldset>
                            <p class="description">表示するボタンの数を選択してください。</p>
                        </td>
                    </tr>
                    <tr><td colspan="2" style="padding:0;border-bottom:1px solid #f4f2f0;"></td></tr>

                    <!-- Button 1 -->
                    <tr>
                        <th scope="row" colspan="2" style="background:#f9f5f0;padding:12px 15px;">
                            <strong>ボタン 1 の設定</strong>
                        </th>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="sticky_btn_1_line1">テキスト行1</label>
                        </th>
                        <td>
                            <input type="text" name="sticky_btn_1_line1" id="sticky_btn_1_line1"
                                   class="regular-text"
                                   value="<?php echo esc_attr( $opts['sticky_btn_1_line1'] ?? '無料体験&#10;レッスン' ); ?>"
                                   placeholder="無料体験&#10;レッスン">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="sticky_btn_1_url">リンク先URL</label>
                        </th>
                        <td>
                            <input type="url" name="sticky_btn_1_url" id="sticky_btn_1_url"
                                   class="regular-text"
                                   value="<?php echo esc_attr( $opts['sticky_btn_1_url'] ?? '/contact' ); ?>"
                                   placeholder="/contact">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="sticky_btn_1_color">背景色</label>
                        </th>
                        <td>
                            <input type="text" name="sticky_btn_1_color" id="sticky_btn_1_color"
                                   class="riff-color-picker"
                                   value="<?php echo esc_attr( $opts['sticky_btn_1_color'] ?? '#6b4226' ); ?>"
                                   data-default-color="#6b4226">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="sticky_btn_1_text_color">テキスト色</label>
                        </th>
                        <td>
                            <input type="text" name="sticky_btn_1_text_color" id="sticky_btn_1_text_color"
                                   class="riff-color-picker"
                                   value="<?php echo esc_attr( $opts['sticky_btn_1_text_color'] ?? '#ffffff' ); ?>"
                                   data-default-color="#ffffff">
                        </td>
                    </tr>
                    <tr><td colspan="2" style="padding:0;border-bottom:1px solid #f4f2f0;"></td></tr>

                    <!-- Button 2 -->
                    <tr>
                        <th scope="row" colspan="2" style="background:#f9f5f0;padding:12px 15px;">
                            <strong>ボタン 2 の設定</strong> <span style="color:#999;font-weight:normal;font-size:12px;">（ボタン2つ表示時のみ使用）</span>
                        </th>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="sticky_btn_2_line1">テキスト行1</label>
                        </th>
                        <td>
                            <input type="text" name="sticky_btn_2_line1" id="sticky_btn_2_line1"
                                   class="regular-text"
                                   value="<?php echo esc_attr( $opts['sticky_btn_2_line1'] ?? 'ご予約&#10;相談' ); ?>"
                                   placeholder="ご予約&#10;相談">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="sticky_btn_2_url">リンク先URL</label>
                        </th>
                        <td>
                            <input type="url" name="sticky_btn_2_url" id="sticky_btn_2_url"
                                   class="regular-text"
                                   value="<?php echo esc_attr( $opts['sticky_btn_2_url'] ?? '/booking' ); ?>"
                                   placeholder="/booking">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="sticky_btn_2_color">背景色</label>
                        </th>
                        <td>
                            <input type="text" name="sticky_btn_2_color" id="sticky_btn_2_color"
                                   class="riff-color-picker"
                                   value="<?php echo esc_attr( $opts['sticky_btn_2_color'] ?? '#a07050' ); ?>"
                                   data-default-color="#a07050">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="sticky_btn_2_text_color">テキスト色</label>
                        </th>
                        <td>
                            <input type="text" name="sticky_btn_2_text_color" id="sticky_btn_2_text_color"
                                   class="riff-color-picker"
                                   value="<?php echo esc_attr( $opts['sticky_btn_2_text_color'] ?? '#ffffff' ); ?>"
                                   data-default-color="#ffffff">
                        </td>
                    </tr>
                </table>

                <?php elseif ( $tab === 'seo' ) : ?>
                <!-- ======= SEO & ビジネス情報設定 ======= -->
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row" colspan="2" style="background:#f9f5f0;padding:12px 15px;">
                            <strong>ビジネス情報（LocalBusiness Schema用）</strong>
                        </th>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="business_name">ビジネス名</label>
                        </th>
                        <td>
                            <input type="text" name="business_name" id="business_name"
                                   class="regular-text"
                                   value="<?php echo esc_attr( $opts['business_name'] ?? '' ); ?>"
                                   placeholder="LUMIRA">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="business_address">所在地</label>
                        </th>
                        <td>
                            <input type="text" name="business_address" id="business_address"
                                   class="regular-text"
                                   value="<?php echo esc_attr( $opts['business_address'] ?? '' ); ?>"
                                   placeholder="東京都渋谷区...">
                            <p class="description">LocalBusiness スキーマに使用されます。</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="business_phone">電話番号</label>
                        </th>
                        <td>
                            <input type="tel" name="business_phone" id="business_phone"
                                   class="regular-text"
                                   value="<?php echo esc_attr( $opts['business_phone'] ?? '' ); ?>"
                                   placeholder="03-xxxx-xxxx">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="business_email">メールアドレス</label>
                        </th>
                        <td>
                            <input type="email" name="business_email" id="business_email"
                                   class="regular-text"
                                   value="<?php echo esc_attr( $opts['business_email'] ?? '' ); ?>"
                                   placeholder="info@example.com">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="business_hours">営業時間（JSON形式）</label>
                        </th>
                        <td>
                            <textarea name="business_hours" id="business_hours"
                                      class="large-text code" rows="5"
                                      style="font-family:monospace;font-size:12px;"><?php echo esc_textarea( $opts['business_hours'] ?? '' ); ?></textarea>
                            <p class="description">例: [{"@type":"OpeningHoursSpecification","dayOfWeek":"Monday","opens":"10:00","closes":"20:00"}]</p>
                        </td>
                    </tr>
                </table>

                <?php else : ?>
                <!-- ======= 近日公開 ======= -->
                <div class="riff-coming-soon">
                    <div class="riff-coming-soon__inner">
                        <span class="riff-coming-soon__icon">🚧</span>
                        <p class="riff-coming-soon__text">この設定は近日公開予定です。</p>
                    </div>
                </div>
                <?php endif; ?>

            </div><!-- .riff-tab-content -->

            <p class="submit">
                <button type="submit" class="button riff-save-btn">変更を保存</button>
            </p>

        </form>
    </div><!-- .wrap -->

    <script>
    jQuery(function($) {
        // Color pickers
        $('.riff-color-picker').wpColorPicker();

        // Overlay opacity range
        var range = document.getElementById('overlay-range');
        var rangeVal = document.getElementById('overlay-range-val');
        if (range && rangeVal) {
            range.addEventListener('input', function() { rangeVal.textContent = this.value; });
        }

        // Slide interval range
        var intervalRange = document.getElementById('slide-interval-range');
        var intervalVal = document.getElementById('slide-interval-val');
        if (intervalRange && intervalVal) {
            intervalRange.addEventListener('input', function() { intervalVal.textContent = this.value; });
        }

        // Background type toggle
        document.querySelectorAll('input[name="hero_bg_type"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                var isVideo = this.value === 'video';
                var slideRows   = document.querySelectorAll('#row-slideshow, #row-interval');
                var videoRows   = document.querySelectorAll('#row-video, #row-video-interval');
                slideRows.forEach(function(r) { r.style.display = isVideo ? 'none' : ''; });
                videoRows.forEach(function(r) { r.style.display = isVideo ? '' : 'none'; });
            });
        });

        // Generic media upload handler
        function setupMediaUpload(btnSelector) {
            document.querySelectorAll(btnSelector).forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    var previewId = this.dataset.preview;
                    var inputId   = this.dataset.input;
                    var frame = wp.media({
                        title: '画像を選択',
                        button: { text: 'この画像を使用' },
                        multiple: false
                    });
                    frame.on('select', function() {
                        var att = frame.state().get('selection').first().toJSON();
                        document.getElementById(inputId).value = att.id;
                        var preview = document.getElementById(previewId);
                        var img = preview.querySelector('img');
                        if (!img) {
                            img = document.createElement('img');
                            img.alt = '';
                            img.style.maxWidth = '100px';
                            img.style.height = '68px';
                            img.style.objectFit = 'cover';
                            img.style.borderRadius = '4px';
                            img.style.display = 'block';
                            preview.innerHTML = '';
                            preview.appendChild(img);
                        }
                        img.src = (att.sizes && att.sizes.thumbnail) ? att.sizes.thumbnail.url : att.url;
                    });
                    frame.open();
                });
            });
        }

        // Generic remove handler
        function setupMediaRemove(btnSelector) {
            document.querySelectorAll(btnSelector).forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    document.getElementById(this.dataset.input).value = '';
                    document.getElementById(this.dataset.preview).innerHTML = '<span class="riff-no-image">未設定</span>';
                });
            });
        }

        setupMediaUpload('.riff-img-upload');
        setupMediaRemove('.riff-img-remove');

        // Video interval range
        var videoIntervalRange = document.getElementById('video-interval-range');
        var videoIntervalVal   = document.getElementById('video-interval-val');
        if (videoIntervalRange && videoIntervalVal) {
            videoIntervalRange.addEventListener('input', function() { videoIntervalVal.textContent = this.value; });
        }

        // Video select buttons (generic, uses data-input / data-preview)
        document.querySelectorAll('.riff-video-select').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var inputId   = this.dataset.input;
                var previewId = this.dataset.preview;
                var frame = wp.media({
                    title: '動画を選択',
                    button: { text: 'この動画を使用' },
                    multiple: false,
                    library: { type: 'video' }
                });
                frame.on('select', function() {
                    var att = frame.state().get('selection').first().toJSON();
                    var url = att.url || '';
                    document.getElementById(inputId).value = url;
                    var preview = document.getElementById(previewId);
                    if (preview && url) {
                        preview.innerHTML = '<video style="max-width:200px;border-radius:4px;display:block;" controls muted><source src="' + url + '"></video>';
                    }
                });
                frame.open();
            });
        });

        // Video clear buttons
        document.querySelectorAll('.riff-video-clear').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var inputId   = this.dataset.input;
                var previewId = this.dataset.preview;
                document.getElementById(inputId).value = '';
                var preview = document.getElementById(previewId);
                if (preview) preview.innerHTML = '';
            });
        });
    });
    </script>
    <?php
}
