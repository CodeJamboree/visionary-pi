<?php
require_once "HTTP_STATUS.php";
require_once "Show.php";
require_once 'JsonWebToken.php';

function get_authorization_token()
{
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        if (preg_match('/Bearer\s+(.*)/', $authHeader, $matches)) {
            return $matches[1];
        }
    }
    return null;
}
function get_authorization_cliams()
{
    $token = get_authorization_token();
    $jwt = new JsonWebToken();
    $claims = $jwt->get_claims($token);
    return is_array($claims) ? $claims : false;
}
function authorization_token_required()
{
    $token = get_authorization_token();
    if (empty($token)) {
        Show::error('Invalid or expired token', HTTP_STATUS_UNAUTHORIZED);
        exit;
    }
    $jwt = new JsonWebToken();
    $claims = $jwt->get_claims($token);
    if (
        is_array($claims) &&
        isset($claims['aud']) &&
        $claims['aud'] === 'auth'
    ) {
        return;
    }
    Show::error('Invalid or expired token', HTTP_STATUS_UNAUTHORIZED);
    exit;
}

authorization_token_required();
