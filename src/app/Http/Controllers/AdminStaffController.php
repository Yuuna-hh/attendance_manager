<?php

namespace App\Http\Controllers;

use App\Models\User;

class AdminStaffController extends Controller
{
    public function list()
    {
        $staffs = User::where('role', 'general')
            ->orderBy('id')
            ->get();

        return view('admin.admin_staff_list', [
            'staffs' => $staffs,
        ]);
    }
}