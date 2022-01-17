<?php


namespace Drupal\hbt_log_Manager\Logger;


use Drupal\Core\Logger\RfcLogLevel;

class Logger extends LoggerBase {

  /**
   * @throws \Exception
   */
  public static function persistCombinedLog() {
    parent::persistCombinedLog();
  }

  public static function criticalEvent($event) {
    self::event('critical', array_merge(func_get_args()));
    if (self::$level >= RfcLogLevel::CRITICAL) {
      self::setLevel(RfcLogLevel::CRITICAL);
      self::setType('critical');
      self::setMainMessage($event);
    }
  }

  public static function emergencyEvent($event) {
    self::event('emergency', array_merge(func_get_args()));
    if (self::$level >= RfcLogLevel::EMERGENCY) {
      self::setLevel(RfcLogLevel::EMERGENCY);
      self::setType('emergency');
      self::setMainMessage($event);
    }

  }

  public static function errorEvent($event) {
    self::event('error', array_merge(array_merge(func_get_args())));
    if (self::$level >= RfcLogLevel::ERROR) {
      self::setLevel(RfcLogLevel::ERROR);
      self::setType('Error');
      self::setMainMessage($event);
    }
  }

  public static function warningEvent($event) {
    self::event('warning', array_merge(func_get_args()));
    if (self::$level >= RfcLogLevel::WARNING) {
      self::setLevel(RfcLogLevel::WARNING);
      self::setType('warning');
      self::setMainMessage($event);
    }
  }

  public static function debugEvent($event) {
    self::event('debug', array_merge(func_get_args()));
    if (self::$level >= RfcLogLevel::DEBUG) {
      self::setLevel(RfcLogLevel::DEBUG);
      self::setType('debug');
      self::setMainMessage($event);
    }
  }

  public static function alertEvent($event) {
    self::event('alert', array_merge(func_get_args()));
    if (self::$level >= RfcLogLevel::ALERT) {
      self::setLevel(RfcLogLevel::ALERT);
      self::setType('alert');
      self::setMainMessage($event);
    }
  }

  public static function noticeEvent($event) {
    self::event('notice', array_merge(func_get_args()));
    if (self::$level >= RfcLogLevel::NOTICE) {
      self::setLevel(RfcLogLevel::NOTICE);
      self::setType('notice');
      self::setMainMessage($event);
    }
  }

  public static function infoEvent($event) {
    self::event('info', array_merge(func_get_args()));
    if (self::$level >= RfcLogLevel::INFO) {
      self::setLevel(RfcLogLevel::INFO);
      self::setType('info');
      self::setMainMessage($event);
    }
  }

}