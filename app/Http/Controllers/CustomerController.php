<?php

namespace App\Http\Controllers;

use App\Url;
use App\Customer;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\DeletedUrls;
use App\Services\UrlService;
use App\Http\Requests\ShortUrl;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;


class CustomerController extends Controller
{
    /**
     * @var UrlService
     */
    protected $url;

    /**
     * UrlController constructor.
     * @param UrlService $urlService
     */
    public function __construct(UrlService $urlService)
    {
        $this->middleware('throttle:30', ['only' => ['store', 'update', 'checkExistingUrl']]);

        $this->url = $urlService;
    }

    /**
     * Show the admin all the Customer Lists.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showCustomersList()
    {
        return view('customer.list');
    }

    /**
     * AJAX load of all the Short URLs to show in the admin URLs list.
     *
     * @return mixed
     * @throws \Exception
     */
    public function loadCustomersList()
    {
        // Here we add a column with the buttons to show analytics and edit short URLs.
        // There could be a better way to do this.
        // TODO: Really NEED to find a better way to handle this. It's horrible.
        $dataTable = Datatables::of(\App\Customer::get()->take(50))
         ->addColumn('action', function ($row) {
            return '<a href="/customer/generateUrl/'.$row->id.'">
                    <button type="button" class="btn btn-success btn-sm btn-url-edit">
                        <i class="fa fa-pencil-alt" alt="Edit"> 
                        </i> '.trans('customer.generate').'</button></a>';
         })
         ->addColumn('check', function ($row1) {
            return '<input type="checkbox" name="customerList" value="'.$row1->id.'">';
         })
        ->rawColumns(['action','check'])
        ->make(true);

        return $dataTable;
    }

    /**
     * Generate URLs from Customer data.
     *
     * @return mixed
     * @throws \Exception
     */
    public function generateUrlFromCustomer(Request $request, $custId){
        $customerData = Customer::where('id',$custId)->first()->toArray();
        $custName = $customerData['customer_name'];
        $custEmail = $customerData['email_id'];
        $custMobile = $customerData['mobile_number'];

        $data['hideUrlStats'] = 0;
        $data['url'] = 'http://demonew.shaze.in/?utm_source=newsletter&utm_medium=email&utm_campaign=spring_sale&'.
                             'utm_term=email%3D'.$custEmail.'%26mobile%3D'.$custMobile.'%26name='.$custName;
                             
        $data['privateUrl'] = 0;
        $data['customUrl'] = null;
        //$data = $request->validated();
        $siteUrl = request()->getHttpHost();

        //If user is not logged in, he can't set private statistics,
        //because otherwise they will not be available to anybody else but admin
        if (! Auth::check()) {
            $data['hideUrlStats'] = 0;
        }

        if ($this->url->customUrlExisting($data['customUrl'])) {
            return Redirect::route('home')
                ->with('existingCustom', $data['customUrl']);
        }

        if ($existing = $this->url->checkExistingLongUrl($data['url'])) {
             return Redirect::route('home')
                 ->with('existing', $existing)
                 ->with('siteUrl', $siteUrl);
        }

        $short = $this->url->shortenUrl($data['url'], $data['customUrl'], $data['privateUrl'], $data['hideUrlStats']);

        return Redirect::route('home')
            ->with('success', $short)
            ->with('siteUrl', $siteUrl);
    }
}
