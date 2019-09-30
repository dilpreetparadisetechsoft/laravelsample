<?php

namespace App\Http\Controllers\Api\Settings;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Settings;
use App\JobStatus;
use App\EmailTemplate;
use Validator, DateTime, Config, Helpers, Hash, DB;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $currentUser = getApiCurrentUser();        
        $jobStatuses = JobStatus::where('comp_id', $currentUser->comp_id)->get();
        $emailTemplates = EmailTemplate::where('comp_id', $currentUser->comp_id)->get();
        $settings = getSettings($currentUser->comp_id);

        return response()->json(['status'=>true, 'message'=>'All Assigned Tasks', 'response'=>compact('jobStatuses','settings','emailTemplates')], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $currentUser = getApiCurrentUser();
        $settings = (array)$request->input('settings');
        if (!empty($settings) && is_array($settings)) {
            foreach ($settings as $key => $value) {
                if (!$setting = Settings::where('comp_id', $currentUser->comp_id)->where('key',$key)->get()->first()) {
                    $setting = new Settings();
                    $setting->created_at = new DateTime;
                    $setting->key = $key;
                    $setting->comp_id = $currentUser->comp_id;
                }
                $setting->value = maybe_encode($value);
                $setting->updated_at = new DateTime;
                $setting->save();
            }
        }

        $settings = getSettings($currentUser->comp_id);
        return Response()->json(['status'=>'success', 'message'=>'Setting Updated', 'response' => compact('settings')], 200);
    }

    
}
