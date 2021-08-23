<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Invitation;
use Carbon\Carbon;
use App\Helpers\CustomHelper;
use JWTAuth;

class UserController extends Controller
{

    protected $customHelper;

    public function __construct(CustomHelper $customHelper) {
        $this->customHelper = $customHelper;
        return $this;
    }

    public function inviteUserForRegistration(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];
        $user = JWTAuth::user();
        if ($user && ($user->user_role != 'admin')) {
            $response['status'] = false;
            $response['message'] = "You don't have permission to invite user.";
            return response($response);
        }
        $email = $request->email;
        $checkUserInvitation = Invitation::where(['email' => $email])->first();
        if ($checkUserInvitation) {
            $response['status'] = false;
            $response['message'] = "User with same email already invited.";
            return response($response);    
        }
        $token = Str::random(64);
        $invitationData = [
            'email' => $email,
            'token' => $token
        ];
        Mail::to($request->email)->send(new UserInvitation($token));
        $invitation = Invitation::create($invitationData);
        if ($invitation) {
            $response['message'] = 'Invitation sent successfully.';
        }
        return response($response);
    }

    public function verifyUser(Request $request) 
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];
        $user = JWTAuth::user();
        if ($user->verification_code == $request->code) {
            $user->verification_code = null;
            $user->verified_at = Carbon::now();
            $user->save();
            $response['message'] = 'You have successfully registered with our app.';
        } else {
            $response['status'] = false;
            $response['message'] = 'Verification code invalid';
        }
        return response($response);    
    }

    public function updateProfile(Request $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];
        $user = JWTAuth::user();
        $requestData = $request->input();
        $user->fill($requestData);
        $user->save();
        $oldAvatar = $user->avatar;
        if ($request->file('avatar')) {
            if (!empty($oldAvatar) && $this->customHelper->fileExists($oldAvatar)) {
                $this->customHelper->delete($oldAvatar);
            }
            $avatar = $request->file('avatar');
            $filePath = $this->customHelper->storeFile('avatar', $avatar);
            $user->update(['avatar' => $filePath]);
        }
        return response($response);
    }
}
