<?php

namespace Modules\SiteSettings\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\SiteSettings\Entities\SiteSettings;
use Modules\SiteSettings\Http\Requests\SiteSettingsRequest;
// use Modules\Clients\Transformers\ClientResource;
// use Modules\Clients\Transformers\ClientsResource;
// use Modules\Contacts\Transformers\ContactsResource;
// use Modules\Deals\Transformers\DealsResource;
// use Modules\Estimates\Transformers\EstimatesResource;
// use Modules\Expenses\Transformers\ExpensesResource;
// use Modules\Invoices\Transformers\InvoicesResource;
// use Modules\Payments\Transformers\PaymentsResource;
// use Modules\Projects\Transformers\ProjectsResource;
// use Modules\Subscriptions\Transformers\SubscriptionsResource;
// use Modules\Users\Entities\User;

class SiteSettingApiController extends Controller
{
    /**
     * Client model
     *
     * @var \Modules\Clients\Entities\SiteSettings
     */
    protected $SiteSettings;
    /**
     * Request instance
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;
    /**
     * Client logo directory path
     *
     * @var string
     */
    protected $site_dir;

    /**
     * Create a new controller instance.
     */
    public function __construct(Request $request)
    {
        $this->middleware('localize');
        $this->request   = $request;
        $this->SiteSettings    = new SiteSettings;
        $this->site_dir = config('system.site_dir') . '/';
    }

    public function index()
    {
        $SiteSettings = new SiteSettings(
            $this->SiteSettings->with(['name,descript,logo,address,remark,active'])
                ->orderByDesc('id')
                ->paginate(40)
        );
        return response($SiteSettings, Response::HTTP_OK);
    }

    /**
     * Show the specified resource.
     *
     * @return Response
     */
    public function show($id = null)
    {
        $SiteSettings = $this->SiteSettings->findOrFail($id);
        return response(new SiteSettings($SiteSettings), Response::HTTP_OK);
    }

    public function save(SiteSettingsRequest $request)
    {
        // dd($request);
        // $this->authorize('create', SiteSettings::class);
        // $SiteSettings = $this->SiteSettings->create($request->all());
        $SiteSettings = $this->SiteSettings;
        $SiteSettings->name = $request->name;
        $SiteSettings->descript = $request->descript;
        $SiteSettings->address = $request->address;
        $SiteSettings->remark = $request->remark;
        $SiteSettings->active = $request->active ? 1 : 0;
        $SiteSettings->save();

        if ($request->hasFile('logo')) {
            $this->uploadLogo($request, $SiteSettings);
        }

        // if (!empty($request->contact_email)) {
        //     $user = User::create(
        //         [
        //             'username' => $request->contact_email,
        //             'email'    => $request->contact_email,
        //             'name'     => $request->contact_name,
        //             'password' => 'secret',
        //         ]
        //     );
        //     $user->profile->update(
        //         [
        //             'company' => $client->id,
        //             'country' => $client->country,
        //         ]
        //     );
        //     // $SiteSettings->update(['primary_contact' => $user->id]);
        // }
        // if ($request->hasFile('logo')) {
        //     $this->uploadLogo($request, $SiteSettings);
        // }
        return ajaxResponse(
            [
                'id'       => $SiteSettings->id,
                'message'  => langapp('saved_successfully'),
                'redirect' => route('sitesettings.index'),
            ],
            true,
            Response::HTTP_CREATED
        );
    }

    public function update(SiteSettingsRequest $request, $id = null)
    {
        // dd($request);
        // exit();
        $SiteSettings = $this->SiteSettings->findOrFail($id);
        // $SiteSettings->update($request->all());
        $SiteSettings->name = $request->name;
        $SiteSettings->descript = $request->descript;
        $SiteSettings->address = $request->address;
        $SiteSettings->remark = $request->remark;
        $SiteSettings->active = $request->active ? 1 : 0;
        $SiteSettings->save();

        if ($request->hasFile('logo')) {
            $this->uploadLogo($request, $SiteSettings);
        }
        return ajaxResponse(
            [
                'id'       => $SiteSettings->id,
                'message'  => langapp('changes_saved_successful'),
                'redirect' => route('sitesettings.index'),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function change_status(SiteSettingsRequest $request)
    {
        // dd($request);
        // exit();
        $data['site_id'] = $this->request->site_id;
        $SiteSettings = $this->SiteSettings->findOrFail($data['site_id']);
        // $SiteSettings->update($request->all());
        // $SiteSettings->name = $request->name;
        $SiteSettings->active = $SiteSettings->active == 1 ? 0 : 1;
        $SiteSettings->save();

        return ajaxResponse(
            [
                'id'       => $SiteSettings->id,
                'message'  => langapp('changes_saved_successful'),
                'redirect' => route('sitesettings.index'),
            ],
            true,
            Response::HTTP_OK
        );
    }

       /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id = null)
    {
        $model = $this->SiteSettings->find($id);
        // dd($model);
        $model->delete();
        return ajaxResponse(
            [
                'message'  => langapp('deleted_successfully'),
                'redirect' => route('sitesettings.index'),
            ],
            true,
            Response::HTTP_OK
        );
    }

    private function uploadLogo($request, $SiteSettings)
    {
        $currentLogo = $SiteSettings->getOriginal('logo');
        if (\Storage::exists($this->site_dir . $currentLogo)) {
            \Storage::delete($this->site_dir . $currentLogo);
        }
        \Storage::putFile($this->site_dir, $request->file('logo'), 'public');
        $SiteSettings->update(['logo' => $request->logo->hashName()]);
    }
    /**
     * Show the specified client contacts.
     *
     * @return \Illuminate\Http\Response|\Illuminate\View\View
     */
    public function contacts($id = null)
    {
        $client   = $this->client->findOrFail($id);
        $contacts = new ContactsResource($client->contacts()->paginate(16));
        if ($this->request->has('json')) {
            $data['contacts'] = $contacts;
            return view('clients::_ajax._contacts')->with($data);
        }
        return response($contacts, Response::HTTP_OK);
    }
    /**
     * Show the specified client contacts.
     *
     * @return \Illuminate\Http\Response|\Illuminate\View\View
     */
    public function projects($id = null)
    {
        $client   = $this->client->findOrFail($id);
        $projects = new ProjectsResource($client->projects()->orderBy('id', 'desc')->paginate(16));
        if ($this->request->has('json')) {
            $data['projects'] = $projects;
            return view('clients::_ajax._projects')->with($data);
        }
        return response($projects, Response::HTTP_OK);
    }

    /**
     * Show the specified client contacts.
     *
     * @return \Illuminate\Http\Response|\Illuminate\View\View
     */
    public function invoices($id = null)
    {
        $client   = $this->client->findOrFail($id);
        $invoices = new InvoicesResource($client->invoices()->orderBy('id', 'desc')->paginate(16));
        if ($this->request->has('json')) {
            $data['invoices'] = $invoices;
            return view('clients::_ajax._invoices')->with($data);
        }
        return response($invoices, Response::HTTP_OK);
    }

    /**
     * Show the specified client contacts.
     *
     * @return \Illuminate\Http\Response|\Illuminate\View\View
     */
    public function estimates($id = null)
    {
        $client    = $this->client->findOrFail($id);
        $estimates = new EstimatesResource($client->estimates()->orderBy('id', 'desc')->paginate(16));
        if ($this->request->has('json')) {
            $data['estimates'] = $estimates;
            return view('clients::_ajax._estimates')->with($data);
        }
        return response($estimates, Response::HTTP_OK);
    }
    /**
     * Show the specified client contacts.
     *
     * @param  string $id
     * @return \Illuminate\Http\Response|\Illuminate\View\View
     */
    public function payments($id = null)
    {
        $client   = $this->client->findOrFail($id);
        $payments = new PaymentsResource($client->payments()->orderBy('id', 'desc')->paginate(20));
        if ($this->request->has('json')) {
            $data['payments'] = $payments;
            return view('clients::_ajax._payments')->with($data);
        }
        return response($payments, Response::HTTP_OK);
    }
    /**
     * Show client subscriptions
     *
     * @param  string $id
     * @return \Illuminate\Http\Response|\Illuminate\View\View
     */
    public function subscriptions($id = null)
    {
        $client        = $this->client->findOrFail($id);
        $subscriptions = new SubscriptionsResource($client->subscriptions()->orderBy('id', 'desc')->paginate(20));
        if ($this->request->has('json')) {
            $data['subscriptions'] = $subscriptions;
            return view('clients::_ajax._subscriptions')->with($data);
        }
        return response($subscriptions, Response::HTTP_OK);
    }

    /**
     * Show the specified client contacts.
     *
     * @return \Illuminate\Http\Response|\Illuminate\View\View
     */
    public function expenses($id = null)
    {
        $client   = $this->client->findOrFail($id);
        $expenses = new ExpensesResource($client->expenses()->orderBy('id', 'desc')->paginate(16));
        if ($this->request->has('json')) {
            $data['expenses'] = $expenses;
            return view('clients::_ajax._expenses')->with($data);
        }
        return response($expenses, Response::HTTP_OK);
    }

    /**
     * Show the specified client contacts.
     *
     * @return \Illuminate\Http\Response|\Illuminate\View\View
     */
    public function deals($id = null)
    {
        $client = $this->client->findOrFail($id);
        $deals  = new DealsResource($client->deals()->orderBy('id', 'desc')->paginate(16));
        if ($this->request->has('json')) {
            $data['deals'] = $deals;
            return view('clients::_ajax._deals')->with($data);
        }
        return response($deals, Response::HTTP_OK);
    }

 
}
