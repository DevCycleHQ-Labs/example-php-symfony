<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use DevCycle\DevCycleConfiguration;
use DevCycle\Api\DevCycleClient;
use DevCycle\Model\DevCycleOptions;
use DevCycle\Model\DevCycleUser;
use GuzzleHttp\Client;

class HomeController extends AbstractController
{
    #[Route('/')]
    public function index(): Response
    {

        #--------------------------------------------------------------------
        # Establish a connection to DevCycle
        #--------------------------------------------------------------------

        // Set up the DevCycle client configuration with your server-side SDK key.
        // The SDK key is retrieved from environment variables for security.
        $config = DevCycleConfiguration::getDefaultConfiguration()->setApiKey(
            "Authorization",
            $_ENV["DEVCYCLE_SERVER_SDK_KEY"]
        );

        // Initialize the DevCycle client with the configured settings.
        // Optionally, you can pass a custom HTTP client that implements `GuzzleHttp\ClientInterface`.
        // If not provided, GuzzleHttp\Client will be used by default.
        $devcycle_client = new DevCycleClient(
            $config,
            new Client(), // This is the HTTP client used for making API requests.
        );

        // Create a DevCycle user object with a unique user identifier.
        // This is used to fetch feature flags and variables specific to the user.
        $user_data = new DevCycleUser(array(
            "user_id" => "my-user"
        ));

        #--------------------------------------------------------------------
        # Variables used in togglebot.html.twig 
        #--------------------------------------------------------------------

        // Fetch the value of the "example-text" variable for the user.
        // This demonstrates using another variable to control additional dynamic content.
        $step = $devcycle_client->variableValue($user_data, "example-text", "default");

        // Determine the header and body text to display based on the "step" variable's value.
        switch ($step) {
            case "step-1":
                $header = "Welcome to DevCycle's example app.";
                $body = "If you got here through the onboarding flow, just follow the instructions to change and create new Variations and see how the app reacts to new Variable values.";
                break;
            case "step-2":
                $header = "Great! You've taken the first step in exploring DevCycle.";
                $body = "You've successfully toggled your very first Variation. You are now serving a different value to your users and you can see how the example app has reacted to this change. Next, go ahead and create a whole new Variation to see what else is possible in this app.";
                break;
            case "step-3":
                $header = "You're getting the hang of things.";
                $body = "By creating a new Variation with new Variable values and toggling it on for all users, you've already explored the fundamental concepts within DevCycle. There's still so much more to the platform, so go ahead and complete the onboarding flow and play around with the feature that controls this example in your dashboard.";
                break;
            default:
                $header = "Welcome to DevCycle's example app.";
                $body = "If you got to the example app on your own, follow our README guide to create the Feature and Variables you need to control this app in DevCycle.";
        }


        #--------------------------------------------------------------------
        # Variables used in description.html.twig 
        #--------------------------------------------------------------------


        // Retrieve all feature flags for the specified user.
        $features = $devcycle_client->allFeatures($user_data);

        // Determine the variation name for the "hello-togglebot" feature.
        // If the feature is found, use its variation name; otherwise, default to "Default".
        $variation_name = $features["hello-togglebot"]
            ? $features["hello-togglebot"]["variationName"]
            : "Default";

        // Fetch the value of the "togglebot-speed" variable for the user.
        // If not set, default to "off".
        $speed = $devcycle_client->variableValue($user_data, "togglebot-speed", "off");

        // Fetch the boolean value of the "togglebot-wink" variable for the user.
        // If not set, default to false.
        $wink = $devcycle_client->variableValue($user_data, "togglebot-wink", false);

        // Determine the message to display based on the "speed" variable's value.
        switch ($speed) {
            case 'slow':
                $message = 'Awesome, look at you go!';
                break;
            case 'fast':
                $message = 'This is fun!';
                break;
            case 'off-axis':
                $message = '...I\'m gonna be sick...';
                break;
            case 'surprise':
                $message = 'What the unicorn?';
                break;
            default:
                $message = 'Hello! Nice to meet you.';
                break;
        }

        // Choose the appropriate image source based on the "wink" variable and "speed" value.
        $togglebot_src = $wink ? '/assets/img/togglebot-wink.png' : '/assets/img/togglebot.png';
        if ($speed === 'surprise') {
            $togglebot_src = '/assets/img/unicorn.svg';
        }

        return $this->render('home.html.twig', ['devcycle_client' => $devcycle_client, 'user_data' => $user_data, 'variation_name' => $variation_name, 'message' => $message, 'togglebot_src' => $togglebot_src, 'header' => $header, 'body' => $body, 'step' => $step, 'features' => $features, 'speed' => $speed, 'wink' => $wink]);
    }
}
