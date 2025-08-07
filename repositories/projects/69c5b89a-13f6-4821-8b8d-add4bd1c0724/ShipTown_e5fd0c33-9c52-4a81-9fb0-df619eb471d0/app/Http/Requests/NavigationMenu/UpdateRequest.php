<?php

namespace App\Http\Requests\NavigationMenu;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $navigationMenu = $this->route('navigation_menu');
        $id = $navigationMenu instanceof \App\Models\NavigationMenu ? $navigationMenu->id : $navigationMenu;

        return [
            'name' => 'required|string|min:3|max:100|unique:navigation_menu,name,' . $id,
            'url' => 'required|string|min:3|max:999',
            'group' => 'required|min:3|max:100|in:picklist,packlist,reports',
        ];
    }
}
