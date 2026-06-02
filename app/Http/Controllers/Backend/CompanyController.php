<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;
use App\Services\ApiPayloadCache;

class CompanyController extends Controller
{
    protected $moduleName;

    public function __construct()
    {
        //Module Name
        $this->moduleName = 'Company';
        view()->share('moduleName', $this->moduleName);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Start building the query
        $query = Company::with('meta');

        // Filter by company_id if authenticated user has one, otherwise, return all companies
        $companyId = Auth::user()?->company_id;
        if ($companyId) {
            $query->where('id', $companyId);
        }

        // Execute the query and get the results
        $pageData = $query->get();

        return view('backend.companies.index', compact('pageData'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.companies.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $company = Company::create([
                'name' => $request->input('name'),
                'logo' => $request->input('logo'),
                'footer_logo_image' => $request->input('footer_logo_image'),
                'short_description' => $request->input('short_description'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'address' => $request->input('address'),
                'website' => $request->input('website'),
                'google_map' => $request->input('google_map'),
                'meta_title' => $request->input('meta_title'),
                'meta_description' => $request->input('meta_description'),
                'is_active' => $request->input('is_active', 1),
            ]);

            // Handle meta fields
            $metaFields = $request->input('meta', []);
            foreach ($metaFields as $key => $value) {
                if (!empty($value)) {
                    $company->meta()->create([
                        'meta_key' => $key,
                        'meta_value' => $value,
                    ]);
                }
            }

            ApiPayloadCache::invalidateCompany((int) $company->id);

            return redirect()->route('companies.edit', $company->id)->with('success', 'Company created successfully!');
        } catch (\Exception $e) {
            Log::error('Error creating company', [
                'error_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('companies.create')->with('error', 'There was an error creating the company.');
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
    public function edit(string $id)
    {
        $query = Company::with('meta');

        $companyId = Auth::user()?->company_id;
        if ($companyId) {
            $query->where('id', $companyId);
        }

        $pageData = $query->findOrFail($id);

        return view('backend.companies.edit', compact('pageData'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {     
            $company = Company::findOrFail($id);
        
            // Update main company fields
            // Use input defaults to avoid wiping existing values if a field isn't sent.
            $company->name = $request->input('name', $company->name);
            $company->logo = $request->input('logo', $company->logo);
            $company->footer_logo_image = $request->input('footer_logo_image', $company->footer_logo_image);
            $company->short_description = $request->input('short_description', $company->short_description);
            $company->email = $request->input('email', $company->email);
            $company->phone = $request->input('phone', $company->phone);
            $company->address = $request->input('address', $company->address);
            $company->website = $request->input('website', $company->website);
            $company->google_map = $request->input('google_map', $company->google_map);
            $company->meta_title = $request->input('meta_title', $company->meta_title);
            $company->meta_description = $request->input('meta_description', $company->meta_description);
            $company->save();
        
            // Handle meta fields
            $metaFields = $request->input('meta', []); // Get all meta fields from the request

            foreach ($metaFields as $key => $value) {
                // Check if the meta key exists for the current company
                $existingMeta = $company->meta()->where('meta_key', $key)->first();
            
                if ($existingMeta) {
                    // If the meta key exists, update it regardless of $value being empty
                    $existingMeta->update(['meta_value' => $value]);
                } else {
                    // If the meta key does not exist, create a new record only if $value is not empty
                    if (!empty($value)) {
                        $company->meta()->create([
                            'meta_key' => $key,
                            'meta_value' => $value
                        ]);
                    }
                }
            }            

            ApiPayloadCache::invalidateCompany((int) $company->id);

            return redirect()->route('companies.edit', $company->id)->with('success', 'Company details updated successfully!');

        } catch (\Exception $e) {
            // Log the error message with a detailed stack trace
            Log::error('Error updating company details for company ID ' . $id, [
                'error_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
    
            // Optionally, return a failure message to the user
            return redirect()->route('companies.edit', $id)->with('error', 'There was an error updating the company details.');
        }
    }
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
