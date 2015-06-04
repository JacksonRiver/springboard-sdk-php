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
   * Flag to toggle debug mode.
   *
   * @var boolean
   */
  private $debug_mode = FALSE;

  /**
   * Array to capture debug information.
   *
   * @var array
   */
  private $debug_info = array();

  /**
   * Array to store http post data.
   */
  private $postFields = array();

  /**
   * Constructor.
   *
   * @param string $api_key The api key to use for all requests from this instance.
   * @param string $url The url endpoint of the service.
   *
   * @return SpringboardAdvocacyAPIClient An instance of the SpringboardAdvocacyAPIClient class.
   */
  public function __construct($url, $api_key = NULL, $debug_mode = FALSE) {
    if (empty($api_key)) {
      //throw new Exception('API key is required.');
    }

    if (empty($url)) {
      throw new Exception('Service URL is required.');
    }

    $this->api_key = $api_key;
    $this->url = $url;
    $this->debug_mode = $debug_mode;

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
   * Public method to enable SDK debug mode.
   */
  public function enableDebugMode() {
    $this->debug_mode = TRUE;
  }

  /**
   * Public method to disable SDK debug mode.
   */
  public function disableDebugMode() {
    $this->debug_mode = FALSE;
  }

  /**
   * Public method to indicate if the SDK is in debug mode.
   *
   * @return boolean The current debug mode state.
   */
  public function isDebugMode() {
    return $this->debug_mode;
  }

  /**
   * Public method to return the latest SDK debug info.
   *
   * @return array Structured array of debug info with the following keys:
   *         - http_method
   *         - request_path
   *         - params
   *         - post_fields
   *         - access_token
   *         - response
   */
  public function getDebugInfo() {
    return $this->debug_info;
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
   * Public method to return all districts associated with a given zip code.
   *
   * @param string $abbr A state abbreviation.
   *
   * @return object A response object with an 'error' property containing a message
   * or a 'data' property containing an array of district names q.
   */
  public function getDistrictsByState($abbr) {
    $response = $this->doRequest('GET', 'districts/state', array('state' => $abbr));
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
   * Public method to return all Target Groups associated with a given search query.
   *
   *
   * @param  array  $params
   * An array containing a search parameters, which must include:
   *
   * group-name
   *
   * and may include:
   *
   * limit
   * offset
   *
   * @return obj A response object with an 'error' property containing a message
   * or a 'data' property containing an array containing an array of Target objects keyed by 'targets'
   * and a result count keyed by 'count'.
   */
  public function searchTargetGroups($params = NULL) {
    $response = $this->doRequest('GET', 'target-groups/search', $params);
    return $response;
  }

  /**
   * Public method to return all Targets with optional parameter filter.
   *
   * @param string $params A query string in the format field_name=value
   * Possible fields include: role state gender party first_name last_name email
   *
   * @return object A response object with an 'error' property containing a message
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


  /**
   * Public method to return all Target Groups.
   *
   * @return obj A response object with an 'error' property containing a message
   * or a 'data' property containing an array of Target Group objects filtered by account and optional params
   */
  public function getTargetGroups() {
    $response = $this->doRequest('GET', 'target-groups');
    return $response;
  }

  /**
   * Public method to return a target group.
   *
   * @param string $id The Target ID.
   *
   * @return obj A response object with an 'error' property containing a message
   * or a 'data' property containing a Target Group object
   */
  public function getTargetGroup($id) {
    $response = $this->doRequest('GET', 'target-groups/group/' . $id);
    return $response;
  }

  /**
   * Public method to return a target group by message id.
   * A fail-safe in case the response to the api client fails
   * after group creation
   * and the calling application is unable to determine/save
   * the message's group ID.
   *
   * @param string $id The Target Message ID.
   *
   * @return obj A response object with an 'error' property containing a message
   * or a 'data' property containing a Target Group object
   */
  public function getTargetGroupByMessageID($message_id) {
    $response = $this->doRequest('GET', 'target-groups/message/' . $message_id);
    return $response;
  }

  /**
   * Public method to create a target group.
   *
   * @param array $targetGroup An multidimensional array of required
   * and optional target group field values.
   *
   * Structured similarly to:
   *
   *  array(
   *    'name' => 'group name', //required
   *    'chambers' => array(
   *         0 => array('chamber' => 'fs', 'party =>'r', state => 'MA'),
   *         1 => array('chamber' => 'fs', 'party =>'d', state => 'MA')
   *       )
   *    'executives' => array(
   *         0 => array('role' => 'PRES01', 'party =>'r'),
   *         1 => array('role' => 'PRES03', 'party =>'d')
   *       )
   *     )
   *     'target_ids' => array('1', '2', '3', '4', '5', '6', '7'),
   *   )
   *
   * @return object A response object with an 'error' property containing a message
   * or a 'data' property containing an array with keys/values:
   * 'status' => array([string: target group success/fail message], [string: chamber sucess/fail message]),
   * 'id' => [target group id];
   */
  public function createTargetGroup(array $targetGroup) {
    $this->postFields = $targetGroup;
    $response = $this->doRequest('POST', 'target-groups');
    return $response;
  }

  /**
   * Public method to update a target group.
   *
   * @param array $targetGroup An multidimensional array of required
   * and optional target group field values.
   *
   * Structured similarly to:
   *
   *  array(
   *    'name' => 'group name', //required
   *    'chambers' => array(
   *         0 => array('chamber' => 'fs', 'party =>'r', state => 'MA'),
   *         1 => array('chamber' => 'fs', 'party =>'d', state => 'MA')
   *       )
   *    'executives' => array(
   *         0 => array('role' => 'PRES01', 'party =>'r'),
   *         1 => array('role' => 'PRES03', 'party =>'d')
   *       )
   *     )
   *     'target_ids' => array('1', '2', '3', '4', '5', '6', '7'),
   *
   *     'message' => array(
   *       group_id =>
   *       message_id =>
   *       form_id =>
   *      ),
   *   )
   *
   *  @param string $targetGroup A target ID.
   *
   * @return object A response object with an 'error' property containing a message
   * or a 'data' property containing an array with keys/values:
   * 'status' => array([string: target group success/fail message], [string: chamber sucess/fail message]),
   * 'id' => [target id];
   */

  public function updateTargetGroup(array $targetGroup, $id) {
    $this->postFields = $targetGroup;
    $response = $this->doRequest('PUT', 'target-groups/group/' . $id);
    return $response;
  }

  /**
   * Public method to delete a target group.
   *
   * @param string $targetGroup A target ID.
   *
   * @return object A response object with an 'error' property containing a message
   * or a 'data' property containing an array with keys/values:
   * 'status' => array([string: target group success/fail message], [string: chamber sucess/fail message]),
   * 'id' => [target id];
   */

  public function deleteTargetGroup($id) {
    $response = $this->doRequest('DELETE', 'target-groups/group/' . $id);
    return $response;
  }

  /**
   * Public method to return target deliverability metrics.
   *
   * @param string $messageActionId The Message Action ID.
   *
   * @return obj A response object with an 'error' property containing a message
   * or a 'data' property containing a list of deliverability metrics
   */
  public function getTargetDeliverability($formId) {

    $response = $this->doRequest('GET', 'deliverability/action/' .$formId);
    return $response;
  }

  /**
   * Public method to return target deliverability metrics.
   *
   * @param $formId
   * @param $targetId
   * @return \obj A response object with an 'error' property containing a message
   * or a 'data' property containing a list of deliverability metrics
   *
   */
  public function getSingleTargetDeliverability($formId, $targetId) {

    $response = $this->doRequest('GET', 'deliverability/action/' .$formId.'/target/'.$targetId);
    return $response;
  }

  /**
   * Public method to resolve targets.
   *
   * @param array $ubmission An array of required contact fields
   *
   * Structured similarly to:
   *'contact' =>
   *   array(
   *    'first_name' => ''
   *    'last_name' => ''
   *    'email' => ''
   *    'address' => ''
   *    'address_2' => ''
   *    'city' => ''
   *    'state' => ''
   *    'country' => ''
   *    'zip' => a full zip+9 zip code
   *    'phone' => ''
   *  )
   *
   *  'form_id' => 'string'
   *  'test_mode' => 'string'
   *  'test_email' => 'string'
   *
   *  'messages' => array(
   *     0 => array('message_id' => '', 'subject =>'', body => '', 'weight => '', 'precedence' => true/false),
   *     1 => array('message_id' => '', 'subject' =>'', body => '', 'weight => '',  'precedence' => true/false)
   *   )
   *
   *  @param string $formId A formId ID.
   *
   * @return object A response object with an 'error' property containing a message
   * or a 'data' property containing an array:
   * 'status' => success,
   */
  public function resolveTargets($submission) {
    $this->postFields =  $submission;
    $response = $this->doRequest('POST', 'targets/resolve');
    return $response;
  }

  /**
   * Public method to return metrics for a given period.
   *
   * @param string $period
   *
   * @return object A response object with an 'error' property containing a message
   * or a 'data' property containing an array of metrics values.
   */
  public function getMetrics($period) {
    $response = $this->doRequest( 'GET','metrics/'.$period);
    return $response;
  }

  /**
   * Public method to return subscription information for an API account.
   *
   * @return object A response object with a 'subscription_type' property containing a string that
   * represents the account subscription type. If the subscription type is 'states-selected' then
   * a 'states_custom' array will also be returned containing all applicable states.
   */
  public function getSubscription() {
    $response = $this->doRequest('GET', 'subscription');
    return $response;
  }

  /**
   * Retrieve a list of committees
   * @return string
   */
  public function getCommitteeList() {
    $response = $this->doRequest('GET', 'committees/list');
    return $response;
  }

  /**
   * Public method to return an Oauth token.
   *
   * @return object A response object with an 'access_token' property containing a fresh access token for the API account
   * and 'token_type' and 'expires_in'properties.
   */

  public function getToken($client_id, $client_secret) {
    $this->postFields = array('grant_type' => 'client_credentials', 'client_id' => $client_id, 'client_secret' => $client_secret);
    $response = $this->doRequest('POST', 'oauth/access-token');

    return $response;
  }

  /**
   * Public method to set the Oauth token as the access credentials for this API user.
   *
   * @return void
   */
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
        'districts/state',
        'targets',
        'targets/custom',
        'targets/search',
        'target-groups',
        'target-groups/group',
        'target-groups/search',
        'target-groups/message',
        'deliverability/action',
        'metrics/daily',
        'metrics/weekly',
        'metrics/monthly',
        'metrics/lifetime',
        'metrics/failed',
        'subscription',
        'committees/list',
      ),
      'POST' => array(
        'targets/custom',
        'targets/resolve',
        'oauth/access-token',
        'target-groups',

      ),
      'PUT' => array(
        'targets/custom',
        'target-groups/group',
      ),
      'DELETE' => array(
        'targets/custom',
        'target-groups/group',
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

    // Send off the request.
    $response = $this->sendCurlRequest($curl);

    // Stash some debug information if debug mode is on.
    if ($this->isDebugMode()) {
      $this->addDebugInfo('http_method', $http_method);
      $this->addDebugInfo('request_path', $request_path);
      $this->addDebugInfo('access_token', $this->access_token);
      $this->addDebugInfo('params', $params);
      $this->addDebugInfo('post_fields', $this->postFields);
      $this->addDebugInfo('response', $response);
    }

    return $response;
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

      if (!empty($this->postFields) &&  ($http_method == "PUT" || $http_method == "POST")) {
        $options[CURLOPT_POSTFIELDS] = http_build_query($this->postFields);
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
    $response = json_decode($json);

    if (!empty($response)) {
      return $response;
    }
    else {
      return 'Did not receive a JSON response';
    }
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

    // remove target ids from request path for validation.
    if (count($paths = explode('/', $request_path)) >=3) {
      // unset($paths[2]);

      foreach($paths as $key =>$val) {
        if($key>=2) {
          unset($paths[$key]);
        }
      }

      $request_path = implode('/', $paths);
    }

    if(!in_array($request_path, $methods[$http_method])) {
      throw new Exception('That api endpoint does not exist. ' .  $request_path);
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

  /**
   * Private method to add debug info to a structure array.
   *
   * @param string $key The under which to store the debug value.
   * @param mixed $value The debug info's value.
   */
  private function addDebugInfo($key, $value) {
    $this->debug_info[$key] = $value;
  }

}
