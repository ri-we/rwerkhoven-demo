# Site Architecture — rwerkhoven.nl rebuild

Based on `docs/site-audit.md` and the project brief. This defines the target IA, URL map, content types, and internal linking before any theme code is written.

## 1. Design principle

Keep every URL that Google has already indexed since 2019–2021 unless there's a concrete reason to change it. The audit found the existing top-level pages and `project` CPT slugs already match (or can trivially match) the brief's target IA, so **this rebuild adds structure, it doesn't need a redirect map** for the core paths. New URLs (individual service pages) are additive.

## 2. URL map

### Top-level pages (unchanged from current site)

| URL | Purpose | Status vs. current site |
|---|---|---|
| `/` | Homepage | Rebuilt |
| `/diensten/` | Services overview: all six services described on this one page, each in an anchored section | Rebuilt, restructured |
| `/diensten/#{anchor}` | One section per service on the overview page, no separate sub-pages (decision 2026-10-09). Anchors: `bouwkundig-ontwerp`, `bouwkundige-tekeningen` (incl. "Bouwaanvraagtekeningen" + "Werktekeningen"), `verbouwing-renovatie`, `aanbouw-uitbouw`, `omgevingsvergunning` (incl. "Brandveilig gebruik", "Gebruiksmelding"), `bedrijfspanden` | New sections |
| `/projecten/` | Projects overview/index | Rebuilt |
| `/project/{slug}/` | Individual project (CPT: `project`, unchanged) | Rebuilt template, same 15 slugs preserved |
| `/het-bureau/` | About page | Rebuilt, expanded |
| `/werkwijze/` | Process page | New (process content currently doesn't exist as its own page) |
| `/contact/` | Contact page + form | Rebuilt, adds real form |
| `/bedankt/` | Form thank-you page | Replaces orphaned `/bedankt-voor-je-contact-bericht/` — old URL 301s here |
| `/privacy/` | Privacy policy (HTML page, not just a PDF) | New — see content-questions.md re: existing PDF |

Two services in the current `/diensten/` content don't map cleanly to the brief's 6 target sub-pages (`Splitsingstekeningen`, `Digitaliseren/Inmeten`, `Ontruimingsplattegronden`). These are real, factual services — they should not be dropped. They'll live as sections/paragraphs on the closest matching service page (`bouwkundige-tekeningen` and `omgevingsvergunning`) rather than getting their own thin page, per the brief's instruction not to create thin SEO pages.

### Redirects required

| Old URL | New URL | Reason |
|---|---|---|
| `/bedankt-voor-je-contact-bericht/` | `/bedankt/` | Cleaner slug, becomes the real form's thank-you page |

Everything else keeps its exact path — no other redirects needed.

## 3. Content types

### Page (native WordPress `page`)
Used for all top-level and service pages. No custom fields needed — service pages are simple content pages, optionally using a "related projects" pattern that queries the `project` CPT by a `service` taxonomy term (see below).

### `project` (existing CPT — enhanced, not replaced)
Currently: title + free-text body + gallery images, no structured fields. Enhancing with real (not fabricated) structured data lets the template render consistent labels and improves internal linking to services.

Fields (as ACF-style fields, or plain post-meta if avoiding a plugin dependency — decision deferred to theme-build phase):
- Title *(native)*
- Featured image *(native)*
- Gallery *(native, via block editor gallery block)*
- Project type (vrije tekst of taxonomy — e.g. "Woning", "Bedrijfspand")
- Location *(text — already present in prose for all 15 projects, extractable)*
- Year *(already present for most projects via original publish context; confirm per-project in content-questions.md where unclear)*
- Description *(native content — existing prose, lightly cleaned up)*
- Services provided *(taxonomy: `dienst`, shared with the service pages so a project can link back to "Bouwkundig ontwerp", "Omgevingsvergunning", etc., and a service page can list related projects)*
- Before/after images *(optional — only where the source content actually distinguishes these; most current projects show a single gallery, not explicit before/after)*
- Drawings *(part of the gallery — floor plans/sections already appear in the sampled galleries)*

New taxonomy: `dienst` (service), shared between `project` and used to cross-link from `/diensten/{slug}/` pages to relevant projects. This is the one new structural element — it's what makes "Projects" and "Services" reinforce each other instead of being two disconnected sections, directly serving the brief's trust/credibility goals.

### Contact form submissions
Handled by a lightweight, self-hosted form (native `<form>` + PHP handler, or a minimal-footprint plugin — decided in the theme-build phase per the brief's "no unnecessary plugins" principle) — not a custom post type. Submissions go to `info@rwerkhoven.nl`.

## 4. Navigation

**Primary nav (header):** Home · Diensten · Projecten · Het Bureau · Werkwijze · Contact
**Footer nav:** same links + Privacy + KvK/legal line if confirmed + LinkedIn.

Diensten and Projecten are the two nav items that need dropdown/expansion consideration:
- Diensten: a mega-menu or simple dropdown isn't required by the brief and could feel over-engineered for 6 items; the overview page (`/diensten/`) is one click away and serves as the hub — no dropdown needed, per "prefer simplest robust solution."
- Projecten: no dropdown — the `/projecten/` index with filtering by project type is enough.

## 5. Internal linking strategy

- Every service page links to 2–4 relevant projects (via the `dienst` taxonomy) as proof-of-work.
- Every project page shows which services were provided, each linking back to that service page.
- Homepage services section links to each service page; homepage projects section links to `/projecten/` and to 2–3 individual highlighted projects.
- `/werkwijze/` (process) links into `/contact/` at each step's natural end ("Klaar om te starten? Neem contact op").
- Footer carries the full page-level nav plus a secondary link to `/projecten/`.

## 6. Breadcrumbs

`Home / Diensten / Bouwkundig ontwerp` and `Home / Projecten / {Projectnaam}` — implemented as a template part, paired with `BreadcrumbList` structured data (brief §18).

## 7. What's explicitly out of scope

- No blog/`post` archive — nothing in the audit suggests one exists or is needed; adding one would be a thin-content risk the brief warns against.
- No search results template beyond WordPress's default unless a later phase shows real user need.
- No multi-language setup — current site is Dutch-only with no indication of an international audience requirement (flagged in content-questions.md if this assumption is wrong).
