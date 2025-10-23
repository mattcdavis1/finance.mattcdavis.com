<?php

namespace App\Console\Commands\Investments\Plainzer\Transactions;

use Illuminate\Console\Command;
use App\Models\Account;
use App\Services\Plainzer\Transaction as PlainzerTransaction;

class SyncDown extends Command
{
  protected $signature = 'plainzer:sync-down.transactions
    {--account-slug=}
  ';
  protected $description = 'Push transactions from local database to Plainzer';

  public function handle()
  {
    $accountSlug = $this->option('account-slug');
    $account = Account::where('slug', $accountSlug)
      ->first();

    $plainzer = app(PlainzerTransaction::class);
    $plainzer->syncDown();
  }
}
