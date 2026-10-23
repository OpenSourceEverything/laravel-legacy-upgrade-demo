<?php

use App\Category;
use App\Item;
use App\StockMovement;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/** HTTP contracts are kept across framework versions, not replaced by mocks. */
class InventoryTest extends TestCase
{
    use DatabaseMigrations;

    // 5.7 otherwise mocks Artisan output and defers command execution until
    // the pending-command object is asserted. These tests need real seeding.
    public $mockConsoleOutput = false;

    private function staff($name = 'owner')
    {
        return User::create(['name' => $name, 'email' => $name.'@example.test', 'password' => bcrypt('fixture-password', ['rounds' => 4])]);
    }

    private function item(User $user, $sku = 'PART-1')
    {
        $category = $user->categories()->create(['name' => 'Parts']);
        return $user->items()->create(['category_id' => $category->id, 'sku' => $sku, 'name' => 'Hinge', 'price_cents' => 450]);
    }

    private function http($method, $uri, $data = [], $json = false)
    {
        return $this->call($method, $uri, $data, [], [], $json ? ['HTTP_ACCEPT' => 'application/json'] : []);
    }

    // Avoid PHPUnit 10's final status() method. This helper asserts an HTTP
    // response status; it does not replace PHPUnit's test-execution status.
    private function assertHttpStatus($expected, $response)
    {
        $this->assertEquals($expected, $response->getStatusCode(), $response->getContent());
    }

    public function test_guests_cannot_read_or_change_inventory()
    {
        $this->assertHttpStatus(302, $this->http('GET', '/items'));
        $this->assertHttpStatus(401, $this->http('POST', '/items', [], true));
        $this->assertEquals(0, Item::count());
    }

    public function test_login_accepts_hashed_password_and_logout_ends_authentication()
    {
        $user = $this->staff();
        $this->assertHttpStatus(200, $this->http('GET', '/login'));
        $guestSession = $this->app['session']->getId();
        $this->assertHttpStatus(302, $this->http('POST', '/login', ['email' => $user->email, 'password' => 'fixture-password']));
        $this->assertEquals($user->id, Auth::id());
        // Successful authentication must rotate the guest session ID, not
        // merely associate credentials with an attacker-known session.
        $this->assertNotSame($guestSession, $this->app['session']->getId());
        $authenticatedSession = $this->app['session']->getId();
        $this->assertHttpStatus(302, $this->http('POST', '/logout'));
        $this->assertFalse(Auth::check());
        $this->assertNotSame($authenticatedSession, $this->app['session']->getId());
    }

    public function test_wrong_password_does_not_authenticate()
    {
        $user = $this->staff();
        $this->assertHttpStatus(302, $this->http('POST', '/login', ['email' => $user->email, 'password' => 'wrong']));
        $this->assertFalse(Auth::check());
    }

    public function test_real_csrf_middleware_rejects_a_missing_token()
    {
        $user = $this->staff();
        // Laravel bypasses CSRF in its test environment. Enable the real check
        // for this request instead of mistaking that bypass for coverage.
        $this->app['env'] = 'local';
        $this->assertHttpStatus(419, $this->http('POST', '/login', ['email' => $user->email, 'password' => 'fixture-password']));
        $this->assertHttpStatus(419, $this->http('POST', '/login', ['email' => $user->email, 'password' => 'fixture-password', '_token' => 'deliberately-wrong-token']));
        $this->assertFalse(Auth::check());
    }

    public function test_category_create_read_update_delete()
    {
        $this->actingAs($this->staff());
        $this->assertHttpStatus(200, $this->http('GET', '/categories/create'));
        $this->assertHttpStatus(302, $this->http('POST', '/categories', ['name' => 'Tools']));
        $category = Category::first();
        $this->assertHttpStatus(200, $this->http('GET', '/categories'));
        $this->assertHttpStatus(200, $this->http('GET', '/categories/'.$category->id.'/edit'));
        $this->assertHttpStatus(302, $this->http('PUT', '/categories/'.$category->id, ['name' => 'Hardware']));
        $this->assertEquals('Hardware', Category::find($category->id)->name);
        $this->assertHttpStatus(302, $this->http('DELETE', '/categories/'.$category->id));
        $this->assertEquals(0, Category::count());
    }

    public function test_item_create_read_update_delete_and_ownership_cannot_be_injected()
    {
        $user = $this->staff();
        $other = $this->staff('other');
        $category = $user->categories()->create(['name' => 'Tools']);
        $this->actingAs($user);
        $data = ['category_id' => $category->id, 'sku' => 'SPANNER', 'name' => 'Spanner', 'price_cents' => 599, 'user_id' => $other->id, 'quantity' => 900];
        $this->assertHttpStatus(200, $this->http('GET', '/items/create'));
        $this->assertHttpStatus(302, $this->http('POST', '/items', $data));
        $item = Item::first();
        $this->assertEquals($user->id, $item->user_id);
        $this->assertEquals(0, $item->quantity);
        $this->assertHttpStatus(200, $this->http('GET', '/items'));
        $this->assertHttpStatus(200, $this->http('GET', '/items/'.$item->id));
        $this->assertHttpStatus(200, $this->http('GET', '/items/'.$item->id.'/edit'));
        $data['name'] = 'Small spanner';
        $this->assertHttpStatus(302, $this->http('PUT', '/items/'.$item->id, $data));
        $this->assertEquals('Small spanner', Item::find($item->id)->name);
        $this->assertEquals(0, Item::find($item->id)->quantity);
        $this->assertHttpStatus(302, $this->http('DELETE', '/items/'.$item->id));
        $this->assertEquals(0, Item::count());
    }

    public function test_invalid_item_does_not_write_a_row()
    {
        $this->actingAs($this->staff());
        $this->assertHttpStatus(422, $this->http('POST', '/items', ['name' => '', 'price_cents' => -1], true));
        $this->assertEquals(0, Item::count());
    }

    public function test_duplicate_sku_is_rejected_for_same_owner()
    {
        $user = $this->staff();
        $item = $this->item($user);
        $this->actingAs($user);
        $this->assertHttpStatus(422, $this->http('POST', '/items', ['category_id' => $item->category_id, 'sku' => $item->sku, 'name' => 'Duplicate', 'price_cents' => 1], true));
        $this->assertEquals(1, Item::count());
    }

    public function test_same_sku_can_belong_to_different_owners()
    {
        // The unique key is (owner, SKU), not a global SKU restriction.
        $this->item($this->staff(), 'SHARED-SKU');
        $other = $this->staff('other');
        $category = $other->categories()->create(['name' => 'Other parts']);
        $this->actingAs($other);
        $this->assertHttpStatus(302, $this->http('POST', '/items', [
            'category_id' => $category->id, 'sku' => 'SHARED-SKU',
            'name' => 'Other owner item', 'price_cents' => 250,
        ]));
        $this->assertEquals(2, Item::where('sku', 'SHARED-SKU')->count());
        $this->assertEquals($other->id, Item::orderBy('id', 'desc')->first()->user_id);
    }

    public function test_other_users_records_are_not_visible_or_mutable()
    {
        $owner = $this->staff();
        $item = $this->item($owner);
        $this->actingAs($this->staff('other'));
        $response = $this->http('GET', '/items');
        $this->assertHttpStatus(200, $response);
        $this->assertFalse(strpos($response->getContent(), 'PART-1') !== false);
        foreach (['GET', 'PUT', 'DELETE'] as $method) {
            $this->assertHttpStatus(404, $this->http($method, '/items/'.$item->id));
        }
        $this->assertHttpStatus(404, $this->http('POST', '/items/'.$item->id.'/adjust', ['delta' => 1, 'reason' => 'Attempt'], true));
        $this->assertHttpStatus(404, $this->http('PUT', '/categories/'.$item->category_id, ['name' => 'Changed']));
        $this->assertHttpStatus(404, $this->http('GET', '/categories/'.$item->category_id.'/edit'));
        $this->assertHttpStatus(404, $this->http('DELETE', '/categories/'.$item->category_id));
        $this->assertEquals(1, Item::count());
        $this->assertEquals(1, Category::count());
        $this->assertEquals(0, StockMovement::count());
    }

    public function test_category_from_another_owner_cannot_be_used()
    {
        $otherItem = $this->item($this->staff('other'));
        $this->actingAs($this->staff());
        $this->assertHttpStatus(422, $this->http('POST', '/items', ['category_id' => $otherItem->category_id, 'sku' => 'NEW', 'name' => 'Attempt', 'price_cents' => 100], true));
        $this->assertEquals(1, Item::count());
    }

    public function test_receipt_and_issue_preserve_stock_ledger_balance()
    {
        $user = $this->staff();
        $item = $this->item($user);
        $this->actingAs($user);
        $this->assertHttpStatus(302, $this->http('POST', '/items/'.$item->id.'/adjust', ['delta' => 10, 'reason' => 'Receipt']));
        $this->assertHttpStatus(302, $this->http('POST', '/items/'.$item->id.'/adjust', ['delta' => -3, 'reason' => 'Issue']));
        $stored = Item::find($item->id);
        $this->assertEquals(7, $stored->quantity);
        $this->assertEquals($stored->quantity, $stored->movements()->sum('delta'));
        $this->assertEquals(2, $stored->movements()->count());
    }

    public function test_negative_stock_is_rejected_without_partial_ledger_write()
    {
        $user = $this->staff();
        $item = $this->item($user);
        $this->actingAs($user);
        $this->assertHttpStatus(422, $this->http('POST', '/items/'.$item->id.'/adjust', ['delta' => -1, 'reason' => 'Cannot issue'], true));
        $this->assertEquals(0, Item::find($item->id)->quantity);
        $this->assertEquals(0, StockMovement::count());
    }

    public function test_zero_or_fractional_adjustments_are_rejected()
    {
        $user = $this->staff();
        $item = $this->item($user);
        $this->actingAs($user);
        foreach ([0, 1.5, 'not a number'] as $delta) {
            $this->assertHttpStatus(422, $this->http('POST', '/items/'.$item->id.'/adjust', ['delta' => $delta, 'reason' => 'Invalid'], true));
        }
        $this->assertEquals(0, StockMovement::count());
    }

    public function test_stock_balance_rolls_back_if_recording_the_movement_fails()
    {
        $user = $this->staff();
        $item = $this->item($user);
        $this->actingAs($user);
        // Controlled fault injection in the in-memory test database only.
        // Reject the SECOND write, after the controller has saved the balance.
        // A validation failure before either write cannot prove atomic rollback.
        DB::unprepared("CREATE TRIGGER reject_test_movement BEFORE INSERT ON stock_movements BEGIN SELECT RAISE(ABORT, 'Controlled ledger failure'); END");
        $this->assertHttpStatus(500, $this->http('POST', '/items/'.$item->id.'/adjust', ['delta' => 5, 'reason' => 'Rollback test']));
        $this->assertEquals(0, Item::find($item->id)->quantity);
        $this->assertEquals(0, StockMovement::count());
        $this->assertEquals(0, DB::transactionLevel());
    }

    public function test_stock_ceiling_is_rejected_without_a_partial_write()
    {
        $user = $this->staff();
        $item = $this->item($user);
        // A boundary fixture represents stock accumulated over many receipts.
        // It starts internally consistent: quantity equals its opening ledger.
        $item->quantity = 1000000;
        $item->save();
        $item->movements()->create(['delta' => 1000000, 'reason' => 'Boundary fixture']);
        $this->actingAs($user);
        $this->assertHttpStatus(422, $this->http('POST', '/items/'.$item->id.'/adjust', ['delta' => 1, 'reason' => 'Over ceiling'], true));
        $this->assertEquals(1000000, Item::find($item->id)->quantity);
        $this->assertEquals(1, StockMovement::count());
        $this->assertEquals(1000000, $item->movements()->sum('delta'));
    }

    public function test_delete_cannot_remove_stock_history_or_a_used_category()
    {
        $user = $this->staff();
        $item = $this->item($user);
        $this->actingAs($user);
        $this->http('POST', '/items/'.$item->id.'/adjust', ['delta' => 1, 'reason' => 'Receipt']);
        $this->assertHttpStatus(409, $this->http('DELETE', '/items/'.$item->id));
        $this->assertHttpStatus(409, $this->http('DELETE', '/categories/'.$item->category_id));
        $this->assertEquals(1, StockMovement::count());
        $this->assertEquals(1, Item::count());
    }

    public function test_html_is_escaped_in_catalog_and_movement_history()
    {
        $user = $this->staff();
        $item = $this->item($user);
        $item->name = '<script>alert(1)</script>';
        $item->save();
        $this->actingAs($user);
        $response = $this->http('GET', '/items');
        $this->assertNotFalse(strpos($response->getContent(), '&lt;script&gt;'));
        $this->assertFalse(strpos($response->getContent(), '<script>') !== false);
        $this->assertHttpStatus(302, $this->http('POST', '/items/'.$item->id.'/adjust', ['delta' => 1, 'reason' => '<script>history</script>']));
        $history = $this->http('GET', '/items/'.$item->id);
        $this->assertHttpStatus(200, $history);
        $this->assertNotFalse(strpos($history->getContent(), '&lt;script&gt;history&lt;/script&gt;'));
        $this->assertFalse(strpos($history->getContent(), '<script>') !== false);
    }

    public function test_demo_seed_is_idempotent_and_preserves_stock()
    {
        $this->artisan('db:seed');
        $this->assertEquals(12, Item::first()->quantity);
        $this->assertEquals(1, StockMovement::count());
        $this->artisan('db:seed');
        $this->assertEquals(1, User::count());
        $this->assertEquals(1, Item::count());
        $this->assertEquals(12, Item::first()->quantity);
        $this->assertEquals(1, StockMovement::count());
    }
}
