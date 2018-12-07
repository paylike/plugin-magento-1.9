#Testing

As you can see the plugin is bundled with selenium testing on this repository. You can use the tests, if you have some experience with testing it could be helpful. 
*DO NOT USE IN PRODUCTION, THE TESTS MANIPULATE ORDERS*

## Requirements

* A magento 1.9 installation is required, in which you need to have the luma theme installed with sample data. 
* You need to disable csrf protection from the advanced config section of the plugin admin. Also appending the key to the url needs to be disabled.
* You need a customer and admin login with the same password. The billing info for the customer should already be filled in. Just manually create an order first with an user. 

## Getting started

1. Follow 1 and 2 from the [Steward readme page](https://github.com/lmc-eu/steward#getting-started)
2. Create an env file in the root folder and add the following:
`
ENVIRONMENT_URL="https://magento.url"
ENVIRONMENT_USER="username"
ENVIRONMENT_EMAIL="useremail"
ENVIRONMENT_PASS="yourpassword"
`
3. Start the testing server. See
[Steward readme page](https://github.com/lmc-eu/steward#4-run-your-tests)
4. Run  ./vendor/bin/steward run staging chrome --group="magento_quick_test" -vv for the short test
5. Run  ./vendor/bin/steward run staging chrome -vv to go trough all the available tests.

## Problems

Since this is a frontend test, its not always consistent, due to delays or some glitches regarding overlapping elements. If you can't get over an issue please open an issue and I'll take a look. 