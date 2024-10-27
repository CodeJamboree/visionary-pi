<?php
require_once 'Database.php';

class DatabaseHelper extends Database
{
    public function preparedExecute(...$values)
    {
        $typeCount = strlen($this->preparedTypes);
        $valueCount = count($values);
        if ($valueCount !== $typeCount) {
            throw new Exception("Count mismatch. Types: $typeCount != Values: $valueCount");
        }
        if (
            $this->bind_param($this->preparedTypes, ...$values) &&
            $this->execute()
        ) {
            return true;
        }
        return false;
    }
    public function selectPreparedScalar(...$values)
    {
        if (
            $this->preparedExecute(...$values) &&
            $this->bind_result($scalar) &&
            $this->fetch()
        ) {
            $this->free_result();
            //$this->closeStatement();
            return $scalar;
        }
        return false;
    }
    public function selectScalar(string $sql, ?string $types = '', ...$values)
    {
        if (
            $this->prepare($sql) &&
            $this->bind_param($types, ...$values) &&
            $this->execute() &&
            $this->bind_result($scalar) &&
            $this->fetch()
        ) {
            return $scalar;
        }
        return false;
    }
    public function selectRow($sql, ?string $types = '', ...$values)
    {
        $rows = $this->selectRows($sql, $types, ...$values);
        $count = count($rows);
        if ($count === 0) {
            return false;
        }

        if ($count === 1) {
            return $rows[0];
        }

        return $this->handleError(
            'More than one row returned.',
            HTTP_STATUS_INTERNAL_SERVER_ERROR
        );
    }
    public function selectRows($sql, ?string $types = '', ...$values)
    {
        if (
            $this->prepare($sql) &&
            ($types == '' || $this->bind_param($types, ...$values)) &&
            $this->execute()
        ) {
            if ($this->affected_rows === 0) {
                return array();
            }
            $result = $this->get_result();
            if ($result === false) {
                return false;
            }
            $rows = $result->fetch_all(MYSQLI_ASSOC);
            $result->free();
            // $this->free_result();
            $this->skip_results();
            $this->closeStatement();
            return $rows;
        }
        return false;
    }
    public function affectAny($sql, $types = '', ...$values)
    {
        return $this->prepare($sql) &&
        $this->bind_param($types, ...$values) &&
        $this->execute();
    }
    public function affectOne($sql, $types, ...$values)
    {
        return $this->prepare($sql) &&
        $this->bind_param($types, ...$values) &&
        $this->execute() &&
        $this->affected_rows === 1;
    }
    public function affectOneOrMore($sql, $types, ...$values)
    {
        return $this->prepare($sql) &&
        $this->bind_param($types, ...$values) &&
        $this->execute() &&
        $this->affected_rows >= 1;
    }
    public function affectOneOrTwo($sql, $types, ...$values)
    {
        return $this->prepare($sql) &&
        $this->bind_param($types, ...$values) &&
        $this->execute() &&
        $this->affected_rows === 1 || $this->affected_rows === 2;
    }
    public function in_placeholder($values)
    {
        $placeholders = implode(', ', array_fill(0, count($values), '?'));
        return "IN ($placeholders)";
    }
    public function param_repeat($type, $values)
    {
        switch ($type) {
            case 'i': //integer
            case 's': //string
            case 'd': // double
            case 'b': // blob
                break;
            default:
                throw new Exception("Invalid paraemter type \"$type\". Expected i, s, d, or b.");
        }
        return str_repeat($type, count($values));
    }
}
