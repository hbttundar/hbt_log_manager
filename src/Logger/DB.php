<?php


namespace Drupal\hbt_log_Manager\Logger;

use Drupal;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;


class DB extends LoggerBase {

  public static function persistCombinedLog() {
    try {
      self::processMessages();
      self::processContext();
      $logger = Drupal::service('logger.dblog');
      $logger->log(self::$logEntry->level, self::$logEntry->main_message . "\r\n{" . self::$logEntry->full_message . "\r\n}", self::$context);
    } catch (Exception $e) {
      error_log($e->getMessage());
    }
  }


}