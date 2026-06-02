<?php

namespace App\Http\Controllers\Backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Upload;
use App\Models\User;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    protected $moduleName;

    public function __construct()
    {
        //Module Name
        $this->moduleName = 'Uploads';
        view()->share('moduleName', $this->moduleName);
    }

    public function index(Request $request)
    {
        //dd(auth()->user()->role_id);
        $all_uploads = Upload::query();
        $search = null;
        $sort_by = null;
        $extension =  $request->extension ?? null;

        if ($request->search != null) {
            $search = $request->search;
            $all_uploads->where('file_original_name', 'like', '%' . $request->search . '%');
        }

        $sort_by = $request->sort;
        switch ($request->sort) {
            case 'newest':
                $all_uploads->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $all_uploads->orderBy('created_at', 'asc');
                break;
            case 'smallest':
                $all_uploads->orderBy('file_size', 'asc');
                break;
            case 'largest':
                $all_uploads->orderBy('file_size', 'desc');
                break;
            default:
                $all_uploads->orderBy('created_at', 'desc');
                break;
        }
        
        if ($extension != null) {
            $all_uploads->where('extension', $extension);
        }

        if (auth()->user()->role_id != 1) {
            //$all_uploads->where('user_id', auth()->user()->id);
            $all_uploads->whereIn('user_id', [auth()->id(), 1]);
        }   
        
        $all_uploads = $all_uploads->paginate(config('custom.pagination_per_media_page'))->appends(request()->query());
        $extensions = Upload::select('extension')->distinct()->pluck('extension');

        return view('backend.uploads.index', compact('all_uploads', 'search', 'sort_by', 'extensions', 'extension'));
    }

    public function create()
    {
        return view('backend.uploads.create');
    }

    public function show_uploader(Request $request)
    {
        return view('backend.uploads.aiz-uploader-modal');
    }
    public function upload(Request $request)
    {
        $type = array(
            "jpg" => "image",
            "jpeg" => "image",
            "png" => "image",
            "svg" => "image",
            "webp" => "image",
            "gif" => "image",
            "mp4" => "video",
            "mpg" => "video",
            "mpeg" => "video",
            "webm" => "video",
            "ogg" => "video",
            "avi" => "video",
            "mov" => "video",
            "flv" => "video",
            "swf" => "video",
            "mkv" => "video",
            "wmv" => "video",
            "wma" => "audio",
            "aac" => "audio",
            "wav" => "audio",
            "mp3" => "audio",
            "zip" => "archive",
            "rar" => "archive",
            "7z" => "archive",
            "doc" => "document",
            "txt" => "document",
            "docx" => "document",
            "pdf" => "document",
            "csv" => "document",
            "xml" => "document",
            "ods" => "document",
            "xlr" => "document",
            "xls" => "document",
            "xlsx" => "document"
        );

        if ($request->hasFile('aiz_file')) {
            $upload = new Upload;
            $extension = strtolower($request->file('aiz_file')->getClientOriginalExtension());

            if (isset($type[$extension])) {
                $upload->file_original_name = null;
                $arr = explode('.', $request->file('aiz_file')->getClientOriginalName());
                for ($i = 0; $i < count($arr) - 1; $i++) {
                    if ($i == 0) {
                        $upload->file_original_name .= $arr[$i];
                    } else {
                        $upload->file_original_name .= "." . $arr[$i];
                    }
                }

                $size = $request->file('aiz_file')->getSize();
                $schoolId = 'media';
                $path = $request->file('aiz_file')->store('uploads/'.$schoolId.'/'.date("Y").'/'.date("m"), 'public');                

                $upload->extension = $extension;
                $upload->file_name = 'storage/'.$path;
                $upload->user_id = Auth::user()->id;
                $upload->type = $type[$upload->extension];
                $upload->file_size = $size;
                $upload->save();

                // makeImageThumbnail($upload->file_name, 150, 150);
                // makeImageThumbnail($upload->file_name, 300, 300);                
            }
            return '{}';
        }
    }

    public function get_uploaded_files(Request $request)
    {
        //$uploads = Upload::where('user_id', Auth::user()->id);
        $uploads = Upload::query();
        if ($request->search != null) {
            $uploads->where('file_original_name', 'like', '%' . $request->search . '%');
        }
        if ($request->sort != null) {
            switch ($request->sort) {
                case 'newest':
                    $uploads->orderBy('created_at', 'desc');
                    break;
                case 'oldest':
                    $uploads->orderBy('created_at', 'asc');
                    break;
                case 'smallest':
                    $uploads->orderBy('file_size', 'asc');
                    break;
                case 'largest':
                    $uploads->orderBy('file_size', 'desc');
                    break;
                default:
                    $uploads->orderBy('created_at', 'desc');
                    break;
            }
        }

        if (auth()->user()->role_id != 1) {
            //$uploads->where('user_id', auth()->user()->id);
            $uploads->whereIn('user_id', [auth()->id(), 1]);
        }
        
        if($request->type != "all"){
            $uploads->where('type', $request->type); //new
        }
         

        return $uploads->paginate(config('custom.pagination_per_media_page'))->appends(request()->query());
    }

    public function destroy($id)
    {
        $upload = Upload::findOrFail($id);
        try {
            unlink(public_path() . '/' . $upload->file_name);
            $upload->delete();
            return redirect()->back()->with('success', __('File deleted successfully'));
        } catch (\Exception $e) {
            $upload->delete();
            return redirect()->back()->with('success', __('File deleted successfully'));
        }
    }

    public function bulk_uploaded_files_delete(Request $request)
    {
        if ($request->id) {
            foreach ($request->id as $file_id) {
                $this->destroy($file_id);
            }
            return 1;
        } else {
            return 0;
        }
    }

    // public function get_preview_files(Request $request)
    // {
    //     $ids = explode(',', $request->ids);
    //     $files = Upload::whereIn('id', $ids)->get();
    //     $new_file_array = [];
    //     foreach ($files as $file) {
    //         $file['file_name'] = my_asset($file->file_name);
    //         if ($file->external_link) {
    //             $file['file_name'] = $file->external_link;
    //         }
    //         $new_file_array[] = $file;
    //     }

    //     return $new_file_array;
    // }

    public function get_preview_files(Request $request)
    {
        $ids = explode(',', $request->ids);
    
        $ids = array_map('intval', $ids); // ensure all IDs are integers
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $files = Upload::whereIn('id', $ids)->orderByRaw("FIELD(id, $placeholders)", $ids)->get();        
    
        $new_file_array = [];
        foreach ($files as $file) {
            $file['file_name'] = my_asset($file->file_name);
            if ($file->external_link) {
                $file['file_name'] = $file->external_link;
            }
            $new_file_array[] = $file;
        }
    
        return $new_file_array;
    }
    

    public function all_file()
    {
        $uploads = Upload::all();
        foreach ($uploads as $upload) {
            try {
                unlink(public_path() . '/' . $upload->file_name);
                $upload->delete();

                flash(__('File deleted successfully'))->success();
            } catch (\Exception $e) {
                $upload->delete();
                flash(__('File deleted successfully'))->success();
            }
        }

        Upload::query()->truncate();

        return back();
    }

    //Download project attachment
    public function attachment_download($id)
    {
        $project_attachment = Upload::find($id);
        try {
            $file_path = public_path($project_attachment->file_name);
            return Response::download($file_path);
        } catch (\Exception $e) {
            flash(__('File does not exist!'))->error();
            return back();
        }
    }
    //Download project attachment
    public function file_info(Request $request)
    {
        $file = Upload::findOrFail($request['id']);
        return view('backend.uploads.info', compact('file'));
    }

    public function generate_all_thumbnails()
    {
        // Standard thumbnail sizes
        $sizes = [
            [150, 150],
            [300, 300],
        ];
    
        // Get all image uploads
        $images = Upload::where('type', 'image')->get();
    
        foreach ($images as $image) {
            foreach ($sizes as [$width, $height]) {
                makeImageThumbnail($image->file_name, $width, $height);
            }
        }
    
        return response()->json([
            'status' => 'success',
            'message' => 'Thumbnails regenerated for all existing images.'
        ]);
    }    
}
