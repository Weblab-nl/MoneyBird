<?php

namespace Weblab\MoneyBird;

use Weblab\MoneyBird\Exceptions\TooManyRequestsException;
use Weblab\RESTClient\Adapters\OAuth;
use Weblab\RESTClient\RESTClient;

/**
 * Class MoneyBird - RESTClient implementation of the MoneyBird API
 * @author Weblab.nl - Eelco Verbeek
 *
 * This class is does api calls for the WebLab application: https://moneybird.com/user/applications/216125731388786642
 *
 * The access token should never expire but if need arises, follow the following steps to generate one:
 *
 * First call MoneyBird::generateRequestTokenURL(string[] scopes);
 * This will output a URL. Give this URL to a person with access to the MoneyBird administration you need the access
 * token for.
 *
 * They will get a screen where they can give access after logging in. After giving access they will see a request token.
 *
 * With this request token you can get an access token by calling MoneyBird::generateAccessToken(string requestToken);
 * This will output the access en refresh tokens. Add those to config.services.moneybird together with the
 * administration_id and you're good to go.
 *
 */
class MoneyBird extends RESTClient {

    /**
     * @var MoneyBird   Stores the instance for singleton pattern
     */
    private static $instance;

    /**
     * @var string      The client id of the MoneyBird WebLab application
     */
    protected static $clientID;

    /**
     * @var string      The client secret of the MoneyBird WebLab application
     */
    protected static $clientSecret;

    /**
     * @var string      The redirect URI for authorization requests.
     */
    protected static $authRedirectURI;

    /**
     * @var string      The id of the administration the calls are made to
     */
    protected static $administrationID;

    /**
     * @var string      The accessToken that will be used to make API calss
     */
    protected static $accessToken;

    /**
     * @var string      The refreshToken which you can use to get a new accessToken when one expires
     */
    protected static $refreshToken;

    /**
     * @var string      The URL to which authorization requests must be made
     */
    protected static $authURL = 'https://moneybird.com/oauth/token';

    /**
     * @var string      The URL to which a user is send to give permission
     */
    protected static $permissionURL = 'https://moneybird.com/oauth/authorize?client_id=%s&redirect_uri=%s&response_type=code&scope=%s';

    /**
     * @var string      The base URL for the API
     */
    protected $baseURL = 'https://moneybird.com/api/v2/';

    /**
     * Setup the authorization for the MoneyBird API
     *
     * @param   string      $clientID           The id of your MoneyBird app
     * @param   string      $clientSecret       The secret of your MoneyBird app
     * @param   int         $administrationID   Id of the administration
     * @param   string|null $accessToken        AccessToken for the passed administration id
     * @param   string|null $refreshToken       RefreshToken for the passed accessToken
     * @param   string      $authRedirectURI    URI where a user will be redirected to when giving access. (default value outputs into browser)
     */
    public static function setup(string $clientID, string $clientSecret, integer $administrationID, string $accessToken = null, string $refreshToken = null, string $authRedirectURI = 'urn:ietf:wg:oauth:2.0:oob') {
        self::$clientID         = $clientID;
        self::$clientSecret     = $clientSecret;
        self::$administrationID = $administrationID;
        self::$accessToken      = $accessToken;
        self::$refreshToken     = $refreshToken;
        self::$authRedirectURI  = $authRedirectURI;
    }

    /**
     * MoneyBird constructor.
     */
    public function __construct() {
        parent::__construct();

        // Complete the base URL
        $this->baseURL .= self::$administrationID . '/';

        // Set the adapter that will handle all the requests
        $this->setAdapter($this->createAdapter());

        // Register a response handler for HTTP status too many requests
        $this->registerResponseHandler(429, 'tooManyRequestsHandler');
    }

    /**
     * getInstance method for singleton pattern
     *
     * @return  MoneyBird
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * Creates the RESTClient adapter
     *
     * @return OAuth
     */
    protected function createAdapter() {
        return (new OAuth)
            ->setURL(self::$authURL)
            ->setClientID(self::$clientID)
            ->setSecret(self::$clientSecret)
            ->setAccessToken(self::$accessToken)
            ->setRefreshToken(self::$refreshToken)
            ->setRedirectURI(self::$authRedirectURI);
    }

    /**
     * Helper function for generating access tokens. See class comments above
     *
     * @param   array   $scopes
     */
    public static function generateRequestTokenURL($scopes = []) {
        // substitute the variable parameters in the url
        printf(self::$permissionURL . PHP_EOL, self::$clientID, self::$authRedirectURI, implode(' ', $scopes));
    }

    /**
     * Helper function for generating access tokens. See class comments above
     *
     * @param   string  $token
     */
    public static function generateAccessToken($token) {
        $adapter = (new OAuth)
            ->setURL(self::$authURL)
            ->setClientID(self::$clientID)
            ->setSecret(self::$clientSecret)
            ->setRedirectURI(self::$authRedirectURI);

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
