<?php

namespace App\Http\Requests;

use App\Models\Station;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class StoreStationRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('station_create');
    }

    public function rules()
    {
        return [
            'station_name' => [
                'string',
                'required',
                'unique:stations',
            ],
            'lat' => [
                'numeric',
            ],
            'lon' => [
                'numeric',
            ],
            'elevation' => [
                'nullable',
                'numeric',
            ],
        ];
    }
}
