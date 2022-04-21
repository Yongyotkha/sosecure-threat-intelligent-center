<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\TBLRuleCategory;
use App\TBLRuleFiles;

class TBLRuleName extends Model
{
    //
    protected $table = 'rule_name';
    protected $guarded = ['id'];

    public function get_name_category(){
        return $this->belongsTo(TBLRuleCategory::class, 'rule_category_id', 'id');
    }

    public function get_file_category(){
        return $this->belongsTo(TBLRuleFiles::class, 'rule_category_id', 'id');
    }
}
