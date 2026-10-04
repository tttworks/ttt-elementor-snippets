<?php
/**
 * ============================================
 * TTT Category Tabs - JS脚本
 * ============================================
 *
 * @version     1.0.0
 * @date        2026-06-22
 * @author      Aloysius Luo
 * @organization TTTWORKS
 * @contact     https://tttworks.com
 * @description 分类列表 Tab 交互脚本
 *
 * --------------------------------------------
 * 版本更新日志
 * --------------------------------------------
 * v1.0.0 (2026-06-22)
 *   [新] 初始版本
 * --------------------------------------------
 */

add_action('wp_enqueue_scripts', 'ttt_category_tabs_enqueue');
add_action('elementor/preview/enqueue_scripts', 'ttt_category_tabs_enqueue');

function ttt_category_tabs_enqueue() {
    $js = <<<'JS'
(function () {
    'use strict';

    var TTTCategoryTabs = {
        init: function () {
            this.cacheElements();
            this.bindEvents();
            this.updateActiveState();
        },

        cacheElements: function () {
            this.containers = document.querySelectorAll('.ttt-tabs-heading');
        },

        bindEvents: function () {
            var self = this;

            this.containers.forEach(function (container) {
                var buttons = container.querySelectorAll('.ttt-tab-title');
                
                buttons.forEach(function (button) {
                    button.addEventListener('click', function (e) {
                        if (e.target.tagName === 'A') {
                            return;
                        }
                        var link = this.querySelector('a');
                        if (link && link.href) {
                            window.location.href = link.href;
                        }
                    });

                    button.addEventListener('keydown', function (e) {
                        self.handleKeyboardNav(e, container);
                    });
                });
            });

            window.addEventListener('load', function () {
                self.updateActiveState();
            });

            window.addEventListener('popstate', function () {
                self.updateActiveState();
            });
        },

        updateActiveState: function () {
            var self = this;
            var currentUrl = window.location.href;

            this.containers.forEach(function (container) {
                var buttons = container.querySelectorAll('.ttt-tab-title');
                
                buttons.forEach(function (button) {
                    var link = button.querySelector('a');
                    if (!link) return;

                    var linkUrl = link.href;
                    var linkPath = linkUrl.split('?')[0].split('#')[0];
                    var currentPath = currentUrl.split('?')[0].split('#')[0];

                    if (linkPath === currentPath || link.href === currentUrl) {
                        button.classList.add('is-active');
                        button.setAttribute('aria-selected', 'true');
                        button.setAttribute('tabindex', '0');
                    } else {
                        button.classList.remove('is-active');
                        button.setAttribute('aria-selected', 'false');
                        button.setAttribute('tabindex', '-1');
                    }
                });
            });
        },

        handleKeyboardNav: function (e, container) {
            var buttons = Array.from(container.querySelectorAll('.ttt-tab-title'));
            var currentIndex = buttons.indexOf(e.target);
            var targetIndex;

            switch (e.key) {
                case 'ArrowRight':
                case 'ArrowDown':
                    e.preventDefault();
                    targetIndex = currentIndex < buttons.length - 1 ? currentIndex + 1 : 0;
                    buttons[targetIndex].focus();
                    break;
                case 'ArrowLeft':
                case 'ArrowUp':
                    e.preventDefault();
                    targetIndex = currentIndex > 0 ? currentIndex - 1 : buttons.length - 1;
                    buttons[targetIndex].focus();
                    break;
                case 'Home':
                    e.preventDefault();
                    buttons[0].focus();
                    break;
                case 'End':
                    e.preventDefault();
                    buttons[buttons.length - 1].focus();
                    break;
                case 'Enter':
                case ' ':
                    e.preventDefault();
                    var link = e.target.querySelector('a');
                    if (link && link.href) {
                        window.location.href = link.href;
                    }
                    break;
            }
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            TTTCategoryTabs.init();
        });
    } else {
        TTTCategoryTabs.init();
    }

    window.TTTCategoryTabs = TTTCategoryTabs;

})();
JS;

    wp_register_script('ttt-category-tabs-js', '', [], null, true);
    wp_enqueue_script('ttt-category-tabs-js');
    wp_add_inline_script('ttt-category-tabs-js', $js);
}
