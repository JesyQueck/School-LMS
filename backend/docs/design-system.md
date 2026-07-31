# School LMS — Design System

Version: 1.0.0
Status: Approved for implementation

## 1. Design Principles

- **Modern SaaS:** Clean, professional, data-dense but not cluttered.
- **Fast:** Minimal DOM depth, efficient Tailwind utilities, no heavy JS frameworks beyond Alpine.js.
- **Responsive:** Mobile-first, works on phones, tablets, laptops, and projectors.
- **WCAG Accessible:** Minimum AA contrast ratios, focus indicators, semantic HTML, keyboard navigation.
- **Premium Appearance:** Subtle shadows, consistent spacing, refined typography, restrained color usage.

---

## 2. Color System

### 2.1 Primary Brand Colors

| Token | Hex | Usage |
|-------|-----|-------|
| `--color-primary-50` | `#eff6ff` | Light backgrounds, hover states |
| `--color-primary-100` | `#dbeafe` | Subtle backgrounds |
| `--color-primary-200` | `#bfdbfe` | Borders, dividers |
| `--color-primary-300` | `#93c5fd` | Disabled states |
| `--color-primary-400` | `#60a5fa` | Hover accents |
| `--color-primary-500` | `#3b82f6` | **Primary brand color** — main buttons, links, active states |
| `--color-primary-600` | `#2563eb` | Hover states, pressed buttons |
| `--color-primary-700` | `#1d4ed8` | Active navigation, focus rings |
| `--color-primary-800` | `#1e40af` | Dark mode primary |
| `--color-primary-900` | `#1e3a8a` | Dark mode hover |

### 2.2 Secondary Colors

| Token | Hex | Usage |
|-------|-----|-------|
| `--color-secondary-50` | `#f8fafc` | Light backgrounds |
| `--color-secondary-100` | `#f1f5f9` | Card backgrounds, table stripes |
| `--color-secondary-200` | `#e2e8f0` | Borders, dividers |
| `--color-secondary-300` | `#cbd5e1` | Disabled text |
| `--color-secondary-400` | `#94a3b8` | Placeholder text |
| `--color-secondary-500` | `#64748b` | **Secondary text** — labels, captions |
| `--color-secondary-600` | `#475569` | Body text in light mode |
| `--color-secondary-700` | `#334155` | Headings in light mode |
| `--color-secondary-800` | `#1e293b` | Dark mode body text |
| `--color-secondary-900` | `#0f172a` | Dark mode headings |

### 2.3 Accent Colors

| Token | Hex | Usage |
|-------|-----|-------|
| `--color-accent-50` | `#fdf4ff` | Light backgrounds |
| `--color-accent-100` | `#fae8ff` | Subtle highlights |
| `--color-accent-200` | `#f5d0fe` | Borders |
| `--color-accent-300` | `#f0abfc` | Soft accents |
| `--color-accent-400` | `#e879f9` | Hover accents |
| `--color-accent-500` | `#d946ef` | **Accent color** — CTAs, highlights, badges |
| `--color-accent-600` | `#c026d3` | Hover states |
| `--color-accent-700` | `#a21caf` | Active states |
| `--color-accent-800` | `#86198f` | Dark mode accent |
| `--color-accent-900` | `#701a75` | Dark mode hover |

### 2.4 Semantic Colors

| Token | Hex | Usage |
|-------|-----|-------|
| `--color-success-50` | `#f0fdf4` | Success backgrounds |
| `--color-success-500` | `#22c55e` | Success icons, text |
| `--color-success-600` | `#16a34a` | Success buttons |

| Token | Hex | Usage |
|-------|-----|-------|
| `--color-warning-50` | `#fffbeb` | Warning backgrounds |
| `--color-warning-500` | `#f59e0b` | Warning icons, text |
| `--color-warning-600` | `#d97706` | Warning buttons |

| Token | Hex | Usage |
|-------|-----|-------|
| `--color-danger-50` | `#fef2f2` | Error backgrounds |
| `--color-danger-500` | `#ef4444` | Error icons, text |
| `--color-danger-600` | `#dc2626` | Error buttons, destructive actions |

| Token | Hex | Usage |
|-------|-----|-------|
| `--color-info-50` | `#eff6ff` | Info backgrounds |
| `--color-info-500` | `#3b82f6` | Info icons, text |
| `--color-info-600` | `#2563eb` | Info buttons |

### 2.5 Neutral Palette (Light Mode)

| Token | Hex | Usage |
|-------|-----|-------|
| `--color-white` | `#ffffff` | Card backgrounds, page bg |
| `--color-neutral-50` | `#f9fafb` | Page background |
| `--color-neutral-100` | `#f3f4f6` | Card backgrounds, table stripes |
| `--color-neutral-200` | `#e5e7eb` | Borders, dividers |
| `--color-neutral-300` | `#d1d5db` | Disabled borders |
| `--color-neutral-400` | `#9ca3af` | Disabled text |
| `--color-neutral-500` | `#6b7280` | Secondary text |
| `--color-neutral-600` | `#4b5563` | Body text |
| `--color-neutral-700` | `#374151` | Headings |
| `--color-neutral-800` | `#1f2937` | Dark headings |
| `--color-neutral-900` | `#111827` | Darkest text |

### 2.6 Dark Mode Overrides

Dark mode swaps neutral scale and darkens brand colors:

| Token | Hex |
|-------|-----|
| `--color-dark-bg` | `#0f172a` |
| `--color-dark-surface` | `#1e293b` |
| `--color-dark-border` | `#334155` |
| `--color-dark-text` | `#f1f5f9` |

---

## 3. Typography

### 3.1 Font Family

**Primary:** `'Instrument Sans'`, `ui-sans-serif`, `system-ui`, `sans-serif`
**Mono:** `'JetBrains Mono'`, `ui-monospace`, `monospace`

### 3.2 Type Scale

| Class | Size | Line Height | Usage |
|-------|------|-------------|-------|
| `text-xs` | 0.75rem | 1rem | Captions, timestamps, helper text |
| `text-sm` | 0.875rem | 1.25rem | Labels, secondary text, table cells |
| `text-base` | 1rem | 1.5rem | Body text, inputs, buttons |
| `text-lg` | 1.125rem | 1.75rem | Lead paragraphs |
| `text-xl` | 1.25rem | 1.75rem | Card titles, section headings |
| `text-2xl` | 1.5rem | 2rem | Page headings |
| `text-3xl` | 1.875rem | 2.25rem | Dashboard stats |
| `text-4xl` | 2.25rem | 2.5rem | Hero text, welcome screens |

### 3.3 Font Weights

| Weight | Class | Usage |
|--------|-------|-------|
| 400 | `font-normal` | Body text, inputs |
| 500 | `font-medium` | Labels, button text |
| 600 | `font-semibold` | Headings, table headers |
| 700 | `font-bold` | Page titles, emphasis |

### 3.4 Accessibility Rules

- Minimum body text size: `text-base` (16px)
- Line height for body: `leading-relaxed` or `leading-normal`
- Color contrast ratio minimum: **4.5:1** for normal text, **3:1** for large text
- Never use color alone to convey meaning — always pair with icon or text

---

## 4. Icons

**Library:** Lucide Icons
**Style:** 24px, 2px stroke, outlined
**Usage:** Inline SVGs via `x-data` or Alpine components, or `<i>` tags with data attributes.

Common icons:
- Dashboard: `layout-dashboard`
- Students: `users`
- Teachers: `graduation-cap`
- Parents: `user-check`
- Classes: `school`
- Results: `clipboard-list`
- Finance: `wallet`
- Attendance: `calendar-check`
- Announcements: `megaphone`
- Settings: `settings`
- Logout: `log-out`
- Search: `search`
- Filter: `filter`
- More: `more-horizontal`
- Chevron down: `chevron-down`
- Plus: `plus`
- Edit: `pencil`
- Delete: `trash-2`
- Eye: `eye`
- Eye off: `eye-off`
- Bell: `bell`
- Moon: `moon`
- Sun: `sun`

---

## 5. Layout

### 5.1 Breakpoints

| Breakpoint | Min Width | Usage |
|------------|-----------|-------|
| `sm` | 640px | Phablets, small tablets |
| `md` | 768px | Tablets |
| `lg` | 1024px | Laptops, small desktops |
| `xl` | 1280px | Desktops |
| `2xl` | 1536px | Large desktops, projectors |

### 5.2 Grid System

- Use Tailwind's 12-column grid: `grid grid-cols-12`
- Gutters: `gap-6` (24px) for cards, `gap-4` (16px) for dense tables/forms
- Container max-width: `max-w-7xl mx-auto px-4 sm:px-6 lg:px-8`

### 5.3 Sidebar

- Width: `w-64` (256px) on desktop, collapsible on mobile
- Position: Fixed left, below header on mobile
- Background: White in light mode, `#1e293b` in dark mode
- Border right: `border-r border-neutral-200 dark:border-dark-border`
- Active item: `bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400`
- Hover: `hover:bg-neutral-100 dark:hover:bg-neutral-800`
- Transition: `transition-colors duration-150`

**Component:** `<x-layout.sidebar-item href="..." icon="..." label="..." active="..." />`

| Prop | Type | Description |
|------|------|-------------|
| href | string | Link URL |
| icon | string | Lucide icon name |
| label | string | Menu item text |
| active | bool | Whether item is active |

Icons supported: `layout-dashboard`, `school`, `graduation-cap`, `users`, `clipboard-list`, `wallet`, `file-text`, `book-open`, `calendar`, and more.

### 5.4 Navbar / Header

- Height: `h-16` (64px)
- Background: White in light mode, `#1e293b` in dark mode
- Border bottom: `border-b border-neutral-200 dark:border-dark-border`
- Left: Breadcrumbs or page title
- Right: Search, notifications, user dropdown, theme toggle
- Sticky: `sticky top-0 z-40`

### 5.5 Footer

- Height: Auto, min `h-12`
- Background: `bg-neutral-50 dark:bg-dark-surface`
- Border top: `border-t border-neutral-200 dark:border-dark-border`
- Content: Copyright, help link, version

### 5.6 Page Container

- Padding: `p-4 sm:p-6 lg:p-8`
- Max width: `max-w-7xl`
- Background: `bg-neutral-50 dark:bg-dark-bg`
- Min height: `min-h-screen`

---

## 6. Component Specifications

### 6.1 Buttons

| Variant | Classes | Usage |
|---------|---------|-------|
| Primary | `bg-primary-600 hover:bg-primary-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm transition-colors` | Main CTAs, form submits |
| Secondary | `bg-white dark:bg-dark-surface border border-neutral-300 dark:border-dark-border text-neutral-700 dark:text-dark-text hover:bg-neutral-50 dark:hover:bg-neutral-800 font-medium px-4 py-2 rounded-lg transition-colors` | Secondary actions, cancel |
| Accent | `bg-accent-600 hover:bg-accent-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm transition-colors` | Highlights, premium features |
| Danger | `bg-danger-600 hover:bg-danger-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm transition-colors` | Destructive actions |
| Ghost | `text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 font-medium px-4 py-2 rounded-lg transition-colors` | Tertiary actions |
| Link | `text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-medium underline-offset-4 hover:underline` | Inline links |

**Sizes:**
- `sm`: `px-3 py-1.5 text-sm`
- `md`: `px-4 py-2 text-base`
- `lg`: `px-5 py-2.5 text-lg`

**Accessibility:**
- Minimum touch target: `44x44px`
- Focus ring: `focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-dark-bg`
- Disabled: `disabled:opacity-50 disabled:cursor-not-allowed`

---

### 6.2 Inputs

| Element | Classes |
|---------|---------|
| Input wrapper | `relative` |
| Input field | `w-full rounded-lg border border-neutral-300 dark:border-dark-border bg-white dark:bg-dark-surface text-neutral-900 dark:text-dark-text placeholder-neutral-400 dark:placeholder-neutral-500 px-3 py-2 text-base transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent` |
| Input with error | `border-danger-500 focus:ring-danger-500` |
| Error message | `mt-1 text-sm text-danger-600 dark:text-danger-400` |
| Label | `block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1` |
| Helper text | `mt-1 text-xs text-neutral-500 dark:text-neutral-400` |

**Accessibility:**
- Label `for` attribute matches input `id`
- Error messages linked via `aria-describedby`
- Required fields: `required` attribute + `*` in label

---

### 6.3 Textareas

Same as inputs, with:
- `resize-vertical` or `resize-none`
- Min height: `min-h-[120px]`

---

### 6.4 Selects

Same base as inputs, with:
- `appearance-none` + custom chevron icon
- Chevron positioned absolute right: `right-3 top-1/2 -translate-y-1/2 pointer-events-none`
- Padding right: `pr-10`

---

### 6.5 Checkboxes

| Element | Classes |
|---------|---------|
| Checkbox | `h-4 w-4 rounded border-neutral-300 dark:border-dark-border text-primary-600 focus:ring-primary-500` |
| Label | `ml-2 text-sm text-neutral-700 dark:text-neutral-300` |
| Checkbox card | `border-2 rounded-lg p-4 cursor-pointer transition-colors` |

---

### 6.6 Radio Buttons

Same as checkboxes, with:
- `rounded-full` instead of `rounded`
- Group wrapper: `flex items-center gap-3`

---

### 6.7 Tables

| Element | Classes |
|---------|---------|
| Table wrapper | `overflow-x-auto rounded-lg border border-neutral-200 dark:border-dark-border` |
| Table | `min-w-full divide-y divide-neutral-200 dark:divide-dark-border` |
| Thead | `bg-neutral-50 dark:bg-dark-surface` |
| Th | `px-4 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider` |
| Td | `px-4 py-3 text-sm text-neutral-600 dark:text-neutral-400 whitespace-nowrap` |
| Tr hover | `hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors` |
| Empty state row | `text-center py-12 text-neutral-500 dark:text-neutral-400` |
| Pagination | `mt-4 flex items-center justify-between` |

**Subcomponents:**
- `<x-ui.table>` — wrapper component
- `<x-ui.table-head>` — thead wrapper
- `<x-ui.table-header-cell>` — th with default classes
- `<x-ui.table-body>` — tbody wrapper
- `<x-ui.table-cell>` — td with default classes

**Accessibility:**
- Use `<th scope="col">` for headers
- Use `<caption>` for table description
- Ensure sufficient contrast on striped rows

---

### 6.8 Cards

| Variant | Classes |
|---------|---------|
| Base card | `bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border shadow-sm` |
| Card header | `px-6 py-4 border-b border-neutral-200 dark:border-dark-border` |
| Card body | `px-6 py-4` |
| Card footer | `px-6 py-4 border-t border-neutral-200 dark:border-dark-border bg-neutral-50 dark:bg-neutral-800/30` |
| Stat card | `bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border p-6 shadow-sm` |
| Interactive card | `hover:shadow-md transition-shadow cursor-pointer` |

---

### 6.9 Badges

| Variant | Classes |
|---------|---------|
| Primary | `inline-flex items-center rounded-full bg-primary-100 dark:bg-primary-900/30 px-2.5 py-0.5 text-xs font-medium text-primary-700 dark:text-primary-300` |
| Success | `... bg-success-100 dark:bg-success-900/30 text-success-700 dark:text-success-300` |
| Warning | `... bg-warning-100 dark:bg-warning-900/30 text-warning-700 dark:text-warning-300` |
| Danger | `... bg-danger-100 dark:bg-danger-900/30 text-danger-700 dark:text-danger-300` |
| Neutral | `... bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300` |

---

### 6.10 Avatars

| Size | Classes |
|------|---------|
| sm | `h-8 w-8 rounded-full` |
| md | `h-10 w-10 rounded-full` |
| lg | `h-12 w-12 rounded-full` |
| xl | `h-16 w-16 rounded-full` |

- Fallback: `bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 flex items-center justify-center font-medium`
- Image: `object-cover`
- Status indicator: `absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-white dark:border-dark-surface`

---

### 6.11 Dropdowns

| Element | Classes |
|---------|---------|
| Trigger | `relative` |
| Menu | `absolute right-0 z-50 mt-2 w-56 rounded-xl border border-neutral-200 dark:border-dark-border bg-white dark:bg-dark-surface shadow-lg` |
| Item | `flex items-center gap-2 px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 cursor-pointer transition-colors` |
| Divider | `border-t border-neutral-200 dark:border-dark-border my-1` |
| Header | `px-4 py-2 text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider` |

**Subcomponents:**
- `<x-ui.dropdown align="right">` — wrapper with Alpine.js open state
- `<x-ui.dropdown-item href="...">` — menu item

**Accessibility:**
- `role="menu"`, `role="menuitem"`
- Keyboard navigation: Arrow keys, Enter, Escape
- Focus trap when open

---

### 6.12 Tabs

| Element | Classes |
|---------|---------|
| Tab list | `flex border-b border-neutral-200 dark:border-dark-border` |
| Tab | `px-4 py-2 text-sm font-medium text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-300 border-b-2 border-transparent hover:border-neutral-300 dark:hover:border-neutral-600 transition-colors` |
| Tab active | `text-primary-600 dark:text-primary-400 border-primary-600 dark:border-primary-400` |
| Tab panel | `mt-4` |

---

### 6.13 Modals

| Element | Classes |
|---------|---------|
| Backdrop | `fixed inset-0 z-50 bg-black/50 backdrop-blur-sm transition-opacity` |
| Modal container | `fixed inset-0 z-50 flex items-center justify-center p-4` |
| Modal | `bg-white dark:bg-dark-surface rounded-xl shadow-xl border border-neutral-200 dark:border-dark-border w-full max-w-lg max-h-[90vh] overflow-y-auto` |
| Modal header | `px-6 py-4 border-b border-neutral-200 dark:border-dark-border flex items-center justify-between` |
| Modal body | `px-6 py-4` |
| Modal footer | `px-6 py-4 border-t border-neutral-200 dark:border-dark-border flex items-center justify-end gap-3` |
| Close button | `text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300` |

**Subcomponents:**
- `<x-ui.modal.show>` — wrapper with Alpine.js show state
- `<x-ui.modal-header title="...">` — header with title and close button
- `<x-ui.modal-body>` — body content
- `<x-ui.modal-footer>` — footer with action buttons

**Accessibility:**
- `role="dialog"`, `aria-modal="true"`
- Focus trap
- Close on Escape
- Return focus to trigger on close

---

### 6.14 Alerts

| Variant | Classes |
|---------|---------|
| Success | `bg-success-50 dark:bg-success-900/20 border border-success-200 dark:border-success-800 text-success-800 dark:text-success-200 px-4 py-3 rounded-lg flex items-start gap-3` |
| Warning | `... bg-warning-50 ... text-warning-800 ...` |
| Danger | `... bg-danger-50 ... text-danger-800 ...` |
| Info | `... bg-info-50 ... text-info-800 ...` |

- Icon: `h-5 w-5 flex-shrink-0 mt-0.5`
- Dismissible: Add close button with `x-on:click="open = false"`

---

### 6.15 Toasts

Fixed position: `fixed bottom-4 right-4 z-50`
Stack: `flex flex-col gap-2`

| Variant | Classes |
|---------|---------|
| Toast base | `px-4 py-3 rounded-lg shadow-lg border flex items-center gap-3 min-w-[300px] max-w-sm` |
| Success | `bg-white dark:bg-dark-surface border-success-200 dark:border-success-800 text-success-800 dark:text-success-200` |
| Error | `... bg-danger-50 dark:bg-danger-900/20 border-danger-200 dark:border-danger-800 text-danger-800 dark:text-danger-200` |
| Info | `... bg-info-50 dark:bg-info-900/20 border-info-200 dark:border-info-800 text-info-800 dark:text-info-200` |

**Animation:**
- Enter: `transition-all duration-300 ease-out transform translate-y-0 opacity-100`
- Exit: `transform translate-y-2 opacity-0`

---

### 6.16 Pagination

| Element | Classes |
|---------|---------|
| Container | `flex items-center justify-between px-4 py-3 border-t border-neutral-200 dark:border-dark-border` |
| Info | `text-sm text-neutral-500 dark:text-neutral-400` |
| Buttons | `flex items-center gap-1` |
| Page button | `px-3 py-1 rounded-md text-sm font-medium text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 disabled:opacity-50 disabled:cursor-not-allowed` |
| Page button active | `bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300` |

---

### 6.17 Breadcrumbs

| Element | Classes |
|---------|---------|
| Container | `flex items-center gap-2 text-sm` |
| Item | `text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-300` |
| Separator | `text-neutral-400 dark:text-neutral-600` |
| Current | `text-neutral-900 dark:text-white font-medium` |

---

### 6.18 Statistics Cards

| Element | Classes |
|---------|---------|
| Card | `bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border p-6 shadow-sm` |
| Label | `text-sm font-medium text-neutral-500 dark:text-neutral-400` |
| Value | `mt-2 text-3xl font-bold text-neutral-900 dark:text-white` |
| Trend up | `mt-2 flex items-center gap-1 text-sm text-success-600 dark:text-success-400` |
| Trend down | `... text-danger-600 dark:text-danger-400` |
| Icon | `h-10 w-10 rounded-lg bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center` |

---

### 6.19 Charts

Use a lightweight charting library (e.g., Chart.js or ApexCharts) wrapped in a card:

| Element | Classes |
|---------|---------|
| Wrapper | `bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border p-6 shadow-sm` |
| Title | `text-lg font-semibold text-neutral-900 dark:text-white mb-4` |
| Canvas | `w-full h-[300px]` |

---

### 6.20 Loading States

| Type | Classes / Pattern |
|------|-------------------|
| Spinner | `h-5 w-5 border-2 border-neutral-200 dark:border-neutral-700 border-t-primary-600 rounded-full animate-spin` |
| Button loading | `relative !text-transparent` + spinner overlay |
| Skeleton text | `h-4 bg-neutral-200 dark:bg-neutral-700 rounded animate-pulse` |
| Skeleton card | `space-y-3` with multiple skeleton elements |
| Page loader | Fixed overlay with centered spinner |

**Component:** `<x-ui.loading variant="spinner" size="md" />`

| Prop | Values | Description |
|------|--------|-------------|
| variant | `spinner`, `dots` | Loading animation type |
| size | `sm`, `md`, `lg` | Spinner size |

---

### 6.21 Empty States

| Element | Classes |
|---------|---------|
| Container | `text-center py-12` |
| Icon | `mx-auto h-12 w-12 text-neutral-400 dark:text-neutral-600 mb-4` |
| Title | `text-lg font-medium text-neutral-900 dark:text-white mb-1` |
| Description | `text-sm text-neutral-500 dark:text-neutral-400 mb-4` |
| Action | `mt-4` |

**Component:** `<x-ui.empty-state title="..." description="..." :action="..." />`

---

### 6.22 Skeleton Loaders

| Element | Classes |
|---------|---------|
| Skeleton base | `bg-neutral-200 dark:bg-neutral-700 rounded animate-pulse` |
| Skeleton text | `h-4 w-full` |
| Skeleton title | `h-6 w-3/4 mb-4` |
| Skeleton card | `h-32 w-full rounded-xl` |
| Skeleton avatar | `h-10 w-10 rounded-full` |

---

### 6.16 Pagination

| Element | Classes |
|---------|---------|
| Container | `flex items-center justify-between px-4 py-3 border-t border-neutral-200 dark:border-dark-border` |
| Info | `text-sm text-neutral-500 dark:text-neutral-400` |
| Buttons | `flex items-center gap-1` |
| Page button | `px-3 py-1 rounded-md text-sm font-medium text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-800 disabled:opacity-50 disabled:cursor-not-allowed` |
| Page button active | `bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300` |

---

### 6.17 Breadcrumbs

| Element | Classes |
|---------|---------|
| Container | `flex items-center gap-2 text-sm` |
| Item | `text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-300` |
| Separator | `text-neutral-400 dark:text-neutral-600` |
| Current | `text-neutral-900 dark:text-white font-medium` |

---

### 6.18 Statistics Cards

| Element | Classes |
|---------|---------|
| Card | `bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border p-6 shadow-sm` |
| Label | `text-sm font-medium text-neutral-500 dark:text-neutral-400` |
| Value | `mt-2 text-3xl font-bold text-neutral-900 dark:text-white` |
| Trend up | `mt-2 flex items-center gap-1 text-sm text-success-600 dark:text-success-400` |
| Trend down | `... text-danger-600 dark:text-danger-400` |
| Icon | `h-10 w-10 rounded-lg bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center` |

---

### 6.19 Charts

Use a lightweight charting library (e.g., Chart.js or ApexCharts) wrapped in a card:

| Element | Classes |
|---------|---------|
| Wrapper | `bg-white dark:bg-dark-surface rounded-xl border border-neutral-200 dark:border-dark-border p-6 shadow-sm` |
| Title | `text-lg font-semibold text-neutral-900 dark:text-white mb-4` |
| Canvas | `w-full h-[300px]` |

---

### 6.20 Loading States

| Type | Classes / Pattern |
|------|-------------------|
| Spinner | `h-5 w-5 border-2 border-neutral-200 dark:border-neutral-700 border-t-primary-600 rounded-full animate-spin` |
| Button loading | `relative !text-transparent` + spinner overlay |
| Skeleton text | `h-4 bg-neutral-200 dark:bg-neutral-700 rounded animate-pulse` |
| Skeleton card | `space-y-3` with multiple skeleton elements |
| Page loader | Fixed overlay with centered spinner |

---

### 6.21 Empty States

| Element | Classes |
|---------|---------|
| Container | `text-center py-12` |
| Icon | `mx-auto h-12 w-12 text-neutral-400 dark:text-neutral-600 mb-4` |
| Title | `text-lg font-medium text-neutral-900 dark:text-white mb-1` |
| Description | `text-sm text-neutral-500 dark:text-neutral-400 mb-4` |
| Action | `mt-4` |

---

### 6.22 Skeleton Loaders

| Element | Classes |
|---------|---------|
| Skeleton base | `bg-neutral-200 dark:bg-neutral-700 rounded animate-pulse` |
| Skeleton text | `h-4 w-full` |
| Skeleton title | `h-6 w-3/4 mb-4` |
| Skeleton card | `h-32 w-full rounded-xl` |
| Skeleton avatar | `h-10 w-10 rounded-full` |

---

## 7. Accessibility (WCAG 2.1 AA)

### 7.1 Focus Management

- All interactive elements: `focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-dark-bg`
- Skip to main content link (visually hidden, focusable)
- Focus trap in modals and dropdowns

### 7.2 Color Contrast

- Body text: `neutral-600` on white = 5.7:1 ✓
- Headings: `neutral-900` on white = 16:1 ✓
- Primary buttons: White on `primary-600` = 4.8:1 ✓
- All text colors meet or exceed 4.5:1 ratio

### 7.3 Semantic HTML

- Use `<nav>`, `<main>`, `<header>`, `<footer>`, `<aside>` for layout
- Use `<button>` for actions, `<a>` for navigation
- Use `<label>` for all form inputs
- Use ARIA labels where text is not sufficient

### 7.4 Keyboard Navigation

- All interactive elements reachable via Tab
- Dropdowns: Arrow keys, Enter, Escape
- Modals: Escape closes, focus trap
- Tabs: Arrow keys navigate between tabs

---

## 8. Theme Switching (Light/Dark)

### 8.1 Implementation

- Store preference in `localStorage` as `theme`
- Apply `dark` class to `<html>` element
- Default to system preference: `prefers-color-scheme: dark`
- Toggle button in header/navbar

### 8.2 Dark Mode Classes

All components support dark mode via `dark:` prefixes:
- Backgrounds: `dark:bg-dark-surface`
- Text: `dark:text-dark-text`
- Borders: `dark:border-dark-border`
- Inputs: `dark:bg-dark-surface dark:border-dark-border dark:text-dark-text`

---

## 9. File Structure

```
resources/
├── css/
│   └── app.css                  # Tailwind imports + @theme tokens
├── js/
│   └── app.js                   # Alpine.js initialization
├── views/
│   ├── layouts/
│   │   ├── app.blade.php        # Base layout with sidebar + header
│   │   ├── guest.blade.php      # Public layout without sidebar
│   │   └── pdf.blade.php        # PDF layout for report cards
│   └── components/
│       ├── ui/
│       │   ├── button.blade.php
│       │   ├── input.blade.php
│       │   ├── select.blade.php
│       │   ├── checkbox.blade.php
│       │   ├── radio.blade.php
│       │   ├── textarea.blade.php
│       │   ├── table.blade.php
│       │   ├── table-head.blade.php
│       │   ├── table-body.blade.php
│       │   ├── table-header-cell.blade.php
│       │   ├── table-cell.blade.php
│       │   ├── card.blade.php
│       │   ├── badge.blade.php
│       │   ├── avatar.blade.php
│       │   ├── dropdown.blade.php
│       │   ├── dropdown-item.blade.php
│       │   ├── tabs.blade.php
│       │   ├── tab.blade.php
│       │   ├── tab-panel.blade.php
│       │   ├── modal.blade.php
│       │   ├── modal-header.blade.php
│       │   ├── modal-body.blade.php
│       │   ├── modal-footer.blade.php
│       │   ├── alert.blade.php
│       │   ├── toast.blade.php
│       │   ├── pagination.blade.php
│       │   ├── breadcrumbs.blade.php
│       │   ├── breadcrumb-item.blade.php
│       │   ├── stat-card.blade.php
│       │   ├── chart.blade.php
│       │   ├── loading.blade.php
│       │   ├── empty-state.blade.php
│       │   └── skeleton.blade.php
│       └── layout/
│           ├── sidebar-item.blade.php
│           ├── sidebar.blade.php
│           ├── navbar.blade.php
│           ├── header.blade.php
│           └── footer.blade.php
```

---

## 10. Implementation Order

1. **Theme tokens** in `app.css`
2. **Alpine.js** setup in `app.js`
3. **Layout components** (sidebar, navbar, header, footer)
4. **Base UI components** (button, input, select, checkbox, radio, textarea)
5. **Data display components** (table, card, badge, avatar)
6. **Overlay components** (dropdown, modal, tabs)
7. **Feedback components** (alert, toast, skeleton, empty-state, loading)
8. **Navigation components** (pagination, breadcrumbs)
9. **Specialty components** (stat-card, chart)
10. **Light/dark mode toggle**
