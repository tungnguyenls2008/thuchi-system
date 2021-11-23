<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class CheckoutOrder
 * @package App\Models
 * @version November 23, 2021, 6:59 am UTC
 *
 * @property string $bill_code
 * @property integer $menu_id
 * @property integer $quantity
 * @property integer $price
 * @property integer $type
 * @property integer $user_id
 */
class CheckoutOrder extends Model
{
    use SoftDeletes;

    use HasFactory;

    public $table = 'checkout_order';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    protected $dates = ['deleted_at'];



    public $fillable = [
        'bill_code',
        'menu_id',
        'quantity',
        'price',
        'type',
        'user_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'bill_code' => 'string',
        'menu_id' => 'integer',
        'quantity' => 'integer',
        'price' => 'integer',
        'type' => 'integer',
        'user_id' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'bill_code' => 'string|max:12',
        'menu_id' => 'integer',
        'quantity' => 'required',
        'price' => 'required',
        'type' => 'integer',
        'user_id' => 'integer',
        'updated_at' => 'nullable',
        'deleted_at' => 'nullable'
    ];


}
