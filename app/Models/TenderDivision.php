<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenderDivision extends Model
{
    public $timestamps = false;
   public $table = 'tender_divisions';
   public $fillable = [
       'division',
       'is_active',
   ];




}
