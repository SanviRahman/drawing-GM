<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Media;
use App\Services\AdminAvatarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function __construct(private readonly AdminAvatarService $avatarService) {}

    public function profile(Request $request)
    {
        $admin = $this->admin($request);

        $breadcrumb = [
            ['text' => 'Account', 'url' => null],
            ['text' => 'Profile', 'url' => null],
        ];

        return view('backoffice.admin.admins.profile', [
            'title' => 'Update Profile',
            'sub_title' => 'Manage your admin account information and profile photo.',
            'breadcrumb' => $breadcrumb,
            'breadcrumbs' => $breadcrumb,
            'admin' => $admin,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $admin = $this->admin($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('admins', 'username')->ignore($admin->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('admins', 'email')->ignore($admin->id)],
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('admins', 'phone')->ignore($admin->id)],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'photo_media_id' => ['nullable', 'integer', Rule::exists('media', 'id')->whereNull('deleted_at'), $this->reusableImageRule()],
        ]);

        $admin->update([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? $admin->phone,
        ]);

        $this->avatarService->syncFromRequest($request, $admin);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function password()
    {
        $breadcrumb = [
            ['text' => 'Account', 'url' => null],
            ['text' => 'Change Password', 'url' => null],
        ];

        return view('backoffice.admin.admins.password', [
            'title' => 'Change Password',
            'sub_title' => 'Update your password to keep your admin account secure.',
            'breadcrumb' => $breadcrumb,
            'breadcrumbs' => $breadcrumb,
        ]);
    }

    public function updatePassword(Request $request)
    {
        $admin = $this->admin($request);

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if (! Hash::check($validated['current_password'], $admin->password)) {
            return back()->withErrors([
                'current_password' => 'Current password is incorrect.',
            ]);
        }

        $admin->update([
            'password' => $validated['password'],
        ]);

        return back()->with('success', 'Password updated successfully.');
    }

    private function admin(Request $request): Admin
    {
        $admin = $request->user('admin');

        abort_unless($admin instanceof Admin, 401);

        return $admin;
    }
    private function reusableImageRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if ($value === null || $value === '') {
                return;
            }

            $media = Media::query()->find((int) $value);

            if (! $media || ! $media->isPickerSafe() || ! $media->isImage()) {
                $fail('The selected media must be an active reusable image.');
            }
        };
    }

}