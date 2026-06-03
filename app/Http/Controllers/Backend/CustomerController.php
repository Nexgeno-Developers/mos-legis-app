<?php

namespace App\Http\Controllers\Backend;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Hash;

class CustomerController extends BaseController
{
    protected $module;

    /** @var list<string> */
    protected array $detailKeys = [
        'profile_photo',
        'street_address',
        'city',
        'state',
        'pin_code',
        'country',
        'company_logo',
        'company_name',
        'gstin',
        'pan_number',
        'industry_type',
        'company_size',
    ];

    public function __construct()
    {
        $this->module = 'customers';
        view()->share('module', $this->module);

        $this->middleware('permission:customers view')->only(['index', 'show']);
        $this->middleware('permission:customers create')->only(['create', 'store']);
        $this->middleware('permission:customers edit')->only(['edit', 'update']);
        $this->middleware('permission:customers delete')->only(['destroy']);
    }

    public function index()
    {
        $search = request()->input('search');
        $customerRoleId = $this->customerRoleId();

        $pageData = User::with('details')
            ->where('role_id', $customerRoleId)
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%')
                        ->orWhereHas('details', function ($query) use ($search) {
                            $query->where('detail_key', 'company_name')
                                ->where('detail_value', 'like', '%'.$search.'%');
                        });
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('backend.'.$this->module.'.index', compact('pageData'));
    }

    public function create()
    {
        return view('backend.'.$this->module.'.create', [
            'details' => [],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate($this->validationRules($request));

        try {
            $customerRoleId = $this->customerRoleId();

            $user = User::create([
                'role_id' => $customerRoleId,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'is_active' => $request->is_active,
            ]);

            $user->assignRole('customer');
            $this->syncDetails($user, $request->input('details', []));

            return response()->json(['status' => true, 'notification' => __('messages.created')]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'notification' => __('messages.failed')]);
        }
    }

    public function show(string $id)
    {
        //
    }

    public function edit($id)
    {
        $user = $this->findCustomerOrFail($id);
        $details = $user->details->pluck('detail_value', 'detail_key');

        return view('backend.'.$this->module.'.edit', compact('user', 'details'));
    }

    public function update(Request $request, $id)
    {
        $request->validate($this->validationRules($request, $id));

        try {
            $user = $this->findCustomerOrFail($id);

            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->is_active = $request->is_active;

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();
            $user->syncRoles('customer');
            $this->syncDetails($user, $request->input('details', []));

            return response()->json(['status' => true, 'notification' => __('messages.updated')]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'notification' => __('messages.failed')]);
        }
    }

    public function destroy($id)
    {
        try {
            $this->findCustomerOrFail($id)->delete();

            return redirect()->route($this->module.'.index')->with('success', __('messages.deleted'));
        } catch (\Exception $e) {
            return redirect()->route($this->module.'.index')->with('error', __('messages.failed'));
        }
    }

    protected function customerRoleId(): int
    {
        return (int) Role::where('name', 'customer')->value('id')
            ?? throw new \RuntimeException('Customer role not found. Run RoleSeeder.');
    }

    protected function findCustomerOrFail($id): User
    {
        return User::with('details')
            ->where('role_id', $this->customerRoleId())
            ->findOrFail($id);
    }

    protected function validationRules(Request $request, $userId = null): array
    {
        $emailRule = 'required|email|max:255|unique:users,email';
        if ($userId) {
            $emailRule .= ','.$userId;
        }

        $phoneRule = 'required|string|max:50|unique:users,phone';
        if ($userId) {
            $phoneRule .= ','.$userId;
        }        

        return [
            'name' => 'required|string|min:3|max:200',
            'email' => $emailRule,
            'phone' => $phoneRule,
            'password' => $userId ? 'nullable|string|min:6' : 'required|string|min:6',
            'is_active' => 'required|boolean',
            'details.profile_photo' => 'nullable|string',
            'details.street_address' => 'nullable|string',
            'details.city' => 'nullable|string|max:100',
            'details.state' => 'nullable|string|max:100',
            'details.pin_code' => 'nullable|string|max:20',
            'details.country' => 'nullable|string|max:100',
            'details.company_logo' => 'nullable|string',
            'details.company_name' => 'nullable|string|max:255',
            'details.gstin' => 'nullable|string|max:50',
            'details.pan_number' => 'nullable|string|max:20',
            'details.industry_type' => 'nullable|string|max:100',
            'details.company_size' => 'nullable|string|max:50',
        ];
    }

    protected function syncDetails(User $user, array $details): void
    {
        foreach ($this->detailKeys as $key) {
            if (array_key_exists($key, $details)) {
                $user->details()->updateOrCreate(
                    ['detail_key' => $key],
                    ['detail_value' => $details[$key] ?? '']
                );
            }
        }
    }
}
