<?php

namespace Modules\WebDefacement\Entities;

use Illuminate\Database\Eloquent\Model;


class WebdefacmentDataCheck extends Model
{

	protected $table = 'webdefacment_data_check';
	protected $fillable = [];


	public static function getData($id)
	{
		$res = WebdefacmentDataCheck::where('webdefacment_setting_id', $id)
			->latest() 
			->first();  

		return $res ?? '-';
	}
}
