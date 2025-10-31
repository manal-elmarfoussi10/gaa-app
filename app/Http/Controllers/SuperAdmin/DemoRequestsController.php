<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DemoRequestsController extends Controller
{
    public function index()
    {
        // Get users who are inactive (demo requests)
        $demoRequests = User::where('is_active', false)
            ->with(['company'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('superadmin.demo-requests.index', compact('demoRequests'));
    }

    public function activate($id)
    {
        $user = User::findOrFail($id);

        if (!$user->is_active) {
            $user->update(['is_active' => true]);

            return back()->with('success', 'Le compte de ' . $user->name . ' a été activé avec succès.');
        }

        return back()->with('error', 'Ce compte est déjà activé.');
    }

    public function show($id)
    {
        $user = User::with(['company'])->findOrFail($id);

        return view('superadmin.demo-requests.show', compact('user'));
    }
}
