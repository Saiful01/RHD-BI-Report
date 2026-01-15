<?php

namespace App\Http\Requests;

use App\Models\DailyWeather;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class UpdateDailyWeatherRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('daily_weather_edit');
    }

    public function rules()
    {
        return [
            'station_id' => [
                'required',
                'integer',
            ],
            'max_temp' => [
                'numeric',
            ],
            'mini_temp' => [
                'numeric',
            ],
            'avg_temp' => [
                'numeric',
            ],
            'humidity' => [
                'numeric',
            ],
            'dry_bulb' => [
                'numeric',
            ],
            'dew_point' => [
                'numeric',
            ],
            'total_rain_fall' => [
                'numeric',
            ],
            'total_sunshine_hour' => [
                'numeric',
            ],
            'record_date' => [
                'required',
                'date_format:' . config('panel.date_format'),
            ],
        ];
    }
}
