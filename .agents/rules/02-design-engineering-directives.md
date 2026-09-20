---
description: Design engineering directives for typography, single accent colors, layout discipline, hero viewports, bento grids, and tactile interactions.
globs: ["*.html", "*.php", "*.css", "*.scss", "*.jsx", "*.tsx", "*.vue"]
---

# Design Engineering Directives

> Derived from `@/skill/SKILL.md` (Sections 3 and 4).
> Related rules: `@01-brief-and-design-system.md`, `@03-ai-tells-and-forbidden-patterns.md`.

---

## 1. Stack & Architecture Conventions

- **Server vs Client Separation:** In React/Next.js, Server Components render static layouts by default. Any component using Motion, scroll listeners, or pointer physics must be an isolated client leaf (`'use client'`).
- **Continuous Input State Rule:** Never use `useState` to track continuous user inputs (mouse position, scroll progress, physics). Use Motion `useMotionValue` / `useTransform` / `useScroll` to avoid re-rendering entire component trees.
- **Icon Discipline:** Allowed libraries in priority order: `@phosphor-icons/react`, `hugeicons-react`, `@radix-ui/react-icons`, `@tabler/icons-react`. `lucide-react` is discouraged unless explicitly requested. Never hand-roll decorative SVG icon paths. Standardize `strokeWidth` globally (e.g., `1.5` or `2.0`). One icon family per project.
- **Emoji Ban:** Raw emojis are forbidden in markup and visible text by default. Use SVG glyphs from the icon library.
- **Viewport Stability:** Never use `h-screen` for full-height hero sections. Always use `min-h-[100dvh]` to prevent mobile address bar jump.
- **CSS Grid over Flex-Math:** Never use complex flex percentage math like `w-[calc(33%-1rem)]`. Use CSS Grid: `grid grid-cols-1 md:grid-cols-3 gap-6`.

---

## 2. Typography Rules

### 2.1 Scale & Hierarchy
- **Headlines:** Default `text-4xl md:text-6xl tracking-tighter leading-none`.
- **Body:** Default `text-base text-gray-600 leading-relaxed max-w-[65ch]`.
- **Sans Default:** `Inter` is discouraged as an automatic default. Choose `Geist`, `Outfit`, `Cabinet Grotesk`, or `Satoshi`.
- **Allowed Pairings:** `Geist` + `Geist Mono`, `Satoshi` + `JetBrains Mono`, `Cabinet Grotesk` + `Inter Tight`.

### 2.2 Strict Serif Discipline
- Serif is **very discouraged** as an automatic default for "creative/premium" briefs.
- Serif is permitted only if:
  1. The brand brief explicitly names a serif font, or
  2. The aesthetic is genuinely editorial, publication, or luxury heritage.
- **Specifically Banned as Defaults:** `Fraunces` and `Instrument_Serif` (LLM-favorite serifs).
- **Emphasis Rule:** When emphasizing a word in a headline, use italic or bold of the **same font family**. Never mix a random serif word into a sans headline.
- **Italic Descender Clearance:** When italic is used on words containing `y g j p q`, use `leading-[1.1]` minimum and add `pb-1` / `mb-1` reserve to prevent clipping.

---

## 3. Color Calibration & Consistency

- **Single Accent Limit:** Maximum 1 accent color with saturation < 80%.
- **The Lila Rule:** No automatic purple button glows or neon mesh gradients. Use neutral bases (Zinc, Slate, Stone) with high-contrast singular accents (Emerald, Electric Blue, Deep Rose, Burnt Orange).
- **Color Consistency Lock:** Once an accent is chosen, use it across the entire page. Do not switch from warm gray to blue CTAs or teal badges in later sections.
- **Premium-Consumer Palette Ban:** Banned as automatic defaults for cookware, wellness, and luxury:
  - Banned backgrounds: `#f5f1ea`, `#f7f5f1`, `#fbf8f1`, `#efeae0`, `#ece6db` ("warm beige/cream/bone").
  - Banned accents: `#b08947`, `#b6553a`, `#9a2436`, `#9c6e2a`, `#bc7c3a` ("brass/clay/oxblood/ochre").
  - Banned text: `#1a1714`, `#1a1814` ("espresso near-black").
  - Rotate alternatives: Cold Luxury (silver-grey + chrome), Forest (deep green + bone), Pure monochrome with single saturated pop.

---

## 4. Materiality, Shadows & Tactility

- **Card Restraint:** Use card containers only when elevation communicates real hierarchy. Otherwise group with `border-t`, `divide-y`, or negative space.
- **Shadow Tinting:** Tint shadows to background hue. Never use pure-black drop shadows on light surfaces.
- **Shape Consistency Lock:** Enforce one corner-radius scale across the page: all-sharp (`rounded-none`), all-soft (`rounded-xl`), or all-pill for interactive elements. Do not mix round buttons with square cards.
- **Tactile Interaction:** Interactive elements on `:active` must provide physical feedback using `-translate-y-[1px]` or `scale-[0.98]`.
- **Button Contrast (WCAG AA):** Ensure button text meets minimum 4.5:1 contrast against button background. Transparent ghost buttons over photos require backdrops.
- **CTA Button Wrap Ban:** Button text must fit on a single line on desktop. Shorten copy (1-3 words max) or widen button container.
- **No Duplicate CTA Intent:** Pick one label per intent across the page (e.g., do not mix "Get in touch", "Contact us", and "Let's talk").

---

## 5. Layout Discipline (Hard Rules)

### 5.1 Hero Viewport Discipline
- **Initial Viewport Fit:** Headline max 2 lines on desktop, subtext max **20 words** and max 4 lines, CTAs visible without scroll.
- **Hero Top Padding Cap:** Desktop top padding max `pt-24` (≈6rem). Never let hero content float halfway down the screen.
- **Hero Stack Discipline (Max 4 Text Elements):**
  1. Eyebrow OR brand strip (optional, pick zero or one)
  2. Headline (max 2 lines)
  3. Subtext (max 20 words)
  4. CTAs (1 primary + max 1 secondary)
- **Trust Logo Wall:** "Trusted by" logo strips belong below the hero section, never inside it.

### 5.2 Navigation Standards
- Navigation items must render on a single line at desktop (`lg` / 1024px).
- Navigation height cap: 64-72px default, 80px maximum.

### 5.3 Bento Grids & Section Rhythm
- **Asymmetric Bento Rhythm:** Vary cell spans (`bento-col-2`), alternate full-width cards, provide visual rhythm instead of uniform repeating boxes.
- **Bento Cell Count Rule:** Grid must have exactly as many cells as content items (3 items = 3 cells, 5 items = 5 cells). No blank or empty tiles.
- **Bento Background Diversity:** Multi-cell grids must include visual variation in at least 2-3 cells (real image, pattern, tinted background), not 6 identical white-on-white boxes.
- **Section Layout Repetition Ban:** A landing page with 8 sections must use at least 4 distinct layout families.
- **Zigzag Alternation Cap:** Alternating image-left/text-right then image-right/text-left is capped at maximum 2 consecutive sections.
- **Eyebrow Restraint:** Maximum 1 eyebrow label per 3 sections (hero counts as 1). A page with 9 sections may have at most 3 eyebrows.
- **Split-Header Ban:** Do not use "left giant headline + right small body paragraph" headers as default. Stack headline and body vertically.
- **Mobile Collapse:** Multi-column layouts must declare explicit `< 768px` fallback to single column in the same component.

---

## 6. Visual Assets & Content Density

- **Asset Priority:** 1) Image generation tool, 2) Real photos (`picsum.photos/seed/...`), 3) Explicit placeholder slots (`<!-- TODO: hero photo -->`). Div-based fake screenshots are strictly banned.
- **Real Logo Marks:** Use Simple Icons SVG or devicon for social proof walls. Plain text wordmarks are banned.
- **Logo Wall Rule:** Logos only. Do not print category labels below logos (e.g., no "Stripe" + "payments" underneath).
- **Content Density:** Short headline (≤ 8 words) + short subtext (≤ 25 words).
- **Spec Sheets & Long Lists:** Spec sheets with 10 rows and `border-b` on every row are banned. Use 2-column card grids, horizontal pill chips, or grouped clusters with sparse dividers.
- **Quote Restraint:** Maximum 3 lines of body text per quote.
