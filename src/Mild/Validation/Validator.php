<?php

namespace Mild\Validation;

use Mild\Support\Arr;
use Mild\Contract\Validation\RuleInterface;
use Mild\Contract\Validation\MessageInterface;
use Mild\Contract\Validation\ValidatorInterface;
use Mild\Contract\Validation\ShouldSkipInterface;
use Mild\Contract\Validation\GatherDataInterface;

class Validator implements ValidatorInterface
{
    /**
     * @var GatherDataInterface
     */
    protected $data;
    /**
     * @var array
     */
    protected $rules;
    /**
     * @var MessageInterface
     */
    protected $message;

    /**
     * Validator constructor.
     *
     * @param MessageInterface $message
     * @param GatherDataInterface $data
     * @param array $rules
     */
    public function __construct(MessageInterface $message, GatherDataInterface $data, array $rules)
    {
        $this->data = $data;
        $this->message = $message;
        foreach ($rules as $key => $value) {
            if ($this->shouldSkipValidation($key, $value)) {
                unset($rules[$key]);
            }
        }

        $this->rules = $rules;
    }

    /**
     * @return void
     * @throws ValidationException
     */
    public function validate()
    {
        foreach ($this->rules as $key => $rules) {

            $value = $this->data->get($key);

            foreach (Arr::wrap($rules) as $rule) {
                if ($rule instanceof RuleInterface === false) {
                    throw new InvalidRuleException($rule);
                }
                /**
                 * @var RuleInterface $rule
                 */
                $rule->handle($this->message, $this->data, $key, $value);
            }
        }

        if ($this->message->isEmpty() === false) {
            throw new ValidationException($this);
        }
    }

    /**
     * @return bool
     */
    public function passes()
    {
        try {
            $this->validate();
            return true;
        } catch (ValidationException $e) {
            return false;
        }
    }

    /**
     * @return GatherDataInterface
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * @return MessageInterface
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param $key
     * @param $rules
     * @return bool
     */
    protected function shouldSkipValidation($key, $rules)
    {
        foreach ($rules as $rule) {
            if ($rule instanceof ShouldSkipInterface && $rule->skipWhen($this->data, $key, $this->data->get($key))) {
                return true;
            }
        }

        return false;
    }
}