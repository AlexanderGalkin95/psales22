<?php

namespace App\Http\Requests;

use App\Traits\FormRequestTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ValidateCreateUserRequest extends FormRequest
{
    use FormRequestTrait;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $this->message = 'У вас недостаточно прав для создания пользователя';
        return Auth::user()->hasRole(['sa', 'pm']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|integer|exists:roles,id',
            'password' => 'required',
            'confirm'  => 'same:password',
            'duo' => 'boolean',
            'phone' => 'string|regex:/^[0-9]*$/|min:10|max:15|nullable|required_if:duo,true|unique:users,phone',
            'telegram' => 'string|nullable|unique:users,telegram',
        ];
    }
}
