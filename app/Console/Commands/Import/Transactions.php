<?php

namespace App\Console\Commands\Import;

use Illuminate\Console\Command;

class Transactions extends Command
{
  protected $signature = 'app:import.transactions
    {account-id=}
  ';
  protected $description = 'Import Transactions';

  public function handle()
  {
    //
  }
}
