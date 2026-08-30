# Design Decision — Visual Direction

Companion to `docs/design-system.md`. Three representative directions were built as real UI (not full sites) using the studio's own photography and one of its actual archived technical drawings — no stock imagery, no fabricated drawings. Reviewable at: the "Richtingenwand" artifact published this session.

All three boards render **identical content** — same headline ("Bouwkundig ontwerp dat klopt."), same three sample services, same featured project (Uitbreiding Villa Bilthoven) — so the comparison isolates the visual system, not the copy.

## Real material used

- Hero photograph: the timber-clad extension from "Uitbouw vrijstaande woning" (Abcoude) — chosen over the other homepage-slider candidates for its geometry, natural light, and visible material detail (cedar cladding, black-framed glazing), matching brief §12/§28 almost exactly.
- Project photograph: villa exterior from "Uitbreiding Villa Bilthoven."
- Real technical drawings from the same Bilthoven project archive: `Voorgevel` (front elevation) and `Doorsnede` (cross-section) — genuine line drawings, not recreated or simulated.
- The existing logo mark, used unmodified (see §5).

## The three directions

### A — Dutch Minimal
Single typeface (Archivo), no accent colour at all, maximum whitespace, the drawings deliberately withheld.
**Read:** calm, credible, the safest choice — but also the least distinctive. Stripped of the drawings, it loses the one asset that's genuinely unique to this business, and risks reading as "a well-made minimalist template" rather than "an architecture practice."

### B — Warm Architecture
Warm plaster-tone background, a muted weathered-bronze accent, Fraunces (display serif) paired with Karla (body). The drawing appears small, as a material detail beside the project photo.
**Read:** the most personal and inviting of the three, which matters for a residential-renovation audience. The real risk, named directly on the board itself: warm cream + terracotta + a characterful serif is a recognizable "AI-default" combination right now. To use this direction well, the execution has to work harder than the obvious version — less saturated bronze, a background pushed away from the exact cliché cream, asymmetric rather than centered composition.

### C — Technical Editorial
White/charcoal base, one restrained architectural blue-grey, Archivo Narrow for headlines, IBM Plex Sans for body, IBM Plex Mono for labels and captions. The cross-section drawing gets its own full-width moment rather than being a decorative accent.

**Read:** the most distinctive and the most directly tied to what Ron Werkhoven actually sells — not architectural concepts, but *accurate, buildable technical drawings and permit documentation*. Precision-as-visual-language is a direct translation of "creativiteit, snelheid en betrouwbaarheid," and it's built from an asset (the real drawing archive) a generic competitor site can't replicate. The risk is feeling cold/corporate if the technical elements are overused.

## Decision: Direction C, warmed

**Direction C is the recommendation**, evaluated against the eight criteria from the brief:

| Criterion | A | B | C |
|---|---|---|---|
| Vertrouwen (trust) | Good | Good | **Best** — precision cues match a technical-documentation service |
| Professionaliteit | Good | Good | **Best** |
| Architectonisch karakter | Fair | Good | **Best** |
| Persoonlijk gevoel | Fair | **Best** | Good (with the adjustment below) |
| Conversie | Good | Good | Good |
| Tijdloosheid | **Best** (but generic) | Fair — serif-editorial is a current trend | Good — fine-line/technical editorial has stayed current for decades |
| Performance | **Best** (1 typeface) | Good (2 families) | Fair (3 families, mitigated — Plex Mono loads only 2 weights, used sparingly) |
| Onderscheidend vermogen | Fair | Good | **Best** — the drawing archive is a genuine, uncopyable asset |

C wins on the criteria that matter most for this specific business (trust in technical competence, architectural character, distinctiveness) and loses narrowly on the two where B or A lead (personal warmth, raw performance). Both gaps are closeable without switching direction:

- **Warmth fix:** don't build C on clinical pure white. Shift the background token warmer (a soft warm-white rather than paper-white, see updated tokens below), keep photography at least as large as any technical element on every page, and cap the accent colour to labels/captions/hairlines — it never fills a background or a button in a way that reads corporate-cold.
- **Performance fix:** IBM Plex Mono loads only for short caption/label text at one weight (400) — its total weight is small relative to Plex Sans and Archivo Narrow; measure actual impact once real font-loading is implemented in Phase 5 and drop it if it's not paying for itself.

### Rejected
- **Direction A** — rejected as the primary direction because it discards the one asset (the drawing archive) that most directly proves architectural competence, and because unrelieved minimalism is currently one of the most common web-design defaults, undercutting the brief's distinctiveness requirement (§8).
- **Direction B** — rejected as primary, but its lesson is kept: warmth is a real requirement (brief §2, §4 — "warm rather than clinical"), so Direction C's final tokens borrow B's instinct without borrowing its palette or serif.

## Typography — final choice

- **Display:** Archivo Narrow (600/700) — a condensed grotesk with a title-block, technical-drawing character, used only for H1/H2.
- **Body:** IBM Plex Sans (400/500/600) — designed for technical/engineering documentation, excellent Latin/Dutch diacritic coverage, pairs cleanly with the condensed display face without competing.
- **Labels/captions/measurements:** IBM Plex Mono (400/500), small sizes only — service numbers, project meta lines, drawing captions.

This supersedes the placeholder serif direction floated in `docs/design-system.md` §3 — that document should be updated to point here rather than carrying two competing typographic proposals.

## Colour — final tokens (revised from the pure-white board shown in the artifact)

```css
--color-background: #FAF8F4;  /* warm off-white, not clinical white */
--color-surface:    #FFFFFF;
--color-text:       #1B1D1F;
--color-muted:      #6E7377;
--color-border:     #E2E1DC;
--color-accent:     #4B6070;  /* architectural blue-grey — labels, hairlines, links, never a fill */
--color-accent-ink: #FFFFFF;
```

The one deliberate departure from a "neutral + one warm earthy accent" reading (brief §4's suggested direction) is choosing blue-grey over terracotta/bronze/green: it reads as technical/precise rather than material/organic, which fits Direction C's concept. Warmth is carried by the *background* tone and by photography, not by the accent hue.

## Image strategy

- Photography is always at least as visually large as any drawing or technical graphic on the page — the hierarchy from brief §3 (photography first) holds even inside the technical-editorial system.
- Real drawings are used only where they exist for that specific project; no drawing is ever fabricated or implied for a project that doesn't have one on record.
- No colour filter/duotone treatment — each photo keeps its natural tone (brief §29); consistency comes from consistent aspect ratios, cropping discipline, and the surrounding type/grid system.

## Animation strategy

Per brief §14/§32–35 and the design system's motion principles:
- Hero: image scale 1.02 → 1.00 and a 12px/400ms opacity+translate entrance on text, once, on load.
- Drawing reveal: a thin rule draws itself across the drawing's caption line on scroll-into-view (transform only, no layout properties).
- Micro-interactions only: project card image scales ~1.03 on hover, arrow glyphs shift 4–6px, underlines animate — all 150–300ms, `transform`/`opacity` only.
- Everything gated behind `prefers-reduced-motion: reduce` → instant states, no animation.
- No parallax, no scroll-hijacking, no page-transition choreography.

## Outstanding note for Phase 5

`docs/design-system.md` currently lists a placeholder serif/sans evaluation and a terracotta accent — both are superseded by this document. When Phase 5 (global styles/`theme.json`) begins, treat this file as authoritative for typography and colour, and update `design-system.md`'s §3 and §14 to reference it rather than duplicating conflicting values.
