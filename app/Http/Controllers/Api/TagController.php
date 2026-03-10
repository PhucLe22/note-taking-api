<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tag\StoreTagRequest;
use App\Http\Resources\TagResource;
use App\Services\TagService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class TagController extends Controller
{
    public function __construct(
        private TagService $tagService
    ) {}

    #[OA\Get(
        path: '/api/v1/tags',
        summary: "List user's tags (paginated)",
        security: [['bearerAuth' => []]],
        tags: ['Tags'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Tag')),
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
        $tags = $this->tagService->getUserTags($request->user()->id);

        return TagResource::collection($tags);
    }

    #[OA\Post(
        path: '/api/v1/tags',
        summary: 'Create a tag',
        security: [['bearerAuth' => []]],
        tags: ['Tags'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'work'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Tag created',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/Tag'),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Validation failed'),
        ]
    )]
    public function store(StoreTagRequest $request): JsonResponse
    {
        $tag = $this->tagService->firstOrCreate(
            $request->user()->id,
            $request->validated('name')
        );

        return (new TagResource($tag))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Delete(
        path: '/api/v1/tags/{id}',
        summary: 'Delete a tag',
        security: [['bearerAuth' => []]],
        tags: ['Tags'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Tag deleted',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Tag deleted successfully.'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Not found'),
        ]
    )]
    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->tagService->delete($id, $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Tag deleted successfully.',
        ]);
    }
}
