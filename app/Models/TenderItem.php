<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenderItem extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'tender_id',
        'division_id',
        'item_code',
        'hs_code',
        'item_name',
        'item_unit',
        'item_quantity',
        'item_rate'
    ];



    public function tender(): BelongsTo
    {
        return $this->belongsTo(Tender::class);
    }

    /**
     * Division এর সাথে রিলেশন (Belongs To)
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(TenderDivision::class, 'division_id');
    }
}
