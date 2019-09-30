<?php

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->delete();
        DB::table('modules')->delete();
        DB::table('privilage')->delete();
    	$uuid = createUuid('Admin');
        $user_id = DB::table('users')->insertGetId([
            'uuid' => $uuid,
            'first_name' => 'Admin',
            'email' => 'admin@gmail.com',
            'phone' => '9034831200',
            'password' => bcrypt('admin'),
            'role_id' => 1,
            'comp_id'=>1,
            'dep_id' => 1,  
            'active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        $modules = [
            [
                'name' => 'company',
                'created_by' => $user_id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'role',
                'created_by' => $user_id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'module',
                'created_by' => $user_id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'privilage',
                'created_by' => $user_id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];
        foreach ($modules as $module) {
            $module_id = DB::table('modules')->insertGetId($module);    
            $privilage = [
                'module_id' => $module_id,
                'add'=>'1',
                'edit'=>'1',
                'view'=>'1',
                'delete'=>'1',
                'user_id'=>$user_id,
                'type'=>'default',
                'created_by'=>$user_id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            DB::table('privilage')->insert($privilage);
        }
    }
}
