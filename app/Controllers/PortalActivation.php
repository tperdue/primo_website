<?php

namespace App\Controllers;

use App\Models\BusinessSettingsModel;
use App\Models\CustomerModel;
use App\Models\CustomerPortalInvitationModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Validation\ValidationRules;
use Throwable;

class PortalActivation extends BaseController
{
    public function show(string $token): string
    {
        [$invitation, $customer] = $this->resolve($token);

        return view('portal/activate', [
            'businessName' => $this->businessName(),
            'customer' => $customer,
            'token' => $token,
            'isValid' => $invitation !== null,
        ]);
    }

    public function activate(string $token): RedirectResponse
    {
        [$invitation, $customer] = $this->resolve($token);
        if ($invitation === null || $customer === null) {
            return redirect()->to('/portal/activate/' . $token)->with('error', 'This invitation is invalid or has expired.');
        }

        $rules = new ValidationRules();
        $passwordRules = $rules->getPasswordRules();
        $passwordRules['rules'][] = 'min_length[12]';
        if (! $this->validateData($this->request->getPost(), [
            'password' => $passwordRules,
            'password_confirm' => $rules->getPasswordConfirmRules(),
        ], [], config('Auth')->DBGroup)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $username = 'customer-' . $customer['id'] . '-' . bin2hex(random_bytes(4));
        $password = (string) $this->request->getPost('password');
        $strength = service('passwords')->check($password, new User([
            'username' => $username,
            'email' => $customer['email'],
        ]));
        if (! $strength->isOK()) {
            return redirect()->back()->withInput()->with('errors', ['password' => $strength->reason()]);
        }

        $provider = auth()->getProvider();
        if ($provider->findByCredentials(['email' => $customer['email']]) !== null) {
            return redirect()->back()->with('error', 'An account already uses this email address. Contact the studio for help.');
        }

        $db = db_connect();
        $db->transStart();
        try {
            $db->table('customer_portal_invitations')
                ->where('id', $invitation['id'])
                ->where('used_at', null)
                ->where('expires_at >=', date('Y-m-d H:i:s'))
                ->update(['used_at' => date('Y-m-d H:i:s')]);
            if ($db->affectedRows() !== 1) {
                throw new \RuntimeException('This invitation has already been used or expired.');
            }
            if (! $provider->save(new User([
                'username' => $username,
                'email' => $customer['email'],
                'password' => $password,
            ]))) {
                throw new \RuntimeException(implode(' ', $provider->errors()));
            }
            $user = $provider->findById($provider->getInsertID());
            if ($user === null) {
                throw new \RuntimeException('Customer account could not be created.');
            }
            $user->addGroup('customer');
            (new CustomerModel())->update($customer['id'], ['user_id' => $user->id]);
        } catch (Throwable $exception) {
            $db->transRollback();
            log_message('error', 'Customer portal activation failed: {message}', ['message' => $exception->getMessage()]);

            return redirect()->back()->with('error', 'The account could not be created. Please try again or contact the studio.');
        }
        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->back()->with('error', 'The account could not be created. Please try again or contact the studio.');
        }

        return redirect()->to('/login')->with('message', 'Your account is ready. Sign in to open your client portal.');
    }

    /** @return array{0: array<string, mixed>|null, 1: array<string, mixed>|null} */
    private function resolve(string $token): array
    {
        $this->response
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate')
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Referrer-Policy', 'no-referrer');
        if (! preg_match('/\A[a-f0-9]{64}\z/', $token)) {
            return [null, null];
        }
        $invitation = (new CustomerPortalInvitationModel())
            ->where('token_hash', hash('sha256', $token))
            ->where('used_at', null)
            ->where('expires_at >=', date('Y-m-d H:i:s'))
            ->first();
        if ($invitation === null) {
            return [null, null];
        }
        $customer = (new CustomerModel())->find($invitation['customer_id']);
        if ($customer === null || $customer['user_id'] !== null) {
            return [null, null];
        }

        return [$invitation, $customer];
    }

    private function businessName(): string
    {
        return (new BusinessSettingsModel())->find(1)['business_name'] ?? 'Design studio';
    }
}
