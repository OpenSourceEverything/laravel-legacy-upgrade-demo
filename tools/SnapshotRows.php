<?php

namespace Demo\Verification;

/** Canonicalize PDO's representation, not the inventory's business values. */
final class SnapshotRows
{
    public static function normalize($table, array $row)
    {
        $integers = [
            'users' => ['id'],
            'categories' => ['id', 'user_id'],
            'items' => ['id', 'user_id', 'category_id', 'quantity', 'price_cents'],
            'stock_movements' => ['id', 'item_id', 'delta'],
        ];
        foreach ($integers[$table] as $column) {
            // Old PDO returns numeric strings; modern PDO returns native ints.
            // Do not cast floats, nulls or arbitrary text: those must still fail.
            if (isset($row[$column]) && is_int($row[$column])) {
                $row[$column] = (string) $row[$column];
            }
        }
        return $row;
    }
}
