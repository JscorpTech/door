<?php

namespace App\Http\Controllers\RestAPI\v3\seller;

use App\Enums\GlobalConstant;
use App\Events\ChattingEvent;
use App\Http\Controllers\Controller;
use App\Models\Chatting;
use App\Models\DeliveryMan;
use App\Models\Seller;
use App\Models\Shop;
use App\Models\User;
use App\Utils\FileManagerLogic;
use App\Utils\Helpers;
use App\Utils\ImageManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{

    public function list(Request $request, $type)
    {
        $seller = $request->seller;

        if ($type == 'customer') {
            $withParam = 'customer';
            $idParam = 'user_id';
        } elseif ($type == 'delivery-man') {
            $withParam = 'deliveryMan';
            $idParam = 'delivery_man_id';
        } else {
            return response()->json(['message' => translate('Invalid Chatting Type!')], 403);
        }

        $total_size = Chatting::where(['seller_id' => $seller['id']])
            ->whereNotNull($idParam)
            ->select($idParam)
            ->distinct()
            ->get()
            ->count();

        $allChatIds = Chatting::where(['seller_id' => $seller['id']])
            ->whereNotNull($idParam)
            ->select('id', $idParam)
            ->latest()
            ->get()
            ->unique($idParam)
            ->toArray();

        $uniqueChatIds = array_slice($allChatIds, $request->offset - 1, $request->limit);

        $chats = [];
        if ($uniqueChatIds) {
            foreach ($uniqueChatIds as $uniqueChatId) {
                $userChatting = Chatting::with([$withParam])
                    ->where(['seller_id' => $seller['id'], $idParam => $uniqueChatId[$idParam]])
                    ->whereNotNull($idParam)
                    ->latest()
                    ->first();

                $userChatting->unseen_message_count = Chatting::where(['seller_id' => $seller['id'], $idParam => $userChatting[$idParam], 'seen_by_seller' => '0'])->count();
                $chats[] = $userChatting;
            }
        }

        $data = array(
            'data' => $seller,
            'total_size' => $total_size,
            'limit' => $request['limit'] ?? '',
            'offset' => $request['offset'] ?? '',
            'chat' => $chats,
        );

        return response()->json($data, 200);
    }

    public function search(Request $request, $type)
    {
        $seller = $request->seller;

        $terms = explode(" ", $request->input('search'));
        if ($type == 'customer') {
            $with_param = 'customer';
            $id_param = 'user_id';
            $users = User::where('id', '!=', 0)
                ->when($request->search, function ($query) use ($terms) {
                    foreach ($terms as $term) {
                        $query->where('f_name', 'like', '%' . $term . '%')
                            ->orWhere('l_name', 'like', '%' . $term . '%');
                    }
                })->pluck('id')->toArray();

        } elseif ($type == 'delivery-man') {
            $with_param = 'deliveryMan';
            $id_param = 'delivery_man_id';
            $users = DeliveryMan::where(['seller_id' => $seller['id']])
                ->when($request->search, function ($query) use ($terms) {
                    foreach ($terms as $term) {
                        $query->where('f_name', 'like', '%' . $term . '%')
                            ->orWhere('l_name', 'like', '%' . $term . '%');
                    }
                })->pluck('id')->toArray();
        } else {
            return response()->json(['message' => translate('Invalid Chatting Type!')], 403);
        }

        $unique_chat_ids = Chatting::where(['seller_id' => $seller['id']])
            ->whereIn($id_param, $users)
            ->select($id_param)
            ->distinct()
            ->get()
            ->toArray();
        $unique_chat_ids = call_user_func_array('array_merge', $unique_chat_ids);

        $chats = array();
        if ($unique_chat_ids) {
            foreach ($unique_chat_ids as $unique_chat_id) {
                $chats[] = Chatting::with([$with_param])
                    ->where(['seller_id' => $seller['id'], $id_param => $unique_chat_id])
                    ->whereNotNull($id_param)
                    ->latest()
                    ->first();
            }
        }

        return response()->json($chats, 200);
    }

    public function get_message(Request $request, $type, $id)
    {

        $seller = $request->seller;
        $validator = Validator::make($request->all(), [
            'offset' => 'required',
            'limit' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }
        if ($type == 'customer') {
            $id_param = 'user_id';
            $sent_by = 'sent_by_customer';
            $with = 'customer';
        } elseif ($type == 'delivery-man') {
            $id_param = 'delivery_man_id';
            $sent_by = 'sent_by_delivery_man';
            $with = 'deliveryMan';

        } else {
            return response()->json(['message' => translate('Invalid Chatting Type!')], 403);
        }

        $query = Chatting::with($with)->where(['seller_id' => $seller['id'], $id_param => $id])->latest();

        if (!empty($query->get())) {
            $message = $query->paginate($request->limit, ['*'], 'page', $request->offset);
            $message?->map(function ($conversation) {
                if (!is_null($conversation->attachment_full_url) && count($conversation->attachment_full_url) > 0) {
                    $attachmentData = [];
                    foreach ($conversation->attachment_full_url as $key => $attachment) {
                        $attachmentData[] = (object)$this->getAttachmentData($attachment);
                    }
                    $conversation->attachment = $attachmentData;
                } else {
                    $conversation->attachment = [];
                }
            });

            if ($query->where($sent_by, 1)->latest()->first()) {
                $query->where($sent_by, 1)->latest()->first()
                    ->update(['seen_by_seller' => 1]);
            }

            $data = array(
                'data' => $seller,
                'total_size' => $message->total(),
                'limit' => $request->limit,
                'offset' => $request->offset,
                'message' => $message->items(),
            );
            return response()->json($data, 200);
        }
        return response()->json(['message' => translate('no messages found!')], 200);

    }

    public function send_message(Request $request, $type)
    {
        $seller = $request->seller;

        $uploadMaxFileSize = str_replace('M', '', ini_get('upload_max_filesize'));
        $maximumUploadSize = $uploadMaxFileSize * 1024 > 2048 ? ($uploadMaxFileSize * 1024) : 2048;
        $maximumUploadSize = $maximumUploadSize > (10 * 1024) ? (10 * 1024) : $maximumUploadSize;

        $validator = Validator::make($request->all(), [
                'id' => 'required',
                'message' => 'required_without_all:file,media',
                'media.*' => 'max:' . $maximumUploadSize . '|mimes:' . str_replace('.', '', implode(',', GlobalConstant::MEDIA_EXTENSION)),
                'file.*' => 'file|max:2048|mimes:' . str_replace('.', '', implode(',', GlobalConstant::DOCUMENT_EXTENSION)),
            ],
            [
                'required_without_all' => translate('type_something') . '!',
                'media.mimes' => translate('the_media_format_is_not_supported') . ' ' . translate('supported_format_are') . ' ' . str_replace('.', '', implode(',', GlobalConstant::MEDIA_EXTENSION)),
                'media.max' => translate('media_maximum_size') . ' ' . ($maximumUploadSize / 1024) . ' MB',
                'file.mimes' => translate('the_file_format_is_not_supported') . ' ' . translate('supported_format_are') . ' ' . str_replace('.', '', implode(',', GlobalConstant::DOCUMENT_EXTENSION)),
                'file.max' => translate('file_maximum_size_') . MAXIMUM_MEDIA_UPLOAD_SIZE,
            ]
        );
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $attachment = [];
        if ($request->file('media')) {
            foreach ($request['media'] as $image) {
                if (in_array('.' . $image->getClientOriginalExtension(), GlobalConstant::VIDEO_EXTENSION)) {
                    $attachment[] = [
                        'file_name' => ImageManager::file_upload(dir: 'chatting/', format: $image->getClientOriginalExtension(), file: $image),
                        'storage' => getWebConfig(name: 'storage_connection_type') ?? 'public',
                    ];
                } else {
                    $attachment[] = [
                        'file_name' => ImageManager::upload('chatting/', 'webp', $image),
                        'storage' => getWebConfig(name: 'storage_connection_type') ?? 'public',
                    ];
                }
            }
        }
        if ($request->file('file')) {
            foreach ($request['file'] as $file) {
                $attachment[] = [
                    'file_name' => ImageManager::file_upload(dir: 'chatting/', format: $file->getClientOriginalExtension(), file: $file),
                    'storage' => getWebConfig(name: 'storage_connection_type') ?? 'public',
                ];
            }
        }

        $shop_id = Shop::where('seller_id', $seller['id'])->first()->id;
        $messageForm = Seller::find($seller['id']);

        $chatting = new Chatting();
        $chatting->seller_id = $seller->id;
        $chatting->message = $request['message'];
        $chatting->attachment = json_encode($attachment);
        $chatting->sent_by_seller = 1;
        $chatting->seen_by_seller = 1;
        $chatting->shop_id = $shop_id;

        if ($type == 'delivery-man') {
            $chatting->delivery_man_id = $request->id;
            $chatting->seen_by_delivery_man = 0;
            $chatting->notification_receiver = 'deliveryman';

            $deliveryMan = DeliveryMan::find($request->id);
            event(new ChattingEvent(key: 'message_from_seller', type: 'delivery_man', userData: $deliveryMan, messageForm: $messageForm));
        } elseif ($type == 'customer') {
            $chatting->user_id = $request->id;
            $chatting->seen_by_customer = 0;
            $chatting->notification_receiver = 'customer';

            $customer = User::find($request->id);
            event(new ChattingEvent(key: 'message_from_seller', type: 'customer', userData: $customer, messageForm: $messageForm));
        } else {
            return response()->json(translate('Invalid_Chatting_Type'), 403);
        }

        if ($chatting->save()) {
            return response()->json(['message' => $request['message'], 'time' => now(), 'attachment' => $attachment], 200);
        } else {
            return response()->json(['message' => translate('Message_sending_failed')], 403);
        }
    }

    private function getAttachmentData($attachment): array
    {
        $extension = strrchr($attachment['path'], '.');
        if (in_array($extension, GlobalConstant::DOCUMENT_EXTENSION)) {
            $type = 'file';
        } else {
            $type = 'media';
        }
        $path = $attachment['status'] == 200 ? $attachment['path'] : null;
        $size = $attachment['status'] == 200 ? FileManagerLogic::getFileSize(path: $path) : null;
        return [
            'type' => $type,
            'key' => $attachment['key'],
            'path' => $path,
            'size' => $size
        ];
    }

    public function seenMessage(Request $request, $type): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        if ($type == 'delivery-man') {
            $idParam = 'delivery_man_id';
        } elseif ($type == 'customer') {
            $idParam = 'user_id';
        } else {
            return response()->json(['message' => translate('Invalid_Chatting_Type')], 403);
        }

        $seller = $request->seller;
        $chatting = Chatting::where(['seller_id' => $seller['id'], $idParam => $request['id']])->update(['seen_by_seller' => 1]);

        if ($chatting) {
            return response()->json(['message' => translate('Successfully_seen')], 200);
        } else {
            return response()->json(['message' => translate('Fail')], 403);
        }
    }
}
