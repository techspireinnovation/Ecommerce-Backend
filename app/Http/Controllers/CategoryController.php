<?php
namespace App\Http\Controllers;

use App\Http\Requests\Category\StoreRequest;
use App\Http\Requests\Category\UpdateRequest;
use App\Http\Resources\BrandResource;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Services\PaginationService;
use Illuminate\Auth\Events\Validated;

class CategoryController extends Controller
{
    private CategoryRepositoryInterface $categoryRepository;
    private PaginationService $paginationService;
    public function __construct(CategoryRepositoryInterface $categoryRepository, PaginationService $paginationService)
    {
        $this->categoryRepository = $categoryRepository;
        $this->paginationService = $paginationService;
    }

    public function index()
    {
        $categories = $this->categoryRepository->all();
        $pagination = $this->paginationService->format($categories);
        return response()->json([
            'success' => true,
            'data' => BrandResource::collection($categories),
            ...$pagination
        ]);

    }
    public function show(int $id)
    {
        $categories = $this->categoryRepository->find($id);
        return response()->json([
            'success' => true,
            'data' => new BrandResource($categories),
        ]);
    }

    public function store(StoreRequest $request)
    {
        $this->categoryRepository->store($request->Validated());
        return response()->json([

            'success' => true,
            'message' => 'Category created successfully',
        ]);

    }
    public function update(UpdateRequest $request, int $id)
    {
        $this->categoryRepository->update($id, $request->validated());
        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
        ]);

    }


    public function destroy(int $id)
    {
        $this->categoryRepository->delete($id);
        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully',
        ]);

    }
    public function activeCategories()
    {
        $activecategory = $this->categoryRepository->activeList();
        return response()->json([
            'success' => true,
            'data' => $activecategory,

        ]);

    }
}