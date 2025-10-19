<?php

namespace App\Services\Plainzer;

// use App\Models\Transaction as TransactionModel;
use App\Models\Account as AccountModel;

class Transaction extends Base {
  protected $path = '/v1/public/transactions';

  public function syncUp()
  {
  }

  public function syncDown(
    ?AccountModel $accountModel = null
  ) {
    $options = [];
    if ($accountModel) {
      $options['query'] = [
        'portfolioId' => $accountModel->external_id,
      ];
    }
    $transactions = $this->request(options: $options);

    foreach ($transactions as $transaction) {
      $debug = $transaction;
    }
  }
}
