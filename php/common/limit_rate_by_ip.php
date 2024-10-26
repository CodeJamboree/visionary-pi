<?php
require_once 'DatabaseHelper.php';
require_once 'Show.php';
require_once 'HTTP_STATUS.php';

// NOTE: Crude DOS Attack Prevention (we have to start somewhere)
// * Bot-nets use multiple IP addresses
// * Many legitimate users may be behind the same IP address
//      ie proxy, vpn, or same network
// * IP headers may be spoofed
// * Even when someone made too many requests, need to limit
//      resources to check database if a botnet is attacking
//      with random IP addresses
// Consider limiting by account or token for authenticated API requests

function limit_rate_by_ip()
{
    $MAX_REQUESTS = 100;
    $THRESHOLD_SECONDS = 60;

    $ip = $_SERVER['REMOTE_ADDR'];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // TODO: Only accept this header if the REMOTE_ADDR is
        // a trusted proxy. This header can be spoofed to block
        // someone elses access if their IP is known to the attacker
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }

    $blocked = false;
    $db = new DatabaseHelper();
    // NOTE: DB is source of truth of time comparison & updates
    // Addresses cases where PHP server clock may be out of sync (especially in farms)
    $row = $db->selectRow('SELECT
        id,
        count,
        (CURRENT_TIMESTAMP - firstAt) as seconds
    FROM
        RateLimitsByIP
    WHERE
        ip = ?',
        's',
        $ip
    );
    if ($row) {
        $id = $row['id'];
        $seconds = $row['seconds'];
        $count = $row['count'];
        if ($seconds <= $THRESHOLD_SECONDS) {
            $db->affectOne('UPDATE
                RateLimitsByIP
            SET
                count = count + 1
            WHERE
                id = ?',
                'i', $id);
            if ($count >= $MAX_REQUESTS) {
                Show::error(
                    "Too Many Requests.",
                    HTTP_STATUS_TOO_MANY_REQUESTS
                );
                exit;
            }
        } else {
            $db->affectOne('UPDATE
                RateLimitsByIP
            SET
                firstAt = CURRENT_TIMESTAMP,
                count = 1
            WHERE
                id = ?',
                'i', $id);
        }
    } else {
        $db->affectOne('INSERT IGNORE INTO
            RateLimitsByIP (ip)
            VALUES (?)
            ON DUPLICATE KEY UPDATE count = count + 1
            ', 's', $ip);
    }
}
limit_rate_by_ip();
