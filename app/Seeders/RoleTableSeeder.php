<?php
namespace App\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $path = storage_path() . "/json/roles.json";
        $arr = getArrFromJson($path);
        foreach ($arr['data'] as $key => $value) {
            \DB::table('roles')->insert(
                [
                'name' => $value['name'],
                'guard_name' => $value['guard_name'],
                'created_at' => now(),
                'updated_at' => now(),
                ]
            );
        }
        app()['cache']->forget('spatie.permission.cache');
        $admin = Role::whereName('admin')->first();
        $admin->syncPermissions(Permission::select('name')->get()->toArray());

        $client = Role::whereName('client')->first();
        $client->syncPermissions($this->clientPermissions());
    }

    private function clientPermissions()
    {
        return [
            'tickets_create',
            'invoices_comment',
            'menu_home',
            'menu_calendar',
            'menu_estimates',
            'menu_expenses',
            'menu_invoices',
            'menu_messages',
            'menu_notes',
            'menu_payments',
            'menu_projects',
            'menu_sales',
            'menu_tickets',
            'menu_creditnotes',
            'menu_contracts',
            'menu_knowledgebase',
            'menu_subscriptions',
            'files_create',
            'estimates_comment',
            'projects_view_expenses',
            'projects_view_hours'
        ];
    }
}
