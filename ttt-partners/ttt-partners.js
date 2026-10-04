/**
 * ============================================
 * TTT Partners - JS 脚本
 * ============================================
 *
 * @version     1.0.0
 * @date        2026-06-21
 * @author      Aloysius Luo
 * @organization TTTWORKS
 * @contact     https://tttworks.com
 * @description Elementor 合作伙伴展示组件 JS v1.0.0 — 网格分页轮播 + 平铺 + 圆点页码
 *
 * --------------------------------------------
 * 版本更新日志
 * --------------------------------------------
 * v1.0.0 (2026-06-21)
 *   [新] grid 分页轮播：Pointer Events 滑动 + 自动轮播 + 圆点页码交互
 *   [新] 响应式行列动态更新
 * ============================================
 */

add_action( 'wp_enqueue_scripts',                'zpt_enqueue_js_v100' );
add_action( 'elementor/preview/enqueue_scripts', 'zpt_enqueue_js_v100' );
function zpt_enqueue_js_v100() {

    $js = <<<'JS'
(function () {
    'use strict';

    function initPartners(el) {
        if (!el || el.dataset.zptInit === '1') return;
        el.dataset.zptInit = '1';

        var isCarousel = el.dataset.carousel === 'yes';
        if (!isCarousel) {
            // 平铺模式只做响应式行列更新
            flatResponsive(el);
            return;
        }

        var track     = el.querySelector('.zpt-track');
        var viewport  = el.querySelector('.zpt-viewport');
        var pages     = Array.from(el.querySelectorAll('.zpt-page'));
        var dots      = Array.from(el.querySelectorAll('.zpt-dot'));
        var totalPages = pages.length;
        var index     = 0;
        var transSpeed = parseInt(el.dataset.transitionSpeed || 500, 10);
        var autoplayEnabled = el.dataset.autoplay === 'yes';
        var autoplayInterval = parseInt(el.dataset.autoplayInterval || 3000, 10);

        if (totalPages <= 1) {
            if (el.querySelector('.zpt-pagination')) {
                el.querySelector('.zpt-pagination').style.display = 'none';
            }
            return;
        }

        var colsBase = parseInt(el.dataset.cols || 3, 10);
        var rowsBase = parseInt(el.dataset.rows || 2, 10);
        var colsTablet = parseInt(el.dataset.colsTablet || 2, 10);
        var rowsTablet = parseInt(el.dataset.rowsTablet || 2, 10);
        var colsMobile = parseInt(el.dataset.colsMobile || 1, 10);
        var rowsMobile = parseInt(el.dataset.rowsMobile || 3, 10);

        var autoTimer = null;

        /* ── 响应式断点 ── */
        function getResponsiveConfig() {
            var w = window.innerWidth;
            if (w <= 767) return { cols: colsMobile, rows: rowsMobile };
            if (w <= 1024) return { cols: colsTablet, rows: rowsTablet };
            return { cols: colsBase, rows: rowsBase };
        }

        /* ── 更新每页的 grid 行列 ── */
        function updateGridColumns(cfg) {
            pages.forEach(function(page) {
                page.style.gridTemplateColumns = 'repeat(' + cfg.cols + ', 1fr)';
            });
        }

        function update() {
            if (!viewport) return;
            var vpW = viewport.getBoundingClientRect().width;
            if (vpW <= 0) return;
            index = Math.max(0, Math.min(index, totalPages - 1));
            var offset = index * vpW;
            track.style.transform = 'translate3d(-' + offset + 'px,0,0)';
            track.style.transitionDuration = transSpeed + 'ms';

            dots.forEach(function(d, i) {
                d.classList.toggle('active', i === index);
            });
        }

        function goTo(newIndex) {
            index = Math.max(0, Math.min(newIndex, totalPages - 1));
            update();
        }

        function nextPage() { if (index < totalPages - 1) goTo(index + 1); }

        var drag = { active: false, startX: 0, currentX: 0, moved: false, baseOffset: 0 };
        viewport.addEventListener('pointerdown', function(e) {
            if (e.button !== 0) return;
            var vpW = viewport.getBoundingClientRect().width;
            drag.active = true; drag.startX = e.clientX; drag.currentX = e.clientX;
            drag.moved = false; drag.baseOffset = index * vpW;
            viewport.setPointerCapture(e.pointerId);
            track.classList.add('no-transition');
            stopAutoplay();
        });
        viewport.addEventListener('pointermove', function(e) {
            if (!drag.active) return;
            var dx = e.clientX - drag.startX; drag.currentX = e.clientX;
            if (Math.abs(dx) > 5) drag.moved = true;
            var vpW = viewport.getBoundingClientRect().width;
            var maxOff = (totalPages - 1) * vpW;
            var rawOffset = drag.baseOffset - dx;
            var offset;
            if (rawOffset < 0) offset = rawOffset / 3;
            else if (rawOffset > maxOff) offset = maxOff + (rawOffset - maxOff) / 3;
            else offset = rawOffset;
            track.style.transform = 'translate3d(-' + offset + 'px,0,0)';
        });
        function onDragEnd(e) {
            if (!drag.active) return;
            drag.active = false;
            track.classList.remove('no-transition');
            if (!drag.moved) { startAutoplay(); return; }
            var dx = drag.currentX - drag.startX;
            if (Math.abs(dx) > 50) {
                if (dx < 0 && index < totalPages - 1) index++;
                else if (dx > 0 && index > 0) index--;
            }
            update(); startAutoplay();
        }
        viewport.addEventListener('pointerup', onDragEnd);
        viewport.addEventListener('pointercancel', onDragEnd);
        viewport.addEventListener('click', function(e) {
            if (drag.moved) { e.preventDefault(); e.stopPropagation(); drag.moved = false; }
        }, true);

        /* ── 圆点点击 ── */
        dots.forEach(function(d) {
            d.addEventListener('click', function(e) {
                e.preventDefault();
                var di = parseInt(d.dataset.dot, 10);
                if (!isNaN(di)) goTo(di);
                stopAutoplay(); startAutoplay();
            });
        });

        /* ── Auto ═══ */
        function startAutoplay() {
            if (!autoplayEnabled) return;
            stopAutoplay();
            autoTimer = setInterval(function() {
                index = (index >= totalPages - 1) ? 0 : index + 1;
                update();
            }, autoplayInterval);
        }
        function stopAutoplay() {
            if (autoTimer) { clearInterval(autoTimer); autoTimer = null; }
        }
        if (autoplayEnabled) {
            el.addEventListener('pointerenter', stopAutoplay);
            el.addEventListener('pointerleave', startAutoplay);
        }

        /* ── 键盘 ═── */
        el.setAttribute('tabindex', '0');
        el.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowRight') { nextPage(); stopAutoplay(); startAutoplay(); e.preventDefault(); }
            if (e.key === 'ArrowLeft')  { if (index > 0) { goTo(index-1); stopAutoplay(); startAutoplay(); } e.preventDefault(); }
        });

        /* ── 初始化 & Resize ── */
        function calcLayout() {
            var cfg = getResponsiveConfig();
            updateGridColumns(cfg);
            update();
        }

        var resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(calcLayout, 150);
        }, { passive: true });

        if (document.readyState === 'complete') { calcLayout(); startAutoplay(); }
        else { window.addEventListener('load', function() { calcLayout(); startAutoplay(); }, { once: true }); }
        setTimeout(function() { calcLayout(); }, 80);
    }

    /* ── 平铺模式的响应式行列更新 ── */
    function flatResponsive(el) {
        var grid = el.querySelector('.zpt-flat-grid');
        if (!grid) return;
        var cols = parseInt(el.dataset.cols || 3, 10);
        var colsT = parseInt(el.dataset.colsTablet || 2, 10);
        var colsM = parseInt(el.dataset.colsMobile || 1, 10);
        function apply() {
            var w = window.innerWidth;
            var c = w <= 767 ? colsM : w <= 1024 ? colsT : cols;
            grid.style.gridTemplateColumns = 'repeat(' + c + ', 1fr)';
        }
        apply();
        var rt;
        window.addEventListener('resize', function() {
            clearTimeout(rt);
            rt = setTimeout(apply, 150);
        }, { passive: true });
    }

    /* ── 启动入口 ── */
    function initAll() {
        document.querySelectorAll('.zpt-wrapper').forEach(initPartners);
    }
    document.addEventListener('DOMContentLoaded', initAll);
    window.addEventListener('elementor/frontend/init', function() {
        if (typeof elementorFrontend !== 'undefined' && elementorFrontend.hooks) {
            elementorFrontend.hooks.addAction('frontend/element_ready/ttt_partners.default', function($el) {
                var w = $el[0] && $el[0].querySelector('.zpt-wrapper');
                if (w) { w.dataset.zptInit = ''; initPartners(w); }
            });
        }
    });
})();
JS;

    wp_register_script( 'ttt-partners', '', [], null, true );
    wp_enqueue_script(  'ttt-partners' );
    wp_add_inline_script( 'ttt-partners', $js );
}
