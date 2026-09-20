---
description: Redesign audit protocol, reference pattern vocabulary, block library schema, and the mandatory pre-flight verification checklist.
globs: ["*"]
---

# Redesign Protocol & Pre-Flight Verification

> Derived from `@/skill/SKILL.md` (Sections 10, 11, 12, 13, 14, and Appendices).
> Related rules: `@01-brief-and-design-system.md`, `@02-design-engineering-directives.md`, `@03-ai-tells-and-forbidden-patterns.md`, `@04-performance-accessibility-and-motion.md`.

---

## 1. Redesign Protocol (Section 11)

Misclassifying a redesign as a greenfield build is the leading cause of broken deliverables. Always categorize the task first:

### 1.1 Redesign Modes
- **Greenfield:** No prior code exists, or complete visual overhaul is explicitly requested.
- **Redesign - Preserve:** Modernize visuals while preserving existing brand equity, SEO rankings, and muscle memory.
- **Redesign - Overhaul:** Fresh visual language, but preserving underlying content and information architecture.

### 1.2 Pre-Redesign Audit
Before modifying code in an existing project, audit and record:
1. **Brand Tokens:** Primary/accent colors, font stack, logo assets, corner radii.
2. **Information Architecture (IA):** Existing URL routes, page slugs, anchor IDs, nav hierarchy.
3. **Content Blocks:** Identifying core value content vs outdated filler.
4. **Preserved Patterns:** Distinctive interactions, recognizable hero layout, copy tone.
5. **SEO Baseline:** Page titles, meta descriptions, headings hierarchy, canonical tags.

### 1.3 Strict Preservation Boundaries (Never Change Silently)
Never modify without explicit user permission:
- URL slugs and route structure (avoids breaking SEO).
- Primary navigation labels.
- Form field names and IDs (preserves analytics tracking and browser autofill).
- Brand logos and trademark wordmarks.
- Existing legal, consent, and privacy copy.

### 1.4 Modernization Levers (In Priority Order)
1. **Typography refresh:** Highest visual return with minimal architectural risk.
2. **Spacing & rhythm:** Clean up section padding and establish clear vertical hierarchy.
3. **Color recalibration:** Unify neutrals, desaturate secondary hues, lock single accent.
4. **Motion layer:** Add tactile `:active` states and subtle scroll reveals.
5. **Hero & key section restructuring:** Apply modern bento or split layouts.
6. **Full block replacement:** Last resort for completely unmaintainable legacy code.

---

## 2. Reference Vocabulary (Section 10)

Key architectural patterns to draw upon during design:
- **Hero Paradigms:** Asymmetric Split Hero, Editorial Poster Hero, Video Mask Hero, Scroll-Pinned Hero.
- **Navigation:** Edge Dock, Dynamic Island Pill, Magnetic Button, Sticky Blur Header.
- **Layouts:** Bento Grid, Masonry Grid, Sticky-Stack Sections, Split-Screen Scroll.
- **Card Mechanics:** Spotlight Border, Frosted Glassmorphism Panel, Swipe Stack.
- **Scroll Effects:** Sticky Scroll Stack, Horizontal Scroll Hijack, Curtain Reveal.
- **Text Techniques:** Kinetic Marquee (max 1/page), Text Mask Video, Decoder Scramble.

---

## 3. Block Library Contract (Section 12)

When building modular reusable blocks, store them under `components/` or `blocks/` following this specification:
1. **Isolated Responsibilities:** One component/block per file.
2. **Standalone Integrity:** Components must render independently with sensible default props.
3. **Clean Props API:** Type-safe props with TypeScript interfaces.
4. **Mobile Responsive Built-in:** Explicit `< 768px` fallback declared within the component.
5. **Dark Mode & Reduced Motion Ready:** Integrated token usage and `prefers-reduced-motion` compliance.

---

## 4. Boundaries & Out-of-Scope (Section 13)

This design-taste system is tailored specifically for **landing pages, portfolios, marketing websites, and visual redesigns**. It is explicitly **NOT** intended for:
- Data-heavy administrative dashboards (use Fluent, Carbon, or Atlassian).
- Complex spreadsheet/data tables (use TanStack Table or AG Grid).
- Multi-step application wizards.
- Code editors and IDE interfaces.
- Native mobile applications (follow Apple HIG or Material Guidelines).

---

## 5. Mandatory Final Pre-Flight Checklist (Section 14)

**Run this verification before declaring any task complete. Every item must pass.**

### 5.1 Hard Guardrails & Anti-Tells
- [ ] **ZERO em-dashes (`—`) or en-dashes (`–`):** Checked all headlines, subheads, buttons, captions, alt text, and code comments.
- [ ] **No Raw Emojis:** Verified no raw emojis are used in place of proper SVG stroke icons.
- [ ] **Page Theme Lock:** Single consistent theme across all sections (no mid-page color inversions).
- [ ] **Single Accent Color Lock:** One accent color applied consistently across all sections.
- [ ] **Shape Consistency Lock:** Consistent corner-radius system across buttons, inputs, and cards.
- [ ] **Button Contrast Check:** Every CTA meets WCAG AA 4.5:1 contrast against its background.
- [ ] **CTA Single-Line:** No button label wraps to two lines on desktop viewports.
- [ ] **No Duplicate CTA Intent:** Single unified action label per user goal across the page.
- [ ] **Form Accessibility:** All inputs have explicit labels and visible `:focus-visible` rings.

### 5.2 Layout & Content Discipline
- [ ] **Hero Fits Initial Viewport:** Headline ≤ 2 lines, subtext ≤ 20 words, CTAs visible without scrolling.
- [ ] **Hero Top Padding Cap:** Desktop padding capped at `pt-24` (no floating halfway down screen).
- [ ] **Hero Stack Discipline:** Maximum 4 text elements; trust logos placed in section below.
- [ ] **Eyebrow Count Restraint:** Maximum 1 eyebrow label per 3 sections.
- [ ] **Split-Header Ban:** Section headlines and explainer paragraphs stacked vertically.
- [ ] **Zigzag Alternation Cap:** Maximum 2 consecutive alternating split-sections.
- [ ] **Bento Cell Count:** Exactly as many cells as content items (no blank filler tiles).
- [ ] **Bento Visual Diversity:** At least 2-3 cells contain visual variation (images, patterns, tint).
- [ ] **Real Visual Assets:** Uses real photography or image tool assets (no div-based fake screenshots).
- [ ] **Navigation on Single Line:** Desktop navbar on 1 line with height ≤ 80px.
- [ ] **Marquee Capped:** Maximum 1 scrolling marquee across the entire page.

### 5.3 Motion & Performance
- [ ] **Reduced Motion Support:** All animations disabled under `prefers-reduced-motion: reduce`.
- [ ] **Hardware Acceleration:** Animations restricted to `transform` and `opacity`.
- [ ] **No `window.scroll` Listeners:** Using Motion hooks, ScrollTrigger, or CSS scroll-driven animations.
- [ ] **Tactile Push Feedback:** Buttons include `:active` scale/translation state.
- [ ] **Mobile Collapse Tested:** Verified responsive layout at desktop (> 1024px), tablet (768-1024px), and mobile (< 768px).

---

## Appendix: Common Design System Installs
```bash
# Radix Themes
npm install @radix-ui/themes

# Fluent UI React (v9)
npm install @fluentui/react-components

# Material Web (M3)
npm install @material/web

# IBM Carbon
npm install @carbon/react @carbon/styles

# shadcn/ui
npx shadcn@latest init
```
