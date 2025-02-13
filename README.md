Installation
------------

```bash
git clone https://github.com/orcasso/orcasso.git
cd orcasso

mkdir -p var/log

docker compose up -d
docker compose exec app composer install
```