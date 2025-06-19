<?php

namespace App\Http\Controllers\RestAPI\v3\seller;

use App\Http\Controllers\Controller;
use App\Models\EmergencyContact;
use App\Utils\Helpers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmergencyContactController extends Controller
{
    public function list(Request $request): JsonResponse
    {
        $seller = $request->seller;
        $contactList = EmergencyContact::where(['user_id' => $seller->id])
            ->when($request->has('search') && $request['search'], function ($query) use ($request) {
                return $query->where('name', 'like', '%' . $request['search'] . '%')
                    ->orWhere('phone', 'like', '%' . $request['search'] . '%');
            })->latest()->get();

        return response()->json(['contact_list' => $contactList], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phone' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $seller = $request->seller;
        EmergencyContact::create([
            'user_id' => $seller->id,
            'name' => $request['name'],
            'phone' => $request['phone'],
            'status' => 1
        ]);
        return response()->json(['message' => translate('emergency_contact_added_successfully!')], 200);
    }

    public function update(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phone' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $seller = $request->seller;
        $emergencyContact = EmergencyContact::where(['user_id' => $seller->id])->find($request->id);
        if (!$emergencyContact) {
            return response()->json(['message' => translate('invalid_emergency_contact!')], 403);
        }
        $emergencyContact->name = $request['name'];
        $emergencyContact->phone = $request['phone'];
        $emergencyContact->update();

        return response()->json(['message' => translate('emergency_contact_updated_successfully!')], 200);
    }

    public function status_update(Request $request): JsonResponse
    {
        $seller = $request->seller;
        $status = EmergencyContact::where(['user_id' => $seller->id, 'id' => $request->id])
            ->update(['status' => $request['status']]);
        if ($status) {
            return response()->json(['message' => translate('contact_status_update_successfully!')], 200);
        } else {
            return response()->json(['message' => translate('contact_status_update_failed!')], 403);
        }
    }

    public function destroy(Request $request): JsonResponse
    {
        $seller = $request->seller;
        $delete = EmergencyContact::where(['user_id' => $seller->id, 'id' => $request->id])->delete();
        if ($delete) {
            return response()->json(['message' => translate('emergency_contact_deleted_successfully!')], 200);
        } else {
            return response()->json(['message' => translate('emergency_contact_delete_failed!')], 403);
        }
    }
}
