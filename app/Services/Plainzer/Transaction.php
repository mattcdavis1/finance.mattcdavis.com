<?php

namespace App\Services\Plainzer;

use App\Models\Security as SecurityModel;
use App\Models\Transaction as TransactionModel;
use App\Models\Account as AccountModel;

class Transaction extends Base {
  protected $path = '/v1/public/transactions';

  public function clean()
  {
    $query = TransactionModel::select('t.*')
      ->from('transactions as t')
      ->join('accounts as a', 't.account_id', '=', 'a.id')
      ->where('a.type', 'brokerage');

    // add security_id to transactions
    foreach ($query->get() as $transaction) {
      if (!$transaction->security_id) {
        if (!$transaction->symbol) {
          $this->logger->error('Missing Ticker for Transaction ' . $transaction->id);
          continue;
        }

        $security = SecurityModel::where('ticker', $transaction->symbol)->first();
        if ($security) {
          $this->logger->info('Updating Transaction ' . $transaction->id);

          $transaction->security_id = $security->id;
          $transaction->save();
        } else {
          $this->logger->error('Missing Security for Ticker ' . $transaction->symbol);
          $securityModel = SecurityModel::create([
            'ticker' => $transaction->symbol,
          ]);
          $securityModel->save();
        }
      } else {
        $this->logger->comment('No Update Needed for Transaction ' . $transaction->id);
      }
    }
  }

  public function create(TransactionModel $transaction)
  {
    $account = $transaction->account;
    $security = $transaction->security;

    $json = [
      'externalTransactionId' => $transaction->uuid,
      'type' => $transaction->type,
      'ticker' => $transaction->security->ticker,
      'quantity' => $transaction->units,
      'costPerItem' => $transaction->unit_price,
      'tradeDate' => $transaction->date->format('Y-m-d'),
      'portfolioId' => $transaction->account->external_id,
    ];
    $response = $this->request('POST', [
      'json' => $json
    ]);

    return $response;
  }

  public function syncUp(
    ?int $accountId = null,
    $unsyncedOnly = true,
  ) {
    $query = TransactionModel::select('t.*')
      ->from('transactions as t')
      ->join('accounts as a', 't.account_id', '=', 'a.id')
      ->where('a.type', 'brokerage');

    if ($accountId) {
      $query->where('t.account_id', $accountId);
    }

    if ($unsyncedOnly) {
      $query->whereNull('t.external_id');
    }

    foreach ($query->get() as $transaction) {
      if ($transaction->external_id) {
        $this->update($transaction);
      } else {
        $this->create($transaction);
      }
    }
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

  public function update(TransactionModel $transaction)
  {
    // @TODO
  }
}
