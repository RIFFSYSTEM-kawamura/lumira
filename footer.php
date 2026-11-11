<?php
$opts          = lumira_get_options();
$logo_text     = $opts['header_logo_text'] ?? 'LUMIRA';
$logo_type     = $opts['header_logo_type'] ?? 'text';
$logo_image_id = $opts['header_logo_image_id'] ?? 0;

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
     SITE FOOTER
     ====================================================== -->
<footer class="site-footer" role="contentinfo">
    <div class="site-footer__inner">

        <div class="site-footer__top">
            <div class="site-footer__brand">
                <div class="site-footer__logo">
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-footer__logo-link">
                        <?php
                        if ( 'image' === $logo_type && $logo_image_id ) {
                            $logo_url = wp_get_attachment_image_url( $logo_image_id, 'full' );
                            if ( $logo_url ) {
                                echo '<img src="' . esc_url( $logo_url ) . '" alt="' . esc_attr( $logo_text ) . '" style="height:auto;max-width:200px;">';
                            } else {
                                echo '<span class="site-footer__logo-text">' . esc_html( $logo_text ) . '</span><span class="site-footer__logo-sub">Interior Design & Renovation</span>';
                            }
                        } else {
                            echo '<span class="site-footer__logo-text">' . esc_html( $logo_text ) . '</span><span class="site-footer__logo-sub">Interior Design & Renovation</span>';
                        }
                        ?>
                    </a>
                </div>
                <p class="site-footer__tagline">
                    空間を、光で彩る。<br>
                    <span>Creating Spaces That Inspire Living.</span>
                </p>
                <ul class="site-footer__sns">
                    <?php foreach ( [ 'line', 'x', 'instagram', 'facebook', 'threads', 'youtube' ] as $platform ) : ?>
                        <?php if ( $sns_data[ $platform ]['show'] && isset( $sns_icons[ $platform ] ) ) : ?>
                        <li><a href="<?php echo esc_url( $sns_data[ $platform ]['url'] ); ?>" class="site-footer__sns-link site-footer__sns-link--<?php echo esc_attr( $platform ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( ucfirst( $platform ) ); ?>"><?php echo $sns_icons[ $platform ]; ?></a></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>

            <nav class="site-footer__nav" aria-label="フッターナビゲーション">
                <?php
                if ( has_nav_menu( 'footer' ) ) :
                    wp_nav_menu( [
                        'theme_location' => 'footer',
                        'menu_class'     => 'site-footer__nav-list',
                        'container'      => false,
                        'depth'          => 1,
                    ] );
                else :
                ?>
                <ul class="site-footer__nav-list">
                    <li><a href="#about">About Us</a></li>
                    <li><a href="#works">Works</a></li>
                    <li><a href="#service">Service</a></li>
                    <li><a href="#recruit">Recruit</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/contact' ) ); ?>">Contact</a></li>
                </ul>
                <?php endif; ?>
            </nav>
        </div>

        <div class="site-footer__bottom">
            <p class="site-footer__copy">
                &copy; <?php echo date( 'Y' ); ?> <?php echo esc_html( $logo_text ); ?>. All Rights Reserved.
            </p>
            <p class="site-footer__credit">
                Designed & Developed by <a href="https://riff-system.com" target="_blank" rel="noopener noreferrer">RIFF SYSTEM</a>
            </p>
        </div>

    </div><!-- .site-footer__inner -->
</footer><!-- .site-footer -->

</div><!-- [/data-barba="container"] -->
</div><!-- [/data-barba="wrapper"] -->

<!-- Page Transition Overlay -->
<div class="page-transition" id="page-transition" aria-hidden="true">
    <div class="page-transition__fill"></div>
</div>

<script>
(function() {
    // Init WebGL displacement ripple (if Three.js available)
    function initNavWebGL() {
        var canvas = document.getElementById('nav-webgl-canvas');
        var nav    = document.getElementById('mobile-menu');
        <?php
        $nav_bg_id = $opts['nav_bg_image_id'] ?? 0;
        $nav_bg_url = $nav_bg_id ? wp_get_attachment_image_url( $nav_bg_id, 'full' ) : '';
        ?>
        var bgImageUrl = '<?php echo esc_js( $nav_bg_url ); ?>';
        console.log('[NavWebGL] Canvas:', canvas, 'Nav:', nav, 'NavBgMesh:', typeof window.NavBgMesh, 'BgUrl:', bgImageUrl);
        if (!canvas) { console.warn('[NavWebGL] Canvas not found'); return; }
        if (!nav)    { console.warn('[NavWebGL] Nav element not found'); return; }
        if (typeof window.NavBgMesh === 'undefined') { console.warn('[NavWebGL] NavBgMesh not defined'); return; }
        var mesh = new window.NavBgMesh(canvas, nav, bgImageUrl);
        console.log('[NavWebGL] Initializing mesh...');
        mesh.init();
        console.log('[NavWebGL] Mesh initialized successfully');
        window._navWebGL = { instance: mesh, active: false };
    }

    function initNav() {
        var trigger = document.getElementById('hamburger');
        var nav     = document.getElementById('mobile-menu');
        if (!trigger || !nav) return;
        if (trigger.dataset.bound) return;
        trigger.dataset.bound = '1';

        var navBg   = nav.querySelector('[data-nav="bg"]');
        var overlay = nav.querySelector('[data-nav="overlay"]');
        var isOpen  = false;
        var webgl   = window._navWebGL;

        function openNav() {
            if (isOpen) return;
            isOpen = true;
            trigger.setAttribute('aria-expanded', 'true');
            nav.setAttribute('aria-hidden', 'false');
            nav.classList.add('-open');
            document.body.style.overflow = 'hidden';
            document.body.classList.add('menu-is-open');
            if (window.riffLenis && window.riffLenis.stop) window.riffLenis.stop();
            console.log('[Nav] Opening menu. WebGL:', webgl);
            if (webgl && webgl.instance) {
                console.log('[Nav] Starting WebGL animation');
                webgl.active = true;
                webgl.instance.start();
            } else {
                console.warn('[Nav] WebGL instance not available');
            }

            if (typeof gsap !== 'undefined') {
                var enTexts   = Array.from(nav.querySelectorAll('[data-nav="link-en-text"]'));
                var jaTexts   = Array.from(nav.querySelectorAll('[data-nav="link-ja-text"]'));
                var snsIcons  = Array.from(nav.querySelectorAll('[data-nav="sns-icon"]'));
                var linkLines = Array.from(nav.querySelectorAll('[data-nav="link-line"]'));
                var ease      = 'power3.out';

                // Initial states
                gsap.set(nav, { opacity: 0 });
                if (navBg)    gsap.set(navBg,   { clipPath: 'inset(100% 0% 0% 0%)' });
                if (overlay)  gsap.set(overlay,  { opacity: 0 });
                if (enTexts.length)  gsap.set(enTexts,  { yPercent: 108 });
                if (jaTexts.length)  gsap.set(jaTexts,  { yPercent: 108 });
                if (snsIcons.length) gsap.set(snsIcons, { yPercent: 108 });
                if (linkLines.length) gsap.set(linkLines, { scaleX: 0, transformOrigin: 'left' });

                // Animate open
                gsap.to(nav,      { opacity: 1, duration: 0.3, ease: 'none' });
                if (navBg)  gsap.to(navBg,  { clipPath: 'inset(0% 0% 0% 0%)', duration: 1.2, ease: ease });
                /* Overlay disabled – show WebGL background instead */
                gsap.to(enTexts,  { yPercent: 0, duration: 1.0, stagger: 0.08, ease: ease, delay: 0.22 });
                gsap.to(jaTexts,  { yPercent: 0, duration: 1.0, stagger: 0.08, ease: ease, delay: 0.3  });
                gsap.to(snsIcons, { yPercent: 0, duration: 0.8, stagger: 0.06, ease: ease, delay: 0.5  });
                if (linkLines.length) gsap.to(linkLines, { scaleX: 1, duration: 0.7, ease: ease, delay: 0.65 });
            }
        }

        function closeNav() {
            if (!isOpen) return;
            isOpen = false;
            trigger.setAttribute('aria-expanded', 'false');
            document.body.classList.remove('menu-is-open');
            if (webgl && webgl.instance) { webgl.active = false; webgl.instance.stop(); }

            if (typeof gsap !== 'undefined' && navBg) {
                if (overlay)  gsap.to(overlay,  { opacity: 0, duration: 0.8,  ease: 'none' });
                gsap.to(navBg, {
                    clipPath: 'inset(0% 0% 100% 0%)',
                    duration: 1.0,
                    ease: 'power3.inOut',
                    onComplete: function() {
                        nav.setAttribute('aria-hidden', 'true');
                        nav.classList.remove('-open');
                        document.body.style.overflow = '';
                        if (window.riffLenis && window.riffLenis.start) window.riffLenis.start();
                        gsap.set(nav, { opacity: 0 });
                        if (navBg)   gsap.set(navBg,   { clipPath: 'inset(100% 0% 0% 0%)' });
                        if (overlay) gsap.set(overlay,  { opacity: 0 });
                        // Reset text positions
                        var enTexts  = Array.from(nav.querySelectorAll('[data-nav="link-en-text"]'));
                        var jaTexts  = Array.from(nav.querySelectorAll('[data-nav="link-ja-text"]'));
                        var snsIcons = Array.from(nav.querySelectorAll('[data-nav="sns-icon"]'));
                        if (enTexts.length)  gsap.set(enTexts,  { yPercent: 108 });
                        if (jaTexts.length)  gsap.set(jaTexts,  { yPercent: 108 });
                        if (snsIcons.length) gsap.set(snsIcons, { yPercent: 108 });
                    }
                });
            } else {
                nav.setAttribute('aria-hidden', 'true');
                nav.classList.remove('-open');
                document.body.style.overflow = '';
                if (window.riffLenis) window.riffLenis.start();
            }
        }

        trigger.addEventListener('click', function() { isOpen ? closeNav() : openNav(); });

        nav.querySelectorAll('[data-nav="link"]').forEach(function(link) {
            link.addEventListener('click', closeNav);
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && isOpen) closeNav();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initNavWebGL();
            initNav();
        });
    } else {
        initNavWebGL();
        initNav();
    }
})();

/* -----------------------------------------------------------
   Standalone hero media switcher – works without GSAP/Lenis
   ----------------------------------------------------------- */
(function() {
    function initMediaSwitchers() {
        // Video reel
        var reel = document.querySelector('.hero__video-reel');
        if (reel) {
            var videos = reel.querySelectorAll('.hero__video-slide');
            if (videos.length >= 2) {
                var vInterval = parseInt(reel.dataset.interval, 10) || 7000;
                var vCurrent = 0;
                // Ensure all videos are playing
                videos.forEach(function(v) { v.play && v.play().catch(function(){}); });
                setInterval(function() {
                    videos[vCurrent].classList.remove('is-active');
                    vCurrent = (vCurrent + 1) % videos.length;
                    videos[vCurrent].classList.add('is-active');
                    videos[vCurrent].play && videos[vCurrent].play().catch(function(){});
                }, vInterval);
            }
        }

        // Image slideshow
        var slideshow = document.querySelector('.hero__slideshow');
        if (slideshow) {
            var slides = slideshow.querySelectorAll('.hero__slide');
            if (slides.length >= 2) {
                var sInterval = parseInt(slideshow.dataset.interval, 10) || 5000;
                var sCurrent = 0;
                setInterval(function() {
                    slides[sCurrent].classList.remove('is-active');
                    sCurrent = (sCurrent + 1) % slides.length;
                    slides[sCurrent].classList.add('is-active');
                }, sInterval);
            }
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMediaSwitchers);
    } else {
        initMediaSwitchers();
    }
})();
</script>
<?php wp_footer(); ?>
</body>
</html>
