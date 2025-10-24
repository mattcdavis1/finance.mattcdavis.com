<?php

namespace App\Console\Commands\Investments\Transactions;

use Illuminate\Console\Command;
use App\Services\Plainzer\Transaction as PlainzerTransaction;

class Clean extends Command
{
  protected $signature = 'app:investments.clean';
  protected $description = 'Clean up Transactions';

  public function handle()
  {
    $plainzer = app(PlainzerTransaction::class);
    $plainzer->setLogger($this);
    $plainzer->clean();
  }
}
