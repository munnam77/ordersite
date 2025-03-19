7.145 60 package suggestions were added by new dependencies, use `composer suggest` to see details.
#20 7.147 Generating optimized autoload files
#20 8.006 > Illuminate\Foundation\ComposerScripts::postAutoloadDump
#20 8.015 > @php artisan package:discover --ansi
#20 8.042 
#20 8.042 Warning: require_once(/var/www/html/bootstrap/app.php): Failed to open stream: No such file or directory in /var/www/html/artisan on line 20
#20 8.042 
#20 8.042 Fatal error: Uncaught Error: Failed opening required '/var/www/html/bootstrap/app.php' (include_path='.:/usr/local/lib/php') in /var/www/html/artisan:20
#20 8.042 Stack trace:
#20 8.042 #0 {main}
#20 8.042   thrown in /var/www/html/artisan on line 20
#20 8.044 Script @php artisan package:discover --ansi handling the post-autoload-dump event returned with error code 255
#20 ERROR: process "/bin/sh -c COMPOSER_ALLOW_SUPERUSER=1 composer install --optimize-autoloader --no-dev --ignore-platform-reqs" did not complete successfully: exit code: 255
------
 > [stage-0 11/14] RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --optimize-autoloader --no-dev --ignore-platform-reqs:
8.006 > Illuminate\Foundation\ComposerScripts::postAutoloadDump
8.015 > @php artisan package:discover --ansi
8.042 
8.042 Warning: require_once(/var/www/html/bootstrap/app.php): Failed to open stream: No such file or directory in /var/www/html/artisan on line 20
8.042 
8.042 Fatal error: Uncaught Error: Failed opening required '/var/www/html/bootstrap/app.php' (include_path='.:/usr/local/lib/php') in /var/www/html/artisan:20
8.042 Stack trace:
8.042 #0 {main}
8.042   thrown in /var/www/html/artisan on line 20
8.044 Script @php artisan package:discover --ansi handling the post-autoload-dump event returned with error code 255
------
Dockerfile:62
--------------------
  60 |     
  61 |     # Install dependencies
  62 | >>> RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --optimize-autoloader --no-dev --ignore-platform-reqs
  63 |     
  64 |     # Configure Laravel, create links and clear caches
--------------------
error: failed to solve: process "/bin/sh -c COMPOSER_ALLOW_SUPERUSER=1 composer install --optimize-autoloader --no-dev --ignore-platform-reqs" did not complete successfully: exit code: 255
error: exit status 1