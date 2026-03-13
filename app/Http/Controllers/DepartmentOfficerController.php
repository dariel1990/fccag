<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepartmentOfficer\StoreDepartmentOfficerRequest;
use App\Http\Requests\DepartmentOfficer\UpdateDepartmentOfficerRequest;
use App\Models\Department;
use App\Models\DepartmentOfficer;
use Illuminate\Http\RedirectResponse;

class DepartmentOfficerController extends Controller
{
    /**
     * Store a newly created officer for the given department.
     */
    public function store(StoreDepartmentOfficerRequest $request, Department $department): RedirectResponse
    {
        $department->officers()->create($request->validated());

        return to_route('departments.show', $department);
    }

    /**
     * Update the specified officer record.
     */
    public function update(UpdateDepartmentOfficerRequest $request, Department $department, DepartmentOfficer $officer): RedirectResponse
    {
        $officer->update($request->validated());

        return to_route('departments.show', $department);
    }

    /**
     * Remove the specified officer record.
     */
    public function destroy(Department $department, DepartmentOfficer $officer): RedirectResponse
    {
        $officer->delete();

        return to_route('departments.show', $department);
    }
}
