<?php

namespace Modules\CategorySettings\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\CategorySettings\Entities\CategorySettings;
use Modules\CategorySettings\Http\Requests\CategorySettingsRequest;
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

class CategorySettingApiController extends Controller
{
    /**
     * Client model
     *
     * @var \Modules\Clients\Entities\CategorySettings
     */
    protected $CategorySettings;
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
    protected $logos_dir;

    /**
     * Create a new controller instance.
     */
    public function __construct(Request $request)
    {
        $this->middleware('localize');
        $this->request   = $request;
        $this->CategorySettings    = new CategorySettings;
        // $this->logos_dir = config('system.logos_dir') . '/';
    }

    public function index()
    {
        $CategorySettings = new CategorySettings(
            $this->CategorySettings->with(['name,module,color,active,order,description,pipeline'])
                ->orderByDesc('id')
                ->paginate(40)
        );
        return response($CategorySettings, Response::HTTP_OK);
    }

    /**
     * Show the specified resource.
     *
     * @return Response
     */
    public function show($id = null)
    {
        $CategorySettings = $this->CategorySettings->findOrFail($id);
        return response(new CategorySettings($CategorySettings), Response::HTTP_OK);
    }

    public function save(CategorySettingsRequest $request)
    {
        // dd($request);
        // $this->authorize('create', CategorySettings::class);
        // $CategorySettings = $this->CategorySettings->create($request->all());
        $CategorySettings = $this->CategorySettings;
        $CategorySettings->code = generator_uuid();
        $CategorySettings->name = $request->name;
        $CategorySettings->active = $request->active ? 1 : 0;
        $CategorySettings->save();

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
        //     // $CategorySettings->update(['primary_contact' => $user->id]);
        // }
        // if ($request->hasFile('logo')) {
        //     $this->uploadLogo($request, $CategorySettings);
        // }
        return ajaxResponse(
            [
                'id'       => $CategorySettings->id,
                'message'  => langapp('saved_successfully'),
                'redirect' => route('categorysettings.index'),
            ],
            true,
            Response::HTTP_CREATED
        );
    }

    public function update(CategorySettingsRequest $request, $id = null)
    {
        // dd($request);
        // exit();
        $CategorySettings = $this->CategorySettings->findOrFail($id);
        // $CategorySettings->update($request->all());
        $CategorySettings->name = $request->name;
        $CategorySettings->active = $request->active ? 1 : 0;
        $CategorySettings->save();

        // if ($request->hasFile('logo')) {
        //     $this->uploadLogo($request, $client);
        // }
        return ajaxResponse(
            [
                'id'       => $CategorySettings->id,
                'message'  => langapp('changes_saved_successful'),
                'redirect' => route('categorysettings.index'),
            ],
            true,
            Response::HTTP_OK
        );
    }

    public function change_status(CategorySettingsRequest $request)
    {
        // dd($request);
        // exit();
        $data['category_id'] = $this->request->category_id;
        $CategorySettings = $this->CategorySettings->findOrFail($data['category_id']);
        // $CategorySettings->update($request->all());
        // $CategorySettings->name = $request->name;
        $CategorySettings->active = $CategorySettings->active == 1 ? 0 : 1;
        $CategorySettings->save();

        // if ($request->hasFile('logo')) {
        //     $this->uploadLogo($request, $client);
        // }
        return ajaxResponse(
            [
                'id'       => $CategorySettings->id,
                'message'  => langapp('changes_saved_successful'),
                'redirect' => route('categorysettings.index'),
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
        $model = $this->CategorySettings->find($id);
        // dd($model);
        $model->delete();
        return ajaxResponse(
            [
                'message'  => langapp('deleted_successfully'),
                'redirect' => route('categorysettings.index'),
            ],
            true,
            Response::HTTP_OK
        );
    }

    private function uploadLogo($request, $client)
    {
        $currentLogo = $client->getOriginal('logo');
        if (\Storage::exists($this->logos_dir . $currentLogo)) {
            \Storage::delete($this->logos_dir . $currentLogo);
        }
        \Storage::putFile($this->logos_dir, $request->file('logo'), 'public');
        $client->update(['logo' => $request->logo->hashName()]);
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
