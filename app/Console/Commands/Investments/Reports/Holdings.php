<?php

namespace App\Console\Commands\Investments\Reports;

// use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Console\Command;

class Holdings extends Command
{
  protected $signature = 'app:investments.reports.holdings
    {--account-alias=}
  ';
  protected $description = 'List holdings';

  public function handle()
  {
    $query = Transaction::selectRaw('
      SUM(units) as units,
      SUM(amount) as cost,
      securities.ticker,
      accounts.alias
    ')
      ->join('securities', 'transactions.security_id', '=', 'securities.id')
      ->join('accounts', 'transactions.account_id', '=', 'accounts.id')
      ->groupByRaw('security_id, securities.ticker, accounts.alias')
      ->orderBy('accounts.alias', 'asc')
      ->orderBy('securities.ticker', 'asc')
      ->having('units', '>', 0);

    $accountAlias = null;
    foreach ($query->get() as $holding) {
      if ($accountAlias != $holding->alias) {
        $this->comment(PHP_EOL . $holding->alias);
        $this->comment('Ticker | Units    | Cost');
      }
      $this->info(str_pad($holding->ticker, 7) . '| ' . str_pad($holding->units, 9) . '| ' . $holding->cost);
      $accountAlias = $holding->alias;
    }
  }
}
