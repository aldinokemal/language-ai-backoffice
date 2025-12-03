<?php

namespace Modules\Public\Http\Controllers\Api;

use App\Enums\StorageSource;
use App\Http\Controllers\Controller;
use App\Models\DB1\ClassAttendance;
use App\Models\DB1\ClassModel;
use App\Models\DB1\ClassParticipant;
use App\Models\DB1\ClassSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventRegistrationController extends Controller
{
    public function eventDetail($id)
    {
        $event = ClassModel::with('schedules')->find(customDecrypt($id));

        if (! $event) {
            return response()->json([
                'message' => 'Event not found',
                'data' => null,
            ], 404);
        }

        return responseJSON(
            'Event detail',
            encryptModel($event),
        );
    }

    public function eventParticipantImage($id, Request $request)
    {
        $event = ClassModel::find(customDecrypt($id));

        if (! $event) {
            return response()->json([
                'message' => 'Event not found',
                'data' => null,
            ], 404);
        }

        $data = $event->participants->map(function ($participant) {
            $face_recognition_data_path = $participant->face_recognition_data_path ?? [];
            $face_recognition_data_path = array_map(function ($item) {
                return Storage::disk(StorageSource::S3->value)->temporaryUrl($item, now()->addMinutes(5));
            }, $face_recognition_data_path);

            return [
                'id' => customEncrypt($participant->id),
                'name' => $participant->name,
                'face_recognition_data_path' => $face_recognition_data_path,
            ];
        });

        return responseJSON(
            'Event participant images',
            $data,
        );
    }

    public function eventRegistration($id, Request $request)
    {
        $input = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:255',
            'company' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'signature' => 'required|string',
            'photo_front' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'photo_side' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'photo_top' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'photo_bottom' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        $event = ClassModel::find(customDecrypt($id));

        if (! $event) {
            return response()->json([
                'message' => 'Event not found',
                'data' => null,
            ], 404);
        }

        $participant = $event->participants()->updateOrCreate(
            ['email' => $input['email']],
            $input
        );

        $uploadedPaths = [];
        $photoFields = [
            'photo_front',
            'photo_side',
            'photo_top',
            'photo_bottom',
        ];

        foreach ($photoFields as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $extension = $file->getClientOriginalExtension() ?: 'jpg';
                $filename = $field.'-'.Str::uuid().'.'.$extension;
                $basePath = 'training/'.$event->id.'/participant/'.$participant->id;

                // Store to S3 and capture the object key (path)
                $objectKey = Storage::disk(StorageSource::S3->value)->putFileAs($basePath, $file, $filename);

                // Save key under its logical name (front/side/top/bottom)
                $uploadedPaths[$field] = $objectKey;
            }
        }

        if (! empty($uploadedPaths)) {
            $existing = $participant->face_recognition_data_path ?? [];
            $participant->face_recognition_data_path = array_merge($existing, $uploadedPaths);
            $participant->save();
        }

        return response()->json([
            'message' => 'Event registration success',
            'data' => [
                'face_recognition_data_path' => $participant->face_recognition_data_path,
            ],
        ]);
    }

    public function eventPresenceFaceRecognition($id, $participant_id, Request $request)
    {
        $id = customDecrypt($id);
        $participant_id = customDecrypt($participant_id);
        $today = now();

        $participant = ClassParticipant::query()->where('class_id', $id)->where('id', $participant_id)->first();
        if (! $participant) {
            return responseJSON(
                'Participant not found',
                null,
                404
            );
        }

        // Find today's schedule for this class (by date)
        $schedule = ClassSchedule::query()
            ->where('class_id', $id)
            ->whereDate('start_time', $today->toDateString())
            ->first();

        if (! $schedule) {
            return responseJSON(
                'Presence is not available today for this class',
                null,
                403
            );
        }

        $classAttendance = ClassAttendance::query()
            ->where('class_id', $id)
            ->where('class_participant_id', $participant_id)
            ->where('schedule_id', $schedule->id)
            ->first();

        if (! $classAttendance) {
            $classAttendance = new ClassAttendance;
            $classAttendance->class_id = $id;
            $classAttendance->class_participant_id = $participant_id;
            $classAttendance->schedule_id = $schedule->id;
            $classAttendance->save();
        }

        return responseJSON(
            'Event presence face recognition success',
            null,
        );
    }
}
