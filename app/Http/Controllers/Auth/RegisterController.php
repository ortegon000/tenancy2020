<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\User;
use Hyn\Tenancy\Environment;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use Hyn\Tenancy\Models\Hostname;
use Hyn\Tenancy\Models\Website;
use Hyn\Tenancy\Repositories\HostnameRepository;
use Hyn\Tenancy\Repositories\WebsiteRepository;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Tenant\User as TenantUser;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    protected $tenantName = null;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');

        $hostname = app(Environment::class)->hostname();

        if ($hostname) {
            $fqdn = $hostname->fqdn;
            $this->tenantName = explode('.', $fqdn)[0];
        }
    }

    public function showRegistrationForm()
    {
        return view('auth.register')->with('tenantName', $this->tenantName);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        if ( ! $this->tenantName) {
            $fqdn = $data['fqdn'] . "." . env('APP_DOMAIN');

            return Validator::make($data, [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'fqdn' => ['required', 'string', 'max:20', Rule::unique('hostnames')
                    ->where( function ($query) use ($fqdn) {
                        return $query->where('fqdn', $fqdn);
                    })],
                'password' => ['required', 'string', 'min:3', 'confirmed'],
            ]);
        }

        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:tenant.users'],
            'password' => ['required', 'string', 'min:3', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $user = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ];

        if ( ! $this->tenantName) {
            return User::create($user);
        }

        return TenantUser::create($user);
    }


    protected function registered(Request $request, $user)
    {
        if ( ! $this->tenantName) {
            $website = $this->website();

            $hostname = $this->hostname($request);

            app(HostnameRepository::class)->attach($hostname, $website);
        }
    }

    protected function website()
    {
        $website = new Website;

        $website->uuid = Str::random(10);

        return app(WebsiteRepository::class)->create($website);
    }

    protected function hostname(Request $request)
    {
        $hostname = new Hostname();

        $hostname->fqdn = $request->fqdn . "." . env('APP_DOMAIN');

        return app(HostnameRepository::class)->create($hostname);
    }
}
