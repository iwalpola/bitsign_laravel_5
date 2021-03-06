<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSignaturesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('signatures')) {
            Schema::drop('signatures');
        }
		Schema::create('signatures', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('contract_id')->length(12)->unsigned()->index();
			$table->integer('signee_id')->length(12)->unsigned()->index();
			$table->string('contractkey_enc', 684);
			$table->char('hash', 44);
			$table->char('txhash', 64);
			$table->string('term', 40);
			$table->boolean('status');
			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('signatures');
	}

}
