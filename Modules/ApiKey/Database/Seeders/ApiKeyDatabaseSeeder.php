<?php

namespace Modules\ApiKey\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class ApiKeyDatabaseSeeder extends Seeder
{
    public function run()
    {
        Model::unguard();
    }
}
