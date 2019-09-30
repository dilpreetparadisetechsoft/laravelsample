<?php

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$uuid = createUuid('test mode');
        DB::table('company')->insert([
            'comp_code' => $uuid,
            'comp_name' => 'test mode',
            'comp_logo' => '',
            'tag_line' => '',
            'comp_address' => '',
            'comp_gst_no' => '',
            'comp_pst_no'=> '',
            'comp_qst_no' => '',
            'comp_status' => '1',
            'finance_mail' => '',
            'created_by' => 0,
            'updated_by' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
}
