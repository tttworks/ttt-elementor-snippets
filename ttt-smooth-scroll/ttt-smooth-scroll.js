/**
 * ============================================
 * TTT Smooth Scroll — JS 脚本
 * ============================================
 *
 * @version     1.0.1
 * @date        2026-06-30
 * @author      Aloysius Luo
 * @organization TTTWORKS
 * @contact     https://tttworks.com
 * @description 全页面惯性平滑滚动：rAF + lerp 插值，覆盖鼠标滚轮/键盘/中键拖拽/锚点跳转/触摸
 *
 * --------------------------------------------
 * 版本更新日志
 * --------------------------------------------
 * v1.0.1 (2026-06-30)
 *   [修] Elementor Popup 冲突：onAnchorClick 放行 #elementor-action 开头的链接
 *   [修] Elementor Popup 冲突：bodyLocked 同时检测 <html> 的 overflow:hidden（Popup 把锁定加在 html 而非 body）
 * v1.0.0 (2026-06-30)
 *   [新] 首次发布 — 完整平滑滚动实现
 *   [新] rAF + lerp 惯性核心（苹果官网手感）
 *   [新] 鼠标滚轮平滑（帧内合并，过滤触控板微量滚动）
 *   [新] 键盘平滑（方向键/PageUp/PageDown/Home/End/空格）
 *   [新] 鼠标中键拖拽惯性滚动（带死区+视觉光标）
 *   [新] 锚点 #hash 缓动跳转（easeOutCubic，可中断）
 *   [新] 浏览器前进/后退 hash 变化接管
 *   [新] 页面初始 hash 平滑定位
 *   [新] 移动端触摸接管（enableMobile 开关）
 *   [新] prefers-reduced-motion 无障碍自动降级
 *   [新] 可滚动子容器/表单元素/Elementor 编辑器自动放行
 *   [新] body overflow:hidden 弹窗检测
 *   [新] resize 时自动重钳位 target
 *   [新] data-no-smooth-scroll 属性手动排除
 *   [新] 所有参数从 get_option() 读取，仪表盘设置页实时生效
 */

add_action('wp_enqueue_scripts',                'ttt_smooth_scroll_enqueue');
add_action('elementor/preview/enqueue_scripts', 'ttt_smooth_scroll_enqueue');
function ttt_smooth_scroll_enqueue() {
    // 从 WordPress 设置读取所有参数
    $config = [
        'lerp'                 => (float) get_option('zss_lerp', 0.07),
        'wheelMultiplier'      => (float) get_option('zss_wheel_multiplier', 1),
        'keyboardStep'         => (float) get_option('zss_keyboard_step', 80),
        'pageScrollRatio'      => (float) get_option('zss_page_scroll_ratio', 0.9),
        'enableMobile'         => (bool)  get_option('zss_enable_mobile', 0),
        'smoothAnchors'        => (bool)  get_option('zss_smooth_anchors', 1),
        'anchorDuration'       => (int)   get_option('zss_anchor_duration', 1000),
        'respectReducedMotion' => (bool)  get_option('zss_respect_reduced_motion', 1),
        'midClickDeadZone'     => (float) get_option('zss_mid_click_dead_zone', 8),
        'midClickSpeedMul'     => (float) get_option('zss_mid_click_speed_mul', 0.12),
        'minWheelDelta'        => (float) get_option('zss_min_wheel_delta', 1.5),
        'debug'                => (bool)  get_option('zss_debug', 0),
    ];
    $config_json = json_encode($config, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    $js = <<<JS
(function () {
    'use strict';

    var CONFIG = $config_json;

    // ============================================================
    //  STATE
    // ============================================================
    var target   = window.scrollY || window.pageYOffset || 0;
    var current  = target;
    var rafId    = null;

    var wheelAccum   = 0;
    var wheelPending  = false;

    var anchorActive    = false;
    var anchorTargetVal = 0;
    var anchorStartVal  = 0;
    var anchorStartTime = 0;

    var midActive  = false;
    var midOriginY = 0;
    var midDisp    = 0;

    var touchActive = false;
    var touchLastY  = 0;

    // ============================================================
    //  HELPERS
    // ============================================================
    function L() {
        if (CONFIG.debug) console.log.apply(console, ['[ZSS]'].concat(Array.prototype.slice.call(arguments)));
    }

    function clamp(v, min, max) { return v < min ? min : (v > max ? max : v); }

    function scrollMax() {
        return Math.max(0, document.documentElement.scrollHeight - window.innerHeight);
    }

    function isReducedMotion() {
        return CONFIG.respectReducedMotion &&
               window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    }

    function isEditor() {
        if (typeof window.elementor !== 'undefined') return true;
        try {
            if (window.parent && window.parent !== window &&
                typeof window.parent.elementor !== 'undefined') return true;
        } catch (e) {}
        return false;
    }

    function isMobile() {
        return /Mobi|Android/i.test(navigator.userAgent) || window.innerWidth <= 767;
    }

    function isScrollableContainer(el) {
        while (el && el !== document.documentElement && el !== document.body) {
            if (el.hasAttribute('data-no-smooth-scroll')) return true;
            var s = window.getComputedStyle(el);
            var oy = s.overflowY, ox = s.overflowX;
            if ((oy === 'auto' || oy === 'scroll' || oy === 'overlay') && el.scrollHeight - el.clientHeight > 2) return true;
            if ((ox === 'auto' || ox === 'scroll' || ox === 'overlay') && el.scrollWidth - el.clientWidth > 2) return true;
            el = el.parentElement;
        }
        return false;
    }

    function isFormElement(el) {
        var t = el.tagName;
        if (t === 'INPUT' || t === 'TEXTAREA' || t === 'SELECT' || t === 'OPTION') return true;
        if (el.isContentEditable) return true;
        if (el.getAttribute('role') === 'textbox' || el.getAttribute('role') === 'combobox') return true;
        return false;
    }

    function bodyLocked() {
        var bs = window.getComputedStyle(document.body);
        var hs = window.getComputedStyle(document.documentElement);
        return bs.overflow === 'hidden' || bs.overflowY === 'hidden' ||
               hs.overflow === 'hidden' || hs.overflowY === 'hidden' ||
               document.body.classList.contains('elementor-editor-active');
    }

    // ============================================================
    //  ANCHOR HELPERS
    // ============================================================
    function easeOutCubic(t) { return 1 - Math.pow(1 - t, 3); }

    function getAnchorTarget(hash) {
        if (!hash || hash === '#') return null;
        var id = decodeURIComponent(hash.replace(/^#/, ''));
        var el = document.getElementById(id);
        if (!el) { try { el = document.querySelector('a[name="' + CSS.escape(id) + '"]'); } catch (ex) {} }
        if (!el) return null;
        return window.scrollY + el.getBoundingClientRect().top;
    }

    function cancelAnchor() { anchorActive = false; }

    function startAnchorAnimation(hash) {
        if (!CONFIG.smoothAnchors) return false;
        var pos = getAnchorTarget(hash);
        if (pos === null) return false;
        if (Math.abs(current - pos) < 5) return false;
        cancelAnchor();
        anchorActive    = true;
        anchorTargetVal = clamp(pos, 0, scrollMax());
        anchorStartVal  = current;
        anchorStartTime = performance.now();
        target = anchorTargetVal;
        startLoop();
        L('anchor →', Math.round(anchorTargetVal));
        return true;
    }

    // ============================================================
    //  CORE LERP LOOP
    // ============================================================
    function startLoop() {
        if (rafId !== null) return;
        rafId = requestAnimationFrame(tick);
    }

    function stopLoop() {
        if (rafId !== null) { cancelAnimationFrame(rafId); rafId = null; }
    }

    function tick(ts) {
        var sm = scrollMax();
        target = clamp(target, 0, sm);

        if (anchorActive) {
            var elapsed = ts - anchorStartTime;
            var t = clamp(elapsed / CONFIG.anchorDuration, 0, 1);
            t = easeOutCubic(t);
            current = anchorStartVal + (anchorTargetVal - anchorStartVal) * t;
            if (t >= 0.999) { current = anchorTargetVal; target = anchorTargetVal; anchorActive = false; }
            window.scrollTo(0, Math.round(current));
            if (anchorActive || midActive || Math.abs(current - target) > 0.5) {
                rafId = requestAnimationFrame(tick);
            } else { stopLoop(); }
            return;
        }

        if (midActive) {
            var velocity = midDisp * CONFIG.midClickSpeedMul;
            target += velocity;
            target = clamp(target, 0, sm);
        }

        current += (target - current) * CONFIG.lerp;
        if (Math.abs(target - current) < 0.3) current = target;

        window.scrollTo(0, Math.round(current));

        if (midActive || Math.abs(current - target) > 0.5) {
            rafId = requestAnimationFrame(tick);
        } else { stopLoop(); }
    }

    function setTarget(val, instant) {
        val = clamp(val, 0, scrollMax());
        if (instant) {
            target = val; current = val;
            window.scrollTo(0, Math.round(val));
            stopLoop();
        } else {
            cancelAnchor();
            target = val;
            if (Math.abs(current - target) > 0.5) startLoop();
        }
    }

    function scrollBy(delta) { setTarget(target + delta, false); }

    // ============================================================
    //  WHEEL HANDLER
    // ============================================================
    function onWheel(e) {
        if (isReducedMotion()) return;
        if (isEditor()) return;
        if (isMobile() && !CONFIG.enableMobile) return;
        if (bodyLocked()) return;
        if (isScrollableContainer(e.target)) return;
        if (isFormElement(e.target)) return;

        var dy = e.deltaY;
        if (Math.abs(dy) < CONFIG.minWheelDelta) return;

        e.preventDefault();
        cancelAnchor();

        wheelAccum += dy * CONFIG.wheelMultiplier;
        if (!wheelPending) {
            wheelPending = true;
            requestAnimationFrame(function () {
                wheelPending = false;
                var delta = wheelAccum;
                wheelAccum = 0;
                setTarget(target + delta, false);
            });
        }
    }

    window.addEventListener('wheel', onWheel, { passive: false });

    // ============================================================
    //  KEYBOARD HANDLER
    // ============================================================
    function onKeydown(e) {
        if (isReducedMotion()) return;
        if (isEditor()) return;
        if (isMobile() && !CONFIG.enableMobile) return;
        if (bodyLocked()) return;
        if (isFormElement(e.target)) return;
        if (e.ctrlKey || e.metaKey || e.altKey) return;

        var vh = window.innerHeight;
        var delta = 0;

        switch (e.key) {
            case 'ArrowDown':  delta =  CONFIG.keyboardStep;               break;
            case 'ArrowUp':    delta = -CONFIG.keyboardStep;               break;
            case 'PageDown':   delta =  vh * CONFIG.pageScrollRatio;      break;
            case 'PageUp':     delta = -vh * CONFIG.pageScrollRatio;      break;
            case 'End':        setTarget(scrollMax(), false); e.preventDefault(); return;
            case 'Home':       setTarget(0, false);         e.preventDefault(); return;
            case ' ':
                if (e.shiftKey) { delta = -vh * CONFIG.pageScrollRatio; }
                else            { delta =  vh * CONFIG.pageScrollRatio; }
                break;
            default: return;
        }

        if (delta !== 0) { e.preventDefault(); scrollBy(delta); }
    }

    window.addEventListener('keydown', onKeydown);

    // ============================================================
    //  MID-CLICK AUTO-SCROLL
    // ============================================================
    function onMousedown(e) {
        if (e.button !== 1) return;
        if (isReducedMotion()) return;
        if (isEditor()) return;
        if (isMobile() && !CONFIG.enableMobile) return;
        if (bodyLocked()) return;
        if (isScrollableContainer(e.target)) return;
        if (isFormElement(e.target)) return;

        e.preventDefault(); e.stopPropagation();
        midActive = true; midOriginY = e.clientY; midDisp = 0;
        document.documentElement.classList.add('zss-mid-dragging');
        startLoop();
        L('mid-click start');
    }

    function onMouseup(e) {
        if (e.button !== 1 || !midActive) return;
        midActive = false; midDisp = 0;
        document.documentElement.classList.remove('zss-mid-dragging');
        L('mid-click end → target:', Math.round(target));
    }

    function onMousemove(e) {
        if (!midActive) return;
        var dy = midOriginY - e.clientY;
        if (Math.abs(dy) < CONFIG.midClickDeadZone) { midDisp = 0; return; }
        var sign = dy > 0 ? 1 : -1;
        midDisp = dy - sign * CONFIG.midClickDeadZone;
    }

    function onContextMenu(e) { if (midActive) e.preventDefault(); }
    function forceEndMidClick() {
        if (!midActive) return;
        midActive = false; midDisp = 0;
        document.documentElement.classList.remove('zss-mid-dragging');
        L('mid-click force-end');
    }

    window.addEventListener('mousedown',  onMousedown);
    window.addEventListener('mouseup',    onMouseup);
    window.addEventListener('mousemove',  onMousemove);
    window.addEventListener('contextmenu', onContextMenu);
    window.addEventListener('mouseleave', forceEndMidClick);
    window.addEventListener('blur',       forceEndMidClick);

    // ============================================================
    //  ANCHOR LINK INTERCEPTION
    // ============================================================
    function isHashScrollable(hash) {
        // 只拦截真正指向页面现有元素的 hash——其他 hash
        //（如 Elementor Popup 的 #elementor-action）留给原生逻辑
        return getAnchorTarget(hash) !== null;
    }

    function onAnchorClick(e) {
        if (!CONFIG.smoothAnchors) return;
        if (isReducedMotion()) return;
        if (isEditor()) return;

        var link = e.target.closest('a[href^="#"]');
        if (!link) return;
        var href = link.getAttribute('href');
        if (!href || href === '#') return;

        // 放行非滚动用途的 hash 链接（Elementor Popup / Lightbox / Tab 等）
        if (!isHashScrollable(href)) return;

        if (link.getAttribute('role') === 'tab') return;
        if (link.getAttribute('data-elementor-open-lightbox') !== null) return;
        if (link.getAttribute('data-toggle') !== null) return;

        e.preventDefault(); e.stopPropagation();
        try { history.pushState(null, '', href); } catch (ex) { window.location.hash = href; }
        startAnchorAnimation(href);
    }

    document.addEventListener('click', onAnchorClick, true);

    window.addEventListener('hashchange', function () {
        if (!CONFIG.smoothAnchors || isReducedMotion() || isEditor()) return;
        var hash = window.location.hash;
        if (hash && hash !== '#') startAnchorAnimation(hash);
    });

    function handleInitialHash() {
        if (isReducedMotion() || isEditor() || !CONFIG.smoothAnchors) return;
        var hash = window.location.hash;
        if (!hash || hash === '#') return;
        setTimeout(function () { startAnchorAnimation(hash); }, 150);
    }

    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        handleInitialHash();
    } else {
        window.addEventListener('DOMContentLoaded', handleInitialHash);
    }

    // ============================================================
    //  TOUCH HANDLER
    // ============================================================
    if (CONFIG.enableMobile && isMobile()) {
        document.addEventListener('touchstart', function (e) {
            if (isReducedMotion() || isEditor() || bodyLocked()) return;
            if (isScrollableContainer(e.target)) return;
            if (e.touches.length !== 1) { touchActive = false; return; }
            touchActive = true; touchLastY = e.touches[0].clientY;
            cancelAnchor();
        }, { passive: false });

        document.addEventListener('touchmove', function (e) {
            if (!touchActive) return;
            if (isScrollableContainer(e.target)) return;
            if (e.touches.length !== 1) return;
            var ty = e.touches[0].clientY;
            var dy = touchLastY - ty;
            if (Math.abs(dy) > 2) { e.preventDefault(); setTarget(target + dy * 1.5, false); }
            touchLastY = ty;
        }, { passive: false });

        document.addEventListener('touchend',    function () { touchActive = false; });
        document.addEventListener('touchcancel', function () { touchActive = false; });
    }

    // ============================================================
    //  RESIZE HANDLER
    // ============================================================
    var resizeTimer;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            var sm = scrollMax();
            target  = clamp(target,  0, sm);
            current = clamp(current, 0, sm);
            if (Math.abs(current - target) > 0.5) startLoop();
        }, 150);
    });

    // ============================================================
    //  INIT
    // ============================================================
    function in