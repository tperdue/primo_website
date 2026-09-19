<?php

namespace App\Controllers;

use App\Libraries\QuoteCart;
use App\Libraries\QuoteQuestionCatalog;
use CodeIgniter\HTTP\RedirectResponse;

class Quote extends BaseController
{
    public function index(): string
    {
        $cart = new QuoteCart();
        $serviceIds = $cart->serviceIds();

        return view('quote/index', $this->publicSiteData() + [
            'services' => $cart->services(),
            'questionGroups' => (new QuoteQuestionCatalog())->forServices($serviceIds),
            'answers' => $cart->answers(),
        ]);
    }

    public function add(int $serviceId): RedirectResponse
    {
        if (! (new QuoteCart())->add($serviceId)) {
            return redirect()->to('/services')->with('quoteError', 'That service could not be added to your quote.');
        }

        return redirect()->to('/quote')->with('quoteMessage', 'Service added to your quote.');
    }

    public function remove(int $serviceId): RedirectResponse
    {
        $cart = new QuoteCart();
        $serviceIds = $cart->serviceIds();
        $cart->saveAnswers($this->request->getPost('answers'), (new QuoteQuestionCatalog())->forServices($serviceIds));
        $cart->remove($serviceId);

        return redirect()->to('/quote')->with('quoteMessage', 'Service removed from your quote.');
    }

    public function save(): RedirectResponse
    {
        $cart = new QuoteCart();
        $cart->saveAnswers(
            $this->request->getPost('answers'),
            (new QuoteQuestionCatalog())->forServices($cart->serviceIds()),
        );
        $destination = $this->request->getPost('next') === 'services' ? '/services' : '/quote';

        return redirect()->to($destination)->with('quoteMessage', 'Quote progress saved.');
    }
}
