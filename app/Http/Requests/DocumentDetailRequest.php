<?php

namespace App\Http\Requests;

use Dingo\Api\Http\FormRequest as Request;

class DocumentDetailRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'status' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'status.required' => 'document_detail_error_status_required'
        ];
    }
}
