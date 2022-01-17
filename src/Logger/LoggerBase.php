<?php

namespace Drupal\hbt_log_Manager\Logger;

use Drupal;
use DateTime;
use Exception;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\mkt_host_manager\Controller\Host;
use Symfony\Component\HttpFoundation\Request;
use Drupal\hbt_log_Manager\Helper\LogManagerHost;
use Drupal\hbt_log_Manager\Helper\LogManagerConfig;


class LoggerBase implements LoggerInterface {

  private static ?LoggerInterface $_instance = NULL;

  protected static ?Request $request = NULL;

  protected static string $project = 'drupal 8';

  protected static $startTimer = FALSE;

  protected static $stepTimer = FALSE;

  protected static array $combinedMessages = [];

  protected static array $splitMessages = [];

  protected static string $brand;

  protected static string $mainMessage = '';

  protected static int $level = 6;

  protected static string $curlCommand = '';


  protected static string $type = '';

  /**
   * @var NULL|LogEntry
   */
  protected static ?LogEntry $logEntry = NULL;

  protected static string $loggerType = 'GrayLog';

  protected static string $uri = '';

  protected static array $context = [];

  protected function __construct() {
    self::$request = Drupal::request();
    self::$brand = Host::getEnvHost();
    self::$loggerType = LogManagerConfig::getDefaultLogger();
    self::$uri = self::$request->getUri();
    drupal_register_shutdown_function([$this, 'persistCombinedLog']);
  }


  public static function getInstance(): LoggerInterface {
    if (empty(self::$_instance)) {
      return new static();
    }
    return self::$_instance;
  }

  public static function get(): LoggerInterface {
    switch (self::$loggerType) {
      case 'DB':
        return DB::getInstance();
      case 'GrayLog':
      default:
        return GrayLog::getInstance();
    }
  }

  public static function startRequest(string $request) {
    if (self::$startTimer == NULL) {
      self::$startTimer = DateTime::createFromFormat('U.u', microtime(TRUE));
      self::$stepTimer = DateTime::createFromFormat('U.u', microtime(TRUE));
    }
    self::event('info', ["start rendering this request:[$request]"]);
  }

  public static function endRequest(string $request) {
    $duration = self::stepDifference(TRUE);
    if ($duration > 15000) {
      self::event('alert', ['low Command took over 15 seconds ']);
    } else {
      self::event('info', ["rendering request:[$request] finished and get:[$duration] time"]);
    }
  }

  public static function stepDifference($fromStart = FALSE) {
    $compareTimer = self::$stepTimer;
    self::$stepTimer = DateTime::createFromFormat('U.u', microtime(TRUE));
    if ($fromStart) {
      $compareTimer = self::$startTimer;
    }
    $duration = 0;
    if (method_exists(self::$stepTimer, 'format') && method_exists($compareTimer, 'format')) {
      $duration = (self::$stepTimer->format('U.u') - $compareTimer->format('U.u')) * 1000;
    }
    return is_numeric($duration) ? (float) number_format($duration, 4, '.', '') : 0;
  }

  public static function event($name, $arguments, $duration = NULL) {
    $arguments['duration'] = self::stepDifference();
    if ($duration != NULL) {
      $arguments['duration'] = $duration;
    }
    if ($arguments['duration'] > 15000 && !$duration) {
      self::event('alert', ['Slow Command took over 15 seconds']);
    }
    self::$combinedMessages[] = [$name => $arguments];
    self::$splitMessages[$name][] = $arguments;
  }

  protected static function setMainMessage($message) {
    self::$mainMessage = self::$type . ": ";
    if (is_string($message)) {
      self::$mainMessage .= $message;
    } elseif (is_array($message) && is_array($message[key($message)])) {
      self::$mainMessage .= (is_array($message[key($message)]) ? var_export($message, TRUE) : $message[key($message)]);
    } else {
      self::$mainMessage .= is_string($message[0]) ? $message[0] : var_export($message, TRUE);
    }
  }

  /**
   * @return string
   */
  public static function getType(): string {
    return self::$type;
  }

  /**
   * @param string $type
   */
  public static function setType(string $type): void {
    self::$type = $type;
  }

  /**
   * @return int
   */
  public static function getLevel(): int {
    return self::$level;
  }


  protected static function setLevel(int $level) {
    if (self::$level > $level) {
      self::$level = $level;
    }
  }

  public static function timerAlertEvent($maxTime, $event) {
    $duration = self::stepDifference();
    if ($duration >= $maxTime) {
      $arguments = array_merge(['Exceeded expected execution time of ' . $maxTime . ' took ' . $duration], array_slice(func_get_args(), 1));
      self::event('debug', $arguments, $duration);
      if (self::$level >= RfcLogLevel::DEBUG) {
        self::$level = RfcLogLevel::DEBUG;
        self::$type = 'debug';
        self::setMainMessage($arguments);
      }
    }
  }

  public static function processMessages() {
    $request = Drupal::request();
    $logEntry = new LogEntry($request);
    $logEntry->main_message = self::$mainMessage;
    $logEntry->combined_message = self::$combinedMessages;
    $logEntry->split_message = self::$splitMessages;
    $logEntry->duration = self::stepDifference(TRUE);
    $logEntry->level = self::$level;
    $logEntry->type = self::$type;
    $logEntry->project = self::$project;
    $logEntry->processMessage();
    self::$logEntry = $logEntry;
  }

  protected static function processContext() {
    $request = Drupal::request();
    self::$context = [
      /**
       * this section add to prevent the core bug from insert in watchdog
       */
      /****************************************************************************************************************/
      'uid' => Drupal::currentUser()->id(),
      'request_uri' => $request->getUri(),
      'referer' => Host::getReferrer($request),
      'ip' => Host::getClientIp(self::$request),
      'link' => '',
      'timestamp' => time(),
      'channel' => 'DB'
      /****************************************************************************************************************/
    ];
  }

  /**
   * @throws \Exception
   */
  public static function persistCombinedLog() {
    try {
      switch (self::getLoggerType()) {
        case 'GrayLog':
          GrayLog::persistCombinedLog();
          break;
        case 'DB':
          DB::persistCombinedLog();
      }
    } catch (Exception $e) {
      error_log($e->getMessage());
    }

  }

  protected static function getLoggerType(): string {
    return self::$loggerType;
  }


  public static function criticalEvent($event) {
    self::event('critical', $event);
  }

  public static function emergencyEvent($event) {
    self::event('emergency', $event);
  }

  public static function errorEvent($event) {
    self::event('error', $event);
  }

  public static function warningEvent($event) {
    self::event('warning', $event);
  }

  public static function debugEvent($event) {
    self::event('debug', $event);
  }

  public static function alertEvent($event) {
    self::event('alert', $event);
  }

  public static function noticeEvent($event) {
    self::event('notice', $event);
  }

  public static function infoEvent($event) {
    self::event('info', $event);
  }


}