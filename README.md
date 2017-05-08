Example application.json file:

```
{
    "application": {
        "layout": "{{LAYOUT}}",
        "unsecure_base_url": "{{UNSECURE_BASE_URL}}",
        "secure_base_url": "{{SECURE_BASE_URL}}",
        "use_ssl": false,
        "force_ssl": false,
        "encryption_key": "{{ENCRYPTION_KEY}}",
        "title": "{{APPLICATION_TITLE}}",
        "default_route": "{{DEFAULT_ROUTE}}",
        "database": {
            "adapter": "mysql",
            "host": "{{MYSQL_HOST}}",
            "name": "{{MYSQL_DBNAME}}",
            "user": "{{MYSQL_USER}}",
            "password": "{{MYSQL_PASSWD}}"
        },
        "cache": {
            "enabled": false
        }
    }
}
```

Example .htaccess file:

```
DirectoryIndex index.php

Options +FollowSymLinks
RewriteEngine on

RewriteCond %{REQUEST_URI} !^/(media|skin|js)/

############################################
## never rewrite for existing files, directories and links

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-l

############################################
## rewrite everything else to index.php

RewriteRule .* index.php [L]
```