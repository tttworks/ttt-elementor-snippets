/**
 * ============================================
 * TTT Smooth Scroll — PHP 主程序（仪表盘设置页）
 * ============================================
 *
 * @version     1.0.0
 * @date        2026-06-30
 * @author      Aloysius Luo
 * @organization TTTWORKS
 * @contact     https://tttworks.com
 * @description 仪表盘设置页 → 设置 > TTT Smooth Scroll，所有参数可视化调节
 *
 * --------------------------------------------
 * 版本更新日志
 * --------------------------------------------
 * v1.0.0 (2026-06-30)
 *   [新] 首次发布 — 仪表盘设置页
 *   [新] 放置在 设置 > TTT Smooth Scroll 子菜单
 *   [新] 滚动手感 / 键盘 / 中键拖拽 / 开关 四大区块
 *   [新] Settings API 自动保存，sanitize 每个参数
 */

// ============================================================
//  1. 注册菜单 & 设置选项
// ============================================================
add_action('admin_menu', 'zss_add_admin_menu');
function zss_add_admin_menu() {
    add_options_page(
        'TTT Smooth Scroll 设置',   // page title
        'TTT Smooth Scroll',         // menu title
        'manage_options',
        'ttt-smooth-scroll',
        'zss_render_settings_page'
    );
}

add_action('admin_init', 'zss_register_settings');
function zss_register_settings() {
    $fields = [
        'zss_lerp'                => ['type'=>'number',   'default'=>0.07,  'min'=>0.02, 'max'=>0.25, 'step'=>0.01],
        'zss_wheel_multiplier'    => ['type'=>'number',   'default'=>1,     'min'=>0.1,  'max'=>3,    'step'=>0.1],
        'zss_keyboard_step'       => ['type'=>'number',   'default'=>80,    'min'=>10,   'max'=>500,  'step'=>10],
        'zss_page_scroll_ratio'   => ['type'=>'number',   'default'=>0.9,   'min'=>0.5,  'max'=>1,    'step'=>0.05],
        'zss_enable_mobile'       => ['type'=>'checkbox', 'default'=>0],
        'zss_smooth_anchors'      => ['type'=>'checkbox', 'default'=>1],
        'zss_anchor_duration'     => ['type'=>'number',   'default'=>1000,  'min'=>200,  'max'=>3000, 'step'=>100],
        'zss_respect_reduced_motion'=> ['type'=>'checkbox', 'default'=>1],
        'zss_mid_click_dead_zone' => ['type'=>'number',   'default'=>8,     'min'=>2,    'max'=>30,   'step'=>1],
        'zss_mid_click_speed_mul' => ['type'=>'number',   'default'=>0.12,  'min'=>0.05, 'max'=>0.5,  'step'=>0.01],
        'zss_min_wheel_delta'     => ['type'=>'number',   'default'=>1.5,   'min'=>0.5,  'max'=>10,   'step'=>0.5],
        'zss_debug'               => ['type'=>'checkbox', 'default'=>0],
    ];

    foreach ($fields as $key => $cfg) {
        register_setting('zss_settings_group', $key, [
            'type'              => $cfg['type'] === 'checkbox' ? 'integer' : 'number',
            'default'           => $cfg['default'],
            'sanitize_callback' => function($v) use ($cfg) {
                if ($cfg['type'] === 'checkbox') {
                    return $v ? 1 : 0;
                }
                $v = (float) $v;
                return max($cfg['min'], min($cfg['max'], $v));
            },
        ]);
    }
}

// ============================================================
//  2. 设置页 HTML
// ============================================================
function zss_render_settings_page() {
    ?>
    <div class="wrap" style="max-width:720px">
        <h1 style="margin-bottom:4px">TTT Smooth Scroll 设置</h1>
        <p style="color:#646970; margin-bottom:20px">所有参数实时生效，保存后刷新网站页面即可看到效果。</p>
        <form method="post" action="options.php">
        <?php
            settings_fields('zss_settings_group');
            $f = function($key, $label, $desc, $opts=[]) {
                $val  = get_option($key, $opts['default'] ?? '');
                $type = $opts['type'] ?? 'number';
                ?>
                <table class="form-table" role="presentation"><tbody><tr>
                    <th scope="row" style="width:200px"><?php echo esc_html($label); ?></th>
                    <td>
                    <?php if ($type === 'checkbox'): ?>
                        <label>
                            <input type="hidden" name="<?php echo esc_attr($key); ?>" value="0">
                            <input type="checkbox" name="<?php echo esc_attr($key); ?>" value="1" <?php checked($val, 1); ?>>
                            <?php echo esc_html($desc); ?>
                        </label>
                    <?php else: ?>
                        <input type="number" name="<?php echo esc_attr($key); ?>"
                               value="<?php echo esc_attr($val); ?>"
                               min="<?php echo esc_attr($opts['min']); ?>"
                               max="<?php echo esc_attr($opts['max']); ?>"
                               step="<?php echo esc_attr($opts['step']); ?>"
                               style="width:100px; margin-right:8px">
                        <span style="color:#646970; font-size:13px"><?php echo esc_html($desc); ?></span>
                    <?php endif; ?>
                    </td>
                </tr></tbody></table>
                <?php
            };

            echo '<h2 style="margin-top:24px">滚动手感</h2>';
            $f('zss_lerp', '惯性强度', '0.02 极粘 ↔ 0.25 接近原生（默认 0.07 = 苹果官网手感）', ['type'=>'number','default'=>0.07,'min'=>0.02,'max'=>0.25,'step'=>0.01]);
            $f('zss_wheel_multiplier', '滚轮灵敏度', '倍数，1 = 正常，2 = 双倍（默认 1）', ['type'=>'number','default'=>1,'min'=>0.1,'max'=>3,'step'=>0.1]);
            $f('zss_min_wheel_delta', '滚轮最小响应量', '低于此值的滚动忽略（过滤触控板微量滚动），单位 px（默认 1.5）', ['type'=>'number','default'=>1.5,'min'=>0.5,'max'=>10,'step'=>0.5]);

            echo '<h2 style="margin-top:24px">键盘滚动</h2>';
            $f('zss_keyboard_step', '方向键单次滚动量', '单位 px（默认 80）', ['type'=>'number','default'=>80,'min'=>10,'max'=>500,'step'=>10]);
            $f('zss_page_scroll_ratio', '翻页占视口比例', '0.9 = 翻页时滚动视口高度的 90%（默认 0.9）', ['type'=>'number','default'=>0.9,'min'=>0.5,'max'=>1,'step'=>0.05]);

            echo '<h2 style="margin-top:24px">中键拖拽滚动</h2>';
            $f('zss_mid_click_dead_zone', '死区大小', '单位 px，避免误触（默认 8）', ['type'=>'number','default'=>8,'min'=>2,'max'=>30,'step'=>1]);
            $f('zss_mid_click_speed_mul', '速度系数', '越大越快（默认 0.12）', ['type'=>'number','default'=>0.12,'min'=>0.05,'max'=>0.5,'step'=>0.01]);

            echo '<h2 style="margin-top:24px">开关</h2>';
            $f('zss_enable_mobile', '移动端接管', '⚠ 实验性：开启后手机/平板也走平滑滚动。默认关闭，走浏览器原生动量滚动', ['type'=>'checkbox','default'=>0]);
            $f('zss_smooth_anchors', '#hash 锚点平滑跳转', '点击页内链接时用缓动动画滑过去（可被手动滚轮中断）', ['type'=>'checkbox','default'=>1]);
            $f('zss_anchor_duration', '锚点动画时长', '单位 ms（默认 1000）', ['type'=>'number','default'=>1000,'min'=>200,'max'=>3000,'step'=>100]);
            $f('zss_respect_reduced_motion', '无障碍：遵守系统的"减少动效"', '用户系统开启动效减弱时自动停用平滑滚动', ['type'=>'checkbox','default'=>1]);
            $f('zss_debug', '调试日志', '开启后在 F12 Console 输出 [ZSS] 日志', ['type'=>'checkbox','default'=>0]);

            submit_button('保存设置');
        ?>
        </form>
    </div>
    <?php
}
