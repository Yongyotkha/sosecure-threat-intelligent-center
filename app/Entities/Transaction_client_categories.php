<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Entities\Categories;


class Transaction_client_categories extends Model
{
	protected $table = 'transaction_client_categories';
	protected $primaryKey = 'id';
	public $incrementing = true;
	protected $dates   = ['created_at', 'updated_at'];

	public function get_transfer_client()
	{
		return $this->hasOne(Categories::class, 'id', 'transaction_id');
	}
}
