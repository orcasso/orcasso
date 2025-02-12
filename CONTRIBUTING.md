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

Tests
-----

First time, create database :
```bash
docker compose exec mariadb mysql -u root -p -e "CREATE DATABASE orcasso_test; GRANT ALL PRIVILEGES ON orcasso_test.* TO 'orca'@'%'; FLUSH PRIVILEGES;"
```

Run tests
```bash
docker compose exec app composer test
```