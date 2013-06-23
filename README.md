Rang API PHP Library
================

Usage
-----------
To load the library, include the following code

<pre>
require('rang.php');
$rang = new Rang('client_secret');
</pre>

##Quickstart

###GET gift_token
<pre>
$reference = 'xxxxxx' // Unique identifier for user
$params = array();
$with_offers = FALSE;

$gift_token = $rang->gift_token($reference, $params, $with_offers);
print_r($gift_token);
</pre>

###POST send\_rewards\_emails

####Single Email
<pre>
$email = 'xxxxxx';
$params = array();

$email_response = $rang->send_rewards_emails($email, $params);
</pre>

####Multiple emails
<pre>
$emails = array('xxxxxxx', 'yyyyyy');
$params_array = array(
  'xxxxxxx' => array(),
  'yyyyyy' => array()
);

$email_response = $rang->send_rewards_emails($emails, $params_array);
</pre>

###POST claim_offer
<pre>
$reference = 'xxxxxx' // Unique identifier for user
$params = array();
$with_offers = FALSE;

$gift_token = $rang->gift_token($reference, $params, $with_offers);

$offer = $gift_token->offers[0];

$claim_response = $rang->claim_offer($gift_token->token, $offer->id);
print_r($claim_response);
</pre>


##Full Documentation
http://developers.rang.com
