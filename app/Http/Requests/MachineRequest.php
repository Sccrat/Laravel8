<?php

namespace App\Http\Requests;

use Dingo\Api\Http\FormRequest as Request;

class MachineRequest extends Request
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
      'code' => 'required|unique:wms_machines'
    ];
  }

  public function messages()
  {
    return [
      'code.required' => 'machine_id_error_required',
      'code.unique' => 'machine_id_error_unique'
    ];
  }
}
