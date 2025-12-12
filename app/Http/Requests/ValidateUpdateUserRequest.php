<?php

namespace App\Http\Requests;

use App\Traits\FormRequestTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ValidateUpdateUserRequest extends FormRequest
{
    use FormRequestTrait;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $this->message = 'У вас недостаточно прав для обновления данных пользователя';
        return Auth::user()->hasRole(['sa', 'pm']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $id = $this->route('userId');
        return [
            'userId' => 'required|integer|exists:users,id',
            'name' => 'required',
            'email' => [
                'required',
                Rule::unique('users','email')->ignore($id)
            ],
            'duoMode' => 'boolean',
            'phone' => 'string|regex:/^[0-9]*$/|min:10|max:15|nullable|required_if:twoFactorMode,true|unique:users,phone,'.$id.',id',
            'telegram' => 'string|nullable|unique:users,telegram,' . $id,
            'password' => 'nullable',
            'role' => 'required|integer|exists:roles,id',
            'confirm'  => 'same:password',
        ];
    }
}
