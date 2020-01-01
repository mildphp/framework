<?php

namespace Mild\Validation;

use Mild\Support\Arr;
use Mild\Support\MessageBag;
use Mild\Support\Traits\Macroable;
use Mild\Contract\Validation\RuleInterface;
use Mild\Contract\Validation\FactoryInterface;
use Mild\Contract\Container\ContainerInterface;
use Mild\Contract\Translation\TranslatorInterface;

class Factory implements FactoryInterface
{
    use Macroable;

    /**
     * @var array
     */
    protected $rules;
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Factory constructor.
     *
     * @param ContainerInterface $container
     * @param array $rules
     */
    public function __construct(ContainerInterface $container, array $rules = [])
    {
        $this->container = $container;
        $this->setRules($rules);
    }

    /**
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @return Validator
     */
    public function make(array $data, array $rules, array $messages = [])
    {
        return new Validator(new Message($this->container->get(TranslatorInterface::class)), $data = new GatherData($data), $this->parseRules($data, $rules, $messages));
    }

    /**
     * @param $key
     * @param $value
     * @return void
     */
    public function setRule($key, $value)
    {
        $this->rules[$key] = $value;
    }

    /**
     * @return array
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param array $rules
     */
    public function setRules(array $rules)
    {
        $this->rules = $rules;
    }

    /**
     * @param $data
     * @param $rules
     * @param array $messages
     * @return array
     */
    protected function parseRules($data, $rules, $messages = [])
    {
        $message = new MessageBag($this->normalizeMessages($rules = $this->normalizeRules($data, $rules), $messages));

        foreach ($rules as $key => $value) {
            $rules[$key] = $this->buildRule($value, $key, $message);
        }

        return $rules;
    }

    /**
     * @param GatherData $data
     * @param $rules
     * @return array
     */
    protected function normalizeRules($data, $rules)
    {
        foreach ($this->getExplicitRuleKeys($data, $rules) as $key => $values) {
            $rules = array_merge(array_fill_keys($values, $rules[$key]), $rules);
            unset($rules[$key]);
        }

        return $rules;
    }

    /**
     * @param GatherData $data
     * @param $rules
     * @return array
     */
    protected function getExplicitRuleKeys($data, $rules)
    {
        $keys = [];

        foreach ($rules as $key => $value) {
            if (($index = strpos($key, '.*')) === false || !is_array(($item = $data->get($base = substr($key, 0, $index))))) {
                continue;
            }
            for ($i = 0; $i < count($item); ++$i) {
                $keys[$key][] = $base.'.'.$i;
            }
        }

        return $keys;
    }

    /**
     * @param $messages
     * @param $rules
     * @return array
     */
    protected function normalizeMessages($rules, $messages)
    {
        foreach ($this->getExplicitMessagesKeys($messages, $rules) as $key => $values) {
            $messages = array_merge(array_fill_keys($values, $messages[$key]), $messages);
            unset($messages[$key]);
        }

        return $messages;
    }

    /**
     * @param $messages
     * @param $rules
     * @return array
     */
    protected function getExplicitMessagesKeys($messages, $rules)
    {
        $keys = array_keys($rules);
        $explicitKeys = [];
        foreach ($messages as $key => $value) {
            if (($index = strpos($key, '.*')) === false) {
                continue;
            }
            foreach (preg_grep('/^'.str_replace('\*', '([^\.]+)', preg_quote(substr($key, 0, $index), '/')).'/', $keys) as $match) {
                $explicitKeys[$key][] = $match.substr($key, $index + 2);
            }
        }
        return $explicitKeys;
    }

    /**
     * @param $rules
     * @param $key
     * @param MessageBag $message
     * @return array|RuleInterface
     */
    protected function buildRule($rules, $key, $message)
    {
        $values = [];

        $rules = Arr::wrap($rules);

        if (isset($rules['rule'])) {
            return $this->createRule($rules['rule'], $key, $message, isset($rules['parameters']) ? $rules['parameters'] : []);
        }
        foreach ($rules as $name => $rule) {
            if (isset($this->rules[$name])) {
                $values = $this->mergeOrPushRule($values, $this->createRule($name, $key, $message, $rule));
                continue;
            }
            if (is_string($rule)) {
                if (strpos($rule, '|') !== false) {
                    $values = $this->mergeOrPushRule($values, $this->buildRule(explode('|', $rule), $key, $message));
                    continue;
                }
                if (strpos($rule, ':') !== false) {
                    $values = $this->mergeOrPushRule($values, $this->buildRule($this->buildParametersRule($rule), $key, $message));
                    continue;
                }
                $values = $this->mergeOrPushRule($values, $this->createRule($rule, $key, $message));
            } elseif (is_array($rule)) {
                $values = $this->mergeOrPushRule($values, $this->buildRule($rule, $key, $message));
            } else {
                $values = $this->mergeOrPushRule($values, $this->createRule($rule, $key, $message));
            }
        }

        return $values;
    }

    /**
     * @param $values
     * @param $rule
     * @return array
     */
    protected function mergeOrPushRule($values, $rule)
    {
        if (is_array($rule)) {
            return array_merge($values, $rule);
        }

        $values[] = $rule;

        return $values;
    }


    /**
     * @param $rule
     * @param $key
     * @param MessageBag $message
     * @param array $parameters
     * @return RuleInterface
     */
    protected function createRule($rule, $key, $message, $parameters = [])
    {
        if (is_callable($rule)) {
            $convert = true;
            if (is_string($rule) && isset($this->rules[$rule])) {
                $convert = false;
            }

            if ($convert) {
                $rule = $this->createCallableRule($rule);
            }
        }

        if ($rule instanceof RuleInterface === false) {
            if (!is_string($rule)) {
                throw new InvalidRuleException($rule);
            }

            $message = $message->get($key.'.'.$rule);

            if (isset($this->rules[$rule])) {
                $rule = $this->rules[$rule];
            }

            if (is_callable($rule)) {
                $rule = $this->createCallableRule($rule, $message);
            } elseif (is_string($rule)) {
                $rule = $this->container->make($rule, array_values(Arr::wrap($parameters)));
            }

            if ($rule instanceof Rule) {
                $rule->setCustomMessage($message);
            }
        }

        return $rule;
    }

    /**
     * @param $rule
     * @return array
     */
    protected function buildParametersRule($rule)
    {
        [$rule, $parameters] = explode(':', $rule);

        $parameters = array_map('trim', explode(',', $parameters));

        return compact('rule', 'parameters');
    }

    /**
     * @param $rule
     * @param null $message
     * @return CallableRule
     */
    protected function createCallableRule($rule, $message = null)
    {
        return new CallableRule($this->container, $rule, $message);
    }
}