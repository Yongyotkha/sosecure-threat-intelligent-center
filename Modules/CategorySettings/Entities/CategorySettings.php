<?php

namespace Modules\CategorySettings\Entities;

use App\Entities\Tag;
use App\Traits\Actionable;
use App\Traits\Commentable;
use App\Traits\CustomBillable;
use App\Traits\Customizable;
use App\Traits\Emailable;
use App\Traits\Noteable;
use App\Traits\Observable;
use App\Traits\Taggable;
use App\Traits\Todoable;
use App\Traits\Uploadable;
use App\Traits\Vaultable;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Modules\Clients\Observers\ClientObserver;
use Modules\Clients\Scopes\ClientScope;
use Modules\Contracts\Entities\Contract;
use Modules\Creditnotes\Entities\CreditNote;
use Modules\Deals\Entities\Deal;
use Modules\Estimates\Entities\Estimate;
use Modules\Expenses\Entities\Expense;
use Modules\Invoices\Entities\Invoice;
use Modules\Payments\Entities\Payment;
use Modules\Projects\Entities\Project;
use Modules\Users\Entities\Profile;
use Modules\Users\Entities\User;

class CategorySettings extends Model implements HasLocalePreference
{
    // use Notifiable, Observable, SoftDeletes, Actionable, Commentable, Todoable, Vaultable,
    // Taggable, Customizable, Noteable, Uploadable, Emailable, CustomBillable;

    protected $table = "categories";
    public $timestamps = false;
    protected $fillable = [
        'name', 'module', 'color', 'active', 'order', 'description', 'pipeline',
    ];
    // protected $appends = ['contact_person', 'expense_cost', 'outstanding', 'map', 'maplink'];
    protected $dates   = ['deleted_at', 'created_at', 'updated_at'];

    // protected static $observer = ClientObserver::class;
    // protected static $scope    = ClientScope::class;

   
 
    /**
     * Get client contacts.
     */
    // public function contacts()
    // {
    //     return $this->hasMany(Profile::class, 'company')->with('user:id,username,email,name');
    // }


    /**
     * Get contact person profile.
     */
    // public function contact()
    // {
    //     return $this->belongsTo(User::class, 'primary_contact');
    // }

   
   
    public function preferredLocale()
    {
        return $this->locale;
    }
}
