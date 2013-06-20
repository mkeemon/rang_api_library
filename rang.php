<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Rang API Library
 *
 * Handles interactions with the Rang API
 *
 * @category  Library
 * @version   0.1
 * @author    Matthew Keemon
 * @link      http://developers.rang.com/
*/

define('HTTP_GET_SUCCESS', 200);
define('HTTP_POST_SUCCESS', 201);

class Rang {


  public function __construct()
  {
    $this->_CI =& get_instance();
    $this->_CI->load->config('rang');
    $this->_CI->load->helper("url");

    $this->_client_secret       = $this->_CI->config->item('rang_secret');
    $this->_response_extension  = $this->_CI->config->item('rang_response_extension');
    $this->_base_uri            = "https://rang.com/wl";
  }


  /*
  |--------------------------------------------------------------------------
  | Whitelabel API
  |--------------------------------------------------------------------------
  | + get_gift_token
  |
  */

  /**
   * Returns a unique url for redeeming a gift
   *
   * @param   string reference Unique Reference ID
   * @param   array params Optional parameters to target user (gender, age, location, income)
   * @return  array Information about the token -- see API documentation
   *
   */

  public function get_gift_token($reference, array $params=array(), $full_api=FALSE)
  {
    $params["reference"]   = $reference;
    $params["api_token"]   = $this->_client_secret;
    $params['with_offers'] = $full_api;

    $base_uri = "$this->_base_uri/issue_token.$this->_response_extension";
    $request_uri = $this->_build_uri($base_uri, $params);

    $json_response = $this->_curl_call('get', $request_uri);
    return json_decode($json_response);
  }


  /*
  |--------------------------------------------------------------------------
  | Full API
  |--------------------------------------------------------------------------
  | + Claim Offer
  |
  */

  /**
   * Claim an offer
   *
   * @param   string Unique token generated
   * @param   string Offer ID
   * @return  array  Response from the server
   *
   */

  public function claim_offer($token, $offer_id)
  {

    $base_uri = "$this->_base_uri.$this->_response_extension";
    $query_params = array(
      'api_token' => $this->_client_secret,
      'token'     => $token,
      'offer_id'  => $offer_id,
    );

    $request_uri = $this->_build_uri($base_uri, $query_params);
    echo $request_uri;
    $response = $this->_curl_call('post', $request_uri);
    return json_encode($response);
  }

  /*
  |--------------------------------------------------------------------------
  | Email API
  |--------------------------------------------------------------------------
  | + send_rewards_emails
  |
  */

  /**
   * Send a rewards email to one email address
   *
   * @param   array emails List of emails to be sent deals
   * @param   array params Array indexed by email addresses containing arrays of optional
   *                       parameters to target user (gender, age, location, deliver_email)
   * @return  array Information regarding the email
   *
   */

  public function send_rewards_emails($emails, array $params=array())
  {
    $query_params = array('api_token' => $this->_client_secret);

    $data = $this->_prepare_email_data($emails, $params);

    $base_uri = "$this->_base_uri/issue_emails.this->_response_extension";
    $request_uri = $this->_build_uri($base_uri, $query_params);

    $json_response = $this->_curl_call('post', $request_uri, $data );
    return json_decode($json_response);
  }

  /*
  |--------------------------------------------------------------------------
  | Helper Function
  |--------------------------------------------------------------------------
  | + _curl_call
  | + _build_uri
  | + _prepare_email_data
  |
  */

  /**
   * Call a url using cURL
   *
   * @param   string method HTTP method to access server
   * @param   string url URL to be called
   * @param   array params Parameters to be passed in for POST calls
   * @return  json JSON response from server
   *
   */

  private function _curl_call($method='get', $url, array $params=array())
  {
    $ch = curl_init($url);

    if($method == "post")
    {
      $json_params = json_encode($params);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
      curl_setopt($ch, CURLOPT_POSTFIELDS, $json_params);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($json_params))
      );
    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $response = curl_exec ($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close ($ch);

    echo $http_status;


    $success = ($method == 'get' && $http_status == HTTP_GET_SUCCESS) ||
               ($method == 'post' && $http_status == HTTP_POST_SUCCESS);

    // Response is successful
    if($success)
    {
      return $response;
    }
    // Response is not successful, throw error
    else
    {
      $decoded_response = json_decode( $response );
      throw new RangException( $decoded_response->error, $http_status );
    }

  }

  /**
   * Builds an http query string.
   *
   * @param string base_uri Base URI to which the query string is appended
   * @param array params Array of key/value queries
   * @return string HTTP encoded uri
   */

  private function _build_uri( $base_uri, array $params )
  {
    $query_str = http_build_query($params);
    return "$base_uri?$query_str";
  }


  /**
   * Prepare email batches to be sent
   *
   * @param   array emails List of emails to be sent deals
   * @param   array params Array indexed by email addresses containing arrays of optional
   *                       parameters to target user (gender, age, location, deliver_email)
   * @return  array Array of emails and parameters to be sent out to users
   *
   */

  public function _prepare_email_data($emails, $params)
  {
    $data = array();
    $reference = array();
    foreach($emails as $email)
    {
      $entry = array();
      $entry['reference'] = $email;
      if(isset($params[$email]))
      {
        $entry = array_merge($entry, $params[$email]);
      }
      $references[] = $entry;
    }
    $data['references'] = $references;
    echo json_encode($data);
    return $data;
  }


}

class RangException extends Exception
{
  public function __construct($response, $status)
  {
    parent::__construct($response, $status);
  }


}