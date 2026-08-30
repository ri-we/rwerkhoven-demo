# Site Audit — rwerkhoven.nl

Date: 2026-08-30
Method: Live-site inspection only (no admin/FTP access available in this environment — no local WordPress install exists in this repository, which is currently empty). Audited via HTTP requests, sitemap.xml, robots.txt, HTTP headers, and REST API probing.

## 1. Current technical stack

- **CMS:** WordPress, hosted on Plesk (nginx, `X-Powered-By: PleskLin`).
- **Theme:** `FoundationPress` + a child theme `FoundationPress-child` (a hand-coded Zurb Foundation starter theme — not a page builder, but also not Gutenberg/FSE-native; last meaningfully maintained upstream years ago).
- **Plugins detected** (via REST API namespaces, since direct plugin listing isn't exposed):
  - Yoast SEO (sitemaps, meta, robots block confirm this)
  - iThemes Security / Solid Security ("Kadence Security" branding in error responses) — **locks down the REST API for unauthenticated requests**, which is good security hygiene worth preserving.
  - Simple History
  - Duplicate Post
  - WP Smush (image compression) — present, but current hero images are still 300–620KB unoptimized JPEGs, so it isn't achieving much.
  - A "hub-connector" namespace, suggesting a remote management tool (e.g. GoDaddy Pro Hub or ManageWP) is connected.
- **Custom Post Type already exists:** `project`, served at `/project/{slug}/`, with its own `projects-sitemap.xml`. This validates the brief's suggestion to use a `project` CPT — it already exists and just needs a cleaner template and (optionally) proper custom fields instead of ad-hoc body content.
- **No contact form plugin detected.** The `/contact/` page has no `<form>` element in its HTML at all — only `mailto:` and `tel:` links. A `/bedankt-voor-je-contact-bericht/` ("thank you") page exists in the sitemap, implying a form existed at some point (or was planned) but isn't currently live. **This is the single biggest conversion gap**: there is no way to submit an enquiry without leaving the site via email client.
- **robots.txt:** clean, permissive, correctly points to Yoast's `sitemap_index.xml`.
- **404 handling:** returns a proper HTTP 404 status.
- **www → non-www:** `www.rwerkhoven.nl` correctly 301-redirects to `rwerkhoven.nl`. Canonical host is the bare domain.

## 2. Current information architecture

Main navigation (identical in header and footer):

| Page | URL | Last modified |
|---|---|---|
| Home | `/` | 2021-09-10 |
| Het Bureau | `/het-bureau/` | 2020-12-30 |
| Diensten | `/diensten/` | 2020-12-30 |
| Projecten | `/projecten/` | 2019-12-27 |
| Contact | `/contact/` | 2020-12-30 |
| (orphaned) | `/bedankt-voor-je-contact-bericht/` | 2019-10-30 |

Diensten and Het Bureau are single long pages — there is **no sub-page per service** today (contradicts the brief's target IA of individual service pages, but confirms there's room to introduce them without breaking anything, since nothing currently links to service-specific URLs).

**Projects (`project` CPT), 15 published:**

| URL slug | Title shown on /projecten/ |
|---|---|
| `verbouw-woning-abcoude` | Uitbouw vrijstaande woning |
| `diverse-projecten-2` | Diverse projecten |
| `polderhuis-abcoude` | Herbestemming Gemeentehuis Abcoude |
| `woning-oisterwijk` | Verbouw woning Oisterwijk |
| `uitbreiding-villa-bilthoven` | Uitbouw Villa Bilthoven |
| `herbestemming-abn-amro-bank` | Herbestemming ABN AMRO Bank |
| `verbouw-woning-soesterberg` | Verbouw woning Soesterberg |
| `nieuwbouw-woning` | Nieuwbouw woning |
| `verbouw-steps-winkels` | Steps (retail) |
| `aanbouw-abcoude` | Aanbouw Abcoude |
| `didi` | Didi (16 winkels) |
| `kapsalon` | Scissors Hairdesign |
| `diverse-projecten` | Tekeningen diverse projecten |
| `coop` | Coop |
| `didi-utrecht` | Didi Utrecht |

Project detail pages (sampled `uitbreiding-villa-bilthoven` and `didi`) each contain: a title, a short narrative paragraph, an implicit location/client, an implicit list of services provided (written into prose, not structured fields), and an image gallery (6 images observed on both samples — exterior/interior shots plus, for the residential example, floor plans and a section drawing).

**Category taxonomy:** only `Uncategorized` — unused, safe to ignore/drop for `project`.

## 3. Content inventory — what's usable vs. what needs work

### Preserve as factual baseline (do not invent replacements)
- Business name: **Ron Werkhoven Bouwkundig Ontwerp- en Tekenbureau**
- Founded: **2010**, based in **Soest**
- Address: **Hofstedering 65, 3763 XB Soest**
- Phone: **06-13645507**
- Email: **info@rwerkhoven.nl**
- LinkedIn: `linkedin.com/in/ron-werkhoven-2594881a`
- Client types served: particulieren, bedrijven, aannemers, vastgoedbeheerders, architecten- en adviesbureaus
- Positioning phrase already in use: "creativiteit, snelheid en betrouwbaarheid", one point of contact from design through realization
- The 8 concrete services listed on `/diensten/` (bouwaanvraagtekeningen, omgevingsvergunning, werktekeningen, splitsingstekeningen, brandveilig gebruik-vergunning, gebruiksmelding, ontruimingsplattegronden, digitaliseren/inmeten)
- All 15 project narratives and their images — real project history, must not be dropped or fictionalized further
- Existing meta description on the homepage is actually well-written and factual — reusable almost verbatim.

### Needs rewriting / fixing
- **Site title is literally "My CMS"** — leaks into every `<title>` tag and Open Graph tag (`Home — My CMS`, `og:site_name: My CMS`). This was never set in WP General Settings. High-priority fix.
- **`og:locale` is `en_US`** on a fully Dutch site — should be `nl_NL`.
- Project pages bury structured facts (location, year, services, client) inside prose instead of exposing them as scannable fields/labels.
- No dedicated service pages to target service-specific search intent or internal-link into projects.
- "Het Bureau" page has almost no personal/credibility content beyond the founding year and positioning tagline — thin for a trust-building page; needs expansion using only verifiable facts (flag anything uncertain in content-questions.md rather than inventing it).
- Homepage H1 was reported as repeating identically across the hero slider (same H1 text 5×) — likely one `<h1>` reused per slide by the Foundation slider component; new build must have exactly one H1 per page.

### Technical/SEO/performance issues found
1. **No contact form** — biggest conversion blocker.
2. Hero images are CSS `background-image` slides, not `<img>` — no alt text, no `srcset`/responsive sizing, not preloadable, invisible to image search and screen readers.
3. Hero/project images are unoptimized JPEGs (300–620KB each observed) despite Smush being installed — no WebP/AVIF, no responsive sizes.
4. Generic `alt=""` on some images (e.g. LinkedIn icon) — acceptable there, but pattern likely repeats on content images that do need real alt text; needs verification per image during migration.
5. Diensten and Het Bureau being single flat pages means no per-service canonical URL to rank for terms like "bouwtekeningen verbouwing" or "omgevingsvergunning aanvragen" independently.
6. No structured data (`LocalBusiness`/`ProfessionalService`) detected in the fetched HTML.

## 4. What must not be lost in the rebuild

- All 15 project URLs and their content (`/project/{slug}/`) — if slugs change, they need 301 redirects to the new equivalents.
- The 5 core page URLs (`/`, `/het-bureau/`, `/diensten/`, `/projecten/`, `/contact/`) — the brief's target IA keeps these exact same top-level paths, so no redirects needed there. New sub-pages under `/diensten/` are additive, not replacements.
- Existing homepage meta description (factually accurate, keep or lightly edit).
- `/bedankt-voor-je-contact-bericht/` is orphaned today; once a real contact form exists, either reuse this URL as its thank-you page or 301 it to the new one — don't leave it as a dead indexed URL.
- LinkedIn profile link.
- Privacy Statement PDF currently at `/wp-content/uploads/2019/12/Privacy-Statement.pdf` — verify with the business owner whether this is current before relinking it (flagged in content-questions.md).

## 5. Migration risks

- **No local WordPress install or export exists in this repository** — this audit was done entirely against the live site over HTTP. Before any destructive change on the live site, a full export (database + `wp-content/uploads`) must be taken. This repo will hold the new theme's source code; deployment/import strategy needs to be confirmed with whoever manages Plesk hosting access (flagged in content-questions.md).
- REST API is access-restricted by the security plugin — any tooling built for content migration must either use authenticated requests (application passwords) or work from an admin-exported dataset, not anonymous REST calls.
- Because `project` already exists as a working CPT with live indexed URLs, the rebuild should **keep the slug `project` and existing post slugs** rather than introducing a new CPT name — avoids a large redirect map for content Google has already indexed since 2020.
- Image assets referenced by old post content (`/wp-content/uploads/...`) must be carried over or re-pointed; galleries can't be rebuilt without the original files.
- Changing `og:locale` and site title is safe and has no redirect implications, but will change how the site appears in existing social shares/search snippets briefly after re-indexing.

## 6. Recommended improvements (summary, expanded in later docs)

- Add a real, accessible, spam-protected contact form (see brief §12) — highest-impact single change.
- Fix site title / tagline in WP settings; fix `og:locale`.
- Convert hero slider to real responsive `<img>`/`<picture>` markup with alt text and modern formats.
- Split `/diensten/` into an overview page + one page per service, cross-linked to relevant projects.
- Add structured fields to the `project` CPT (location, year, project type, services provided, client) instead of free-text-only, so the template can render them consistently and they become part of the page's semantic structure/schema.
- Add `LocalBusiness`/`ProfessionalService` + `BreadcrumbList` structured data.
- Expand "Het Bureau" with genuine, verifiable credibility content once the owner confirms what can be stated.

Open questions arising from this audit are tracked separately in `/docs/content-questions.md` (to be created in a later phase) rather than answered by assumption here.
