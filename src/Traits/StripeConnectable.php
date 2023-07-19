<?php

namespace Bisual\LaravelCashierStripeConnect\Traits;

trait StripeConnectable {
    /**
     * ATRIBUTTES
     */
    protected $stripe_connect_db_key = "stripe_connect_id";
    protected $stripe_status_db_key = "stripe_status";

    /**
     * ABSTRACT FUNCTIONS
     */

    /**
     * Map your model parameters to Stripe Account object: https://stripe.com/docs/api/accounts/object
     * @return array map of your model to an Stripe Account. Do not include metadata.
     */
    protected abstract function modelToStripeModel() : array;

    /**
     * Metadata to add to your Model to Stripe Account. Will be used on create and update.
     * Normally used to save local model identificators.
     * @return array
     */
    protected abstract function modelToStripeMetadata() : array;

    /**
     * Urls where the user will be redirected after completing (or failing) the onboarding process.
     */
    protected abstract function stripeOnboardingRefreshUrl(): string;
    protected abstract function stripeOnboardingReturnUrl(): string;

    /**
     * PUBLIC FUNCTIONS
     */
    public final function getConnectOnboardingLink() {
        $acct = $this->getStripeAccountId();
        if($acct == null) throw new \Exception("Stripe Account Id cannot be null.");
        $stripe = $this->getStripeInstance();
        $obj = $stripe->accountLinks->create([
            'account' => $acct,
            'refresh_url' => $this->stripeOnboardingRefreshUrl(),
            'return_url' => $this->stripeOnboardingReturnUrl(),
            'type' => 'account_onboarding',
        ]);

        return $obj['url'];
    }

    public final function isStripeAccountCreated() {
        return $this->getStripeAccountId() != null;
    }

    public final function isStripeAccountVerified() {
        return $this->getStripeStatus() === 'completed';
    }

    public final function createAsStripeCustomer() {
        $stripe = $this->getStripeInstance();
        $body = $this->modelToStripeModel();
        $body['metadata'] = $this->modelToStripeMetadata();
        $obj = $stripe->accounts->create($body);
        $acct = $obj['id'];
        $this->setStripeAccountId($acct);
        $this->markAccountAsRestricted();
    }

    public final function updateAsStripeCustomer() {
        $acct = $this->getStripeAccountId();
        if($acct == null) $this->createAsStripeCustomer();
        else {
            $stripe = $this->getStripeInstance();
            $stripe->accounts->update($acct, [
                'metadata' => $this->modelToStripeMetadata()
            ]);
        }
    }

    public final function checkAccountVerification() {
        $stripe = $this->getStripeInstance();
        $data = $stripe->accounts->retrieve($this->getStripeAccountId(),[]);
        if($data['details_submitted']===true) {
            $this->markAccountAsVerified();
        }
    }

    /**
     * PROTECTED FUNCTIONS
     */

    protected final function getStripeAccountId() {
        $key = $this->stripe_connect_db_key;
        return $this->$key;
    }

    protected final function setStripeAccountId(string $acct) {
        $key = $this->stripe_connect_db_key;
        $this->$key = $acct;
        $this->save();
    }

    protected final function getStripeStatus() {
        $key = $this->stripe_status_db_key;
        return $this->$key;
    }

    protected final function markAccountAsRestricted() {
        $key = $this->stripe_status_db_key;
        $this->$key = "restricted";
        $this->save();
    }

    /**
     * PRIVATE FUNCTIONS
     */

    private final function markAccountAsVerified() {
        $key = $this->stripe_status_db_key;
        $this->$key = "completed";
        $this->save();
    }

    private final static function getStripeInstance() {
        return new \Stripe\StripeClient(config('cashier.secret'));
    }

    /**
     * ATRIBUTOS FICTICIOS
     */
    public function getStripeEnabledAttribute(): bool {
        return $this->getStripeAccountId() && $this->isStripeAccountVerified();
    }
}
