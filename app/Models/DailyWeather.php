<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DailyWeather extends Model
{
    use SoftDeletes, HasFactory;

    public $table = 'daily_weathers';

    protected $dates = [
        'record_date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'station_id',
        'max_temp',
        'mini_temp',
        'avg_temp',
        'humidity',
        'dry_bulb',
        'dew_point',
        'total_rain_fall',
        'total_sunshine_hour',
        'record_date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function station()
    {
        return $this->belongsTo(Station::class, 'station_id');
    }

    public function getRecordDateAttribute($value)
    {
        return $value ? Carbon::parse($value)->format(config('panel.date_format')) : null;
    }

    public function setRecordDateAttribute($value)
    {
        if (!$value) {
            $this->attributes['record_date'] = null;
            return;
        }

        // Try to parse the date in various formats
        try {
            // First try the panel date format (d/m/Y)
            $this->attributes['record_date'] = Carbon::createFromFormat(config('panel.date_format'), $value)->format('Y-m-d');
        } catch (\Exception $e) {
            try {
                // Try Y-m-d format
                $this->attributes['record_date'] = Carbon::createFromFormat('Y-m-d', $value)->format('Y-m-d');
            } catch (\Exception $e) {
                // Fallback to Carbon parse
                $this->attributes['record_date'] = Carbon::parse($value)->format('Y-m-d');
            }
        }
    }
}
