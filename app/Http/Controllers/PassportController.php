<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\TokenRepository;
use Laravel\Passport\RefreshTokenRepository;

class PassportController extends Controller
{
    private $returnMessage = '';
    private $returnStatus = 0;
    /**
     * @OA\Post(
     * path="/api/register",
     * operationId="Register",
     * tags={"Register"},
     * summary="User Register",
     * description="User Register here",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"name","email", "password"},
     *               @OA\Property(property="name", type="text"),
     *               @OA\Property(property="email", type="text"),
     *               @OA\Property(property="password", type="password"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=200,
     *          description="Register Successfully",
     *          content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="success",
     *                         type="boolean",
     *                         description="false",
     *                          example="true"
     *                     ),
     *                     @OA\Property(
     *                         property="message",
     *                         type="string",
     *                         description="User registered succesfully, Use Login method to receive token."
     *                     )
     *                 )
     *             )
     *         }
     *       ),
     *      @OA\Response(
     *          response=404,
     *          description="Resource Not Found",
     *          content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="success",
     *                         type="boolean",
     *                         description="false",
     *                          example="false"
     *                     ),
     *                     @OA\Property(
     *                         property="message",
     *                         type="string",
     *                         description="Please see errors parameter for all errors."
     *                     ),
     *                     @OA\Property(
     *                         property="errors",
     *                         type="object",
     *                         description="Detail error description"
     *                     )
     *                 )
     *             )
     *         }
     *      ),
     * )
     */
    public function register(Request $request)
    {
        $input = $request->only(['name', 'email', 'password']);

        $validate_data = [
            'name' => 'required|string|min:4',
            'email' => 'required|email',
            'password' => 'required|min:8',
        ];

        try {
            $validator = Validator::make($input, $validate_data);

            if ($validator->fails()) {
                $this->returnMessage = 'Please see errors parameter for all errors.';
                $this->returnStatus = 404;
                throw new \Exception($validator->errors());
            }

            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password'])
            ]);

            $this->returnStatus = 200;
            $ret = array('success' => true,
                    'message' => 'User registered succesfully, Use Login method to receive token.');

        }catch (\Exception $ex){

            if($this->returnStatus == 0)
                $this->returnStatus = 404;

            $ret = array('success' => false,
                    'message' => $this->returnMessage,
                    'errors' => $ex->getMessage());
        }

        return response()->json($ret, $this->returnStatus);
    }

    /**
     * @OA\Post(
     * path="/api/login",
     * operationId="Login",
     * tags={"Login"},
     * summary="Login User",
     * description="User Login here",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"email", "password"},
     *               @OA\Property(property="email", type="text"),
     *               @OA\Property(property="password", type="password"),
     *            ),
     *        ),
     *    ),
     *      @OA\Response(
     *          response=200,
     *          description="Logged in Successfully",
     *          content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="success",
     *                         type="boolean",
     *                         description="false",
     *                          example="true"
     *                     ),
     *                     @OA\Property(
     *                         property="message",
     *                         type="string",
     *                         description="User registered succesfully, Use Login method to receive token."
     *                     ),
     *                     @OA\Property(
     *                         property="token",
     *                         type="string",
     *                         description="Use this token for other requests."
     *                     )
     *                 )
     *             )
     *         }
     *       ),
     *      @OA\Response(
     *          response=404,
     *          description="Resource Not Found",
     *          content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="success",
     *                         type="boolean",
     *                         description="false",
     *                          example="false"
     *                     ),
     *                     @OA\Property(
     *                         property="message",
     *                         type="string",
     *                         description="Error general description."
     *                     ),
     *                     @OA\Property(
     *                         property="errors",
     *                         type="object",
     *                         description="Detail error description"
     *                     )
     *                 )
     *             )
     *         }
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="User authentication failed",
     *          content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="success",
     *                         type="boolean",
     *                         description="false",
     *                          example="false"
     *                     ),
     *                     @OA\Property(
     *                         property="message",
     *                         type="string",
     *                         description="Error general description."
     *                     ),
     *                     @OA\Property(
     *                         property="errors",
     *                         type="object",
     *                         description="Detail error description"
     *                     )
     *                 )
     *             )
     *         }
     *      ),
     * )
     */
    public function login(Request $request)
    {
        $input = $request->only(['email', 'password']);

        $validate_data = [
            'email' => 'required|email',
            'password' => 'required|min:8',
        ];

        try {

            $validator = Validator::make($input, $validate_data);

            if ($validator->fails()) {
                $this->returnStatus = 401;
                $this->returnMessage = 'Please see errors parameter for all errors.';

                throw new \Exception($validator->errors());

            }

            // authentication attempt
            if (auth()->attempt($input)) {
                $token = auth()->user()->createToken('passport_token')->accessToken;

                $this->returnStatus = 200;

                $ret = array('success' => true,
                    'message' => 'User login succesfully, Use token to authenticate.',
                    'token' => $token
                );

            } else {
                $this->returnStatus = 401;
                $this->returnMessage = 'User authentication failed.';

                throw new \Exception('User authentication failed.');

            }
        }catch (\Exception $ex){
            if($this->returnStatus == 0)
                $this->returnStatus = 404;

            $ret = array('success' => false,
                'message' => $this->returnMessage,
                'errors' => $ex->getMessage());
        }

        return response()->json($ret, $this->returnStatus);
    }

    /**
     * @OA\Post(
     * path="/api/user-detail",
     * operationId="user-detail",
     * tags={"User detail"},
     * summary="user-detail",
     * description="Get user details",
     *     security={{"bearer_token":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="User detail fetched in Successfully",
     *          content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="success",
     *                         type="boolean",
     *                         description="false",
     *                          example="true"
     *                     ),
     *                     @OA\Property(
     *                         property="message",
     *                         type="string",
     *                         description="Data fetched successfully"
     *                     ),
     *                     @OA\Property(
     *                         property="data",
     *                         type="object",
     *                         description="User list."
     *                     )
     *                 )
     *             )
     *         }
     *       )
     * )
     */
    public function userDetail()
    {
        return response()->json([
            'success' => true,
            'message' => 'Data fetched successfully.',
            'data' => auth()->user()
        ], 200);
    }

    /**
     * @OA\Post(
     * path="/api/logout",
     * operationId="logout",
     * tags={"Logout"},
     * summary="logout",
     * description="Logout",
     *     security={{"bearer_token":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="User logout successfully",
     *          content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="success",
     *                         type="boolean",
     *                         description="true|false",
     *                          example="true"
     *                     ),
     *                     @OA\Property(
     *                         property="message",
     *                         type="string",
     *                         description="User logout successfully"
     *                     )
     *                 )
     *             )
     *         }
     *       )
     * )
     */
    public function logout()
    {
        $access_token = auth()->user()->token();

        // logout from only current device
        $tokenRepository = app(TokenRepository::class);
        $tokenRepository->revokeAccessToken($access_token->id);

        // use this method to logout from all devices
        // $refreshTokenRepository = app(RefreshTokenRepository::class);
        // $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($$access_token->id);

        return response()->json([
            'success' => true,
            'message' => 'User logout successfully.'
        ], 200);
    }

}
