# PitStop.sk — inštalácia a rýchly štart

Obsah
- Úvod
- Rýchle spustenie pomocou Docker Compose
- Vzorový súbor `.env`
- Databázové skripty (import)
- Spustenie bez Dockeru (lokálny PHP)
- Tipy na ladieie a bezpečnosť
- Kontakt / ďalšie informácie

Úvod
----
Aplikácia beží vo webovom root-e `public/`. V projekte je pripravené Docker prostredie (v priečinku `docker/`) — obsahuje službu web (Apache + PHP 8.3), MariaDB a Adminer pre jednoduchú správu DB.

Rýchle spustenie pomocou Docker Compose (Windows / cmd.exe)
---------------------------------------------------------
1) Skopírujte alebo vytvorte súbor s premennými prostredia pre Docker Compose. Odporúčame vytvoriť súbor `docker/.env` s nastaveniami pre MariaDB (príklad ďalej).

2) Otvorte príkazový riadok (cmd.exe) a spustite kontajnery:

    cd docker
    docker-compose up -d

3) Počkajte, kým sa kontajnery spustia. Webová aplikácia bude dostupná na:

    http://localhost/

   Adminer (webový DB klient) bude dostupný na:

    http://localhost:8080/

   Prihláste sa do Adminer pomocou údajov z `docker/.env` (server: `db`, používateľ a heslo podľa `.env`).

Vzorový súbor `docker/.env`
---------------------------
Vytvorte súbor `docker/.env` (s rovnakým názvom a umiestnením, kde je `docker-compose.yml`) s niečím podobným:

    MARIADB_ROOT_PASSWORD=changeme_root_password
    MARIADB_DATABASE=vaiicko_db
    MARIADB_USER=vaiicko_user
    MARIADB_PASSWORD=dtb456

Poznámky:
- Hodnoty zvoľte bezpečne (nepoužívajte v produkcii jednoduché heslá).
- Ak zmeníte názov DB / používateľa / hesla, upravte podľa potreby aj `App/Configuration.php` alebo nechajte tieto hodnoty a používajte rovnaké v konfigu.

Databázové skripty
------------------
V priečinku `docker/sql/` sú pripravené SQL skripty, ktoré sa pri prvom štarte MariaDB kontajnera automaticky importujú (vďaka mountu do `/docker-entrypoint-initdb.d`). Obsahuje napr. `create_users.sql` a `create_posts.sql`.

Ak chcete importovať ručne, môžete v Adminer vykonať obsah týchto súborov alebo v konteineri spustiť psql/mysql cli.

Tipy na ladieie a bezpečnosť
----------------------------
- CSRF ochrana: projekt obsahuje minimalistickú CSRF ochranu pre HTML formuláre (token v session a hidden `_csrf` input vo vybraných formách).
- Session cookies: ak nasadzujete na HTTPS, zabezpečte, aby `session.cookie_secure` bolo zapnuté a `httponly` + `samesite` boli nastavené podľa potreby.
- Heslá: používame `password_hash()` a `password_verify()` pre nové heslá. Projekt obsahuje fallback pre staré plaintext heslá (automatické prehashovanie pri prvom prihlásení).
- Režim vývoja: v `App/Configuration.php` je možnosť zapnúť detailné výpisy SQL a chýb (používajte len pri lokálnom vývoji).

Najčastejšie problémy a riešenia
--------------------------------
- Kontajnery sa nespustia: skontrolujte `docker-compose logs` alebo `docker-compose -f docker\docker-compose.yml logs -f`.
- MariaDB neprijíma spojenie: overte hodnoty v `docker/.env` a že kontajner DB je zdravý (`docker ps`).
- Súbory v `public/` sa neukazujú: overte, že `APACHE_DOCUMENT_ROOT` a mount v `docker/docker-compose.yml` sú správne a že spúšťate `docker-compose` v priečinku `docker` (alebo použijete -f s absolútnou cestou).

Ďalšie informácie
-----------------
- Projekt obsahuje komentovaný zdroj a jednoduchú dokumentáciu vo WIKI autora (odkazy v pôvodnom repozitári). Pre interné úpravy preštudujte `Framework/` a `App/` priečinky.

Kontakt
-------
Ak potrebuješ pomôcť s inštaláciou alebo chceš, aby som pridal automatické kroky (napr. skript `start-local.bat` pre Windows, single-use CSRF token, alebo meta tag pre AJAX CSRF), napíš čo presne chceš a implementujem to.


---
Automaticky vygenerované inštrukcie pre tento projekt. Uprav ich podľa vlastných preferencií pred zdieľaním ďalej.
