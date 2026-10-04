/**
 * ============================================
 * TTT Simple List from Menu - JS 脚本
 * ============================================
 *
 * @version     1.0.0
 * @date        2026-06-27
 * @author      Aloysius Luo
 * @organization TTTWORKS
 * @contact     https://tttworks.com
 * @description 菜单链接列表组件 JS — Elementor 前端集成与编辑器预览
 *
 * --------------------------------------------
 * 版本更新日志
 * --------------------------------------------
 * v1.0.0 (2026-06-27)
 *   [新] 初始版本 — Elementor frontend/editor 集成
 * ============================================
 */

add_action( 'wp_enqueue_scripts',                'ttt_menu_list_enqueue_js_v100' );
add_action( 'elementor/preview/enqueue_scripts', 'ttt_menu_list_enqueue_js_v100' );
function ttt_menu_list_enqueue_js_v100() {

    $js = <<<'JS'
(function () {
    'use strict';

    /* Elementor 前端初始化 */
    if (typeof window.elementorFrontend !== 'undefined' && window.elementorFrontend.hooks) {
        window.elementorFrontend.hooks.addAction(
            'frontend/element_ready/ttt_menu_list.default',
            function ($el) {
                /* 占位 — 纯 CSS 驱动，无需额外 JS 逻辑 */
                /* 预留钩子供后续版本扩展（如手风琴模式、搜索过滤等） */
            }
        );
    }

    /* 编辑器预览下刷新渲染 */
    document.addEventListener('DOMContentLoaded', function () {
        /* 菜单列表为纯服务端渲染 + CSS 驱动，无需客户端重绘 */
    });
})();
JS;

    wp_register_script( 'ttt-menu-list', '', [], null, true );
    wp_enqueue_script(  'ttt-menu-list' );
    wp_add_inline_script( 'ttt-menu-list', $js );
}
