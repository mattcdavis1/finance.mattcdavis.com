<?php

namespace App\Console\Commands\Investments\Plainzer\Transactions;

use Illuminate\Console\Command;
use App\Services\Plainzer\Transaction as PlainzerTransaction;

class SyncUp extends Command
{
  protected $signature = 'investments:plainzer.sync-up-transactions
    {--account-id=}
  ';
  protected $description = 'Push transactions from local database to Plainzer';

  public function handle()
  {
    app(PlainzerTransaction::class)
      ->syncUp(
        accountId: $this->option('account-id'),
        unsyncedOnly: true,
      );
  }
}
