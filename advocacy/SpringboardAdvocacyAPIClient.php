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
   * will be used in some service method calls.
   *
   * @var string
   */
  private $api_key;

  /**
   * The oauth access token granted to the client application. This key
   * will be used in most service method calls.
   *
   * @var string
   */
  private $access_token;

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
   * The oauth client id
   *
   * @var string
   */
  private $client_id;

 /**
   * The oauth secret
   *
   * @var string
   */
  private $client_secret;

  /**
   * Constructor.
   *
   * @param string $api_key The api key to use for all requests from this instance.
   * @param string $url The url endpoint of the service.
   *
   * @return SpringboardAdvocacyAPIClient An instance of the SpringboardAdvocacyAPIClient class.
   */
  public function __construct($url, $api_key = NULL) {
    if (empty($api_key)) {
      //throw new Exception('API key is required.');
    }

    if (empty($url)) {
      throw new Exception('Service URL is required.');
    }

    $this->api_key = $api_key;
    $this->url = $url;

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
   * @return object A response object with an 'error' property containing a message 
   * or a 'data' property containing an array of Legislators objects.
   */
  public function getLegislators($zip) {
    $response = $this->doRequest( 'GET','targets/legislators', array('zip' => $zip));
    return $response;
  }

  /**
   * Public method to return all districts associated with a given zip code.
   *
   * @param string $zip A full 9-digit US zip code in the format 99999-9999.
   *
   * @return object A response object with an 'error' property containing a message 
   * or a 'data' property containing an array of districts keyed by legislative chamber.
   */
  public function getDistricts($zip) {
    $response = $this->doRequest('GET', 'districts', array('zip' => $zip));
    return $response;
  }

  /**
   * Public method to return all Targets associated with a given search query.
   *
   *
   * @param  array  $params 
   * An array containing search parameters, which may include:
   *
   * class_name
   * last_name
   * gender
   * party
   * state
   * role (legislative chamber)
   * offset
   * limit
   *
   * Multiple values for a single field should be sepatated by a pipe "|"
   *
   * Or an set of fields which can combine the above:
   * fields (whose value is comma-spearated field names of any of the above. May not implement this.)
   * values (who value is the comma-separated combined values of the combination field)
   *
   * @return obj A response object with an 'error' property containing a message 
   * or a 'data' property containing an array containing an array of Target objects keyed by 'targets'
   * and a result count keyed by 'count'.
   */
  public function searchTargets($params = NULL) {
    $response = $this->doRequest('GET', 'targets/search', $params);
    return $response;
  }

  /**
   * Public method to return all Targets with optional parameter filter.
   *
   * @param string $params A query string in the format field_name=value
   * Possible fields include: role state gender party first_name last_name email
   *
   * @return obj A response object with an 'error' property containing a message 
   * or a 'data' property containing an array of Target objects filtered by account and optional params
   */
  public function getCustomTargets($params = NULL) {
    $response = $this->doRequest('GET', 'targets/custom', $params);
    return $response;
  }

  /**
   * Public method to return a custom target.
   *
   * @param string $id The Target ID.
   *
   * @return obj A response object with an 'error' property containing a message 
   * or a 'data' property containing a Target object
   */
  public function getCustomTarget($id) {
    $response = $this->doRequest('GET', 'targets/custom/' . $id);
    return $response;
  }

  /**
   * Public method to create a custom target.
   *
   * @param array $target An array of required target field values.
   *
   * @return object A response object with an 'error' property containing a message 
   * or a 'data' property containing an array with keys/values: 
   * 'status' => array([string: target success/fail message], [string: address sucess/fail message]), 
   * 'id' => [target id];
   */
  public function createCustomTarget(array $target) {
    $this->postFields = $target;
    $response = $this->doRequest('POST', 'targets/custom');
    return $response;
  }

  /**
   * Public method to update a custom target.
   *
   * @param array $target An array of required target field values.
   * @param string $target A target ID.
   *
   * @return object A response object with an 'error' property containing a message 
   * or a 'data' property containing an array with keys/values: 
   * 'status' => array([string: target success/fail message], [string: address sucess/fail message]), 
   * 'id' => [target id];
   */

  public function updateCustomTarget(array $target, $id) {
    $this->postFields = $target;
    $response = $this->doRequest('PUT', 'targets/custom/' . $id);
    return $response;
  }

  /**
   * Public method to delete a custom target.
   *
   * @param string $target A target ID.
   *
   * @return object A response object with an 'error' property containing a message 
   * or a 'data' property containing an array with keys/values: 
   * 'status' => array([string: target success/fail message], [string: address sucess/fail message]), 
   * 'id' => [target id];
   */

  public function deleteCustomTarget($id) {
    $response = $this->doRequest('DELETE', 'targets/custom/' . $id);
    return $response;
  }

  public function getToken($client_id, $client_secret) {
    $this->postFields = array('grant_type' => 'client_credentials', 'client_id' => $client_id, 'client_secret' => $client_secret);
    $response = $this->doRequest('POST', 'oauth/access_token');
    return $response;
  }

  public function setToken($token) {
    $this->access_token = $token;
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
        'targets/search',
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
  private function doRequest($http_method, $request_path, $params = NULL) {

    $this->validHttpVerb($http_method);
    $this->validApiMethod($request_path, $http_method);

    $url = $this->buildRequestUrl($request_path, $params);
    $curl = $this->prepareCurl($url, $http_method);

    return $this->sendCurlRequest($curl);    // // Set curl options.
  }

  /**
   * Private method for setting CURL options.
   *
   * @param string $url The request url.
   * @param string $http_method The HTTP verb to use for the call.
   *
   * @return array Curl options.
   */
  private function prepareCurl($url, $http_method) {

      // Set curl options.
      $options = array(
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

      if (!empty($this->access_token)) {
        $options[CURLOPT_HTTPHEADER] = array('Authorization: Bearer ' . $this->access_token);
      }

      return $options;
  }

  /**
   * Private method for sending the Curl request.
   *
   * @param string $url The request url.
   * @param string $http_method The HTTP verb to use for the call.
   *
   * @return string JSON reprentation of service call response.
   */
  private function sendCurlRequest($curlOptions) {
    $handle = curl_init();
    curl_setopt_array($handle, $curlOptions);
    $json = curl_exec($handle);
    curl_close($handle);

    return json_decode($json);
  }

  /**
   * Private function to validate that the service method being
   * called actually exists.
   *
   * @param string $http_method The HTTP verb to use for the call.
   *
   * @return boolean True if the method exists.
   */
 
  private function validHttpVerb($http_method)
  {
    $valid_verbs = array('GET', 'POST', 'PUT', 'DELETE');
    if (!in_array($http_method, $valid_verbs)) {
        throw new Exception('Method does not exist.');
    }
    return true;
  }

  /**
   * Private function to validate that the request path being
   * called actually exists.
   *
   * @param string $request_path The name of the service method to call.
   * @param string $http_method The HTTP verb to use for the call.
   *
   * @return boolean True if the method exists.
   */
 
  private function validApiMethod($request_path, $http_method)
  {
    $methods = $this->getApiMethods();
    if(!in_array($request_path, $methods[$http_method])) {
        throw new Exception('That api endpoint does not exist.');
    }
    return true;
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