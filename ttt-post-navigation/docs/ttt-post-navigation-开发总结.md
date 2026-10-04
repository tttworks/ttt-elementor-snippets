# TTT Post Navigation 开发总结

## 概述

TTT Post Navigation 是一个 Elementor 自定义 Widget，用于在单篇文章模板中替代 Elementor 自带的 Post Navigation。采用 Code Snippets 插件部署，拆分为 PHP/CSS/JS 三个 snippet 文件。

---

## 版本演进

| 版本 | 日期 | 变更 |
|------|------|------|
| v1.0.0 | 2026-06-25 | 初始版（结构错误） |
| v1.1.0 | 2026-06-25 | 修正为 2 行 2 列 Grid 布局（又理解错了） |
| v1.2.0 | 2026-06-25 | 最终修正为正确的行式结构 |

---

## HTML 结构（最终版）

```
第一行：Prev 标签  |  上一篇文章标题
第二行：Next 标签  |  下一篇文章标题
```

```html
<div class="zpn-navigation">
    <!-- 第一行：上一篇 -->
    <div class="zpn-row zpn-row-prev">
        <div class="zpn-label">Prev</div>
        <a href="..." class="zpn-link">上一篇文章标题</a>
    </div>
    <!-- 第二行：下一篇 -->
    <div class="zpn-row zpn-row-next">
        <div class="zpn-label">Next</div>
        <a href="..." class="zpn-link">下一篇文章标题</a>
    </div>
</div>
```

---

## 踩坑经验

### 1. HTML 结构理解偏差（核心踩坑）

最严重的错误。AI 对"2 行 2 列"的理解连续两次出错：

- **第一次（v1.0.0）**：把结构做成了 `Prev + 标题` 一列、`Next + 标题` 另一列，即按列分组
- **第二次（v1.1.0）**：又改成了 Grid 的 2x2，但做成了标签在第一行、标题在第二行
- **最终（v1.2.0）**：按行分组，每行包含标签和标题

**教训**：当需求描述涉及"行列"结构时，AI 容易混淆行列的对应关系。需要更明确地说明"每行包含什么"而非仅指定行列数。用户说的"1行1列、1行2列"指的是同一行中的两个格子，而不是不同行。

### 2. PHP 文件头冲突

用户要求新代码不需要 `<?php` 开头。但项目中现有的所有 snippet 文件都以 `<?php` 开头。AI 遵循了模板范例但忽略了用户的明确要求。

**教训**：用户优先级高于模板范例。即使现有文件都带 `<?php`，如果用户明确要求移除，必须照做。

### 3. 版本命名策略

用户要求新代码不覆盖旧文件，所有版本文件保留。这导致同一 widget 有多个版本文件共存，需要在文件名中标明版本号并用更新日志记录变更。

**规范**：
- 文件名：`{slug}-{type}-v{version}.txt`
- 旧文件一律保留不删除
- 版本号从 v1.0.0 起步，每次需求变更递进次版本号（v1.1.0 → v1.2.0）

### 4. 控件设计演变

**【始终在同分类】功能的设计迭代**：

- **v1.0.0**：简单的 SWITCHER 开关，只判断"是/否同分类"
- **v1.1.0+**：改为多级选择：
  - 先选择"文章类型"（Post / 自定义文章类型如 ttt_product）
  - 再选择"分类法"（Categories / Tags / 其他 Taxonomy）

**如何实现**：使用 Elementor 的 SELECT 控件 + `condition` 条件显示，根据 `same_category_enabled` 的 `yes/no` 控制显隐。

注意 Elementor 的 SWITCHER 控件，`return_value` 固定为 `'yes'`，判断时用 `=== 'yes'`。

### 5. 相邻文章的查询逻辑

使用 `get_adjacent_post()` 方法替代 WordPress 原生的 `get_previous_post()` / `get_next_post()`。原因：

- 原生函数默认限制 `post_type = 'post'`，不支持自定义文章类型
- 原生函数的 `$in_same_term` 参数不够灵活
- 自定义查询用 `WP_Query` + `date_query` 实现"按日期排序的上一篇/下一篇"

关键代码：
```php
$args = [
    'post_type'    => $post_type,
    'post_status'  => 'publish',
    'posts_per_page' => 1,
    'post__not_in' => [ $current_id ],
];

// 上一篇：日期早于当前文章
if ( $previous ) {
    $args['order'] = 'DESC';
    $args['date_query'] = [[ 'before' => get_the_date( 'Y-m-d H:i:s', $current_id ) ]];
} 
// 下一篇：日期晚于当前文章
else {
    $args['order'] = 'ASC';
    $args['date_query'] = [[ 'after' => get_the_date( 'Y-m-d H:i:s', $current_id ) ]];
}
```

### 6. Taxonomy 选择器的动态生成

用 `get_taxonomies()` 获取所有公开的分类法，但需要排除 `post_format`。如果用户选择了某个文章类型（如 `ttt_product`），应该根据该类型绑定到的 taxonomy 来动态过滤选项——当前版本还未实现这层关联，需要用户手动选择分类法。

### 7. CSS 布局方案演变

| 版本 | 布局方式 | 问题 |
|------|----------|------|
| v1.0.0 | Flex row + flex-wrap | 结构错误，已废弃 |
| v1.1.0 | CSS Grid 2x2 | 结构错误，已废弃 |
| v1.2.0 | Flex column + flex row | 最终方案 |

**最终方案**：外层 flex column 排两行，每行内 flex row 排标签和标题。标签通过固定宽度 + `flex-shrink: 0` 保证两行对齐。

### 8. 移动端响应式

移动端断点 767px 以下，两行保持原有结构不变，仅缩小字体和宽度。不需要折腾成单列布局——两行两列在手机上依然可用。

---

## 文件清单（最终）

| 文件 | 用途 |
|------|------|
| `ttt-post-navigation-php-v1.2.0.txt` | PHP 主程序 |
| `ttt-post-navigation-css-v1.2.0.txt` | CSS 样式 |
| `ttt-post-navigation-js-v1.2.0.txt` | JS 交互脚本 |

旧版本 v1.0.0、v1.1.0 均保留在目录中。

---

## 现有可参考的 TTT Widget

| Widget | 说明 |
|--------|------|
| TTT Card Slider | 卡片轮播，结构最复杂 |
| TTT Category Tabs | 分类 Tab，结构最接近本例 |
| TTT Loop Grid | 列表网格，JS 交互丰富 |

Category Tabs 的 PHP 文件结构（register_controls / render / content_template 模式）最推荐作为同类 widget 的参考模板。
