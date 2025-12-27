<?php

namespace Database\Seeders;

use App\Models\ItemCategory;
use Illuminate\Database\Seeder;
use App\Models\Admin;


class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::updateOrCreate(
            ['email' => 'sadanand@ioepc.edu.np'], // unique field
            [
                'name' => 'Sadanand Paneru',
                'password' => bcrypt('S@ddy9843'),
                'contact' => '9843521965',
            ]
        );
        Admin::updateOrCreate(
            ['email' => 'cit@ioepc.edu.np'], // unique field
            [
                'name' => 'Purwanchal Campus',
                'password' => bcrypt('Admin@ioepc123'),
                'contact' => '9843521965',
            ]
        );
        ItemCategory::updateOrCreate(
            ['name_en' => 'Non-Consumable'], // unique field
            [
                'type' => '1',
                'name_np' => 'पूजीगत',
            ]
        );
        ItemCategory::updateOrCreate(
            ['name_en' => 'Consumable'], // unique field
            [
                'type' => '0',
                'name_np' => 'संचालन',
            ]
        );
    }
}
