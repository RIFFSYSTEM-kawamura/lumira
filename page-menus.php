<?php
/**
 * Template Name: Menus
 * Description: Menu detail page template
 * LUMIRA WordPress Theme
 */
get_header();
?>

<!-- ======================================================
     HERO SECTION
     ====================================================== -->
<?php
$post_id = get_the_ID();
$page_hero_img_id = intval( get_post_meta( $post_id, '_lumira_page_hero_image', true ) );
$page_hero_subtitle = get_post_meta( $post_id, '_lumira_page_hero_subtitle', true );
$menu_hero_img_url = '';

if ( $page_hero_img_id ) {
    $menu_hero_img_url = wp_get_attachment_image_url( $page_hero_img_id, 'lumira-hero' ) ?: wp_get_attachment_image_url( $page_hero_img_id, 'full' );
} else {
    $opts = lumira_get_options();
    $menu_hero_img_id = intval( $opts['menu_hero_image_id'] ?? 0 );
    $menu_hero_img_url = $menu_hero_img_id ? wp_get_attachment_image_url( $menu_hero_img_id, 'lumira-hero' ) ?: wp_get_attachment_image_url( $menu_hero_img_id, 'full' ) : '';
}
?>
<?php if ( $menu_hero_img_url ) : ?>
<section class="page-hero" style="background-image:url('<?php echo esc_url( $menu_hero_img_url ); ?>');" aria-label="<?php echo esc_attr( get_the_title() ); ?>">
  <div class="page-hero__overlay"></div>
  <div class="page-hero__content">
    <div class="container">
      <h1 class="page-hero__title"><?php echo esc_html( get_the_title() ); ?></h1>
      <?php if ( $page_hero_subtitle ) : ?>
      <p class="page-hero__subtitle"><?php echo wp_kses_post( $page_hero_subtitle ); ?></p>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php else : ?>
<section class="page-hero page-hero--no-image">
  <div class="container">
    <h1 class="page-hero__title"><?php echo esc_html( get_the_title() ); ?></h1>
    <?php if ( $page_hero_subtitle ) : ?>
    <p class="page-hero__subtitle"><?php echo wp_kses_post( $page_hero_subtitle ); ?></p>
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
     MAIN CONTENT
     ====================================================== -->
<main id="main-content" class="page-main">
  <div class="container">

    <!-- ======================================================
         FACIAL MENU SECTION
         ====================================================== -->
    <section class="menu-section" id="menu-facial-menu">
      <div class="menu-section__header">
        <h2 class="menu-section__title">Facial Menu</h2>
        <p class="menu-section__subtitle">フェイシャルメニュー</p>
      </div>

      <div class="menu-grid">
        <div class="menu-card">
          <figure class="menu-card__image">
            <img src="https://placehold.co/400x300?text=Facial+Menu+1" alt="フェイシャルメニュー 1" width="400" height="300" loading="lazy">
          </figure>
          <h3 class="menu-card__title">美容液ケアフェイシャル</h3>
          <p class="menu-card__description">最高級の美容液を使用した贅沢なフェイシャルトリートメント。肌の奥深くまで浸透する栄養が、あなたの素肌を輝かせます。</p>
          <a href="#" class="btn btn--primary">詳細はこちら</a>
        </div>

        <div class="menu-card">
          <figure class="menu-card__image">
            <img src="https://placehold.co/400x300?text=Facial+Menu+2" alt="フェイシャルメニュー 2" width="400" height="300" loading="lazy">
          </figure>
          <h3 class="menu-card__title">エイジングケアフェイシャル</h3>
          <p class="menu-card__description">年齢肌に特化したスペシャルフェイシャル。ハリと艶を取り戻し、若々しい表情へと導きます。</p>
          <a href="#" class="btn btn--primary">詳細はこちら</a>
        </div>

        <div class="menu-card">
          <figure class="menu-card__image">
            <img src="https://placehold.co/400x300?text=Facial+Menu+3" alt="フェイシャルメニュー 3" width="400" height="300" loading="lazy">
          </figure>
          <h3 class="menu-card__title">毛穴クリーニング</h3>
          <p class="menu-card__description">最新の毛穴吸引技術で、頑固な汚れと余分な皮脂を徹底除去。透明感のある肌を実現します。</p>
          <a href="#" class="btn btn--primary">詳細はこちら</a>
        </div>

        <div class="menu-card">
          <figure class="menu-card__image">
            <img src="https://placehold.co/400x300?text=Facial+Menu+4" alt="フェイシャルメニュー 4" width="400" height="300" loading="lazy">
          </figure>
          <h3 class="menu-card__title">クリスタルフェイシャル</h3>
          <p class="menu-card__description">天然クリスタルを使用した高級フェイシャル。肌本来の美しさを引き出す最高峰のトリートメントです。</p>
          <a href="#" class="btn btn--primary">詳細はこちら</a>
        </div>
      </div>
    </section>

    <!-- ======================================================
         BODY MENU SECTION
         ====================================================== -->
    <section class="menu-section" id="menu-body-menu">
      <div class="menu-section__header">
        <h2 class="menu-section__title">Body Menu</h2>
        <p class="menu-section__subtitle">ボディメニュー</p>
      </div>

      <div class="menu-grid">
        <div class="menu-card">
          <figure class="menu-card__image">
            <img src="https://placehold.co/400x300?text=Body+Menu+1" alt="ボディメニュー 1" width="400" height="300" loading="lazy">
          </figure>
          <h3 class="menu-card__title">全身美痩身コース</h3>
          <p class="menu-card__description">話題の痩身トリートメントで、理想のボディラインへ。セルライト除去と脂肪燃焼を同時実現。</p>
          <a href="#" class="btn btn--primary">詳細はこちら</a>
        </div>

        <div class="menu-card">
          <figure class="menu-card__image">
            <img src="https://placehold.co/400x300?text=Body+Menu+2" alt="ボディメニュー 2" width="400" height="300" loading="lazy">
          </figure>
          <h3 class="menu-card__title">デトックスボディケア</h3>
          <p class="menu-card__description">体内の毒素を排出し、本来の輝きを取り戻す。疲労回復と美肌効果が同時に得られるコース。</p>
          <a href="#" class="btn btn--primary">詳細はこちら</a>
        </div>

        <div class="menu-card">
          <figure class="menu-card__image">
            <img src="https://placehold.co/400x300?text=Body+Menu+3" alt="ボディメニュー 3" width="400" height="300" loading="lazy">
          </figure>
          <h3 class="menu-card__title">美脚ブースト</h3>
          <p class="menu-card__description">脚専門の美容トリートメント。むくみ解消と美脚効果で、憧れのスタイルを実現します。</p>
          <a href="#" class="btn btn--primary">詳細はこちら</a>
        </div>

        <div class="menu-card">
          <figure class="menu-card__image">
            <img src="https://placehold.co/400x300?text=Body+Menu+4" alt="ボディメニュー 4" width="400" height="300" loading="lazy">
          </figure>
          <h3 class="menu-card__title">ヒップアップケア</h3>
          <p class="menu-card__description">ヒップラインを美しく整える専門トリートメント。ツヤと弾力を取り戻し、自信をもたらします。</p>
          <a href="#" class="btn btn--primary">詳細はこちら</a>
        </div>
      </div>
    </section>

  </div>
</main>

<?php
get_footer();
