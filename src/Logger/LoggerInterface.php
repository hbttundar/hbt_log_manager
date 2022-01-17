<?php

namespace Drupal\hbt_log_Manager\Logger;

interface LoggerInterface {

  public static function startRequest(string $request);

  public static function endRequest(string $request);

  public static function criticalEvent($event);

  public static function emergencyEvent($event);

  public static function errorEvent($event);

  public static function warningEvent($event);

  public static function debugEvent($event);

  public static function alertEvent($event);

  public static function noticeEvent($event);

  public static function infoEvent($event);

  public static function timerAlertEvent($maxTime, $event);

  public static function persistCombinedLog();

}