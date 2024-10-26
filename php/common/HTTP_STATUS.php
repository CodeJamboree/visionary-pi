<?php
// Information
define('HTTP_STATUS_CONTINUE', 100);
define('HTTP_STATUS_SWITCHING_PROTOCOLS', 101);
define('HTTP_STATUS_PROCESSING', 102);
define('HTTP_STATUS_EARLY_HINTS', 103);

// Success
define('HTTP_STATUS_OK', 200);
define('HTTP_STATUS_CREATED', 201);
define('HTTP_STATUS_ACCEPTED', 202);
define('HTTP_STATUS_NON_AUTHORITATIVE_INFORMATION', 203);
define('HTTP_STATUS_NO_CONTENT', 204);
define('HTTP_STATUS_RESET_CONTENT', 205);
define('HTTP_STATUS_PARTIAL_CONTENT', 206);
define('HTTP_STATUS_MULTI_STATUS', 207);
define('HTTP_STATUS_ALREADY_REPORTED', 208);
define('HTTP_STATUS_IM_USED', 226);

// Redirectional
define('HTTP_STATUS_MULTIPLE_CHOICES', 300);
define('HTTP_STATUS_MOVED_PERMANENTLY', 301);
define('HTTP_STATUS_FOUND', 302);
define('HTTP_STATUS_SEE_OTHER', 303);
define('HTTP_STATUS_NOT_MODIFIED', 304);
// define('HTTP_STATUS_USE_PROXY', 305);
// define('HTTP_STATUS_UNUSED', 306);
define('HTTP_STATUS_TEMPORARY_REDIRECT', 307);
define('HTTP_STATUS_PERMANENT_REDIRECT', 308);

// Client Error
define('HTTP_STATUS_BAD_REQUEST', 400);
define('HTTP_STATUS_UNAUTHORIZED', 401);
define('HTTP_STATUS_PAYMENT_REQUIRED', 402);
define('HTTP_STATUS_FORBIDDEN', 403);
define('HTTP_STATUS_NOT_FOUND', 404);
define('HTTP_STATUS_METHOD_NOT_ALLOWED', 405);
define('HTTP_STATUS_NOT_ACCEPTABLE', 406);
define('HTTP_STATUS_PROXY_AUTHENTICATION_REQUIRED', 407);
define('HTTP_STATUS_REQUEST_TIMEOUT', 408);
define('HTTP_STATUS_CONFLICT', 409);
define('HTTP_STATUS_GONE', 410);
define('HTTP_STATUS_LENGTH_REQUIRED', 411);
define('HTTP_STATUS_PRECONDITION_FAILED', 412);
define('HTTP_STATUS_PAYLOAD_TOO_LARGE', 413);
define('HTTP_STATUS_URI_TOO_LONG', 414);
define('HTTP_STATUS_UNSUPPORTED_MEDIA_TYPE', 415);
define('HTTP_STATUS_RANGE_NOT_SATISFIABLE', 416);
define('HTTP_STATUS_EXPECTATION_FAILED', 417);
define('HTTP_STATUS_IM_A_TEAPOT', 418);
// define('HTTP_STATUS_MISDIRECTED_REQUEST', 418);
define('HTTP_STATUS_METHOD_FAILURE', 420);
define('HTTP_STATUS_MISDIRECTED_REQUEST', 421);
define('HTTP_STATUS_UNPROCESSABLE_CONTENT', 422);
define('HTTP_STATUS_LOCKED', 423);
define('HTTP_STATUS_FAILED_DEPENDENCY', 424);
define('HTTP_STATUS_TOO_EARLY', 425);
define('HTTP_STATUS_UPGRADE_REQUIRED', 426);
define('HTTP_STATUS_PRECONDITION_REQUIRED', 428);
define('HTTP_STATUS_TOO_MANY_REQUESTS', 429);
define('HTTP_STATUS_REQUEST_HEADER_FIELDS_TOO_LARGE', 431);
define('HTTP_STATUS_BLOCKED_BY_PARENTAL_CONTROLS', 450);
define('HTTP_STATUS_UNAVAILABLE_FOR_LEGAL_REASONS', 451);

// Server Error
define('HTTP_STATUS_INTERNAL_SERVER_ERROR', 500);
define('HTTP_STATUS_NOT_IMPLEMENTED', 501);
define('HTTP_STATUS_BAD_GATEWAY', 502);
define('HTTP_STATUS_GATEWAY_TIMEOUT', 504);
define('HTTP_STATUS_HTTP_VERSION_NOT_SUPPORTED', 505);
define('HTTP_STATUS_VARIANT_ALSO_NEGOTIATES', 506);
define('HTTP_STATUS_INSUFFICIENT_STORAGE', 507);
define('HTTP_STATUS_LOOP_DETECTED', 508);
define('HTTP_STATUS_BANDWIDTH_LIMIT_EXCEEDED', 509);
define('HTTP_STATUS_NOT_EXTENDED', 510);
define('HTTP_STATUS_NETWORK_AUTHENTICATION_REQUIRED', 511);
define('HTTP_STATUS_UNKNOWN_ERROR', 520);
define('HTTP_STATUS_INVALID_SSL_CERTIFICATE', 526);
define('HTTP_STATUS_SITE_OVERLOAD', 529);
define('HTTP_STATUS_SITE_IS_FROZEN', 530);
define('HTTP_STATUS_PERMISSION_DENIED', 550);
define('HTTP_STATUS_OPTION_NOT_SUPPORTED', 551);

// Application-specific
define('HTTP_STATUS_API_RATE_LIMIT_EXCEEDED', 710);

// Humor
define('HTTP_STATUS_IM_AFRAID_I_CANT_DO_THAT', 9000);

function http_status_message($code)
{
    switch ($code) {
        // Information
        case HTTP_STATUS_CONTINUE: return 'Continue';
        case HTTP_STATUS_SWITCHING_PROTOCOLS: return 'Switching protocols';
        case HTTP_STATUS_PROCESSING: return 'Processing';
        case HTTP_STATUS_EARLY_HINTS: return 'Early hints';

        // Success
        case HTTP_STATUS_OK: return 'Ok';
        case HTTP_STATUS_CREATED: return 'Created';
        case HTTP_STATUS_ACCEPTED: return 'Accepted';
        case HTTP_STATUS_NON_AUTHORITATIVE_INFORMATION: return 'Non-authoritative information';
        case HTTP_STATUS_NO_CONTENT: return 'No content';
        case HTTP_STATUS_RESET_CONTENT: return 'Reset content';
        case HTTP_STATUS_PARTIAL_CONTENT: return 'Partial content';
        case HTTP_STATUS_MULTI_STATUS: return 'Multi-status';
        case HTTP_STATUS_ALREADY_REPORTED: return 'Already reported';
        case HTTP_STATUS_IM_USED: return 'I\'m used';

        // Redirectional
        case HTTP_STATUS_MULTIPLE_CHOICES: return 'Multiple choices';
        case HTTP_STATUS_MOVED_PERMANENTLY: return 'Moved permanently';
        case HTTP_STATUS_FOUND: return 'Found';
        case HTTP_STATUS_SEE_OTHER: return 'See other';
        case HTTP_STATUS_NOT_MODIFIED: return 'Not modified';
        case HTTP_STATUS_TEMPORARY_REDIRECT: return 'Temporary redirect';
        case HTTP_STATUS_PERMANENT_REDIRECT: return 'Permanent redirect';

        // Client Error
        case HTTP_STATUS_BAD_REQUEST: return 'Bad request';
        case HTTP_STATUS_UNAUTHORIZED: return 'Unauthorized';
        case HTTP_STATUS_PAYMENT_REQUIRED: return 'Payment required';
        case HTTP_STATUS_FORBIDDEN: return 'Forbidden';
        case HTTP_STATUS_NOT_FOUND: return 'Not found';
        case HTTP_STATUS_METHOD_NOT_ALLOWED: return 'Method not allowed';
        case HTTP_STATUS_NOT_ACCEPTABLE: return 'Not acceptable';
        case HTTP_STATUS_PROXY_AUTHENTICATION_REQUIRED: return 'Proxy authentication required';
        case HTTP_STATUS_REQUEST_TIMEOUT: return 'Request timeout';
        case HTTP_STATUS_CONFLICT: return 'Conflict';
        case HTTP_STATUS_GONE: return 'Gone';
        case HTTP_STATUS_LENGTH_REQUIRED: return 'Length required';
        case HTTP_STATUS_PRECONDITION_FAILED: return 'Precondition failed';
        case HTTP_STATUS_PAYLOAD_TOO_LARGE: return 'Payload too large';
        case HTTP_STATUS_URI_TOO_LONG: return 'URI too long';
        case HTTP_STATUS_UNSUPPORTED_MEDIA_TYPE: return 'Unsupported media type';
        case HTTP_STATUS_RANGE_NOT_SATISFIABLE: return 'Range not satisfiable';
        case HTTP_STATUS_EXPECTATION_FAILED: return 'Expectation failed';
        case HTTP_STATUS_IM_A_TEAPOT: return 'I\'m a teapot';
        case HTTP_STATUS_METHOD_FAILURE: return 'Method failure';
        case HTTP_STATUS_MISDIRECTED_REQUEST: return 'Misdirected request';
        case HTTP_STATUS_UNPROCESSABLE_CONTENT: return 'Unprocessable content';
        case HTTP_STATUS_LOCKED: return 'Locked';
        case HTTP_STATUS_FAILED_DEPENDENCY: return 'Failed dependency';
        case HTTP_STATUS_TOO_EARLY: return 'Too learly';
        case HTTP_STATUS_UPGRADE_REQUIRED: return 'Upgrade required';
        case HTTP_STATUS_PRECONDITION_REQUIRED: return 'Precondition required';
        case HTTP_STATUS_TOO_MANY_REQUESTS: return 'Too many requests';
        case HTTP_STATUS_REQUEST_HEADER_FIELDS_TOO_LARGE: return 'Request header fields too large';
        case HTTP_STATUS_BLOCKED_BY_PARENTAL_CONTROLS: return 'Blocked by parental controls';
        case HTTP_STATUS_UNAVAILABLE_FOR_LEGAL_REASONS: return 'Unavailable for legal reasons';

        // Server Error
        case HTTP_STATUS_INTERNAL_SERVER_ERROR: return 'Internal server error';
        case HTTP_STATUS_NOT_IMPLEMENTED: return 'Not implemented';
        case HTTP_STATUS_BAD_GATEWAY: return 'Bad gateway';
        case HTTP_STATUS_GATEWAY_TIMEOUT: return 'Gateway timeout';
        case HTTP_STATUS_HTTP_VERSION_NOT_SUPPORTED: return 'HTTP version not supported';
        case HTTP_STATUS_VARIANT_ALSO_NEGOTIATES: return 'Variant also negotiates';
        case HTTP_STATUS_INSUFFICIENT_STORAGE: return 'Insufficient storage';
        case HTTP_STATUS_LOOP_DETECTED: return 'Loop detected';
        case HTTP_STATUS_BANDWIDTH_LIMIT_EXCEEDED: return 'Bandwidth limit exceeded';
        case HTTP_STATUS_NOT_EXTENDED: return 'Not extended';
        case HTTP_STATUS_NETWORK_AUTHENTICATION_REQUIRED: return 'Network authentication required';
        case HTTP_STATUS_UNKNOWN_ERROR: return 'Unknown error';
        case HTTP_STATUS_INVALID_SSL_CERTIFICATE: return 'Invalid SSL certificate';
        case HTTP_STATUS_SITE_OVERLOAD: return 'Site overload';
        case HTTP_STATUS_SITE_IS_FROZEN: return 'Site is frozen';
        case HTTP_STATUS_PERMISSION_DENIED: return 'Permission denied';
        case HTTP_STATUS_OPTION_NOT_SUPPORTED: return 'Option not supported';

        // Application-specific
        case HTTP_STATUS_API_RATE_LIMIT_EXCEEDED: return 'API rate limit exceeded';

        // Humor
        case HTTP_STATUS_IM_AFRAID_I_CANT_DO_THAT: return 'I\'m afraid I can\'t do that';
        default:
            if ($code < 100) {
                return null;
            }

            if ($code < 200) {
                return 'Information';
            }

            if ($code < 300) {
                return 'Success';
            }

            if ($code < 400) {
                return 'Client error';
            }

            if ($code < 500) {
                return 'Server error';
            }

            return null;
    }

}
