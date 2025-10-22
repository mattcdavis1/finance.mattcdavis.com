<?php

namespace App\Services\Plainzer;

use App\Models\Transaction as TransactionModel;
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
      $account = AccountModel::where('external_id', $transaction->portfolio->portfolioId)
        ->orWhere('alias', $transaction->portfolio->name)
        ->first();

      $model = TransactionModel::where('external_id', $transaction->transactionId)
        ->orWhere('uuid', $transaction->externalTransactionId)
        ->first();

      if (!$model) {
        $model = TransactionModel::whereRaw("DATE_FORMAT(date, '%Y-%m-%d')", $transaction->tradeDate)
          ->where('units', $transaction->quantity)
          ->where('unit_price', $transaction->costPerItem)
          ->where('type', $transaction->type)
          ->where('ticker', $transaction->ticker->symbol)
          ->where('account_id', $account->id)
          ->firstOrNew()
        ;
      }

      $model->fillFromPlainzer($transaction);
      $model->save();
    }
  }
}
