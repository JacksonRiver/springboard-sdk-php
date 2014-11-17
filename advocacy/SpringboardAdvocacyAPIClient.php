<?php
/**
 * Springboard Advocacy Services API : Simple PHP wrapper for v1 of Springboard Advocacy API
 *
 *
 * @category Advocacy
 * @package  Springboard-Advocacy-API-PHP
 * @author   Phillip Cave <phillip.cave@jacksonriver.com>
 * @license  MIT License
 * @link     https://github.com/JacksonRiver/springboard-sdk-php/advocacy
 */
class SpringboardAdvocacyAPIClient
{
  const USER_AGENT = 'springboard-sdk-php/1.0';

  /**
   * The api key granted to the client application. This key
   * will be used in all service method calls.
   *
   * @var string
   */
  private $api_key;

  /**
   * The service url the client will connect to.
   *
   * @var string
   */
  private $url;

  /**
   * The version of the api client.
   *
   * @var string
   */
  private $version = '1.0';

  /**
   * The path prefix to use in api versioning.
   *
   * @var string
   */
  private $version_prefix = 'api/v1';

  /**
   * Constructor.
   *
   * @param string $api_key The api key to use for all requests from this instance.
   * @param string $url The url endpoint of the service.
   *
   * @return SpringboardAdvocacyAPIClient An instance of the SpringboardAdvocacyAPIClient class.
   */
  public function __construct($url, $access_token, $api_key) {

    if (empty($api_key)) {
      //throw new Exception('API key is required.');
    }

    if (empty($url)) {
      throw new Exception('Service URL is required.');
    }

    $this->api_key = $api_key;
    $this->url = $url;
    $this->access_token = $access_token;

    return $this;
  }

  /**
   * Public method to return the version of the API.
   *
   * @return string The current API version.
   */
  public function getVersion() {
    return $this->version;
  }

  /**
   * Public method to return all legislators that represent a given zip code.
   *
   * @param string $zip A full 9-digit US zip code in the format 99999-9999.
   *
   * @return array An array of Legislators objects.
   */
  public function getLegislators($zip) {
    $response = $this->doRequest('targets/legislators', array('zip' => $zip), 'GET');
    return json_decode($response);
  }

  public function getDistricts($zip) {
    $response = $this->doRequest('districts', array('zip' => $zip), 'GET');
    return json_decode($response);
  }

  public function getCustomTargets($access_token) {
    $response = $this->doRequest('targets/custom', NULL, 'GET');
    return json_decode($response);
  }

  public function getCustomTarget($id) {
    $response = $this->doRequest('targets/custom/' . $id, NULL, 'GET');
    return json_decode($response);
  }

  public function createCustomTarget(array $target) {
    $this->postFields = $target;
    $response = $this->doRequest('targets/custom', NULL, 'POST');
    return json_decode($response);
  }

  public function updateCustomTarget(array $target, $id) {
    $this->postFields = $target;
    $response = $this->doRequest('targets/custom/' . $id, NULL, 'PUT');
    return json_decode($response);
  }

  public function deleteCustomTarget($id) {
    $response = $this->doRequest('targets/custom/' . $id, NULL, 'DELETE');
    return json_decode($response);
  }

  public function getToken($client_id, $client_secret) {
    $this->postFields = array('grant_type' => 'client_credentials', 'client_id' => $client_id, 'client_secret' => $client_secret);
    $response = $this->doRequest('oauth/access_token', NULL, 'POST', NULL);
    return json_decode($response);
  }


  /**
   * Method to describe the available service methods.
   */
  public function getApiMethods() {
    static $http_methods = array(
      'GET' => array(
        'targets/legislators',
        'districts',
        'targets',
        'targets/custom',
      ),
      'POST' => array(
        'targets/custom',
        'oauth/access_token',
      ),
      'PUT' => array(
        'targets/custom',
      ),
      'DELETE' => array(
        'targets/custom',
      ),
    );

    return $http_methods;
  }

  /**
   * Private method for making the actual call to the API.
   *
   * @param string method The name of the service method to call.
   * @param string $params The parameters required by the service method.
   * @param string $http_method The HTTP verb to use for the call.
   *
   * @return string JSON reprentation of service call response.
   */
  private function doRequest($request_path, $params, $http_method) {

    // Validate the request to prevent calling bogus endpoints.
    if (!$this->validRequest($request_path, $http_method)) {
      throw new Exception('Method does not exist.');
    }

    // Build ot the url to the service endpoint.
    $url = $this->buildRequestUrl($request_path, $params);

    if (!empty($this->access_token)) {
      $header = array('Authorization: Bearer ' . $this->access_token);
    }
    else {
      $header = array();
    }

    // Set curl options.
    $options = array(
      CURLOPT_HTTPHEADER => $header,
      CURLOPT_USERAGENT => self::USER_AGENT,
      CURLOPT_HEADER => false,
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_TIMEOUT => 10,
    );

    if (!empty($this->postFields) &&  $http_method == "PUT") {
      $options[CURLOPT_POSTFIELDS] = http_build_query($this->postFields);
    }

    elseif(!empty($this->postFields) &&  $http_method == "POST") {
      $options[CURLOPT_POSTFIELDS] = $this->postFields;
    }

    if ($http_method == "DELETE" || $http_method == "PUT") {
      $options[CURLOPT_CUSTOMREQUEST] = $http_method;
    }
    $handle = curl_init();
    curl_setopt_array($handle, $options);
    $json = curl_exec($handle);
    curl_close($handle);
    return $json;
  }

  /**
   * Private function to validate that the service method being
   * called actually exists.
   *
   * @param string $request_path The name of the service method to call.
   * @param string $http_method The HTTP verb to use for the call.
   *
   * @return boolean True if the method exists, false if not.
   */
  private function validRequest($request_path, $http_method) {
    $valid_verbs = array('GET', 'POST', 'PUT', 'DELETE');
    if (!in_array($http_method, $valid_verbs)) {
      return FALSE;
    }

    $methods = $this->getApiMethods();
    if (count($paths = explode('/', $request_path)) == 3) {
       unset($paths[2]);
       $request_path = implode('/', $paths);
    }

    return in_array($request_path, $methods[$http_method]);
  }

  /**
   * Private method to generate the path to a service method endpoint.
   *
   * @param string $method
   * @param string $params
   *
   * @return string Path to service endpoint with api key and query string params.
   */
  private function buildRequestUrl($request_path, $params) {
    // Start with the basic service endpoint in the format
    // of url/version/request_path?apikey=.
    $url = sprintf('%s/%s/%s?apikey=%s',
      $this->url,
      $this->version_prefix,
      $request_path,
      rawurlencode($this->api_key)
    );

    // Add query params if available.
    if (!empty($params)) {
      foreach ($params as $key => $value) {
        $query[] = rawurlencode($key) . '=' . rawurlencode($value);
      }
      $url .= '&' . implode('&', $query);
    }

    return $url;
  }

}