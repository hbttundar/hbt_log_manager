<?php


namespace Drupal\hbt_log_Manager\Logger;

use Drupal;
use Drupal\Core\Logger\RfcLogLevel;
use Exception;
use Gelf\Message;
use Gelf\Publisher;
use Gelf\Transport\TcpTransport;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class GrayLog extends Logger implements LoggerInterface {

  /**
   * @throws \Exception
   */
  public static function persistCombinedLog() {
    self::processMessages();
    self::processContext();
    try {
      if (self::$mainMessage) {
        $transport = new TcpTransport(self::getGraylogServer(), self::getGraylogPort());
        $publisher = new Publisher();
        $publisher->addTransport($transport);
        $message = new Message();
        $message->setFullMessage(self::$logEntry->full_message)
          ->setLevel(self::$logEntry->level)
          ->setShortMessage(self::$logEntry->main_message)
          ->setAdditional('request_uri', self::$logEntry->url)
          ->setAdditional('referer', self::$logEntry->referer)
          ->setAdditional('brand', self::$logEntry->brand)
          ->setAdditional('domain', self::$logEntry->host)
          ->setAdditional('ip', self::$logEntry->client_ip)
          ->setAdditional('project', self::$project)
          ->setAdditional('type', self::$logEntry->type)
          ->setAdditional('duration_milliseconds', self::$logEntry->duration)
          ->setAdditional('curl', self::$logEntry->curlCommand)
          ->setAdditional('timestamp', time())
          ->setHost(self::$logEntry->host);
        $publisher->publish($message);
      }
    } catch (Exception $e) {
      DB::persistCombinedLog();
    }
  }

  private static function getGraylogServer() {
    return getenv('GRAYLOG_HOST');

  }

  private static function getGraylogPort() {
    return getenv('GRAYLOG_PORT');
  }

}