<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Note\StoreNoteRequest;
use App\Http\Requests\Note\UpdateNoteRequest;
use App\Http\Resources\NoteResource;
use App\Services\NoteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class NoteController extends Controller
{
    public function __construct(
        private NoteService $noteService
    ) {}

    #[OA\Get(
        path: '/api/v1/notes',
        summary: "List user's notes (paginated)",
        security: [['bearerAuth' => []]],
        tags: ['Notes'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Note')),
                        new OA\Property(property: 'links', type: 'object'),
                        new OA\Property(property: 'meta', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $notes = $this->noteService->getUserNotes($request->user()->id);

        return NoteResource::collection($notes);
    }

    #[OA\Post(
        path: '/api/v1/notes',
        summary: 'Create a note',
        security: [['bearerAuth' => []]],
        tags: ['Notes'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title'],
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'My Note'),
                    new OA\Property(property: 'content', type: 'string', example: 'Note content here'),
                    new OA\Property(property: 'tags', type: 'array', items: new OA\Items(type: 'integer'), example: [1, 2]),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Note created',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/Note'),
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Success'),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Validation failed'),
        ]
    )]
    public function store(StoreNoteRequest $request): JsonResponse
    {
        $note = $this->noteService->create(
            $request->validated(),
            $request->user()->id
        );

        return (new NoteResource($note))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Get(
        path: '/api/v1/notes/{id}',
        summary: 'Get a note',
        security: [['bearerAuth' => []]],
        tags: ['Notes'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/Note'),
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Success'),
                    ]
                )
            ),
            new OA\Response(response: 403, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function show(Request $request, int $id): NoteResource
    {
        $note = $this->noteService->findOrFail($id);

        $this->authorize('view', $note);

        return new NoteResource($note);
    }

    #[OA\Put(
        path: '/api/v1/notes/{id}',
        summary: 'Update a note',
        security: [['bearerAuth' => []]],
        tags: ['Notes'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'Updated Title'),
                    new OA\Property(property: 'content', type: 'string', example: 'Updated content'),
                    new OA\Property(property: 'tags', type: 'array', items: new OA\Items(type: 'integer'), example: [1, 3]),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Note updated'),
            new OA\Response(response: 403, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function update(UpdateNoteRequest $request, int $id): NoteResource
    {
        $note = $this->noteService->findOrFail($id);

        $this->authorize('update', $note);

        $note = $this->noteService->update($note, $request->validated());

        return new NoteResource($note);
    }

    #[OA\Delete(
        path: '/api/v1/notes/{id}',
        summary: 'Soft delete a note',
        security: [['bearerAuth' => []]],
        tags: ['Notes'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Note deleted',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Note deleted successfully.'),
                    ]
                )
            ),
            new OA\Response(response: 403, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function destroy(Request $request, int $id): JsonResponse
    {
        $note = $this->noteService->findOrFail($id);

        $this->authorize('delete', $note);

        $this->noteService->delete($note);

        return response()->json([
            'success' => true,
            'message' => 'Note deleted successfully.',
        ]);
    }

    #[OA\Patch(
        path: '/api/v1/notes/{id}/restore',
        summary: 'Restore a soft-deleted note',
        security: [['bearerAuth' => []]],
        tags: ['Notes'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Note restored'),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function restore(Request $request, int $id): NoteResource
    {
        $note = $this->noteService->restore($id, $request->user()->id);

        return new NoteResource($note);
    }

    #[OA\Get(
        path: '/api/v1/notes/search',
        summary: 'Search notes by title or content',
        security: [['bearerAuth' => []]],
        tags: ['Notes'],
        parameters: [
            new OA\Parameter(name: 'q', in: 'query', required: true, schema: new OA\Schema(type: 'string', minLength: 1, maxLength: 100)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Search results',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Note')),
                        new OA\Property(property: 'links', type: 'object'),
                        new OA\Property(property: 'meta', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Query parameter required'),
        ]
    )]
    public function search(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'q' => 'required|string|min:1|max:100',
        ]);

        $notes = $this->noteService->search(
            $request->user()->id,
            $request->query('q')
        );

        return NoteResource::collection($notes);
    }
}
