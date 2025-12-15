<?php

namespace App\Http\Controllers;

use App\Models\TaskCategory;
use Illuminate\Http\Request;

class TaskCategoryController extends Controller
{
    public function index()
    {
        // Tree View for Index
        $categories = TaskCategory::whereNull('parent_id')
            ->with('children.children')
            ->orderBy('name')
            ->get();
        return view('task_categories.index', compact('categories'));
    }

    public function create()
    {
        // Flattened list for the parent dropdown
        $categories = TaskCategory::orderBy('name')->get();
        
        // Pass an empty object so the _form partial doesn't crash on $category->name
        $category = new TaskCategory();
        
        return view('task_categories.create', compact('categories', 'category'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:task_categories,id'
        ]);

        TaskCategory::create($request->all());

        return redirect()->route('task-categories.index')
                         ->with('success', 'Category created successfully.');
    }

    public function edit(TaskCategory $taskCategory)
    {
        $categories = TaskCategory::where('id', '!=', $taskCategory->id)->orderBy('name')->get();
        return view('task_categories.edit', compact('taskCategory', 'categories'));
    }

    public function update(Request $request, TaskCategory $taskCategory)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:task_categories,id'
        ]);

        if($request->parent_id == $taskCategory->id) {
            return back()->with('error', 'A category cannot be its own parent.');
        }

        $taskCategory->update($request->all());

        return redirect()->route('task-categories.index')
                         ->with('success', 'Category updated successfully.');
    }

    public function destroy(TaskCategory $taskCategory)
    {
        if($taskCategory->children()->exists()) {
             return back()->with('error', 'Cannot delete: This category has sub-categories.');
        }
        $taskCategory->delete();
        return back()->with('success', 'Category deleted.');
    }

    // API for Dropdowns (Keep this as is)
    public function getChildren($parentId = null)
    {
        if (!$parentId) {
            $data = TaskCategory::whereNull('parent_id')->orderBy('name')->get();
        } else {
            $data = TaskCategory::where('parent_id', $parentId)->orderBy('name')->get();
        }
        return response()->json($data);
    }
}