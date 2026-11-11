<?php
/**
 * Template Name: About
 * Description: About page template
 * LUMIRA WordPress Theme
 */
get_header();
?>

<!-- ======================================================
     HERO SECTION
     ====================================================== -->
<?php
$post_id = get_the_ID();
// ページ固有のメタデータをチェック
$page_hero_img_id = intval( get_post_meta( $post_id, '_lumira_page_hero_image', true ) );
$page_hero_subtitle = get_post_meta( $post_id, '_lumira_page_hero_subtitle', true );
$about_hero_img_url = '';

if ( $page_hero_img_id ) {
    // ページ固有の画像がある場合はそちらを使用
    $about_hero_img_url = wp_get_attachment_image_url( $page_hero_img_id, 'lumira-hero' ) ?: wp_get_attachment_image_url( $page_hero_img_id, 'full' );
} else {
    // ない場合はテーマオプションから取得
    $opts = lumira_get_options();
    $about_hero_img_id = intval( $opts['about_hero_image_id'] ?? 0 );
    $about_hero_img_url = $about_hero_img_id ? wp_get_attachment_image_url( $about_hero_img_id, 'lumira-hero' ) ?: wp_get_attachment_image_url( $about_hero_img_id, 'full' ) : '';
}
?>
<?php if ( $about_hero_img_url ) : ?>
<section class="page-hero" style="background-image:url('<?php echo esc_url( $about_hero_img_url ); ?>');" aria-label="<?php echo esc_attr( get_the_title() ); ?>">
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
         CONCEPT SECTION
         ====================================================== -->
    <section class="about-section about-section--concept">
      <div class="about-section__header">
        <h2 class="about-section__title">Concept</h2>
        <p class="about-section__subtitle">コンセプト</p>
      </div>

      <div class="about-section__content">
        <div class="about-section__text">
          <h3 class="about-section__heading">心も喜ぶ最高の癒しを。</h3>
          <p class="about-section__description">
            私たちは「ただ美しくなる」ことだけでなく、訪れるすべてのお客さまが心身ともにリラックスできる空間を大切にしています。最高級の施術技術と、こころからのおもてなしで、あなたの大切な時間をサポートします。
          </p>
          <p class="about-section__description">
            LUMIRAは、空間を光で彩り、人生を豊かに照らす場所。美しさと癒しが一つになった、特別なエステティックサロンです。
          </p>
        </div>

        <div class="about-section__images">
          <figure class="about-section__image">
            <img src="https://placehold.co/500x350/D4A5A5/ffffff?text=Concept+Image" alt="コンセプト イメージ 1" width="500" height="350" loading="lazy">
          </figure>
          <figure class="about-section__image">
            <img src="https://placehold.co/500x350/C9B4A8/ffffff?text=Concept+Image" alt="コンセプト イメージ 2" width="500" height="350" loading="lazy">
          </figure>
        </div>
      </div>
    </section>

    <!-- ======================================================
         AMENITY & ROOM SECTION
         ====================================================== -->
    <section class="about-section about-section--amenity">
      <div class="about-section__header">
        <h2 class="about-section__title">Amenity &amp; Room</h2>
        <p class="about-section__subtitle">アメニティ&ルーム</p>
      </div>

      <div class="about-section__content">
        <div class="about-section__text">
          <h3 class="about-section__heading">上質な空間で贅沢な時間を。</h3>
          <p class="about-section__description">
            LUMIRAのプライベートルームは、最高のくつろぎを実現するために設計されています。厳選されたアメニティと、心地よい空間デザインが、施術の効果をさらに高めます。
          </p>
          <p class="about-section__description">
            照明、香り、音にこだわった環境で、日常の喧騒から離れた贅沢な時間をお過ごしください。あなた専用の癒しの空間が、お待ちしています。
          </p>
        </div>

        <div class="about-section__images">
          <figure class="about-section__image">
            <img src="https://placehold.co/400x300/E8D5C4/ffffff?text=Room+Image" alt="ルーム イメージ 1" width="400" height="300" loading="lazy">
          </figure>
          <figure class="about-section__image">
            <img src="https://placehold.co/400x300/D9C5B8/ffffff?text=Room+Image" alt="ルーム イメージ 2" width="400" height="300" loading="lazy">
          </figure>
          <figure class="about-section__image">
            <img src="https://placehold.co/400x300/DCC5B3/ffffff?text=Room+Image" alt="ルーム イメージ 3" width="400" height="300" loading="lazy">
          </figure>
          <figure class="about-section__image">
            <img src="https://placehold.co/400x300/E5D0C0/ffffff?text=Room+Image" alt="ルーム イメージ 4" width="400" height="300" loading="lazy">
          </figure>
        </div>
      </div>
    </section>

    <!-- ======================================================
         ACCESS SECTION
         ====================================================== -->
    <section class="about-section about-section--access">
      <div class="about-section__header">
        <h2 class="about-section__title">Access</h2>
        <p class="about-section__subtitle">アクセス</p>
      </div>

      <div class="about-section__content">
        <div class="about-section__text">
          <h3 class="about-section__heading">時間を忘れさせる場所へ。</h3>
          <p class="about-section__description">
            好立地で訪れやすい場所に位置するLUMIRA。駅からのアクセスも便利で、日常の中で特別な時間をお手軽に体験いただけます。
          </p>
          <p class="about-section__description">
            仕事帰りや休日の時間に、ふらりと立ち寄れるエステティックサロン。あなたのライフスタイルに寄り添う場所としてお待ちしています。
          </p>
        </div>

        <!-- 地図セクション -->
        <div class="map-section">
            <div class="map-container">
                <!-- Google Maps埋め込み用エリア -->
                <div class="map-embed" id="google-map">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d6474.858197700848!2d139.6883257!3d35.7648345!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x6018ed2a7f5ebc97%3A0x6d893e92edb81366!2z44CSMTc0LTAwNjMg5p2x5Lqs6YO95p2_5qmL5Yy65YmN6YeO55S677yS5LiB55uu77yR77yZ4oiS77yR77yUIOOCqOODq-ODnuODvOODjuW4uOebpOWPsOODpOOCsOODog!5e0!3m2!1sja!2sjp!4v1771812281310!5m2!1sja!2sjp" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>

                <!-- 大きな地図で表示するボタン -->
                <div class="map-button-container">
                    <a href="https://share.google/s1WbLk81quQdh4k0m" class="map-button" target="_blank">大きな地図で表示する</a>
                </div>
            </div>
        </div>

        <div class="about-section__info">
          <h3>店舗情報</h3>
          <dl class="about-info-list">
            <dt>店舗名</dt>
            <dd>LUMIRA by HEÅL Building</dd>
            <dt>所在地</dt>
            <dd>大阪府大阪市北区◯◯◯◯◯◯◯◯◯◯◯◯◯◯◯◯◯◯</dd>
            <dt>営業時間</dt>
            <dd>平日 11:00〜21:00 / 土日祝 10:00〜20:00</dd>
            <dt>定休日</dt>
            <dd>毎週月曜日・第2日曜日</dd>
            <dt>電話番号</dt>
            <dd><a href="tel:0612345678">06-1234-5678</a></dd>
          </dl>
        </div>
      </div>
    </section>

    <!-- ======================================================
         CTA SECTION
         ====================================================== -->
    <section class="about-section about-section--cta">
      <div class="about-section__cta-content">
        <h2>特別な時間を体験してみませんか？</h2>
        <p>LUMIRAでは、初めてのお客さまに向けて特別なプランをご用意しております。</p>
        <a href="<?php echo esc_url( get_post_type_archive_link( 'campaign' ) ); ?>" class="btn btn--primary">キャンペーン一覧を見る</a>
      </div>
    </section>

  </div>
</main>

<?php
get_footer();
