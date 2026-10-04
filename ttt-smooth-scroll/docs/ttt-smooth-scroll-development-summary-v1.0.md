# TTT Smooth Scroll — 开发经验与踩坑记录

> 全页面惯性平滑滚动，Code Snippets 三文件部署，rAF + lerp 插值核心

---

## 版本信息

| 文件 | 版本 | 行数 |
|------|------|------|
| ttt-smooth-scroll-php-v1.0.0.txt | v1.0.0 | 132 |
| ttt-smooth-scroll-css-v1.0.0.txt | v1.0.0 | 44 |
| ttt-smooth-scroll-js-v1.0.0.txt | v1.0.1 | 452 |

## 技术架构

**核心机制**：Virtual Scroll（参考 LENIS）

```
每一帧：current += (target - current) × lerp
window.scrollTo(0, current)
```

- `target`：用户操作累积的目标位置
- `current`：当前视觉位置（虚拟滚动值）
- `lerp`：插值因子（默认 0.07 = 苹果官网手感）
- 不劫持原生 scroll——用 `window.scrollTo()` 修改，原生事件照常触发，Elementor Sticky / Mega Menu 等依赖 scroll 的组件不受影响

**覆盖的滚动方式**：

| 方式 | 实现要点 |
|------|---------|
| 鼠标滚轮 | 帧内合并 delta 再推 target，过滤触控板微量滚动（`minWheelDelta`） |
| 键盘方向键 | 每次 ±80px，PageUp/Down 跳视口 90%，Home/End 到顶/底 |
| 空格键 | 同 PageDown，只在焦点不在 input 时接管 |
| 中键拖拽 | 按下中键记录原点，上下移动控制速度，带 8px 死区，松手后 lerp 自然减速 |
| 锚点 #hash | easeOutCubic 缓动动画，可被用户手动滚轮中断 |
| 触摸 | `enableMobile` 开关控制，默认关闭走浏览器原生动量 |

**三层安全防线**：
1. 不拦截可滚动子容器（overflow:auto/scroll）、表单元素、`data-no-smooth-scroll` 属性标记
2. `prefers-reduced-motion` 自动降级
3. Elementor 编辑器内不启动

**参数配置**：通过 WordPress Settings API 存储在 `wp_options`，仪表盘"设置 > TTT Smooth Scroll"可视化管理，JS 通过 `get_option()` 读取注入。

---

## 踩坑记录

### 坑 1：Elementor Popup 不生效 — 锚点拦截吃掉了 Popup 的 hash 链接

**现象**：开启 smooth scroll 后，网站所有 Elementor Popup 点击无反应，完全弹不出来。

**排查**：F12 Console 无报错，Network 无异常，但 Popup 的 click 事件如同石沉大海。

**根因**：`onAnchorClick` 用 `a[href^="#"]` 全量捕获了所有 `#` 开头的链接，包含 Elementor Popup 的 `<a href="#elementor-action:...">`。`preventDefault()` 后 Popup 的 JS 绑定收不到事件。

**修复**：新增 `isHashScrollable()` 预判函数——调用 `getAnchorTarget(hash)` 检查该 hash 是否真的指向页面上的 DOM 元素。`#elementor-action:...` 在页面中不存在对应 ID，返回 null，直接放行给原生逻辑。

```javascript
function isHashScrollable(hash) {
    return getAnchorTarget(hash) !== null;  // 只有页面真有这个 ID 才接管
}

function onAnchorClick(e) {
    // ... 前面省略 ...
    if (!isHashScrollable(href)) return;  // 放行 Elementor Popup 等非滚动 hash
    // ... 后面才 preventDefault + startAnchorAnimation ...
}
```

**教训**：**拦截 hash 链接前必须先验证目标元素是否存在。** 不可盲目接管所有 `#` 链接——页面上的 hash 链接用途远不止锚点跳转（Popup、Tab、Modal、Router 都可能用）。

---

### 坑 2：Elementor Popup 遮罩下滚动仍然被接管

**现象**：Popup 虽然修好了能弹出来，但在遮罩层上滚鼠标滚轮时，背后的页面也跟着滚动。

**根因**：`bodyLocked()` 只检测了 `<body>` 的 `overflow:hidden`：

```javascript
// 有问题的版本
function bodyLocked() {
    var bs = window.getComputedStyle(document.body);
    return bs.overflow === 'hidden' || bs.overflowY === 'hidden';
}
```

Elementor Popup 打开时，`overflow:hidden !important` 是加在 `<html>` 上的（通过 `elementorFrontend` 直接操作 `documentElement.style`），`<body>` 保持原样不动。于是 `bodyLocked()` 永远返回 false，wheel/keyboard/mid-click 三大接管全部继续生效。

**修复**：`bodyLocked()` 同时检测 `<html>` 和 `<body>`：

```javascript
function bodyLocked() {
    var bs = window.getComputedStyle(document.body);
    var hs = window.getComputedStyle(document.documentElement);
    return bs.overflow === 'hidden' || bs.overflowY === 'hidden' ||
           hs.overflow === 'hidden' || hs.overflowY === 'hidden';
}
```

**教训**：**Elementor 的弹窗/遮罩锁定加在 `<html>` 而非 `<body>`。** 任何页面级别的全局守卫函数（scroll 接管、事件拦截、状态判断）都必须双层检测 `documentElement` + `body`，漏一层就会在遮罩下仍然接管页面交互。

---

### 坑 3：`add_dashboard_page` 菜单找不到

**现象**：部署设置页后，用户在仪表盘左侧翻了好几遍找不到"TTT Smooth Scroll"菜单项。

**根因**：`add_dashboard_page` 把子菜单放在"仪表盘"（首页）下方，但这个位置通常被"更新"、"Site Health"等系统项和一堆插件小工具淹没，视觉上极不显眼。

**修复**：改用 `add_options_page`，放在"设置"子菜单下，跟所有标准插件设置页（如 Elementor Settings、WP Rocket Settings）同位置。

| 函数 | 位置 | 可见性 |
|------|------|--------|
| `add_options_page` | 设置 > xxx | ✅ 最标准、最易找 |
| `add_dashboard_page` | 仪表盘 > xxx | ❌ 底部淹没 |
| `add_menu_page` | 顶层菜单 | 仅独立功能用 |

**教训**：**为用户做设置页，默认用 `add_options_page`**。除非有充分理由（如独立后台面板），不要用 `add_dashboard_page`。

---

### 坑 4：拆分文件时同名误删

**现象**：把单文件 JS snippet（`ttt-smooth-scroll-js-v1.0.0.txt`）拆成三文件后，用 glob 匹配 `ttt-smooth-scroll*` 看到旧的 JS 文件还在，执行删除。结果新拆的 JS 恰好也叫 `ttt-smooth-scroll-js-v1.0.0.txt`，被一并删了。

**根因**：旧单文件和拆分后的新 JS 文件同名同版本号。删除指令没有区分新旧。

**操作顺序错误**：
1. 建新 PHP 文件 ✅
2. 建新 CSS 文件 ✅
3. 删除旧单文件（glob 匹配）❌ —— 此时新 JS 还没建，但 delete 用的是通配模式

**正确顺序**：
1. 先建完所有三个新文件
2. `glob` 验证三个新文件都存在
3. 精确删除旧文件（不用通配，用精确路径）

**教训**：**拆分文件时，先建全部新文件 → 验证 → 再删旧的。** 如果新旧版本号相同，旧文件用明确的绝对路径删除，不要依赖 glob 模式匹配。

---

### 坑 5：单文件快速验证 → 三文件正式部署的节奏

**背景**：用户最初的需求是"写个 snippets 实现平滑滚动"。用单文件 JS（全部逻辑写在一个 .txt，PHP 包裹）快速交付验证效果，用户很满意。随后用户要求加仪表盘设置页、拆成三文件。

**经验总结**：

| 阶段 | 形式 | 目的 |
|------|------|------|
| 验证 | 单文件 JS | 快速部署，一个 snippet 跑起来看效果 |
| 部署 | PHP + CSS + JS 三文件 | 职责分离，各自独立维护升级 |

**教训**：不要一上来就按最终工程标准写三文件——先验证核心效果、让用户确认方向和手感、再拆分成标准结构。用户看到效果满意后，加需求的意愿会高很多，拆分时也更清楚哪些参数需要暴露到设置页。

---

### 坑 6：滚动条拖拽无法拦截（行业共识）

用户最初希望覆盖滚动条拖拽操作，但浏览器不暴露 scrollbar 拖拽事件——`scroll` 事件在拖拽时持续触发但无法 `preventDefault()`。包括 LENIS 在内的所有平滑滚动库都不拦截滚动条拖拽。

**处理方式**：不做任何处理，接受原生行为。滚轮/键盘/中键的平滑体验本身能覆盖绝大多数用户的滚动习惯。

---

## 与现有 Widget 的兼容性验证

部署前对项目内所有 widget 做了逐行 JS 扫描：

| Widget | 滚动相关操作 | 冲突？ |
|--------|-------------|--------|
| Google Maps | `scrollwheel` 是 Maps API 内部参数 | 否 |
| Mega Menu | 只读 `window scroll` 做 re-position | 否 |
| Hotspot on Map | `requestAnimationFrame` 仅用于 SVG 轨道动画 | 否 |
| Category Tabs | 方向键拦截仅在组件 focus 时生效 | 否 |
| Card/Product Slider | 不涉及任何 scroll | 否 |

**结论**：零冲突。rAF + `window.scrollTo()` 的 Virtual Scroll 方案天然兼容所有监听原生 scroll 事件的组件。
