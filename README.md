[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/OrangeRT/AnonymizerBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/OrangeRT/AnonymizerBundle/?branch=master)

# Installation

## Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require orange-rt/anonymize-bundle "^0.1.0"
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

## Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new OrangeRT\AnonymizeBundle\OrangeRTAnonymizeBundle(),
        );

        // ...
    }

    // ...
}
```

# Usage

Doctrine managed entities can be anonymized by annotating the properties
that you want to be anonymized. There are two annotations available,
`@Anonymize` and `@AnonymizeEntity`.

## Anonymizing a property
Properties can be anonymized with the `@Anonymize` annotation like as follows:

```php
<?php

use OrangeRT\AnonymizeBundle\Mapping\Anonymize;

/**
 * A doctrine managed entity
 */
class Person
{
    /**
     * @Anonymize(faker="email")
     */
    private $username;
}
```

Upon calling `php bin/console anonymize:anonymize` the property `username`
will be updated to `$faker->email`. The faker is generated in the driver.

## Anonymizing callback
Sometimes it is required that properties need an advanced or a custom way
of anonymizing said entity. If a method is annotated with the `@Anonymize`
annotation, the method is called. If you need a faker you can typehint the
parameter, like in the example below:
```php
<?php

use OrangeRT\AnonymizeBundle\Mapping\Anonymize;

class Person
{
    private $username;
    private $email;
    
    /**
     * @Anonymize() 
     */
    public function anonymize(\Faker\UniqueGenerator $generator)
    {
        $this->username = $this->email = $generator->email;
    }
}
```

Upon anonymizing the person, a UniqueGenerator is created for the method and
the method is invoked with the generator. The username will be the same as
the email, and it will be a uniquely generated email.

## Excluding entities
It is possible to either skip a property, or to skip an entire object.

### Skipping objects
It is either possible to blacklist an object, or to whitelist an object.
The exclusions are done with a key value pair, where the key is the name
of the property, and the value is either a direct match with the value,
or a regex.

The inclusions are done in the same way, if one of the inclusions is matched,
the object is anonymized.

In the example below, every person will be anonymized except for the people
that have a username that ends with `@orangert.nl`.
```php
<?php

use OrangeRT\AnonymizeBundle\Mapping\AnonymizeEntity;
use OrangeRT\AnonymizeBundle\Mapping\Anonymize;

/**
 * 
 * @AnonymizeEntity(exclusions={"username": "/@orangert.nl$/"})
 */
class Person
{
    /**
     * @Anonymize(faker="email", unique=true)
     */
    private $username;
    
    /**
     * @Anonymize(faker="firstName")
     */
    private $firstname;
    
    /**
     * @Anonymize(faker="lastName")
     */
    private $lastname;
}
```

## Changing the faker locale
The default faker locale is `nl_NL`. To set the locale:
```yaml
# app/config/parameters.yml
    // ...
    orange_rt_anonymize.default_locale: 'en_US'
```

## Unique variables
For properties like email and usernames, unique values should be used.
The Anonymize property has a `unique=true` flag to set use the
`UniqueGenerator` provided by the Faker library. If a callback needs
the UniqueGenerator, typehint the generator with the UniqueGenerator.

# Contributing

## Pull requests
There might be open issues in the Github issue tracker. I'm open to receive
new pull requests and will check them as soon as I possibly can.

## Issues
If you find any bugs, please report them at the issue tracker. I will look at
them as soon as I possibly can.
