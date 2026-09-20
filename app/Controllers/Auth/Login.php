<?php

namespace App\Controllers\Auth;

use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Shield\Authentication\Authenticators\Session;
use CodeIgniter\Shield\Controllers\LoginController;

class Login extends LoginController
{
    public function loginView(): RedirectResponse|string
    {
        if (auth()->loggedIn()) {
            return redirect()->to($this->destination())->withCookies();
        }

        /** @var Session $authenticator */
        $authenticator = auth('session')->getAuthenticator();
        if ($authenticator->hasAction()) {
            return redirect()->route('auth-action-show');
        }

        return view('auth/login');
    }

    public function loginAction(): RedirectResponse
    {
        if (! $this->validateData($this->request->getPost(), $this->getValidationRules(), [], config('Auth')->DBGroup)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $credentials = $this->request->getPost(setting('Auth.validFields')) ?? [];
        $credentials = array_filter($credentials);
        $credentials['password'] = $this->request->getPost('password');

        /** @var Session $authenticator */
        $authenticator = auth('session')->getAuthenticator();
        $result = $authenticator->remember(false)->attempt($credentials);
        if (! $result->isOK()) {
            return redirect()->route('login')->withInput()->with('error', $result->reason());
        }
        if ($authenticator->hasAction()) {
            return redirect()->route('auth-action-show')->withCookies();
        }

        return redirect()->to($this->destination())->withCookies();
    }

    private function destination(): string
    {
        $user = auth()->user();

        if ($user !== null && $user->inGroup('admin')) {
            return '/admin/';
        }

        return $user !== null && $user->inGroup('customer') ? '/portal' : '/';
    }
}
