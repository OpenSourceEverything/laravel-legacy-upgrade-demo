<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Deliberately synthetic local fixture. Rerunning setup preserves edits.
        $user = \App\User::firstOrCreate(['email' => 'staff@example.test'], [
            'name' => 'Demo Staff', 'password' => bcrypt('demo-password'),
        ]);
        $category = $user->categories()->firstOrCreate(['name' => 'Hardware']);
        // 5.3's relationship firstOrCreate accepts only one attributes array;
        // unlike Builder::firstOrCreate, its second values array is ignored.
        $item = $user->items()->where('sku', 'HINGE-001')->first();
        if (!$item) {
            $item = $user->items()->create([
                'sku' => 'HINGE-001', 'category_id' => $category->id,
                'name' => 'Brass hinge', 'price_cents' => 450,
            ]);
        }
        if ($item->quantity === 0 && !$item->movements()->exists()) {
            $item->quantity = 12;
            $item->save();
            $item->movements()->create(['delta' => 12, 'reason' => 'Synthetic opening stock']);
        }
    }
}
