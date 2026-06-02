<?php

namespace App\Gate;

use App\Gate\Policies\AbstractPolicy;
use App\User\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class Gate
{
    protected const array EVALUATION_CRITERIA_PRIORITY = [
        AbstractPolicy::FORCE_DENY => false,
        AbstractPolicy::FORCE_ALLOW => true,
        AbstractPolicy::DENY => false,
        AbstractPolicy::ALLOW => true,
    ];

    protected array $policies = [];

    protected $userResolver;

    protected array $policyClasses;

    public function __construct(
        callable $userResolver,
        array $policyClasses
    ) {
        $this->policyClasses = $policyClasses;
        $this->userResolver = $userResolver;
    }

    public function forUser(User $user): static
    {
        return new static(
            fn () => $user,
            $this->policyClasses
        );
    }

    protected function resolveUser(): User
    {
        return call_user_func($this->userResolver);
    }

    public function any($abilities, $model = null): bool
    {
        return (new Collection($abilities))->contains(fn ($ability) => $this->allows($ability, $model));
    }

    public function allows(string $ability, $model): bool
    {
        if (is_null($actor = $this->resolveUser())) {
            return false;
        }

        $results = [];
        $appliedPolicies = [];

        if ($model) {
            $modelClasses = is_string($model) ? [$model] : array_merge(class_parents($model), [get_class($model)]);

            foreach ($modelClasses as $class) {
                $appliedPolicies = array_merge($appliedPolicies, $this->getPolicies($class));
            }
        } else {
            $appliedPolicies = $this->getPolicies(AbstractPolicy::GLOBAL);
        }

        foreach ($appliedPolicies as $policy) {
            $results[] = $policy->checkAbility($actor, $ability, $model);
        }

        foreach (static::EVALUATION_CRITERIA_PRIORITY as $criteria => $decision) {
            if (in_array($criteria, $results, true)) {
                return $decision;
            }
        }

        // If no policy covered this permission query, we will only grant
        // the permission if the actor's groups have it. Otherwise, we will
        // not allow the user to perform this action.
        if ($actor->isAdmin() || $actor->hasPermission($ability)) {
            return true;
        }

        return false;
    }

    protected function getPolicies(string $model)
    {
        $compiledPolicies = Arr::get($this->policies, $model);
        if (is_null($compiledPolicies)) {
            $policyClasses = Arr::get($this->policyClasses, $model, []);
            $compiledPolicies = array_map(function ($policyClass) {
                return app($policyClass);
            }, $policyClasses);
            Arr::set($this->policies, $model, $compiledPolicies);
        }

        return $compiledPolicies;
    }
}
