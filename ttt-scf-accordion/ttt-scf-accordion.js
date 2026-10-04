/**
 * SCF Features Accordion - JS Scripts
 * 描述: 手风琴互斥展开，展开/关闭动画均为 400ms
 * 版本: 1.3.2
 * 日期: 2026-05-11
 *
 * 变更记录:
 * v1.3.2: 修复快速点击时动画丢失 bug - 关闭前先获取当前高度，添加 16ms 延迟确保动画触发
 * v1.3.1: 修复闭合无动画 bug
 */

add_action('wp_footer', 'scf_features_accordion_scripts');
add_action('elementor/preview/enqueue_scripts', 'scf_features_accordion_scripts');

function scf_features_accordion_scripts() {
    static $printed = false;
    if ($printed) return;
    $printed = true;
    ?>
    <script id="scf-features-accordion-js">
    document.addEventListener('DOMContentLoaded', function() {
        const accordions = document.querySelectorAll('.scf-features-accordion details');

        accordions.forEach(acc => {
            const summary = acc.querySelector('.scf-features-accordion-title');
            const content = acc.querySelector('.scf-features-accordion-content');

            if (acc.hasAttribute('open')) {
                content.style.maxHeight = content.scrollHeight + 'px';
                content.style.opacity = '1';
            }

            summary.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                if (acc.hasAttribute('open')) {
                    closeAccordion(acc, content);
                } else {
                    accordions.forEach(otherAcc => {
                        if (otherAcc !== acc && otherAcc.hasAttribute('open')) {
                            closeAccordion(otherAcc, otherAcc.querySelector('.scf-features-accordion-content'));
                        }
                    });
                    openAccordion(acc, content);
                }
            });

            summary.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    summary.click();
                }
            });
        });

        function openAccordion(acc, content) {
            acc.setAttribute('open', '');
            acc.querySelector('.scf-features-accordion-title').setAttribute('aria-expanded', 'true');
            content.style.opacity = '1';
            requestAnimationFrame(function() {
                content.style.maxHeight = content.scrollHeight + 'px';
            });
        }

        function closeAccordion(acc, content) {
            var currentHeight = content.scrollHeight;
            acc.removeAttribute('open');
            acc.querySelector('.scf-features-accordion-title').setAttribute('aria-expanded', 'false');
            content.style.opacity = '0';
            setTimeout(function() {
                content.style.maxHeight = '0';
            }, 16);
        }
    });
    </script>
    <?php
}