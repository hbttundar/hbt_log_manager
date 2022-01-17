<?php

namespace Drupal\hbt_log_Manager\Helper;


class LogManagerDataHelper {

  /**
   * @param string $text
   *
   * @return string
   */
  public static function maskSensitiveData(string $text): string {
    /**
     * @todo As I'm not pretty sure that shall we hide Iban in graylog like password or security code i don't
     *       change it here but i wrote code for hiding just in case but make it comment
     */
    $text = str_replace(['\u0000*\u0000', 'u0000'], '', $text);
    $patterns = [
      '/([\'|"]password[\'|\"])([:|=]\s*)(.*?)(?=,|\s|$)/i',
      '/(&password)([:|=]\s*)(.*?)(?=,|\s|$)/i',
      '/([\'|"]authorization: Basic[\'|"])([:|=]\s*)(.*?)(?=,|\s|$)/i',
      '/([\'|"]authorization: Bearer[\'|"])([:|=]\s*)(.*?)(?=,|\s|$)/i',
      '/([\'|"]access token[\'|"])([:|=]\s*)(.*?)(?=,|\s|$)/i',
      '/([\'|"]access_token[\'|"])([:|=]\s*)(.*?)(?=,|\s|$)/i',
      '/([\'|"]refresh token[\'|"])([:|=]\s*)(.*?)(?=,|\s|$)/i',
      '/([\'|"]refresh_token[\'|"])([:|=]\s*)(.*?)(?=,|\s|$)/i',
      '/([\'|"]ca_ticket)([=|:])(.*?)(?=,|;|\s|$)/i',
      #'/([\'|"]SepaDataAccountIban[\'|"])([=|:]\s*)(.*?)(?=,|$)/i',
      #'/(IBAN)([=|:]\s*)(.*?)(?=,|$)/i',
    ];
    $replacements = [
      '\1\2███████████████',
      '\1\2███████████████',
      '\1\2███████████████',
      '\1\2███████████████',
      '\1\2███████████████',
      '\1\2███████████████',
      '\1\2███████████████',
      '\1\2███████████████',
      '\1\2███████████████',
      #'\1\2███████████████',
      #'\1\2███████████████',
    ];
    return preg_replace($patterns, $replacements, $text);
  }

  public static function getSensitiveKeys(): array {
    return [
      strtolower('password') => strtolower('password'),
      strtolower('access token') => strtolower('access token'),
      strtolower('refresh token') => strtolower('refresh token'),
      strtolower('authorization: Basic') => strtolower('authorization: Basic'),
      strtolower('authorization: Bearer') => strtolower('authorization: Bearer'),
    ];
  }

  public static function toBeautifyString($data): string {
    $result = '';
    foreach ($data as $key => $value) {
      $result .= is_string($key) ? ucwords($key) . ':' : '';
      switch (gettype($value)) {
        case 'array':
          foreach ($value as $key => $message) {
            $result .= is_string($key) ? ucwords($key) . ':' : '';
            switch (gettype($message)) {
              case 'array':
                $result .= self::maskSensitiveData(json_encode($message, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) . "\r\n";
                break;
              case 'object':
                $result .= self::maskSensitiveData(json_encode((array) $message, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) . "\r\n";
                break;
              default:
                $result .= self::maskSensitiveData(trim($message)) . "\r\n";
                break;
            }
          }
          break;
        case 'object':
          $result .= self::maskSensitiveData(json_encode((array) $value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) . "\r\n";
          break;
        default:
          $result .= self::maskSensitiveData(trim($value)) . "\r\n";
      }
    }
    return $result;
  }

  public static function maskScalerData(string $text): string {
    return str_repeat('█', strlen($text));
  }

}