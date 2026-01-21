Úvod

PitStop.sk je PHP webová aplikácia. Verejná časť aplikácie beží z adresára public/.
Projekt obsahuje pripravené Docker prostredie, ktoré umožňuje rýchle spustenie aplikácie bez manuálnej inštalácie PHP, databázy alebo webového servera.

Docker konfigurácia sa nachádza v súbore:

app/docker/docker-compose.yml
Požiadavky

Pred spustením aplikácie je potrebné mať nainštalované:

Docker

Docker Compose

Vytvorte súbor s premennými prostredia:

app/docker/.env

Príklad obsahu:

MARIADB_ROOT_PASSWORD=changeme_root_password
MARIADB_DATABASE=pitstop_db
MARIADB_USER=pitstop_user
MARIADB_PASSWORD=dtb456

Z koreňového adresára projektu spustite Docker Compose

Po spustení budú dostupné tieto služby:

Webová aplikácia:
http://localhost/

Adminer (správa databázy):
http://localhost:8080/

Server: db
Prihlasovacie údaje podľa súboru .env

Databáza

SQL skripty sa nachádzajú v priečinku:

app/docker/sql/

Vývojový režim je možné nastaviť v App/Configuration.php