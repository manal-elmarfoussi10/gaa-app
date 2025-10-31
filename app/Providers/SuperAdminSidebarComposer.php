<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Models\Contact;
use App\Models\User;
use App\Models\Email;

class SuperAdminSidebarComposer extends ServiceProvider
{
    public function boot()
    {
        View::composer('layouts.sidebars.superadmin', function ($view) {
            // Count unread emails (assuming 'read' column exists, or use 'is_read')
            $unreadEmailsCount = Email::where('is_read', false)->count();

            // Count pending demo requests (inactive users)
            $pendingDemoRequests = User::where('is_active', false)->count();

            // Count unread messages (contacts that haven't been read)
            $unreadMessagesCount = Contact::where('read', false)->count();

            // Pass to view
            $view->with([
                'saUnreadEmailsCount' => $unreadEmailsCount,
                'saPendingDemoRequests' => $pendingDemoRequests,
                'saUnreadMessagesCount' => $unreadMessagesCount,
            ]);
        });
    }
}
