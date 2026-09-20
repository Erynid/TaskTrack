---
description: Protocol for brief inference, the Three Dials configuration, and official design system mapping.
globs: ["*.html", "*.php", "*.jsx", "*.tsx", "*.vue", "*.svelte"]
---

# Brief Inference & Design System Protocol

> Derived from `@/skill/SKILL.md` (Sections 0, 1, 2, and Appendices).
> Governs landing pages, portfolios, and marketing redesigns.
> Related rules: `@02-design-engineering-directives.md`, `@03-ai-tells-and-forbidden-patterns.md`.

---

## 0. Brief Inference (Read the Room First)

Before writing any code or tweaking dial values, infer the real user intent. Do not jump to a default aesthetic.

### 0.A Signals to Read First
1. **Page Kind:** Landing (SaaS / consumer / agency / event), portfolio (developer / designer / studio), redesign (preserve vs overhaul), editorial / blog.
2. **Vibe Words:** "minimalist", "calm", "Linear-style", "Awwwards", "brutalist", "premium consumer", "Apple-y", "playful", "serious B2B", "editorial", "dark tech".
3. **Reference Signals:** URLs linked, screenshots pasted, products named, competitor brands mentioned.
4. **Audience:** B2B procurement panel vs design-conscious consumer vs hiring manager scanning a portfolio. The audience chooses the aesthetic.
5. **Existing Brand Assets:** Logo, color, typography, photography. For redesigns, these are mandatory starting materials.
6. **Quiet Constraints:** Accessibility-first audiences, public-sector mandates, regulated industries, trust-first commerce. These constraints override aesthetic preference.

### 0.B Mandatory One-Line "Design Read"
Before generating code, state in one line:
> **"Reading this as: <page kind> for <audience>, with a <vibe> language, leaning toward <design system or aesthetic family>."**

Example reads:
- *"Reading this as: B2B SaaS landing for technical buyers, with a Linear-style minimalist language, leaning toward Tailwind utilities + Geist + restrained motion."*
- *"Reading this as: solo designer portfolio for hiring managers, with an kinetic-type language, leaning toward native CSS + scroll-driven animation."*
- *"Reading this as: redesign of a public-sector service site, with a trust-first language, leaning toward GOV.UK Frontend or USWDS."*

### 0.C Ambiguity Handling
- If the brief is genuinely ambiguous, ask exactly **one** clarifying question. Never dump a multi-question list.
- Example: *"Should this feel closer to Linear-clean or Awwwards-experimental?"*
- If confidence is high from context, do not ask. Declare the design read and proceed.

### 0.D Anti-Default Discipline
Do not default to AI-purple gradients, centered hero over dark mesh, three equal feature cards, generic glassmorphism, infinite micro-animations, or Inter + slate-900. Reach past clichés based on the design read.

---

## 1. The Three Dials (Core Configuration)

After declaring the design read, configure the three dials. Every layout, motion, and density decision is gated by them:

- **`DESIGN_VARIANCE: 8`** (1 = Perfect Symmetry, 10 = Artsy Chaos)
- **`MOTION_INTENSITY: 6`** (1 = Static, 10 = Cinematic / Physics)
- **`VISUAL_DENSITY: 4`** (1 = Art Gallery / Airy, 10 = Cockpit / Packed Data)

**Baseline:** `8 / 6 / 4`. Adjust conversationally based on the design read.

### 1.A Dial Inference Matrix
| Signal / Context | VARIANCE | MOTION | DENSITY |
|---|:---:|:---:|:---:|
| "minimalist / clean / calm / editorial / Linear-style" | 5-6 | 3-4 | 2-3 |
| "premium consumer / Apple-y / luxury / brand" | 7-8 | 5-7 | 3-4 |
| "playful / wild / Dribbble / Awwwards / experimental" | 9-10 | 8-10 | 3-4 |
| "landing page / portfolio / marketing site (default)" | 7-9 | 6-8 | 3-5 |
| "trust-first / public-sector / regulated / accessibility" | 3-4 | 2-3 | 4-5 |
| "redesign - preserve" | match | match+1 | match |
| "redesign - overhaul" | +2 | +2 | match |

### 1.B Use-Case Presets
- **Landing (SaaS, mainstream):** `7 / 6 / 4`
- **Landing (Agency / creative):** `9 / 8 / 3`
- **Landing (Premium consumer):** `7 / 6 / 3`
- **Portfolio (Designer / studio):** `8 / 7 / 3`
- **Portfolio (Developer):** `6 / 5 / 4`
- **Editorial / Publication:** `6 / 4 / 3`
- **Public-Sector Service:** `3 / 2 / 5`

---

## 2. Brief to Design System Map

Pick the right foundation. Do not reinvent CSS for problems solved by official packages.

### 2.A Official Design Systems (Use Official Packages)
| Brief Reads As... | Reach For | Rationale |
|---|---|---|
| Microsoft / Enterprise SaaS / Dashboards | `@fluentui/react-components` | Official Fluent 2 tokens, accessible primitives |
| Google-flavored product / Web App | `@material/web` + M3 tokens | Official Material 3 web components |
| IBM-style Enterprise Analytics / Data B2B | `@carbon/react` + `@carbon/styles` | Mature data-density, accessibility |
| Shopify App Admin Surfaces | `polaris.js` / Polaris React | Mandatory for Shopify ecosystem |
| Atlassian / Jira-style tools | `@atlaskit/*` + `@atlaskit/tokens` | Official Atlassian design system |
| GitHub Devtool / Community marketing | `@primer/css` or `@primer/react-brand`| Official Primer ecosystem |
| UK Public Sector Service | `govuk-frontend` | Regulatory expectation |
| US Federal Public Sector | `uswds` | Regulatory compliance |
| Fast MVP / Local Business | Bootstrap 5.3 | Fast, battle-tested utilities |
| Modern accessible React foundation | `@radix-ui/themes` | Polished accessible primitives |
| Owned component SaaS | shadcn/ui (`npx shadcn@latest add`) | Customized owned code, never default |
| Tailwind modern SaaS / Indie product | Tailwind v4 utilities + `dark:` variant | Modern utility-first standard |

**Honesty Rule:** When a design system is chosen, install and use its official package. Never recreate its CSS by hand.
**One System Per Project:** Never mix Fluent with Carbon, or shadcn/ui into Material 3 in the same tree.

### 2.B Aesthetic Directions (No Single Library Exists)
When building an aesthetic rather than a system, use native CSS + Tailwind + primitives honestly:
- **Glassmorphism:** `backdrop-filter`, layered borders (`border-white/10`), inner highlight shadows. Provide solid fallback under `prefers-reduced-transparency`.
- **Bento Grid:** CSS Grid with mixed cell spans (`bento-col-2`). No single library owns it.
- **Brutalism:** Native CSS, monospace type, raw heavy borders, zero border-radius.
- **Editorial:** High-contrast serif headlines, asymmetric white space, generous margins.
- **Dark Tech:** Monospace accents, dark slate/zinc base, single neon telemetry accent.
- **Apple Liquid Glass:** Approximated on web via `backdrop-filter` + layered highlights. Always label as approximation.

---

## 3. Implementation Checklist
- [ ] Stated one-line design read before generating code.
- [ ] Set explicit `DESIGN_VARIANCE`, `MOTION_INTENSITY`, and `VISUAL_DENSITY` values.
- [ ] Selected exactly one official design system or honest aesthetic baseline.
- [ ] Verified dependencies exist in `package.json` before importing.
