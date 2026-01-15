<?php
namespace App\Http\Controllers;

use App\Http\Requests\SubCategory\StoreRequest;
use App\Http\Requests\SubCategory\UpdateRequest;
use App\Http\Resources\SubCategoryResource;
use App\Repositories\Interfaces\SubCategoryRepositoryInterface;


class SubCategoryController extends Controller
{
    private SubCategoryRepositoryInterface $subCategoryRepository;
    public function __construct(SubCategoryRepositoryInterface $subCategoryRepository)
    {
        $this->subCategoryRepository = $subCategoryRepository;
    }
    public function index()
    {
        $subcategories = $this->subCategoryRepository->all();
        return response()->json([
            'success' => true,
            'data' => SubCategoryResource::collection($subcategories),
        ]);
    }
    public function show(int $id)
    {
        $subCategories = $this->subCategoryRepository->find($id);
        return response()->json([
            'success' => true,
            'data' => new SubCategoryResource($subCategories),
        ]);
    }

    public function store(StoreRequest $request)
    {
        $this->subCategoryRepository->store($request->validated());
        return response()->json([
            'success' => true,
            'message' => 'Sub Category created successfully',
        ]);

    }

    public function update(UpdateRequest $request, int $id)
    {
        $this->subCategoryRepository->update($id, $request->validated());
        return response()->json([
            'success' => true,
            'message' => 'Sub Category updated successfully',
        ]);
    }

    public function destroy(int $id)
    {
        $this->subCategoryRepository->delete($id);
        return response()->json([
            'success' => true,
            'message' => 'Sub Category deleted successfully',
        ]);
    }

    public function activeSubCategories()
    {
        $activeSubcategory = $this->subCategoryRepository->activeList();
        return response()->json([
            'success' => true,
            'data' => $activeSubcategory,
        ]);
    }

}