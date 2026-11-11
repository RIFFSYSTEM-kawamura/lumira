/**
 * NavBgMesh – WebGL displacement ripple effect for fullscreen nav
 * Requires: Three.js (r120+)
 */
(function (global) {
  'use strict';
  if (typeof THREE === 'undefined') return;

  /* ── Shaders ─────────────────────────────────────────── */
  var BG_VERT = [
    'varying vec2 vUv;',
    'void main(){',
    '  vUv = uv;',
    '  gl_Position = projectionMatrix * modelViewMatrix * vec4(position,1.0);',
    '}'
  ].join('\n');

  var BG_FRAG = [
    '#define PI 3.14159265358979323846',
    'uniform vec2      uMeshSize;',
    'uniform vec2      uTextureSize;',
    'uniform sampler2D uTexture;',
    'uniform sampler2D uDisplacement;',
    'varying vec2 vUv;',
    'void main(){',
    '  vec4  disp  = texture2D(uDisplacement, vUv);',
    '  float theta = disp.r * 3.0 * PI;',
    '  vec2  dir   = vec2(sin(theta), cos(theta));',
    '  vec2  uv    = vUv + dir * disp.r * 0.1;',
    '  vec2  ratio = vec2(',
    '    min((uMeshSize.x/uMeshSize.y)/(uTextureSize.x/uTextureSize.y), 1.0),',
    '    min((uMeshSize.y/uMeshSize.x)/(uTextureSize.y/uTextureSize.x), 1.0)',
    '  );',
    '  uv -= 0.5; uv /= ratio; uv += 0.5;',
    '  gl_FragColor = texture2D(uTexture, uv);',
    '}'
  ].join('\n');

  /* ── Constructor ──────────────────────────────────────── */
  function NavBgMesh(canvas, navEl, bgImageUrl) {
    this.canvas     = canvas;
    this.navEl      = navEl;
    this.bgImageUrl = bgImageUrl || '';
    this.w          = window.innerWidth;
    this.h          = window.innerHeight;
    this.max        = 100;
    this.cur        = 0;
    this.meshes     = [];
    this.mouse      = { x: 0, y: 0 };
    this.prev       = { x: 0, y: 0 };
    this.animId     = null;
    this.enabled    = false;
    this.ready      = false;
  }

  /* ── Init ─────────────────────────────────────────────── */
  NavBgMesh.prototype.init = function () {
    var self = this, w = this.w, h = this.h;

    /* Renderer */
    this.renderer = new THREE.WebGLRenderer({
      canvas: this.canvas, antialias: false, alpha: false
    });
    this.renderer.setPixelRatio(Math.min(window.devicePixelRatio, 1.5));
    this.renderer.setSize(w, h);

    /* Orthographic camera – origin at centre */
    this.cam = new THREE.OrthographicCamera(-w/2, w/2, h/2, -h/2, -100, 100);
    this.cam.position.z = 10;

    /* Offscreen render target for the displacement map */
    this.rt = new THREE.WebGLRenderTarget(w, h, {
      minFilter: THREE.LinearFilter,
      magFilter: THREE.LinearFilter,
      format:    THREE.RGBAFormat
    });

    /* Ripple scene (offscreen) */
    this.rippleScene = new THREE.Scene();
    this.rippleTex   = this._makeRippleTex();

    var rGeo = new THREE.PlaneGeometry(64, 64);
    for (var i = 0; i < this.max; i++) {
      var mat = new THREE.MeshBasicMaterial({
        map:         this.rippleTex,
        transparent: true,
        opacity:     0,
        blending:    THREE.AdditiveBlending,
        depthTest:   false,
        depthWrite:  false
      });
      var m = new THREE.Mesh(rGeo, mat);
      m.visible    = false;
      m.rotation.z = Math.random() * Math.PI * 2;
      this.rippleScene.add(m);
      this.meshes.push(m);
    }

    /* Background scene */
    this.bgScene = new THREE.Scene();
    this.bgMat   = new THREE.ShaderMaterial({
      vertexShader:   BG_VERT,
      fragmentShader: BG_FRAG,
      uniforms: {
        uMeshSize:     { value: new THREE.Vector2(w, h) },
        uTextureSize:  { value: new THREE.Vector2(1600, 1000) },
        uTexture:      { value: null },
        uDisplacement: { value: this.rt.texture }
      }
    });
    this.bgMesh = new THREE.Mesh(new THREE.PlaneGeometry(w, h), this.bgMat);
    this.bgScene.add(this.bgMesh);

    /* Load background image (optional – dark fallback if missing) */
    if (this.bgImageUrl) {
      new THREE.TextureLoader().load(
        this.bgImageUrl,
        function (tex) {
          self.bgMat.uniforms.uTexture.value = tex;
          self.bgMat.uniforms.uTextureSize.value.set(tex.image.width, tex.image.height);
        }
      );
    }

    /* Events */
    this._onMM = this._handleMouseMove.bind(this);
    this._onRZ = this._handleResize.bind(this);
    this.navEl.addEventListener('mousemove', this._onMM);
    window.addEventListener('resize', this._onRZ);

    this.ready = true;
  };

  /* ── Helpers ──────────────────────────────────────────── */
  NavBgMesh.prototype._makeRippleTex = function () {
    var sz = 128, c = document.createElement('canvas');
    c.width = c.height = sz;
    var ctx = c.getContext('2d');
    var g   = ctx.createRadialGradient(sz/2, sz/2, 0, sz/2, sz/2, sz/2);
    g.addColorStop(0,    'rgba(255,255,255,1)');
    g.addColorStop(0.25, 'rgba(255,255,255,0.6)');
    g.addColorStop(0.7,  'rgba(255,255,255,0.1)');
    g.addColorStop(1,    'rgba(255,255,255,0)');
    ctx.fillStyle = g;
    ctx.fillRect(0, 0, sz, sz);
    return new THREE.CanvasTexture(c);
  };

  NavBgMesh.prototype._handleMouseMove = function (e) {
    this.mouse.x =  e.clientX - this.w / 2;
    this.mouse.y =  this.h / 2 - e.clientY;
  };

  NavBgMesh.prototype._handleResize = function () {
    var w = window.innerWidth, h = window.innerHeight;
    this.w = w; this.h = h;
    this.renderer.setSize(w, h);
    this.cam.left = -w/2; this.cam.right  =  w/2;
    this.cam.top  =  h/2; this.cam.bottom = -h/2;
    this.cam.updateProjectionMatrix();
    this.rt.setSize(w, h);
    if (this.bgMesh) this.bgMesh.geometry = new THREE.PlaneGeometry(w, h);
    if (this.bgMat)  this.bgMat.uniforms.uMeshSize.value.set(w, h);
  };

  NavBgMesh.prototype._updateMouse = function () {
    if (Math.abs(this.mouse.x - this.prev.x) < 4 &&
        Math.abs(this.mouse.y - this.prev.y) < 4) return;
    this._stamp(this.mouse.x, this.mouse.y, this.cur);
    this.cur = (this.cur + 1) % this.max;
    this.prev.x = this.mouse.x;
    this.prev.y = this.mouse.y;
  };

  NavBgMesh.prototype._stamp = function (x, y, idx) {
    var m = this.meshes[idx];
    m.visible         = true;
    m.position.set(x, y, 0);
    m.scale.set(1, 1, 1);
    m.material.opacity = 0.5;
  };

  /* ── Render loop ──────────────────────────────────────── */
  NavBgMesh.prototype._tick = function () {
    this._updateMouse();

    // Debug: log visible ripple meshes count
    var visibleCount = 0;
    for (var i = 0; i < this.meshes.length; i++) {
      if (this.meshes[i].visible) visibleCount++;
    }
    if (visibleCount > 0 && !this._lastLog) {
      console.log('[WebGL] Rendering ripples. Visible meshes:', visibleCount);
      this._lastLog = Date.now();
    } else if (Date.now() - (this._lastLog || 0) > 2000) {
      this._lastLog = null;
    }

    /* Fade & grow ripples */
    for (var i = 0; i < this.meshes.length; i++) {
      var m = this.meshes[i];
      if (!m.visible) continue;
      m.material.opacity *= 0.975;
      m.scale.x = m.scale.x * 0.975 + 0.175;
      m.scale.y = m.scale.x;
      if (m.material.opacity < 0.005) m.visible = false;
    }

    /* 1. Render ripples → offscreen RT */
    this.renderer.setRenderTarget(this.rt);
    this.renderer.setClearColor(0x000000, 1);
    this.renderer.clear();
    this.renderer.render(this.rippleScene, this.cam);

    /* 2. Pass RT as displacement; render bg → screen */
    this.bgMat.uniforms.uDisplacement.value = this.rt.texture;
    this.renderer.setRenderTarget(null);
    this.renderer.setClearColor(0x6b4226, 1);
    this.renderer.clear();
    this.renderer.render(this.bgScene, this.cam);
  };

  /* ── Public API ───────────────────────────────────────── */
  NavBgMesh.prototype.start = function () {
    if (!this.ready || this.enabled) return;
    this.enabled = true;
    var self = this;
    (function loop() {
      if (!self.enabled) return;
      self._tick();
      self.animId = requestAnimationFrame(loop);
    })();
  };

  NavBgMesh.prototype.stop = function () {
    this.enabled = false;
    if (this.animId) { cancelAnimationFrame(this.animId); this.animId = null; }
    /* Clear ripples */
    this.meshes.forEach(function (m) { m.visible = false; });
  };

  NavBgMesh.prototype.destroy = function () {
    this.stop();
    if (this._onMM) this.navEl.removeEventListener('mousemove', this._onMM);
    if (this._onRZ) window.removeEventListener('resize', this._onRZ);
  };

  global.NavBgMesh = NavBgMesh;
})(window);
