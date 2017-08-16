# Omnipay: Heartland

**Heartland driver for the Omnipay PHP payment processing library**

[![Build Status](https://travis-ci.org/hps/omnipay-heartland.png?branch=master)](https://travis-ci.org/hps/omnipay-heartland)
[![Latest Stable Version](https://poser.pugx.org/hps/omnipay-heartland/version.png)](https://packagist.org/packages/hps/omnipay-heartland)
[![Total Downloads](https://poser.pugx.org/hps/omnipay-heartland/d/total.png)](https://packagist.org/packages/hps/omnipay-heartland)

[Omnipay](https://github.com/thephpleague/omnipay) is a framework agnostic, multi-gateway payment
processing library for PHP 5.3+. This package implements Heartland support for Omnipay.

## Installation

Omnipay is installed via [Composer](http://getcomposer.org/). To install, simply add it
to your `composer.json` file:

```json
{
    "require": {
        "hps/omnipay-heartland": "dev-master"
    }
}
```

And run composer to update your dependencies:

    $ curl -s http://getcomposer.org/installer | php
    $ php composer.phar update

## Basic Usage

The following gateways are provided by this package:

* Heartland’s [**Portico Gateway API**](http://developer.heartlandpaymentsystems.com/Portico)
* Heartland's [**PayPlan API**](https://developer.heartlandpaymentsystems.com/Resource/download/payplan-devguide)

For general usage instructions, please see the main [Omnipay](https://github.com/thephpleague/omnipay)
repository.

### Heartland Single-use Tokenization

The Heartland integration is fairly straight forward. Essentially you just pass
a `token` field through to Heartland instead of the regular credit card data.

Start by following the standard Heartland Single-use Tokenization guide here:
[https://developer.heartlandpaymentsystems.com/documentation/v2/introduction](https://developer.heartlandpaymentsystems.com/documentation/v2/introduction)

After that you will have a `payment_token` field which will be submitted to your server.
Simply pass this through to the gateway as `token`, instead of the usual `card` array:

```php
$token = $_POST['payment_token'];

$response = $gateway->purchase([
    'amount' => '10.00',
    'currency' => 'USD',
    'token' => $token,
])->send();
```

## Testing & Certification

<img src="http://developer.heartlandpaymentsystems.com/Resource/Download/sdk-readme-icon-tools" align="right"/>
Testing your implementation in our Certification/Sandbox environment helps to identify and squash bugs before you begin processing transactions in the production environment. While you are encouraged to run as many test transactions as you can, Heartland provides a specific series of tests that you are required to complete before receiving Certification. Please contact Heartland to initiate certification for your integration. For eComm integrations please email our <a href="mailto:SecureSubmitCert@e-hps.com?Subject=Certification Request&Body=I am ready to start certifying my integration! ">Specialty Products Team</a>, for POS developers please email <a href="mailto:integration@e-hps.com?Subject=Certification Request&Body=I am ready to start certifying my integration! ">Integrations</a>.

## Support

If you are having general issues with Omnipay, we suggest posting on
[Stack Overflow](http://stackoverflow.com/). Be sure to add the
[omnipay tag](http://stackoverflow.com/questions/tagged/omnipay) so it can be easily found.

If you want to keep up to date with release announcements, discuss ideas for the project,
or ask more detailed questions, there is also a [mailing list](https://groups.google.com/forum/#!forum/omnipay) which
you can subscribe to.

If you believe you have found a bug, please report it using the [GitHub issue tracker](https://github.com/hps/omnipay-heartland/issues),
or better yet, fork the library and submit a pull request.
