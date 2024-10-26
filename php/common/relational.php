<?php
function batch_leaf_nodes_first($rows)
{
    $ids = [];
    $batchIds = [];

    while (count($rows) !== 0) {
        $childRows = array_filter($rows, function ($row) use ($ids) {
            $parentId = $row['parentId'];
            return $parentId === 0 || in_array($parentId, $ids);
        }, $rows);
        if (count($childRows) === 0) {
            throw new Exception("Infinite loop detcted");
        }
        $childIds = array_map(function ($row) {return $row['id'];}, $childRows);
        $batchIds[] = $childIds;
        $ids = array_merge($ids, $childIds);
        $rows = array_filter($rows, function ($row) use ($ids) {
            return !in_array($rows['id'], $ids);
        });
    }
    return $batchIds;
}
