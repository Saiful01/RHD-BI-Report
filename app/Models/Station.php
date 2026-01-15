<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Station extends Model
{
    use SoftDeletes, HasFactory;

    public $table = 'stations';

    public const STATUS_RADIO = [
        '1' => 'Active',
        '0' => 'Inactive',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'station_name',
        'lat',
        'lon',
        'elevation',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function stationDailyWeathers()
    {
        return $this->hasMany(DailyWeather::class, 'station_id', 'id');
    }



    protected static function booted()
    {
        static::addGlobalScope('station_name_alpha_first', function (Builder $builder) {
            $builder

                ->orderByRaw("station_name REGEXP '[0-9]' ASC")

                ->orderBy('station_name', 'asc');
        });
    }

}
