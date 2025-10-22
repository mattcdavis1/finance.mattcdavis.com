<?php

namespace App\Services\Plainzer;

use App\Models\Security as SecurityModel;
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
    $response = $this->request(options: $options);

    foreach ($response->result as $transaction) {
      $account = AccountModel::where('external_id', $transaction->portfolio->portfolioId)
        ->orWhere('alias', $transaction->portfolio->name)
        ->first();

      $query = TransactionModel::where('external_id', $transaction->transactionId);
      if ($transaction->externalTransactionId) {
        $query->orWhere('externalTransactionId', $transaction->externalTransactionId);
      }

      $model = $query->first();

      if (!$model) {
        $security = SecurityModel::where('external_id', $transaction->ticker->tickerId)->first();
        if (!$security) {
          SecurityModel::where('ticker', $transaction->ticker->symbol)->first();
        }

        $query = TransactionModel::whereRaw("DATE_FORMAT(date, '%Y-%m-%d') = ?", [ $transaction->tradeDate ])
          ->where('units', $transaction->quantity)
          ->where('unit_price', $transaction->costPerItem)
          ->where('type', $transaction->type)
          ->where('account_id', $account->id)
        ;

        if ($security) {
          $query->where('security_id', $security->id);
        } else {
          $query->where('symbol', $transaction->ticker->symbol);
        }

        $model = $query->firstOrNew();
      }

      $model->fillFromPlainzer($transaction);
      $model->save();
    }
  }
}
