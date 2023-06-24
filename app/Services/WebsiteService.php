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
        $this->website->newQuery()->create([
            'client_id'     => $request->client_id,
            'domain'        => $request->domain,
            'auth_token'    => $request->auth_token
        ]);
    }

}
