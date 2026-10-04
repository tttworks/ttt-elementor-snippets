/**
 * ============================================
 * TTT Post Navigation - PHP主程序
 * ============================================
 *
 * @version     1.2.0
 * @date        2026-06-25
 * @author      Aloysius Luo
 * @organization TTTWORKS
 * @contact     https://tttworks.com
 * @description 文章导航组件，用于单篇文章页面展示上一篇和下一篇导航
 *
 * --------------------------------------------
 * 版本更新日志
 * --------------------------------------------
 * v1.2.0 (2026-06-25)
 *   [改] 修正 HTML 结构为正确的行式布局
 * --------------------------------------------
 */

add_action('elementor/widgets/register', function ( $widgets_manager ) {
    if ( ! class_exists( '\Elementor\Widget_Base' ) ) return;
    if ( ! class_exists( 'TTT_Post_Navigation' ) ) {
        class TTT_Post_Navigation extends \Elementor\Widget_Base {

            public function get_name() {
                return 'ttt_post_navigation';
            }

            public function get_title() {
                return 'TTT Post Navigation';
            }

            public function get_icon() {
                return 'eicon-navigation-horizontal';
            }

            public function get_categories() {
                return [ 'general' ];
            }

            public function get_keywords() {
                return [ 'navigation', 'prev', 'next', 'ttt', '导航' ];
            }

            public function get_style_depends() {
                return [ 'ttt-post-navigation-css' ];
            }

            public function get_script_depends() {
                return [ 'ttt-post-navigation-js' ];
            }

            protected function register_controls() {
                // 内容选项
                $this->start_controls_section(
                    'section_content',
                    [
                        'label' => '内容选项',
                        'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
                    ]
                );

                // 上一篇标签文字
                $this->add_control(
                    'prev_label',
                    [
                        'label'       => '上一篇标签文字',
                        'type'        => \Elementor\Controls_Manager::TEXT,
                        'default'     => 'Prev',
                        'label_block' => true,
                    ]
                );

                // 下一篇标签文字
                $this->add_control(
                    'next_label',
                    [
                        'label'       => '下一篇标签文字',
                        'type'        => \Elementor\Controls_Manager::TEXT,
                        'default'     => 'Next',
                        'label_block' => true,
                    ]
                );

                // 始终在同分类
                $this->add_control(
                    'same_category_enabled',
                    [
                        'label'        => '始终在同分类',
                        'type'         => \Elementor\Controls_Manager::SWITCHER,
                        'label_on'     => '是',
                        'label_off'    => '否',
                        'return_value' => 'yes',
                        'default'      => 'no',
                    ]
                );

                // 文章类型选择
                $this->add_control(
                    'post_type',
                    [
                        'label'       => '文章类型',
                        'type'        => \Elementor\Controls_Manager::SELECT,
                        'default'     => 'post',
                        'options'     => $this->get_post_type_options(),
                        'label_block' => true,
                        'condition'   => [
                            'same_category_enabled' => 'yes',
                        ],
                    ]
                );

                // 分类法选择
                $this->add_control(
                    'taxonomy',
                    [
                        'label'       => '分类法',
                        'type'        => \Elementor\Controls_Manager::SELECT,
                        'default'     => 'category',
                        'options'     => $this->get_taxonomy_options(),
                        'label_block' => true,
                        'condition'   => [
                            'same_category_enabled' => 'yes',
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

                // 容器间距
                $this->add_responsive_control(
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
                            'size' => 20,
                            'unit' => 'px',
                        ],
                        'selectors' => [
                            '{{WRAPPER}} .zpn-navigation' => 'gap: {{SIZE}}{{UNIT}};',
                        ],
                    ]
                );

                // 容器内边距
                $this->add_responsive_control(
                    'container_padding',
                    [
                        'label'      => '容器内边距',
                        'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                        'size_units' => [ 'px', 'em' ],
                        'default'    => [
                            'top'    => 20,
                            'right'  => 20,
                            'bottom' => 20,
                            'left'   => 20,
                        ],
                        'selectors'  => [
                            '{{WRAPPER}} .zpn-navigation' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                        ],
                    ]
                );

                $this->end_controls_section();

                // 标签样式
                $this->start_controls_section(
                    'section_style_label',
                    [
                        'label' => '标签样式',
                        'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
                    ]
                );

                // 标签文字颜色
                $this->add_control(
                    'label_color',
                    [
                        'label'     => '标签文字颜色',
                        'type'      => \Elementor\Controls_Manager::COLOR,
                        'default'   => '#999999',
                        'selectors' => [
                            '{{WRAPPER}} .zpn-label' => 'color: {{VALUE}};',
                        ],
                    ]
                );

                // 标签字体样式
                $this->add_group_control(
                    \Elementor\Group_Control_Typography::get_type(),
                    [
                        'name'     => 'label_typography',
                        'label'    => '标签字体',
                        'selector' => '{{WRAPPER}} .zpn-label',
                    ]
                );

                // 标签宽度
                $this->add_responsive_control(
                    'label_width',
                    [
                        'label'      => '标签宽度',
                        'type'       => \Elementor\Controls_Manager::SLIDER,
                        'size_units' => [ 'px', '%' ],
                        'range'      => [
                            'px' => ['min' => 50, 'max' => 200],
                            '%'  => ['min' => 5, 'max' => 50],
                        ],
                        'default' => [
                            'size' => 100,
                            'unit' => 'px',
                        ],
                        'selectors' => [
                            '{{WRAPPER}} .zpn-label' => 'width: {{SIZE}}{{UNIT}};',
                        ],
                    ]
                );

                $this->end_controls_section();

                // 链接样式
                $this->start_controls_section(
                    'section_style_link',
                    [
                        'label' => '链接样式',
                        'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
                    ]
                );

                // 链接默认颜色
                $this->add_control(
                    'link_color_default',
                    [
                        'label'     => '链接默认颜色',
                        'type'      => \Elementor\Controls_Manager::COLOR,
                        'default'   => '#333333',
                        'selectors' => [
                            '{{WRAPPER}} .zpn-link' => 'color: {{VALUE}};',
                        ],
                    ]
                );

                // 链接悬停颜色
                $this->add_control(
                    'link_color_hover',
                    [
                        'label'     => '链接悬停颜色',
                        'type'      => \Elementor\Controls_Manager::COLOR,
                        'default'   => '#59A498',
                        'selectors' => [
                            '{{WRAPPER}} .zpn-link:hover' => 'color: {{VALUE}};',
                        ],
                    ]
                );

                // 链接字体样式
                $this->add_group_control(
                    \Elementor\Group_Control_Typography::get_type(),
                    [
                        'name'     => 'link_typography',
                        'label'    => '链接字体',
                        'selector' => '{{WRAPPER}} .zpn-link',
                    ]
                );

                // 链接下划线
                $this->add_control(
                    'link_underline',
                    [
                        'label'        => '显示下划线',
                        'type'         => \Elementor\Controls_Manager::SWITCHER,
                        'label_on'     => '是',
                        'label_off'    => '否',
                        'return_value' => 'yes',
                        'default'      => 'no',
                        'selectors'    => [
                            '{{WRAPPER}} .zpn-link' => 'text-decoration: underline;',
                        ],
                    ]
                );

                // 链接悬停下划线
                $this->add_control(
                    'link_underline_hover',
                    [
                        'label'        => '悬停显示下划线',
                        'type'         => \Elementor\Controls_Manager::SWITCHER,
                        'label_on'     => '是',
                        'label_off'    => '否',
                        'return_value' => 'yes',
                        'default'      => 'yes',
                        'selectors'    => [
                            '{{WRAPPER}} .zpn-link:hover' => 'text-decoration: underline;',
                        ],
                        'condition'    => [
                            'link_underline' => '',
                        ],
                    ]
                );

                $this->end_controls_section();

                // 布局方向
                $this->start_controls_section(
                    'section_layout',
                    [
                        'label' => '布局选项',
                        'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
                    ]
                );

                $this->add_control(
                    'layout_alignment',
                    [
                        'label'   => '对齐方式',
                        'type'    => \Elementor\Controls_Manager::SELECT,
                        'default' => 'center',
                        'options' => [
                            'flex-start'    => '左对齐',
                            'center'       => '居中',
                            'flex-end'     => '右对齐',
                            'space-between' => '两端对齐',
                            'space-around'  => '环绕对齐',
                        ],
                        'selectors' => [
                            '{{WRAPPER}} .zpn-navigation' => 'justify-content: {{VALUE}};',
                        ],
                    ]
                );

                $this->end_controls_section();
            }

            private function get_post_type_options() {
                $post_types = get_post_types([
                    'public'   => true,
                    '_builtin' => false,
                ], 'objects');

                $options = [
                    'post' => '文章（Posts）',
                ];

                foreach ( $post_types as $post_type ) {
                    $options[ $post_type->name ] = $post_type->labels->name;
                }

                return $options;
            }

            private function get_taxonomy_options() {
                $taxonomies = get_taxonomies([
                    'public'   => true,
                    '_builtin' => true,
                ], 'objects');

                $options = [];

                foreach ( $taxonomies as $taxonomy ) {
                    if ( $taxonomy->name !== 'post_format' ) {
                        $options[ $taxonomy->name ] = $taxonomy->labels->name;
                    }
                }

                return $options;
            }

            protected function render() {
                $settings = $this->get_settings_for_display();

                $prev_label = esc_html( $settings['prev_label'] ?? 'Prev' );
                $next_label = esc_html( $settings['next_label'] ?? 'Next' );
                $same_category_enabled = ( $settings['same_category_enabled'] ?? '' ) === 'yes';
                $post_type = $settings['post_type'] ?? 'post';
                $taxonomy = $settings['taxonomy'] ?? 'category';

                $prev_post = $this->get_adjacent_post( $same_category_enabled, $post_type, $taxonomy, true );
                $next_post = $this->get_adjacent_post( $same_category_enabled, $post_type, $taxonomy, false );

                if ( empty( $prev_post ) && empty( $next_post ) ) {
                    if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                        echo '<p style="text-align:center;color:#999;">暂无相邻文章</p>';
                    }
                    return;
                }
                ?>
                <div class="zpn-navigation">
                    <?php if ( $prev_post ) : ?>
                        <div class="zpn-row zpn-row-prev">
                            <div class="zpn-label"><?php echo $prev_label; ?></div>
                            <a href="<?php echo esc_url( get_permalink( $prev_post->ID ) ); ?>" class="zpn-link">
                                <?php echo esc_html( $prev_post->post_title ); ?>
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if ( $next_post ) : ?>
                        <div class="zpn-row zpn-row-next">
                            <div class="zpn-label"><?php echo $next_label; ?></div>
                            <a href="<?php echo esc_url( get_permalink( $next_post->ID ) ); ?>" class="zpn-link">
                                <?php echo esc_html( $next_post->post_title ); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                <?php
            }

            private function get_adjacent_post( $in_same_term, $post_type, $taxonomy, $previous ) {
                global $post;
                $current_id = $post ? $post->ID : 0;

                if ( empty( $current_id ) ) {
                    return null;
                }

                $args = [
                    'post_type'      => $post_type,
                    'post_status'    => 'publish',
                    'posts_per_page' => 1,
                    'orderby'        => 'date',
                    'order'          => $previous ? 'DESC' : 'ASC',
                    'post__not_in'   => [ $current_id ],
                ];

                if ( $in_same_term ) {
                    $terms = wp_get_post_terms( $current_id, $taxonomy );
                    if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
                        $term_ids = wp_list_pluck( $terms, 'term_id' );
                        $args['tax_query'] = [
                            [
                                'taxonomy' => $taxonomy,
                                'field'    => 'term_id',
                                'terms'    => $term_ids,
                            ],
                        ];
                    }
                }

                if ( $previous ) {
                    $args['date_query'] = [
                        [
                            'column' => 'post_date',
                            'before' => get_the_date( 'Y-m-d H:i:s', $current_id ),
                        ],
                    ];
                } else {
                    $args['date_query'] = [
                        [
                            'column' => 'post_date',
                            'after'  => get_the_date( 'Y-m-d H:i:s', $current_id ),
                        ],
                    ];
                }

                $query = new WP_Query( $args );
                $post_data = $query->have_posts() ? $query->next_post() : null;
                wp_reset_postdata();

                return $post_data;
            }

            protected function content_template() {
                ?>
                <#
                var prevLabel = settings.prev_label || 'Prev';
                var nextLabel = settings.next_label || 'Next';
                #>
                <div class="zpn-navigation">
                    <div class="zpn-row zpn-row-prev">
                        <div class="zpn-label">{{{ prevLabel }}}</div>
                        <a href="#" class="zpn-link">上一篇文章标题示例</a>
                    </div>
                    <div class="zpn-row zpn-row-next">
                        <div class="zpn-label">{{{ nextLabel }}}</div>
                        <a href="#" class="zpn-link">下一篇文章标题示例</a>
                    </div>
                </div>
                <?php
            }
        }
    }
    $widgets_manager->register( new TTT_Post_Navigation() );
} );