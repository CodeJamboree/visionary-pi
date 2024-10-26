<?php
function url_safe_string($string)
{
    // Convert unicode to compatible base charaters + accents
    // ie - letter "e" followed by an acute accent mark
    $string = Normalizer::normalize($string, Normalizer::FORM_D);

    // PascalCase and CamelCase
    $string = preg_replace('/([a-z])([A-Z])/u', '$1-$2', $string);

    // SnakeCase
    $string = preg_replace('/_+/', '-', $string);

    $string = strtolower($string);

    // Group successive digits
    $string = preg_replace('/(\d{2,})/', '-\\1-', $string);

    // Replace spaces with dashes
    $string = str_replace(' ', '-', $string);

    // Only allow a-z, digits, and dash
    $string = preg_replace('/[^a-z0-9-]/', '', $string);

    // Remove repeated dashes
    $string = preg_replace('/-+/', '-', $string);

    // Remove leading/trailing dashes
    $string = trim($string, '-');

    return $string;
}
function generate_slug($db, $text, $atempt = 0)
{
    // digits already mapped
    if (ctype_digit($text)) {
        return true;
    }

    if ($attempt > 10) {
        throw new Exception("Too many conflicts generating slug");
    }
    $slug = url_safe_string($text);
    if (empty($slug)) {
        $slug = uniqid();
    }
    if (str_length($slug) > 64) {
        $slug = substr($slug, 0, 64);
    }

    $sql = "SELECT COUNT(0) FROM Slugs WHERE slug = ?";
    $total = $db->selectScalar($sql, 's', $text);
    if ($total === false) {
        throw $db->get_last_exception();
    }
    if ($total === 0) {
        return $slug;
    }

    $sql = "SELECT MAX(SUBSTRING(slug, ?))
    FROM Slugs
    WHERE
    slug LIKE ?
    AND NOT slug LIKE ?
    ";
    $max = $db->selectScalar($sql, 'iss', str_length($slug) + 1, "$slug-%", "$slug-%-%");
    if ($max === false) {
        throw $db->get_last_exception();
    }
    $suffix = increment_slug_suffix($max);
    $slug = "$slug-$suffix";

    // edge case
    if (str_length($slug) > 64) {
        $suffix = uniqid();
        $truncated = substr($slug, 0, str_length($slug) - (str_length($suffix) + 1));
        return generate_slug($db, "$truncated-$suffix", $attempt + 1);
    }
    return $slug;
}
function register_slug($db, $slug, $type, $id)
{
    if (empty($slug) || empty($type)) {
        return false;
    }
    if (ctype_digit($slug)) {
        return true;
    }
    $sql = "INSERT INTO SlugTypes (name) VALUES(?)
        ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)";
    $result = $db->affectAny($sql, 's', $type);
    if ($result === false) {
        throw $db->get_last_exception();
    }
    $typeId = $db->insert_id();
    $sql = "INSERT INTO Slugs (slug, typeId, resourceId) VALUES(?, ?, ?)";
    $result = $db->affectOne($sql, 'sii', $slug, $typeId, $id);
    if ($result === false) {
        throw $db->get_last_exception();
    }
    return true;
}
function resolve_slugs_to_ids($db, $type, $slugs)
{
    $lookup = [];
    if (empty($type)) {
        return false;
    }

    if (empty($slugs)) {
        return $lookp;
    }
    $slugs = array_values(array_unique($slugs));
    $alpha = [];
    foreach ($slugs as $slug) {
        if (ctype_digit($slug)) {
            $lookp[$slug] = (int) $slug;
        } else {
            $alpha[] = $slug;
        }
    }
    if (count($alpha) === 0) {
        return $lookup;
    }

    $paramArgs = [$type];
    $paramTypes = 's';
    $paramTypes .= str_repeat('s', count($alpha));
    $paramArgs = array_merge($paramArgs, $alpha);
    $slugPlaceholders = implode(',', array_fill(0, count($alpha), '?'));

    $sql = "SELECT
        Slugs.resourceId,
        Slugs.slug
    FROM Slugs
    INNER JOIN SlugTypes
        ON Slugs.typeId = SlugTypes.id
    WHERE
    SlugTypes.name = ?
    AND Slugs.slug IN ($slugPlaceholders)
    LIMIT 500";

    $rows = $db->selectRows($sql, $paramTypes, ...$paramArgs);
    if ($rows === false) {
        throw $db->get_last_exception();
    }
    if (count($rows) === 0) {
        return $lookup;
    }

    foreach ($rows as $row) {
        $lookup[$row['slug']] = $row['resourceId'];
        $alpha = array_diff($alpha, [$row['slug']]);
    }
    if (count($alpha) === 0) {
        return $lookup;
    }

    $sql = "SELECT
        SlugHistory.slug,
        Slugs.resourceId
    FROM Slugs
    INNER JOIN SlugHistory ON
        Slugs.id = SlugHistory.slugId
    INNER JOIN SlugTypes
        ON Slugs.typeId = SlugTypes.id
    WHERE
    SlugTypes.name = ?
    AND SlugHistory.slug IN ($slugPlaceholders)
    ORDER BY changedAt DESC
    LIMIT 500";

    $rows = $db->selectRows($sql, $paramTypes, ...$paramArgs);
    if ($rows === false) {
        throw $db->get_last_exception();
    }
    if (count($rows) === 0) {
        return $lookup;
    }

    foreach ($rows as $row) {
        if (!isset($lookp[$row['slug']])) {
            $lookup[$row['slug']] = $row['resourceId'];
        }
    }
    return $lookup;
}
function resolve_slug_as_id($db, $type, $slug)
{
    if (empty($type)) {
        return false;
    }
    if (ctype_digit($slug)) {
        return (int) $slug;
    }

    $sql = "SELECT id FROM SlugTypes WHERE name = ?";
    $typeId = $db->selectScalar($sql, 's', $type);
    if ($typeId === false) {

        throw $db->get_last_exception("Slug Type $type not found.");
    }
    if ($typeId === null) {
        return false;
    }

    $sql = "SELECT COUNT(0) FROM Slugs WHERE typeId = ? AND slug = ?";
    $count = $db->selectScalar($sql, 'is', $typeId, $slug);
    if ($count === false) {
        throw $db->get_last_exception(
            "Unable to check if $slug exists for type $typeId ($type).");
    }
    if ($count === 1) {
        $sql = "SELECT resourceId FROM Slugs WHERE typeId = ? AND slug = ?";
        $id = $db->selectScalar($sql, 'is', $typeId, $slug);
        if ($id === false) {
            throw $db->get_last_exception(
                "Slug $slug not found for type $typeId ($type).");
        }
    } else {
        $sql = "SELECT Slugs.resourceId
        FROM SlugHistory
        INNER JOIN Slugs
            ON SlugHistory.slugId = Slugs.id
        WHERE
            Slugs.typeId = ? AND SlugHistory.slug = ?";
        $id = $db->selectScalar($sql, 'is', $typeId, $slug);
        if ($id === false) {
            throw $db->get_last_exception(
                "Slug $slug not found in history for type $typeId ($type).");
        }
    }

    return $id ?? $slug;
}
function resolve_id_as_slug($db, $type, $id)
{
    if (empty($type)) {
        return false;
    }

    $sql = "SELECT id FROM SlugTypes WHERE name = ?";
    $typeId = $db->selectScalar($sql, 's', $type);
    if ($typeId === false) {

        throw $db->get_last_exception("Slug Type $type not found.");
    }
    if ($typeId === null) {
        return false;
    }

    $sql = "SELECT slug FROM Slugs WHERE typeId = ? AND resourceId = ?";
    $slug = $db->selectScalar($sql, 'ii', $typeId, $id);
    if ($slug === false) {
        throw $db->get_last_exception(
            "Slug resource $id not found for type $typeId ($type).");
    }
    return $slug ?? $id;
}
function synchronize_slug($db, $type, $id, $slug)
{
    if (empty($slug) || emtpy($type)) {
        return false;
    }
    if (ctype_digit($slug)) {
        return true;
    }
    $sql = "SELECT id FROM SlugTypes WHERE name = ?";
    $typeId = $db->selectScalar($sql, 's', $type);
    if ($typeId === false) {
        throw $db->get_last_exception();
    }
    if ($typeId === null) {
        return false;
    }
    $sql = "SELECT slug FROM Slugs WHERE typeId = ? AND resourceId = ?";
    $currentSlug = $db->selectScalar($sql, 'ii', $typeId, $id);
    if ($currentSlug === false) {
        throw $db->get_last_exception();
    }
    if ($slug === $currentSlug) {
        return true;
    }
    $sql = "SELECT COUNT(0) FROM Slugs WHERE slug = ?";
    $dupes = $db->selectScalar($sql, 'is', $typeId, $slug);
    if ($dupes === false) {
        throw $db->get_last_exception();
    }
    if ($dupes !== 0) {
        return false;
    }

    $sql = "UPDATE Slugs SET slug = ? WHERE typeId = ? AND resourceId = ? AND slug = ?";
    $result = $db->affectOne($sql, 'siis', $slug, $typeId, $id, $currentSlug);
    if ($result === false) {
        throw $db->get_last_exception();
    }
    return true;
}

function resolve_slugs($db, $type, array $ids)
{
    if (empty($ids)) {
        return false;
    }
    $ids = array_values(array_unique($ids));
    $count = count($ids);

    $sql = "SELECT id FROM SlugTypes WHERE name = ?";
    $typeId = $db->selectScalar($sql, 's', $type);
    if ($typeId === false) {
        throw $db->get_last_exception();
    }
    if ($typeId === null) {
        return false;
    }

    $paramTypes = 'i';
    $paramArgs = [$typeId];

    $paramTypes .= str_repeat('i', count($ids));
    $paramArgs = array_merge($paramArgs, $ids);

    $idPlaceholders = implode(',', array_fill(0, count($ids), '?'));
    $sql = "SELECT
    s.resourceId AS `id`,
    s.slug
FROM
    Slugs s
    INNER JOIN (
        SELECT resourceId, typeId, MAX(changedAt) latestChangedAt
        FROM Slugs
        WHERE typeId = ? AND resourceId IN ($idPlaceholders)
        GROUP BY resourceId
    ) AS latest ON
    s.resourceId = latest.resourceId
    AND s.changedAt = latest.latestChangedAt
    AND s.typeId = latest.typeId
WHERE
    s.typeId = ?
    AND s.resourceId IN ($idPlaceholders)
    ";

    $paramTypes .= $paramTypes;
    $paramArgs = array_merge($paramArgs, $paramArgs);

    $rows = $db->selectRows($sql, $paramTypes, ...$paramArgs);
    if ($rows === false) {
        throw $db->get_last_exception();
    }
    if (count($rows) === 0) {
        return false;
    }

    $slugs = [];
    foreach ($rows as $row) {
        $slugs[$row['id']] = $row['slug'];
    }
    return $slugs;
}
function unregister_slugs($db, $type, $id)
{
    $sql = "SELECT id FROM SlugTypes (name) WHERE name = ?";
    $typeId = $db->selectScalar($sql, 's', $type);
    if ($typeId === false) {
        throw $db->get_last_exception();
    }
    if ($typeId === null) {
        return false;
    }
    $sql = "DELETE FROM Slugs WHERE typeId = ? AND resourceId = ?";
    $result = $db->affectAny($sql, 'ii', $typeId, $id);
    if ($result === false) {
        throw $db->get_last_exception();
    }
    return true;
}
function increment_slug_suffix(string $suffix)
{
    // latin1 sorting order of valid slug characters
    $charset = '123456789';
    $base = strlen($charset);

    if (empty($suffix)) {
        return '1';
    }

    $values = array_map(function ($char) use ($charset) {
        $pos = strpos($charset, $char);
        if ($pos === false) {
            $pos = $base - 1;
        }
        return $pos;
    }, str_split($slug));

    for ($i = count($values) - 1; $i >= 0; $i--) {
        $values[$i]++;
        if ($values[$i] < $charCount) {
            break;
        }
        $values[$i] = 0;
        if ($i === 0) {
            array_unshift($values, 0);
        }
    }

    return implode('', array_map(function ($index) use ($charset) {
        return $charset[$index];
    }, $values));
}
