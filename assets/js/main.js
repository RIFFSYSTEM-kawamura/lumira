/**
 * LUMIRA WordPress Theme - main.js
 * GSAP 3 + Lenis + Barba.js
 * RIFF SYSTEM
 */

(function () {
  'use strict';

  /* ==========================================================================
     GSAP Plugin Registration – defensive guard
     ========================================================================== */
  if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') {
    // GSAP CDN failed – make all hidden hero elements visible immediately
    document.addEventListener('DOMContentLoaded', function () {
      var hero = document.querySelector('.hero');
      if (!hero) return;
      var show = ['.hero__title-jp', '.hero__subtitle', '.hero__news'];
      show.forEach(function (sel) {
        hero.querySelectorAll(sel).forEach(function (el) {
          el.style.cssText += 'opacity:1!important;transform:none!important;clip-path:none!important;';
        });
      });
    });
    return;
  }

  gsap.registerPlugin(ScrollTrigger);

  /* ==========================================================================
     Memory Management - Store interval IDs for cleanup
     ========================================================================== */
  const activeIntervals = {
    slideshow: null,
    videoReel: null,
    newsSlide: null,
  };

  /* ==========================================================================
     1. Lenis Smooth Scroll
     ========================================================================== */
  let lenis;

  function initLenis() {
    if (typeof Lenis === 'undefined') {
      console.warn('Lenis library not loaded');
      return;
    }
    lenis = new Lenis({
      duration: 1.4,
      easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
      orientation: 'vertical',
      smoothWheel: true,
      wheelMultiplier: 0.9,
      touchMultiplier: 1.5,
    });

    // Expose globally so footer.php script can stop/start it
    window.riffLenis = lenis;

    // Integrate with GSAP ticker
    gsap.ticker.add((time) => {
      lenis.raf(time * 1000);
    });

    gsap.ticker.lagSmoothing(0);

    // Update ScrollTrigger on scroll
    lenis.on('scroll', ScrollTrigger.update);

    // Smooth anchor link scrolling via Lenis
    document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
      anchor.addEventListener('click', (e) => {
        const hash = anchor.getAttribute('href');
        if (!hash || hash === '#') return;
        const target = document.querySelector(hash);
        if (!target) return;
        e.preventDefault();
        const headerH = parseInt(
          getComputedStyle(document.documentElement).getPropertyValue('--header-h')
        ) || 72;
        lenis.scrollTo(target, { offset: -headerH, duration: 1.4 });
      });
    });
  }

  /* ==========================================================================
     2. Custom Cursor
     ========================================================================== */
  function initCursor() {
    const cursor = document.getElementById('custom-cursor');
    if (!cursor || window.matchMedia('(hover: none)').matches) return;

    const outer = cursor.querySelector('.custom-cursor__outer');
    const inner = cursor.querySelector('.custom-cursor__inner');

    let mouseX = 0, mouseY = 0;
    let outerX = 0, outerY = 0;

    window.addEventListener('mousemove', (e) => {
      mouseX = e.clientX;
      mouseY = e.clientY;
      inner.style.left = mouseX + 'px';
      inner.style.top  = mouseY + 'px';
    });

    // Smooth outer cursor
    function tickCursor() {
      outerX += (mouseX - outerX) * 0.12;
      outerY += (mouseY - outerY) * 0.12;
      outer.style.left = outerX + 'px';
      outer.style.top  = outerY + 'px';
      requestAnimationFrame(tickCursor);
    }
    tickCursor();

    // Hover state
    document.querySelectorAll('a, button, [data-cursor-hover]').forEach((el) => {
      el.addEventListener('mouseenter', () => document.body.classList.add('cursor-hover'));
      el.addEventListener('mouseleave', () => document.body.classList.remove('cursor-hover'));
    });
  }

  /* ==========================================================================
     3. Loading Screen
     ========================================================================== */
  function initLoader() {
    return new Promise((resolve) => {
      const screen  = document.getElementById('loading-screen');
      const bar     = document.getElementById('loading-bar');
      const counter = document.getElementById('loading-counter');

      if (!screen) { resolve(); return; }

      document.body.classList.add('is-loading');

      let progress = 0;
      const duration = 1400; // ms
      const startTime = performance.now();

      function tick(now) {
        const elapsed = now - startTime;
        progress = Math.min(100, Math.floor((elapsed / duration) * 100));

        if (bar)     bar.style.width = progress + '%';
        if (counter) counter.textContent = progress;

        if (progress < 100) {
          requestAnimationFrame(tick);
        } else {
          // Finish
          gsap.to(screen, {
            opacity: 0,
            duration: 0.7,
            ease: 'power2.inOut',
            onComplete: () => {
              screen.classList.add('is-hidden');
              document.body.classList.remove('is-loading');
              resolve();
            },
          });
        }
      }

      requestAnimationFrame(tick);
    });
  }

  /* ==========================================================================
     4. Header Behavior
     ========================================================================== */
  function initHeader() {
    const header = document.getElementById('site-header');
    if (!header) {
      console.warn('Header not found');
      return;
    }
    console.log('Header initialized', header);

    let lastScrollY = 0;
    const SCROLL_THRESHOLD = 80;
    const HIDE_THRESHOLD   = 300;

    function onScroll() {
      const scrollY = lenis ? lenis.scroll : window.scrollY;

      // Scrolled state (background appears and fixed)
      if (scrollY > SCROLL_THRESHOLD) {
        header.classList.add('is-scrolled');
      } else {
        header.classList.remove('is-scrolled');
      }

      // Hide on scroll down / show on scroll up
      if (scrollY > HIDE_THRESHOLD) {
        if (scrollY > lastScrollY + 5) {
          header.classList.add('is-hidden');
        } else if (scrollY < lastScrollY - 5) {
          header.classList.remove('is-hidden');
        }
      } else {
        header.classList.remove('is-hidden');
      }

      lastScrollY = scrollY;
    }

    if (lenis) {
      lenis.on('scroll', ({ scroll }) => {
        onScroll();
      });
    } else {
      window.addEventListener('scroll', onScroll, { passive: true });
    }

    // Initial state
    onScroll();
  }

  /* ==========================================================================
     5. Mobile Menu
     ========================================================================== */
  function initMobileMenu() {
    const hamburger  = document.getElementById('hamburger');
    const mobileMenu = document.getElementById('mobile-menu');
    if (!hamburger || !mobileMenu || hamburger.dataset.bound) return;

    let isOpen = false;

    function openMenu() {
      isOpen = true;
      hamburger.classList.add('is-active');
      hamburger.setAttribute('aria-expanded', 'true');
      document.body.classList.add('menu-is-open');
      mobileMenu.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';

      // Stagger menu items
      const items = mobileMenu.querySelectorAll('.mobile-menu__item');
      gsap.fromTo(items,
        { x: -30, opacity: 0 },
        { x: 0, opacity: 1, duration: 0.5, stagger: 0.07, ease: 'power3.out', delay: 0.3 }
      );
    }

    function closeMenu() {
      isOpen = false;
      hamburger.classList.remove('is-active');
      hamburger.setAttribute('aria-expanded', 'false');
      document.body.classList.remove('menu-is-open');
      mobileMenu.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
    }

    hamburger.addEventListener('click', () => {
      isOpen ? closeMenu() : openMenu();
    });

    // Close on link click
    mobileMenu.querySelectorAll('.mobile-menu__link').forEach((link) => {
      link.addEventListener('click', closeMenu);
    });

    // Close on Escape
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && isOpen) closeMenu();
    });
  }

  /* ==========================================================================
     6. Hero Animations
     ========================================================================== */
  function initHeroAnimations() {
    const hero = document.querySelector('.hero');
    if (!hero) return;

    /* -- Set initial hidden states via GSAP (progressive enhancement) -- */
    const titleJp  = hero.querySelector('.hero__title-jp');
    const subtitle = hero.querySelector('.hero__subtitle');
    const news     = hero.querySelector('.hero__news');

    if (titleJp)  gsap.set(titleJp,  { clipPath: 'inset(0 0 100% 0)' });
    if (subtitle) gsap.set(subtitle, { opacity: 0, y: 16 });
    if (news)     gsap.set(news,     { opacity: 0, y: 10 });

    const tl = gsap.timeline({
      defaults: { ease: 'power3.out' },
    });

    /* -- Japanese Title (clip-path reveal) -- */
    if (titleJp) {
      tl.to(titleJp, {
        clipPath: 'inset(0 0 0% 0)',
        duration: 1.0,
        ease: 'power4.out',
      }, 0.35);
    }

    /* -- Subtitle -- */
    if (subtitle) {
      tl.to(subtitle, { opacity: 1, y: 0, duration: 0.7, ease: 'power2.out' }, 0.9);
    }

    /* -- News Ticker -- */
    if (news) {
      tl.to(news, { opacity: 1, y: 0, duration: 0.6 }, 1.3);
    }

    /* -- Mark hero as animated -- */
    tl.add(() => hero.classList.add('is-animated'), 0.5);

    /* -- Video / Slideshow subtle scale (parallax) -- */
    const videoBg = hero.querySelector('.hero__video, .hero__image, .hero__slideshow');
    if (videoBg) {
      gsap.from(videoBg, { scale: 1.08, duration: 2.0, ease: 'power2.out' });

      ScrollTrigger.create({
        trigger: hero,
        start: 'top top',
        end: 'bottom top',
        onUpdate: (self) => {
          gsap.set(videoBg, { y: self.progress * 80 });
        },
      });
    }
  }

  /* ==========================================================================
     6b. Hero Slideshow (images)
     ========================================================================== */
  function initHeroSlideshow() {
    const slideshow = document.querySelector('.hero__slideshow');
    if (!slideshow) return;

    const slides = slideshow.querySelectorAll('.hero__slide');
    if (slides.length < 2) return;

    // Clear previous interval if exists
    if (activeIntervals.slideshow) clearInterval(activeIntervals.slideshow);

    const interval = parseInt(slideshow.dataset.interval, 10) || 5000;
    let current = 0;

    activeIntervals.slideshow = setInterval(() => {
      slides[current].classList.remove('is-active');
      current = (current + 1) % slides.length;
      slides[current].classList.add('is-active');
    }, interval);
  }

  /* ==========================================================================
     6c. Hero Video Reel – fade + directional pan
     ========================================================================== */
  function initHeroVideoReel() {
    const reel = document.querySelector('.hero__video-reel');
    if (!reel) return;

    const videos = Array.from(reel.querySelectorAll('.hero__video-slide'));
    if (!videos.length) return;

    // Ensure all videos play (CSS animations handle pan/zoom)
    videos.forEach((v) => { v.play && v.play().catch(() => {}); });

    if (videos.length < 2) return;

    // Clear previous interval if exists
    if (activeIntervals.videoReel) clearInterval(activeIntervals.videoReel);

    const intervalMs = parseInt(reel.dataset.interval, 10) || 7000;
    let current = 0;

    activeIntervals.videoReel = setInterval(() => {
      videos[current].classList.remove('is-active');
      current = (current + 1) % videos.length;
      videos[current].classList.add('is-active');
      videos[current].play && videos[current].play().catch(() => {});
    }, intervalMs);
  }

  /* ==========================================================================
     6d. About Section Parallax
     ========================================================================== */
  function initAboutParallax() {
    const bg = document.querySelector('.home-about__bg');
    const orb = document.querySelector('.home-about__orb-lg');
    if (!bg || typeof ScrollTrigger === 'undefined') return;

    ScrollTrigger.create({
      trigger: '.home-about',
      start: 'top bottom',
      end: 'bottom top',
      onUpdate: (self) => {
        const p = self.progress;
        gsap.set(bg, { y: (p - 0.5) * 120 });
        if (orb) gsap.set(orb, { y: (p - 0.5) * -60 });
      },
    });
  }

  /* ==========================================================================
     6e. News Slider – slide content horizontally
     ========================================================================== */
  function initNewsSlide() {
    const newsPanel = document.querySelector('.hero__news');
    const newsContainer = document.querySelector('.hero__news-container');
    if (!newsPanel || !newsContainer) return;

    const links = Array.from(newsContainer.querySelectorAll('.hero__news-link'));
    if (links.length < 2) return;

    // Clear previous interval if exists
    if (activeIntervals.newsSlide) clearInterval(activeIntervals.newsSlide);

    const intervalMs = parseInt(newsPanel.dataset.newsInterval, 10) || 5000;
    let current = 0;

    // Hide all except first
    links.forEach((link, i) => {
      if (i !== 0) link.style.opacity = '0';
    });

    function slideToNext() {
      const currentLink = links[current];
      const nextIndex = (current + 1) % links.length;
      const nextLink = links[nextIndex];

      // Show next link and start slide-in animation
      nextLink.style.opacity = '1';
      nextLink.classList.add('is-sliding-in');

      // Animate current link out
      currentLink.classList.add('is-sliding-out');

      // After animation completes
      setTimeout(() => {
        // Reset current link
        currentLink.classList.remove('is-sliding-out');
        currentLink.style.opacity = '0';

        // Reset next link
        nextLink.classList.remove('is-sliding-in');
        nextLink.style.opacity = '1';

        current = nextIndex;
      }, 600); // match CSS animation duration
    }

    activeIntervals.newsSlide = setInterval(slideToNext, intervalMs);
  }

  /* ==========================================================================
     7. Barba.js Page Transitions
     ========================================================================== */
  function initBarba() {
    if (typeof barba === 'undefined') return;

    const transition = document.getElementById('page-transition');
    const fill = transition ? transition.querySelector('.page-transition__fill') : null;

    barba.init({
      debug: false,
      preventRunning: true,
      cacheSize: 3,
      prevent: ({ el }) => (
        el.classList.contains('no-barba') ||
        (el.hostname && el.hostname !== window.location.hostname) ||
        el.href.includes('/wp-admin') ||
        el.href.includes('/wp-login') ||
        el.href.includes('?paged=') ||
        el.href.includes('?page=')
      ),
      transitions: [{
        name: 'default-transition',

        async leave() {
          // Clear all active intervals to prevent memory leaks
          if (activeIntervals.slideshow) clearInterval(activeIntervals.slideshow);
          if (activeIntervals.videoReel) clearInterval(activeIntervals.videoReel);
          if (activeIntervals.newsSlide) clearInterval(activeIntervals.newsSlide);

          if (lenis) lenis.stop();
          window.scrollTo(0, 0);
        },

        async enter() {
          // Clean up all animations
          gsap.killTweensOf('*');
          ScrollTrigger.getAll().forEach((st) => st.kill());
          ScrollTrigger.refresh();

          if (lenis) {
            lenis.scrollTo(0, { immediate: true });
            lenis.start();
          }

          initPageAnimations();
        },
      }],
    });

    barba.hooks.afterEnter(() => {
      initHeader();
      initCampaignAccordion();

      // DOM が完全に準備されるまで少し遅延させる
      setTimeout(() => {
        initCampaignSidebar();
      }, 100);

      // Clear home page cache to ensure fresh load on return
      if (window.location.pathname === '/' || window.location.pathname === '') {
        barba.cache.clear();
      }
    });
  }

  /* ==========================================================================
     8. General Page Animations (called on each page load / transition)
     ========================================================================== */
  function initPageAnimations() {
    // Reset reveal animations from previous page
    document.querySelectorAll('[data-reveal]').forEach((el) => {
      gsap.set(el, { opacity: 0, y: 30 });
    });

    // Hero (only on front page)
    if (document.querySelector('.hero')) {
      initHeroAnimations();
      initHeroSlideshow();
      initHeroVideoReel();
      initNewsSlide();
    }

    // About section parallax
    initAboutParallax();

    // Sticky CTA (re-init on page transition)
    initStickyCta();

    // Campaign sidebar sticky (re-init on page transition)
    initCampaignSidebar();

    // Generic reveal animations - use requestAnimationFrame to ensure DOM is ready
    requestAnimationFrame(() => {
      ScrollTrigger.batch('[data-reveal]', {
        start: 'top 75%',
        onEnter: (batch) => {
          gsap.to(batch, {
            opacity: 1,
            y: 0,
            duration: 0.6,
            stagger: 0.08,
            ease: 'power3.out',
          });
        },
        once: true,
      });
    });
  }

  /* ==========================================================================
     9. Bootstrap
     ========================================================================== */
  async function init() {
    // Loader (only on first load, not Barba transitions)
    const isInitial = !document.body.dataset.barbaLoaded;
    if (isInitial) {
      document.body.dataset.barbaLoaded = '1';
      await initLoader();
    }

    // Core systems
    initLenis();
    initCursor();
    initHeader();
    initMobileMenu();
    initCampaignAccordion();

    // Page-specific animations
    initPageAnimations();

    // Barba (after first paint)
    requestAnimationFrame(() => { initBarba(); });

    // Safety: if hero was never marked as animated within 4s, force-show all content
    setTimeout(() => {
      const hero = document.querySelector('.hero');
      if (!hero || hero.classList.contains('is-animated')) return;
      const forcShow = ['.hero__title-jp', '.hero__subtitle', '.hero__news'];
      forcShow.forEach((sel) => {
        hero.querySelectorAll(sel).forEach((el) => {
          el.style.cssText += 'opacity:1!important;transform:none!important;clip-path:none!important;';
        });
      });
    }, 4000);
  }

  /* ==========================================================================
     Sticky CTA Buttons – Scroll Detection
     ========================================================================== */
  function initStickyCta() {
    var wrapper = document.getElementById('sticky-cta-wrapper');
    if (!wrapper) {
      return;
    }
    window.addEventListener('scroll', function () {
      var scrollY = window.scrollY;
      if (scrollY > 200) {
        wrapper.classList.add('is-visible');
      } else {
        wrapper.classList.remove('is-visible');
      }
      // Also add is-visible to individual buttons
      wrapper.querySelectorAll('.sticky-cta').forEach(function(btn) {
        if (scrollY > 200) {
          btn.classList.add('is-visible');
        } else {
          btn.classList.remove('is-visible');
        }
      });
    }, { passive: true });

    // Trigger initial scroll event
    window.dispatchEvent(new Event('scroll'));
  }

  /* ==========================================================================
     Campaign Archive Month Accordion
     ========================================================================== */
  function initCampaignAccordion() {
    // すべてのアコーディオンの初期状態を設定（最初のものは開く、他は閉じる）
    const allMonths = document.querySelectorAll('.campaign-archive-month');

    if (allMonths.length === 0) return;

    allMonths.forEach((month, index) => {
      const header = month.querySelector('.campaign-archive-month__header');
      const items = month.querySelector('.campaign-archive-month__items');
      const icon = header.querySelector('.campaign-archive-month__toggle-icon');

      if (index === 0) {
        // 最初は開く
        header.setAttribute('aria-expanded', 'true');
        if (items) {
          items.classList.remove('is-collapsed');
          // 最初のアイテムは開いた状態で高さを設定
          items.style.maxHeight = items.scrollHeight + 'px';
        }
      } else {
        // 2番目以降は閉じる
        header.setAttribute('aria-expanded', 'false');
        if (items) {
          items.classList.add('is-collapsed');
          items.style.maxHeight = '0';
        }
      }

      // toggle-icon がない場合は追加
      if (!icon) {
        const newIcon = document.createElement('span');
        newIcon.className = 'campaign-archive-month__toggle-icon';
        header.appendChild(newIcon);
      }
    });
  }

  // クリックイベント（document レベルで一度だけ登録）
  if (typeof window.campaignAccordionInitialized === 'undefined') {
    document.addEventListener('click', (e) => {
      const header = e.target.closest('.campaign-archive-month__header');
      if (!header) return;

      const items = header.nextElementSibling;
      if (!items || !items.classList.contains('campaign-archive-month__items')) return;

      // スクロール位置を保存
      const scrollY = lenis ? lenis.scroll : window.scrollY;

      const isExpanded = header.getAttribute('aria-expanded') === 'true';
      header.setAttribute('aria-expanded', String(!isExpanded));
      items.classList.toggle('is-collapsed');

      // 高さを動的に計算
      if (isExpanded) {
        // 閉じる
        items.style.maxHeight = '0';
      } else {
        // 開く - 実際のコンテンツ高さを使用
        items.style.maxHeight = items.scrollHeight + 'px';
      }

      // スクロール位置を復元
      if (lenis) {
        // lenis 復元後に少し遅延させる
        setTimeout(() => {
          lenis.scrollTo(scrollY, { immediate: true });
          // サイドバーの位置を再計算
          setTimeout(() => {
            if (lenis) {
              lenis.emit('scroll', { scroll: scrollY });
            }
          }, 50);
        }, 0);
      } else {
        requestAnimationFrame(() => {
          window.scrollTo(0, scrollY);
          // スクロールイベントを発火してサイドバー位置を再計算
          window.dispatchEvent(new Event('scroll'));
        });
      }
    });

    document.addEventListener('keydown', (e) => {
      const header = e.target.closest('.campaign-archive-month__header');
      if (!header) return;

      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        header.click();
      }
    });

    window.campaignAccordionInitialized = true;
  }

  /* ==========================================================================
     Campaign Sidebar Sticky Positioning
     ========================================================================== */
  let campaignSidebarUpdateHandler = null;

  function initCampaignSidebar() {
    const sidebar = document.querySelector('.campaign-sidebar');
    const wrapper = document.querySelector('.campaign-content-wrapper');
    if (!sidebar || !wrapper) return;

    let previousState = null;
    const headerHeight = 100;
    const offset = 20;

    function updateSidebarPosition() {
      const scrollY = lenis ? lenis.scroll : window.scrollY;
      const wrapperRect = wrapper.getBoundingClientRect();
      const wrapperTop = wrapperRect.top + scrollY;
      const wrapperBottom = wrapperTop + wrapper.offsetHeight;
      const sidebarHeight = sidebar.offsetHeight;

      let state;

      if (scrollY + headerHeight + offset > wrapperTop && scrollY + headerHeight + offset < wrapperBottom - sidebarHeight) {
        state = {
          type: 'fixed',
          position: 'fixed',
          top: (headerHeight + offset) + 'px',
          left: (wrapperRect.left + wrapperRect.width - sidebar.offsetWidth) + 'px',
          width: '300px'
        };
      } else if (scrollY + headerHeight + offset >= wrapperBottom - sidebarHeight) {
        state = {
          type: 'absolute',
          position: 'absolute',
          top: (wrapper.offsetHeight - sidebarHeight) + 'px',
          left: 'auto',
          width: '300px'
        };
      } else {
        state = {
          type: 'relative',
          position: 'relative',
          top: '0',
          left: 'auto',
          width: 'auto'
        };
      }

      // 状態が変わった時のみ更新
      if (!previousState || previousState.type !== state.type) {
        sidebar.style.position = state.position;
        sidebar.style.top = state.top;
        sidebar.style.left = state.left;
        sidebar.style.width = state.width;
        previousState = state;
      } else if (state.type === 'fixed') {
        // fixed 状態の時は毎フレーム left を更新（スクロール時に位置変更される可能性があるため）
        sidebar.style.left = state.left;
      }
    }

    // 古いハンドラーを削除
    if (campaignSidebarUpdateHandler && lenis) {
      lenis.off('scroll', campaignSidebarUpdateHandler);
    }

    // 新しいハンドラーを登録
    if (lenis) {
      campaignSidebarUpdateHandler = updateSidebarPosition;
      lenis.on('scroll', updateSidebarPosition);
    } else {
      window.addEventListener('scroll', updateSidebarPosition, { passive: true });
    }

    // 初期状態を計算
    updateSidebarPosition();
  }

  /* ==========================================================================
     Core Web Vitals - Lazy Load Background Images
     ========================================================================== */
  function initLazyLoadBg() {
    var images = document.querySelectorAll('[data-bg-image]');
    if (!images.length || !window.IntersectionObserver) return;

    var io = new IntersectionObserver(function(entries) {
      entries.forEach(function(entry) {
        if (entry.isIntersecting) {
          var el = entry.target;
          var bgUrl = el.getAttribute('data-bg-image');
          if (bgUrl) {
            el.style.backgroundImage = 'url(' + bgUrl + ')';
            el.removeAttribute('data-bg-image');
            io.unobserve(el);
          }
        }
      });
    }, { rootMargin: '50px' });

    images.forEach(function(img) {
      io.observe(img);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLazyLoadBg);
  } else {
    initLazyLoadBg();
  }

  // Init sticky CTA
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initStickyCta);
  } else {
    initStickyCta();
  }

  // DOMContentLoaded
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
