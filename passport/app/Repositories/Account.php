<?php
namespace App\Repositories;

class Account
{
    public function login($username, $password)
    {
        $user = app('db')->table('member')
            ->select('id', 'email', 'access_token', 'confirmed_at', 'blocked_at')
            ->where('email', $username)
            ->where('password', md5($password))
            ->first();

        if (!$user) {
            throw new \Exception('无效的用户或密码!', 404);
        }

        if (! $user->confirmed_at) {
            throw new \Exception('未确认的帐号!', 403);
        }

        if ($user->blocked_at) {
            throw new \Exception('帐户已被锁', 403);
        }

        $userId = $user->id;
        $profile = app('db')->table('member_profile')
            ->where('user_id', $userId)
            ->first();

        return [
            'id' => $userId,
            'email' => $user->email,
            'access_token' => $user->access_token,
            'profile' => [
                'name' => $profile->name,
                'phone_number' => $profile->phone_number,
                'job_name' => $profile->job_name,
                'where_from' => $profile->where_from
            ]
        ];
    }

    public function wechatLogin($open_id)
    {

    }

    public function register($userInfo)
    {
        $userInfo = array_mult_merge([
            'email' => null,
            'title' => null,
            'password' => null,
            'registration_ip' => null
        ], $userInfo);

        $exists = app('db')->table('member')
            ->where('email', $userInfo['email'])
            ->exists();
        if ($exists) {
            throw new \Exception('已存在的用户!', 504);
        }

        $key = md5('usleju.'.$userInfo['email']);

        $userData = [
            'email' => $userInfo['email'],
            'password' => md5($userInfo['password']),
            'auth_key' => $key,
            'access_token' => $key,
            'registration_ip' => $userInfo['registration_ip'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'flags' => 0

        ];
        $userId = app('db')->table('member')->insertGetId($userData);
        if (!$userId) {
            throw new \Exception('注册失败!', 505);
        }

        app('db')->table('member_profile')->insert([
            'user_id' => $userId,
            'name' => $userInfo['title']
        ]);

        return true;
    }

    public function forgotPasspwrd($username)
    {

    }
}