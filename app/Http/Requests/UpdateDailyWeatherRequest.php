<?php

namespace App\Http\Requests;

use App\Models\DailyWeather;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

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
                'nullable',
                'numeric',
            ],
            'mini_temp' => [
                'nullable',
                'numeric',
            ],
            'avg_temp' => [
                'nullable',
                'numeric',
            ],
            'humidity' => [
                'nullable',
                'numeric',
            ],
            'dry_bulb' => [
                'nullable',
                'numeric',
            ],
            'dew_point' => [
                'nullable',
                'numeric',
            ],
            'total_rain_fall' => [
                'nullable',
                'numeric',
            ],
            'total_sunshine_hour' => [
                'nullable',
                'numeric',
            ],
            'record_date' => [
                'required',
                'date',
            ],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        if ($this->ajax() || $this->wantsJson()) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422));
        }

        parent::failedValidation($validator);
    }
}
