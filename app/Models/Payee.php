<?php namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;

class Payee extends Model
{
    protected $table = 'payees';
    protected $fillable = [
        'name',
        'active',
    ];
}
