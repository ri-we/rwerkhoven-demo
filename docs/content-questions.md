# Open Content Questions

Compiled during the site audit and architecture phases (`docs/site-audit.md`, `docs/site-architecture.md`). None of these block technical implementation — placeholders are used where noted, and this list should be worked through with Ron Werkhoven before final copy/launch.

## Business facts

1. **Service area.** The current site doesn't state a geographic radius. Soest is the base — is the practical service area "Soest en omgeving," the wider Utrecht/Het Gooi region, or does work genuinely happen nationwide (the Didi project spanned 16 locations across NL and Belgium, suggesting no hard geographic limit)? This affects local SEO copy (§19) — using a placeholder of "Soest, Utrecht en omgeving, en daarbuiten" until confirmed.
2. **KvK / BTW number.** Not published anywhere on the current site. Should it be added to the footer (common for Dutch business trust signals)? If yes, please provide it — it will not be guessed or fabricated.
3. **Qualifications/education.** "Het Bureau" states only a 2010 founding date. Is there a specific education, prior work history, or professional body membership that can be named? Left out entirely until confirmed rather than implied.
4. **Years of experience framing.** Is "sinds 2010" (founded 2010) accurate to state directly, or is there earlier relevant experience predating the business itself that should be mentioned separately (e.g. "meer dan X jaar ervaring in de bouwkundige sector" as distinct from "bedrijf opgericht in 2010")?
5. **Testimonials/reviews.** The current contact page has a "Wilt u een review geven, klik hier" link — where do those reviews live (Google Business Profile? another platform?), and is there consent to quote any of them on the new site? No testimonials will be added without a real, attributable source.
6. **Preferred contact method.** Phone, email, or is a contact form clearly preferred? This affects CTA ordering/emphasis on the homepage and contact page.
7. **Site visits.** Does Ron Werkhoven offer in-person site visits as a standard part of the process (relevant to the "Kennismaken" step in Werkwijze)?
8. **Working with contractors.** Does the practice have relationships with specific aannemers it regularly works alongside, or does it stay strictly on the design/drawing/permit side and hand off? Relevant to how "Realisatie" (step 5 of the process) is worded.

## Per-project confirmation

9. Several project pages state location/year within prose but not always both — during content migration, confirm **year completed** for each of the 15 projects where it isn't explicit (sampled pages had it, but not all 15 were individually re-verified field-by-field).
10. **Which projects should be featured on the homepage** (only room for 2–3 highlights)? Suggest letting Ron pick, since "most impressive" and "most representative of typical work" may differ.
11. Are there **before/after photo pairs** for any project, or does the archive only contain final-result photography? The brief's project template supports before/after but it should only be shown where genuinely available.
12. Any projects that **should not be public** (e.g. sensitive commercial clients, NDAs)? Currently assuming all 15 indexed projects remain publishable since they're already live and indexed.

## Legal

13. The current Privacy Statement PDF (`/wp-content/uploads/2019/12/Privacy-Statement.pdf`) is dated 2019 — is it still accurate (data processing practices, form tooling, analytics used), or does it need a rewrite once the new contact form and any analytics are decided? Recommend rewriting as an HTML page (`/privacy/`) rather than re-linking the old PDF as-is, but the legal content itself must come from the business owner or their advisor, not be invented here.
14. **Analytics.** Is any analytics tool desired (privacy-friendly options exist, e.g. self-hosted/cookieless), or should the new site ship with none by default per the "minimise third-party tracking" principle (§26)? Defaulting to **no analytics** until requested.
15. **Cookies.** With no analytics and no embedded third-party widgets (no YouTube/Maps embeds currently found), the new site likely needs no cookie banner at all. Flagging this as an assumption to confirm rather than building a banner nobody needs.

## Hosting / deployment

16. This repository currently has no WordPress install, and admin/FTP access to the live Plesk-hosted site isn't available in this environment. Development will proceed on a local WordPress instance; a `docs/deployment.md` (final-phase deliverable) will describe how to move the finished theme to the live Plesk site, but actually performing that deployment will require credentials or hands-on-keyboard access from Ron Werkhoven or whoever manages hosting.

## Inhoudelijk te verifiëren door Ron (toegevoegd 2026-09-04)

17. **Omgevingsvergunning-tekst geactualiseerd.** De oude sitetekst ("de omgevingsvergunning vervangt sinds 2010 …", EPN-berekeningen) was gebaseerd op de situatie vóór de Omgevingswet. De nieuwe pagina `/diensten/omgevingsvergunning/` beschrijft nu: Omgevingswet per 1-1-2024, aanvragen via het Omgevingsloket, de knip tussen omgevingsplan-toets en technische bouwregels, de Wet kwaliteitsborging voor het bouwen (kwaliteitsborger bij bepaalde nieuwbouw), en BENG i.p.v. EPN. **Graag vakinhoudelijk checken**: kloppen de genoemde drempels bij brandveilig gebruik (nachtverblijf > 10 personen; gebruiksmelding > 50 personen) nog met de huidige Bbl-regels, en dekt de Wkb-formulering de praktijk van het bureau?
