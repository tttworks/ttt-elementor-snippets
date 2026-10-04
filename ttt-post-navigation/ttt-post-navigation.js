/**
 * ============================================
 * TTT Post Navigation - JS脚本
 * ============================================
 *
 * @version     1.2.0
 * @date        2026-06-25
 * @author      Aloysius Luo
 * @organization TTTWORKS
 * @contact     https://tttworks.com
 * @description 文章导航组件交互脚本
 *
 * --------------------------------------------
 * 版本更新日志
 * --------------------------------------------
 * v1.2.0 (2026-06-25)
 *   [改] 更新选择器以适配新的行式结构
 * --------------------------------------------
 */

add_action('wp_enqueue_scripts', 'ttt_post_navigation_enqueue');
add_action('elementor/preview/enqueue_scripts', 'ttt_post_navigation_enqueue');

function ttt_post_navigation_enqueue() {
    $js = <<<'JS'
(function () {
    'use strict';

    var TTTPostNavigation = {
        init: function () {
            this.cacheElements();
            this.bindEvents();
        },

        cacheElements: function () {
            this.containers = document.querySelectorAll('.zpn-navigation');
        },

        bindEvents: function () {
            var self = this;

            this.containers.forEach(function (container) {
                var links = container.querySelectorAll('.zpn-link');

                links.forEach(function (link) {
                    link.addEventListener('keydown', function (e) {
                        self.handleKeyboardNav(e, container);
                    });
                });
            });
        },

        handleKeyboardNav: function (e, container) {
            var links = Array.from(container.querySelectorAll('.zpn-link'));
            var currentIndex = links.indexOf(e.target);
            var targetIndex;

            switch (e.key) {
                case 'ArrowRight':
                case 'ArrowDown':
                    e.preventDefault();
                    targetIndex = currentIndex < links.length - 1 ? currentIndex + 1 : 0;
                    links[targetIndex].focus();
                    break;
                case 'ArrowLeft':
                case 'ArrowUp':
                    e.preventDefault();
                    targetIndex = currentIndex > 0 ? currentIndex - 1 : links.length - 1;
                    links[targetIndex].focus();
                    break;
                case 'Home':
                    e.preventDefault();
                    if (links.length > 0) {
                        links[0].focus();
                    }
                    break;
                case 'End':
                    e.preventDefault();
                    if (links.length > 0) {
                        links[links.length - 1].focus();
                    }
                    break;
                case 'Enter':
                case ' ':
                    if (e.target.tagName === 'A' && e.target.getAttribute('href') !== '#') {
                        return;
                    }
                    e.preventDefault();
                    if (e.target.tagName === 'A' && e.target.getAttribute('href') !== '#') {
                        window.location.href = e.target.getAttribute('href');
                    }
                    break;
            }
        },

        reinit: function () {
            this.cacheElements();
            this.bindEvents();
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            TTTPostNavigation.init();
        });
    } else {
        TTTPostNavigation.init();
    }

    window.TTTPostNavigation = TTTPostNavigation;

})();
JS;

    wp_register_script('ttt-post-navigation-js', '', [], null, true);
    wp_enqueue_script('ttt-post-navigation-js');
    wp_add_inline_script('ttt-post-navigation-js', $js);
}