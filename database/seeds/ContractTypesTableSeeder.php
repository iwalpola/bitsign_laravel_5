<?php

use Illuminate\Database\Seeder;

class ContractTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $records = [
        [1, 'default'],
        [2, 'intellectual_property_record']
        ];

        foreach ($records as $record) {
            DB::table('contracttypes')->insert([
            'id' => $record[0],
            'contract_type' => $record[1],
            ]);
        }
        
    }
}
