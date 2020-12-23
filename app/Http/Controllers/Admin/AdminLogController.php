<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminLogService;
use Illuminate\Http\Request;

class AdminLogController extends Controller
{
    /**
     * @var AdminLogService
     */
    private $adminLogService;

    public function __construct(AdminLogService $adminLogService)
    {
        $this->adminLogService = $adminLogService;
    }

    //
    public function list(Request $request)
    {
        $this->adminLogService->list($request->get("page", 1), $request->get("limit", 10));
        return $this->AppReturn(
            $this->adminLogService->_code,
            $this->adminLogService->_msg,
            $this->adminLogService->_data
        );
    }
}
