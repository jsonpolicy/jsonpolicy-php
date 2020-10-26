# JSON Policy - Distributed Rule Engine

JSON Policy is the lightweight, no dependencies framework that allows you to decouple your codebase from conditional logic.

It is the domain-specific language based on the JSON-format where you prepare well-structured JSON policies that contain knowledge on when to execute certain features/services and under which circumstances.

## Prerequisites

Before you begin, ensure you have met the following requirements:
PHP 7.3+ (recommended), however, may run on PHP 7.0+;
You have read [Json Policy Documentation](https://jsonpolicy.github.io/);

## Installation

You can install the library via Composer with the following command:

```
composer require jsonpolicy/jsonpolicy-php
```

To install manually, download this repository and move all the files inside the `src` folder to the desired destination. Then simply register the autoload function as following

```php
spl_autoload_register(function ($class_name) {
    if (strpos($class_name, 'JSONPolicy') === 0) {
        $filepath  = '<your-desired-folder>';
        $filepath += str_replace(array('JSONPolicy', '\\'), array('', '/'), $class_name) . '.php';
    }

    if (!empty($filepath) && file_exists($filepath)) {
        require_once $filepath;
    }
});
```

## Using JSON Policy

Let's imagine that you build an application for the car dealership, where based on the currently logged in agent, you determine if he/she is allowed to sell the car from available stock. Conditions under which this is determined may vary and most likely will be fluctuated regularly.

Here is how your code may look like:

```php
use JsonPolicy\Manager as JsonPolicyManager;

$manager = JsonPolicyManager::bootstrap([
    'repository' => [
        file_get_contents(__DIR__  . '/policy.json')
    ]
]);

// Create the car dealership instance and pass the available list of cars that can
// be sold
$dealership = new Dealership($stock);

// Check which car is allowed to be sold based on policy attached to current
// identity
foreach ($dealership as $car) {
    if ($manager->isAllowedTo($car, 'sell') === true) {
        echo "You can sell the {$car->model} ($car->year)\n";
    } else {
        echo "You cannot sell the {$car->model} ($car->year)\n";
    }
}
```
The policy that defines all the conditions can be something like this:

```json
{
    "Statement": [
        {
            "Effect": "deny",
            "Resource": "Car"
        },
        {
            "Effect": "allow",
            "Resource": "Car",
            "Action": [
                "sell",
                "view"
            ],
            "Condition": {
                "LessOrEquals": {
                    "(*int)${Car.price}": 30000
                }
            }
        }
    ]
}
```

The above policy does not allow agents to sell or even see cars that are above $30k.


## Contributing to <project_name>
To contribute to JSON Policy, follow these steps:

1. Fork this repository.
2. Create a branch: `git checkout -b <branch_name>`.
3. Make your changes and commit them: `git commit -m '<commit_message>'`
4. Push to the original branch: `git push origin <project_name>/<location>`
5. Create the pull request.

Alternatively see the GitHub documentation on [creating a pull request](https://help.github.com/en/github/collaborating-with-issues-and-pull-requests/creating-a-pull-request).


## Contact

If you want to contact me you can reach me at <vasyl@vasyltech.com>.


## License

This project uses the following license: [<GNU General Public License>](https://www.gnu.org/licenses/#GPL).