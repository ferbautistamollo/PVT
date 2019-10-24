<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use App\Affiliate;
class AffiliateForm extends FormRequest
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
        $this->sanitize();
        $rules = [
            'first_name' => 'alpha_spaces|min:3',
            'last_name' => 'alpha_spaces|min:3',
            'gender' => 'in:M,F',
            'birth_date' => 'date_format:"Y-m-d"',
            'city_birth_id' => 'exists:cities,id',
            'city_identity_card_id' => 'exists:cities,id',
            'civil_status' => 'in:C,D,S,V',
            'identity_card' => 'min:3',
            'affiliate_state_id' => 'nullable|exists:affiliate_states,id',
            'degree_id' => 'nullable|exists:degrees,id',
            'pension_entity_id' => 'nullable|exists:pension_entities,id',
            'mothers_last_name' =>'nullable|alpha_spaces|min:3',
            'second_name' =>'nullable|alpha_spaces|min:3',
            'date_death' => 'nullable|date_format:"Y-m-d"',
            'date_entry' => 'nullable|date_format:"Y-m-d"',
            'date_derelict' => 'nullable|date_format:"Y-m-d"',
            'due_date' => 'nullable|date_format:"Y-m-d"',
            'surname_husband' => 'nullable|alpha_spaces|min:3'
        ];
        switch ($this->method()) {
            case 'POST': {
                foreach (array_slice($rules, 0, 8) as $key => $rule) {
                    $rules[$key] = implode('|', ['required', $rule]);
                }
                $rules['identity_card'] = implode('|', ['unique:affiliates', $rules['identity_card']]);
                return $rules;
            }
            case 'PUT':
            case 'PATCH':{
                return $rules;
            }
        }
       return $rules;

    }
    public function sanitize(){
        $input = $this->all();
        if (array_key_exists('first_name', $input)) $input['first_name'] = mb_strtoupper($input['first_name']);
        if (array_key_exists('second_name', $input)) $input['second_name'] = mb_strtoupper($input['second_name']);
        if (array_key_exists('last_name', $input)) $input['last_name'] = mb_strtoupper($input['last_name']);
        if (array_key_exists('mothers_last_name', $input)) $input['mothers_last_name'] = mb_strtoupper($input['mothers_last_name']);
        if (array_key_exists('reason_death', $input)) $input['reason_death'] = mb_strtoupper($input['reason_death']);
        if (array_key_exists('identity_card', $input)) $input['identity_card'] = mb_strtoupper($input['identity_card']);
        if (array_key_exists('surname_husband', $input)) $input['surname_husband'] = mb_strtoupper($input['surname_husband']);
        if (array_key_exists('gender', $input)) $input['gender'] = mb_strtoupper($input['gender']);
        if (array_key_exists('civil_status', $input)) $input['civil_status'] = mb_strtoupper($input['civil_status']);

        $this->replace($input);
    }
    public function messages(){
        return[
            'first_name.required' => 'El campo Primer Nombre del Afiliado es requerido',
            'first_name.alpha_spaces' => 'El campo Primer Nombre del Afiliado solo puede contener letras y espacios.',
            'first_name.min' =>'El campo Primer Nombre del Afiliado debe ser de al menos :min',
            'last_name.required' => 'El campo Apellido Paterno del Afiliado es requerido',
            'last_name.alpha_spaces' => 'El campo Apellido Paterno del Afiliado solo puede contener letras y espacios.',
            'last_name.min' =>'El campo Apellido Paterno del Afiliado debe ser de al menos :min',
            'gender.in' =>'La selección genero del Afiliado es inválida',
            'gender.required' =>'El campo Genero del Afiliado es requerido',
            'birth_date.required' =>'Elcampo Fecha de nacimiento del afiliado es requerido',
            'birth_date.date_format' =>'El campo fecha de nacimiento del Afiliado no coincide con el formato :format.',
            'city_birth_id.required' => 'El campo Ciudad de nacimiento del Afiliado es requerido',
            'city_birth_id.exists' =>'La selección Ciudad de nacimiento del Afiliado es inválida.', 
            'city_identity_card_id.required' => 'El campo Ciudad de expedición del Afiliado es requerido',
            'city_identity_card_id.exists' =>'La selección Ciudad de expedición del Afiliado es inválida',
            'civil_status.required' =>'El campo Estado Civil del Afiliado es requerido',
            'civil_status.in' =>'La selección Estado Civil del Afiliado es inválida',
            'identity_card.min' =>'El campo Carnet de Identidad del Afiliado debe ser de almenos :min',
            'identity_card.unique' =>'El campo Carnet de Identidad del Afiliado ya existe.',
            'pension_entity_id.exists' =>'La selección Entidad de pensiones del Afiliado es inválida.',
            'mothers_last_name.alpha_spaces' =>'El campo Apellido Materno del Afiliado solo puede contener letras y espacios.',
            'mothers_last_name.min' =>'El campo Apellido Materno del Afiliado debe ser de al menos :min',
            'second_name.alpha_spaces' =>'El campo Segundo Nombre del Afiliado solo puede contener letras y espacios.',
            'second_name.min' =>'El campo Segundo Nombre del Afiliado debe ser de al menos :min',
            'date_death.date_format' =>'El campo fecha de fallecimiento del Afiliado no coincide con el formato :format.',
            'date_entry.date_format' =>'El campo fecha de ingreso del Afiliado no coincide con el formato :format.',
            'date_derelict.date_format' =>'El campo fecha de culminación del Afiliado no coincide con el formato :format.',
            'due_date.date_format' =>'El campo fecha de vencimiento de CI del Afiliado no coincide con el formato :format.',
            'surname_husband.min' =>'El campo Apellido del conyugue del Afiliado debe ser de al menos :min',
            'surname_husband.alpha_spaces' =>'El campo Apellido del conyugue del Afiliado solo puede contener letras y espacios.',
          
        ];
    } 
}





