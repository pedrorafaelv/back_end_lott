<?php

namespace App\Http\Requests\Api\Raffle;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
                'name'=> 'max:100',
                'description' => 'max:255',
                'user_id' => 'required',
                'group_id' => 'required',
                'total_amount' =>'number|digits|min:0',
                'card_amount' =>'number|digits|min:0', 
                'minimun_play'  =>'integer|min:1',
                'maximun_play'  =>'integer|min:5',
                'maximun_user_play' =>'integer|between:5,1000',
                'retention_percent' =>'integer|between:0,25',
                'retention_amount' =>'integer|min:0',
                'admin_retention_percent' =>'integer|between:0,20',
                'admin_retention_amount' =>'integer|min:0',
                // 'raffle_type' =>'',
                // 'privacy' =>'',
                'reward_line' =>'boolean',
                'percent_line' =>'integer|between:0,100',
                'reward_full' =>'boolean',
                'percent_full' =>'integer|between:0,100',
                // 'admin_user' =>'',
                'scheduled_date' =>'date|after_or_equal:start_date',
                'start_date' =>'required|date',
                'end_date' =>'date|after:start_date',
                'created_at' =>'date',
                'updated_at' =>'date',
        ];
    }
}
