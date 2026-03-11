<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ImageController extends Controller
{
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,gif,webp|max:5120',
        ]);

        $file = $request->file('image');
        $filename = Str::ulid() . '.' . $file->getClientOriginalExtension();
        $fileSize = $file->getSize();
        $mimeType = $file->getMimeType();

        $token = config('services.uploadthing.token');
        $decoded = json_decode(base64_decode($token), true);
        $apiKey = $decoded['apiKey'];

        // Step 1: Request presigned URL
        $prepareRes = Http::withHeaders([
            'x-uploadthing-api-key' => $apiKey,
        ])->post('https://api.uploadthing.com/v7/prepareUpload', [
            'files' => [
                [
                    'name' => $filename,
                    'size' => $fileSize,
                    'type' => $mimeType,
                ],
            ],
            'acl' => 'public-read',
            'contentDisposition' => 'inline',
        ]);

        if (!$prepareRes->successful()) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to prepare upload: ' . $prepareRes->body(),
            ], 500);
        }

        $presigned = $prepareRes->json('data.0');

        // Step 2: Upload file to presigned URL
        $uploadRes = Http::withHeaders([
            'Content-Type' => $mimeType,
        ])->withBody(
            file_get_contents($file->getRealPath()),
            $mimeType
        )->put($presigned['url'], );

        if (!$uploadRes->successful()) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload file',
            ], 500);
        }

        // Step 3: Confirm upload complete
        Http::withHeaders([
            'x-uploadthing-api-key' => $apiKey,
        ])->post('https://api.uploadthing.com/v7/completeUpload', [
            'fileKey' => $presigned['key'],
        ]);

        $url = $presigned['fileUrl'] ?? "https://utfs.io/f/{$presigned['key']}";

        return response()->json([
            'success' => true,
            'data' => [
                'url' => $url,
                'path' => $presigned['key'],
            ],
        ]);
    }
}
