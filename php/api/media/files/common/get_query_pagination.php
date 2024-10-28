<?php
function get_query_pagination($defaultLimit = 20, $defaultOffset = 0)
{
    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : $defaultLimit;
    $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : $defaultOffset;
    if ($limit < 1) {
        $limit = 1;
    } elseif ($limit > 500) {
        $limit = 500;
    }
    if ($offset < 0) {
        $offset = 0;
    }
    return ['limit' => $limit, 'offset' => $offset];
}
