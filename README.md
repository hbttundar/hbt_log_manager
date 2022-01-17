## About hbt_log_Manager

hbt_log_manager is a simple modules that allows you to improve the way you log into graylog in drupal 8 apps

 - before doing any thing you should know that this modules need that this package install on your drupal 8 project using composer
 - run following command in your root project of drupal 8 apps
 - [ composer require  "graylog2/gelf-php" ]
 - after install garylog gelf-php packages you can download this modules and add it to your drupal 8 modules folder
 - this folder is in web->modules->{your company modules folder}->hbt_log_manager
 - after that you can install the module using admin ui or using drush command
 - for drush command you can install it like this : [drush -l "your-site-name" pm-enable hbt_log_manager or drush pm-enable hbt_log_manager]
 - you should also define env variable that contain your graylog host address and your graylog port number
    - [GRAYLOG_HOST = {your graaylog host}]
    - [GRAYLOG_PORT = {your graaylog port}]
- as defualt in this module we use tcp gelf connections , if you wanna change it feel free to change it in src->Logger->Gralog.php
- remember that this is not a package this is a drupal module and need drupal installation cause it use sort of drupal service container
## how to use it in code

by Installing this module in your drupal 8 your application send log message for whole request that received to graylog in once and also anonymous the secret key like password token and ...
you have this static modules that you can use every where in your drupal app :

- [Logger:infoEvent() for example - Logger::infoEvent('rendering method in xxx class ', $data)]
- [Logger::debugEvent()  for example - Logger::debugEvent('debug info for special process  in InDrupal', $data)]
- [Logger::warningEvent()  for example - Logger::warningEvent('warning in InDrupal', $data)]
- [Logger::errorEvent()  for example - Logger::errorEvent('Exception in InDrupal', $data)]
- [Logger::emergencyEvent()  for example - Logger::emergencyEvent('Emergency error in In Drupal method xxx', $data)]
- [Logger::criticalEvent()  for example - Logger::criticalEvent('critical error in In Drupal method xxx', $data)]

## License
This drupal 8 module  is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
