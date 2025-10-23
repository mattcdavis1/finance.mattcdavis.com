<?php

namespace App\Console\Commands\Investments\Plainzer\Transactions;

use Illuminate\Console\Command;
use App\Models\Transaction as TransactionModel;
use App\Services\Plainzer\Transaction as PlainzerTransaction;

class SyncUp extends Command
{
  protected $signature = 'investments:plainzer.sync-up-transactions
    {--account-id=}
    {--portfolio-id=}
  ';
  protected $description = 'Push transactions from local database to Plainzer';

  public function handle()
  {
    $accountId = $this->option('account-id');
    $portfolioId = $this->option('portfolio-id');
    $transactions = TransactionModel::where('account_id', $accountId)
      ->get();
    $plainzer = app(PlainzerTransaction::class);

    foreach ($transactions as $transaction) {
    }
  }
}
