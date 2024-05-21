# Mage2 Module WIKA Azure B2C

Magento2 Module to add support for a login via Azure B2C

## Configuration
The configuration can be found in the admin backend under:  
Stores -> Settings -> Configuration -> WIKA GROUP -> Azure B2C

If you do not want to use the provided button, you can disable it with the "Show button" setting.  
To trigger the redirect to Azure for the login, call the route `<your-domain>/azureb2c/login/authorize`.  
The response will be processed from the controller `<your-domain>/azureb2c/login/callback`.

![image](doc/Settings.png)

## Events
Observers for the event `customer_login` will be triggered if a customer uses Azure B2C to login.
