<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\Account;

class AccountController extends Controller
{
    public function login(Account $account, Request $req)
    {
        $this->validate($req, [
            'username' => 'required',
            'password' => 'required',
        ]);

        $username = $req->get('username');
        $password = $req->get('password');

        return $account->login($username, $password);
    }

    public function wechatLogin(Account $account, Request $req)
    {
        $this->validate($req, [
            'open_id' => 'required'
        ]);

        $openId = $req->get('open_id');
        $account->wechatLogin($openId);
    }

    public function register(Account $account, Request $req)
    {
        $this->validate($req, [
            'email' => 'required|email',
            'title' => 'required',
            'password' => 'required'
        ]);

        $userInfo = $req->all();
        $userInfo['registration_ip'] = $req->getClientIp();

        return $account->register($userInfo);
    }

    public function forgotPasspwrd(Account $account, Request $req)
    {
        $this->validate($req, [
            'username' => 'required'
        ]);

        $username = $req->get('username');
        $account->forgotPasspwrd($username);
    }
}
