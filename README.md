OrcAsso
======

Open-source Registry for Clubs and Associations
----------------------

L'application OrcAsso facilite la gestion des adhérents pour votre association à but non lucratif.
Le code source est libre et l'application gratuite.

Installation
------------

```bash
git clone https://github.com/orcasso/orcasso.git
cd orcasso

mkdir -p var/log

docker compose up -d
docker compose exec app composer install
docker compose exec app bin/console importmap:install
docker compose exec app composer reset:db
```

Create user via CLI
----------------

```bash
docker compose exec app bin/console app:user:create
```
