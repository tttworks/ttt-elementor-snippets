/**
 * ============================================
 * TTT Simple List from Menu - PHP 主程序
 * ============================================
 *
 * @version     1.0.0
 * @date        2026-06-27
 * @author      Aloysius Luo
 * @organization TTTWORKS
 * @contact     https://tttworks.com
 * @description Elementor 菜单链接列表组件 — 调用 WordPress 菜单输出文字链接列表，支持横竖排列、颜色/排版/间距控制
 *
 * --------------------------------------------
 * 版本更新日志
 * --------------------------------------------
 * v1.0.0 (2026-06-27)
 *   [新] 初始版本 — 菜单选择、横竖排列（默认竖排）、文字样式（颜色/排版/装饰/间距）、普通+悬停状态
 *        slug: ttt-menu-list, class 前缀: zml-
 * ============================================
 */

add_action( 'elementor/widgets/register', function ( $widgets_manager ) {

    if ( ! class_exists( '\Elementor\Widget_Base' ) ) return;

    if ( ! class_exists( 'TTT_Menu_List_Widget' ) ) {

    class TTT_Menu_List_Widget extends \Elementor\Widget_Base {

        public function get_name()           { return 'ttt_menu_list'; }
        public function get_title()          { return 'TTT Menu List'; }
        public function get_icon()           { return 'eicon-bullet-list'; }
        public function get_categories()     { return [ 'general' ]; }
        public function get_keywords()       { return [ 'menu', 'list', 'link', 'nav', 'ttt' ]; }
        public function get_style_depends()  { return [ 'ttt-menu-list' ]; }
        public function get_script_depends() { return [ 'ttt-menu-list' ]; }

        /* ── 获取所有 WordPress 菜单 ── */
        private function get_menu_options() {
            $menus   = wp_get_nav_menus( [ 'orderby' => 'name' ] );
            $options = [ '' => '— 请选择菜单 —' ];
            foreach ( $menus as $menu ) {
                $options[ $menu->term_id ] = $menu->name;
            }
            return $options;
        }

        protected function register_controls() {

            /* ── 数据来源 ── */
            $this->start_controls_section( 'section_source', [
                'label' => '数据来源',
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ] );
            $this->add_control( 'menu_id', [
                'label'   => '选择菜单',
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => '',
                'options' => $this->get_menu_options(),
            ] );
            $this->end_controls_section();

            /* ── 布局设置 ── */
            $this->start_controls_section( 'section_layout', [
                'label' => '布局设置',
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ] );

            $this->add_control( 'direction', [
                'label'   => '排列方向',
                'type'    => \Elementor\Controls_Manager::CHOOSE,
                'default' => 'column',
                'options' => [
                    'column' => [ 'title' => '垂直', 'icon' => 'eicon-editor-list-ul' ],
                    'row'    => [ 'title' => '水平', 'icon' => 'eicon-justify-space-between-h' ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .zml-wrapper' => 'flex-direction: {{VALUE}};',
                ],
                'toggle' => false,
            ] );

            $this->add_responsive_control( 'alignment', [
                'label'   => '文字对齐',
                'type'    => \Elementor\Controls_Manager::CHOOSE,
                'default' => 'left',
                'options' => [
                    'left'   => [ 'title' => '左', 'icon' => 'eicon-text-align-left' ],
                    'center' => [ 'title' => '中', 'icon' => 'eicon-text-align-center' ],
                    'right'  => [ 'title' => '右', 'icon' => 'eicon-text-align-right' ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .zml-item' => 'text-align: {{VALUE}};',
                ],
                'toggle' => false,
            ] );

            $this->add_responsive_control( 'item_gap', [
                'label'       => '项目间距',
                'type'        => \Elementor\Controls_Manager::SLIDER,
                'size_units'  => [ 'px', 'em' ],
                'range'       => [
                    'px' => [ 'min' => 0, 'max' => 100 ],
                    'em' => [ 'min' => 0, 'max' => 10 ],
                ],
                'default'         => [ 'unit' => 'px', 'size' => 12 ],
                'tablet_default'  => [ 'unit' => 'px', 'size' => 10 ],
                'mobile_default'  => [ 'unit' => 'px', 'size' => 8 ],
                'selectors'       => [
                    '{{WRAPPER}} .zml-wrapper' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ] );

            $this->end_controls_section();

            /* ── 菜单项样式 ── */
            $this->start_controls_section( 'section_style_item', [
                'label' => '菜单项样式',
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ] );

            /* 普通状态 */
            $this->add_control( 'item_normal_heading', [
                'label' => '普通状态',
                'type'  => \Elementor\Controls_Manager::HEADING,
            ] );

            $this->add_control( 'item_color', [
                'label'     => '文字颜色',
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#333333',
                'selectors' => [ '{{WRAPPER}} .zml-link' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'text_decoration', [
                'label'     => '文字装饰',
                'type'      => \Elementor\Controls_Manager::SELECT,
                'default'   => 'none',
                'options'   => [
                    'none'         => '无',
                    'underline'    => '下划线',
                    'overline'     => '上划线',
                    'line-through' => '删除线',
                ],
                'selectors' => [ '{{WRAPPER}} .zml-link' => 'text-decoration: {{VALUE}};' ],
            ] );

            /* 悬停状态 */
            $this->add_control( 'item_hover_heading', [
                'label'     => '悬停状态',
                'type'      => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ] );

            $this->add_control( 'item_color_hover', [
                'label'     => '悬停颜色',
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#0073aa',
                'selectors' => [ '{{WRAPPER}} .zml-link:hover' => 'color: {{VALUE}};' ],
            ] );

            $this->add_control( 'text_decoration_hover', [
                'label'     => '悬停文字装饰',
                'type'      => \Elementor\Controls_Manager::SELECT,
                'default'   => 'underline',
                'options'   => [
                    'none'         => '无',
                    'underline'    => '下划线',
                    'overline'     => '上划线',
                    'line-through' => '删除线',
                ],
                'selectors' => [ '{{WRAPPER}} .zml-link:hover' => 'text-decoration: {{VALUE}};' ],
            ] );

            /* 文字排版 */
            $this->add_control( 'typography_heading', [
                'label'     => '文字排版',
                'type'      => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ] );

            $this->add_group_control(
                \Elementor\Group_Control_Typography::get_type(),
                [
                    'name'     => 'item_typography',
                    'selector' => '{{WRAPPER}} .zml-link',
                    'fields_options' => [
                        'font_family' => [ 'default' => 'Montserrat' ],
                        'font_size'   => [ 'default' => [ 'unit' => 'px', 'size' => 16 ] ],
                        'font_weight' => [ 'default' => '500' ],
                        'line_height' => [ 'default' => [ 'unit' => 'em', 'size' => 1.5 ] ],
                    ],
                ]
            );

            /* 间距 */
            $this->add_control( 'spacing_heading', [
                'label'     => '间距设置',
                'type'      => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ] );

            $this->add_responsive_control( 'item_padding', [
                'label'      => '项目内边距',
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'default'    => [
                    'top' => '4', 'right' => '0', 'bottom' => '4', 'left' => '0',
                    'unit' => 'px', 'isLinked' => false,
                ],
                'tablet_default' => [
                    'top' => '4', 'right' => '0', 'bottom' => '4', 'left' => '0',
                    'unit' => 'px', 'isLinked' => false,
                ],
                'mobile_default' => [
                    'top' => '4', 'right' => '0', 'bottom' => '4', 'left' => '0',
                    'unit' => 'px', 'isLinked' => false,
                ],
                'selectors'  => [
                    '{{WRAPPER}} .zml-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ] );

            $this->end_controls_section();
        }

        /* ════════════════════ 前端 HTML ════════════════════ */
        protected function render() {

            $settings = $this->get_settings_for_display();
            $menu_id  = $settings['menu_id'] ?? '';

            if ( empty( $menu_id ) ) {
                echo '<p style="color:#999;padding:20px;">请在左侧面板选择一个 WordPress 菜单。</p>';
                return;
            }

            $items = wp_get_nav_menu_items( $menu_id );
            if ( empty( $items ) || is_wp_error( $items ) ) {
                echo '<p style="color:#999;padding:20px;">所选菜单中没有项目。</p>';
                return;
            }

            echo '<div class="zml-wrapper">';
            foreach ( $items as $item ) {
                $url     = $item->url ?? '';
                $title   = $item->title ?? '';
                $target  = ! empty( $item->target ) ? ' target="' . esc_attr( $item->target ) . '"' : '';
                $classes = '';
                if ( ! empty( $item->classes ) && is_array( $item->classes ) ) {
                    $classes = implode( ' ', array_map( 'esc_attr', $item->classes ) );
                }

                echo '<div class="zml-item">';
                echo '<a href="' . esc_url( $url ) . '" class="zml-link ' . esc_attr( $classes ) . '"' . $target . '>' . esc_html( $title ) . '</a>';
                echo '</div>';
            }
            echo '</div>';
        }
    }
    }
    $widgets_manager->register( new TTT_Menu_List_Widget() );
} );
