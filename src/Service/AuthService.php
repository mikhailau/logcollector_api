<?php


namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

class AuthService
{
    public function checkLogin(Request $request)
    {
        return \pam_auth($request->headers->get('php-auth-user'),$request->headers->get('php-auth-pw'));
    }
}