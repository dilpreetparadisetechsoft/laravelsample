<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRelationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {			
            $table->foreign('dep_id')->references('dep_id')->on('department')->onDelete('cascade');
			$table->foreign('role_id')->references('role_id')->on('roles')->onDelete('cascade');
			$table->foreign('comp_id')->references('comp_id')->on('company')->onDelete('cascade');
        });
		Schema::table('tax', function (Blueprint $table) {
			$table->foreign('comp_id')->references('comp_id')->on('company')->onDelete('cascade');
        });
		Schema::table('email_template', function (Blueprint $table) {
			$table->foreign('comp_id')->references('comp_id')->on('company')->onDelete('cascade');
        });
		Schema::table('task_type', function (Blueprint $table) {
			$table->foreign('comp_id')->references('comp_id')->on('company')->onDelete('cascade');
        });
		Schema::table('customer', function (Blueprint $table) {
			$table->foreign('comp_id')->references('comp_id')->on('company')->onDelete('cascade');
			$table->foreign('branch_id')->references('branch_id')->on('branch')->onDelete('cascade');
			$table->foreign('serv_id')->references('serv_id')->on('services')->onDelete('cascade');
			$table->foreign('lead_id')->references('lead_id')->on('lead_source')->onDelete('cascade');
			$table->foreign('loc_id')->references('loc_id')->on('locations')->onDelete('cascade');
			$table->foreign('status_id')->references('status_id')->on('job_status')->onDelete('cascade');
        });
		Schema::table('branch', function (Blueprint $table) {
			$table->foreign('comp_id')->references('comp_id')->on('company')->onDelete('cascade');
			$table->foreign('loc_id')->references('loc_id')->on('locations')->onDelete('cascade');
        });
		Schema::table('locations', function (Blueprint $table) {
			$table->foreign('comp_id')->references('comp_id')->on('company')->onDelete('cascade');
			$table->foreign('tax_id')->references('tax_id')->on('tax')->onDelete('cascade');
        });
		Schema::table('email_attachment', function (Blueprint $table) {
			$table->foreign('comp_id')->references('comp_id')->on('company')->onDelete('cascade');
        });
		Schema::table('services', function (Blueprint $table) {
			$table->foreign('comp_id')->references('comp_id')->on('company')->onDelete('cascade');
        });
		Schema::table('estimate', function (Blueprint $table) {
			$table->foreign('comp_id')->references('comp_id')->on('company')->onDelete('cascade');
			$table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
			$table->foreign('customer_id')->references('customer_id')->on('customer')->onDelete('cascade');
			$table->foreign('status_id')->references('status_id')->on('job_status')->onDelete('cascade');
			$table->foreign('serv_id')->references('serv_id')->on('services')->onDelete('cascade');
        });
		/*Schema::table('estimate_log', function (Blueprint $table) {
			$table->foreign('comp_id')->references('comp_id')->on('company')->onDelete('cascade');
			$table->foreign('estimate_id')->references('estimate_id')->on('estimate')->onDelete('cascade');
			$table->foreign('serv_id')->references('serv_id')->on('services')->onDelete('cascade');
			$table->foreign('customer_id')->references('customer_id')->on('customer')->onDelete('cascade');
        });*/
		Schema::table('user_detail', function (Blueprint $table) {
			$table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
		});
	    Schema::table('login_logs', function (Blueprint $table) {
			$table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
		});
	    Schema::table('job_status', function (Blueprint $table) {
			$table->foreign('comp_id')->references('comp_id')->on('company')->onDelete('cascade');
		});
	    Schema::table('lead_source', function (Blueprint $table) {
			$table->foreign('comp_id')->references('comp_id')->on('company')->onDelete('cascade');
		});
	    Schema::table('template', function (Blueprint $table) {
			$table->foreign('comp_id')->references('comp_id')->on('company')->onDelete('cascade');
		});
		Schema::table('equipment', function (Blueprint $table) {
			$table->foreign('comp_id')->references('comp_id')->on('company')->onDelete('cascade');
		});	
        Schema::table('estimate_item', function (Blueprint $table) {
			$table->foreign('comp_id')->references('comp_id')->on('company')->onDelete('cascade');
			$table->foreign('estimate_id')->references('estimate_id')->on('estimate')->onDelete('cascade');
			$table->foreign('chg_code_id')->references('chg_code_id')->on('charge_code')->onDelete('cascade');
		});	
        Schema::table('privilage', function (Blueprint $table) {
			$table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
			$table->foreign('module_id')->references('module_id')->on('modules')->onDelete('cascade');
		});	
        Schema::table('kpi', function (Blueprint $table) {
			$table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
			$table->foreign('comp_id')->references('comp_id')->on('company')->onDelete('cascade');
		});		
        Schema::table('document_type', function (Blueprint $table) {
			$table->foreign('comp_id')->references('comp_id')->on('company')->onDelete('cascade');
		});	
        Schema::table('kit', function (Blueprint $table) {
			$table->foreign('comp_id')->references('comp_id')->on('company')->onDelete('cascade');
		});	
        /*Schema::table('invoice_payments', function (Blueprint $table) {
			$table->foreign('comp_id')->references('comp_id')->on('company')->onDelete('cascade');
			$table->foreign('estimate_id')->references('estimate_id')->on('estimate')->onDelete('cascade');
			$table->foreign('invoice_id')->references('invoice_id')->on('invoice')->onDelete('cascade');
		});*/	
        Schema::table('charge_code', function (Blueprint $table) {
			$table->foreign('comp_id')->references('comp_id')->on('company')->onDelete('cascade');
		}); 			 
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
		//
    }
}
