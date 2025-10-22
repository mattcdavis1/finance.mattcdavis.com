<?php

namespace App\Models;

use App\Models\Account as AccountModel;
use App\Models\Security as SecurityModel;
use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
  use HasFactory;
  use UsesUuid;

  /**
   * The table associated with the model.
   *
   * @var string
   */
  protected $table = 'transactions';

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'date',
    'date_bank_processed',
    'type',
    'amount',
    'external_id',
    'account_id',
    'source_id',
    'category_id',
    'vendor_id',
    'transfer_from_account_id',
    'transfer_to_account_id',
    'cusip',
    'symbol',
    'security_id',
    'units',
    'unit_price',
    'note',
    'memo',
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'date' => 'datetime',
    'date_bank_processed' => 'datetime',
    'amount' => 'decimal:2',
    'units' => 'decimal:2',
    'unit_price' => 'decimal:2',
  ];

  public function account(): BelongsTo
  {
    return $this->belongsTo(Account::class);
  }

  public function category(): BelongsTo
  {
    return $this->belongsTo(Category::class);
  }

  public function fillFromPlainzer($data) {
    foreach ([
      'external_id' => 'transactionId',
      'type' => 'type',
      'units' => 'quantity',
      'unit_price' => 'costPerItem',
      'amount' => 'totalCost',
      'date' => 'tradeDate',
      'created_at_external' => 'createdOn',
      'updated_at_external' => 'updatedOn',
    ] as $local => $external) {
      $this->{$local} = $data->{$external};
    }

    // security
    $security = SecurityModel::where('external_id', $data->ticker->tickerId)
      ->first();

    if (!$security) {
      $security = SecurityModel::where('ticker', $data->ticker->symbol)
        ->firstOrNew();

      $security->external_id = $data->ticker->tickerId;
      $security->ticker = $data->ticker->symbol;
      $security->stock_name = $data->ticker->name;
      $security->save();
    } else if ($security->ticker != $data->ticker->symbol) {
      $security->ticker = $data->ticker->symbol;
      $security->save();
    }

    $this->security_id = $security->id;

    // account
    $account = AccountModel::where('external_id', $data->portfolio->portfolioId)
      ->first();

    if (!$account) {
      $account = AccountModel::where('alias', $data->portfolio->name)
        ->orWhere('name', $data->portfolio->name)
        ->firstOrNew();

      $account->external_id = $data->portfolio->portfolioId;
      empty($account->alias) && $account->alias = $data->portfolio->name;
      empty($account->name) && $account->name = $data->portfolio->name;

      $account->save();
    }

    $this->account_id = $account->id;
  }

  public function source(): BelongsTo
  {
    return $this->belongsTo(Source::class);
  }

  public function vendor(): BelongsTo
  {
    return $this->belongsTo(Vendor::class);
  }

  public function transferFromAccount(): BelongsTo
  {
    return $this->belongsTo(Account::class, 'transfer_from_account_id');
  }

  public function transferToAccount(): BelongsTo
  {
    return $this->belongsTo(Account::class, 'transfer_to_account_id');
  }
}
