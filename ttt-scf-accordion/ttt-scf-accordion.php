<?php
/**
 * SCF Features Accordion - PHP Logic (Elementor 官方结构版)
 * 描述: 生成 SCF 手风琴结构，使用 scf-features-accordion 前缀避免冲突
 * 版本: 1.2.0
 * 日期: 2026-05-11
 *
 * 变更记录:
 * v1.2.0: 类名前缀改为 scf-features-accordion，避免与 Elementor 官方手风琴冲突
 */

add_action('wp', function() {

    add_shortcode('scf_features_accordion', function() {
        $post_id = get_the_ID();
        if ( ! $post_id ) {
            global $post;
            $post_id = isset($post) ? $post->ID : 0;
        }
        if ( ! $post_id ) return '';

        $features = [];
        $count = intval( get_post_meta( $post_id, 'features', true ) );

        if ( $count > 0 ) {
            for ( $i = 0; $i < $count; $i++ ) {
                $features[] = [
                    'features_item_title'   => get_post_meta( $post_id, 'features_' . $i . '_features_item_title', true ),
                    'features_item_content' => get_post_meta( $post_id, 'features_' . $i . '_features_item_content', true ),
                ];
            }
        }

        if (empty($features)) return '';

        $unique_id = uniqid('scf-features-accordion-');

        $output = '<div class="scf-features-accordion" aria-label="SCF Features Accordion">';

        $index = 0;
        foreach ($features as $item) {
            $index++;
            $title = isset($item['features_item_title']) ? esc_html($item['features_item_title']) : '';
            $content = isset($item['features_item_content']) ? wp_kses_post($item['features_item_content']) : '';

            if ( empty($title) && empty($content) ) continue;

            $item_id = $unique_id . '-' . $index;
            $is_first = ($index === 1);

            $output .= '<details id="' . $item_id . '" class="scf-features-accordion-item"' . ($is_first ? ' open=""' : '') . '>';

            $output .= '<summary class="scf-features-accordion-title" data-accordion-index="' . $index . '" tabindex="' . ($is_first ? '0' : '-1') . '" aria-expanded="' . ($is_first ? 'true' : 'false') . '" aria-controls="' . $item_id . '">';
            $output .= '<span class="scf-features-accordion-title-header"><div class="scf-features-accordion-title-text">' . $title . '</div></span>';

            $output .= '<span class="scf-features-accordion-icon">';
            $output .= '<span class="scf-features-accordion-icon-opened">';
            $output .= '<svg aria-hidden="true" class="e-font-icon-svg e-fas-minus" viewBox="0 0 448 512" xmlns="http://www.w3.org/2000/svg"><path d="M416 208H32c-17.67 0-32 14.33-32 32v32c0 17.67 14.33 32 32 32h384c17.67 0 32-14.33 32-32v-32c0-17.67-14.33-32-32-32z"></path></svg>';
            $output .= '</span>';
            $output .= '<span class="scf-features-accordion-icon-closed">';
            $output .= '<svg aria-hidden="true" class="e-font-icon-svg e-fas-plus" viewBox="0 0 448 512" xmlns="http://www.w3.org/2000/svg"><path d="M416 208H272V64c0-17.67-14.33-32-32-32h-32c-17.67 0-32 14.33-32 32v144H32c-17.67 0-32 14.33-32 32v32c0 17.67 14.33 32 32 32h144v144c0 17.67 14.33 32 32 32h32c17.67 0 32-14.33 32-32V304h144c17.67 0 32-14.33 32-32v-32c0-17.67-14.33-32-32-32z"></path></svg>';
            $output .= '</span>';
            $output .= '</span>';
            $output .= '</summary>';

            $output .= '<div role="region" aria-labelledby="' . $item_id . '" class="scf-features-accordion-content elementor-element elementor-element-content e-con-full e-flex e-con e-child" data-id="content-' . $index . '" data-element_type="container">';
            $output .= '<div class="elementor-element elementor-element-text e-con e-child" data-element_type="container">';
            $output .= '<div class="elementor-element elementor-element-text-editor elementor-widget elementor-widget-text-editor" data-element_type="widget" data-widget_type="text-editor.default">';
            $output .= '<div class="elementor-widget-container">' . $content . '</div>';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</div>';

            $output .= '</details>';
        }
        $output .= '</div>';

        return $output;
    });

});