<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use Illuminate\Routing\Controller as BaseController;

class UserController extends BaseController
{

    protected $module;    

    public function __construct()
    {
        $this->module = 'users';
        view()->share('module', $this->module);

        $this->middleware('permission:users view')->only(['index', 'show']);
        $this->middleware('permission:users create')->only(['create', 'store']);
        $this->middleware('permission:users edit')->only(['edit', 'update']);
        $this->middleware('permission:users delete')->only(['destroy']);         
    } 

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get the search parameter from the request
        $search = request()->input('search');
    
        // Start building the query
        $query = User::with('role')
        ->when($search, function($query) use ($search) {
            $query->where(function($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhereHas('role', function($query) use ($search) {
                        $query->where('name', 'like', '%'.$search.'%');
                    });
            });
        })
        ->orderBy('id', 'desc');

        $pageData = $query->paginate(10);
    
        // Return the view with data
        return view('backend.' . $this->module . '.index', compact('pageData'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Fetch all roles for the dropdown
        $roles = Role::all();
        return view('backend.' . $this->module . '.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
        // Validate form data
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'name' => 'required|string|min:3|max:200',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'is_active' => 'required|boolean',
        ]);
    
        try {

            // Insert the user record
            $user = User::create([
                'role_id' => $request->role_id,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_active' => $request->is_active,
            ]);

            $role = Role::find($request->role_id);
            if ($role) {
                $user->assignRole($role->name); // Sync Spatie role system
            }            
    
            // Return success response
            return response()->json(['status' => true, 'notification' => __('messages.created')]);
            
        } catch (\Exception $e) {
            // Return error response
            return response()->json(['status' => false, 'notification' => __('messages.failed')]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // Find the user and fetch all roles for the dropdown
        $user = User::findOrFail($id);
        $roles = Role::all();
        return view('backend.' . $this->module . '.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Validate form data
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'name' => 'required|string|min:3|max:200',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6',
            'is_active' => 'required|boolean',
        ]);
    
        try {
            // Find the user
            $user = User::findOrFail($id);
    
            // Update user data
            $user->role_id = $request->role_id;
            $user->name = $request->name;
            $user->email = $request->email;
    
            // Only update password if provided
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }
    
            $user->is_active = $request->is_active;
            $user->save();

            $role = Role::find($request->role_id);
            if ($role) {
                $user->syncRoles($role->name); // Sync Spatie role system
            }             
    
            // Return success response
            return response()->json(['status' => true, 'notification' => __('messages.updated')]);
    
        } catch (\Exception $e) {
            // Return error response
            return response()->json(['status' => false, 'notification' => __('messages.failed')]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            // Attempt to delete the record
            User::destroy($id);
            // Redirect back with a success message
            return redirect()->route($this->module . '.index')->with('success', __('messages.deleted'));
        } catch (\Exception $e) {
            // Redirect back with an error message
            return redirect()->route($this->module . 'index')->with('error', __('messages.failed'));
        }
    }
}
