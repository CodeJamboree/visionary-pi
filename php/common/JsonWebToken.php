<?php
class JsonWebToken
{
    private $secret_key;
    private $config = [
        'iss' => null,
        'aud' => null,
        'sub' => null,
        'ttl' => null,
        'delay' => null,
        'jti' => false,
        'iat' => false,
    ];

    public function __construct(
        #[SensitiveParameter] string $secret_key = null,
        array $config = null
    ) {
        if (!empty($config)) {
            $this->configure($config);
        }
        if (!empty($secret_key)) {
            $this->secret_key = $secret_key;
            return;
        }
        $base64 = getenv('JWT_KEY');
        if (empty($base64)) {
            throw new Exception("JWT Secret not available", 500);
        }
        $secret_key = base64_decode($base64);
        if ($secret_key === false) {
            throw new Exception("JWT Secret base64 decoding failed", 500);
        }
        $this->secret_key = $secret_key;
    }
    public function build(
        array $claims = null
    ) {
        $header = self::json_base64_url_encode(
            ['alg' => 'HS256', 'typ' => 'JWT']
        );

        $timestamp = time();
        $payload = [];

        if ($this->config['iat'] !== false) {
            $payload['iat'] = $timestamp;
        }

        if ($this->config['jti']) {
            $payload['jti'] = uniqid();
        }

        if ($this->config['delay'] !== null) {
            $payload['nbf'] = $timestamp + $this->config['delay'];
        }
        if ($this->config['ttl'] !== null) {
            $payload['exp'] = $timestamp + $this->config['ttl'];
        }
        foreach (['iss', 'aud', 'sub'] as $name) {
            if ($this->config[$name] !== null) {
                $payload[$name] = $this->config[$name];
            }
        }

        if (is_array($claims)) {
            foreach ($claims as $claim => $value) {
                $payload[$claim] = $value;
            }
        }

        if (count($payload) === 0) {
            return false;
        }

        $payload = self::json_base64_url_encode($payload);

        $signature = hash_hmac(
            "sha256",
            "$header.$payload",
            $this->secret_key,
            true
        );
        $signature = self::base64_url_encode($signature);

        $token = "$header.$payload.$signature";
        return $token;
    }
    public function get_claims(string $token)
    {
        if (
            preg_match(
                "/^(?<header>.+)\.(?<payload>.+)\.(?<signature>.+)$/",
                $token,
                $matches
            ) !== 1
        ) {
            return false;
        }

        $header_encoded = $matches['header'];
        $payload_encoded = $matches['payload'];
        $signature_encoded = $matches['signature'];

        $header = self::json_base64_url_decode($header_encoded);
        if (!is_array($header)) {
            return false;
        }

        if (isset($header['typ']) && strtoupper($header['typ']) !== 'JWT') {
            return false;
        }

        $signature = self::base64_url_decode($signature_encoded);

        switch ($header['alg']) {
            case 'none':return true;
            case 'HS256':break;
            default:return false;
        }

        $expected_signature = hash_hmac(
            "sha256",
            "$header_encoded.$payload_encoded",
            $this->secret_key,
            true
        );

        if (!hash_equals($expected_signature, $signature)) {
            return false;
        }

        $claims = self::json_base64_url_decode($payload_encoded);

        if (!is_array($claims)) {
            return false;
        }
        if (count($claims) === 0) {
            return false;
        }

        if (isset($claims['nbf']) && (time() < $claims['nbf'])) {
            return false;
        }

        if (isset($claims['exp']) && (time() > $claims['exp'])) {
            return false;
        }

        return $claims;
    }
    public function configure(array $options)
    {
        foreach ($options as $option => $value) {
            $this->config[$option] = $value;
        }
    }
    public function get_config()
    {
        return $this->config;
    }
    private static function json_base64_url_encode($data)
    {
        return self::base64_url_encode(
            json_encode($data)
        );
    }
    private static function json_base64_url_decode($data)
    {
        return json_decode(
            self::base64_url_decode($data),
            true
        );
    }
    private static function base64_url_encode(string $data)
    {
        return str_replace(
            ['+', '/', '='],
            ['-', '_', ''],
            base64_encode($data)
        );
    }
    private static function base64_url_decode(string $data)
    {
        $base64 = str_replace(
            ['-', '_'],
            ['+', '/'],
            $data
        );

        $padding = 4 - (strlen($base64) % 4);
        if ($padding !== 0) {
            $base64 .= str_repeat('=', $padding);
        }

        return base64_decode($base64);
    }
}
