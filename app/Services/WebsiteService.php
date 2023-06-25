<?php

namespace App\Services;

use App\Models\Website;
use Illuminate\Http\Request;

class WebsiteService
{

    protected $website;

    public function __construct(Website $website)
    {
        $this->website = $website;
    }

    public function store(Request $request)
    {
        $site = $this->website->newQuery()->create([
            'client_id'     => $request->client_id,
            'domain'        => $request->domain,
            'auth_token'    => $request->auth_token
        ]);

        (new UserService)->sendNotification("A new website information has been created.", 'website', $site->id);
    }

    public function getAll()
    {
        return $this->website->newQuery()->with('client')->latest()->get();
    }

}
