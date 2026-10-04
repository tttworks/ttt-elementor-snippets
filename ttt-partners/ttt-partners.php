/**
 * ============================================
 * TTT Partners - PHP 主程序
 * ============================================
 *
 * @version     1.0.2
 * @date        2026-06-21
 * @author      Aloysius Luo
 * @organization TTTWORKS
 * @contact     https://tttworks.com
 * @description Elementor 合作伙伴展示组件 v1.0.2
 *
 * --------------------------------------------
 * 版本更新日志
 * --------------------------------------------
 * v1.0.2 (2026-06-21)
 *   [增] image_text_source 控件：可选「图片标题」或「图片说明」作为 item 文字来源
 *
 * v1.0.1 (2026-06-21)
 *   [修] 分隔线与图片间距默认值改为 0
 *   [修] CSS grid 引用 CSS 变量 --zpt-gap-h/--zpt-gap-v 修复间距不生效
 *
 * v1.0.0 (2026-06-21)
 *   [新] Gallery 图片源，图片标题即文字
 *   [新] 行列网格分组轮播 / 平铺双模式
 *   [新] 圆点状翻页页码（活跃 36×6 / 非活跃 12×6）
 *   [新] 图片文字分隔线显隐
 *   [新] 自动轮播 + 间隔可配
 * ============================================
 */

add_action( 'elementor/widgets/register', function ( $widgets_manager ) {

    if ( ! class_exists( '\Elementor\Widget_Base' ) ) return;

    if ( ! class_exists( 'TTT_Partners_Widget' ) ) {

    class TTT_Partners_Widget extends \Elementor\Widget_Base {

        public function get_name()           { return 'ttt_partners'; }
        public function get_title()          { return 'TTT Partners'; }
        public function get_icon()           { return 'eicon-gallery-grid'; }
        public function get_categories()     { return [ 'general' ]; }
        public function get_keywords()       { return [ 'partners', 'gallery', 'logo', 'grid', 'carousel', 'ttt' ]; }
        public function get_style_depends()  { return [ 'ttt-partners' ]; }
        public function get_script_depends() { return [ 'ttt-partners' ]; }

        protected function register_controls() {

            /* ── 图片相册 ── */
            $this->start_controls_section( 'section_gallery', [
                'label' => '图片相册',
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ] );
            $this->add_control( 'gallery', [
                'label'      => '添加图片',
                'type'       => \Elementor\Controls_Manager::GALLERY,
                'default'    => [],
                'description'=> '每张图片即为一个 Partner 项。图片文字将自动取自下方选择的字段。',
            ] );
            $this->add_control( 'image_text_source', [
                'label'   => '图片文字来源',
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'title',
                'options' => [
                    'title'   => '图片标题 (Title)',
                    'caption' => '图片说明 (Caption)',
                ],
                'description' => '在媒体库中编辑图片的标题或说明字段即可。',
            ] );
            $this->end_controls_section();

            /* ── 布局 ── */
            $this->start_controls_section( 'section_layout', [
                'label' => '布局设置',
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ] );

            $this->add_responsive_control( 'cols_per_page', [
                'label'   => '每行显示列数',
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => '3',
                'tablet_default' => '2',
                'mobile_default' => '1',
                'options' => [ '1'=>'1 列','2'=>'2 列','3'=>'3 列','4'=>'4 列','5'=>'5 列','6'=>'6 列' ],
            ] );

            $this->add_responsive_control( 'rows_per_page', [
                'label'   => '每组显示行数',
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => '2',
                'tablet_default' => '2',
                'mobile_default' => '3',
                'options' => [ '1'=>'1 行','2'=>'2 行','3'=>'3 行','4'=>'4 行' ],
                'description' => '每组轮播 = 列数 × 行数个 item。例如 3列×2行 = 每页6个。',
            ] );

            $this->add_control( 'carousel_enabled', [
                'label'        => '启用轮播',
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => '轮播',
                'label_off'    => '平铺',
                'return_value' => 'yes',
                'default'      => 'yes',
                'description'  => '关闭后所有图片一次性平铺展示，无页码。',
            ] );

            $this->add_responsive_control( 'image_height', [
                'label'      => '图片容器高度 (px)',
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 60, 'max' => 500 ] ],
                'default'    => [ 'unit' => 'px', 'size' => 120 ],
                'selectors'  => [ '{{WRAPPER}} .zpt-item-image' => 'height: {{SIZE}}px;' ],
            ] );

            $this->add_control( 'image_fit', [
                'label'   => '图片填充方式',
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'contain',
                'options' => [ 'contain' => 'Contain（完整显示）', 'cover' => 'Cover（裁剪填充）' ],
            ] );

            $this->add_responsive_control( 'gap_h', [
                'label'      => '列间距 (px)',
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 80 ] ],
                'default'    => [ 'unit' => 'px', 'size' => 20 ],
                'selectors'  => [ '{{WRAPPER}}' => '--zpt-gap-h: {{SIZE}}px;' ],
            ] );

            $this->add_responsive_control( 'gap_v', [
                'label'      => '行间距 (px)',
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [ 'px' => [ 'min' => 0, 'max' => 80 ] ],
                'default'    => [ 'unit' => 'px', 'size' => 20 ],
                'selectors'  => [ '{{WRAPPER}}' => '--zpt-gap-v: {{SIZE}}px;' ],
            ] );

            $this->end_controls_section();

            /* ── 导航 & 轮播 ── */
            $this->start_controls_section( 'section_nav', [
                'label'     => '导航 & 轮播',
                'tab'       => \Elementor\Controls_Manager::TAB_CONTENT,
                'condition' => [ 'carousel_enabled' => 'yes' ],
            ] );
            $this->add_control( 'autoplay', [
                'label'        => '自动轮播',
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
            ] );
            $this->add_control( 'autoplay_interval', [
                'label'     => '每页停留时间（毫秒）',
                'type'      => \Elementor\Controls_Manager::NUMBER,
                'min'       => 1000,
                'max'       => 15000,
                'step'      => 500,
                'default'   => 3000,
                'condition' => [ 'autoplay' => 'yes' ],
            ] );
            $this->add_control( 'transition_speed', [
                'label'     => '轮播过渡速度（毫秒）',
                'type'      => \Elementor\Controls_Manager::NUMBER,
                'min'       => 200,
                'max'       => 2000,
                'step'      => 100,
                'default'   => 500,
            ] );
            $this->end_controls_section();

            /* ── 分隔线样式 ── */
            $this->start_controls_section( 'section_style_separator', [
                'label' => '分隔线样式',
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ] );
            $this->add_control( 'separator_show', [
                'label'        => '显示分隔线',
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
            ] );
            $this->add_control( 'separator_color', [
                'label'     => '分隔线颜色',
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => 'rgba(0, 0, 0, 0.1)',
                'selectors' => [ '{{WRAPPER}} .zpt-item-sep' => 'background: {{VALUE}};' ],
                'condition' => [ 'separator_show' => 'yes' ],
            ] );
            $this->add_responsive_control( 'separator_height', [
                'label'     => '分隔线高度 (px)',
                'type'      => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'     => [ 'px' => [ 'min' => 1, 'max' => 10 ] ],
                'default'   => [ 'unit' => 'px', 'size' => 1 ],
                'selectors' => [ '{{WRAPPER}} .zpt-item-sep' => 'height: {{SIZE}}px;' ],
                'condition' => [ 'separator_show' => 'yes' ],
            ] );
            $this->add_responsive_control( 'separator_image_gap', [
                'label'     => '分隔线上间距 (px)',
                'type'      => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'     => [ 'px' => [ 'min' => 0, 'max' => 80 ] ],
                'default'   => [ 'unit' => 'px', 'size' => 0 ],
                'selectors' => [ '{{WRAPPER}} .zpt-item-sep' => 'margin-top: {{SIZE}}px;' ],
                'condition' => [ 'separator_show' => 'yes' ],
            ] );
            $this->add_responsive_control( 'separator_text_gap', [
                'label'     => '分隔线下间距 (px)',
                'type'      => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'     => [ 'px' => [ 'min' => 0, 'max' => 80 ] ],
                'default'   => [ 'unit' => 'px', 'size' => 15 ],
                'selectors' => [ '{{WRAPPER}} .zpt-item-sep' => 'margin-bottom: {{SIZE}}px;' ],
                'condition' => [ 'separator_show' => 'yes' ],
            ] );
            $this->end_controls_section();

            /* ── 文字样式 ── */
            $this->start_controls_section( 'section_style_text', [
                'label' => '文字样式',
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ] );
            $this->add_control( 'text_color', [
                'label'     => '文字颜色',
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#333333',
                'selectors' => [ '{{WRAPPER}} .zpt-item-text' => 'color: {{VALUE}};' ],
            ] );
            $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [
                'name'     => 'text_typography',
                'selector' => '{{WRAPPER}} .zpt-item-text',
                'fields_options' => [
                    'font_family' => [ 'default' => 'Montserrat' ],
                    'font_size'   => [ 'default' => [ 'unit' => 'px', 'size' => 18 ] ],
                    'font_weight' => [ 'default' => '400' ],
                ],
            ] );
            $this->end_controls_section();

            /* ── 页码样式 ── */
            $this->start_controls_section( 'section_style_pagination', [
                'label'     => '页码样式',
                'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [ 'carousel_enabled' => 'yes' ],
            ] );
            $this->add_control( 'dot_active_bg', [
                'label'     => '当前页码背景色',
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => 'rgba(89, 164, 152, 1)',
                'selectors' => [ '{{WRAPPER}}' => '--zpt-dot-active-bg: {{VALUE}};' ],
            ] );
            $this->add_control( 'dot_inactive_bg', [
                'label'     => '非当前页码背景色',
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => 'rgba(89, 164, 152, 0.4)',
                'selectors' => [ '{{WRAPPER}}' => '--zpt-dot-inactive-bg: {{VALUE}};' ],
            ] );
            $this->add_responsive_control( 'dot_active_w', [
                'label'     => '当前页码宽度 (px)',
                'type'      => \Elementor\Controls_Manager::SLIDER,
                'size_units'=> [ 'px' ],
                'range'     => [ 'px' => [ 'min' => 12, 'max' => 80 ] ],
                'default'   => [ 'unit' => 'px', 'size' => 36 ],
                'selectors' => [ '{{WRAPPER}}' => '--zpt-dot-active-w: {{SIZE}}px;' ],
            ] );
            $this->add_responsive_control( 'dot_inactive_w', [
                'label'     => '非当前页码宽度 (px)',
                'type'      => \Elementor\Controls_Manager::SLIDER,
                'size_units'=> [ 'px' ],
                'range'     => [ 'px' => [ 'min' => 6, 'max' => 40 ] ],
                'default'   => [ 'unit' => 'px', 'size' => 12 ],
                'selectors' => [ '{{WRAPPER}}' => '--zpt-dot-inactive-w: {{SIZE}}px;' ],
            ] );
            $this->add_responsive_control( 'dot_height', [
                'label'     => '页码高度 (px)',
                'type'      => \Elementor\Controls_Manager::SLIDER,
                'size_units'=> [ 'px' ],
                'range'     => [ 'px' => [ 'min' => 2, 'max' => 20 ] ],
                'default'   => [ 'unit' => 'px', 'size' => 6 ],
                'selectors' => [ '{{WRAPPER}}' => '--zpt-dot-h: {{SIZE}}px;' ],
            ] );
            $this->add_responsive_control( 'dot_gap', [
                'label'     => '页码之间间距 (px)',
                'type'      => \Elementor\Controls_Manager::SLIDER,
                'size_units'=> [ 'px' ],
                'range'     => [ 'px' => [ 'min' => 0, 'max' => 30 ] ],
                'default'   => [ 'unit' => 'px', 'size' => 5 ],
                'selectors' => [ '{{WRAPPER}}' => '--zpt-dot-gap: {{SIZE}}px;' ],
            ] );
            $this->add_responsive_control( 'pagination_top_gap', [
                'label'     => '页码与轮播组间距 (px)',
                'type'      => \Elementor\Controls_Manager::SLIDER,
                'size_units'=> [ 'px' ],
                'range'     => [ 'px' => [ 'min' => 0, 'max' => 120 ] ],
                'default'   => [ 'unit' => 'px', 'size' => 40 ],
                'selectors' => [ '{{WRAPPER}}' => '--zpt-pagination-gap: {{SIZE}}px;' ],
            ] );
            $this->end_controls_section();
        }

        /* ════════════ 前端 HTML ════════════ */
        protected function render() {

            $settings = $this->get_settings_for_display();
            $gallery = $settings['gallery'] ?? [];
            $total_items = count( $gallery );

            if ( empty( $gallery ) ) {
                echo '<p style="color:#999;padding:20px;">请在左侧面板的「图片相册」中添加合作伙伴图片。</p>';
                return;
            }

            $carousel   = ( $settings['carousel_enabled'] ?? 'yes' ) === 'yes';
            $cols       = (int)( $settings['cols_per_page'] ?? 3 );
            $rows       = (int)( $settings['rows_per_page'] ?? 2 );
            $items_per_page = $cols * $rows;
            $total_pages    = $carousel ? (int) ceil( $total_items / $items_per_page ) : 1;

            $img_fit     = $settings['image_fit'] ?? 'contain';
            $auto        = ( $settings['autoplay'] ?? 'yes' ) === 'yes' ? 'yes' : 'no';
            $auto_intv   = (int)( $settings['autoplay_interval'] ?? 3000 );
            $trans_speed = (int)( $settings['transition_speed'] ?? 500 );
            $sep_show    = ( $settings['separator_show'] ?? 'yes' ) === 'yes';
            $text_src    = $settings['image_text_source'] ?? 'title';

            // v1.0.2: 辅助函数 — 从图片 ID 获取文字（标题/说明）
            $get_img_text = function( $img_id ) use ( $text_src ) {
                if ( ! $img_id ) return '';
                if ( $text_src === 'caption' ) {
                    $post = get_post( $img_id );
                    $caption = $post ? $post->post_excerpt : '';
                    return $caption;
                }
                // 默认 title
                $title = get_the_title( $img_id );
                if ( empty( $title ) ) {
                    $title = get_post_meta( $img_id, '_wp_attachment_image_alt', true ) ?: '';
                }
                return $title;
            };

            // 响应式数据
            $cols_t = is_numeric( $settings['cols_per_page_tablet'] ?? null )  ? (int)$settings['cols_per_page_tablet']  : 2;
            $cols_m = is_numeric( $settings['cols_per_page_mobile'] ?? null )  ? (int)$settings['cols_per_page_mobile']  : 1;
            $rows_t = is_numeric( $settings['rows_per_page_tablet'] ?? null )  ? (int)$settings['rows_per_page_tablet']  : 2;
            $rows_m = is_numeric( $settings['rows_per_page_mobile'] ?? null )  ? (int)$settings['rows_per_page_mobile']  : 3;

            $uid = 'zpt_' . $this->get_id();
            ?>

            <div class="zpt-wrapper <?php echo $carousel ? 'zpt-carousel' : 'zpt-flat'; ?>"
                 id="<?php echo esc_attr( $uid ); ?>"
                 data-cols="<?php echo esc_attr( $cols ); ?>"
                 data-rows="<?php echo esc_attr( $rows ); ?>"
                 data-cols-tablet="<?php echo esc_attr( $cols_t ); ?>"
                 data-rows-tablet="<?php echo esc_attr( $rows_t ); ?>"
                 data-cols-mobile="<?php echo esc_attr( $cols_m ); ?>"
                 data-rows-mobile="<?php echo esc_attr( $rows_m ); ?>"
                 data-carousel="<?php echo $carousel ? 'yes' : 'no'; ?>"
                 data-autoplay="<?php echo esc_attr( $auto ); ?>"
                 data-autoplay-interval="<?php echo esc_attr( $auto_intv ); ?>"
                 data-transition-speed="<?php echo esc_attr( $trans_speed ); ?>"
                 data-total-pages="<?php echo esc_attr( $total_pages ); ?>">

                <?php if ( $carousel ) : ?>
                <div class="zpt-viewport">
                    <div class="zpt-track" style="transition-duration: <?php echo esc_attr( $trans_speed ); ?>ms;">
                        <?php
                        // 预分组渲染页面
                        $pages = array_chunk( $gallery, $items_per_page );
                        foreach ( $pages as $page_idx => $page_items ) :
                        ?>
                        <div class="zpt-page"
                             data-page="<?php echo esc_attr( $page_idx ); ?>"
                             style="grid-template-columns: repeat(<?php echo esc_attr( $cols ); ?>, 1fr);">
                            <?php foreach ( $page_items as $item ) :
                                $img_id    = $item['id'] ?? 0;
                                $img_url   = $item['url'] ?? '';
                                $img_title = $get_img_text( $img_id );
                            ?>
                            <div class="zpt-item">
                                <div class="zpt-item-image">
                                    <?php if ( $img_id ) :
                                        echo wp_get_attachment_image( $img_id, 'medium', false, [
                                            'class'   => 'zpt-item-img',
                                            'loading' => 'lazy',
                                            'draggable' => 'false',
                                            'alt'     => esc_attr( $img_title ),
                                            'style'   => 'object-fit:' . esc_attr( $img_fit ) . ';',
                                        ] );
                                    elseif ( $img_url ) : ?>
                                        <img src="<?php echo esc_url( $img_url ); ?>"
                                             class="zpt-item-img"
                                             loading="lazy"
                                             draggable="false"
                                             alt="<?php echo esc_attr( $img_title ); ?>"
                                             style="object-fit:<?php echo esc_attr( $img_fit ); ?>;">
                                    <?php endif; ?>
                                </div>
                                <?php if ( $sep_show ) : ?>
                                <div class="zpt-item-sep"></div>
                                <?php endif; ?>
                                <?php if ( ! empty( $img_title ) ) : ?>
                                <div class="zpt-item-text"><?php echo esc_html( $img_title ); ?></div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php if ( $total_pages > 1 ) : ?>
                <div class="zpt-pagination">
                    <?php for ( $d = 0; $d < $total_pages; $d++ ) : ?>
                    <span class="zpt-dot<?php echo $d === 0 ? ' active' : ''; ?>"
                          data-dot="<?php echo esc_attr( $d ); ?>"></span>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>

                <?php else : /* 平铺模式 */ ?>
                <div class="zpt-flat-grid"
                     style="grid-template-columns: repeat(<?php echo esc_attr( $cols ); ?>, 1fr);">
                    <?php foreach ( $gallery as $item ) :
                        $img_id    = $item['id'] ?? 0;
                        $img_url   = $item['url'] ?? '';
                        $img_title = $get_img_text( $img_id );
                    ?>
                    <div class="zpt-item">
                        <div class="zpt-item-image">
                            <?php if ( $img_id ) :
                                echo wp_get_attachment_image( $img_id, 'medium', false, [
                                    'class'   => 'zpt-item-img',
                                    'loading' => 'lazy',
                                    'draggable' => 'false',
                                    'alt'     => esc_attr( $img_title ),
                                    'style'   => 'object-fit:' . esc_attr( $img_fit ) . ';',
                                ] );
                            elseif ( $img_url ) : ?>
                                <img src="<?php echo esc_url( $img_url ); ?>"
                                     class="zpt-item-img"
                                     loading="lazy"
                                     draggable="false"
                                     alt="<?php echo esc_attr( $img_title ); ?>"
                                     style="object-fit:<?php echo esc_attr( $img_fit ); ?>;">
                            <?php endif; ?>
                        </div>
                        <?php if ( $sep_show ) : ?>
                        <div class="zpt-item-sep"></div>
                        <?php endif; ?>
                        <?php if ( ! empty( $img_title ) ) : ?>
                        <div class="zpt-item-text"><?php echo esc_html( $img_title ); ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

            </div>
            <?php
        }
    }
    }
    $widgets_manager->register( new TTT_Partners_Widget() );
} );
