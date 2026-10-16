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

## Publiceren in drie stappen

### 1. Zet de bestanden op GitHub

Eenmalig inloggen, daarna een publieke repository aanmaken en deze map pushen:

```bash
gh auth login
gh repo create rwerkhoven-demo --public --source=. --remote=demo --push
```

Wil je er geen hele repository voor gebruiken, dan volstaat elke plek die de bestanden publiek en met CORS serveert.

### 2. Vul de basis-URL in

Vervang de plaatshouder door de map waar de vier bestanden staan:

```bash
BASE="https://raw.githubusercontent.com/ri-we/rwerkhoven-demo/master/playground"
sed -i "s#__BASE_URL__#$BASE#g" playground/blueprint.json
git add playground/blueprint.json && git commit -m "Vul de basis-URL van de demo in" && git push demo
```

### 3. Deel de link

```
https://playground.wordpress.net/?blueprint-url=<BASE>/blueprint.json
```

Dus met de URL uit stap 2 wordt dat:

```
https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/ri-we/rwerkhoven-demo/master/playground/blueprint.json
```

Open die link zelf eerst één keer om te controleren dat alles laadt.

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
