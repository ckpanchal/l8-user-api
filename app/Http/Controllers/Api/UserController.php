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
use App\Http\Requests\Api\UserInvitationRequest;
use App\Http\Requests\Api\VerifyUserRequest;
use App\Http\Requests\Api\UserProfileRequest;
use App\Notifications\RegistrationSuccess;
use App\Http\Resources\User\User as UserResource;
use JWTAuth;

class UserController extends Controller
{
    /**
     * Custom helper instance
     */
    protected $customHelper;

    public function __construct(CustomHelper $customHelper) {
        $this->customHelper = $customHelper;
        return $this;
    }

    /**
     * Handle logic to invite user for registration
     * 
     * @param \App\Http\Requests\Api\UserInvitationRequest
     * 
     * @return json 
     */
    public function inviteUserForRegistration(UserInvitationRequest $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];
        $user = JWTAuth::user();
        if (!$user || ($user && ($user->user_role != 'admin'))) {
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

    /**
     * Handle logic to verify user email
     * 
     * @param \App\Http\Requests\VerifyUserRequest
     * 
     * @return json 
     */
    public function verifyUser(VerifyUserRequest $request) 
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];
        $user = JWTAuth::user();
        if ($user && ($user->verification_code == $request->code)) {
            $user->verification_code = null;
            $user->verified_at = Carbon::now();
            $user->save();

            // Notify user successfull
            $user->notify(new RegistrationSuccess($user));

            $response['message'] = 'You have successfully registered with our app.';
        } else {
            $response['status'] = false;
            $response['message'] = 'Verification code invalid';
        }
        return response($response);    
    }

    /**
     * Handle logic to update user profile
     * 
     * @param \App\Http\Requests\UserProfileRequest
     * 
     * @return json 
     */
    public function updateProfile(UserProfileRequest $request)
    {
        $response = ['status' => true, 'data' => [], 'errors' => []];
        $user = JWTAuth::user();
        if(!$user) {
            $response['status'] = false;
            $response['message'] = 'Oops something went wrong';
            return response($response);
        }
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
        $response['data'] = new UserResource($user);
        return response($response);
    }
}
