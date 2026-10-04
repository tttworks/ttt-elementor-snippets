/*
 * Snippet Name: Z Grid Card - Widget
 * Description: 独立的图文网格，支持整卡点击、原生响应式列数与高度、丰富的悬停遮罩效果。
 *
 * v1.2  2026-04-12
 *   [修] image_ratio 默认值改为 83/89，配合 CSS v1.3 的 absolute 图片定位
 *        确保 .zgc-img-wrap 始终有 aspect-ratio 撑高
 * v1.1  2026-04-12
 *   [增] container-type 控件、cqw 变量控件、遮罩拆分颜色+透明度
 * v1.0
 *   独立的图文网格基础版
 */

add_action( 'elementor/widgets/register', function ( $widgets_manager ) {
    if ( ! class_exists( '\Elementor\Widget_Base' ) ) return;

    class ZGridCard_Widget extends \Elementor\Widget_Base {

        public function get_name()           { return 'z_grid_card'; }
        public function get_title()          { return 'Z Grid Card'; }
        public function get_icon()           { return 'eicon-gallery-grid'; }
        public function get_categories()     { return [ 'general' ]; }
        public function get_keywords()       { return [ 'grid', 'card', 'image', 'quote' ]; }
        public function get_style_depends()  { return [ 'z-grid-card' ]; }
        public function get_script_depends() { return [ 'z-grid-card' ]; }

        protected function register_controls() {

            /* ─── 1. 内容：卡片列表 ─── */
            $this->start_controls_section( 'section_cards', [
                'label' => '图文卡片列表',
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ] );

            $repeater = new \Elementor\Repeater();
            $repeater->add_control( 'image', [
                'label'   => '卡片图片',
                'type'    => \Elementor\Controls_Manager::MEDIA,
                'default' => [ 'url' => \Elementor\Utils::get_placeholder_image_src() ],
            ] );
            $repeater->add_control( 'link', [
                'label'   => '整卡点击链接',
                'type'    => \Elementor\Controls_Manager::URL,
                'options' => [ 'url', 'is_external', 'nofollow' ],
                'default' => [ 'url' => '' ],
            ] );
            $repeater->add_control( 'quote', [
                'label'       => '文案内容',
                'type'        => \Elementor\Controls_Manager::TEXTAREA,
                'rows'        => 3,
                'placeholder' => 'Empower your journey...',
            ] );
            $repeater->add_control( 'name', [
                'label'       => '署名 (可选)',
                'type'        => \Elementor\Controls_Manager::TEXT,
                'placeholder' => 'To Our "Z" Members',
            ] );

            $this->add_control( 'cards', [
                'label'       => '卡片列表',
                'type'        => \Elementor\Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'default'     => [
                    [ 'quote' => 'Empower your journey — embrace your own path to wellness', 'name' => 'To Our "Z" Members' ],
                    [ 'quote' => 'This routine helped me feel energized and balanced...', 'name' => 'Sophia L.' ],
                ],
                'title_field' => '{{{ name ? name : "图文卡片" }}}',
            ] );

            $this->end_controls_section();

            /* ─── 2. 布局 & 结构 ─── */
            $this->start_controls_section( 'section_layout', [
                'label' => '布局 (原生断点支持)',
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ] );

            $this->add_responsive_control( 'columns', [
                'label'          => '网格列数',
                'type'           => \Elementor\Controls_Manager::NUMBER,
                'min'            => 1,
                'max'            => 6,
                'default'        => 3,
                'tablet_default' => 2,
                'mobile_default' => 1,
                'selectors'      => [
                    '{{WRAPPER}} .zgc-grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
                ],
            ] );

            $this->add_responsive_control( 'gap', [
                'label'      => '卡片间距 (px)',
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 100 ] ],
                'default'    => [ 'unit' => 'px', 'size' => 24 ],
                'selectors'  => [
                    '{{WRAPPER}} .zgc-grid' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ] );

            $this->add_responsive_control( 'image_ratio', [
                'label'          => '图片宽高比 (例如 1/1, 4/3) 空白代表无限制',
                'type'           => \Elementor\Controls_Manager::TEXT,
                'default'        => '83/89',
                'tablet_default' => '83/89',
                'mobile_default' => '83/89',
                'selectors'      => [
                    '{{WRAPPER}} .zgc-img-wrap' => 'aspect-ratio: {{VALUE}};',
                ],
            ] );

            $this->add_control( 'container_type', [
                'label'        => '容器查询（cqw 单位必须开启）',
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => '开启',
                'label_off'    => '关闭',
                'return_value' => 'inline-size',
                'default'      => 'inline-size',
                'selectors'    => [ '{{WRAPPER}} .zgc-img-wrap' => 'container-type: {{VALUE}};' ],
            ] );

            $this->add_responsive_control( 'image_fixed_height', [
                'label'      => '强制统一高度 (仅在不写宽高比时有效)',
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'vh' ],
                'range'      => [ 'px' => [ 'min' => 100, 'max' => 800 ] ],
                'selectors'  => [
                    '{{WRAPPER}} .zgc-img-wrap' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ] );

            $this->add_control( 'show_icon', [
                'label'        => '显示引号图标',
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => '显示',
                'label_off'    => '隐藏',
                'return_value' => 'yes',
                'default'      => 'yes',
            ] );

            $this->add_control( 'quote_icon', [
                'label'       => '自定义图标',
                'type'        => \Elementor\Controls_Manager::MEDIA,
                'media_types' => [ 'image', 'svg' ],
                'default'     => [ 'url' => 'https://placehold.co/64x64/png' ],
                'condition'   => [ 'show_icon' => 'yes' ],
            ] );

            $this->end_controls_section();

            /* ─── 3. 样式：遮罩 (Normal & Hover) ─── */
            $this->start_controls_section( 'section_style_mask', [
                'label' => '遮罩效果',
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ] );

            $this->start_controls_tabs( 'tabs_mask_style' );

            // -- 默认状态 --
            $this->start_controls_tab( 'tab_mask_normal', [ 'label' => '默认状态' ] );

            $this->add_control( 'mask_bg', [
                'label'     => '遮罩背景色',
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#231515',
                'selectors' => [ '{{WRAPPER}} .zgc-img-wrap' => '--zgc-mask-bg: {{VALUE}};' ],
            ] );
            $this->add_responsive_control( 'mask_opacity', [
                'label'      => '遮罩透明度',
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 100 ] ],
                'default'    => [ 'unit' => 'px', 'size' => 60 ],
                'selectors'  => [ '{{WRAPPER}} .zgc-img-wrap' => '--zgc-mask-opacity: {{SIZE}};' ],
            ] );
            $this->add_responsive_control( 'mask_height', [
                'label'      => '遮罩高度',
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ '%', 'px' ],
                'default'    => [ 'unit' => '%', 'size' => 45 ],
                'selectors'  => [ '{{WRAPPER}} .zgc-mask' => 'height: {{SIZE}}{{UNIT}};' ],
            ] );
            $this->add_control( 'text_color', [
                'label'     => '文案颜色',
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#ffffff',
                'selectors' => [ '{{WRAPPER}} .zgc-quote' => 'color: {{VALUE}};' ],
            ] );
            $this->add_control( 'name_color', [
                'label'     => '署名颜色',
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#ffffff',
                'selectors' => [ '{{WRAPPER}} .zgc-name' => 'color: {{VALUE}};' ],
            ] );

            $this->end_controls_tab();

            // -- 悬停状态 --
            $this->start_controls_tab( 'tab_mask_hover', [ 'label' => '鼠标悬停' ] );

            $this->add_control( 'mask_bg_hover', [
                'label'     => '遮罩背景色 (悬停)',
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#00999e',
                'selectors' => [ '{{WRAPPER}} .zgc-img-wrap' => '--zgc-mask-bg-hover: {{VALUE}};' ],
            ] );
            $this->add_responsive_control( 'mask_opacity_hover', [
                'label'      => '遮罩透明度 (悬停)',
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 100 ] ],
                'default'    => [ 'unit' => 'px', 'size' => 80 ],
                'selectors'  => [ '{{WRAPPER}} .zgc-img-wrap' => '--zgc-mask-bg-hover-opacity: {{SIZE}};' ],
            ] );
            $this->add_responsive_control( 'mask_height_hover', [
                'label'      => '遮罩高度 (悬停)',
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ '%', 'px' ],
                'default'    => [ 'unit' => '%', 'size' => 55 ],
                'selectors'  => [ '{{WRAPPER}} .zgc-card:hover .zgc-mask' => 'height: {{SIZE}}{{UNIT}};' ],
            ] );
            $this->add_control( 'text_color_hover', [
                'label'     => '文案颜色 (悬停)',
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .zgc-card:hover .zgc-quote' => 'color: {{VALUE}};' ],
            ] );
            $this->add_control( 'name_color_hover', [
                'label'     => '署名颜色 (悬停)',
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .zgc-card:hover .zgc-name' => 'color: {{VALUE}};' ],
            ] );

            $this->end_controls_tab();
            $this->end_controls_tabs();

            $this->add_control( 'hr_mask', [ 'type' => \Elementor\Controls_Manager::DIVIDER ] );

            // 字体控制
            $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [
                'name'     => 'quote_typography',
                'label'    => '文案字体',
                'selector' => '{{WRAPPER}} .zgc-quote',
                'fields_options' => [
                    'typography' => [ 'default' => 'custom' ],
                ],
            ] );
            $this->add_responsive_control( 'quote_text_size', [
                'label'      => '文案字号 (cqw)',
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 1, 'max' => 15 ] ],
                'default'    => [ 'unit' => 'px', 'size' => 5.59 ],
                'selectors'  => [ '{{WRAPPER}} .zgc-img-wrap' => '--zgc-text-size: {{SIZE}}cqw;' ],
            ] );
            $this->add_responsive_control( 'quote_line_clamp', [
                'label'     => '文案最多显示行数',
                'type'      => \Elementor\Controls_Manager::NUMBER,
                'min'       => 1,
                'max'       => 10,
                'default'   => 3,
                'selectors' => [
                    '{{WRAPPER}} .zgc-quote' => '-webkit-line-clamp: {{VALUE}};',
                ],
            ] );
            $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [
                'name'     => 'name_typography',
                'label'    => '署名字体',
                'selector' => '{{WRAPPER}} .zgc-name',
                'fields_options' => [
                    'typography' => [ 'default' => 'custom' ],
                ],
            ] );
            $this->add_responsive_control( 'quote_name_size', [
                'label'      => '署名字号 (cqw)',
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 1, 'max' => 10 ] ],
                'default'    => [ 'unit' => 'px', 'size' => 4.39 ],
                'selectors'  => [ '{{WRAPPER}} .zgc-img-wrap' => '--zgc-name-size: {{SIZE}}cqw;' ],
            ] );

            $this->add_responsive_control( 'icon_size', [
                'label'      => '图标大小',
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 1, 'max' => 20 ] ],
                'default'    => [ 'unit' => 'px', 'size' => 8 ],
                'selectors'  => [
                    '{{WRAPPER}} .zgc-img-wrap' => '--zgc-icon-size: {{SIZE}}cqw;',
                ],
                'condition'  => [ 'show_icon' => 'yes' ],
            ] );

            $this->add_responsive_control( 'icon_margin_top', [
                'label'      => '图标上边距 (px)',
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 100 ] ],
                'default'    => [ 'unit' => 'px', 'size' => 20 ],
                'selectors'  => [
                    '{{WRAPPER}} .zgc-icon-margin-top' => 'margin-top: {{SIZE}}{{UNIT}};',
                ],
            ] );

            $this->end_controls_section();
        }

        protected function render() {
            $settings = $this->get_settings_for_display();
            $cards    = $settings['cards'] ?? [];

            if ( empty( $cards ) ) {
                echo '<p>请在左侧面板添加图文卡片。</p>';
                return;
            }

            $show_icon = $settings['show_icon'] === 'yes';
            $icon_url  = $settings['quote_icon']['url'] ?? '';

            echo '<div class="zgc-grid">';

            foreach ( $cards as $card ) {
                $img_id   = $card['image']['id'] ?? 0;
                $img_url  = $card['image']['url'] ?? '';
                $link_url = $card['link']['url'] ?? '';
                $target   = ! empty( $card['link']['is_external'] ) ? ' target="_blank"' : '';
                $nofollow = ! empty( $card['link']['nofollow'] ) ? ' rel="nofollow"' : '';

                $tag_open  = $link_url ? "<a href='" . esc_url( $link_url ) . "' {$target} {$nofollow} class='zgc-card'>" : "<div class='zgc-card'>";
                $tag_close = $link_url ? "</a>" : "</div>";

                echo $tag_open;
                echo '<div class="zgc-img-wrap">';

                if ( $img_id ) {
                    echo wp_get_attachment_image( $img_id, 'large', false, [ 'class' => 'zgc-img-el', 'loading' => 'lazy' ] );
                } elseif ( $img_url ) {
                    echo '<img src="' . esc_url( $img_url ) . '" class="zgc-img-el" loading="lazy" alt="">';
                }

                echo '<div class="zgc-mask">';

                if ( $show_icon && $icon_url ) {
                    echo '<div class="zgc-icon zgc-icon-margin-top"><img src="' . esc_url( $icon_url ) . '" alt=""></div>';
                }

                echo '<p class="zgc-quote">' . wp_kses_post( $card['quote'] ) . '</p>';

                if ( ! empty( $card['name'] ) ) {
                    echo '<div class="zgc-name">' . esc_html( $card['name'] ) . '</div>';
                }

                echo '</div>'; // .zgc-mask
                echo '</div>'; // .zgc-img-wrap
                echo $tag_close;
            }

            echo '</div>';
        }
    }

    $widgets_manager->register( new ZGridCard_Widget() );
} );