<?php

namespace Bisual\LaravelCashierStripeConnect\Traits;

trait StripeConnectable
{
    use UseStripe;

    /**
     * ATRIBUTTES
     */
    protected $stripe_connect_db_key = 'stripe_connect_id';

    protected $stripe_status_db_key = 'stripe_status';

    /**
     * ABSTRACT FUNCTIONS
     */

    /**
     * Map your model parameters to Stripe Account object: https://stripe.com/docs/api/accounts/object
     *
     * @return array map of your model to an Stripe Account. Do not include metadata.
     */
    abstract protected function modelToStripeAccount(): array;

    /**
     * Metadata to add to your Model to Stripe Account. Will be used on create and update.
     * Normally used to save local model identificators.
     */
    abstract protected function modelToStripeMetadata(): array;

    /**
     * Urls where the user will be redirected after completing (or failing) the onboarding process.
     */
    abstract protected function stripeOnboardingRefreshUrl(): string;

    abstract protected function stripeOnboardingReturnUrl(): string;

    /**
     * PUBLIC FUNCTIONS
     */
    final public function getConnectOnboardingLink()
    {
        $acct = $this->getStripeConnectAccountId();
        if ($acct == null) {
            throw new \Exception('Stripe Account Id cannot be null.');
        }
        $stripe = $this->getStripeInstance();
        $obj = $stripe->accountLinks->create([
            'account' => $acct,
            'refresh_url' => $this->stripeOnboardingRefreshUrl(),
            'return_url' => $this->stripeOnboardingReturnUrl(),
            'type' => 'account_onboarding',
        ]);

        return $obj['url'];
    }

    final public function isStripeAccountCreated()
    {
        return $this->getStripeConnectAccountId() != null;
    }

    final public function isStripeAccountVerified()
    {
        return $this->getStripeStatus() === 'completed';
    }

    final public function createAsStripeConnectCustomer()
    {
        $stripe = $this->getStripeInstance();
        $body = $this->modelToStripeAccount();
        $body['metadata'] = $this->modelToStripeMetadata();
        $obj = $stripe->accounts->create($body);
        $acct = $obj['id'];
        $this->setStripeConnectAccountId($acct);
        $this->markAccountAsRestricted();
    }

    final public function updateAsStripeConnectCustomer(array $params = [])
    {
        $acct = $this->getStripeConnectAccountId();
        if ($acct == null) {
            $this->createAsStripeConnectCustomer();
        } else {
            $stripe = $this->getStripeInstance();
            $stripe->accounts->update($acct, array_merge([
                'metadata' => $this->modelToStripeMetadata(),
            ], $params));
        }
    }

    final public function updateWithManualPayouts()
    {
        return $this->updateAsStripeConnectCustomer(['settings' => ['payouts' => ['schedule' => ['interval' => 'manual']]]]);
    }

    final public function checkAccountVerification()
    {
        $stripe = $this->getStripeInstance();
        $data = $stripe->accounts->retrieve($this->getStripeConnectAccountId(), []);
        if ($data['details_submitted'] === true) {
            $this->markAccountAsVerified();
        }
    }

    final public function transfer(int $amountInCents, array $params = [])
    {
        if (! $this->isStripeEnabled()) {
            throw new \Exception('Stripe is not enabled on this account.');
        }
        $stripe = $this->getStripeInstance();

        return $stripe->transfers->create(array_merge($params, [
            'amount' => $amountInCents,
            'currency' => $this->getCurrency(),
            'destination' => $this->getStripeConnectAccountId(),
        ]));
    }

    final public function isStripeEnabled()
    {
        return $this->getStripeConnectAccountId() && $this->isStripeAccountVerified();
    }

    public function getCurrency()
    {
        return 'eur';
    }

    final public function getExternalAccounts(array $params = [])
    {
        $stripe = $this->getStripeInstance();

        return $stripe->accounts->allExternalAccounts(
            $this->getStripeConnectAccountId(),
            $params
        );
    }

    final public function getBalance(array $params = [])
    {
        $stripe = $this->getStripeInstance();

        return $stripe->balance->retrieve($params, ['stripe_account' => $this->getStripeConnectAccountId()]);
    }

    final public function createFullPayout()
    {
        $stripe = $this->getStripeInstance();
        $balance = $this->getBalance();
        $payouts = [];
        foreach ($balance['available'] as $availability) {
            $amount = $availability['amount'];
            $currency = $availability['currency'];

            $payout = $stripe->payouts->create(
                [
                    'amount' => $amount,
                    'currency' => $currency,
                ],
                ['stripe_account' => $this->getStripeConnectAccountId()]
            );

            array_push($payouts, $payout);
        }

        return $payout;
    }

    public function getPayouts($limit = 10, $starting_after = null, $ending_before = null)
    {
        $stripe = $this->getStripeInstance();
        $params = ['limit' => $limit];

        if ($starting_after) {
            $params['starting_after'] = $starting_after;
        }

        if ($ending_before) {
            $params['ending_before'] = $ending_before;
        }

        return $stripe->payouts->all(
            $params,
            ['stripe_account' => $this->getStripeConnectAccountId()]
        );
    }

    public function getAllTransactions($limit = 100, $starting_after = null, $ending_before = null)
    {
        $stripe = $this->getStripeInstance();
        $stripeAccountId = $this->getStripeConnectAccountId();
        
        $params = ['limit' => $limit];

        if ($starting_after) {
            $params['starting_after'] = $starting_after;
        }

        if ($ending_before) {
            $params['ending_before'] = $ending_before;
        }
        
        // Charges (incoming payments), 3eros a usuario
        $charges = $stripe->charges->all(
            $params,
            ['stripe_account' => $stripeAccountId]
        );

        // Payouts (outgoing payments), el usuario a 3eros
        $payouts = $stripe->payouts->all(
            $params,
            ['stripe_account' => $stripeAccountId]
        );

        // Combine charges and payouts into one array
        $transactions = [
            'charges' => $charges->data,
            'payouts' => $payouts->data,
        ];

        return $transactions;
    }

    /**
     * PROTECTED FUNCTIONS
     */
    final protected function getStripeConnectAccountId()
    {
        $key = $this->stripe_connect_db_key;

        return $this->$key;
    }

    final protected function setStripeConnectAccountId(string $acct)
    {
        $key = $this->stripe_connect_db_key;
        $this->$key = $acct;
        $this->save();
    }

    final protected function getStripeStatus()
    {
        $key = $this->stripe_status_db_key;

        return $this->$key;
    }

    final protected function markAccountAsRestricted()
    {
        $key = $this->stripe_status_db_key;
        $this->$key = 'restricted';
        $this->save();
    }

    /**
     * PRIVATE FUNCTIONS
     */
    private function markAccountAsVerified()
    {
        $key = $this->stripe_status_db_key;
        $this->$key = 'completed';
        $this->save();
    }

    /**
     * ATRIBUTOS FICTICIOS
     */
    public function getStripeEnabledAttribute(): bool
    {
        return $this->isStripeEnabled();
    }
}

