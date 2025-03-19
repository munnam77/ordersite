#20 3.090 #13 /var/www/html/artisan(37): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))
#20 3.090 #14 {main}
#20 3.090 "} 
#20 3.091 
#20 3.098 In AppServiceProvider.php line 28:
#20 3.098                                                                       
#20 3.098   Undefined constant Illuminate\Http\Request::HEADER_X_FORWARDED_ALL  
#20 3.098                                                                       
#20 3.098 
#20 3.103 Script @php artisan package:discover --ansi handling the post-autoload-dump event returned with error code 1
#20 ERROR: process "/bin/sh -c COMPOSER_ALLOW_SUPERUSER=1 composer install --optimize-autoloader --no-dev --ignore-platform-reqs" did not complete successfully: exit code: 1
------
 > [stage-0 11/14] RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --optimize-autoloader --no-dev --ignore-platform-reqs:
3.090 #13 /var/www/html/artisan(37): Illuminate\\Foundation\\Console\\Kernel->handle(Object(Symfony\\Component\\Console\\Input\\ArgvInput), Object(Symfony\\Component\\Console\\Output\\ConsoleOutput))
3.090 #14 {main}
3.090 "} 
3.091 
3.098 In AppServiceProvider.php line 28:
3.098                                                                       
3.098   Undefined constant Illuminate\Http\Request::HEADER_X_FORWARDED_ALL  
3.098                                                                       
3.098 
3.103 Script @php artisan package:discover --ansi handling the post-autoload-dump event returned with error code 1
------
Dockerfile:49
--------------------
  47 |     
  48 |     # Install dependencies
  49 | >>> RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --optimize-autoloader --no-dev --ignore-platform-reqs
  50 |     
  51 |     # Configure Laravel, create links and clear caches
--------------------
error: failed to solve: process "/bin/sh -c COMPOSER_ALLOW_SUPERUSER=1 composer install --optimize-autoloader --no-dev --ignore-platform-reqs" did not complete successfully: exit code: 1
error: exit status 1