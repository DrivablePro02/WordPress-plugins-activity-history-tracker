<?php
/**
 * Plugin Activity Tracker Test
 * 
 * This file contains tests for the PAH_Plugin_Activity_Tracker class.
 * It uses PHPUnit for testing and WP_Mock for mocking WordPress functions.
 */

namespace PAH\Tests;

use PAH\PAH_Plugin_Activity_Tracker;
use PHPUnit\Framework\TestCase;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

/**
 * Test class for PAH_Plugin_Activity_Tracker
 * 
 * This class extends PHPUnit's TestCase and uses MockeryPHPUnitIntegration
 * to integrate with the Mockery mocking framework.
 */
class PAH_Plugin_Activity_TrackerTest extends TestCase {
    // This trait allows us to use Mockery with PHPUnit
    use MockeryPHPUnitIntegration;

    /**
     * Set up the test environment before each test
     * 
     * This method is called before each test method is executed.
     * It initializes WP_Mock and sets up common mocks for WordPress functions.
     */
    public function setUp(): void {
        // Call the parent setUp method
        parent::setUp();
        
        // Initialize WP_Mock
        \WP_Mock::setUp();
        
        // Set up common mocks for WordPress functions
        // These mocks will be available for all test methods
        
        // Mock current_user_can function to always return true
        \WP_Mock::userFunction('current_user_can', [
            'args' => ['manage_options'],
            'return' => true,
        ]);
        
        // Mock is_admin function to always return true
        \WP_Mock::userFunction('is_admin', [
            'return' => true,
        ]);
        
        // Mock get_current_user_id function to return user ID 1
        \WP_Mock::userFunction('get_current_user_id', [
            'return' => 1,
        ]);
        
        // Mock current_time function to return a fixed timestamp
        \WP_Mock::userFunction('current_time', [
            'args' => ['mysql'],
            'return' => '2023-04-20 12:00:00',
        ]);
        
        // Mock get_option function to return an empty array
        \WP_Mock::userFunction('get_option', [
            'args' => ['plugin_activity_logs', []],
            'return' => [],
        ]);
        
        // Mock update_option function to always return true
        \WP_Mock::userFunction('update_option', [
            'args' => ['plugin_activity_logs', \WP_Mock\Functions::type('array'), true],
            'return' => true,
        ]);
        
        // Mock get_transient function to return false (no cached data)
        \WP_Mock::userFunction('get_transient', [
            'args' => ['plugin_activity_logs_transient'],
            'return' => false,
        ]);
        
        // Mock set_transient function to always return true
        \WP_Mock::userFunction('set_transient', [
            'args' => ['plugin_activity_logs_transient', \WP_Mock\Functions::type('array'), 7200],
            'return' => true,
        ]);
        
        // Mock get_plugin_data function to return test plugin data
        \WP_Mock::userFunction('get_plugin_data', [
            'args' => [\WP_Mock\Functions::type('string')],
            'return' => [
                'Name' => 'Test Plugin',
                'Version' => '1.0.0',
            ],
        ]);
    }

    /**
     * Clean up after each test
     * 
     * This method is called after each test method is executed.
     * It tears down WP_Mock and calls the parent tearDown method.
     */
    public function tearDown(): void {
        // Tear down WP_Mock
        \WP_Mock::tearDown();
        
        // Call the parent tearDown method
        parent::tearDown();
    }

    /**
     * Test that the constructor adds all required WordPress hooks
     * 
     * This test verifies that when the PAH_Plugin_Activity_Tracker class
     * is instantiated, it correctly hooks into WordPress using add_action.
     */
    public function testConstructorAddsHooks() {
        // Set expectations for WordPress actions
        // These expectations define what we expect to happen when the constructor is called
        
        // Expect admin_init hook to be added
        \WP_Mock::expectActionAdded('admin_init', ['\PAH\PAH_Plugin_Activity_Tracker', 'check_user_cap']);
        
        // Expect activated_plugin hook to be added with priority 10 and 2 arguments
        \WP_Mock::expectActionAdded('activated_plugin', ['\PAH\PAH_Plugin_Activity_Tracker', 'track_plugin_activation'], 10, 2);
        
        // Expect deactivated_plugin hook to be added with priority 10 and 1 argument
        \WP_Mock::expectActionAdded('deactivated_plugin', ['\PAH\PAH_Plugin_Activity_Tracker', 'track_plugin_deactivation'], 10, 1);
        
        // Expect deleted_plugin hook to be added with priority 10 and 2 arguments
        \WP_Mock::expectActionAdded('deleted_plugin', ['\PAH\PAH_Plugin_Activity_Tracker', 'track_plugin_deletion'], 10, 2);
        
        // Expect upgrader_process_complete hook to be added with priority 10 and 2 arguments
        \WP_Mock::expectActionAdded('upgrader_process_complete', ['\PAH\PAH_Plugin_Activity_Tracker', 'track_plugin_update_or_install'], 10, 2);
        
        // Expect admin_menu hook to be added
        \WP_Mock::expectActionAdded('admin_menu', ['\PAH\PAH_Plugin_Activity_Tracker', 'admin_menu_KjB654']);
        
        // Create an instance of the class
        $tracker = new PAH_Plugin_Activity_Tracker();
        
        // Verify all expectations were met
        \WP_Mock::assertActionsCalled();
    }
    
    /**
     * Test the check_user_cap method
     * 
     * This test verifies that the check_user_cap method correctly
     * checks if the current user has the manage_options capability.
     */
    public function testCheckUserCap() {
        // Mock the WordPress function to return true
        \WP_Mock::userFunction('current_user_can', [
            'args' => ['manage_options'],
            'return' => true,
        ]);
        
        // Create an instance of the class
        $tracker = new PAH_Plugin_Activity_Tracker();
        
        // Assert that check_user_cap returns true
        $this->assertTrue($tracker->check_user_cap());
    }
    
    /**
     * Test the track_plugin_activation method
     * 
     * This test verifies that the track_plugin_activation method correctly
     * logs plugin activation events.
     */
    public function testTrackPluginActivation() {
        // Mock WordPress functions needed for this test
        
        // Mock is_admin to return true
        \WP_Mock::userFunction('is_admin', [
            'return' => true,
        ]);
        
        // Mock get_plugin_data to return test plugin data
        \WP_Mock::userFunction('get_plugin_data', [
            'args' => [\WP_Mock\Functions::type('string')],
            'return' => [
                'Name' => 'Test Plugin',
                'Version' => '1.0.0',
            ],
        ]);
        
        // Mock get_current_user_id to return user ID 1
        \WP_Mock::userFunction('get_current_user_id', [
            'return' => 1,
        ]);
        
        // Mock current_time to return a fixed timestamp
        \WP_Mock::userFunction('current_time', [
            'args' => ['mysql'],
            'return' => '2023-04-20 12:00:00',
        ]);
        
        // Mock get_transient to return false (no cached data)
        \WP_Mock::userFunction('get_transient', [
            'args' => ['plugin_activity_logs_transient'],
            'return' => false,
        ]);
        
        // Mock get_option to return an empty array
        \WP_Mock::userFunction('get_option', [
            'args' => ['plugin_activity_logs', []],
            'return' => [],
        ]);
        
        // Mock update_option to return true
        \WP_Mock::userFunction('update_option', [
            'args' => ['plugin_activity_logs', \WP_Mock\Functions::type('array'), true],
            'return' => true,
        ]);
        
        // Mock set_transient to return true
        \WP_Mock::userFunction('set_transient', [
            'args' => ['plugin_activity_logs_transient', \WP_Mock\Functions::type('array'), 7200],
            'return' => true,
        ]);
        
        // Create an instance of the class
        $tracker = new PAH_Plugin_Activity_Tracker();
        
        // Call the track_plugin_activation method with test data
        $tracker->track_plugin_activation('test-plugin/test-plugin.php', false);
        
        // Verify that all expected WordPress functions were called
        \WP_Mock::assertActionsCalled();
    }
    
    /**
     * Test the track_plugin_deactivation method
     * 
     * This test verifies that the track_plugin_deactivation method correctly
     * logs plugin deactivation events.
     */
    public function testTrackPluginDeactivation() {
        // Mock WordPress functions needed for this test
        
        // Mock is_admin to return true
        \WP_Mock::userFunction('is_admin', [
            'return' => true,
        ]);
        
        // Mock get_plugin_data to return test plugin data
        \WP_Mock::userFunction('get_plugin_data', [
            'args' => [\WP_Mock\Functions::type('string')],
            'return' => [
                'Name' => 'Test Plugin',
                'Version' => '1.0.0',
            ],
        ]);
        
        // Mock get_current_user_id to return user ID 1
        \WP_Mock::userFunction('get_current_user_id', [
            'return' => 1,
        ]);
        
        // Mock current_time to return a fixed timestamp
        \WP_Mock::userFunction('current_time', [
            'args' => ['mysql'],
            'return' => '2023-04-20 12:00:00',
        ]);
        
        // Mock get_transient to return false (no cached data)
        \WP_Mock::userFunction('get_transient', [
            'args' => ['plugin_activity_logs_transient'],
            'return' => false,
        ]);
        
        // Mock get_option to return an empty array
        \WP_Mock::userFunction('get_option', [
            'args' => ['plugin_activity_logs', []],
            'return' => [],
        ]);
        
        // Mock update_option to return true
        \WP_Mock::userFunction('update_option', [
            'args' => ['plugin_activity_logs', \WP_Mock\Functions::type('array'), true],
            'return' => true,
        ]);
        
        // Mock set_transient to return true
        \WP_Mock::userFunction('set_transient', [
            'args' => ['plugin_activity_logs_transient', \WP_Mock\Functions::type('array'), 7200],
            'return' => true,
        ]);
        
        // Create an instance of the class
        $tracker = new PAH_Plugin_Activity_Tracker();
        
        // Call the track_plugin_deactivation method with test data
        $tracker->track_plugin_deactivation('test-plugin/test-plugin.php');
        
        // Verify that all expected WordPress functions were called
        \WP_Mock::assertActionsCalled();
    }
    
    /**
     * Test the admin_menu_KjB654 method
     * 
     * This test verifies that the admin_menu_KjB654 method correctly
     * adds a submenu page to the WordPress admin menu.
     */
    public function testAdminMenu() {
        // Mock add_submenu_page function to return true
        \WP_Mock::userFunction('add_submenu_page', [
            'args' => [
                'plugins.php',
                \WP_Mock\Functions::type('string'),
                \WP_Mock\Functions::type('string'),
                'manage_options',
                'plugin-activity-history',
                \WP_Mock\Functions::type('array'),
            ],
            'return' => true,
        ]);
        
        // Create an instance of the class
        $tracker = new PAH_Plugin_Activity_Tracker();
        
        // Call the admin_menu_KjB654 method
        $tracker->admin_menu_KjB654();
        
        // Verify that add_submenu_page was called
        \WP_Mock::assertActionsCalled();
    }
    
    /**
     * Test the log_activity method
     * 
     * This test verifies that the log_activity method correctly
     * logs plugin activity events.
     * 
     * Since log_activity is a private method, we use PHP's Reflection API
     * to access and test it.
     */
    public function testLogActivity() {
        // Use reflection to access the private method
        $tracker = new PAH_Plugin_Activity_Tracker();
        $reflection = new \ReflectionClass($tracker);
        $method = $reflection->getMethod('log_activity');
        $method->setAccessible(true);
        
        // Mock WordPress functions needed for this test
        
        // Mock get_current_user_id to return user ID 1
        \WP_Mock::userFunction('get_current_user_id', [
            'return' => 1,
        ]);
        
        // Mock current_time to return a fixed timestamp
        \WP_Mock::userFunction('current_time', [
            'args' => ['mysql'],
            'return' => '2023-04-20 12:00:00',
        ]);
        
        // Mock get_transient to return false (no cached data)
        \WP_Mock::userFunction('get_transient', [
            'args' => ['plugin_activity_logs_transient'],
            'return' => false,
        ]);
        
        // Mock get_option to return an empty array
        \WP_Mock::userFunction('get_option', [
            'args' => ['plugin_activity_logs', []],
            'return' => [],
        ]);
        
        // Mock update_option to return true
        \WP_Mock::userFunction('update_option', [
            'args' => ['plugin_activity_logs', \WP_Mock\Functions::type('array'), true],
            'return' => true,
        ]);
        
        // Mock set_transient to return true
        \WP_Mock::userFunction('set_transient', [
            'args' => ['plugin_activity_logs_transient', \WP_Mock\Functions::type('array'), 7200],
            'return' => true,
        ]);
        
        // Call the private method with test data
        $method->invoke($tracker, 'test_action', 'Test Plugin', 'test-plugin.php', '1.0.0');
        
        // Verify that all expected WordPress functions were called
        \WP_Mock::assertActionsCalled();
    }
    
    /**
     * Test the get_paginated_logs method
     * 
     * This test verifies that the get_paginated_logs method correctly
     * retrieves and paginates plugin activity logs.
     * 
     * Since get_paginated_logs is a private method, we use PHP's Reflection API
     * to access and test it.
     */
    public function testGetPaginatedLogs() {
        // Create a reflection to access the private method
        $tracker = new PAH_Plugin_Activity_Tracker();
        $reflection = new \ReflectionClass($tracker);
        $method = $reflection->getMethod('get_paginated_logs');
        $method->setAccessible(true);
        
        // Mock WordPress functions needed for this test
        
        // Mock get_transient to return false (no cached data)
        \WP_Mock::userFunction('get_transient', [
            'args' => ['plugin_activity_logs_transient'],
            'return' => false,
        ]);
        
        // Mock get_option to return test log data
        \WP_Mock::userFunction('get_option', [
            'args' => ['plugin_activity_logs', []],
            'return' => [
                ['action' => 'activation', 'plugin_name' => 'Plugin 1'],
                ['action' => 'deactivation', 'plugin_name' => 'Plugin 2'],
                ['action' => 'activation', 'plugin_name' => 'Plugin 3'],
            ],
        ]);
        
        // Test pagination without filter
        $result = $method->invoke($tracker, 1);
        $this->assertCount(3, $result);
        
        // Test with action filter
        $_GET['action'] = 'activation';
        $result = $method->invoke($tracker, 1);
        $this->assertCount(2, $result);
        
        // Clean up
        unset($_GET['action']);
    }
    
    /**
     * Test error handling in the log_activity method
     * 
     * This test verifies that the log_activity method correctly
     * handles errors when updating the logs.
     * 
     * Since log_activity is a private method, we use PHP's Reflection API
     * to access and test it.
     */
    public function testLogActivityWithError() {
        // Use reflection to access the private method
        $tracker = new PAH_Plugin_Activity_Tracker();
        $reflection = new \ReflectionClass($tracker);
        $method = $reflection->getMethod('log_activity');
        $method->setAccessible(true);
        
        // Mock WordPress functions to simulate an error
        
        // Mock get_current_user_id to return user ID 1
        \WP_Mock::userFunction('get_current_user_id', [
            'return' => 1,
        ]);
        
        // Mock current_time to return a fixed timestamp
        \WP_Mock::userFunction('current_time', [
            'args' => ['mysql'],
            'return' => '2023-04-20 12:00:00',
        ]);
        
        // Mock get_transient to return false (no cached data)
        \WP_Mock::userFunction('get_transient', [
            'args' => ['plugin_activity_logs_transient'],
            'return' => false,
        ]);
        
        // Mock get_option to return an empty array
        \WP_Mock::userFunction('get_option', [
            'args' => ['plugin_activity_logs', []],
            'return' => [],
        ]);
        
        // Mock update_option to return false (simulate failure)
        \WP_Mock::userFunction('update_option', [
            'args' => ['plugin_activity_logs', \WP_Mock\Functions::type('array'), true],
            'return' => false,
        ]);
        
        // Mock error_log to return true
        \WP_Mock::userFunction('error_log', [
            'args' => [\WP_Mock\Functions::type('string')],
            'return' => true,
        ]);
        
        // Call the private method with test data
        $method->invoke($tracker, 'test_action', 'Test Plugin', 'test-plugin.php', '1.0.0');
        
        // Verify that error_log was called
        \WP_Mock::assertActionsCalled();
    }
}