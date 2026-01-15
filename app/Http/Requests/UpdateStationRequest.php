<?php

namespace App\Http\Requests;

use App\Models\Station;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;

class UpdateStationRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('station_edit');
    }

    public function rules()
    {
        return [
            'station_name' => [
                'string',
                'required',
                'unique:stations,station_name,' . request()->route('station')->id,
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
