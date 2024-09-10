<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TeamController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $ownedTeams = $user->ownedTeams;
        $memberTeams = $user->teams;

        return Inertia::render('Teams/Index', [
            'ownedTeams' => $user->ownedTeams()->with('owner')->get(),
            'memberTeams' => $user->teams()->with('owner')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Teams/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $team = Auth::user()->ownedTeams()->create($validated);

        return $this->showTeam($team)->with('success', 'Team created successfully.');
    }

    public function show(Team $team)
    {
        if (Auth::id() !== $team->owner_id && !$team->members->contains(Auth::id())) {
            abort(403, 'Unauthorized action.');
        }

        return $this->showTeam($team);
    }

    public function addMember(Request $request, Team $team)
    {
        if (Auth::id() !== $team->owner_id) {
            return Inertia::render('Teams/Show', [
                'team' => $team->load('members'),
                'error' => 'Unauthorized action.',
            ]);
        }

        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return $this->showTeam($team)->with('error', 'User not found.');
        }

        if ($team->members->contains($user)) {
            return $this->showTeam($team)->with('error', 'This user is already a member of the team.');
        }

        $team->members()->attach($user);

        return $this->showTeam($team)->with('success', 'User added successfully.');
    }

    public function removeMember(Request $request, Team $team)
    {
        if (Auth::id() !== $team->owner_id) {
            return $this->showTeam($team)->with('error', 'Unauthorized action.');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        if ($team->owner_id == $validated['user_id']) {
            return $this->showTeam($team)->with('error', 'You cannot remove the team owner.');
        }

        $team->members()->detach($validated['user_id']);

        return $this->showTeam($team)->with('success', 'User removed successfully.');
    }

    public function update(Request $request, Team $team)
    {
        if (Auth::id() !== $team->owner_id) {
            return $this->showTeam($team)->with('error', 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $team->update($validated);

        return $this->showTeam($team)->with('success', 'Team updated successfully.');
    }

    public function destroy(Team $team)
    {
        if (Auth::id() !== $team->owner_id) {
            return Inertia::render('Teams/Index', [
                'error' => 'Unauthorized action.',
            ]);
        }
        $team->delete();

        return Inertia::render('Teams/Index', [
            'success' => 'Team deleted successfully.',
        ]);
    }

    public function leave(Team $team)
    {
        $user = Auth::user();

        if ($team->owner_id === $user->id) {
            return $this->showTeam($team)->with('error', 'Team owners cannot leave their team.');
        }

        $team->members()->detach($user->id);

        return Inertia::render('Teams/Index', [
            'success' => 'You have left the team successfully.',
        ]);
    }

    private function showTeam(Team $team)
    {
        $user = Auth::user();
        $canManageTeam = $user->id === $team->owner_id;

        return Inertia::render('Teams/Show', [
            'team' => $team->load('members'),
            'canManageTeam' => $canManageTeam,
            'user' => $user,
        ]);
    }

    public function getUserTeams()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                Log::error('User not authenticated in getUserTeams');
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            $ownedTeams = $user->ownedTeams()->get();
            $memberTeams = $user->teams()->with('owner')->get();

            Log::info('User ID: ' . $user->id);
            Log::info('Owned Teams: ' . $ownedTeams->toJson());
            Log::info('Member Teams: ' . $memberTeams->toJson());

            return response()->json([
                'ownedTeams' => $ownedTeams,
                'memberTeams' => $memberTeams,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getUserTeams: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while fetching teams'], 500);
        }
    }
}
