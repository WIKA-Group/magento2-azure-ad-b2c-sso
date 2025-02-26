<?php

namespace WikaGroup\AzureB2cSSO\Model;

use Exception;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Laminas\Http\Client;
use League\OAuth2\Client\Provider\GenericProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use RuntimeException;
use UnexpectedValueException;
use WikaGroup\AzureB2cSSO\Helper\Settings;

class AzureB2cProvider extends GenericProvider
{
    public function __construct(
        protected ScopeConfigInterface $scopeConfig,
        protected Settings $settings,
    ) {
        parent::__construct([
            'clientId' => $this->settings->getClientId(),
            'clientSecret' => $this->settings->getClientSecret(),
            'redirectUri' => $this->settings->getRedirectUri(),
            'urlAuthorize' => $this->settings->getBaseUrl() . '/oauth2/v2.0/authorize',
            'urlAccessToken' => $this->settings->getBaseUrl() . '/oauth2/v2.0/token',
            'urlResourceOwnerDetails' => 'https://graph.microsoft.com/v1.0/me',
            'scopes' => 'openid',
        ]);
    }

    /** Source: https://github.com/SocialiteProviders/AzureADB2C/blob/master/Provider.php */
    public function getLogoutUri(string $redirectUri): string
    {
        return $this->getOpenIdConfiguration()->end_session_endpoint . '?logout&post_logout_redirect_uri=' . urlencode($redirectUri);
    }

    /** Get user information */
    public function getUserInfo(string $code): array
    {
        $accessToken = $this->getAccessTokenResponse($code);
        $claims = $this->validateIdToken($accessToken['id_token']);
        $claims['oauthId'] = $claims['sub'];
        $claims['email'] = $claims['email'] ?? $claims['emails'][0];
        return $claims;
    }

    /** Requests an access token using a specified grant and option set */
    private function getAccessTokenResponse(string $code): array
    {
        $grant = $this->verifyGrant('authorization_code');

        $params = [
            'client_id'     => $this->settings->getClientId(),
            'client_secret' => $this->settings->getClientSecret(),
            'redirect_uri'  => $this->settings->getRedirectUri(),
        ];

        $params   = $grant->prepareRequestParameters($params, ['code' => $code]);
        $request  = $this->getAccessTokenRequest($params);
        $response = $this->getParsedResponse($request);
        if (false === is_array($response)) {
            throw new UnexpectedValueException(
                'Invalid response received from Authorization Server. Expected JSON.'
            );
        }
        return $response;
    }

    /**
     * Get OpenID Configuration
     *
     * Source: https://github.com/SocialiteProviders/AzureADB2C/blob/master/Provider.php
     */
    private function getOpenIdConfiguration()
    {
        try {
            $client = new Client();
            $client->setUri($this->settings->getBaseUrl() .'/v2.0/.well-known/openid-configuration');
            $client->setMethod('GET');
            $response = $client->send();
        } catch (Exception $ex) {
            throw new RuntimeException("Error on getting OpenID Configuration. {$ex}");
        }

        return json_decode((string)$response->getBody());
    }

    /**
     * Get public keys to verify id_token from jwks_uri
     *
     * Source: https://github.com/SocialiteProviders/AzureADB2C/blob/master/Provider.php
     */
    private function getJWTKeys(string $jwks_uri): array
    {
        $client = new Client();
        $client->setUri($jwks_uri);
        $client->setMethod('GET');
        $response = $client->send();

        return json_decode((string)$response->getBody(), true);
    }

    /**
     * validate id_token
     * - signature validation using firebase/jwt library.
     * - claims validation
     *   iss: MUST much iss = issuer value on metadata.
     *   aud: MUST include client_id for this client.
     *   exp: MUST time() < exp.
     *
     * Source: https://github.com/SocialiteProviders/AzureADB2C/blob/master/Provider.php
     */
    private function validateIdToken(string $idToken): array
    {
        try {
            $openIdConfig = $this->getOpenIdConfiguration();

            // payload validation
            $payload = explode('.', $idToken);
            $payloadJson = json_decode(base64_decode(str_pad(strtr($payload[1], '-_', '+/'), strlen($payload[1]) % 4, '=', STR_PAD_RIGHT)), true);

            // iss validation
            if (strcmp($payloadJson['iss'], $openIdConfig->issuer)) {
                throw new RuntimeException('iss on id_token does not match issuer value on the OpenID configuration');
            }
            // aud validation
            if (strpos($payloadJson['aud'], $this->settings->getClientId()) === false) {
                throw new RuntimeException('aud on id_token does not match the client_id for this application');
            }
            // exp validation
            if ((int) $payloadJson['exp'] < time()) {
                throw new RuntimeException('id_token is expired');
            }

            JWT::$leeway = $this->settings->getLeewayTime();

            // signature validation and return claims
            return (array) JWT::decode($idToken, JWK::parseKeySet($this->getJWTKeys($openIdConfig->jwks_uri), $this->settings->getDefaultAlgorithm()));
        } catch (Exception $ex) {
            throw new RuntimeException("Error on validationg id_token. {$ex}");
        }
    }
}
