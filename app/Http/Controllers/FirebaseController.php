<?php

namespace App\Http\Controllers;

use App\Http\Requests\Request;
use App\Utils\Helpers;
use App\Models\BusinessSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Session;

class FirebaseController extends Controller
{
    protected mixed $messaging;

    public function __construct()
    {
        $this->messaging = app('firebase.messaging');
    }

    public function subscribeToTopic(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
            'topic' => 'required|string',
        ]);

        $token = $request->input('token');
        $topic = $request->input('topic');

        try {
            if($this->messaging){
                $this->messaging->subscribeToTopic($topic, $token);
                return response()->json(['message' => 'Successfully subscribed to topic'], 200);
            }
            return response()->json(['message' => 'Unauthorized'], 401);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
