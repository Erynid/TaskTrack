---
description: Performance guardrails, accessibility standards (WCAG AA, focus-visible, reduced motion), and motivated motion implementation patterns.
globs: ["*.html", "*.php", "*.css", "*.js", "*.jsx", "*.tsx", "*.ts"]
---

# Performance, Accessibility & Motion Guardrails

> Derived from `@/skill/SKILL.md` (Sections 5, 6, and 7).
> Related rules: `@01-brief-and-design-system.md`, `@02-design-engineering-directives.md`.

---

## 1. Accessibility Guardrails (Non-Negotiable)

### 1.1 Universal `:focus-visible` Indicators (WCAG 2.4.7)
- All interactive elements (`a`, `button`, `input`, `select`, `textarea`, `[tabindex]`) must have an unmistakable visible focus ring.
- Standard pattern:
  ```css
  :focus-visible {
    outline: 3px solid var(--color-primary);
    outline-offset: 2px;
  }
  ```

### 1.2 Contrast Ratios (WCAG 2.1 AA)
- Normal text (< 18px / 14pt bold): Minimum **4.5:1** contrast against background.
- Large text (≥ 18px or ≥ 14pt bold): Minimum **3:1** contrast.
- UI components & borders: Minimum **3:1** contrast against adjacent colors.
- Form inputs, placeholder text, and error messages must pass WCAG AA contrast.

### 1.3 Reduced Motion (WCAG 2.3.3)
- When `prefers-reduced-motion: reduce` is active, all automatic animations, scroll hijacks, infinite loops, and spring physics must collapse to static or instantaneous transitions.
- CSS pattern:
  ```css
  @media (prefers-reduced-motion: reduce) {
    *, *::before, *::after {
      animation-duration: 0.01ms !important;
      animation-iteration-count: 1 !important;
      transition-duration: 0.01ms !important;
      scroll-behavior: auto !important;
    }
  }
  ```
- Motion / React pattern:
  ```tsx
  import { useReducedMotion } from "motion/react";
  const shouldReduce = useReducedMotion();
  ```

---

## 2. Motivated Motion & Choreography

### 2.1 The Golden Rule of Motion
- **"Motion claimed, motion shown."** If `MOTION_INTENSITY > 4`, the page must visibly move (hero enter, scroll reveals, tactile pushes). If motion cannot be completed, drop the dial to 3.
- **Motion must be motivated.** Every animation must serve:
  1. Hierarchy (directing attention)
  2. Storytelling (sequential narrative reveal)
  3. Feedback (acknowledging user interaction)
  4. State transition (explaining spatial change)
- Never animate simply "because it looks cool."

### 2.2 Marquee Restraint
- Horizontal scrolling marquees are permitted at most **once per page**. Multiple marquees constitute lazy filler.

### 2.3 Forbidden Animation Practices
- **`window.addEventListener("scroll", ...)` is strictly banned.** It causes main-thread scroll jank. Use Motion `useScroll()`, GSAP `ScrollTrigger`, IntersectionObserver, or native CSS `animation-timeline`.
- **Storing scroll position in React state is banned.** Avoid `useState` for continuously changing scroll or mouse values.
- **`requestAnimationFrame` touching React state is banned.** Use Motion values (`useMotionValue`, `useTransform`) instead.

---

## 3. Canonical Motion Skeletons

### 3.1 Sticky-Stack Sections (GSAP)
Pin sections at viewport top and scrub scale/opacity with the arrival of the next card:
```tsx
"use client";
import { useRef, useEffect } from "react";
import { gsap } from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";
import { useReducedMotion } from "motion/react";

gsap.registerPlugin(ScrollTrigger);

export function StickyStack({ cards }: { cards: React.ReactNode[] }) {
  const ref = useRef<HTMLDivElement>(null);
  const reduce = useReducedMotion();

  useEffect(() => {
    if (reduce || !ref.current) return;
    const ctx = gsap.context(() => {
      const cardEls = gsap.utils.toArray<HTMLElement>(".stack-card");
      cardEls.forEach((card, i) => {
        if (i === cardEls.length - 1) return;
        ScrollTrigger.create({
          trigger: card,
          start: "top top",
          endTrigger: cardEls[cardEls.length - 1],
          end: "top top",
          pin: true,
          pinSpacing: false,
        });
        gsap.to(card, {
          scale: 0.92,
          opacity: 0.55,
          ease: "none",
          scrollTrigger: {
            trigger: cardEls[i + 1],
            start: "top bottom",
            end: "top top",
            scrub: true,
          },
        });
      });
    }, ref);
    return () => ctx.revert();
  }, [reduce]);

  return (
    <div ref={ref} className="relative">
      {cards.map((card, i) => (
        <div key={i} className="stack-card sticky top-0 min-h-[100dvh] flex items-center justify-center">
          {card}
        </div>
      ))}
    </div>
  );
}
```

### 3.2 Scroll-Reveal Stagger (Motion)
For list entries and grid items, use Motion's lightweight `whileInView`:
```tsx
"use client";
import { motion, useReducedMotion } from "motion/react";

export function RevealStagger({ items }: { items: string[] }) {
  const reduce = useReducedMotion();
  return (
    <ul className="grid gap-6">
      {items.map((item, i) => (
        <motion.li
          key={item}
          initial={reduce ? false : { opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true, amount: 0.25 }}
          transition={{ duration: 0.5, delay: i * 0.08, ease: [0.16, 1, 0.3, 1] }}
        >
          {item}
        </motion.li>
      ))}
    </ul>
  );
}
```

---

## 4. Performance & Core Web Vitals

- **Hardware Acceleration:** Animate **only** `transform` and `opacity`. Never animate layout-triggering properties (`top`, `left`, `width`, `height`, `margin`).
- **Layer Cost:** Apply noise, grain, or blur filters only to fixed, `pointer-events-none` overlays. Never attach blur filters to scrolling containers.
- **Core Web Vitals Thresholds:**
  - **LCP (Largest Contentful Paint) < 2.5s:** Preload or assign `priority` to the hero visual asset.
  - **INP (Interaction to Next Paint) < 200ms:** Offload heavy computations; avoid blocking main thread.
  - **CLS (Cumulative Layout Shift) < 0.1:** Always declare width/height aspect ratios on images and reserve space for dynamic blocks.
- **Z-Index Hierarchy:** Restrict z-index to systemic layers: base (0), elevated card (10), sticky header (100), modal backdrop (500), modal content (510), toast (1000). Never spam arbitrary values (`z-50`, `z-[99999]`).

---

## 5. Technical Dial Reference

- **`DESIGN_VARIANCE`:**
  - 1-3: Symmetrical, centered, equal column widths.
  - 4-7: Controlled offsets, alternating spans, asymmetric white space.
  - 8-10: Organic masonry, heavy contrast in scales. Must collapse to single-column below 768px.
- **`MOTION_INTENSITY`:**
  - 1-3: Static layouts, instant states, CSS `:hover` only.
  - 4-7: Smooth cubic-bezier transitions, staggered scroll reveals.
  - 8-10: Advanced pinned scroll-tracking, spring physics. Strict cleanup required.
- **`VISUAL_DENSITY`:**
  - 1-3: Spacious art gallery, generous section padding (`py-28` to `py-36`).
  - 4-7: Balanced modern product interface (`py-16` to `py-24`).
  - 8-10: Compact data layout, 1px divider lines, monospace telemetry.
