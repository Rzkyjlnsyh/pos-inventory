<?php

namespace App\Http\Controllers\Owner;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ProfileOwnerController extends Controller
{

    public function index(Request $request)
    {
        return view('owner.profile.index', [
            'user' => $request->user()
        ]);
    }

    public function update(Request $request, User $user)
    {
        try {
            // Log request data
            Log::info('Profile update attempt', [
                'has_file' => $request->hasFile('avatar'),
                'all_data' => $request->all()
            ]);

            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $request->user()->id],
                'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:10000'],
            ]);

            if ($request->hasFile('avatar')) {
                // Log file information
                Log::info('Avatar file details', [
                    'original_name' => $request->file('avatar')->getClientOriginalName(),
                    'mime_type' => $request->file('avatar')->getMimeType(),
                    'size' => $request->file('avatar')->getSize(),
                    'error' => $request->file('avatar')->getError()
                ]);

                if (!$request->file('avatar')->isValid()) {
                    Log::error('Invalid file upload');
                    return redirect()->back()
                        ->withErrors(['avatar' => 'Invalid file upload'])
                        ->withInput();
                }

                // Create directory if it doesn't exist
                $avatarPath = storage_path('app/public/avatars');
                if (!file_exists($avatarPath)) {
                    mkdir($avatarPath, 0755, true);
                }

                // Delete old avatar
                if ($request->user()->avatar) {
                    $oldAvatarPath = storage_path('app/public/avatars/' . $request->user()->avatar);
                    if (file_exists($oldAvatarPath)) {
                        unlink($oldAvatarPath);
                    }
                }

                // Generate filename
                $avatarName = time() . '_' . uniqid() . '.' . $request->file('avatar')->getClientOriginalExtension();
                
                try {
                    // Store file using move
                    $request->file('avatar')->move(storage_path('app/public/avatars'), $avatarName);
                    
                    // Verify file was saved
                    if (!file_exists(storage_path('app/public/avatars/' . $avatarName))) {
                        throw new \Exception('File not saved successfully');
                    }

                    $request->user()->avatar = $avatarName;
                    Log::info('Avatar saved successfully', ['filename' => $avatarName]);
                } catch (\Exception $e) {
                    Log::error('Failed to save avatar', ['error' => $e->getMessage()]);
                    return redirect()->back()
                        ->withErrors(['avatar' => 'Failed to save avatar: ' . $e->getMessage()])
                        ->withInput();
                }
            }

            $request->user()->name = $request->name;
            $request->user()->email = $request->email;
            $request->user()->save();

            Log::info('Profile updated successfully', ['user_id' => $request->user()->id]);

            return redirect()
                ->route('owner.profile.index')
                ->with('success', 'Profile updated successfully.');

        } catch (\Exception $e) {
            Log::error('Profile update error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->back()
                ->withErrors(['error' => 'An error occurred: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request, User $user)
    {
        try {
            $request->validate([
                'current_password' => ['required'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);

            if (!Hash::check($request->current_password, $request->user()->password)) {
                return back()->withErrors([
                    'current_password' => 'The provided password does not match your current password.',
                ]);
            }

            $request->user()->password = Hash::make($request->password);
            $request->user()->save();

            return redirect()
                ->route('owner.profile.index')
                ->with('success', 'Password updated successfully.');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'An error occurred while updating your password.'])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request): RedirectResponse
    {
        try {
            $request->validateWithBag('userDeletion', [
                'password' => ['required', 'current_password'],
            ]);

            $user = $request->user();

            // Delete user's avatar if exists
            if ($user->avatar && Storage::exists('public/avatars/' . $user->avatar)) {
                Storage::delete('public/avatars/' . $user->avatar);
            }

            Auth::logout();

            $user->delete();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return Redirect::to('/');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withErrors(['error' => 'An error occurred while deleting your account.'])
                ->withInput();
        }
    }
}
