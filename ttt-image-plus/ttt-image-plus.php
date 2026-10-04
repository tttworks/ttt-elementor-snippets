/**
 * Snippet Name: image plus – PHP Widget
 *
 * v1.3  2026-04-25
 *   [修] 改用原生 <img> 标签代替 wp_get_attachment_image()，避免 srcset 生成不存在的缩略图导致 404
 *   [修] 图像使用完整 URL，不依赖 WordPress 附件系统
 * v1.2  2026-04-25
 *   [改] 图像链接新增 Elementor 动态标签支持
 *   [改] 布局选项移至样式标签页，宽度高度默认值改为 40px
 *   [改] 宽度、最大宽度、高度支持移动端响应式，单位可选择 px/%/em/rem/vw/vh
 *   [删] 去掉宽度类型选项
 * v1.1  2026-04-25
 *   [改] 全面支持自适应尺寸、滤镜、悬停动画、边框阴影等样式选项
 * v1.0  2026-04-25
 *   初始版本
 */

add_action('elementor/widgets/register', function ($widgets_manager) {
    if ( ! class_exists( '\Elementor\Widget_Base' ) ) return;

    // 防止重复定义
    if ( class_exists('Image_Plus_Widget') ) return;

    class Image_Plus_Widget extends \Elementor\Widget_Base {

        public function get_name()           { return 'image_plus'; }
        public function get_title()          { return 'image plus'; }
        public function get_icon()           { return 'eicon-image-bold'; }
        public function get_categories()     { return [ 'general' ]; }
        public function get_keywords()       { return [ 'image', 'photo', 'hover', 'swap', 'hover image' ]; }
        public function get_style_depends()  { return [ 'image-plus' ]; }
        public function get_script_depends() { return [ 'image-plus' ]; }

        protected function register_controls() {

            /* ─── 内容选项 ─── */
            $this->start_controls_section( 'section_content', [
                'label' => '图像内容',
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ] );

            // 默认图像
            $this->add_control( 'image_default', [
                'label'   => '默认图像',
                'type'    => \Elementor\Controls_Manager::MEDIA,
                'default' => [ 'url' => \Elementor\Utils::get_placeholder_image_src() ],
                'description' => '鼠标悬停前显示的图像',
            ] );

            // 悬停图像
            $this->add_control( 'image_hover', [
                'label'   => '悬停图像',
                'type'    => \Elementor\Controls_Manager::MEDIA,
                'default' => [ 'url' => \Elementor\Utils::get_placeholder_image_src() ],
                'description' => '鼠标悬停时切换显示的图像（建议与默认图像尺寸一致）',
            ] );

            // 图片链接（支持动态标签）
            $this->add_control( 'image_link', [
                'label'       => '图像链接',
                'type'        => \Elementor\Controls_Manager::URL,
                'options'     => [ 'url', 'is_external', 'nofollow' ],
                'dynamic'     => [
                    'active' => true,
                ],
                'default'     => [ 'url' => '' ],
                'separator'   => 'before',
            ] );

            $this->end_controls_section();

            /* ─── 样式：尺寸 ─── */
            $this->start_controls_section( 'section_style_size', [
                'label' => '尺寸',
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ] );

            // 宽度
            $this->add_responsive_control( 'width', [
                'label'      => '宽度',
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'em', 'rem', 'vw' ],
                'range'      => [
                    'px'  => [ 'min' => 1, 'max' => 2000 ],
                    '%'   => [ 'min' => 1, 'max' => 100 ],
                    'em'  => [ 'min' => 1, 'max' => 200 ],
                    'rem' => [ 'min' => 1, 'max' => 200 ],
                    'vw'  => [ 'min' => 1, 'max' => 100 ],
                ],
                'default'    => [ 'unit' => 'px', 'size' => 40 ],
            ] );

            // 最大宽度
            $this->add_responsive_control( 'max_width', [
                'label'      => '最大宽度',
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'em', 'rem', 'vw' ],
                'range'      => [
                    'px'  => [ 'min' => 1, 'max' => 2000 ],
                    '%'   => [ 'min' => 1, 'max' => 100 ],
                    'em'  => [ 'min' => 1, 'max' => 200 ],
                    'rem' => [ 'min' => 1, 'max' => 200 ],
                    'vw'  => [ 'min' => 1, 'max' => 100 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .imgplus-container' => 'max-width: {{SIZE}}{{UNIT}};',
                ],
                'default'    => [ 'unit' => 'px', 'size' => 100 ],
            ] );

            // 高度
            $this->add_responsive_control( 'height', [
                'label'      => '高度',
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'em', 'rem', 'vh' ],
                'range'      => [
                    'px'  => [ 'min' => 1, 'max' => 2000 ],
                    '%'   => [ 'min' => 1, 'max' => 100 ],
                    'em'  => [ 'min' => 1, 'max' => 200 ],
                    'rem' => [ 'min' => 1, 'max' => 200 ],
                    'vh'  => [ 'min' => 1, 'max' => 100 ],
                ],
                'default'    => [ 'unit' => 'px', 'size' => 40 ],
            ] );

            // 对象拟合
            $this->add_responsive_control( 'object_fit', [
                'label'   => '对象拟合',
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'cover',
                'options' => [
                    'cover'   => '覆盖',
                    'contain' => '包含',
                    'fill'    => '拉伸',
                    'none'    => '无',
                ],
            ] );

            // 对齐方式
            $this->add_responsive_control( 'align', [
                'label'   => '对齐方式',
                'type'    => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left'   => [ 'title' => '左对齐', 'icon' => 'eicon-text-align-left' ],
                    'center' => [ 'title' => '居中',   'icon' => 'eicon-text-align-center' ],
                    'right'  => [ 'title' => '右对齐', 'icon' => 'eicon-text-align-right' ],
                ],
                'default' => 'center',
                'selectors' => [
                    '{{WRAPPER}}' => 'text-align: {{VALUE}};',
                ],
            ] );

            $this->end_controls_section();

            /* ─── 样式：标准状态 ─── */
            $this->start_controls_section( 'section_style_standard', [
                'label' => '样式 - 标准',
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ] );

            $this->add_control( 'std_opacity', [
                'label'      => '不透明度',
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ '%' ],
                'range'      => [ '%' => [ 'min' => 0, 'max' => 100 ] ],
                'default'    => [ 'unit' => '%', 'size' => 100 ],
                'selectors'  => [
                    '{{WRAPPER}}' => '--imgplus-default-opacity: {{SIZE}}/100;',
                ],
            ] );

            $this->add_control( 'std_filter', [
                'label'   => 'CSS 滤镜',
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'none',
                'options' => [
                    'none'       => '无',
                    'grayscale'  => '灰度',
                    'sepia'      => '复古',
                    'blur'       => '模糊',
                    'brightness' => '亮度',
                    'contrast'   => '对比度',
                    'saturate'   => '饱和度',
                ],
            ] );

            $this->end_controls_section();

            /* ─── 样式：悬停状态 ─── */
            $this->start_controls_section( 'section_style_hover', [
                'label' => '样式 - 悬停',
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ] );

            $this->add_control( 'hover_opacity', [
                'label'      => '不透明度',
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ '%' ],
                'range'      => [ '%' => [ 'min' => 0, 'max' => 100 ] ],
                'default'    => [ 'unit' => '%', 'size' => 100 ],
                'selectors'  => [
                    '{{WRAPPER}}' => '--imgplus-hover-opacity: {{SIZE}}/100;',
                ],
            ] );

            $this->add_control( 'hover_filter', [
                'label'   => 'CSS 滤镜',
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'none',
                'options' => [
                    'none'       => '无',
                    'grayscale'  => '灰度',
                    'sepia'      => '复古',
                    'blur'       => '模糊',
                    'brightness' => '亮度',
                    'contrast'   => '对比度',
                    'saturate'   => '饱和度',
                ],
            ] );

            $this->add_control( 'transition_duration', [
                'label'      => '过渡时间 (s)',
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 's' ],
                'range'      => [ 's' => [ 'min' => 0, 'max' => 2, 'step' => 0.1 ] ],
                'default'    => [ 'unit' => 's', 'size' => 0.4 ],
            ] );

            $this->add_control( 'hover_animation', [
                'label'   => '悬停动画',
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'fade',
                'options' => [
                    'none'     => '无',
                    'fade'     => '淡入淡出',
                    'slide_up'   => '上滑',
                    'slide_down' => '下滑',
                    'zoom'    => '放大',
                    'zoomout' => '缩小',
                    'rotate'  => '旋转',
                ],
            ] );

            $this->end_controls_section();

            /* ─── 样式：边框与阴影 ─── */
            $this->start_controls_section( 'section_style_border', [
                'label' => '边框与阴影',
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ] );

            $this->add_control( 'border_type', [
                'label'   => '边框类型',
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'none',
                'options' => [
                    'none'   => '无',
                    'solid'  => '实线',
                    'dashed' => '虚线',
                    'dotted' => '点线',
                    'double' => '双线',
                ],
            ] );

            $this->add_control( 'border_color', [
                'label'     => '边框颜色',
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#000000',
                'condition' => [ 'border_type!' => 'none' ],
            ] );

            $this->add_control( 'border_width', [
                'label'      => '边框宽度 (px)',
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 20 ] ],
                'default'    => [ 'unit' => 'px', 'size' => 1 ],
                'condition'  => [ 'border_type!' => 'none' ],
            ] );

            $this->add_control( 'border_radius', [
                'label'   => '圆角',
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'none',
                'options' => [
                    'none' => '无',
                    'sm'   => '小 (4px)',
                    'md'   => '中 (10px)',
                    'lg'   => '大 (20px)',
                    'full' => '圆形',
                ],
            ] );

            $this->add_control( 'box_shadow', [
                'label'   => '盒阴影',
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'none',
                'options' => [
                    'none' => '无',
                    'sm'   => '小',
                    'md'   => '中',
                    'lg'   => '大',
                    'xl'   => '特大',
                ],
            ] );

            $this->end_controls_section();
        }

        protected function render() {
            $settings = $this->get_settings_for_display();

            $image_default = $settings['image_default'] ?? [];
            $image_hover  = $settings['image_hover'] ?? [];
            $default_url  = $image_default['url'] ?? '';
            $hover_url    = $image_hover['url'] ?? '';

            // 链接处理（支持动态标签）
            $link     = $settings['image_link'] ?? [];
            $has_link = ! empty( $link['url'] );
            $target   = ! empty($link['is_external']) ? ' target="_blank"' : '';
            $nofollow = ! empty($link['nofollow']) ? ' rel="nofollow"' : '';

            // 尺寸
            $width      = $settings['width'] ?? [ 'size' => 40, 'unit' => 'px' ];
            $height     = $settings['height'] ?? [ 'size' => 40, 'unit' => 'px' ];
            $max_width  = $settings['max_width'] ?? [ 'size' => 100, 'unit' => 'px' ];
            $object_fit = $settings['object_fit'] ?? 'cover';

            // 标准状态
            $std_opacity = ($settings['std_opacity']['size'] ?? 100) / 100;
            $std_filter  = $settings['std_filter'] ?? 'none';

            // 悬停状态
            $hover_opacity = ($settings['hover_opacity']['size'] ?? 100) / 100;
            $hover_filter  = $settings['hover_filter'] ?? 'none';
            $transition     = $settings['transition_duration']['size'] ?? 0.4;
            $hover_anim     = $settings['hover_animation'] ?? 'fade';

            // 边框
            $border_type   = $settings['border_type'] ?? 'none';
            $border_color  = $settings['border_color'] ?? '#000000';
            $border_width  = $settings['border_width']['size'] ?? 1;
            $border_radius = $settings['border_radius'] ?? 'none';
            $box_shadow    = $settings['box_shadow'] ?? 'none';

            // 构建类名
            $container_classes = [
                'imgplus-container',
                'imgplus-object-' . $object_fit,
                'imgplus-filter-' . $std_filter,
                'imgplus-filter-hover-' . $hover_filter,
            ];

            // 过渡时间
            if ($transition >= 1) {
                $container_classes[] = 'imgplus-transition-1';
            } elseif ($transition >= 0.8) {
                $container_classes[] = 'imgplus-transition-08';
            } elseif ($transition >= 0.6) {
                $container_classes[] = 'imgplus-transition-06';
            } elseif ($transition >= 0.4) {
                $container_classes[] = 'imgplus-transition-04';
            } elseif ($transition >= 0.2) {
                $container_classes[] = 'imgplus-transition-02';
            } else {
                $container_classes[] = 'imgplus-transition-0';
            }

            // 悬停动画
            if ($hover_anim !== 'none') {
                $container_classes[] = 'imgplus-hover-' . $hover_anim;
            }

            // 边框
            $container_classes[] = $border_type !== 'none'
                ? 'imgplus-border-' . $border_type
                : 'imgplus-border-none';

            // 圆角
            $container_classes[] = 'imgplus-radius-' . $border_radius;

            // 阴影
            $container_classes[] = 'imgplus-shadow-' . $box_shadow;

            // 容器样式
            $container_style = '';
            $width_size  = floatval($width['size'] ?? 40);
            $width_unit  = $width['unit'] ?? 'px';
            $height_size = floatval($height['size'] ?? 40);
            $height_unit = $height['unit'] ?? 'px';

            $container_style .= 'width: ' . $width_size . $width_unit . '; ';
            $container_style .= 'height: ' . $height_size . $height_unit . '; ';

            if (!empty($max_width['size']) && $max_width['size'] > 0) {
                $container_style .= 'max-width: ' . floatval($max_width['size']) . ($max_width['unit'] ?? 'px') . '; ';
            }

            if ($border_type !== 'none') {
                $container_style .= 'border: ' . intval($border_width) . 'px ' . $border_type . ' ' . esc_attr($border_color) . '; ';
            }
            $container_style .= 'transition-duration: ' . floatval($transition) . 's; ';

            ?>
            <div class="imgplus-wrapper"
                 data-default-opacity="<?php echo esc_attr($std_opacity); ?>"
                 data-hover-opacity="<?php echo esc_attr($hover_opacity); ?>">
                <?php if ($has_link) : ?>
                <a href="<?php echo esc_url($link['url']); ?>"<?php echo $target . $nofollow; ?> class="imgplus-link">
                <?php endif; ?>

                    <div class="<?php echo implode(' ', array_map('esc_attr', $container_classes)); ?>"
                         style="<?php echo esc_attr($container_style); ?>">

                        <?php if ($default_url) : ?>
                        <img src="<?php echo esc_url($default_url); ?>"
                             class="imgplus-image imgplus-image-default"
                             loading="lazy" alt="">
                        <?php endif; ?>

                        <?php if ($hover_url) : ?>
                        <img src="<?php echo esc_url($hover_url); ?>"
                             class="imgplus-image imgplus-image-hover"
                             loading="lazy" alt="">
                        <?php endif; ?>

                    </div>

                <?php if ($has_link) : ?>
                </a>
                <?php endif; ?>
            </div>
            <?php
        }

        protected function content_template() {
            ?>
            <#
            var width = settings.width || { size: 40, unit: 'px' };
            var height = settings.height || { size: 40, unit: 'px' };
            var maxWidth = settings.max_width || { size: 100, unit: 'px' };
            var objectFit = settings.object_fit || 'cover';
            var stdFilter = settings.std_filter || 'none';
            var hoverFilter = settings.hover_filter || 'none';
            var transition = settings.transition_duration.size || 0.4;
            var hoverAnim = settings.hover_animation || 'fade';
            var borderType = settings.border_type || 'none';
            var borderRadius = settings.border_radius || 'none';
            var boxShadow = settings.box_shadow || 'none';

            var containerClasses = [
                'imgplus-container',
                'imgplus-object-' + objectFit,
                'imgplus-filter-' + stdFilter,
                'imgplus-filter-hover-' + hoverFilter,
            ];

            if (transition >= 1) containerClasses.push('imgplus-transition-1');
            else if (transition >= 0.8) containerClasses.push('imgplus-transition-08');
            else if (transition >= 0.6) containerClasses.push('imgplus-transition-06');
            else if (transition >= 0.4) containerClasses.push('imgplus-transition-04');
            else if (transition >= 0.2) containerClasses.push('imgplus-transition-02');
            else containerClasses.push('imgplus-transition-0');

            if (hoverAnim !== 'none') {
                containerClasses.push('imgplus-hover-' + hoverAnim);
            }

            if (borderType !== 'none') {
                containerClasses.push('imgplus-border-' + borderType);
            } else {
                containerClasses.push('imgplus-border-none');
            }

            containerClasses.push('imgplus-radius-' + borderRadius);
            containerClasses.push('imgplus-shadow-' + boxShadow);

            var containerStyle = '';
            containerStyle += 'width:' + (width.size || 40) + (width.unit || 'px') + '; ';
            containerStyle += 'height:' + (height.size || 40) + (height.unit || 'px') + '; ';
            if (maxWidth && maxWidth.size > 0) {
                containerStyle += 'max-width:' + maxWidth.size + (maxWidth.unit || 'px') + '; ';
            }
            if (borderType !== 'none') {
                containerStyle += 'border:' + (settings.border_width.size || 1) + 'px ' + borderType + ' ' + (settings.border_color || '#000000') + '; ';
            }
            containerStyle += 'transition-duration:' + transition + 's;';
            #>

            <div class="imgplus-wrapper">
                <# if (settings.image_link && settings.image_link.url) { #>
                <a href="{{ settings.image_link.url }}" class="imgplus-link"<# if (settings.image_link.is_external) { #> target="_blank"<# } #><# if (settings.image_link.nofollow) { #> rel="nofollow"<# } #>>
                <# } #>

                    <div class="{{ containerClasses.join(' ') }}" style="{{ containerStyle }}">
                        <# if (settings.image_default.url) { #>
                        <img src="{{ settings.image_default.url }}" class="imgplus-image imgplus-image-default" loading="lazy" alt="">
                        <# } #>
                        <# if (settings.image_hover.url) { #>
                        <img src="{{ settings.image_hover.url }}" class="imgplus-image imgplus-image-hover" loading="lazy" alt="">
                        <# } #>
                    </div>

                <# if (settings.image_link && settings.image_link.url) { #>
                </a>
                <# } #>
            </div>
            <?php
        }
    }

    $widgets_manager->register( new Image_Plus_Widget() );
});