<?php

require_once __DIR__.'/../tools/SnapshotRows.php';

use Demo\Verification\SnapshotRows;

class SnapshotRowsTest extends \PHPUnit\Framework\TestCase
{
    public function test_pdo_integer_representation_does_not_imply_changed_data()
    {
        $old = ['id' => '1', 'user_id' => '2', 'category_id' => '3', 'quantity' => '12', 'price_cents' => '450', 'sku' => '001'];
        $new = ['id' => 1, 'user_id' => 2, 'category_id' => 3, 'quantity' => 12, 'price_cents' => 450, 'sku' => '001'];
        $this->assertSame($old, SnapshotRows::normalize('items', $new));
    }

    public function test_real_stock_changes_and_invalid_types_are_not_normalized_away()
    {
        $old = ['quantity' => '12'];
        $this->assertNotSame($old, SnapshotRows::normalize('items', ['quantity' => 13]));
        $this->assertNotSame($old, SnapshotRows::normalize('items', ['quantity' => 12.0]));
        $this->assertNotSame($old, SnapshotRows::normalize('items', ['quantity' => null]));
        $this->assertNotSame($old, SnapshotRows::normalize('items', ['quantity' => '012']));
    }

    public function test_password_and_timestamp_text_are_preserved_exactly()
    {
        $row = ['id' => '1', 'password' => 'synthetic-hash', 'created_at' => '2016-09-01 12:00:00'];
        $this->assertSame($row, SnapshotRows::normalize('users', $row));
    }
}
