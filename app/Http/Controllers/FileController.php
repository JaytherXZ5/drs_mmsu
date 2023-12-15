<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Administrative;
use App\Models\Archive;
use App\Models\Degree;
use App\Models\File;
use App\Models\Folder;
use App\Models\Institution;
use App\Models\Ppp;
use App\Models\PsvArea;
use App\Models\SurveyFile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class FileController extends Controller
{
    public function getFiless($id){

            $files = File::where('folder_id', $id)->with(['user'])->get();
            // ... rest of your code

        
        //$path = storage_path('app/public/uploads/BSinfoTech/Aldrin/1701754681.txt');
        //dd(filesize($path) / (1024 * 1024) );
        return response()->json(['files' => $files]);
        
    }
    public function getFilessss($id) // Assuming $id is the folder_id
{
    try {
        // Get files based on the folder_id
        $files = File::where('folder_id', $id)->with(['user', 'psv_area', 'surveyFiles'])->get();

        // Return the files with related data
        return response()->json(['files' => $files]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

    public function getFiles($id){//id is folder
        try {
            // Get files based on the folder_id
            $files = File::where('folder_id', $id)->with(['psv_area'])->get();
    
            // Initialize an array to store survey files
            $surveyFiles = [];
    
            // Loop through each file and get associated survey files
            foreach ($files as $file) {
                $fileId = $file->id;
    
                // Get survey files for the current file
                $fileSurveyFiles = SurveyFile::where('file_id', $fileId)->with(['file', 'psv_area'])->get();
    
                // Append to the overall surveyFiles array
                $surveyFiles[] = [
                    'file' => $file,
                    'files' => $fileSurveyFiles,
                ];
            }
    
            return response()->json(['files' => $surveyFiles]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    
}

    public function getFileUser($id){
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    public function get_folder_files($id){
        try {
            $user = Auth::user();
            $user_role = Auth::user()->role_id;
    
            if ($user_role == 1) {
                $folder = Folder::where('id', $id)
                    ->where('administrative_id', $user->administrative_id)
                    ->firstOrFail();
            } elseif ($user_role == 2 || $user_role == 4) {
                $folder = Folder::where('id', $id)
                    ->where('degree_id', $user->degree_id)
                    ->firstOrFail();
            }elseif($user_role == 3 || $user_role == 5){
                $folder = Folder::where('id', $id)
                    ->where('institution_id', $user->institution_id)
                    ->firstOrFail();
            }
    
            // Check if the folder was found
            if ($folder) {
                // Fetch files belonging to the folder
                $files = File::where('folder_id', $folder->id)->get();
                //folder
                return response()->json([
                    'success' => true,
                    'folder' => $folder,
                    'files' => $files,
                ], 200);
            }
    
            return response()->json([
                'success' => false,
                'message' => 'Folder not found.',
            ], 404);
    
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function renameFile(Request $request, $id)
    {
        $file = File::findOrFail($id);

        if (!$file) {
            return response()->json(['message' => 'File not found'], 404);
        }

        $data = $request->validate([
            'name' => 'required|string',
        ]);

        $existingFile = File::where('name', $data['name'])
        ->where('id', '!=', $file->id)
        ->first();

        if ($existingFile) {
            return response()->json(['error' => 'Name already exists'], 422);
        }

        $file->update($data);

        return response()->json($file);
    }
  

public function deleteFile($id){
    $file = File::findOrFail($id);
    $user = User::findOrFail($file->user_id);
    
    $folder = Folder::findOrFail($file->folder_id)->name;
    $filename = $file->name_generate;
    
   // Storage::delete($file->path);
    

    $officeFolder = null;
    $degree_id = null;
    $institution_id = null;
    $administrative_id = null;
    if($user->degree_id !== 0){
        $officeFolder = $user->degree->abbr;
        $degree_id = $user->degree->id;
    }else if($user->institution_id !== 0){
        $officeFolder = $user->institution->name;
        $institution_id = $user->institution->id;
    }else if($user->administrative_id !==0){
        $officeFolder = $user->administrative->name;
        $administrative_id = $user->administrative->id;
    }


   // $file->storeAs('uploads/'.$officeFolder.'/archives', $filename, 'public');
   
    $path = 'public/uploads/'.$officeFolder.'/'.$folder.'/'.$filename;

    $newPath = 'public/uploads/Archives/' . $officeFolder.'/'. $filename;
    Storage::move($path, $newPath);

    $newFilename = time() . '_' . uniqid() . '.' . $file->extension;

    
    $archive = new Archive();
    $archive->user_id = $file->user_id;
    $archive->name = $file->name;
    $archive->type = $file->type;
    $archive->size = $file->size;
    $archive->name_generate = $file->name_generate;
    $archive->degree_id = $degree_id;
    $archive->institution_id = $institution_id;
    $archive->administrative_id = $administrative_id;
    $archive->save();
    $file->surveyFiles()->delete();
    $file->delete();
    
    //unlink(storage_path($path));


    
   

    return response()->json([
        'archive'=>$archive,
        'message'=>'file moved to archive succesfully'
    ]);
    //to archive

}

public function uploadFiles(Request $request, $id)
{
    if (!$request->hasFile('files') || !$request->file('files')) {
        return response()->json(['error' => 'No files were uploaded.'], 400);
    }


    $request->validate([
        'files.*' => 'required|mimes:png,jpg,pdf,html,txt,docx,xlsx,csv|max:10000',
    ]);

    $uploadedFiles = [];

    foreach ($request->file('files') as $file) {

        $fileSize = $file->getSize(); // Get file size in bytes

        // Check if the file size exceeds the limit (10,000 KB)
        if ($fileSize > 10000 * 1024) {
            return response()->json(['error' => 'File exceeded recommended file size.'], 400);
        }

        $filename = time(). '_'.uniqid() . '.' . $file->extension();
        $user_role = Auth::user()->role_id;
        $folder = Folder::findOrFail($id)->name;
        $user = Auth::user();
        $fileStorage = null;
       
        if ($user_role == 1) {
            $fileStorage = $user->administrative->name;
        }elseif($user_role == 2 || $user_role == 4){
            $fileStorage = $user->degree->abbr;

        }elseif($user_role == 3 || $user_role == 5){
            $fileStorage = $user->institution->name;
        }

        $file->storeAs('uploads/'.$fileStorage.'/'.$folder, $filename, 'public');
       
        $fileModel = new File();
        $fileModel->name = $file->getClientOriginalName();
        $fileModel->name_generate = $filename;
        $fileModel->type = $file->guessExtension();
        $fileModel->size = $file->getSize();
        $fileModel->folder_id = $id;
        $fileModel->user_id = $user->id;
        $fileModel->save();

        // Add file details to the response array
        $uploadedFiles[] = [
            'id' => $fileModel->id,
            'name' => $fileModel->name,
            'type' => $fileModel->type,
            'size' => $fileModel->size,
        ];
    }

    return response()->json(['files' => $uploadedFiles]);
}

public function uploadSurveyFiles(Request $request, $id)
{
    if (!$request->hasFile('files') || !$request->file('files')) {
        return response()->json(['error' => 'No files were uploaded.'], 400);
    }

    $request->validate([
        'files.*' => 'required|mimes:png,jpg,pdf,html,txt,docx,xlsx,csv|max:10000',
    ]);

    $uploadedFiles = [];

    foreach ($request->file('files') as $file) {

        if($request->input('survey_instrument_id')=== null ){

        }

        $existingFile = File::where('folder_id', $id)
        ->where('name', $file->getClientOriginalName())
        ->first();


        if ($existingFile) {
            return response()->json(['error' => 'File Already Exists'], 400);
        }

        $fileSize = $file->getSize(); // Get file size in bytes

        // Check if the file size exceeds the limit (10,000 KB)
        if ($fileSize > 10000 * 1024) {
            return response()->json(['error' => 'File exceeded recommended file size.'], 400);
        }



        $filename = time(). '_'.uniqid() . '.' . $file->extension();
        $user_role = Auth::user()->role_id;
        $folder = Folder::findOrFail($id)->name;
        $user = Auth::user();
        $fileStorage = null;
       
        if ($user_role == 1) {
            $fileStorage = $user->administrative->name;
        }elseif($user_role == 2 || $user_role == 4){
            $fileStorage = $user->degree->abbr;

        }elseif($user_role == 3 || $user_role == 5){
            $fileStorage = $user->institution->name;
        }

        $file->storeAs('uploads/'.$fileStorage.'/'.$folder, $filename, 'public');
       
        $fileModel = new File();
        $fileModel->name = $file->getClientOriginalName();
        $fileModel->name_generate = $filename;
        $fileModel->type = $file->guessExtension();
        $fileModel->size = $file->getSize();
        $fileModel->description = $request->input('description');
        $fileModel->folder_id = $id;
        $fileModel->user_id = $user->id;
        $fileModel->psv_area_id = $request->input('psv_area_id');
        $fileModel->save();

        $ppps = new Ppp([
            'psv_area_id' => $request->input('psv_area_id'),
            'user_id'=> $user->id,
        ]);
    
        $ppps->save();



        $survey_file = new SurveyFile([
            'ppps_id' => $ppps->id,
            'file_id' => $fileModel->id,
            'survey_instrument_id' => $request->input('survey_instrument_id'),
            'compliance_report_id' => $request->input('compliance_report_id'),
            'psv_area_id' => $request->input('psv_area_id'),
        ]);

        $survey_file->save();


        // Add file details to the response array
        $uploadedFiles[] = [
            'id' => $fileModel->id,
            'name' => $fileModel->name,
            'type' => $fileModel->type,
            'size' => $fileModel->size,
        ];
    }

    return response()->json(['files' => $uploadedFiles]);
}
public function getSurveyFile($id){
    $surveyFile = SurveyFile::where('file_id', $id)->get();
    $ppps = Ppp::where('id',$surveyFile->ppps_id)->get();
    $psv_area = PsvArea::where('id', $ppps->psv_area_id)->get();

    return response()->json([
        'surveyFile' =>$surveyFile,
        'ppps' => $ppps,
        '$psv_area' => $psv_area


    ]);
}

}

