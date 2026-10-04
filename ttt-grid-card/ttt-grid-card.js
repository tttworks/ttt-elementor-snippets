/**
 * Snippet Name: Z Grid Card - JS
 *
 * v1.3  2026-04-12
 *   [增] 新增 orientationchange 监听，覆盖手机端旋转屏幕场景
 *   [增] 新增 ResizeObserver 监听 grid 容器自身尺寸变化，
 *        覆盖 Elementor 编辑器内拖动面板改变预览宽度的场景
 *        （ResizeObserver 比 window.resize 更精准，只在容器宽度真正变化时触发）
 *   [优] 将 resizeTimer 提升为每个 grid 独立管理，逻辑更清晰
 * v1.2  2026-04-12
 *   [增] fixGridHeight()：JS 直接计算并设置 .zgc-grid min-height
 *        解决移动端 grid 行高错误导致与后续元素重叠的问题
 * v1.1  2026-04-12
 *   配合 PHP v1.1 升级，JS 无逻辑变动
 * v1.0
 *   基础初始化
 */

add_action( 'wp_enqueue_scripts', 'zgc_enqueue_js' );
add_action( 'elementor/preview/enqueue_scripts', 'zgc_enqueue_js' );

function zgc_enqueue_js() {
    $js = <<<'JS'
(function () {
    'use strict';

    /* ── 核心：修正 .zgc-grid 的高度 ── */
    function fixGridHeight(grid) {
        if (!grid) return;

        var cards = grid.querySelectorAll('.zgc-card');
        if (!cards.length) return;

        /* 1. 读取列数和间距 */
        var cs = window.getComputedStyle(grid);
        var colsStr = cs.gridTemplateColumns || '';
        var cols = colsStr.trim() ? colsStr.trim().split(/\s+/).length : 1;
        var gap = parseFloat(cs.rowGap) || parseFloat(cs.gap) || 0;

        /* 2. 先清除之前设置的 min-height，让布局重排，拿到真实高度 */
        grid.style.minHeight = '';

        /* 3. 下一帧读取高度，避免读到旧值 */
        requestAnimationFrame(function() {
            var colHeights = new Array(cols).fill(0);

            for (var i = 0; i < cards.length; i++) {
                var colIndex = i % cols;
                colHeights[colIndex] += cards[i].getBoundingClientRect().height;
            }

            /* 4. 每列再加行间距（行数 - 1）条 */
            var rows = Math.ceil(cards.length / cols);
            var totalGapHeight = (rows - 1) * gap;
            var maxColHeight = Math.max.apply(null, colHeights);
            var totalHeight = maxColHeight + totalGapHeight;

            grid.style.minHeight = Math.ceil(totalHeight) + 'px';
        });
    }

    /* ── 初始化单个 grid ── */
    function initGrid(el) {
        if (!el || el.dataset.zgcInit === '1') return;
        el.dataset.zgcInit = '1';

        /* 等图片全部加载完再首次计算 */
        var images  = el.querySelectorAll('.zgc-img-el');
        var total   = images.length;
        var loaded  = 0;

        function onReady() {
            loaded++;
            if (loaded >= total) fixGridHeight(el);
        }

        if (total === 0) {
            fixGridHeight(el);
        } else {
            for (var i = 0; i < images.length; i++) {
                if (images[i].complete) {
                    onReady();
                } else {
                    images[i].addEventListener('load',  onReady);
                    images[i].addEventListener('error', onReady);
                }
            }
        }

        /* 兜底：500ms 后强制修正一次 */
        setTimeout(function() { fixGridHeight(el); }, 500);

        /* ── 监听1：window resize（桌面端拖动浏览器窗口） ── */
        var resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() { fixGridHeight(el); }, 150);
        });

        /* ── 监听2：orientationchange（手机端旋转屏幕） ── */
        window.addEventListener('orientationchange', function() {
            /* 旋转后需等浏览器完成重排，延迟稍长 */
            setTimeout(function() { fixGridHeight(el); }, 300);
        });

        /* ── 监听3：ResizeObserver（Elementor 编辑器拖动面板 / 任何容器宽度变化） ── */
        if (typeof ResizeObserver !== 'undefined') {
            var ro = new ResizeObserver(function(entries) {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() { fixGridHeight(el); }, 150);
            });
            ro.observe(el); /* 监听 grid 容器自身 */
        }
    }

    /* ── Elementor 前端钩子 ── */
    window.addEventListener('elementor/frontend/init', function() {
        if (typeof elementorFrontend !== 'undefined' && elementorFrontend.hooks) {
            elementorFrontend.hooks.addAction(
                'frontend/element_ready/z_grid_card.default',
                function($el) {
                    var grid = $el[0] && $el[0].querySelector('.zgc-grid');
                    if (grid) initGrid(grid);
                }
            );
        }
    });

    /* ── 非 Elementor 编辑器环境兜底（前台直接访问） ── */
    document.addEventListener('DOMContentLoaded', function() {
        var grids = document.querySelectorAll('.zgc-grid');
        for (var i = 0; i < grids.length; i++) {
            initGrid(grids[i]);
        }
    });

})();
JS;

    wp_register_script( 'z-grid-card', '', [], null, true );
    wp_enqueue_script(  'z-grid-card' );
    wp_add_inline_script( 'z-grid-card', $js );
}