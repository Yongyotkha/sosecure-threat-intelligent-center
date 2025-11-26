<?php

namespace Modules\WebDefacement\Entities;

use Illuminate\Database\Eloquent\Model;


class WebdefacmentDataCheck extends Model
{

	protected $table = 'webdefacment_data_check';
	protected $fillable = [];
	protected $casts = [
		'section_diffs' => 'array',
		'assets_add'    => 'array',
		'assets_del'    => 'array',
		'outbound_new_not_whitelisted' => 'array',
	];


	public static function getData($id)
	{
		return WebdefacmentDataCheck::where('webdefacment_setting_id', $id)
			->latest()
			->first();
	}
}
