---
description: Forbidden AI tells, non-negotiable zero em-dash ban, and dark mode consistency protocols.
globs: ["*"]
---

# AI Tells & Forbidden Patterns

> Derived from `@/skill/SKILL.md` (Sections 4.11, 8, and 9).
> Related rules: `@01-brief-and-design-system.md`, `@02-design-engineering-directives.md`, `@05-redesign-and-preflight-check.md`.

---

## 1. The Em-Dash Ban (Non-Negotiable Hard Rule)

**The em-dash (`—`) and en-dash (`–`) are COMPLETELY BANNED.**
This is the single most recurring visual tell in AI-generated frontend code. There are zero exceptions.

### 1.1 Scope of Ban
- **Banned in headlines:** Use a period, colon, or comma.
- **Banned in eyebrows, pills, labels, button text, and captions:** Replace with line breaks, columns, or spacing.
- **Banned in body copy:** Restructure into separate sentences, or use commas, parentheses, or colons.
- **Banned in quote attribution:** Use a standard hyphen with spaces (` - `) or a line break with secondary weight.
- **Banned in ranges:** Number ranges and date ranges use the regular ASCII hyphen (`2018-2026`, `10-20ms`).

### 1.2 Allowed Dash Characters
The ONLY permitted dash characters on the page are:
1. Regular hyphen `-` (for compound words, CSS classes, ranges, and markup).
2. Minus sign in mathematical expressions (`-5px`, `-10°C`).

If any output contains a single `—` or `–` anywhere visible to the user or in code comments, it fails pre-flight verification.

---

## 2. Visual & Styling Tells

- **No Neon / Outer Glows:** Avoid automatic purple/cyan glows and neon shadows. Use 1px inner borders or soft tinted ambient shadows.
- **No Pure Black (`#000000`):** Use rich dark neutrals such as `zinc-950`, `slate-950`, or custom off-black (`#090d16`).
- **No Pure White (`#ffffff`) for Dark Mode Text:** Use `zinc-100` or `slate-100` to prevent harsh eye strain.
- **No Oversaturated Accents:** Keep accent saturation balanced with neutral backgrounds (< 80% saturation).
- **No Excessive Gradient Text:** Avoid multi-color rainbow gradients on large headlines.
- **No Custom Mouse Cursors:** Outdated, accessibility-hostile, and causes severe GPU performance drops.
- **No Three Equal Feature Cards:** Three identical horizontal cards with generic icons is the signature AI feature row. Use asymmetric bento grids or 2-column alternating layouts.

---

## 3. Content & Copy Tells ("Jane Doe" Effect)

- **No Generic Placeholder Names:** "John Doe", "Sarah Chan", "Jack Su" are banned. Use realistic, culturally grounded names.
- **No Generic Avatars:** SVG generic user silhouettes or egg avatars are banned. Use high-quality photography placeholders or styled initial badges.
- **No Startup-Slop Brand Names:** Names like "Acme", "Nexus", "SmartFlow", "Cloudly" are banned. Invent contextual, credible names.
- **No Fake-Precise Numbers:** Arbitrary metrics like `99.99%`, `4.1x`, `13.4 lb` without real data are banned.
- **No AI Filler Verbs:** Avoid cliché verbs like "Elevate", "Seamless", "Unleash", "Next-Gen", "Revolutionize". Use direct, concrete verbs.
- **Copy Self-Audit:** Re-read all visible strings. Eliminate mock-poetic phrasing, passive-aggressive humility, and grammatically broken AI sentences.

---

## 4. Production-Test Tells (Banned Signatures)

### 4.1 Hero & Top-of-Page
- **No Version Badges in Hero:** Labels like `v0.6`, `BETA`, `EARLY ACCESS` are banned unless the project is explicitly a software launch.
- **No Micro-Meta Sub-Eyebrows:** Phrases like "Brand · No. 01 · The Collection" are banned.
- **No Bottom Decoration Text Strips:** Banned mono strips like `BRAND. MOTION. SPATIAL.` across the hero bottom.

### 4.2 Section Numbering & Punctuation
- **No Section-Number Eyebrows:** Banned labels like `00 / INDEX`, `01 · Capabilities`, `06 · How it works`. Use clear functional words.
- **No Tile Numbering:** Avoid `01 / 4` pagination labels on bento tiles or gallery cards.
- **Middle-Dot (`·`) Rationing:** Maximum 1 middle-dot per metadata line. Do not use it as a universal separator.
- **No Decorative Status Dots:** Colored glowing dots before regular nav items, task rows, or badges are banned unless displaying real live server telemetry.

### 4.3 Fake Product Previews & Decorations
- **No Div-Based Fake Screenshots:** Never construct fake software dashboards, task lists, or terminal windows using raw HTML `<div>` blocks. Use real screenshots, generated imagery, or an actual functioning interactive mini-component.
- **No Fake Version Footers:** Footers containing `v1.4.2`, `Build 0048`, `last sync 4s ago` on marketing sites are banned.
- **No `<br>`-Broken Italic Headlines:** Do not arbitrarily break lines and italicize single words as a forced design trick.
- **No 90-Degree Rotated Text:** Vertical text running up the page is an agency cliché; avoid unless explicitly requested.
- **No Crosshairs or Hairline Grids as Decoration:** Lines drawn purely to appear technical are banned.
- **No Overlaid Image Tags:** Avoid floating pill tags directly on top of photos.
- **No Fake Photo Credits:** Captions like `Field study no. 12 · Ines Caetano` under stock photos are banned.
- **No Scroll Cues:** Labels like `Scroll`, `Scroll to explore`, or animated mouse wheels are banned.
- **No Progress Bars with Filled Tracks:** Avoid heavy dashboard-style comparison bars on marketing pages.
- **No Atmospheric Locale/Weather Strips:** Avoid strings like `LIS 14:23 · 18°C` unless timezone-critical.

---

## 5. Dark Mode & Page Theme Lock Protocol

### 5.1 Page Theme Lock (Section 4.11)
- The entire page shares **ONE theme**.
- Never invert single sections mid-scroll (e.g., a warm paper light section sandwiched between two dark sections).
- Theme toggles must transition the whole page consistently.

### 5.2 Dark Mode Standards (Section 8)
- **Dual-Mode by Default:** Build with both light and dark modes in mind.
- **Token Consistency:** Use Tailwind `dark:` variants or semantic CSS variables (`--bg-surface`, `--text-main`, `--border-color`).
- **Hierarchy Parity:** Visual weight and focal points must remain identical across light and dark modes.
- **Contrast Integrity:** Meet WCAG AA minimums (4.5:1 for body, 3:1 for large text) in both modes.
- **Surface Layering:** Create depth through layered surfaces (`#090d16` canvas -> `#101726` card -> `#1a2234` elevated dropdown) rather than flat pitch black.
