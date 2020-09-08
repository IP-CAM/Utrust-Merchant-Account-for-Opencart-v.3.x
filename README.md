![Utrust integrations - OpenCart](https://user-images.githubusercontent.com/1558992/92476185-75cebd80-f1d6-11ea-901e-144a5ad3885b.png)

# Utrust for OpenCart 3

**Demo Store:** https://opencart.store.utrust.com/

Accept Bitcoin, Ethereum, Utrust Token and other cryptocurrencies directly on your store with the Utrust payment gateway for OpenCart 3.
Utrust is cryptocurrency agnostic and provides fiat settlements.
The Utrust extension extends OpenCart allowing you to take cryptocurrency payments directly on your store via the Utrust API.
Find out more about Utrust at [utrust.com](https://utrust.com).

## Requirements

- Utrust Merchant account
- Online store in OpenCart v3.0 (or greater)

## Install and Update

### Installing

1. Copy the folders inside `/upload` to the root of your OpenCart installation via FTP.
2. Go to your OpenCart admin dashboard.
3. Click on _Extensions_ > _Modifications_.
4. Click on the blue arrows on the top right to refresh the Modifications.
5. Click on _Extensions_ > _Extensions_ and choose _payment_ on the dropdown selector.
6. Find the _Utrust_ entry and click on the "+" icon to install it.

## Setup

### On the Utrust side

1. Go to the [Utrust Merchant dashboard](https://merchants.utrust.com).
2. Log in, or sign up if you haven't yet.
3. In the sidebar on the left choose _Integrations_.
4. Select _OpenCart_ in the dropdown.
5. Click the button _Generate Credentials_.
6. You will now see your `Api Key` and `Webhook Secret`, save them somewhere safe temporarily.

   :warning: You will only be able to see the `Webhook Secret` once! After refreshing or changing page you will no longer be able to copy it. However, you can always regenerate your credentials as needed.

   :no_entry_sign: Don't share your credentials with anyone. They can use it to place orders **on your behalf**.

### On the OpenCart side

1. Go to your OpenCart admin dashboard.
2. Click on _Extensions_ > _Extensions_ and choose _payment_ on the dropdown selector.
3. Find _Utrust_ entry and click the _edit_ icon.
4. Add your `Api Key`, `Webhook Secret` and change other settings you find appropriate.
5. Click the _save_ icon on top right.

## Frequently Asked Questions

Find below a list of the most common questions about the Utrust for OpenCart plugin.

Don't find what you're looking for in this list? Feel free to reach us [by opening an issue on GitHub](https://github.com/digito-solutions/Plugin-Utrust-Opencart-3/issues/new).

### Does this support both live mode and test mode for testing?

Yes, it does - choosing between live and test mode is driven by the API keys you use. They are different in both environments. Live API keys won't work for the test environment, and vice-versa.

### What happens if I cancel the Order manually?

:construction: We are working on it. Our API is not ready yet for merchant manual changes. If you need to change the Order status, change it in OpenCart and then go to our Utrust Merchant Dashboard to start a refund.

## Support

Feel free to reach [by opening an issue on GitHub](https://github.com/digito-solutions/Plugin-Utrust-Opencart-3/issues/new) if you need any help with the Utrust for OpenCart plugin.

If you're having specific problems with your account, then please contact support@utrust.com.

## Contribute

This plugin was written by [Digito Solutions](https://github.com/hellodevapps).
It's now opened it to the world so that the community using this extension may have the chance of shaping its development.

You can contribute by simply letting us know your suggestions or any problems that you find [by opening an issue on GitHub](https://github.com/digito-solutions/Plugin-Utrust-Opencart-3/issues/new).

You can also fork the repository on GitHub and open a pull request for the `master` branch with your missing features and/or bug fixes.
Please make sure the new code follows the same style and conventions as already written code.
