<?php
/**
 * ============================================
 * Zizo Category Tabs - PHP主程序
 * ============================================
 *
 * @version     1.0.1
 * @date        2026-07-08
 * @author      Aloysius Luo
 * @organization TTTWORKS
 * @contact     https://tttworks.com
 * @description 分类列表 Tab，展示指定分类的子分类，支持手动添加和动态查询
 *
 * --------------------------------------------
 * 版本更新日志
 * --------------------------------------------
 * v1.0.1 (2026-07-08)
 *   [增] 动态查询模式下新增排序功能：默认/按名称升序/按名称降序/自定义排序
 *   [增] 自定义排序控件：SELECT2 逐条选择子分类，支持拖拽排列
 * v1.0.0 (2026-06-22)
 *   [新] 初始版本，支持横排/竖排布局、手动添加和动态查询模式
 * --------------------------------------------
 */

add_action('elementor/widgets/register', function ( $widgets_manager ) {
    if ( ! class_exists( '\Elementor\Widget_Base' ) ) return;
    if ( ! class_exists( 'Zizo_Category_Tabs' ) ) {
        class Zizo_Category_Tabs extends \Elementor\Widget_Base {

            public function get_name() {
                return 'zizo_category_tabs';
            }

            public function get_title() {
                return 'Zizo Category Tabs';
            }

            public function get_icon() {
                return 'eicon-tabs';
            }

            public function get_categories() {
                return [ 'general' ];
            }

            public function get_keywords() {
                return [ 'category', 'tabs', 'zizo', '分类' ];
            }

            public function get_style_depends() {
                return [ 'zizo-category-tabs-css' ];
            }

            public function get_script_depends() {
                return [ 'zizo-category-tabs-js' ];
            }

            protected function register_controls() {
                // 内容选项卡
                $this->start_controls_section(
                    'section_content',
                    [
                        'label' => '内容选项',
                        'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
                    ]
                );

                // 数据源选择
                $this->add_control(
                    'data_source',
                    [
                        'label'   => '数据源',
                        'type'    => \Elementor\Controls_Manager::SELECT,
                        'default' => 'dynamic',
                        'options' => [
                            'dynamic' => '动态查询',
                            'manual'  => '手动添加',
                        ],
                    ]
                );

                // Term 类型
                $this->add_control(
                    'term_type',
                    [
                        'label'     => 'Term 类型',
                        'type'      => \Elementor\Controls_Manager::SELECT,
                        'default'   => 'category',
                        'options'   => [
                            'category'  => '分类（Category）',
                            'post_tag'  => '标签（Tag）',
                        ],
                        'condition' => [
                            'data_source' => 'dynamic',
                        ],
                    ]
                );

                // 父级分类选择
                $this->add_control(
                    'parent_category',
                    [
                        'label'     => '父级分类',
                        'type'      => \Elementor\Controls_Manager::SELECT2,
                        'label_block' => true,
                        'multiple'  => false,
                        'options'   => $this->get_categories_options(),
                        'condition' => [
                            'data_source' => 'dynamic',
                            'term_type'   => 'category',
                        ],
                    ]
                );

                // 显示层级深度
                $this->add_control(
                    'depth',
                    [
                        'label'     => '显示层级深度',
                        'type'      => \Elementor\Controls_Manager::NUMBER,
                        'min'       => 1,
                        'max'       => 10,
                        'step'      => 1,
                        'default'   => 1,
                        'condition' => [
                            'data_source' => 'dynamic',
                        ],
                    ]
                );

                // 排序方式
                $this->add_control(
                    'sort_by',
                    [
                        'label'     => '排序方式',
                        'type'      => \Elementor\Controls_Manager::SELECT,
                        'default'   => 'default',
                        'options'   => [
                            'default'    => '默认（WordPress 原始顺序）',
                            'name_asc'   => '按名称升序（A→Z）',
                            'name_desc'  => '按名称降序（Z→A）',
                            'custom'     => '自定义排序',
                        ],
                        'condition' => [
                            'data_source' => 'dynamic',
                        ],
                    ]
                );

                // 自定义排序 Repeater
                $custom_order_repeater = new \Elementor\Repeater();

                $custom_order_repeater->add_control(
                    'category_id',
                    [
                        'label'       => '选择子分类',
                        'type'        => \Elementor\Controls_Manager::SELECT2,
                        'label_block' => true,
                        'options'     => $this->get_categories_options(),
                    ]
                );

                $this->add_control(
                    'custom_order',
                    [
                        'label'       => '自定义排序列表',
                        'description' => '按先后顺序排列子分类，拖拽调整顺序。未出现在列表中的子分类将按原始顺序排在末尾。',
                        'type'        => \Elementor\Controls_Manager::REPEATER,
                        'fields'      => $custom_order_repeater->get_controls(),
                        'title_field' => '{{{ category_id }}}',
                        'condition'   => [
                            'data_source' => 'dynamic',
                            'sort_by'     => 'custom',
                        ],
                    ]
                );

                // 手动添加 Repeater
                $repeater = new \Elementor\Repeater();

                $repeater->add_control(
                    'tab_title',
                    [
                        'label'       => '分类名称',
                        'type'        => \Elementor\Controls_Manager::TEXT,
                        'default'     => '分类名称',
                        'label_block' => true,
                    ]
                );

                $repeater->add_control(
                    'tab_link',
                    [
                        'label'       => '链接地址',
                        'type'        => \Elementor\Controls_Manager::URL,
                        'placeholder' => 'https://',
                        'default'     => [
                            'url' => '#',
                        ],
                    ]
                );

                $this->add_control(
                    'tabs_list',
                    [
                        'label'       => '分类列表',
                        'type'        => \Elementor\Controls_Manager::REPEATER,
                        'fields'      => $repeater->get_controls(),
                        'default'     => [
                            [
                                'tab_title' => '分类 1',
                                'tab_link'  => ['url' => '#'],
                            ],
                        ],
                        'title_field' => '{{{ tab_title }}}',
                        'condition'   => [
                            'data_source' => 'manual',
                        ],
                    ]
                );

                $this->end_controls_section();

                // 布局选项
                $this->start_controls_section(
                    'section_layout',
                    [
                        'label' => '布局选项',
                        'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
                    ]
                );

                $this->add_control(
                    'layout_direction',
                    [
                        'label'   => '排列方向',
                        'type'    => \Elementor\Controls_Manager::SELECT,
                        'default' => 'row',
                        'options' => [
                            'row'    => '横排',
                            'column' => '竖排',
                        ],
                    ]
                );

                $this->add_control(
                    'wrap_enabled',
                    [
                        'label'        => '允许换行',
                        'type'         => \Elementor\Controls_Manager::SWITCHER,
                        'label_on'     => '是',
                        'label_off'    => '否',
                        'return_value' => 'yes',
                        'default'      => 'yes',
                    ]
                );

                $this->add_control(
                    'alignment',
                    [
                        'label'   => '对齐方式',
                        'type'    => \Elementor\Controls_Manager::SELECT,
                        'default' => 'center',
                        'options' => [
                            'flex-start'    => '左对齐',
                            'center'       => '居中',
                            'flex-end'     => '右对齐',
                            'space-between' => '两端对齐',
                        ],
                    ]
                );

                $this->end_controls_section();

                // 容器样式
                $this->start_controls_section(
                    'section_style_container',
                    [
                        'label' => '容器样式',
                        'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
                    ]
                );

                $this->add_control(
                    'container_gap',
                    [
                        'label'      => '元素间距',
                        'type'       => \Elementor\Controls_Manager::SLIDER,
                        'size_units' => [ 'px', 'em', 'rem' ],
                        'range'      => [
                            'px'  => ['min' => 0, 'max' => 50],
                            'em'  => ['min' => 0, 'max' => 5],
                            'rem' => ['min' => 0, 'max' => 5],
                        ],
                        'default' => [
                            'size' => 10,
                            'unit' => 'px',
                        ],
                        'selectors' => [
                            '{{WRAPPER}} .zizo-tabs-heading' => 'gap: {{SIZE}}{{UNIT}};',
                        ],
                    ]
                );

                $this->end_controls_section();

                // 按钮样式
                $this->start_controls_section(
                    'section_style_button',
                    [
                        'label' => '按钮样式',
                        'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
                    ]
                );

                // 默认状态
                $this->start_controls_tabs( 'tabs_button_colors' );

                $this->start_controls_tab(
                    'tab_button_default',
                    ['label' => '默认']
                );

                $this->add_control(
                    'button_bg_default',
                    [
                        'label'     => '背景颜色',
                        'type'      => \Elementor\Controls_Manager::COLOR,
                        'default'   => '#F6F6F9',
                        'selectors' => [
                            '{{WRAPPER}} .zizo-tab-title' => 'background-color: {{VALUE}};',
                        ],
                    ]
                );

                $this->add_control(
                    'button_color_default',
                    [
                        'label'     => '文字颜色',
                        'type'      => \Elementor\Controls_Manager::COLOR,
                        'default'   => '#333333',
                        'selectors' => [
                            '{{WRAPPER}} .zizo-tab-title' => 'color: {{VALUE}};',
                            '{{WRAPPER}} .zizo-tab-title a' => 'color: {{VALUE}};',
                        ],
                    ]
                );

                $this->end_controls_tab();

                // 悬停状态
                $this->start_controls_tab(
                    'tab_button_hover',
                    ['label' => '悬停']
                );

                $this->add_control(
                    'button_bg_hover',
                    [
                        'label'     => '背景颜色',
                        'type'      => \Elementor\Controls_Manager::COLOR,
                        'default'   => '#59A498',
                        'selectors' => [
                            '{{WRAPPER}} .zizo-tab-title:hover' => 'background-color: {{VALUE}};',
                        ],
                    ]
                );

                $this->add_control(
                    'button_color_hover',
                    [
                        'label'     => '文字颜色',
                        'type'      => \Elementor\Controls_Manager::COLOR,
                        'default'   => '#FFFFFF',
                        'selectors' => [
                            '{{WRAPPER}} .zizo-tab-title:hover' => 'color: {{VALUE}};',
                            '{{WRAPPER}} .zizo-tab-title:hover a' => 'color: {{VALUE}};',
                        ],
                    ]
                );

                $this->end_controls_tab();

                // 激活状态
                $this->start_controls_tab(
                    'tab_button_active',
                    ['label' => '当前分类']
                );

                $this->add_control(
                    'button_bg_active',
                    [
                        'label'     => '背景颜色',
                        'type'      => \Elementor\Controls_Manager::COLOR,
                        'default'   => '#59A498',
                        'selectors' => [
                            '{{WRAPPER}} .zizo-tab-title.is-active' => 'background-color: {{VALUE}};',
                        ],
                    ]
                );

                $this->add_control(
                    'button_color_active',
                    [
                        'label'     => '文字颜色',
                        'type'      => \Elementor\Controls_Manager::COLOR,
                        'default'   => '#FFFFFF',
                        'selectors' => [
                            '{{WRAPPER}} .zizo-tab-title.is-active' => 'color: {{VALUE}};',
                            '{{WRAPPER}} .zizo-tab-title.is-active a' => 'color: {{VALUE}};',
                        ],
                    ]
                );

                $this->end_controls_tab();
                $this->end_controls_tabs();

                // 按钮尺寸
                $this->add_control(
                    'heading_button_size',
                    [
                        'type'      => \Elementor\Controls_Manager::HEADING,
                        'label'     => '按钮尺寸',
                        'separator' => 'before',
                    ]
                );

                $this->add_responsive_control(
                    'button_padding',
                    [
                        'label'      => '内边距',
                        'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                        'size_units' => [ 'px', 'em' ],
                        'default'    => [
                            'top'    => 14,
                            'right'  => 30,
                            'bottom' => 14,
                            'left'   => 30,
                        ],
                        'selectors'  => [
                            '{{WRAPPER}} .zizo-tab-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                        ],
                    ]
                );

                $this->add_control(
                    'button_border_radius',
                    [
                        'label'      => '圆角',
                        'type'       => \Elementor\Controls_Manager::SLIDER,
                        'size_units' => [ 'px', '%' ],
                        'range'      => [
                            'px' => ['min' => 0, 'max' => 100],
                            '%'  => ['min' => 0, 'max' => 50],
                        ],
                        'default' => [
                            'size' => 50,
                            'unit' => 'px',
                        ],
                        'selectors' => [
                            '{{WRAPPER}} .zizo-tab-title' => 'border-radius: {{SIZE}}{{UNIT}};',
                        ],
                    ]
                );

                // 字体样式
                $this->add_control(
                    'heading_button_typography',
                    [
                        'type'      => \Elementor\Controls_Manager::HEADING,
                        'label'     => '字体样式',
                        'separator' => 'before',
                    ]
                );

                $this->add_group_control(
                    \Elementor\Group_Control_Typography::get_type(),
                    [
                        'name'     => 'button_typography',
                        'label'    => '字体',
                        'selector' => '{{WRAPPER}} .zizo-tab-title,
                                       {{WRAPPER}} .zizo-tab-title a',
                    ]
                );

                $this->end_controls_section();
            }

            /**
             * 获取所有分类选项（供 parent_category 控件使用）
             */
            private function get_categories_options() {
                $options = [];
                $categories = get_categories([
                    'hide_empty' => false,
                    'hierarchical' => true,
                    'taxonomy' => 'category',
                ]);

                $excluded_slugs = ['elementor_library_category', 'elementor_library'];

                foreach ( $categories as $category ) {
                    if ( in_array( $category->slug, $excluded_slugs ) ) {
                        continue;
                    }
                    $options[ $category->term_id ] = $category->name;
                }

                return $options;
            }

            private function get_current_category_id() {
                if ( is_category() ) {
                    return get_queried_object_id();
                }
                return 0;
            }

            protected function render() {
                $settings = $this->get_settings_for_display();
                $current_category_id = $this->get_current_category_id();

                $tabs = [];

                if ( $settings['data_source'] === 'dynamic' ) {
                    $tabs = $this->get_dynamic_tabs( $settings, $current_category_id );
                } else {
                    $tabs = $this->get_manual_tabs( $settings, $current_category_id );
                }

                if ( empty( $tabs ) ) {
                    if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                        echo '<p style="text-align:center;color:#999;">暂无分类数据，请检查设置</p>';
                    }
                    return;
                }

                $container_style = 'display: flex; ';
                $container_style .= 'flex-direction: ' . esc_attr( $settings['layout_direction'] ) . '; ';
                $container_style .= 'justify-content: ' . esc_attr( $settings['alignment'] ) . '; ';

                if ( $settings['wrap_enabled'] === 'yes' ) {
                    $container_style .= 'flex-wrap: wrap; ';
                } else {
                    $container_style .= 'flex-wrap: nowrap; ';
                }

                ?>
                <div class="zizo-tabs-heading" style="<?php echo esc_attr( $container_style ); ?>">
                    <?php foreach ( $tabs as $tab ) :
                        $is_active = isset( $tab['is_active'] ) && $tab['is_active'];
                        $active_class = $is_active ? 'is-active' : '';
                        $tab_link = ! empty( $tab['link'] ) ? esc_url( $tab['link'] ) : '#';
                    ?>
                        <button
                            id="zizo-tab-title-<?php echo esc_attr( $tab['id'] ); ?>"
                            class="zizo-tab-title <?php echo esc_attr( $active_class ); ?>"
                            data-tab-index="<?php echo esc_attr( $tab['index'] ); ?>"
                            role="tab"
                            tabindex="<?php echo $is_active ? '0' : '-1'; ?>"
                            aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
                        >
                            <a href="<?php echo esc_url( $tab_link ); ?>">
                                <?php echo esc_html( $tab['title'] ); ?>
                            </a>
                        </button>
                    <?php endforeach; ?>
                </div>
                <?php
            }

            private function get_dynamic_tabs( $settings, $current_category_id ) {
                $tabs = [];
                $term_type = $settings['term_type'] ?? 'category';
                $parent_id = $settings['parent_category'] ?? 0;
                $depth = intval( $settings['depth'] ?? 1 );
                $sort_by = $settings['sort_by'] ?? 'default';

                if ( $parent_id <= 0 ) {
                    return $tabs;
                }

                $taxonomy = $term_type === 'category' ? 'category' : 'post_tag';

                $children = get_categories([
                    'hide_empty'   => false,
                    'hierarchical' => true,
                    'taxonomy'     => $taxonomy,
                    'parent'       => $parent_id,
                ]);

                // === 排序逻辑 ===
                switch ( $sort_by ) {
                    case 'name_asc':
                        usort( $children, function( $a, $b ) {
                            return strcasecmp( $a->name, $b->name );
                        });
                        break;

                    case 'name_desc':
                        usort( $children, function( $a, $b ) {
                            return strcasecmp( $b->name, $a->name );
                        });
                        break;

                    case 'custom':
                        $custom_order = $settings['custom_order'] ?? [];
                        if ( ! empty( $custom_order ) ) {
                            // 提取自定义排序中的 term_id 列表，保持顺序
                            $custom_ids = [];
                            foreach ( $custom_order as $item ) {
                                $cid = $item['category_id'] ?? '';
                                if ( $cid !== '' ) {
                                    $custom_ids[] = (int) $cid;
                                }
                            }

                            if ( ! empty( $custom_ids ) ) {
                                // 以 term_id 为 key 重建 children 索引
                                $indexed = [];
                                foreach ( $children as $child ) {
                                    $indexed[ $child->term_id ] = $child;
                                }

                                // 按自定义顺序构建结果
                                $sorted = [];
                                $used = [];

                                foreach ( $custom_ids as $cid ) {
                                    if ( isset( $indexed[ $cid ] ) ) {
                                        $sorted[] = $indexed[ $cid ];
                                        $used[ $cid ] = true;
                                    }
                                }

                                // 未在自定义列表中的追加到末尾
                                foreach ( $children as $child ) {
                                    if ( ! isset( $used[ $child->term_id ] ) ) {
                                        $sorted[] = $child;
                                    }
                                }

                                $children = $sorted;
                            }
                        }
                        break;

                    default:
                        // 保持 WordPress 原生顺序
                        break;
                }

                $index = 1;

                foreach ( $children as $child ) {
                    $is_active = ( $child->term_id == $current_category_id );
                    $tabs[] = [
                        'id'        => $child->term_id,
                        'index'     => $index++,
                        'title'     => $child->name,
                        'link'      => get_category_link( $child->term_id ),
                        'is_active' => $is_active,
                    ];
                }

                return $tabs;
            }

            private function get_manual_tabs( $settings, $current_category_id ) {
                $tabs = [];
                $tabs_list = $settings['tabs_list'] ?? [];

                if ( empty( $tabs_list ) ) {
                    return $tabs;
                }

                $index = 1;
                foreach ( $tabs_list as $item ) {
                    $url = $item['tab_link']['url'] ?? '#';
                    $tabs[] = [
                        'id'        => $index,
                        'index'     => $index,
                        'title'     => $item['tab_title'] ?? '',
                        'link'      => $url,
                        'is_active' => false,
                    ];
                    $index++;
                }

                return $tabs;
            }

            protected function content_template() {
                ?>
                <#
                var containerStyle = 'display: flex; flex-direction: ' + settings.layout_direction + '; justify-content: ' + settings.alignment + ';';
                if ( settings.wrap_enabled === 'yes' ) {
                    containerStyle += ' flex-wrap: wrap;';
                } else {
                    containerStyle += ' flex-wrap: nowrap;';
                }
                #>
                <div class="zizo-tabs-heading" style="{{{ containerStyle }}}">
                    <# if ( settings.data_source === 'manual' && settings.tabs_list.length ) { #>
                        <# _.each( settings.tabs_list, function( item, index ) { #>
                            <button
                                id="zizo-tab-title-{{{ index + 1 }}}"
                                class="zizo-tab-title"
                                data-tab-index="{{{ index + 1 }}}"
                                role="tab"
                                tabindex="-1"
                                aria-selected="false"
                            >
                                <a href="{{{ item.tab_link.url || '#' }}}">
                                    {{{ item.tab_title }}}
                                </a>
                            </button>
                        <# } ); #>
                    <# } else { #>
                        <button class="zizo-tab-title">子分类 1</button>
                        <button class="zizo-tab-title">子分类 2</button>
                        <button class="zizo-tab-title">子分类 3</button>
                    <# } #>
                </div>
                <?php
            }
        }
    }
    $widgets_manager->register( new Zizo_Category_Tabs() );
} );
