<!--
  浮动窗口显示逻辑

  v1.0 初始版本：
  - PC端：鼠标靠近右侧触发展开，离开收起；滚动到底部展开；首次访问10秒自动隐藏
  - 移动端/平板：永远完全显示

  v1.1 修改说明（2026-04-13）：
  - 移动端显示逻辑调整：默认隐藏50%（由CSS控制初始状态，消除加载闪烁）
  - 移动端交互改为：点击浮动窗口展开，点击页面其他区域收起
  - 移动端展开后点击窗口内部保持展开，不影响链接正常跳转
  - 移除移动端原有"永远完全显示"逻辑
  - setExpanded() 移除移动端特殊拦截，统一走展开/收起计算逻辑
  - init() 中新增移动端click事件绑定（使用命名空间 .mobile 便于解绑）
  - resize 时移动端重新执行 init() 重绑事件，PC端自动解绑移动端事件
  - PC端及平板原有显示逻辑不变
-->
<script>
jQuery(function($) {
    var $floatingWidget = $('#float-customize');
    var expandDistanceFromRight = 0;    // 鼠标距右侧0px内触发
    var expandDistanceFromBottom = 500; // 距离底部500px内触发
    var widgetWidth = 0;                // 存储容器宽度

    var isFirstVisit = true;            // 是否首次访问
    var autoHideTimer = null;           // 10秒自动隐藏定时器
    var userInteracted = false;         // 用户是否已经触发过其他条件
    var isMouseNearRight = false;       // 鼠标是否在右侧区域
    var collapseDebounceTimer = null;   // 收起防抖定时器

    // 判断是否为移动端
    function isMobile() {
        return window.innerWidth <= 768;
    }

    // 初始化：获取容器实际宽度并设置初始位置
    function init() {
        widgetWidth = $floatingWidget.outerWidth();

        if (isMobile()) {
            // 移动端：初始隐藏50%由CSS保证，JS只负责绑定交互事件
            // 先解绑避免 resize 时重复绑定
            $floatingWidget.off('.mobile');
            $(document).off('.mobile');

            // 点击浮动窗口：展开
            $floatingWidget.on('click.mobile', function(e) {
                e.stopPropagation(); // 阻止冒泡，避免触发document的收起事件
                setExpanded(true);
            });

            // 点击页面其他区域：收起
            $(document).on('click.mobile', function() {
                setExpanded(false);
            });

            return; // 移动端不需要后续PC逻辑
        }

        // PC端：首次访问且页面有焦点时，完全显示
        if (isFirstVisit && document.hasFocus()) {
            $floatingWidget.css('right', '20px');
            $floatingWidget.addClass('expanded');
            startAutoHideTimer(); // 启动10秒倒计时
        } else {
            // 非首次访问或页面无焦点，半遮面
            var halfWidth = widgetWidth / 2;
            $floatingWidget.css('right', '-' + halfWidth + 'px');
        }
    }

    // 启动10秒自动隐藏定时器（仅PC端）
    function startAutoHideTimer() {
        if (isMobile()) return; // 移动端不启动定时器

        clearTimeout(autoHideTimer);
        autoHideTimer = setTimeout(function() {
            // 10秒后，如果用户没有触发其他条件，则隐藏
            if (isFirstVisit && !userInteracted) {
                setExpanded(false);
                isFirstVisit = false; // 标记首次访问结束
            }
        }, 10000); // 10秒
    }

    // 取消10秒自动隐藏
    function cancelAutoHideTimer() {
        clearTimeout(autoHideTimer);
        userInteracted = true; // 标记用户已交互
        isFirstVisit = false;  // 结束首次访问状态
    }

    // 设置展开/收起状态（移动端和PC端共用）
    function setExpanded(expanded) {
        if (expanded) {
            $floatingWidget.addClass('expanded');
            $floatingWidget.css('right', '20px');
        } else {
            $floatingWidget.removeClass('expanded');
            widgetWidth = $floatingWidget.outerWidth();
            var halfWidth = widgetWidth / 2;
            $floatingWidget.css('right', '-' + halfWidth + 'px');
        }
    }

    // 检查是否应该收起（带防抖，仅PC端）
    function checkAndCollapse() {
        if (isMobile()) return; // 移动端收起由click事件控制

        clearTimeout(collapseDebounceTimer);
        collapseDebounceTimer = setTimeout(function() {
            // 检查所有展开条件
            var scrollBottom = $(document).height() - $(window).scrollTop() - $(window).height();
            var isAtBottom = scrollBottom <= expandDistanceFromBottom;
            var isHoveringWidget = $floatingWidget.is(':hover');

            // 如果所有条件都不满足，才收起
            if (!isMouseNearRight && !isAtBottom && !isHoveringWidget) {
                setExpanded(false);
            }
        }, 150); // 150ms防抖延迟
    }

    // 页面加载后初始化
    setTimeout(init, 100);

    // 监听页面焦点变化（仅PC端）
    $(window).on('focus', function() {
        if (isMobile()) return; // 移动端不响应焦点变化

        // 页面获得焦点
        if (isFirstVisit && !userInteracted) {
            setExpanded(true);
            startAutoHideTimer();
        }
    }).on('blur', function() {
        if (isMobile()) return; // 移动端不响应焦点变化

        // 页面失去焦点，收起
        if (isFirstVisit) {
            cancelAutoHideTimer();
        }
        setExpanded(false);
    });

    // 处理鼠标靠近右侧（仅PC端）
    $(document).on('mousemove', function(e) {
        if (isMobile()) return;

        var fromRight = window.innerWidth - e.clientX;

        // 扩大右侧触发区域，包含容器展开后的宽度 + 20px空隙
        var triggerZone = widgetWidth + 20;

        if (fromRight <= triggerZone) {
            isMouseNearRight = true;
            cancelAutoHideTimer(); // 用户触发条件，取消自动隐藏
            setExpanded(true);
        } else {
            isMouseNearRight = false;
            checkAndCollapse();
        }
    });

    // 悬浮在容器上也展开（仅PC端）
    $floatingWidget.on('mouseenter', function() {
        if (!isMobile()) {
            cancelAutoHideTimer(); // 用户触发条件，取消自动隐藏
            setExpanded(true);
        }
    }).on('mouseleave', function() {
        if (!isMobile()) {
            checkAndCollapse();
        }
    });

    // 处理滚动到底部（仅PC端）
    $(window).on('scroll', function() {
        if (isMobile()) return;

        var scrollBottom = $(document).height() - $(window).scrollTop() - $(window).height();
        if (scrollBottom <= expandDistanceFromBottom) {
            cancelAutoHideTimer(); // 用户触发条件，取消自动隐藏
            setExpanded(true);
        } else {
            checkAndCollapse();
        }
    });

    // 响应浏览器窗口大小变化
    $(window).on('resize', function() {
        widgetWidth = $floatingWidget.outerWidth();

        if (isMobile()) {
            // 切换到移动端：清除PC端定时器，重新执行移动端初始化（含事件重绑定）
            clearTimeout(autoHideTimer);
            init();
        } else {
            // 切换到PC端：解绑移动端事件，避免干扰PC逻辑
            $floatingWidget.off('.mobile');
            $(document).off('.mobile');

            // PC端调整宽度后重新计算收起位置
            if (!$floatingWidget.hasClass('expanded')) {
                var halfWidth = widgetWidth / 2;
                $floatingWidget.css('right', '-' + halfWidth + 'px');
            }
        }
    });
});
</script>