<?php

namespace Mild\Validation;

use Mild\Support\ServiceProvider;
use Mild\Contract\Validation\FactoryInterface;

class ValidationServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register()
    {
        $this->application->set('validation', function ($app) {
            return new Factory($app, [
                'ip' => IpRule::class,
                'max' => MaxRule::class,
                'min' => MinRule::class,
                'date' => DateRule::class,
                'file' => FileRule::class,
                'ipv4' => Ipv4Rule::class,
                'ipv6' => Ipv6Rule::class,
                'json' => JsonRule::class,
                'same' => SameRule::class,
                'size' => SizeRule::class,
                'uuid' => UuidRule::class,
                'int' => IntegerRule::class,
                'email' => EmailRule::class,
                'array' => ArrayRule::class,
                'alpha' => AlphaRule::class,
                'mimes' => MimesRule::class,
                'image' => ImageRule::class,
                'unique' => UniqueRule::class,
                'exists' => ExistsRule::class,
                'string' => StringRule::class,
                'digits' => DigitsRule::class,
                'nullable' => Nullable::class,
                'integer' => IntegerRule::class,
                'between' => BetweenRule::class,
                'boolean' => BooleanRule::class,
                'present' => PresentRule::class,
                'numeric'   => NumericRule::class,
                'distinct' => DistinctRule::class,
                'accepted' => AcceptedRule::class,
                'required' => RequiredRule::class,
                'timezone' => TimezoneRule::class,
                'mimetypes' => MimeTypesRule::class,
                'confirmed' => ConfirmedRule::class,
                'date_format' => DateFormatRule::class,
                'digits_between' => DigitsBetween::class,
                'alpha_numeric' => AlphaNumericRule::class
            ]);
        });

        $this->application->alias(Factory::class, 'validation');
        $this->application->alias(FactoryInterface::class, 'validation');
    }

    /**
     * @return void
     */
    public function boot()
    {
        Factory::macro('rule', function ($key, $value) {
            /**
             * @var Factory $factory
             */
            $factory = $this;

            $factory->setRule($key, $value);
        });

        Factory::macro('validate', function ($data, $rules, $messages = []) {
            /**
             * @var Factory $factory
             */
            $factory = $this;

            $factory->make($data, $rules, $messages)->validate();
        });
    }
}