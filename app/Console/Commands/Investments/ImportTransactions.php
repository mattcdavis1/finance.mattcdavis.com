<?php

namespace App\Console\Commands\Investments;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;
use League\Csv\Statement;

class ImportTransactions extends Command
{
  protected $signature = 'app:investments.import-transactions
    {--path=}
  ';
  protected $description = 'Import Transactions';

  public function handle()
  {
    $csvPath = Storage::path($this->option('path'));
    $csv = Reader::createFromPath($csvPath, 'r');
    $csv->setHeaderOffset(0);

    $stmt = new Statement();
    $rows = $stmt->process($csv);

    foreach ($rows as $row) {
      $account = Account::where('alias', $row['Account'])->first();
      $model = Transaction::firstOrNew([
        'type' => $row['Type'],
        'date' => $row['Date'],
        'symbol' => $row['Symbol'],
        'units' => $row['Units'],
        'unit_price' => $row['Unit Price'],
        'account_id' => $account->id,
      ]);

      $model->date_bank_processed = $model->date;

      $model->save();
    }
  }
}
