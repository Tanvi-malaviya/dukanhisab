<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AnalyticsApiController extends Controller
{
    /**
     * Record single or batch analytics events from mobile/web clients.
     */
    public function track(Request $request): JsonResponse
    {
        $userId = $request->user()?->id ?? $request->input('user_id');
        $shopId = $request->header('X-Shop-Id') ?? $request->input('shop_id');
        $platform = $request->header('X-Platform') ?? $request->input('platform', 'api');
        $appVersion = $request->header('X-App-Version') ?? $request->input('app_version');
        $ip = $request->ip();

        // Check if batch events provided
        if ($request->has('events') && is_array($request->input('events'))) {
            $events = $request->input('events');
            $recordedCount = 0;

            foreach ($events as $eventData) {
                if (!isset($eventData['event_name']) || empty($eventData['event_name'])) {
                    continue;
                }

                AnalyticsService::track(
                    eventName: (string) $eventData['event_name'],
                    userId: $eventData['user_id'] ?? $userId,
                    shopId: $eventData['shop_id'] ?? $shopId,
                    properties: (array) ($eventData['properties'] ?? []),
                    platform: $eventData['platform'] ?? $platform,
                    appVersion: $eventData['app_version'] ?? $appVersion,
                    ipAddress: $ip
                );
                $recordedCount++;
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully recorded {$recordedCount} analytics events.",
                'count' => $recordedCount,
            ]);
        }

        // Single event
        $request->validate([
            'event_name' => 'required|string|max:100',
            'properties' => 'nullable|array',
            'platform' => 'nullable|string|max:30',
            'app_version' => 'nullable|string|max:30',
        ]);

        $event = AnalyticsService::track(
            eventName: $request->input('event_name'),
            userId: $userId,
            shopId: $shopId,
            properties: $request->input('properties', []),
            platform: $platform,
            appVersion: $appVersion,
            ipAddress: $ip
        );

        return response()->json([
            'success' => true,
            'message' => 'Event recorded successfully.',
            'event_id' => $event->id,
        ]);
    }
}
