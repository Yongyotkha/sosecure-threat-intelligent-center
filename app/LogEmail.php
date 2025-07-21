<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LogEmail extends Model
{
    protected $table = 'logs_emails';
    protected $fillable = ['body', 'to', 'subject', 'status', 'site_id'];
}
