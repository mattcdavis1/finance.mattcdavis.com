<?php

namespace App\Services\Util;

use Illuminate\Support\Facades\Log;

class Logger
{
  public function confirm($message)
  {
    Log::debug($message);
  }

  public function line($message)
  {
    Log::debug($message);
  }

  public function info($message)
  {
    Log::notice($message);
  }

  public function comment($message)
  {
    Log::warning($message);
  }

  public function question($message)
  {
    Log::warning($message);
  }

  public function error($message)
  {
    Log::error($message);
  }
}
