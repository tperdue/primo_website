<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\BusinessSettingsModel;
use CodeIgniter\HTTP\RedirectResponse;

class BusinessSettings extends BaseController
{
    public function edit(): string
    {
        $business = (new BusinessSettingsModel())->find(1);
        return view('admin/settings', [
            'business' => $business,
            'businessName' => $business['business_name'] ?? 'Design studio',
        ]);
    }

    public function update(): RedirectResponse
    {
        $rules = [
            'business_name' => 'required|max_length[120]',
            'tagline' => 'required|max_length[180]',
            'description' => 'required|max_length[2000]',
            'contact_email' => 'permit_empty|valid_email|max_length[254]',
            'notification_email' => 'permit_empty|valid_email|max_length[254]',
        ];

        $data = [];
        foreach (array_keys($rules) as $field) {
            $value = $this->request->getPost($field);
            $data[$field] = is_string($value) ? trim($value) : $value;
        }

        if (! $this->validateData($data, $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        (new BusinessSettingsModel())->update(1, $data);

        return redirect()->to('/admin/settings')->with('message', 'Business settings saved.');
    }
}
