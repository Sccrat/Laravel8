<?php

namespace App\Common;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;


/**
 * Custom class for confoguration settings stored in the table wms_settings
 */
class SoberanaServices
{
  public static function PostReceive($objet, $token)
  {
    $url = env("API_SAYA_URL", "http://190.85.144.50:8080/artmode/");

    $client = new Client([
      'base_uri' => "$url"
    ]);

    $response = $client->request('POST', 'saveOrder/', [
      'json' => $objet,
      'headers' => ['token' => $token],
      'http_errors' => false
    ]);

    return $response->getBody();
  }

  public static function PostReceivePlus($objet, $token)
  {
    $client = new Client([
      'base_uri' => 'http://190.85.144.50:8080/artmode/apiV1/InterfacePtolomeo/'
    ]);

    $response = $client->request('POST', 'saveProductionOrder/', [
      'json' => $objet,
      'headers' => ['token' => $token],
      'http_errors' => false
    ]);

    return $response->getBody();
  }

  public static function PostReceiveBatch($objet)
  {

    $url = env("API_SAYA_URL", "http://190.85.144.50:8080/artmode/");

    $client = new Client([
      'base_uri' => "$url"
    ]);

    $response = $client->request('POST', 'apiV1/', [
      'headers' => ['Content-Type' => 'application/json'],
      'json' => $objet,
      'http_errors' => false
    ]);
    return  $response->getBody()->getContents();
  }

  public static function saveOrder($objet, $token, $companyId)
  {
    $settingsObj = new Settings($companyId);
    $url = $settingsObj->get('service_url_save_order');

    $client = new Client([
      'base_uri' => "$url"
    ]);

    $response = $client->request('POST', 'saveOrder/', [
      'json' => $objet,
      'headers' => ['token' => $token],
      'http_errors' => false
    ]);

    return $response->getBody();
  }

  public static function cancelDocument($objet, $token, $companyId)
  {
    $settingsObj = new Settings($companyId);
    $url = $settingsObj->get('service_url_cancel_document');

    $client = new Client([
      'base_uri' => $url
    ]);

    $response = $client->request('POST', 'cancelOrder/', [
      'json' => $objet,
      'headers' => ['token' => $token],
      'http_errors' => false
    ]);

    return $response->getBody();
  }

  public static function getToken($objet, $companyId)
  {
    $settingsObj = new Settings($companyId);
    $url = $settingsObj->get('service_url_token');

    $client = new Client([
      'base_uri' => "$url"
    ]);

    $response = $client->request('POST', 'apiV1/', [
      'headers' => ['Content-Type' => 'application/json'],
      'json' => $objet,
      'http_errors' => false
    ]);
    return  $response->getBody()->getContents();
  }

  public static function getUUID()
  {
    $client = new Client([
      'base_uri' => "https://www.uuidgenerator.net/api/version1"
    ]);

    $response = $client->request('GET', '', [
      'headers' => ['Content-Type' => 'application/json'],
      'http_errors' => false
    ]);
    return  $response->getBody()->getContents();
  }
}
