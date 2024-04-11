<?php

namespace Bisual\LaravelCashierStripeConnect\Traits;

trait StripeVerifiable
{
    use UseStripe;

    public function createVerificationSession(bool $require_matching_selfie = false, bool $require_live_capture = false)
    {
        $stripe = $this->getStripeInstance();

        return $stripe->identity->verificationSessions->create([
            'type' => 'document',
            'metadata' => [
                'user_id' => $this->id,
            ],
            'options' => [
                'document' => [
                    'require_matching_selfie' => $require_matching_selfie,
                    'require_live_capture' => $require_live_capture,
                ],
            ],
        ]);
    }

    public function getVerificationSession($id, array $options = [])
    {
        $stripe = $this->getStripeInstance();

        return $stripe->identity->verificationSessions->retrieve($id, $options);
    }
}
