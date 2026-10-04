/**
 * SCF Features Accordion Pro - JS Scripts
 * 描述: PC端左右布局切换，手机端手风琴动画
 * 版本: 1.0.6
 * 日期: 2026-05-11
 *
 * 变更记录:
 * v1.0.6: 适配手机版手风琴结构（.scf-features-accordion-pro-mobile）
 * v1.0.5: 适配新HTML结构（左右分离布局，共享content面板）
 */

add_action('wp_footer', 'scf_features_accordion_pro_scripts');
add_action('elementor/preview/enqueue_scripts', 'scf_features_accordion_pro_scripts');

function scf_features_accordion_pro_scripts() {
    static $printed = false;
    if ($printed) return;
    $printed = true;
    ?>
    <script id="scf-features-accordion-pro-js">
    document.addEventListener('DOMContentLoaded', function() {
        var accordions = document.querySelectorAll('.scf-features-accordion-pro');

        accordions.forEach(function(accordion) {
            var titles = accordion.querySelectorAll('.scf-features-accordion-pro-titles .scf-features-accordion-pro-title');
            var contents = accordion.querySelectorAll('.scf-features-accordion-pro-content-panel .scf-features-accordion-pro-content');
            var mobileItems = accordion.querySelectorAll('.scf-features-accordion-pro-mobile .scf-features-accordion-pro-item');

            function isMobile() {
                return window.innerWidth <= 767;
            }

            // PC Layout - Left titles
            titles.forEach(function(title) {
                title.addEventListener('click', function() {
                    var index = this.getAttribute('data-index');

                    titles.forEach(function(t) {
                        t.classList.remove('is-active');
                        t.setAttribute('aria-expanded', 'false');
                    });
                    contents.forEach(function(c) {
                        c.classList.remove('is-active');
                    });

                    this.classList.add('is-active');
                    this.setAttribute('aria-expanded', 'true');

                    var targetContent = accordion.querySelector('.scf-features-accordion-pro-content-panel .scf-features-accordion-pro-content[data-index="' + index + '"]');
                    if (targetContent) {
                        targetContent.classList.add('is-active');
                    }
                });

                title.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        this.click();
                    }
                });
            });

            // Mobile Layout - Accordion items
            mobileItems.forEach(function(item) {
                var title = item.querySelector('.scf-features-accordion-pro-item-title');
                var content = item.querySelector('.scf-features-accordion-pro-item-content');

                title.addEventListener('click', function() {
                    var isActive = item.classList.contains('is-active');

                    if (isActive) {
                        item.classList.remove('is-active');
                        title.classList.remove('is-active');
                        title.setAttribute('aria-expanded', 'false');
                        content.style.maxHeight = '0';
                        content.style.opacity = '0';
                    } else {
                        var allItems = accordion.querySelectorAll('.scf-features-accordion-pro-mobile .scf-features-accordion-pro-item');
                        var allContents = accordion.querySelectorAll('.scf-features-accordion-pro-mobile .scf-features-accordion-pro-item-content');

                        allItems.forEach(function(i) {
                            i.classList.remove('is-active');
                            var t = i.querySelector('.scf-features-accordion-pro-item-title');
                            t.classList.remove('is-active');
                            t.setAttribute('aria-expanded', 'false');
                        });
                        allContents.forEach(function(c) {
                            c.style.maxHeight = '0';
                            c.style.opacity = '0';
                        });

                        item.classList.add('is-active');
                        title.classList.add('is-active');
                        title.setAttribute('aria-expanded', 'true');
                        content.style.maxHeight = content.scrollHeight + 'px';
                        content.style.opacity = '1';
                    }
                });

                title.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        this.click();
                    }
                });
            });
        });
    });
    </script>
    <?php
}