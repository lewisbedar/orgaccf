<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Models\SchoolClass;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $this->coordinator();
        return view('users.index', ['users' => User::orderBy('role')->orderBy('display_name')->get()]);
    }

    public function create()
    {
        $this->coordinator();
        return $this->form(new User());
    }

    public function edit(User $user)
    {
        $this->coordinator();
        return $this->form($user);
    }

    public function store(Request $request)
    {
        $this->coordinator();
        $data = $this->validated($request);
        $data['password'] = Hash::make($request->password);
        $user = User::create($data);
        $this->syncAssignments($user, $request);
        return redirect()->route('users.index')->with('success', 'Utilisateur créé.');
    }

    public function update(Request $request, User $user)
    {
        $this->coordinator();
        $data = $this->validated($request, $user);
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }
        $user->update($data);
        $this->syncAssignments($user, $request);
        return redirect()->route('users.index')->with('success', 'Utilisateur mis à jour.');
    }

    public function destroy(User $user)
    {
        $this->coordinator();
        abort_if($user->username === 'admin', 422);
        $user->update(['is_active' => false]);
        return back()->with('success', 'Utilisateur désactivé.');
    }

    private function form(User $user)
    {
        return view('users.form', [
            'user' => $user,
            'languages' => Language::where('is_active', true)->orderBy('sort_order')->get(),
            'classes' => SchoolClass::where('school_year_id', $this->activeYear()?->id)->orderBy('name')->get(),
            'selectedLanguages' => $user->exists ? $user->assignments()->pluck('language_id')->all() : [],
            'selectedClasses' => $user->exists ? $user->assignments()->pluck('school_class_id')->all() : [],
        ]);
    }

    private function validated(Request $request, ?User $user = null): array
    {
        $id = $user?->id ?: 'NULL';
        $data = $request->validate([
            'username' => ['required', 'max:80', 'unique:users,username,' . $id],
            'display_name' => ['required', 'max:160'],
            'email' => ['nullable', 'email', 'unique:users,email,' . $id],
            'role' => ['required', 'in:coordinateur,enseignant'],
            'password' => [$user?->exists ? 'nullable' : 'required', 'min:6'],
            'languages' => ['array'],
            'classes' => ['array'],
        ]);
        $data['name'] = $data['display_name'];
        $data['email'] = $data['email'] ?: $data['username'] . '@orgaccf.local';
        $data['languages_text'] = Language::whereIn('id', $request->input('languages', []))->get()->map->label()->implode(', ');
        $data['classes_text'] = SchoolClass::whereIn('id', $request->input('classes', []))->pluck('name')->implode(', ');
        unset($data['languages'], $data['classes']);
        return $data;
    }

    private function syncAssignments(User $user, Request $request): void
    {
        $user->assignments()->delete();
        if ($request->role !== 'enseignant') {
            return;
        }
        foreach ($request->input('classes', []) as $classId) {
            foreach ($request->input('languages', []) as $languageId) {
                TeacherAssignment::firstOrCreate(['user_id' => $user->id, 'school_class_id' => $classId, 'language_id' => $languageId]);
            }
        }
    }
}
