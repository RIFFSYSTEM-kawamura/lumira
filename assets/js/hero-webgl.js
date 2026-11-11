/**
 * LUMIRA Hero WebGL – Hero下端の弧状カット処理
 * Three.js を使用して動画テクスチャを PlaneGeometry でレンダリング
 * フラグメントシェーダーで下端を弧状にフェードアウト・マスク処理
 */

(function () {
  'use strict';

  if (typeof THREE === 'undefined') {
    console.warn('Three.js not loaded – hero WebGL disabled');
    return;
  }

  class HomeFvMesh {
    constructor(canvasEl, videoEl) {
      this.canvas = canvasEl;
      this.video = videoEl;
      this.renderer = null;
      this.scene = null;
      this.camera = null;
      this.mesh = null;
      this.videoTexture = null;
      this.animationId = null;
    }

    init() {
      if (!this.canvas || !this.video) return;

      // Renderer
      this.renderer = new THREE.WebGLRenderer({
        canvas: this.canvas,
        alpha: true,
        antialias: true,
        powerPreference: 'high-performance'
      });
      this.renderer.setPixelRatio(window.devicePixelRatio || 1);
      this.renderer.setClearColor(0x000000, 0);

      // Scene
      this.scene = new THREE.Scene();

      // Camera
      this.camera = new THREE.OrthographicCamera(-1, 1, 1, -1, 0.1, 10);
      this.camera.position.z = 1;

      // Geometry
      const geometry = new THREE.PlaneGeometry(2, 2, 32, 32);

      // Video Texture
      this.videoTexture = new THREE.VideoTexture(this.video);
      this.videoTexture.minFilter = THREE.LinearFilter;
      this.videoTexture.magFilter = THREE.LinearFilter;
      this.videoTexture.format = THREE.RGBFormat;

      console.log('[Hero WebGL] Video element:', this.video);
      console.log('[Hero WebGL] Video paused:', this.video.paused);
      console.log('[Hero WebGL] Video duration:', this.video.duration);
      console.log('[Hero WebGL] Video currentTime:', this.video.currentTime);

      // Material with Custom Shader
      const material = new THREE.ShaderMaterial({
        uniforms: {
          uTexture: { value: this.videoTexture },
          uResolution: { value: new THREE.Vector2(window.innerWidth, window.innerHeight) },
          uArcHeight: { value: 0.18 }
        },
        vertexShader: this._getVertexShader(),
        fragmentShader: this._getFragmentShader(),
        side: THREE.FrontSide,
        transparent: true
      });

      // Mesh
      this.mesh = new THREE.Mesh(geometry, material);
      this.scene.add(this.mesh);

      // Resize & Render
      this._onWindowResize();
      window.addEventListener('resize', () => this._onWindowResize());

      this._animate();
    }

    _getVertexShader() {
      return `
        varying vec2 vUv;

        void main() {
          vUv = uv;
          gl_Position = projectionMatrix * modelViewMatrix * vec4(position, 1.0);
        }
      `;
    }

    _getFragmentShader() {
      return `
        uniform sampler2D uTexture;
        uniform vec2 uResolution;
        uniform float uArcHeight;
        varying vec2 vUv;

        void main() {
          vec4 color = texture2D(uTexture, vUv);

          // 下端の弧状マスク：放物線
          // 中心が高く、両端が低い上向き弧
          float curve = uArcHeight * (1.0 - 4.0 * (vUv.x - 0.5) * (vUv.x - 0.5));

          // vUv.y が上から下へ 0→1 なので、
          // 1.0 - vUv.y により下端での判定を実施
          float alpha = smoothstep(1.0 - curve - 0.02, 1.0 - curve + 0.02, 1.0 - vUv.y);

          color.a = alpha;
          gl_FragColor = color;
        }
      `;
    }

    _onWindowResize() {
      const w = this.canvas.clientWidth;
      const h = this.canvas.clientHeight;
      console.log('[Hero WebGL] Canvas size:', w, h);
      this.renderer.setSize(w, h);

      if (this.mesh && this.mesh.material.uniforms.uResolution) {
        this.mesh.material.uniforms.uResolution.value.set(w, h);
      }
    }

    _animate() {
      this.animationId = requestAnimationFrame(() => this._animate());

      // デバッグ: 最初のフレームだけログ出力
      if (!this._loggedFirstFrame) {
        console.log('[Hero WebGL] First render frame executed');
        console.log('[Hero WebGL] Renderer size:', this.renderer.getSize(new THREE.Vector2()));
        this._loggedFirstFrame = true;
      }

      this.renderer.render(this.scene, this.camera);
    }

    dispose() {
      if (this.animationId) cancelAnimationFrame(this.animationId);
      if (this.mesh && this.mesh.geometry) this.mesh.geometry.dispose();
      if (this.mesh && this.mesh.material) this.mesh.material.dispose();
      if (this.videoTexture) this.videoTexture.dispose();
      if (this.renderer) this.renderer.dispose();
    }
  }

  // Initialize on DOMContentLoaded
  function initHeroWebGL() {
    const canvas = document.getElementById('hero-canvas');
    // Find any video element inside hero__bg, including hero__video-slide
    const video = document.querySelector('.hero__bg video');

    if (!canvas) {
      console.warn('Hero canvas not found');
      return;
    }

    if (!video) {
      console.warn('Hero video not found');
      return;
    }

    // Ensure video is playing
    if (video.paused) {
      video.play().catch(e => console.warn('Video play failed:', e));
    }

    const mesh = new HomeFvMesh(canvas, video);
    mesh.init();

    // Store for cleanup if needed
    window.heroWebGL = mesh;

    console.log('Hero WebGL initialized successfully');
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initHeroWebGL);
  } else {
    initHeroWebGL();
  }
})();
