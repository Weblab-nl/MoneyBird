<?php

namespace Weblab\MoneyBird;

use Weblab\MoneyBird\Exceptions\TooManyRequestsException;
use Weblab\RESTClient\Adapters\OAuth;
use Weblab\RESTClient\RESTClient;

/**
 * Class MoneyBird - RESTClient implementation of the MoneyBird API
 * @author Weblab.nl - Eelco Verbeek
 *
 * The access token should never expire but if need arises, follow the following steps to generate one:
 *
 * First call MoneyBird::generateRequestTokenURL();
 * This will output a URL. Give this URL to a person with access to the MoneyBird administration you need the access
 * token for.
 *
 * This person will get a screen where they can give permission after logging in. After giving permission they will see
 * a request token which they will need to give this token back to you.
 *
 * With this request token you can get an access token by calling MoneyBird::generateAccessToken();
 * This will output the access en refresh tokens. Store these somewhere in your .env or config files and use them to
 * construct this class.
 *
 */
class MoneyBird extends RESTClient {
    /**
     * @var string      The base URL for the API
     */
    protected $baseURL = 'https://moneybird.com/api/v2/';

    /**
     * @var string      The client id of the MoneyBird WebLab application
     */
    protected $clientID;

    /**
     * @var string      The client secret of the MoneyBird WebLab application
     */
    protected $clientSecret;

    /**
     * @var string      The redirect URI for authorization requests.
     */
    protected $authRedirectURI;

    /**
     * @var string      The id of the administration the calls are made to
     */
    protected $administrationID;

    /**
     * @var string      The accessToken that will be used to make API calss
     */
    protected $accessToken;

    /**
     * @var string      The refreshToken which you can use to get a new accessToken when one expires
     */
    protected $refreshToken;

    /**
     * @var string      The URL to which authorization requests must be made
     */
    protected static $authURL = 'https://moneybird.com/oauth/token';

    /**
     * @var string      The URL to which a user is send to give permission
     */
    protected static $permissionURL = 'https://moneybird.com/oauth/authorize?client_id=%s&redirect_uri=%s&response_type=code&scope=%s';

    /**
     * MoneyBird constructor.
     *
     * @param   string      $clientID           The id of your MoneyBird app
     * @param   string      $clientSecret       The secret of your MoneyBird app
     * @param   int         $administrationID   Id of the administration
     * @param   string|null $accessToken        AccessToken for the passed administration id
     * @param   string|null $refreshToken       RefreshToken for the passed accessToken
     * @param   string      $authRedirectURI    URI where a user will be redirected to when giving access. (default value outputs into browser)
     */
    public function __construct(string $clientID, string $clientSecret, int $administrationID, string $accessToken = null, string $refreshToken = null, string $authRedirectURI = 'urn:ietf:wg:oauth:2.0:oob') {
        parent::__construct();

        $this->clientID         = $clientID;
        $this->clientSecret     = $clientSecret;
        $this->administrationID = $administrationID;
        $this->accessToken      = $accessToken;
        $this->refreshToken     = $refreshToken;
        $this->authRedirectURI  = $authRedirectURI;

        // Complete the base URL
        $this->baseURL .= $this->administrationID . '/';

        // Set the adapter that will handle all the requests
        $this->setAdapter($this->createAdapter());

        // Register a response handler for HTTP status too many requests
        $this->registerResponseHandler(429, 'tooManyRequestsHandler');
    }

    /**
     * Creates the RESTClient adapter
     *
     * @return OAuth
     */
    protected function createAdapter() {
        return (new OAuth)
            ->setURL(self::$authURL)
            ->setClientID($this->clientID)
            ->setSecret($this->clientSecret)
            ->setAccessToken($this->accessToken)
            ->setRefreshToken($this->refreshToken)
            ->setRedirectURI($this->authRedirectURI);
    }

    /**
     * Helper function for generating access tokens. See class comments above
     *
     * @param   string  $clientID           The id of your MoneyBird app
     * @param   array   $scopes             Add additional permissions. See: https://developer.moneybird.com/authentication/#scopes
     * @param   string  $authRedirectURI    URI where a user will be redirected to when giving access. (default value outputs into browser)
     */
    public static function generateRequestTokenURL(string $clientID, $scopes = [], string $authRedirectURI = 'urn:ietf:wg:oauth:2.0:oob') {
        // substitute the variable parameters in the url
        printf(self::$permissionURL . PHP_EOL, $clientID, $authRedirectURI, implode(' ', $scopes));
    }

    /**
     * Helper function for generating access tokens. See class comments above
     *
     * @param   string  $token              The request token in the response from generateRequestTokenURL()
     * @param   string  $clientID           The id of your MoneyBird app
     * @param   string  $clientSecret       The secret of your MoneyBird app
     * @param   string  $authRedirectURI    URI where a user will be redirected to when giving access. (default value outputs into browser)
     */
    public static function generateAccessToken(string $token, string $clientID, string $clientSecret, string $authRedirectURI = 'urn:ietf:wg:oauth:2.0:oob') {
        $adapter = (new OAuth)
            ->setURL(self::$authURL)
            ->setClientID($clientID)
            ->setSecret($clientSecret)
            ->setRedirectURI($authRedirectURI);

        // Request an access token
        $result = json_decode($adapter->processRequestToken($token)->getResult());

        // Output the access token and refresh token
        printf('Access token: ' . $result->access_token . PHP_EOL);
        printf('Refresh token: ' . $result->refresh_token . PHP_EOL);
    }

    /**
     * Response handler for the 429 HTTP status code
     *
     * @param   $response
     * @param   $type
     * @param   $url
     * @param   $params
     * @throws  TooManyRequestsException
     */
    protected function tooManyRequestsHandler($response, $type, $url, $params) {
        throw new TooManyRequestsException();
    }

    /**
     * Do a POST request to the API
     *
     * @param   string      $url
     * @param   string      $params
     * @param   array       $options
     * @param   array       $headers
     * @return  mixed
     * @throws  \Exception
     */
    public function post($url, $params, $options = [], $headers = []) {
        // Add JSON headers
        $headers['Content-Type']    = 'application/json';
        $headers['Content-Length']  = strlen($params);

        // done, return the REST-post call
        return parent::post($url, $params, $options, $headers);
    }

    /**
     * Do a update request to the API
     *
     * @param   string      $type
     * @param   string      $url
     * @param   string      $params
     * @param   array       $options
     * @param   array       $headers
     * @return  mixed
     * @throws  \Exception
     */
    public function update($type, $url, $params, $options = [], $headers = []) {
        // Add JSON headers
        $headers['Content-Type']    = 'application/json';
        $headers['Content-Length']  = strlen($params);

        // done, return the REST-update-call
        return parent::update($type, $url, $params, $options, $headers);
    }

}
