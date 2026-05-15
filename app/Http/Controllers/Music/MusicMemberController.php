<?php

namespace App\Http\Controllers\Music;

use App\Http\Controllers\Controller;
use App\Http\Requests\Music\StoreMusicMemberRequest;
use App\Http\Requests\Music\UpdateMusicMemberRequest;
use App\Models\MusicMember;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class MusicMemberController extends Controller
{
    public function index(): Response
    {
        $linkedUserIds = MusicMember::query()->whereNotNull('user_id')->pluck('user_id');

        return Inertia::render('music/music-members/Index', [
            'members' => MusicMember::query()
                ->with('user:id,name,email')
                ->orderBy('name')
                ->get(['id', 'name', 'user_id', 'instruments', 'is_active']),
            'users' => User::query()
                ->whereNotIn('id', $linkedUserIds)
                ->orderBy('name')
                ->get(['id', 'name', 'email']),
        ]);
    }

    public function store(StoreMusicMemberRequest $request): RedirectResponse
    {
        MusicMember::create($request->validated());

        return redirect()->back();
    }

    public function update(UpdateMusicMemberRequest $request, MusicMember $musicMember): RedirectResponse
    {
        $musicMember->update($request->validated());

        return redirect()->back();
    }

    public function destroy(MusicMember $musicMember): RedirectResponse
    {
        $musicMember->delete();

        return to_route('music.music-members.index');
    }
}
