<?php
/**
 * SCF Features Accordion Pro - PHP Logic
 * 描述: PC端左右分离布局，手机端手风琴样式，每组item包含title和content
 * 版本: 1.0.6
 * 日期: 2026-05-11
 *
 * 变更记录:
 * v1.0.6: 手机版手风琴结构 - 每组 item 包含 title 和 content
 */

add_action('wp', function() {

    add_shortcode('scf_features_accordion_pro', function() {
        $post_id = get_the_ID();
        if ( ! $post_id ) {
            global $post;
            $post_id = isset($post) ? $post->ID : 0;
        }
        if ( ! $post_id ) return '';

        $features = [];
        $count = intval( get_post_meta( $post_id, 'performance', true ) );

        if ( $count > 0 ) {
            for ( $i = 0; $i < $count; $i++ ) {
                $features[] = [
                    'performance_item_title'   => get_post_meta( $post_id, 'performance_' . $i . '_performance_item_title', true ),
                    'performance_item_content' => get_post_meta( $post_id, 'performance_' . $i . '_performance_item_content', true ),
                ];
            }
        }

        if (empty($features)) return '';

        $unique_id = uniqid('scf-features-accordion-pro-');

        $output = '<div class="scf-features-accordion-pro" data-unique-id="' . $unique_id . '">';

        // PC Layout - Left column titles
        $output .= '<div class="scf-features-accordion-pro-titles">';
        $index = 0;
        foreach ($features as $item) {
            $index++;
            $title = isset($item['performance_item_title']) ? esc_html($item['performance_item_title']) : '';
            if ( empty($title) ) continue;

            $is_first = ($index === 1);
            $active_class = $is_first ? ' is-active' : '';

            $output .= '<div class="scf-features-accordion-pro-title' . $active_class . '" data-index="' . $index . '" tabindex="' . ($is_first ? '0' : '-1') . '" role="button" aria-expanded="' . ($is_first ? 'true' : 'false') . '">';
            $output .= '<span class="scf-features-accordion-pro-title-text">' . $title . '</span>';
            $output .= '<span class="scf-features-accordion-pro-arrow"></span>';
            $output .= '</div>';
        }
        $output .= '</div>';

        // PC Layout - Right column shared content panel
        $output .= '<div class="scf-features-accordion-pro-content-panel">';
        $index = 0;
        foreach ($features as $item) {
            $index++;
            $content = isset($item['performance_item_content']) ? wp_kses_post($item['performance_item_content']) : '';
            if ( empty($content) ) continue;

            $is_first = ($index === 1);
            $active_class = $is_first ? ' is-active' : '';

            $output .= '<div class="scf-features-accordion-pro-content' . $active_class . '" data-index="' . $index . '">';
            $output .= '<div class="scf-features-accordion-pro-content-inner">' . $content . '</div>';
            $output .= '</div>';
        }
        $output .= '</div>';

        // Mobile Layout - Accordion items (each contains title + content)
        $output .= '<div class="scf-features-accordion-pro-mobile">';
        $index = 0;
        foreach ($features as $item) {
            $index++;
            $title = isset($item['performance_item_title']) ? esc_html($item['performance_item_title']) : '';
            $content = isset($item['performance_item_content']) ? wp_kses_post($item['performance_item_content']) : '';

            if ( empty($title) && empty($content) ) continue;

            $is_first = ($index === 1);
            $active_class = $is_first ? ' is-active' : '';

            $output .= '<div class="scf-features-accordion-pro-item' . $active_class . '" data-index="' . $index . '">';
            $output .= '<div class="scf-features-accordion-pro-item-title" tabindex="' . ($is_first ? '0' : '-1') . '" role="button" aria-expanded="' . ($is_first ? 'true' : 'false') . '">';
            $output .= '<span class="scf-features-accordion-pro-title-text">' . $title . '</span>';
            $output .= '<span class="scf-features-accordion-pro-arrow"></span>';
            $output .= '</div>';
            $output .= '<div class="scf-features-accordion-pro-item-content">';
            $output .= '<div class="scf-features-accordion-pro-content-inner">' . $content . '</div>';
            $output .= '</div>';
            $output .= '</div>';
        }
        $output .= '</div>';

        $output .= '</div>';

        return $output;
    });

});