# puper/flarum-ext-roadrunner

`flarum-ext-roadrunner` is a Flarum extension that leverages [RoadRunner](https://roadrunner.dev/) to accelerate your Flarum installation, improving performance and scalability.

## Features

- Optimizes Flarum performance by utilizing the RoadRunner PHP application server.
- Handles requests with better efficiency, reducing the overhead of traditional PHP-FPM.
- Seamlessly integrates with your existing Flarum installation with minimal configuration.

## Installation

Follow these steps to set up `flarum-ext-roadrunner` in your Flarum project.

### 1. Place Files in Your Flarum Root Directory

Put all extension files into the root directory of your Flarum installation. This will allow the extension to access necessary files and configurations.

### 2. Download and Install RoadRunner

Download the latest RoadRunner binary from the [official releases page](https://github.com/roadrunner-server/roadrunner/releases) and move it to the `/usr/local/bin` directory.

### 3. Update `composer.json`

Add the following snippet to your `composer.json` file under the `"repositories"` section:

```json
"repositories": [
    {
        "type": "path",
        "url": "./roadrunner/worker"
    },
],
"minimum-stability": "dev"
```

This tells Composer to look for local development packages.

### 4. Install Dependencies
Run the following command to install RoadRunner and all its dependencies:

```
composer require spiral/roadrunner-http "^3" --with-all-dependencies --prefer-source
```
This will fetch the required RoadRunner server for Flarum.

## Usage
1. Start the RoadRunner Server
Once everything is set up, you can start the RoadRunner server with the following command:
```
rr serve
```
2. Access Your Flarum Installation
After starting the RoadRunner server, your Flarum installation will be accessible at:
```
http://localhost:8080
```
Enjoy the improved performance of your Flarum forum!

## Notes
Make sure to configure your server and environment according to the RoadRunner documentation for optimal performance.
This extension is designed to work with RoadRunner 3.x.

## License
This project is licensed under the MIT License. See the LICENSE file for more details.