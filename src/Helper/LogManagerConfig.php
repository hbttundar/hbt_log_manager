<?php

namespace Drupal\hbt_log_Manager\Helper;

use Drupal;
use Drupal\Core\Config\ImmutableConfig;

class LogManagerConfig {


  public static function getEnableStatus(): bool {
    $result = static::getConfig()->get('Enable');
    return !empty($result) ? $result : FALSE;
  }

  public static function getConfig(): ImmutableConfig {
    return Drupal::config('hbt_log_Manager.settings');
  }

  public static function getDefaultLogger() {
    $result = NULL;
    switch (ENVIRONMENT) {
      case 'DEV':
        $result = static::getConfig()
          ->get('Log_aggregation_for_Development_Environment.default_logger');
      case 'STAGE':
        $result = static::getConfig()
          ->get('Log_aggregation_for_Test_Environment.default_logger');
      case 'LIVE':
      default:
        $result = static::getConfig()
          ->get('Log_aggregation_for_Live_Environment.log_into_graylog');
    }
    return $result ?? 'GrayLog';
  }

}
