<?php

namespace App\Console\Commands;

use App\Models\TenderDivision;
use Illuminate\Console\Command;
use App\Models\Tender;
use App\Models\TenderItem;
use Illuminate\Support\Facades\DB;

class ImportTenderItems extends Command
{

    protected $signature = 'tender:import {file}';
    protected $description = 'Import Tender Items from CSV without any external package';

    public function handle()
    {

        $fileName = $this->argument('file');
        $filePath = storage_path('app/' . $fileName);

        if (!file_exists($filePath)) {
            $this->error("File not found at: " . $filePath);
            return;
        }

        $this->info("Starting Tender Items Import...");

        $file = fopen($filePath, 'r');


        $header = fgetcsv($file);

        $rowCount = 0;
        $successCount = 0;


        while (($row = fgetcsv($file)) !== FALSE) {

            $tenderNo     = trim($row[1]);
            $itemType     = trim($row[5], " \t\n\r\0\x0B.");
            $itemCode     = trim($row[7]);
            $itemName     = trim($row[8], " \t\n\r\0\x0B.");
            $unit         = trim($row[9]);
            $quantity     = (float) str_replace(',', '', $row[11]);
            $rate         = (float) str_replace(',', '', $row[12]);


            $tender = Tender::where('tenderid', $tenderNo)->first();


            $division = TenderDivision::firstOrCreate(
                ['division' => $itemType]
            );


            if ($tender) {
                TenderItem::create([
                    'tender_id'     => $tender->id,
                    'division_id'   => $division->id,
                    'item_code'     => $itemCode,
                    'item_name'     => $itemName,
                    'item_unit'     => $unit,
                    'item_quantity' => $quantity,
                    'item_rate'     => $rate,
                ]);
                $successCount++;
            } else {
                $this->warn("Skipping row: Tender ID $tenderNo not found in database.");
            }

            $rowCount++;


            if ($rowCount % 100 == 0) {
                $this->line("Processed $rowCount rows...");
            }
        }

        fclose($file);

        $this->info("Import Finished!");
        $this->info("Total Rows Processed: $rowCount");
        $this->info("Successfully Imported: $successCount");
    }
}
