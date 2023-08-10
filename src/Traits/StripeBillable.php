<?php

namespace Bisual\LaravelCashierStripeConnect\Traits;

trait StripeBillable
{
    use UseStripe;

    /**
     * ATRIBUTTES
     */
    protected $stripe_customer_id_key = 'stripe_id';

    protected $stripe_payment_method_id_key = 'stripe_payment_method_id';

    /**
     * ABSTRACT FUNCTIONS
     */
    /**
     * Map your model parameters to Stripe Account object: https://stripe.com/docs/api/accounts/object
     *
     * @return array map of your model to an Stripe Account. Do not include metadata.
     */
    abstract protected function modelToStripeModel(): array;

    /**
     * Metadata to add to your Model to Stripe Account. Will be used on create and update.
     * Normally used to save local model identificators.
     */
    abstract protected function modelToStripeMetadata(): array;

    /**
     * PUBLIC FUNCTIONS
     */
    final public function newSetupIntent(array $params = [])
    {
        $stripe = $this->getStripeInstance();

        return $stripe->setupIntents->create(array_merge($params, [
            'payment_method_types' => ['card'],
            'customer' => $this->getStripeCustomerId(),
        ]));
    }

    final public function confirmSetupIntent($id, array $params = [])
    {
        $stripe = $this->getStripeInstance();

        return $stripe->setupIntents->confirm($id, array_merge($params, []));
    }

    final public function getSetupIntent($id)
    {
        $stripe = $this->getStripeInstance();

        return $stripe->setupIntents->retrieve($id);
    }

    final public function createStripeCustomer()
    {
        $stripe = $this->getStripeInstance();
        $body = $this->modelToStripeModel();
        $body['metadata'] = $this->modelToStripeMetadata();
        $obj = $stripe->customers->create($body);
        $acct = $obj['id'];
        $this->setStripeAccountId($acct);
    }

    final public function updateAsStripeCustomer()
    {
        $acct = $this->getStripeCustomerId();
        if ($acct == null) {
            $this->createStripeCustomer();
        } else {
            $stripe = $this->getStripeInstance();
            $stripe->customers->update($acct, [
                'metadata' => $this->modelToStripeMetadata(),
            ]);
        }
    }

    public function setPaymentMethod(string $payment_method)
    {
        $key = $this->stripe_payment_method_id_key;
        $this->$key = $payment_method;
        $this->save();
    }

    /**
     * PROTECTED FUNCTIONS
     */
    final public function getStripeCustomerId()
    {
        $key = $this->stripe_customer_id_key;

        return $this->$key;
    }

    final protected function setStripeAccountId(string $acct)
    {
        $key = $this->stripe_customer_id_key;
        $this->$key = $acct;
        $this->save();
    }

    final protected function getStripePaymentMethodId()
    {
        $key = $this->stripe_payment_method_id_key;

        return $this->$key;
    }

    /**
     * PRIVATE FUNCTIONS
     */

    /**
     * ATRIBUTOS FICTICIOS
     */
    final public function getIsStripeCustomerRegisteredAttribute()
    {
        return $this->getStripeCustomerId() != null;
    }

    final public function getIsStripePaymentMethodSetAttribute()
    {
        return $this->getStripePaymentMethodId() != null;
    }
}
