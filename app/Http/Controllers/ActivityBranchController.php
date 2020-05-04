<?php

namespace App\Http\Controllers;

use App\Services\ActivityBranchService;
use App\Traits\RestControllerTrait;
use Illuminate\Http\Request;

use App\Model\ActivityBranch;

class ActivityBranchController extends Controller
{
    use RestControllerTrait;

    public function index()
    {
        return $this->response(
            ActivityBranch::query()
                ->orderBy('order', 'asc')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $this->validate($request, ActivityBranch::roles(), [], ActivityBranch::mappedProperties());

        return $this->response(ActivityBranchService::save($request->all()));
    }

    public function show($id)
    {
        return $this->response(ActivityBranch::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        if ($request->all()['id'] !== $id) throw new ModelNotFoundException();

        $this->validate($request, ActivityBranch::roles(), [], ActivityBranch::mappedProperties());

        return $this->response(ActivityBranchService::save($request->all()));
    }

    public function destroy($id)
    {
        ActivityBranch::findOrFail($id)->delete();
    }
}
