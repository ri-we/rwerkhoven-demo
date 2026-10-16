# Demo in WordPress Playground

Deze map bevat alles om de site als klikbare demo te openen in [WordPress Playground](https://playground.wordpress.net/) — WordPress dat volledig in de browser van de bezoeker draait, zonder hosting.

## Wat je moet weten voordat je dit deelt

- **Playground is geen hosting.** De site wordt bij elke keer openen opnieuw opgebouwd in de browser van wie de link aanklikt. Wat je klant daar verandert, is weg zodra het tabblad sluit. Prima voor een review, niet geschikt als acceptatieomgeving.
- **De bestanden hieronder moeten publiek bereikbaar zijn.** Playground haalt ze op vanuit de browser, dus de URL's moeten zonder inloggen werken én CORS toestaan. GitHub voldoet daaraan (`raw.githubusercontent.com`).
- **De demo weegt ongeveer 36 MB**, bijna helemaal foto's. Reken op tien tot twintig seconden laden bij de eerste keer. Playground toont ondertussen een voortgangsbalk.
- De foto's zijn teruggeschaald naar maximaal 1200 pixels breed en opnieuw gecomprimeerd. Dat is ruim genoeg voor het scherm, maar het zijn niet de originelen.

## Inhoud van deze map

| Bestand | Wat het is |
|---|---|
| `blueprint.json` | Het recept dat Playground uitvoert. Bevat nog de plaatshouder `__BASE_URL__`. |
| `rwerkhoven-theme.zip` | Het thema, 0,5 MB. |
| `uploads.zip` | De 469 mediabestanden, 34 MB. |
| `content.json` | Alle pagina's, projecten, bijlagen en instellingen, 0,3 MB. |
| `seed.php` | Bouwt de inhoud op uit `content.json`. Haalt niets van internet. |

`content.json` en `uploads.zip` worden gemaakt door `scripts/export-playground.php`. Draai dat script opnieuw zodra de inhoud van de site verandert.

## De link om te delen

Dit staat live op `ri-we/rwerkhoven-demo`, tak `master`:

```
https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/ri-we/rwerkhoven-demo/master/playground/blueprint.json
```

## Bijwerken na een wijziging aan de site

De demo is een momentopname. Verandert er iets aan het thema of de inhoud, dan moet je het pakket opnieuw maken en pushen; de link zelf blijft gelijk.

```bash
# inhoud opnieuw exporteren
docker compose cp scripts/export-playground.php wpcli:/tmp/export-playground.php
docker compose exec -T wpcli wp eval-file /tmp/export-playground.php
docker compose cp wpcli:/tmp/content.json playground/content.json

git add playground && git commit -m "Demo bijwerken" && git push demo master
```

Het thema- en het media-zipbestand worden gemaakt zoals beschreven in de commitgeschiedenis van deze map. Let op: `scripts/export-playground.php` leest de afmetingen van de afbeeldingen uit de teruggeschaalde set in `/tmp/pg-final`. Bestaat die map niet, dan neemt het script de afmetingen van de volledige originelen over en kloppen de `srcset`-waarden niet meer met de bestanden in `uploads.zip`. Bouw die set dus opnieuw voordat je exporteert.

Wil je de bestanden ergens anders neerzetten, dan kan dat op elke plek die ze publiek serveert én CORS toestaat. Vervang daarna de vier URL's in `blueprint.json`.

## Aanpassen

- **Lichter maken.** Verlaag `$max_w` en `$quality` in het script dat de mediaset bouwt, of laat ongebruikte afbeeldingsmaten weg. `scripts/export-playground.php` laat automatisch elke maat vallen waarvan het bestand ontbreekt, dus de srcset blijft kloppen.
- **Op een andere pagina laten openen.** Pas `landingPage` aan, bijvoorbeeld `"/diensten/"`.
- **Beheeromgeving tonen.** Playground logt standaard in als beheerder. Wil je dat je klant alleen de voorkant ziet, voeg dan `"login": false` toe naast `landingPage`.

## Lokaal controleren zonder Playground

`seed.php` draait ook gewoon in de ontwikkelomgeving. Zo bouw je de inhoud na in een lege database, wat is gebruikt om deze export te verifiëren:

```bash
docker compose exec -T -u root wpcli sh -c 'mkdir -p /var/www/html/pgseed && chmod 0777 /var/www/html/pgseed'
docker compose cp playground/content.json wpcli:/var/www/html/pgseed/content.json
docker compose cp playground/seed.php    wpcli:/var/www/html/pgseed/seed.php
docker compose run --rm --entrypoint wp -e WORDPRESS_DB_NAME=pgtest wpcli eval-file /var/www/html/pgseed/seed.php
```
