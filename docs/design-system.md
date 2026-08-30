# Design System — rwerkhoven.nl rebuild

## 1. Brand input

The existing logo (`ron_werkhoven_logo.svg`) is a single-color wordmark/mark using `#212121` (a near-black charcoal), rendered as fine architectural linework. There is no existing accent color to preserve — the brand today is monochrome. This gives the rebuild a clean basis: keep the charcoal as the anchor ink color and introduce one restrained accent, per brief §14.

## 2. Colour tokens

**Superseded by `docs/design-decision.md`** — after building and comparing three real-content visual directions, the accent moved from terracotta to a restrained architectural blue-grey (see that document for the full rationale and the rejected alternatives). The tokens below are the current, final values; treat this section as the live reference and `design-decision.md` as the "why."

Defined as CSS custom properties (and mirrored in `theme.json` `settings.color.palette`), light-mode only — this is a business site, not an app; a dark mode isn't a brief requirement and would dilute the "premium architectural print" feel the brief asks for. Tokens are structured so dark mode could be added later without touching component CSS.

```css
:root {
  --color-background: #FFFFFF;   /* pure white — the owner confirmed this over a warmed off-white */
  --color-surface:    #F7F7F5;   /* cards, form fields, elevated panels */
  --color-text:       #1B1D1F;   /* near-black, warmer than the logo's #212121 to avoid a cold/blue-black cast */
  --color-muted:      #72777C;   /* secondary text, captions, meta labels */
  --color-border:     #DFE1E2;   /* hairlines, dividers, card borders */
  --color-accent:     #4B6070;   /* architectural blue-grey — one accent only */
  --color-accent-ink: #FFFFFF;   /* text placed on the accent color */
}
```

Rationale for the accent: a desaturated blue-grey reads as precise/technical rather than material/organic — it reinforces Direction C's core idea that this business sells accurate, buildable documentation, not just architectural concepts. It is used only for labels, hairlines, links and small measurement-style captions — it never fills a button background or a large surface; warmth comes from the off-white background and from photography, not from the accent hue.

No secondary accent, no gradients. Photography supplies the visual richness; the palette stays quiet so projects and drawings stand out.

## 3. Typography

**Superseded by `docs/design-decision.md`** — final pick, made after comparing three real-content directions:

- **Display (H1/H2 only):** Archivo Narrow, weights 600/700 — a condensed grotesk with a title-block, technical-drawing character.
- **Body/UI:** IBM Plex Sans, weights 400/500/600 — designed for technical documentation, strong Dutch diacritic support, pairs cleanly with the condensed display face.
- **Labels/captions/measurements only:** IBM Plex Mono, weights 400/500, small sizes — service numbers, project meta lines, drawing captions. Never used for running body copy.

Three webfont families is more than the original "system stack only" plan in this document, justified because Plex Mono loads a single weight at small caption sizes only and Plex Sans/Archivo Narrow together still stay within a modest total weight budget. Measure actual font-loading cost once Phase 5 implements this and drop Plex Mono in favour of a body-font fallback for labels if it isn't paying for itself.

### Scale (fluid, `clamp()`-based to avoid separate mobile/desktop overrides)

| Token | Role | Size (clamp) |
|---|---|---|
| `--text-h1` | Page hero heading | `clamp(2.25rem, 1.6rem + 3vw, 4rem)` |
| `--text-h2` | Section heading | `clamp(1.75rem, 1.4rem + 1.6vw, 2.75rem)` |
| `--text-h3` | Sub-section / card title | `clamp(1.25rem, 1.1rem + 0.8vw, 1.75rem)` |
| `--text-body` | Body copy | `1.0625rem` (17px), line-height 1.6 |
| `--text-small` | Captions, meta, labels | `0.875rem` (14px), line-height 1.5 |

Line length capped at ~68ch for body copy via a `.prose`-style max-width on text containers.

## 4. Spacing

An 8px base unit, expressed as tokens rather than hard-coded values throughout the theme:

```css
--space-1: 0.5rem;   /* 8px */
--space-2: 1rem;     /* 16px */
--space-3: 1.5rem;   /* 24px */
--space-4: 2.5rem;   /* 40px */
--space-5: 4rem;     /* 64px */
--space-6: 6rem;     /* 96px */  /* section vertical rhythm on desktop */
```

Section padding uses `--space-6` top/bottom on desktop, collapsing via `clamp()` toward `--space-4` on mobile — generous whitespace (§13) without a second breakpoint-specific override system.

## 5. Grid & layout

- Content max-width: `1200px`, with a `--space-3`–`--space-4` fluid side gutter below that.
- Full-bleed sections (hero, large project photography) break out of the max-width to the viewport edge — the one deliberate "architectural" layout move: text stays in a disciplined column, imagery is allowed to run wide.
- 12-column CSS grid for services/project card layouts, collapsing to a single column under 768px.

## 6. Breakpoints

Mobile-first; only real, tested breakpoints, matching brief §16:

```css
/* base styles = 320px+ */
@media (min-width: 480px)  { /* large phones */ }
@media (min-width: 768px)  { /* tablet */ }
@media (min-width: 1024px) { /* small laptop */ }
@media (min-width: 1280px) { /* desktop */ }
@media (min-width: 1920px) { /* large desktop — mostly just raises max-width ceilings, no layout change */ }
```

390px/430px (iPhone variants) and 1440px are verification checkpoints during QA (brief §16), not additional CSS breakpoints — the fluid type/spacing scale should already cover them without extra rules.

## 7. Components / patterns

Each becomes a Gutenberg block pattern (`patterns/*.php`) built from core blocks + theme.json styles, not custom blocks, unless a genuine interactive need arises (the mobile nav toggle and the contact form are the only components expected to need theme-supplied JS).

| Component | Notes |
|---|---|
| Header / nav | Logo left, nav right, phone number CTA visible at ≥1024px; hamburger + accessible off-canvas/disclosure menu below that. Optional subtle sticky behaviour, capped height, no shrink-jank. |
| Hero | Full-bleed `<picture>` (not CSS background-image — fixes the audit's alt-text/responsive gap), H1, supporting paragraph, two CTAs. |
| Service card | Title, one-line description, text link — no icon library; a thin accent rule substitutes for an icon per "avoid excessive icons." |
| Project card | Large image (fixed aspect-ratio to prevent layout shift), title, project type + location as small meta line. |
| Project gallery | CSS grid of images at consistent aspect ratios; lightbox only if it can be done with near-zero JS (native `<dialog>`), otherwise a simple in-page large-image layout — no heavy lightbox library. |
| Process steps | Numbered 01–05 list, accent-colored numerals, short heading + one sentence each. |
| Trust section | Short statements in a 2–3 column grid, no icons required, restrained rule/border between items. |
| CTA band | Full-width, `--color-surface` or a very light tint, headline + two contact methods + button. |
| Contact form | Labeled native inputs, visible focus states, inline validation messages, honeypot + server-side check for spam (no reCAPTCHA to avoid third-party JS unless spam volume later proves it's needed). |
| Footer | Company info, nav, legal links, LinkedIn, copyright. |
| Breadcrumbs | Text-only trail with `/` separators, paired with `BreadcrumbList` schema. |

## 8. Buttons

Two variants only:
- **Primary:** solid `--color-accent` background, `--color-accent-ink` text, no gradient, subtle `background-color` transition on hover/focus (respecting `prefers-reduced-motion`).
- **Secondary:** transparent background, `1px solid --color-border` (or text-color) border, fills or underlines on hover.

Square-ish corners with a small radius (`4px`) — explicitly avoiding the "overly rounded cards" pattern called out in §13 as a thing to avoid.

## 9. Imagery treatment

- Fixed aspect-ratio containers (`aspect-ratio` CSS) for every card/grid image to guarantee zero layout shift.
- `loading="lazy"` on everything below the fold; hero image preloaded and not lazy.
- Responsive `srcset`/`sizes` generated via WordPress's native image size system — no manual multi-format authoring burden for the site admin.
- No filters, duotones, or heavy cropping distortion — photography is shown honestly per §21.

This document is the reference for Phase 5 (global styles/`theme.json`) onward — implementation should not introduce colors, sizes, or spacing values that aren't tokens defined here.
