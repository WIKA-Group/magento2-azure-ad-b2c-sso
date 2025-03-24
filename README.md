# Mage2 Module WIKA Azure B2C

Magento2 Module to add support for a login via Azure B2C

> **Important:** Ensure that the email address is delivered in the Azure B2C response.

## Configuration
The configuration can be found in the admin backend under:  
`Stores` -> `Settings` -> `Configuration` -> `WIKA GROUP` -> `Azure B2C`

If you do not want to use the provided button, you can disable it with the "Show button" setting.  
To trigger the redirect to Azure for the login, call the route `<your-domain>/azureb2c/login/authorize`.  
The response will be processed from the controller `<your-domain>/azureb2c/login/callback`.

![image](doc/Settings.png)

## Autologin after registration in B2C
The first time a customer is registrating on the shop, the redirect after the registration takes them back to the shop.
But the shop doesn't know that the user is logged in.
For this scenario, a GET parameter can be used to automatically redirect the user to the login again.
As the customer is already logged in in B2C, no input is required and a redirect will lead back to the shop, but now the customer will also be logged in to the shop.

![image](doc/AutologinSettings.png)

## Events
Observers for the event `customer_login` will be triggered if a customer uses Azure B2C to login.
