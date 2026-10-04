/**
 * Snippet Name: image plus – JS
 *
 * v1.1  2026-04-25
 *   [改] 配合 PHP 样式更新，JS 主要负责初始化
 * v1.0  2026-04-25
 *   初始版本
 */

add_action( 'wp_enqueue_scripts',                'imgplus_enqueue_js' );
add_action( 'elementor/preview/enqueue_scripts', 'imgplus_enqueue_js' );

function imgplus_enqueue_js() {

    $js = <<<'JS'
(function () {
    'use strict';

    function initImagePlus(el) {
        if (!el || el.dataset.imgplusInit === '1') return;
        el.dataset.imgplusInit = '1';

        var container = el.querySelector('.imgplus-container');
        if (!container) return;

        // 图像预加载（可选，提升悬停切换体验）
        var hoverImg = el.querySelector('.imgplus-image-hover');
        if (hoverImg && hoverImg.dataset.src) {
            var tempImg = new Image();
            tempImg.src = hoverImg.dataset.src;
        }

        // 动态设置 CSS 变量（支持运行时更新）
        var defaultOpacity = el.dataset.defaultOpacity || 1;
        var hoverOpacity = el.dataset.hoverOpacity || 1;
        container.style.setProperty('--imgplus-default-opacity', defaultOpacity);
        container.style.setProperty('--imgplus-hover-opacity', hoverOpacity);
    }

    /* ── 初始化页面所有 image plus ── */
    function initAll() {
        document.querySelectorAll('.imgplus-wrapper').forEach(initImagePlus);
    }

    document.addEventListener('DOMContentLoaded', initAll);

    /* ── Elementor 编辑器 Widget ready ── */
    window.addEventListener('elementor/frontend/init', function() {
        if (typeof elementorFrontend !== 'undefined' && elementorFrontend.hooks) {
            elementorFrontend.hooks.addAction(
                'frontend/element_ready/image_plus.default',
                function($el) {
                    var wrapper = $el[0] && $el[0].querySelector('.imgplus-wrapper');
                    if (wrapper) {
                        wrapper.dataset.imgplusInit = '';
                        initImagePlus(wrapper);
                    }
                }
            );
        }
    });

})();
JS;

    wp_register_script( 'image-plus', '', [], null, true );
    wp_enqueue_script(  'image-plus' );
    wp_add_inline_script( 'image-plus', $js );
}