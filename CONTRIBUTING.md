PHP-CS-FIXER
-------------

We use `friendsofphp/php-cs-fixer` with `.php-cs-fixer.dist.php` configuration

```bash
# Display proposed fixes without changing files
docker compose exec app composer lint:ci

# Apply the proposed fixes
docker compose exec app composer lint
```

Load Fixtures in dev environment
--------------------------------

```bash
docker compose exec app composer reset:db
```
