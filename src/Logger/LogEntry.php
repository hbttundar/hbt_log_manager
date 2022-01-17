<?php


namespace Drupal\hbt_log_Manager\Logger;


use function Aws\default_user_agent;
use Drupal\devel\Plugin\Devel\Dumper\Kint;
use Drupal\mkt_host_manager\Controller\Host;
use Symfony\Component\HttpFoundation\Request;
use Drupal\hbt_log_Manager\Helper\LogManagerHost;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Drupal\hbt_log_Manager\Helper\LogManagerDataHelper;

class LogEntry {

  protected string $main_message = '';

  protected array $combined_message = [];

  protected float $duration = 0.0;

  protected string $url = '';

  protected $client_ip = '';

  protected int $level = 6;

  protected string $type = '';

  protected array $split_message = [];

  protected string $brand = '';

  protected array $get = [];

  protected array $post = [];

  protected string $full_message = '';

  protected string $curlCommand = '';

  protected string $host = '';

  protected bool $verbose = TRUE;

  protected string $referer = '';

  protected string $back_trace = '';

  /**
   * @var \Symfony\Component\Serializer\Encoder\JsonEncoder
   */
  protected JsonEncoder $encoder;


  public function __construct(Request $request) {
    $this->brand = Host::getBrand();
    $this->host = Host::getEnvHost();
    $this->url = Host::getUri($request);
    $this->referer = Host::getReferrer($request);
    $this->client_ip = Host::getClientIp($request);
    $this->get = $_GET ?? [];
    $this->post = $_POST ?? [];
  }

  public function processMessage() {
    $this->processMainMessage();
    if ($this->verbose && $this->level <= 3) {
      $this->processBackTrace();
    }
    $this->processFullMessage();
    $this->processCurlCommand();
  }

  private function processCurlCommand() {
    $this->curlCommand = "curl '" . $this->url;
    $phpInput = file_get_contents('php://input');
    if ($phpInput && strlen($phpInput)) {
      $this->curlCommand .= " -XPOST '" . $phpInput . "'";
    } elseif (isset($this->post) && count($this->post) > 0) {
      $this->curlCommand .= " -XPOST '" . http_build_query($this->post) . "'";
    }
    if (is_array(getallheaders())) {
      foreach (getallheaders() as $header => $value) {
        $this->curlCommand .= " -H '" . $header . ": " . $value . "'";
      }
    }
  }

  private function processBackTrace() {
    $debug_backtrace = debug_backtrace();
    if (!empty($debug_backtrace)) {
      $this->back_trace = "----------------------------------------------------------------------------------------------------\r\n";
      $this->back_trace .= str_pad($this->brand, 28) . " Backtrace @ " . date('r') . " " . $this->main_message . "\r\n";
      $this->back_trace = "----------------------------------------------------------------------------------------------------\r\n";
      foreach ($debug_backtrace as $key => $value) {
        $this->back_trace .= (is_string($key) ? $key . ":" : '');
        $this->back_trace .= LogManagerDataHelper::toBeautifyString($value);
      }
      $this->back_trace .= "\r\n----------------------------------------------------------------------------------------------------";
    }
  }

  private function processFullMessage() {
    $this->full_message = "----------------------------------------------------------------------------------------------------\r\n";
    if (count($this->combined_message) > 0) {
      $this->full_message .= str_pad($this->brand, 28) . " " . $this->type . " @ " . date('r') . " " . $this->main_message . "\r\n";
      foreach ($this->combined_message as $name => $message) {
        switch (gettype($message)) {
          case 'array':
            $current_message = LogManagerDataHelper::toBeautifyString($message);
            break;
          default:
            $current_message = $message;
            break;
        }
        if (!empty($current_message)) {
          $this->full_message .= "----------------------------------------------------------------------------------------------------\r\n";
          $this->full_message .= (is_string($name) ? ucwords($name) . ":\r\n" : '') . $current_message . "\r\n";
        }
      }
      $this->full_message .= "----------------------------------------------------------------------------------------------------\r\n";
      if (!empty($this->back_trace)) {
        $this->full_message .= str_pad($this->brand, 28) . "Backtrace @ " . date('r') . "\r\n";
        $this->full_message .= $this->back_trace;
      }

    }
  }


  private function processMainMessage() {
    $message = $this->main_message;
    $this->main_message = LogManagerDataHelper::maskSensitiveData($message);
  }

  ##############################################################################
  ############################### Magic Methods ################################
  ##############################################################################
  public function __get($name) {
    if (property_exists($this, $name)) {
      return $this->{$name};
    }
  }

  public function __set($name, $value) {
    if (property_exists($this, $name)) {
      return $this->{$name} = $value;
    }
  }

  public function __isset($name) {
    if (property_exists($this, $name)) {
      return isset($this->{$name});
    }
  }


}