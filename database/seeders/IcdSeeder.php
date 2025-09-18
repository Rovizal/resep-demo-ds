<?php

namespace Database\Seeders;

use JeroenZwart\CsvSeeder\CsvSeeder;
use Illuminate\Support\Facades\DB;

class IcdSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->file = base_path() . '/database/seeders/csv/icd10.csv';
        $this->tablename = 'icds';
        $this->delimiter = ';';
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::disableQueryLog();

        parent::run();
    }
}
