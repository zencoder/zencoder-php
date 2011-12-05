<?php
/*

  Zencoder API PHP Library
  Version: 2.0
  See the README file for info on how to use this library.

  Based on Services_Twilio_TinyHttp from https://github.com/twilio/twilio-php/blob/master/Services/Twilio/TinyHttp.php.
  Copyright 2011, Neuman Vong. BSD License.

*/

class Services_Zencoder_HttpException extends Services_Zencoder_Exception {}

class Services_Zencoder_Http {
  protected $api_key, $scheme, $host, $debug, $curlopts;

  public function __construct($uri = '', $kwargs = array()) {
    foreach (parse_url($uri) as $name => $value) $this->$name = $value;
    $this->api_key = isset($kwargs['api_key']) ? $kwargs['api_key'] : NULL;
    $this->debug = isset($kwargs['debug']) ? !!$kwargs['debug'] : NULL;
    $this->curlopts = isset($kwargs['curlopts']) ? $kwargs['curlopts'] : array();
  }

  public function __call($name, $args) {
    list($res, $req_headers, $req_body) = $args + array(0, array(), '');

    $opts = $this->curlopts + array(
      CURLOPT_URL               => "$this->scheme://$this->host$res",
      CURLOPT_HEADER            => TRUE,
      CURLOPT_RETURNTRANSFER    => TRUE,
      CURLOPT_INFILESIZE        => -1,
      CURLOPT_POSTFIELDS        => NULL,
      CURLOPT_CONNECTTIMEOUT    => 30,
      CURLOPT_TIMEOUT           => 30,
      CURLOPT_SSL_VERIFYPEER    => 1,
      CURLOPT_SSL_VERIFYHOST    => 2
    );

    foreach ($req_headers as $k => $v) $opts[CURLOPT_HTTPHEADER][] = "$k: $v";
    if ($this->debug) $opts[CURLINFO_HEADER_OUT] = TRUE;
    if ($this->api_key) $opts[CURLOPT_HTTPHEADER][] = "Zencoder-Api-Key: $this->api_key";
    switch ($name) {
    case 'get':
      $opts[CURLOPT_HTTPGET] = TRUE;
      break;
    case 'post':
      $opts[CURLOPT_POST] = TRUE;
      $opts[CURLOPT_POSTFIELDS] = $req_body;
      break;
    case 'put':
      $opts[CURLOPT_PUT] = TRUE;
      if (strlen($req_body)) {
        if ($buf = fopen('php://memory', 'w+')) {
          fwrite($buf, $req_body);
          fseek($buf, 0);
          $opts[CURLOPT_INFILE] = $buf;
          $opts[CURLOPT_INFILESIZE] = strlen($req_body);
        } else throw new Services_Zencoder_HttpException('Unable to open memory buffer');
      } else {
        $opts[CURLOPT_INFILESIZE] = 0;
      }
      break;
    case 'delete':
      $opts[CURLOPT_CUSTOMREQUEST] = 'DELETE';
      break;
    default:
      throw new Services_Zencoder_HttpException('Invalid HTTP Method');
      break;
    }
    try {
      if ($curl = curl_init()) {
        if (curl_setopt_array($curl, $opts)) {
          if ($response = curl_exec($curl)) {
            $parts = explode("\r\n\r\n", $response, 3);
            list($head, $body) = ($parts[0] == 'HTTP/1.1 100 Continue')
              ? array($parts[1], $parts[2])
              : array($parts[0], $parts[1]);
            $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($this->debug) {
              error_log(
                curl_getinfo($curl, CURLINFO_HEADER_OUT) .
                $req_body
              );
            }
            $header_lines = explode("\r\n", $head);
            array_shift($header_lines);
            foreach ($header_lines as $line) {
              list($key, $value) = explode(":", $line, 2);
              $headers[$key] = trim($value);
            }
            curl_close($curl);
            if (isset($buf) && is_resource($buf)) fclose($buf);
            return array($status, $headers, $body);
          } else throw new Services_Zencoder_HttpException(curl_error($curl));
        } else throw new Services_Zencoder_HttpException(curl_error($curl));
      } else throw new Services_Zencoder_HttpException('Unable to initialize cURL');
    } catch (ErrorException $e) {
      if (is_resource($curl)) curl_close($curl);
      if (isset($buf) && is_resource($buf)) fclose($buf);
      throw $e;
    }
  }
}
