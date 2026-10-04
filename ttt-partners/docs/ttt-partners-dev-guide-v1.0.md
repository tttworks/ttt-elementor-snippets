# TTT Partners — 开发记录与工程指南

## 基本信息

| 项目 | 说明 |
|------|------|
| **项目名** | TTT Partners（Elementor 自定义合作伙伴展示组件） |
| **客户/网站** | TTTWorks - TTT |
| **技术栈** | WordPress + Elementor Pro + Code Snippets 插件 |
| **部署方式** | 3 个 php-wrapped `.txt` 文件，通过 Code Snippets 插件代替 functions.php |
| **当前版本** | PHP v1.0.2 + CSS v1.0.2 + JS v1.0.0 |
| **作者/组织** | Aloysius Luo / TTTWORKS |
| **文件路径** | `D:\#工作文档\01.合作项目\TTTWorks - ttt\Snippets\ttt-partners-*.txt` |
| **开发规则** | `Elementor-Widget-通用Prompt模板-v1.0.txt`（同目录） |

---

## 架构概述

小部件由三个独立文件组成，每个文件都是 PHP 包装的，通过 `add_action` 注入到 WordPress 中：

| 文件 | 职责 | 注入方式 |
|------|------|----------|
| **PHP** | 通过 `elementor/widgets/register` 注册小部件，定义 Elementor 控件和 `render()` | `add_action( 'elementor/widgets/register', ... )` |
| **CSS** | 所有 `.zpt-*` 样式 | `add_action( 'wp_head', function() { ?><style>...</style><?php } )` |
| **JS** | 交互逻辑：grid 分页轮播、pointer events 滑动、自动轮播、圆点页码 | `wp_add_inline_script` |

---

## 小部件结构

```
┌─ .zpt-wrapper (data-* 响应式配置) ─────────────────────────┐
│ ┌─ .zpt-viewport (overflow:hidden) ────────────────────────┐│
│ │ ┌─ .zpt-track (flex, translate3d 滑动) ─────────────────┐││
│ │ │ ┌─ .zpt-page[0] (grid, repeat(N, 1fr)) ──────────────┐│││
│ │ │ │ .zpt-item × (cols × rows)                           ││││
│ │ │ │   ├ .zpt-item-image (容器, 120px 默认高)             ││││
│ │ │ │   │   └ .zpt-item-img (object-fit 控制)             ││││
│ │ │ │   ├ .zpt-item-sep (分隔线, 可选显隐)                 ││││
│ │ │ │   └ .zpt-item-text (取自图片标题或说明)              ││││
│ │ │ └────────────────────────────────────────────────────┘│││
│ │ │ ┌─ .zpt-page[1] ─────────────────────────────────────┐│││
│ │ │ │ ...                                                ││││
│ │ │ └────────────────────────────────────────────────────┘│││
│ │ └──────────────────────────────────────────────────────┘││
│ └─────────────────────────────────────────────────────────┘│
│ ┌─ .zpt-pagination ───────────────────────────────────────┐│
│ │ .zpt-dot (平行四边形 clip-path 裁剪, active 36px, 非 12px)││
│ └─────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────┘
```

---

## 主要功能与亮点

### 1. 双模式布局

**轮播模式**（默认）：PHP `render()` 端按 `cols × rows` 预分组，每组为一个 `.zpt-page`。JS 端通过 `translate3d()` 追踪平移，每次显示一个完整 page，配合圆点页码导航。

**平铺模式**：关闭轮播开关后，所有图片一次性以 grid 平铺展示，不分组、无页码。JS 仅做响应式行列更新。

### 2. 预分组渲染

PHP 在服务端用 `array_chunk()` 将 gallery 图片按 `cols × rows` 分组，每组生成一个 `.zpt-page`。好处是页面初次渲染时 HTML 已经确定，无需 JS 重新排列 DOM，首屏无闪烁。

### 3. 响应式行列

Elementor 标准响应式控件 `cols_per_page` 和 `rows_per_page` 分别设置 desktop / tablet / mobile 三档行列数，通过 `data-*` 属性传给 JS。JS 在 resize 时根据 `window.innerWidth` 断点（>1024 / 768~1024 / ≤767）动态更新 `grid-template-columns`。

### 4. Pointer Events 滑动轮播

使用 Pointer Events API（非 Touch Events），一套代码同时处理鼠标和触屏。拖拽逻辑包含弹性边界效果：到达首尾页时偏移量按 1/3 系数阻尼，松手回弹。`pointermove` 超过 50px 阈值才翻页，防止误触。

### 5. 自动轮播 + 悬停暂停

自动轮播用 `setInterval` 驱动，到达末页后回到首页循环。鼠标/手指进入 widget 区域时暂停（`pointerenter` 停止 interval），离开后恢复（`pointerleave` 重新启动），交互友好。

### 6. 圆点页码

平行四边形裁切（`clip-path: polygon`），左右 35° 斜角。当前页码 36×6px + 活跃色，非当前页码 12×6px + 半透明色。宽度过渡动画 0.3s ease。每个圆点尺寸/颜色/间距全部通过 CSS 变量暴露给 Elementor 面板控制。

### 7. 图片文字来源可选

v1.0.2 新增 `image_text_source` 控件，可选「图片标题 (Title)」或「图片说明 (Caption)」作为每个 item 下方的文字。支持回退逻辑：title 为空时尝试 `_wp_attachment_image_alt`。

---

## 版本历史

### PHP

| 版本 | 日期 | 主要改动 |
|------|------|----------|
| v1.0.2 | 2026-06-21 | 新增 `image_text_source` 控件，可选 title 或 caption 作为 item 文字来源 |
| v1.0.1 | 2026-06-21 | 分隔线与图片间距默认值改为 0；CSS grid 列/行间距显式引用 CSS 变量修复间距不生效 |
| v1.0.0 | 2026-06-21 | Gallery 图片源、行列网格分组轮播/平铺双模式、圆点页码、分隔线显隐、自动轮播 + 间隔可配 |

### CSS

| 版本 | 日期 | 主要改动 |
|------|------|----------|
| v1.0.2 | 2026-06-29 | **[关键修复]** 移除 `.zpt-track` 的 `will-change:transform`（避免 GPU 合成层导致 overflow:hidden 失效）；新增多层溢出保护（`.zpt-wrapper` + `.zpt-viewport` + `.zpt-page` 均设 overflow:hidden）；`.zpt-page` 和 `.zpt-item` 加 `min-width:0` 防 grid 子像素舍入溢出；`.zpt-item-text` 加 `overflow-wrap:break-word` 文本溢出保护；`.zpt-viewport` 加 `contain:paint` 隔离布局 |
| v1.0.1 | 2026-06-21 | Grid gap 显式引用 CSS 变量；页码圆点改用 clip-path 平行四边形裁切 |
| v1.0.0 | 2026-06-21 | Item 居中布局、图片容器+标题、分隔线、grid 分页、圆点页码、响应式三断点 |

### JS

| 版本 | 日期 | 主要改动 |
|------|------|----------|
| v1.0.0 | 2026-06-21 | Grid 分页轮播（Pointer Events 滑动 + 自动轮播 + 圆点页码交互）、响应式行列动态更新、平铺模式行列切换 |

---

## 关键技术决策

### 为什么在服务端预分组而非 JS 动态分页

PHP `render()` 中用 `array_chunk()` 将图片按 `cols × rows` 分组后直接渲染 HTML。优点是首屏无 JS 布局闪烁、SEO 友好、减少客户端计算。代价是响应式切换行列数时，JS 只管 `grid-template-columns`，不重新分页——如果用户在手机端设了比 desktop 少的 rows，末页 item 数会不匹配。当前策略是用 Elementor 响应式控件让面板设置者自行保证 rows 一致性。

### 为什么用 Pointer Events 而非 Touch Events

Pointer Events 是 W3C 统一标准，一套 API 同时处理鼠标、触屏、触控笔。代码中只需 `pointerdown` / `pointermove` / `pointerup` 三个事件，无需分别监听 `touchstart` / `mousedown`。配合 `setPointerCapture` 处理手指滑出区域的情况。兼容性方面，iOS Safari 13+ 和 Android Chrome 55+ 均无问题。

### 为什么 translate3d 而非 scrollLeft

轮播平移用 `transform: translate3d()` 而非修改 `scrollLeft`。translate3d 走 GPU 合成，不触发 layout/paint，性能远优于 scroll 方案。过渡期间用 CSS transition 控制 easing 曲线，JS 只负责计算目标偏移量。

### 为什么圆点页码用 clip-path 而非 border-radius

设计需求是平行四边形斜角页码，具体角度由视觉决定。`clip-path: polygon()` 比 CSS `skewX()` 更精确控制斜角像素偏移（4px），且不会影响内部文字渲染（虽然页码内没有文字，但统一方案更清晰）。圆角在 v1.0.1 中已完全移除。

---

## 踩坑经验

### 坑1：手机端水平溢出滚动条（v1.0.2 修复）

**现象**：手机浏览器访问页面时，右侧出现水平滚动条，溢出空间会自己动、时大时小。

**根因**：三个因素叠加——

1. `.zpt-track` 上的 `will-change: transform` 将元素提升为 GPU 合成层，破坏父级 `.zpt-viewport` 的 `overflow: hidden` 裁剪效果。这是主犯。
2. Grid 列使用 `1fr` 在非整数 viewport 宽度下产生子像素舍入溢出（如 345px ÷ 2 = 164.5px → 舍入为 165px × 2 = 346px > 345px）。这是从犯。
3. 长合作伙伴名称可能撑破 `.zpt-item` 再撑破 `.zpt-page`。这是帮凶。

**为什么抖动时大时小**：autoplay 每翻一页触发 `translate3d()` 过渡，GPU 层重新合成时，新页面和旧页面的 grid 子像素舍入值不同，溢出量也会变化。这就解释了用户描述的"自己动、时大时小"。

**修复方案**：
- 移除 `will-change: transform`（根因修复）
- `.zpt-wrapper`、`.zpt-page` 加 `overflow: hidden`（多层保险）
- `.zpt-viewport` 加 `contain: paint`（隔离布局）
- `.zpt-page`、`.zpt-item`、`.zpt-item-image` 加 `min-width: 0`（防止 grid item 撑破容器）
- `.zpt-item-text` 加 `overflow-wrap: break-word` + `max-width: 100%`（长文本换行）

**关键教训**：`will-change` 是双刃剑——虽然能优化动画性能，但在 flex/grid + overflow:hidden 的组合中会破坏裁剪。轮播场景下 `translate3d` 自身已经触发 GPU 合成，额外的 `will-change` 是不必要且有害的。

### 坑2：Elementor CSS 变量通过 selectors 输出不生效（v1.0.1 修复）

**现象**：`gap_h` / `gap_v` 控件设置了 `selectors` 输出 CSS 变量，但 `.zpt-page` 的 `column-gap: var(--zpt-gap-h)` 拿不到值。

**根因**：Elementor 的 `selectors` 机制对 CSS 自定义属性的处理有时序问题——变量定义和变量引用可能不在同一批 CSS 输出中。

**修复**：在 CSS 文件中显式写出 `column-gap: var(--zpt-gap-h, 20px)`，确保 `var()` 引用在静态 CSS 中声明，Elementor 只负责通过 `selectors` 写到 wrapper 上的变量值。这与 Mega Menu 的颜色方案教训一致：自定义属性最好在静态 CSS 中以 `var(--name, default)` 形式引用，服务端/Elementor 仅负责写变量值。

### 坑3：分隔线默认间距过大（v1.0.1 修复）

**现象**：分隔线与图片之间的间距默认值过大，导致视觉不协调。

**修复**：将 `separator_image_gap` 默认值从 15px 改为 0。

### 坑4：图片文字来源单一（v1.0.2 修复）

**现象**：只取 `get_the_title()`，有些用户在媒体库的 Caption 字段填了内容但 title 为空，导致文字缺失。

**修复**：新增 `image_text_source` 下拉控件，可选 title 或 caption。增加回退链：title 为空 → 尝试 alt text。

---

## 文件修改注意事项

1. **文件命名规范**：`ttt-partners-{type}-v{x.y.z}.txt`，type 为 php/css/js。保留所有旧版本文件，不删除不覆盖。

2. **版本号策略**：小版本号（1.0.x）用于 bug 修复，中版本号（1.x.0）用于功能新增。若涉及架构变更（如响应式重做），应升大版本号。

3. **File Write/Edit 截断问题**：`Write` 工具在处理 ~40KB+ 文件时、`Edit` 工具在对 >25KB 文件做大面积替换时存在已知截断问题。Partners 的三文件目前都在 15KB 以下，Write 直接写入没有问题。如果未来文件膨胀，应改用 Python 脚本通过 bash 构建。

4. **部署后验证**：CSS 补丁部署后，在 Elementor 编辑器中重新保存包含该 widget 的页面以刷新 Elementor CSS 缓存。另需在真实手机浏览器上验证效果——桌面 Chrome DevTools 模拟器的 GPU 合成行为与真实移动浏览器有差异。

5. **CSS 变量命名**：Partners 的 CSS 变量前缀统一为 `--zpt-`，与 Mega Menu 的 `--zmm-` 严格区分，避免同一页面使用两个 widget 时变量冲突。

---

## 已知边界情况

1. **末页不满**：当总 item 数不能被 `cols × rows` 整除时，末页的 grid 会有空位。当前的 grid 布局下 item 会自动靠左排列，空位在右侧，行为符合预期。

2. **只显示一页时**：JS 会隐藏页码容器（`display: none`），同时跳过所有轮播逻辑，track 不可滑动。这是因为 `totalPages <= 1` 时提前 return。

3. **无图片时**：PHP render 输出占位提示文字，提示用户在面板中添加图片。

4. **超多图片**：无性能上限，但自动轮播在页面数极多时用户体验下降（需要很久才能看完一轮），建议控制在 10 页以内。

5. **图片尺寸不统一**：`object-fit: contain` 模式下，不同比例的图片会在容器内保持完整显示，上下或左右留白。如果期望所有 logo 填满统一高度，使用 `cover` 模式。

---

## 未来 AI 会话的快速启动

修改 TTT Partners widget 时：

1. 读取 `D:\#工作文档\01.合作项目\TTTWorks - ttt\Snippets\` 中最新版本的三个文件（当前为 PHP v1.0.2 + CSS v1.0.2 + JS v1.0.0）
2. 确认修改范围（PHP 控件/render、CSS 样式、JS 交互），只改相关文件
3. 遵循文件命名规范，递增版本号，保留旧版本
4. CSS 新增样式注意 `--zpt-` 前缀，不要污染其他 widget
5. 如果涉及轮播行为，务必测试 `will-change` 和 `overflow:hidden` 的兼容性——这是最容易踩的坑
6. 修改后验证三个文件的完整性（PHP 以 `} );` 结尾、包含 `widgets_manager->register`；CSS 以 `<?php } );` 结尾；JS 以 `}` 结尾）
7. 提醒用户在生产环境部署后，在真实手机上验证（特别是 iOS Safari）

---

## 相关 widget 参考

- [TTT Mega Menu 开发指南](ttt-mega-menu-dev-guide.md) — 同项目的超级菜单 widget，其中有关于 Elementor 响应式宽度驱动、颜色绕过 CSS 缓存、动画门控等更深入的架构讨论
- [TTT Hotspot on Map 开发记录](ttt-hotspot-on-map-development-summary-v1.0.md) — SVG 叠加层 + 贝塞尔曲线地图热点标记 widget
- [TTT Smooth Scroll 项目](ttt-smooth-scroll-project.md) — 全页面惯性平滑滚动，其中记录了与 Elementor Popup 冲突的踩坑经验
