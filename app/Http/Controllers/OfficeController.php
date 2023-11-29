<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class OfficeController extends Controller
{
    public function getUserOffice($id){
        
            $user = User::findOrFail($id);

            if($user->degree_id !==0){
                $office = $user->degree->degree;
                
                return response()->json([
                    'degree' => $office,
                ]);
            }elseif ($user->institution_id !==0) {
                $office = $user->institution->name;

                return response()->json([
                    'institution' => $office,
                ]);
            }elseif($user->psv_area_id !==0){
                $office = $user->psv_area->area_name;

                return response()->json([
                    'psv_area' => $office,
                ]);
            }elseif($user->admin_office_id !==0){
                $office = $user->admin_office->name;

                return response()->json([
                    'admin_office' => $office,
                ]);
            }

        
    }
}
