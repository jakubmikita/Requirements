[![Latest Stable Version](https://poser.pugx.org/underdev/requirements/v/stable)](https://packagist.org/packages/underdev/requirements) [![Total Downloads](https://poser.pugx.org/underdev/requirements/downloads)](https://packagist.org/packages/underdev/requirements) [![Latest Unstable Version](https://poser.pugx.org/underdev/requirements/v/unstable)](https://packagist.org/packages/underdev/requirements)

# WordPress plugin requirements
WordPress drop-in to check requirements

Just grab the underDEV_Requirements.php file and include it in your project or install via Composer:

`composer require underdev/requirements`

Default checks:
* PHP version
* PHP extensions loaded
* WordPress version
* Active plugins and their versions
* Active theme
* Function collisions
* Class collisions

You can add also your own checks. See the example below.

It doesn't brake the user action, ie. activating many plugins at once. Will just display a message in admin area:

![Requirements in WP Admin](https://www.wpart.co/img/requirements.png)

## Sample usage

```php
<?php
/*
Plugin Name: My Test Plugin
Version: 1.0
*/

/**
 * If installed by download
 */
require_once( 'underDEV_Requirements.php' );

/**
 * If installed via Composer it's included in the autoloader
 */
require_once( 'vendor/autoload.php' );

$requirements = new underDEV_Requirements( 'My Test Plugin', array(
	'php'                => '5.3',
	'php_extensions'     => array( 'soap' ),
	'wp'                 => '4.8',
	'plugins'            => array(
		'akismet/akismet.php'   => array( 'name' => 'Akismet', 'version' => '3.0' ),
		'hello-dolly/hello.php' => array( 'name' => 'Hello Dolly', 'version' => '1.5' )
	),
	'theme'              => array(
		'slug' => 'twentysixteen',
		'name' => 'Twenty Sixteen'
	),
	'function_collision' => array( 'my_function_name', 'some_other_potential_collision' ),
	'class_collision'    => array( 'My_Test_Plugin', 'My_Test_Plugin_Other_Class' ),
	'custom_check'       => 'thing to check', // this is not default check and will have to be registered
) );

/**
 * Add your own check
 */
function my_plugin_custom_check( $comparsion, $r ) {
	if ( $comparsion != 'thing to check' ) {
		$r->add_error( 'this thing to be that' );
	}
}

$requirements->add_check( 'custom_check', 'my_plugin_custom_check' );

/**
 * Run all the checks and check if requirements has been satisfied
 * If not - display the admin notice and exit from the file
 */
if ( ! $requirements->satisfied() ) {

	add_action( 'admin_notices', array( $requirements, 'notice' ) );
	return;

}

/**
 * Checks passed - load the plugin
 */
new My_Test_Plugin();
```
