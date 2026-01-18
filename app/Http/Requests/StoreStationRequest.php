<?php

namespace App\Http\Requests;

use App\Models\Station;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

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
                'nullable',
                'numeric',
            ],
            'lon' => [
                'nullable',
                'numeric',
            ],
            'elevation' => [
                'nullable',
                'numeric',
            ],
            'status' => [
                'nullable',
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
