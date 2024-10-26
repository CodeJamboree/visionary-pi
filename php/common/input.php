<?php
function get_query_parameter(string $name, int $argNumber = null, string $default_value = null)
{
    if (isset($_GET[$name])) {
        return $_GET[$name];
    }

    if (isset($_GET["_arg$argNumber"])) {
        return $_GET["_arg$argNumber"];
    }

    return $default_value;
}

function unique_ids($ids)
{
    if (empty($ids)) {
        return $ids;
    }
    if (!is_array($ids)) {
        throw new Exception("ids must be an array");
    }
    if (count($ids) !== count(array_unique($ids))) {
        throw new Exception('ids must be unique');
    }
    foreach ($ids as $id) {
        if (!is_numeric($id) || !is_int($id) || $id < 1) {
            throw new Exception("Invalid number: $id");
        }
    }
    return $ids;
}

function clamp_string($value, $max_length)
{
    if (empty($value)) {
        return $value;
    }
    $value = trim($value);
    $length = strlen($value);
    if ($length > $max_length) {
        throw new Exception("String exceeds $max_length bytes. Actual length: $length");
    }
    return $value;
}
function clamp_string_array($values, $max_length)
{
    if (empty($values)) {
        return $values;
    }
    $values = (array) $values;
    $length = count($values);
    if ($length > 100) {
        throw new Error("Array is too large.");
    }
    return array_map(function ($value) use ($max_length) {
        return clamp_string($value, $max_length);
    }, $values);
}
function clamp_int_array($values, $min, $max)
{
    if (empty($values)) {
        return $values;
    }
    $values = (array) $values;
    $length = count($values);
    if ($length > 100) {
        throw new Error("Array is too large.");
    }
    return array_map(function ($value) use ($min, $max) {
        return clamp_int($value, $min, $max);
    }, $values);
}
function clamp_boolean_array($values)
{
    if (empty($values)) {
        return $values;
    }
    $values = (array) $values;
    $length = count($values);
    if ($length > 100) {
        throw new Error("Array is too large.");
    }
    return array_map(function ($value) {
        return clamp_boolean($value);
    }, $values);
}
function clamp_boolean($value)
{
    return filter_var($value, FILTER_VALIDATE_BOOLEAN);
}
function clamp_int($value, $min, $max)
{
    if (empty($value)) {
        return $min;
    }
    if (!is_numeric($value)) {
        return $min;
    }

    if (!is_int($value)) {
        $value = intval($value);
    }

    if ($value < $min) {
        return $min;
    }

    if ($value > $max) {
        return $max;
    }
    return $value;
}
