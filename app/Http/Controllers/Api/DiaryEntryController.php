<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendDiaryEntryCreatedNotification;
use App\Http\Requests\DiaryEntry\IndexDiaryEntryRequest;
use App\Http\Requests\DiaryEntry\StoreDiaryEntryRequest;
use App\Http\Requests\DiaryEntry\UpdateDiaryEntryRequest;
use App\Http\Resources\DiaryEntryResource;
use App\Models\DiaryEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DiaryEntryController extends Controller
{
    public function index(IndexDiaryEntryRequest $request): AnonymousResourceCollection
    {
        $validated = $request->validated();

        $query = $request->user()
            ->diaryEntries()
            ->when(
                isset($validated['mood']),
                fn ($builder) => $builder->where('mood', $validated['mood'])
            )
            ->when(
                isset($validated['search']),
                fn ($builder) => $builder->where(function ($inner) use ($validated): void {
                    $search = '%'.$validated['search'].'%';
                    $inner
                        ->where('title', 'like', $search)
                        ->orWhere('content', 'like', $search);
                })
            )
            ->when(
                isset($validated['from']),
                fn ($builder) => $builder->whereDate('entry_date', '>=', $validated['from'])
            )
            ->when(
                isset($validated['to']),
                fn ($builder) => $builder->whereDate('entry_date', '<=', $validated['to'])
            )
            ->when(
                array_key_exists('drafts', $validated),
                fn ($builder) => $builder->where('is_draft', $validated['drafts']),
                fn ($builder) => $request->boolean('calendar')
                    ? $builder
                    : $builder->where('is_draft', false)
            )
            ->orderByDesc('entry_date')
            ->orderByDesc('id');

        if ($request->boolean('calendar')) {
            return DiaryEntryResource::collection(
                $query->limit(500)->get()
            );
        }

        return DiaryEntryResource::collection(
            $query->paginate($validated['per_page'] ?? 15)->withQueryString()
        );
    }

    public function store(StoreDiaryEntryRequest $request): JsonResponse
    {
        $user = $request->user();
        $entry = $user->diaryEntries()->create($request->validated());

        if (! $entry->is_draft) {
            SendDiaryEntryCreatedNotification::dispatch($entry, $user);
        }

        return response()->json([
            'message' => 'Diary entry created successfully.',
            'data' => [
                'entry' => new DiaryEntryResource($entry),
            ],
        ], 201);
    }

    public function show(DiaryEntry $diaryEntry): JsonResponse
    {
        $this->authorize('view', $diaryEntry);

        return response()->json([
            'data' => [
                'entry' => new DiaryEntryResource($diaryEntry),
            ],
        ]);
    }

    public function update(UpdateDiaryEntryRequest $request, DiaryEntry $diaryEntry): JsonResponse
    {
        $diaryEntry->update($request->validated());

        return response()->json([
            'message' => 'Diary entry updated successfully.',
            'data' => [
                'entry' => new DiaryEntryResource($diaryEntry->fresh()),
            ],
        ]);
    }

    public function destroy(DiaryEntry $diaryEntry): JsonResponse
    {
        $this->authorize('delete', $diaryEntry);

        $diaryEntry->delete();

        return response()->json([
            'message' => 'Diary entry deleted successfully.',
        ]);
    }
}
